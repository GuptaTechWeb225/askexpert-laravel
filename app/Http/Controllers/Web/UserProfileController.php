<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\RobotsMetaContentRepositoryInterface;
use App\Enums\WebConfigKey;
use App\Events\RefundEvent;
use App\Http\Requests\Web\CustomerProfileUpdateRequest;
use App\Traits\PdfGenerator;
use App\Http\Controllers\Controller;
use App\Models\ChatPayment;
use App\Models\UserPayment;
use App\Models\UserSubscription;
use App\Traits\CommonTrait;
use App\Models\User;
use App\Models\ChatSession;
use App\Models\ChatRefundRequest;
use App\Utils\ImageManager;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\Processor;

class UserProfileController extends Controller
{
    use CommonTrait, PdfGenerator, Processor;


    private $config_values;

    public function __construct(
        private readonly BusinessSettingRepositoryInterface   $businessSettingRepo,
        private readonly RobotsMetaContentRepositoryInterface $robotsMetaContentRepo,
    ) {}

    public function user_profile(Request $request)
    {
        $total_loyalty_point = auth('customer')->user()->loyalty_point;
        $total_wallet_balance = auth('customer')->user()->wallet_balance;
        $customer_detail = User::where('id', auth('customer')->id())->first();

        return view(VIEW_FILE_NAMES['user_profile'], compact('customer_detail', 'addresses', 'wishlists', 'total_order', 'total_loyalty_point', 'total_wallet_balance'));
    }

    public function user_account(Request $request)
    {
        $country_restrict_status = getWebConfig(name: 'delivery_country_restriction');
        $customerDetail = User::where('id', auth('customer')->id())->first();
        return view(VIEW_FILE_NAMES['user_account'], compact('customerDetail'));
    }




