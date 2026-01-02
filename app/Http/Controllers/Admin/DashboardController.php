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
use App\Models\ChatSession;
use App\Models\Expert;
use App\Models\UserPayment;
use App\Models\ExpertCategory;
use App\Models\ChatRefundRequest;
use App\Models\UserSubscription;
use Spatie\Activitylog\Models\Activity;


class DashboardController extends BaseController
{
    public function __construct(
        private readonly AdminWalletRepositoryInterface      $adminWalletRepo,
        private readonly DashboardService                    $dashboardService,
    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->dashboard();
    }

    public function dashboard(): View
    {
        $totalQuestions = ChatSession::whereDate('started_at', today())->count();
        $pendingQuestions = ChatSession::where('status', 'pending')->count();
        $refundRequests = ChatRefundRequest::count();

        $joiningFees = UserPayment::where('type', 'joining_fee')
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', today())
            ->sum('amount');
        $chatPayment = ChatSession::where('payment_status', 'paid')
            ->whereDate('started_at', today())
            ->sum('total_charged');

        $membershipFees = UserSubscription::whereNotNull('user_id')
            ->whereDate('created_at', today())
            ->sum('monthly_fee');


        $revenueToday = $joiningFees + $membershipFees + $chatPayment;

        $experts = Expert::with('category')
            ->when(request('category'), function ($q) {
                $q->where('category_id', request('category'));
            })
            ->when(request()->filled('status'), function ($q) {
                $q->where('is_online', request('status'));
            })
            ->when(request('searchValue'), function ($q) {
                $search = request('searchValue');

                $q->where(function ($sub) use ($search) {
                    $sub->where('f_name', 'like', "%{$search}%")
                        ->orWhere('l_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);


        $categories = ExpertCategory::all();
        $activities = Activity::latest()
        ->take(5)
        ->get();

        return view(Dashboard::VIEW[VIEW], compact(
            'totalQuestions',
            'pendingQuestions',
            'refundRequests',
            'categories',
            'revenueToday',
            'experts',
            'activities',
        ));
    }


    public function graphData(Request $request)
    {
        $filter = $request->filter ?? 'year';

        switch ($filter) {
            case 'week':
                $start = now()->startOfWeek();
                $end   = now()->endOfWeek();
                $periods = 7;
                break;

            case 'month':
                $start = now()->startOfMonth();
                $end   = now()->endOfMonth();
                $periods = 4;
                break;

            default: // year
                $start = now()->startOfYear();
                $end   = now()->endOfYear();
                $periods = 12;
        }

        /**
         * 🔹 Joining Fees
         */
        $joiningFees = UserPayment::where('type', 'joining_fee')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw($this->groupBySql('paid_at', $filter))
            ->groupBy('period')
            ->pluck('total', 'period');

        /**
         * 🔹 Membership Fees
         */
        $membershipFees = UserSubscription::whereNotNull('user_id')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw($this->groupBySql('created_at', $filter, 'monthly_fee'))
            ->groupBy('period')
            ->pluck('total', 'period');

        /**
         * 🔹 Chat Revenue
         */
        $chatRevenue = ChatSession::where('payment_status', 'paid')
            ->whereBetween('started_at', [$start, $end])
            ->selectRaw($this->groupBySql('started_at', $filter, 'total_charged'))
            ->groupBy('period')
            ->pluck('total', 'period');

        /**
         * 🔹 Merge all revenue
         */
        $data = [];
        for ($i = 1; $i <= $periods; $i++) {
            $data[] =
                ($joiningFees[$i] ?? 0) +
                ($membershipFees[$i] ?? 0) +
                ($chatRevenue[$i] ?? 0);
        }

        return response()->json([
            'data' => $data,
            'categories' => range(1, $periods)
        ]);
    }


    private function groupBySql($column, $filter, $amountColumn = 'amount')
    {
        return match ($filter) {
            'week'  => "DAYOFWEEK($column) as period, SUM($amountColumn) as total",
            'month' => "WEEK($column, 1) - WEEK(DATE_SUB($column, INTERVAL DAYOFMONTH($column)-1 DAY), 1) + 1 as period, SUM($amountColumn) as total",
            default => "MONTH($column) as period, SUM($amountColumn) as total",
        };
    }
}
