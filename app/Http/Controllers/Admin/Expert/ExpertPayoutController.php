<?php

namespace App\Http\Controllers\Admin\Expert;


use App\Http\Controllers\Controller;
use App\Contracts\Repositories\ExpertRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Enums\WebConfigKey;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Traits\EmailTemplateTrait;
use App\Traits\PaginatorTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB; // <-- yeh line add karo
use App\Models\ExpertCategory;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Events\CustomerStatusUpdateEvent;
use App\Models\ExpertEarning;
use App\Models\Expert;
use App\Models\UserPayment;
use App\Models\UserSubscription;
use App\Models\ChatPayment;
use App\Models\ChatSession;

class ExpertPayoutController extends Controller
{

    use PaginatorTrait, EmailTemplateTrait;

    public function __construct(
        private readonly ExpertRepositoryInterface        $expertRepo,
        private readonly CustomerRepositoryInterface        $customerRepo,
    ) {}
    public function index(Request|null $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->expertPayouts($request);
    }

    public function expertPayouts(Request $request)
    {
        $query = Expert::with(['category', 'earnings'])
            ->whereHas('earnings');

        // Filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->whereHas('earnings', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('f_name', 'like', "%{$request->search}%")
                    ->orWhere('l_name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $experts = $query->paginate(10);

        $completedEarnings = ExpertEarning::with('chat.payment')->get();

        // Total expert ko payout bana (paid + pending dono)
        $totalPaid = $completedEarnings->sum('total_amount');
        $pendingPayouts    = $completedEarnings->where('status', 'pending')->sum('total_amount');
        $totalPaidToExperts = $completedEarnings->where('status', 'paid')->sum('total_amount');

        // 2. Sirf un chats ka user payment (expert_fee) jinke liye ExpertEarning bana hai
        $consultationRevenueFromCompletedChats = $completedEarnings
            ->whereNotNull('chat.payment') // safety
            ->sum(function ($earning) {
                return $earning->chat->payment->expert_fee ?? 0;
            });

        $joiningFees = UserPayment::where('type', 'joining_fee')
            ->whereNotNull('paid_at')
            ->sum('amount');
        $chatPayment = ChatSession::where('payment_status', 'paid')
            ->sum('total_charged');

        $membershipFees = UserSubscription::whereNotNull('user_id')
            ->sum('monthly_fee');


        $totalRevenue = $joiningFees + $membershipFees + $chatPayment;

        $platformCommission = $totalRevenue - $totalPaid;

        $platformCommission = max(0, $platformCommission);

        $categories = ExpertCategory::active()->get();

        return view('admin-views.expert-payout.index', compact(
            'experts',
            'categories',
            'pendingPayouts',
            'totalPaid',
            'platformCommission',
            'totalRevenue'
        ));
    }

    public function viewExpertPayout($expertId)
    {
        $expert = Expert::with(['earnings.chat.firstMessage', 'category'])->findOrFail($expertId);

        $totalEarnings = $expert->earnings()->sum('total_amount');
        $paidAmount = $expert->earnings()->where('status', 'paid')->sum('total_amount');
        $pendingAmount = $expert->earnings()->where('status', 'pending')->sum('total_amount');
        $totalSessions = $expert->earnings()->count();

        return response()->json([
            'view' => view('admin-views.expert-payout.partials.view-modal', compact(
                'expert',
                'totalEarnings',
                'paidAmount',
                'pendingAmount',
                'totalSessions'
            ))->render()
        ]);
    }

    public function setupPayout($expertId, Request $request)
    {
        $expert = Expert::findOrFail($expertId);

        $earnings = $expert->earnings()
            ->with(['chat.firstMessage', 'category'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('admin-views.expert-payout.setup', compact('expert', 'earnings'));
    }

  public function payEarnings(Request $request)
{
    $request->validate([
        'earning_ids' => 'required|array',
        'earning_ids.*' => 'exists:expert_earnings,id',
        'message' => 'nullable|string|max:1000'
    ]);

    $earnings = ExpertEarning::whereIn('id', $request->earning_ids)
        ->where('status', 'pending')
        ->get();

    // ❗ FIRST empty check
    if ($earnings->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No valid pending earnings found.'
        ]);
    }

    $totalAmount = $earnings->sum('total_amount');

    // ✅ Safe now
    $expertId = $earnings->first()->expert_id;
        // -----------------------------
    // Mark earnings as paid
    // -----------------------------
    ExpertEarning::whereIn('id', $request->earning_ids)->update([
        'status' => 'paid',
        'paid_at' => now(),
        'note' => $request->message ?? 'Paid by admin'
    ]);

    // 🔔 Notification repo
    $notificationRepo = app(\App\Contracts\Repositories\AdminNotificationRepositoryInterface::class);

    // -----------------------------
    // 1️⃣ Admin Notification
    // -----------------------------
    $notificationRepo->notifyRecipients(
        $earnings->first()->id,
        ExpertEarning::class,
        "Expert Payout Processed",
        "A payout of \${$totalAmount} has been processed for Expert ID #{$expertId}.",
        [
            ['type' => 'admin', 'id' => 1]
        ]
    );

    // -----------------------------
    // 2️⃣ Expert Notification
    // -----------------------------
    $notificationRepo->notifyRecipients(
        $earnings->first()->id,
        ExpertEarning::class,
        "Payout Received",
        "Your payout of \${$totalAmount} has been processed successfully.",
        [
            ['type' => 'expert', 'id' => $expertId]
        ]
    );

    return response()->json([
        'success' => true,
        'message' => "Payment of \${$totalAmount} processed successfully for {$earnings->count()} session(s).",
        'total' => $totalAmount
    ]);
}

}
