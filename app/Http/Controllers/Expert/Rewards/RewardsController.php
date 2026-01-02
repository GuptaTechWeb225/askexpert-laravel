<?php

namespace App\Http\Controllers\Restaurant\Rewards;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\RestaurantRepositoryInterface;
use App\Enums\ViewPaths\Restaurant\Rewards;
use App\Enums\ExportFileNames\Admin\Customer as CustomerExport;
use App\Enums\WebConfigKey;
use App\Http\Controllers\BaseController;
use App\Traits\EmailTemplateTrait;
use App\Traits\PaginatorTrait;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\RestaurantBillPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class RewardsController extends BaseController
{
    use PaginatorTrait, EmailTemplateTrait;

    public function __construct(
        private readonly CustomerRepositoryInterface        $customerRepo,
        private readonly RestaurantRepositoryInterface        $restaurantRepo,
    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View|RedirectResponse
    {
        return $this->getView($request);
    }

    public function getView(Request $request): View|RedirectResponse
    {
        $restaurantId = auth('restaurant')->id();
        if (!\App\Utils\Helpers::addon_permission_check('Reward Performance', $restaurantId)) {
            Toastr::error(translate('Access Denied: Your plan has no Reward Analytics') . '!');
            return back();
        }
        $transactions = RestaurantBillPayment::with('customer')
            ->where('restaurant_id', $restaurantId)
            ->orderBy('paid_at', 'desc') // paid_at ka use
            ->get();

        $allBillAmount = $transactions->sum('original_amount');
        $totalBillAmount = $transactions->sum('final_amount');
        $rewardValue = $allBillAmount - $totalBillAmount;
        $totalTransactions = $transactions->count();
        $avgBillValue = $totalTransactions > 0 ? $totalBillAmount / $totalTransactions : 0;

        // Helper function to get top 5 rewards for a given date range
        $getTopRewards = function ($from, $to) use ($restaurantId) {
            return RestaurantBillPayment::select(
                'coupon_code',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('SUM(final_amount) as total_amount')
            )
                ->where('restaurant_id', $restaurantId)
                ->whereNotNull('coupon_code')
                ->whereBetween('paid_at', [$from, $to]) // paid_at column
                ->groupBy('coupon_code')
                ->orderByDesc('usage_count')
                ->limit(5)
                ->get();
        };

        $now = Carbon::now();

        // Current week, month, year ranges
        $topRewardsWeek = $getTopRewards($now->copy()->startOfWeek(), $now->copy()->endOfWeek());
        $topRewardsMonth = $getTopRewards($now->copy()->startOfMonth(), $now->copy()->endOfMonth());
        $topRewardsYear = $getTopRewards($now->copy()->startOfYear(), $now->copy()->endOfYear());

        // For monthly chart example
        $from = $now->copy()->startOfYear()->format('Y-m-d');
        $to = $now->copy()->endOfYear()->format('Y-m-d');

        $range = range(1, 12);
        $label = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        $couponsUsed = RestaurantBillPayment::where('restaurant_id', $restaurantId)
            ->whereBetween('paid_at', [$from, $to])
            ->whereNotNull('coupon_id')
            ->get();

        $couponStats = $couponsUsed->groupBy('coupon_id')->map(function ($group) {
            return [
                'code' => $group->first()->coupon_code ?? 'Unknown',
                'count' => $group->count(),
            ];
        })->sortByDesc('count');

        $topCoupons = $couponStats->take(3);
        $otherCount = $couponStats->slice(3)->sum('count');

        $userData = $topCoupons->pluck('count')->toArray();
        $userData[] = $otherCount;

        $userLabels = $topCoupons->pluck('code')->toArray();
        $userLabels[] = 'Other';

        // Daily coupon/coins used
        $startDate = $now->copy()->subDays(30)->startOfDay();
        $rewardData = RestaurantBillPayment::select(
            DB::raw('DAY(paid_at) as day'),
            DB::raw('SUM(CASE WHEN coupon_code IS NOT NULL THEN 1 ELSE 0 END) as coupon_used'),
            DB::raw('SUM(CASE WHEN coupon_code IS NULL THEN 1 ELSE 0 END) as coins_used')
        )
            ->where('restaurant_id', $restaurantId)
            ->where('paid_at', '>=', $startDate)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->get();

        $rewardDays = $rewardData->pluck('day')->toArray();
        $couponUsed = $rewardData->pluck('coupon_used')->toArray();
        $coinsUsed  = $rewardData->pluck('coins_used')->toArray();

        $dailyBills = RestaurantBillPayment::selectRaw('DATE(paid_at) as date, SUM(original_amount) as total')
            ->where('restaurant_id', $restaurantId)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $dates = $dailyBills->pluck('date')->map(fn($date) => Carbon::parse($date)->format('d M'));
        $amounts = $dailyBills->pluck('total');


        $transactionsWithCoupon = $transactions->whereNotNull('coupon_id');

        // Group by coupon
        $couponStateTable = $transactionsWithCoupon->groupBy('coupon_id')->map(function ($group) use ($totalBillAmount) {
            $totalAmount = $group->sum('original_amount');
            $usageCount = $group->count();
            $avgBillValue = $usageCount > 0 ? $totalAmount / $usageCount : 0;
            $usagePercentage = $totalBillAmount > 0 ? ($totalAmount / $totalBillAmount) * 100 : 0;

            return [
                'coupon_code' => $group->first()->coupon_code ?? 'Unknown',
                'usage_count' => $usageCount,
                'total_amount' => $totalAmount,
                'avg_bill_value' => round($avgBillValue, 2),
                'usage_percentage' => round($usagePercentage, 2)
            ];
        })->sortByDesc('usage_count')->values();
        return view(Rewards::VIEW[VIEW], [
            'transactions' => $transactions,
            'totalTransactions' => $totalTransactions,
            'totalBillAmount' => $totalBillAmount,
            'rewardValue' => $rewardValue,
            'avgBillValue' => $avgBillValue,
            'couponStateTable' => $couponStateTable,
            'userData' => $userData,
            'userLabels' => $userLabels,
            'label' => $label,
            'range' => $range,
            'rewardDays' => $rewardDays,
            'couponUsed' => $couponUsed,
            'coinsUsed' => $coinsUsed,
            'dates' => $dates,
            'amounts' => $amounts,
            'topRewardsWeek' => $topRewardsWeek,
            'topRewardsMonth' => $topRewardsMonth,
            'topRewardsYear' => $topRewardsYear,
        ]);
    }
}
