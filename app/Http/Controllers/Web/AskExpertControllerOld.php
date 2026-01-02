<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PythonExpertService;
use App\Services\ExpertService;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\PaymentRequest;
use App\Models\ExpertCategory;
use App\Models\User;
use App\Models\UserPayment;
use App\Models\UserSubscription;
use App\Models\ChatPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AskExpertController extends Controller
{
    public function startChat(
        Request $request,
        PythonExpertService $pythonService,
        ExpertService $availabilityService
    ) {
        $request->validate(['question' => 'required|min:10']);

        $userId = auth('customer')->id();
        $user = User::find($userId);
        $result = $pythonService->recommendExperts($request->question);

        if (empty($result['recommendations'])) {
            return response()->json([
                'success' => false,
                'message' => 'No experts found for this question'
            ]);
        }

        $recommendations = collect($result['recommendations']);
        $matched = $recommendations->first();
        $categoryId = $matched['category_id'] ?? null;

        $category = ExpertCategory::findOrFail($categoryId);

        $expertIds = $recommendations->pluck('expert_id')->toArray();
        $expert = $availabilityService->findAvailableExpert($expertIds);

        if (!$expert) {
            return response()->json([
                'success' => false,
                'message' => 'No expert available right now'
            ]);
        }

        $needsJoining = !$user->hasPaidJoiningFee();
        $needsMembership = !$user->hasActiveMembership($categoryId);
        $needsExpertFee = true;

        $totalAmount = 0;
        $breakdown = [];

        if ($needsJoining) {
            $totalAmount += $category->joining_fee;
            $breakdown[] = "Joining Fee: $" . number_format($category->joining_fee, 2);
        }

        if ($needsMembership) {
            $totalAmount += $category->monthly_subscription_fee;
            $breakdown[] = "Monthly Membership (1st month): $" . number_format($category->monthly_subscription_fee, 2);
        }

        if ($needsExpertFee) {
            $totalAmount += $category->expert_fee;
            $breakdown[] = "Expert Consultation Fee: $" . number_format($category->expert_fee, 2);
        }

        if ($totalAmount <= 0) {
            return $this->createChatAndRedirect($user, $expert, $categoryId, $request->question);
        }

        DB::beginTransaction();
        try {
            $paymentRequest = PaymentRequest::create([
                'id' => Str::uuid(),
                'payer_id' => $user->id,
                'payment_amount' => $totalAmount,
                'currency_code' => 'USD', // ya jo bhi use kar rahe ho
                'payment_method' => 'stripe',
                'payment_platform' => 'web',
                'success_hook' => 'expert_chat_payment_success',
                'failure_hook' => 'expert_chat_payment_fail',
                'additional_data' => json_encode([
                    'user_id' => $user->id,
                    'question' => $request->question,
                    'expert_id' => $expert->id,
                    'category_id' => $categoryId,
                    'needs_joining' => $needsJoining,
                    'needs_membership' => $needsMembership,
                    'joining_fee' => $needsJoining ? $category->joining_fee : 0,
                    'membership_fee' => $needsMembership ? $category->monthly_subscription_fee : 0,
                    'expert_fee' => $category->expert_fee,
                    'category_name' => $category->name,
                    'category_specialty' => $category->primary_specialty ?? $category->name,
                    'product_title' => $category->name,
                    'headline' => "Fix your " . strtolower($category->primary_specialty ?? $category->name) . " issue with expert help now",
                    'pricing_paragraph' => "Ask your question" .
                        ($needsJoining ? " for a $" . number_format($category->joining_fee, 2) . " one-time joining fee" : "") .
                        (($needsMembership || $needsJoining) && $needsMembership ? " and" : "") .
                        ($needsMembership ? " $" . number_format($category->monthly_subscription_fee, 2) . "/mo membership" : "") .
                        " — then either cancel anytime or continue for $" . number_format($category->monthly_subscription_fee, 2) . "/mo thereafter.",
                    'category_image' => $category->card_image_url ?? asset('default-category-image.jpg'),
                ]),
                'attribute' => 'expert_chat',
                'attribute_id' => Str::random(10), // temporary
                'is_paid' => 0,
            ]);

            DB::commit();

            $redirectUrl = route('stripe.pay') . '?payment_id=' . $paymentRequest->id;
            return response()->json([
                'success' => true,
                'requires_payment' => true,
                'payment_url' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Yeh 3 lines add kar de
            Log::error('PaymentRequest Create Error: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Payment setup failed: ' . $e->getMessage(), // temporary
                'error_details' => $e->getMessage() // frontend pe dikhega temporarily
            ]);
        }
    }
    public static function expert_chat_payment_success($paymentRequest)
    {

        Log::info('expert_chat_payment_success hook triggered');
        $data = json_decode($paymentRequest->additional_data);

        $user = User::find($data->user_id);
        $expert = \App\Models\Expert::find($data->expert_id);
        $category = ExpertCategory::find($data->category_id);

        if (!$user || !$expert || !$category) return;

        DB::beginTransaction();
        try {
            if ($data->needs_joining) {
                UserPayment::create([
                    'user_id' => $user->id,
                    'amount' => $data->joining_fee,
                    'type' => 'joining_fee',
                    'stripe_payment_intent_id' => $paymentRequest->transaction_id ?? null,
                    'paid_at' => now(),
                ]);
            }

            if ($data->needs_membership) {
                UserSubscription::create([
                    'user_id'                => $user->id,
                    'category_id'            => $data->category_id,
                    'monthly_fee'            => $data->membership_fee,
                    'stripe_subscription_id' => 'one_time_' . ($paymentRequest->transaction_id ?? Str::random(10)),
                    'stripe_customer_id'     => 'guest_' . $user->id,
                    'current_period_start'   => now(),
                    'current_period_end'     => now()->addMonth(),
                    'active'                 => true,
                    'auto_renew'             => true,
                ]);
            }

            $chat = ChatSession::create([
                'user_id' => $user->id,
                'expert_id' => $expert->id,
                'category_id' => $data->category_id,
                'status' => 'active',
                'payment_status' => 'paid',
                'total_charged' => $data->expert_fee,
                'started_at' => now()
            ]);

            ChatPayment::create([
                'chat_session_id' => $chat->id,
                'user_id' => $user->id,
                'expert_fee' => $data->expert_fee,
                'stripe_payment_intent_id' => $paymentRequest->transaction_id ?? null,
                'paid_at' => now(),
            ]);

            $expert->update([
                'is_busy' => true,
                'current_chat_id' => $chat->id
            ]);

            ChatMessage::create([
                'chat_session_id' => $chat->id,
                'sender_type' => 'user',
                'sender_id' => $user->id,
                'message' => $data->question,
                'sent_at' => now()
            ]);

            ChatMessage::create([
                'chat_session_id' => $chat->id,
                'sender_type' => 'expert',
                'message' => 'Expert is arriving, please wait a few seconds',
                'sent_at' => now()
            ]);

            DB::commit();

            session(['expert_chat_after_payment' => $chat->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Expert chat payment success failed: ' . $e->getMessage());
        }
    }

    public static function expert_chat_payment_fail($paymentRequest)
    {
        // Optional: log or notify
    }

    // Final success page (user yaha redirect hoga payment ke baad)
    public function paymentSuccess()
    {

        Log::info('paymentSuccess');

        $chatId = session('expert_chat_after_payment');
        session()->forget('expert_chat_after_payment');

        if (!$chatId) {
            return redirect('/')->with('error', 'Chat session not found.');
        }

        return redirect()->route('chat.view', $chatId)->with('success', 'Payment successful! You can now chat with the expert.');
    }

    public function paymentFail()
    {
        return view('payment.failed')->with('message', 'Payment failed or cancelled.');
    }

    private function createChatAndRedirect($user, $expert, $categoryId, $question)
    {

        Log::info('createChatAndRedirect');

        $chat = ChatSession::create([
            'user_id' => $user->id,
            'expert_id' => $expert->id,
            'category_id' => $categoryId,
            'status' => 'active',
            'payment_status' => 'free',
            'total_charged' => 0,
            'started_at' => now()
        ]);

        $expert->update([
            'is_busy' => true,
            'current_chat_id' => $chat->id
        ]);

        ChatMessage::create([
            'chat_session_id' => $chat->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'message' => $question,
            'sent_at' => now()
        ]);

        ChatMessage::create([
            'chat_session_id' => $chat->id,
            'sender_type' => 'expert',
            'message' => 'Expert is arriving, please wait a few seconds',
            'sent_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'redirect_url' => route('chat.view', $chat->id)
        ]);
    }
}
