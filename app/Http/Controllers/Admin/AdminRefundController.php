<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\AdminWalletRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Enums\ViewPaths\Admin\Dashboard;
use App\Http\Controllers\BaseController;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ChatRefundRequest;
use Stripe\Stripe;
use Stripe\Refund;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\PaymentRequest;
use App\Models\UserSubscription;
use App\Models\UserPayment;
use App\Traits\Processor;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface;

class   AdminRefundController extends BaseController
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

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->dashboard($request);
    }

    public function dashboard(Request $request)
    {
        $query = ChatRefundRequest::with([
            'user',
            'chatSession.expert',
            'chatSession.category',
            'chatSession.firstMessage',
            'chatPayment'
        ]);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('f_name', 'like', "%{$request->search}%")
                    ->orWhere('l_name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $refunds = $query->latest()->paginate(15);

        return view('admin-views.refund.index', compact('refunds'));
    }

    public function view($id)
    {
        $refund = ChatRefundRequest::with([
            'user',
            'chatSession.expert',
            'chatSession.category',
            'chatSession.firstMessage',
            'chatPayment'
        ])->findOrFail($id);

        return view('admin-views.refund.view', compact('refund'));
    }


    public function processRefund(Request $request, $id)
    {
        $refundRequest = ChatRefundRequest::findOrFail($id);

        if ($refundRequest->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Already processed']);
        }

        $refundTypes = $request->input('refund_types', []);
        $cancelSub = $request->boolean('cancel_subscription');
        $totalRefundAmount = 0;

        // Calculate total refund amount
        if (in_array('expert_fee', $refundTypes)) {
            $totalRefundAmount += $refundRequest->requested_amount;
        }

        if (in_array('joining_fee', $refundTypes)) {
            $joiningPaid = UserPayment::where('user_id', $refundRequest->user_id)
                ->where('type', 'joining_fee')
                ->whereNotNull('paid_at')
                ->sum('amount');
            $totalRefundAmount += $joiningPaid;
        }

        if ($totalRefundAmount > 0) {
            try {
                Stripe::setApiKey($this->config_values->api_key);

                $paymentIntentId = $refundRequest->chatPayment?->stripe_payment_intent_id;

                if (!$paymentIntentId) {
                    throw new \Exception('Payment Intent not found');
                }

                Refund::create([
                    'payment_intent' => $paymentIntentId,
                    'amount' => (int) ($totalRefundAmount * 100),
                    'reason' => 'requested_by_customer',
                    'metadata' => [
                        'refund_request_id' => $refundRequest->id,
                        'refunded_items' => implode(', ', $refundTypes),
                        'admin_note' => $request->admin_note ?? ''
                    ]
                ]);

                Log::info('Refund created', ['refund ammount' => $totalRefundAmount]);
            } catch (\Exception $e) {
                Log::error('Refund failed: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Stripe refund failed: ' . $e->getMessage()
                ]);
            }
        }
        if ($cancelSub) {
            UserSubscription::where('user_id', $refundRequest->user_id)
                ->where('active', true)
                ->update(['auto_renew' => false]);
        }
        $refundRequest->update([
            'status' => 'approved',
            'admin_note' => $request->admin_note,
            'approved_at' => now(),
        ]);

        $notificationRepo = app(AdminNotificationRepositoryInterface::class);

        $userId = $refundRequest->user_id;
        $refundAmountText = $totalRefundAmount > 0 ? "\${$totalRefundAmount}" : "partial amount";


        $notificationRepo->notifyRecipients(
            $refundRequest->id,
            ChatRefundRequest::class,
            "Refund Approved",
            "Refund request #{$refundRequest->id} has been approved. Amount: {$refundAmountText}.",
            [
                ['type' => 'admin', 'id' => 1]
            ]
        );


        $notificationRepo->notifyRecipients(
            $refundRequest->id,
            ChatRefundRequest::class,
            "Refund Approved",
            "Your refund request has been approved. Refunded amount: {$refundAmountText}.",
            [
                ['type' => 'user', 'id' => $userId]
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Refund of \${$totalRefundAmount} approved and processed successfully."
        ]);
    }


    public function rejectRefund(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $refundRequest = ChatRefundRequest::findOrFail($id);

        if ($refundRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request is already processed.'
            ]);
        }

        $refundRequest->status = 'rejected';
        $refundRequest->admin_note = $request->admin_note;
        $refundRequest->rejected_at =  now();
        $message = 'Refund request rejected.';


        $refundRequest->save();

        $notificationRepo = app(AdminNotificationRepositoryInterface::class);

        $userId = $refundRequest->user_id;
        $note = $request->admin_note ? " Reason: {$request->admin_note}" : "";

        $notificationRepo->notifyRecipients(
            $refundRequest->id,
            ChatRefundRequest::class,
            "Refund Rejected",
            "Refund request #{$refundRequest->id} has been rejected.{$note}",
            [
                ['type' => 'admin', 'id' => 1]
            ]
        );
        $notificationRepo->notifyRecipients(
            $refundRequest->id,
            ChatRefundRequest::class,
            "Refund Request Rejected",
            "Your refund request has been rejected.{$note}",
            [
                ['type' => 'user', 'id' => $userId]
            ]
        );


        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function getRefundData($id)
    {
        $refundRequest = ChatRefundRequest::with(['user', 'chatPayment'])->findOrFail($id);

        $joiningFee = UserPayment::where('user_id', $refundRequest->user_id)
            ->where('type', 'joining_fee')
            ->whereNotNull('paid_at')
            ->sum('amount');

        return response()->json([
            'joining_fee' => $joiningFee,
            'expert_fee' => $refundRequest->requested_amount,
            'has_subscription' => UserSubscription::where('user_id', $refundRequest->user_id)
                ->where('active', true)
                ->exists()
        ]);
    }
}