    public function getUserProfileUpdate(CustomerProfileUpdateRequest $request)
    {

        Log::info('the request is ', ['request' => $request->all()]);
        try {
            $imageName = $request->file('image') ? ImageManager::update('profile/', auth('customer')->user()->image, 'webp', $request->file('image')) : auth('customer')->user()->image;
            $user = auth('customer')->user();

            User::find($user['id'])->update([
                'f_name' => $request['f_name'],
                'l_name' => $request['l_name'],
                'phone' => $user['is_phone_verified'] ? $user['phone'] : $request['phone'],
                'email' => $request['email'],
                'is_phone_verified' => $request['phone'] == $user['phone'] ? $user['is_phone_verified'] : 0,
                'is_email_verified' => $request['email'] == $user['email'] ? $user['is_email_verified'] : 0,
                'image' => $imageName,
                'password' => strlen($request['password']) > 5 ? bcrypt($request['password']) : auth('customer')->user()->password,
            ]);

            return response()->json([
                'status' => 1,
                'message' => translate('updated_successfully'),
            ]);
        } catch (\Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'error' => translate('something_went_wrong'),
                'exception' => $e->getMessage(),
            ], 500);
        }
    }


    public function account_delete($id)
    {
        if (auth('customer')->id() == $id) {
            $user = User::find($id);
            auth()->guard('customer')->logout();

            ImageManager::delete('/profile/' . $user['image']);
            $user->delete();
            Toastr::success(translate('Your_account_deleted_successfully!!'));
            return response()->json([
                'success' => true,
                'message' => translate('Your_account_deleted_successfully!!'),
                'redirect' => route('home')
            ]);
        }
        Toastr::warning(translate('access_denied') . '!!');
        return back();
    }


    public function userPlans(Request $request): View
    {
        $userId = auth('customer')->id();

        // 1. Active Memberships (monthly subscriptions)
        $subscriptions = UserSubscription::with('category')
            ->where('user_id', $userId)
            ->where('active', true)
            ->get();

        $joiningPayments = UserPayment::where('user_id', $userId)
            ->where('type', 'joining_fee')
            ->get();


        // 3. Expert Consultation Fees (per chat/question)
        $expertPayments = ChatPayment::with(['chatSession.category', 'chatSession.firstMessage', 'chatSession.refundRequest'])
            ->where('user_id', $userId)
            ->orderByDesc('paid_at')
            ->paginate(10);

        return view(VIEW_FILE_NAMES['user_plan'], compact('subscriptions', 'joiningPayments', 'expertPayments'));
    }
    public function userExperts(Request $request): View
    {
        $chatExpertsQuery = ChatSession::with(['expert', 'category'])
            ->where('user_id', auth('customer')->id())
            ->whereNotNull('expert_id');

        // My Experts Filters
        if ($request->filled('my_category_id')) {
            $chatExpertsQuery->where('category_id', $request->my_category_id);
        }

        if ($request->filled('my_status')) {
            $chatExpertsQuery->where('status', $request->my_status);
        }

        if ($request->filled('my_search')) {
            $search = $request->my_search;
            $chatExpertsQuery->whereHas('expert', function ($q) use ($search) {
                $q->where('f_name', 'like', "%{$search}%")
                    ->orWhere('l_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(f_name, ' ', l_name) LIKE ?", ["%{$search}%"])
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $chatExperts = $chatExpertsQuery
            ->latest('started_at')
            ->paginate(10, ['*'], 'chat_experts_page')
            ->withQueryString();
        return view(VIEW_FILE_NAMES['my_experts'], compact('chatExperts'));
    }
    public function userQuestions(Request $request): View
    {
        $query = ChatSession::with([
            'category:id,name',
            'expert:id,f_name,l_name',
            'firstMessage:id,chat_session_id,message'
        ])
            ->where('user_id', auth('customer')->id());
        if ($request->filled('q_category_id')) {
            $query->where('category_id', $request->q_category_id);
        }


        if ($request->filled('q_status')) {
            $query->where('status', $request->q_status);
        }

        if ($request->filled('q_search')) {
            $searchTerm = $request->q_search;
            $query->whereHas('firstMessage', function ($q) use ($searchTerm) {
                $q->where('message', 'like', "%{$searchTerm}%");
            });
        }

        $questions = $query
            ->latest('started_at')
            ->paginate(10)
            ->withQueryString();
        return view(VIEW_FILE_NAMES['my_questions'], compact('questions'));
    }

    public function storeRefundRequest(Request $request)
    {
        $request->validate([
            'chat_session_id' => 'required|exists:chat_sessions,id',
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $chatSession = ChatSession::where('id', $request->chat_session_id)
            ->where('user_id', auth('customer')->id())
            ->firstOrFail();

        // 24 hours check
        if (!$chatSession->ended_at || $chatSession->ended_at->lt(now()->subHours(24))) {
            return response()->json([
                'success' => false,
                'message' => 'Refund window has expired (only within 24 hours after chat ends)'
            ], 422);
        }

        // Already request bani hai?
        if ($chatSession->refundRequest()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Refund request already submitted for this session'
            ], 422);
        }

        $chatPayment = $chatSession->payment;

        if (!$chatPayment) {
            return response()->json([
                'success' => false,
                'message' => 'No payment found for this session'
            ], 422);
        }

        $refundRequest = ChatRefundRequest::create([
            'chat_session_id' => $chatSession->id,
            'user_id' => auth('customer')->id(),
            'chat_payment_id' => $chatPayment->id,
            'requested_amount' => $chatPayment->expert_fee,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);


        $notificationRepo = app(\App\Contracts\Repositories\AdminNotificationRepositoryInterface::class);
        $notificationRepo->notifyRecipients(
            $refundRequest->id,
            ChatRefundRequest::class,
            "New Refund Request",
            "A refund request has been submitted for chat #{$chatSession->id}",
            [
                ['type' => 'admin', 'id' => 1]
            ]
        );

        $notificationRepo->notifyRecipients(
            $refundRequest->id,
            ChatRefundRequest::class,
            "Refund Request Submitted",
            "Your refund request has been submitted and is under review.",
            [
                ['type' => 'user', 'id' => auth('customer')->id()]
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Refund request submitted successfully. We will review it soon.'
        ]);
    }

    public function cancelAutoRenew(Request $request, $subscriptionId)
    {
        $subscription = UserSubscription::where('id', $subscriptionId)
            ->where('user_id', auth('customer')->id())
            ->where('active', true)
            ->firstOrFail();
        $notificationRepo = app(\App\Contracts\Repositories\AdminNotificationRepositoryInterface::class);


        if (!$subscription->stripe_subscription_id) {
            $subscription->update(['auto_renew' => false]);
            $notificationRepo->notifyRecipients(
                $subscription->id,
                UserSubscription::class,
                "Auto Renew Cancelled",
                "User cancelled auto-renew for subscription #{$subscription->id}",
                [
                    ['type' => 'admin', 'id' => 1]
                ]
            );

            // 🔔 Customer notification
            $notificationRepo->notifyRecipients(
                $subscription->id,
                UserSubscription::class,
                "Auto Renew Cancelled",
                "Your subscription auto-renew has been cancelled successfully.",
                [
                    ['type' => 'user', 'id' => auth('customer')->id()]
                ]
            );
            return response()->json([
                'success' => true,
                'message' => translate('Auto_renew_cancelled_successfully')
            ]);
        }

        try {
            $config = $this->payment_config('stripe', 'payment_config');
            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment configuration not found'
                ], 500);
            }

            $config_values = $config->mode === 'live'
                ? json_decode($config->live_values)
                : json_decode($config->test_values);

            \Stripe\Stripe::setApiKey($config_values->api_key);

            $stripeSub = \Stripe\Subscription::retrieve($subscription->stripe_subscription_id);
            $cancelAtPeriodEnd = !$stripeSub->cancel_at_period_end;

            \Stripe\Subscription::update($subscription->stripe_subscription_id, [
                'cancel_at_period_end' => $cancelAtPeriodEnd,
            ]);
            $subscription->update([
                'auto_renew' => !$cancelAtPeriodEnd,
            ]);

            $message = $cancelAtPeriodEnd
                ? translate('Auto_renew_cancelled_successfully') . ' ' . translate('Your subscription will end on') . ' ' . Carbon::createFromTimestamp($stripeSub->current_period_end)->format('M d, Y')
                : translate('Auto_renew_enabled_successfully');

            return response()->json([
                'success' => true,
                'message' => $message,
                'will_end_on' => $cancelAtPeriodEnd ? Carbon::createFromTimestamp($stripeSub->current_period_end)->format('M d, Y') : null
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe subscription cancel/renew failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => translate('Failed to update subscription. Please try again or contact support.')
            ], 500);
        }
    }
}
