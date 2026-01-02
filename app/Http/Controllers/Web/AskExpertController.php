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
use App\Traits\Processor;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use Stripe\Subscription;
use Stripe\InvoiceItem;
use App\Utils\Notifications;
use App\Models\Admin;
use Stripe\PaymentIntent;

class AskExpertController extends Controller
{
    use Processor;

    private $config_values;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('stripe', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }
        $this->payment = $payment;
    }

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
                'message' => 'No experts found'
            ]);
        }

        $matched = collect($result['recommendations'])->first();
        $category = ExpertCategory::findOrFail($matched['category_id']);

        $expert = $availabilityService->findAvailableExpert(
            collect($result['recommendations'])->pluck('expert_id')->toArray()
        );

        // if (!$expert) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'No expert available'
        //     ]);
        // }
        $expertId = $expert?->id;

        $needsJoining = !$user->hasPaidJoiningFee();

        $activeSubscription = UserSubscription::where('user_id', $user->id)
            ->where('active', true)
            ->first();

        if (!$activeSubscription || $activeSubscription->current_period_end < now()) {
            $needsMembership = true;
            $subscriptionToExtend = $activeSubscription;
        } else {
            $needsMembership = false;
            $subscriptionToExtend = $activeSubscription;
        }

        $needsExpertFee = true;
        Stripe::setApiKey($this->config_values->api_key);
        $stripeCustomer = $user->stripe_customer_id
            ? \Stripe\Customer::retrieve($user->stripe_customer_id)
            : \Stripe\Customer::create([
                'email' => $user->email,
                'name'  => $user->f_name . ' ' . $user->l_name,
                'metadata' => ['user_id' => $user->id],
            ]);

        if (!$user->stripe_customer_id) {
            $user->update(['stripe_customer_id' => $stripeCustomer->id]);
        }

        DB::beginTransaction();
        try {
            $subscription = null;
            $paymentIntent = null;

            if ($needsMembership) {
                if (!$category->stripe_product_id) {
                    $product = Product::create([
                        'name' => 'Membership - ' . $category->name,
                        'metadata' => ['category_id' => $category->id],
                    ]);

                    $category->stripe_product_id = $product->id;
                    $category->save();
                }

                if (!$category->stripe_subscription_price_id) {
                    $price = Price::create([
                        'product' => $category->stripe_product_id,
                        'unit_amount' => (int) ($category->monthly_subscription_fee * 100),
                        'currency' => 'usd',
                        'recurring' => ['interval' => 'month'],
                    ]);

                    $category->stripe_subscription_price_id = $price->id;
                    $category->save();
                }

                $subscription = Subscription::create([
                    'customer' => $stripeCustomer->id,
                    'items' => [
                        ['price' => $category->stripe_subscription_price_id],
                    ],
                    'payment_behavior' => 'default_incomplete',
                    'expand' => ['latest_invoice.payment_intent'],
                ]);

                $paymentIntent = $subscription->latest_invoice->payment_intent;
            }

            if ($needsJoining) {
                InvoiceItem::create([
                    'customer' => $stripeCustomer->id,
                    'amount' => (int) ($category->joining_fee * 100),
                    'currency' => 'usd',
                    'description' => 'Joining Fee - ' . $category->name,
                ]);
            }

            if ($needsExpertFee) {
                InvoiceItem::create([
                    'customer' => $stripeCustomer->id,
                    'amount' => (int) ($category->expert_fee * 100),
                    'currency' => 'usd',
                    'description' => 'Expert Consultation - ' . $category->name,
                ]);
            }

            $paymentRequest = PaymentRequest::create([
                'id' => Str::uuid(),
                'payer_id' => $user->id,
                'payment_amount' => ($needsJoining ? $category->joining_fee : 0) +
                    ($needsExpertFee ? $category->expert_fee : 0) +
                    ($needsMembership ? $category->monthly_subscription_fee : 0),
                'currency_code' => 'USD',
                'payment_method' => 'stripe',
                'payment_platform' => 'web',
                'transaction_id' => $paymentIntent->id ?? null,
                'attribute' => 'expert_chat',
                'attribute_id' => Str::random(10),
                'is_paid' => 0,
                'additional_data' => json_encode([
                    'user_id' => $user->id,
                    'expert_id' => $expertId,
                    'category_id' => $category->id,
                    'stripe_subscription_id' => $subscriptionToExtend?->stripe_subscription_id ?? $subscription?->id,
                    'stripe_customer_id' => $stripeCustomer->id,
                    'question' => $request->question,
                    'needs_joining' => $needsJoining,
                    'needs_membership' => $needsMembership,
                    'joining_fee' => $category->joining_fee,
                    'membership_fee' => $category->monthly_subscription_fee,
                    'expert_fee' => $category->expert_fee
                ]),
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
            Log::error('Stripe Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment setup failed',
            ]);
        }
    }


    public static function expert_chat_payment_success($paymentRequest)
    {
        Log::info('expert_chat_payment_success triggered');
        $data = json_decode($paymentRequest->additional_data);

        $user = User::find($data->user_id);
        $expert = $data->expert_id
            ? \App\Models\Expert::find($data->expert_id)
            : null;
        $category = ExpertCategory::find($data->category_id);

        if (!$user || !$category) return;

        DB::beginTransaction();
        try {
            // Joining fee payment
            if ($data->needs_joining && !UserPayment::where('stripe_payment_intent_id', $paymentRequest->transaction_id)
                ->where('type', 'joining_fee')->exists()) {

                UserPayment::create([
                    'user_id' => $user->id,
                    'amount' => $data->joining_fee,
                    'type' => 'joining_fee',
                    'stripe_payment_intent_id' => $paymentRequest->transaction_id,
                    'paid_at' => now(),
                ]);
            }
            if ($data->needs_membership) {
                UserSubscription::updateOrCreate(
                    ['user_id' => $user->id, 'category_id' => $data->category_id],
                    [
                        'monthly_fee' => $data->membership_fee,
                        'stripe_subscription_id' => $data->stripe_subscription_id,
                        'stripe_customer_id' => $data->stripe_customer_id,
                        'current_period_start' => now(),
                        'current_period_end' => now()->addMonth(),
                        'active' => true,
                        'auto_renew' => true,
                    ]
                );
            }


            $chat = ChatSession::create([
                'user_id' => $user->id,
                'expert_id' => $expert?->id, // NULL allowed
                'category_id' => $data->category_id,
                'status' => 'active',
                'payment_status' => 'paid',
                'total_charged' => $data->expert_fee,
                'started_at' => now()
            ]);
            if (!UserPayment::where('stripe_payment_intent_id', $paymentRequest->transaction_id)
                ->where('type', 'expert_fee')->exists()) {

                ChatPayment::create([
                    'chat_session_id' => $chat->id ?? null,
                    'user_id' => $user->id,
                    'expert_fee' => $data->expert_fee,
                    'stripe_payment_intent_id' => $paymentRequest->transaction_id,
                    'paid_at' => now(),
                ]);
            }

            if ($expert) {
                $expert->update([
                    'is_busy' => true,
                    'current_chat_id' => $chat->id
                ]);
            }


            ChatMessage::create([
                'chat_session_id' => $chat->id,
                'sender_type' => 'user',
                'sender_id' => $user->id,
                'message' => $data->question,
                'sent_at' => now()
            ]);

            if ($expert) {
                ChatMessage::create([
                    'chat_session_id' => $chat->id,
                    'sender_type' => 'expert',
                    'sender_id' => $expert->id,
                    'message' => 'Expert is arriving, please wait a few seconds',
                    'sent_at' => now()
                ]);
            } else {
                ChatMessage::create([
                    'chat_session_id' => $chat->id,
                    'sender_type' => 'system',
                    'message' => 'Please wait a few minutes, admin will assign you an expert shortly.',
                    'sent_at' => now()
                ]);
            }

            DB::commit();
            $notificationRepo = app(\App\Contracts\Repositories\AdminNotificationRepositoryInterface::class);

            if ($expert) {
                $notificationRepo->notifyRecipients(
                    $chat->id,
                    ChatSession::class,
                    "Question Assigned",
                    "Question has been assigned to expert {$expert->f_name} {$expert->l_name}",
                    [['type' => 'admin', 'id' => 1]]
                );

                // Expert
                $notificationRepo->notifyRecipients(
                    $chat->id,
                    ChatSession::class,
                    "New Question Assigned",
                    "You have been assigned a new question from {$user->f_name} {$user->l_name}",
                    [['type' => 'expert', 'id' => $expert->id]]
                );

                // User
                $notificationRepo->notifyRecipients(
                    $chat->id,
                    ChatSession::class,
                    "Expert Assigned",
                    "Your question has been assigned to expert {$expert->f_name} {$expert->l_name}",
                    [['type' => 'user', 'id' => $user->id]]
                );
            } else {
                // 🚨 CASE 2: NO EXPERT — ONLY ADMIN

                $notificationRepo->notifyRecipients(
                    $chat->id,
                    ChatSession::class,
                    "Expert Assignment Required",
                    "A paid chat has been created. Please assign an expert.",
                    [['type' => 'admin', 'id' => 1]]
                );
            }
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
