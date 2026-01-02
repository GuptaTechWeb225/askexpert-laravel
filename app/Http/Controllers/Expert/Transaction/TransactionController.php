<?php

namespace App\Http\Controllers\Restaurant\Transaction;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\RestaurantRepositoryInterface;
use App\Enums\ViewPaths\Restaurant\Transaction;
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
use Illuminate\Support\Facades\Response;

class TransactionController extends BaseController
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
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View
    {
        $restaurantId = auth('restaurant')->id();
        $search = $request->input('searchValue');
        $perPage = getWebConfig(name: WebConfigKey::PAGINATION_LIMIT) ?? 10;

        $query = RestaurantBillPayment::with('customer')
            ->where('restaurant_id', $restaurantId)
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('f_name', 'like', "%{$search}%")
                    ->orWhere('l_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Paginate transactions
        $transactions = $query->paginate($perPage)->withQueryString();

        // Aggregate values for all transactions (without pagination)
        $allTransactions = RestaurantBillPayment::where('restaurant_id', $restaurantId)->get();
        $totalBillAmount = $allTransactions->sum('final_amount');
        $totalTransactions = $allTransactions->count();
        $avgBillValue = $totalTransactions > 0 ? $totalBillAmount / $totalTransactions : 0;
        $pointsAwarded = $allTransactions->sum('coins_earned');
        $pointsRedeemed = $allTransactions->sum('coins_used');

        return view(Transaction::LIST[VIEW], [
            'transactions' => $transactions,
            'totalTransactions' => $totalTransactions,
            'totalBillAmount' => $totalBillAmount,
            'avgBillValue' => $avgBillValue,
            'pointsAwarded' => $pointsAwarded,
            'pointsRedeemed' => $pointsRedeemed
        ]);
    }

    public function export(Request $request)
    {
        $restaurantId = auth('restaurant')->id();
        $search = $request->input('searchValue');

        $transactions = RestaurantBillPayment::with('customer')
            ->where('restaurant_id', $restaurantId)
            ->orderBy('created_at', 'desc')
            ->get();


        $exportData = $transactions->map(function ($t) {
            return [
                'Customer Name'  => $t->customer?->f_name . ' ' . $t->customer?->l_name,
                'Customer Email' => $t->customer?->email,
                'Customer Phone' => $t->customer?->phone,
                'Original Amount' => $t->original_amount,
                'Final Amount'   => $t->final_amount,
                'Coins Used'     => $t->coins_used,
                'Coins Earned'   => $t->coins_earned,
                'Coupon Code'    => $t->coupon_code ?? 'No Coupon',
                'Date'           => $t->created_at->format('d M Y'),
                'Status'         => $t->status,
            ];
        });

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="restaurant_transactions.csv"',
        ];

        $callback = function () use ($exportData) {
            $handle = fopen('php://output', 'w');

            if ($exportData->isNotEmpty()) {
                fputcsv($handle, array_keys($exportData->first())); // header row
            }

            foreach ($exportData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }


    public function getView(Request $request): View|RedirectResponse
    {
        $restaurantId = auth('restaurant')->id(); // सिर्फ auth restaurant
        if (!\App\Utils\Helpers::addon_permission_check('Transaction Analytics', $restaurantId)) {
            Toastr::error(translate('Access Denied: Your plan has no Transaction Analytics') . '!');
            return back();
        }

        $perPage = getWebConfig(name: WebConfigKey::PAGINATION_LIMIT) ?? 10;

        // Base query
        $query = RestaurantBillPayment::with('customer')
            ->where('restaurant_id', $restaurantId)
            ->orderBy('created_at', 'desc');

        // Pagination ke liye
        $transactions = $query->paginate($perPage)->withQueryString();

        $allTransactions = RestaurantBillPayment::where('restaurant_id', $restaurantId)->get();

        $totalBillAmount = $allTransactions->sum('final_amount');
        $totalTransactions = $allTransactions->count();
        $avgBillValue = $totalTransactions > 0 ? $totalBillAmount / $totalTransactions : 0;
        $pointsAwarded = $allTransactions->sum('coins_earned');
        $pointsRedeemed = $allTransactions->sum('coins_used');
        $uniqueCustomers = $allTransactions->pluck('customer')->unique('id');

        // Charts and stats logic same as before...
        $from = now()->startOfYear()->format('Y-m-d');
        $to = now()->endOfYear()->format('Y-m-d');

        $range = range(1, 12);
        $label = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        $couponsUsed = RestaurantBillPayment::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$from, $to])
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

        $startDate = Carbon::now()->subDays(30)->startOfDay();

        $rewardData = RestaurantBillPayment::select(
            DB::raw('DAY(created_at) as day'),
            DB::raw('SUM(CASE WHEN coupon_code IS NOT NULL THEN 1 ELSE 0 END) as coupon_used'),
            DB::raw('SUM(CASE WHEN coupon_code IS NULL THEN 1 ELSE 0 END) as coins_used')
        )
            ->where('restaurant_id', $restaurantId)
            ->where('created_at', '>=', $startDate)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->get();

        $rewardDays = $rewardData->pluck('day')->toArray();
        $couponUsed = $rewardData->pluck('coupon_used')->toArray();
        $coinsUsed  = $rewardData->pluck('coins_used')->toArray();

        $dailyBills = RestaurantBillPayment::selectRaw('DATE(created_at) as date, SUM(original_amount) as total')
            ->where('restaurant_id', $restaurantId)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $dates = $dailyBills->pluck('date')->map(fn($date) => Carbon::parse($date)->format('d M'));
        $amounts = $dailyBills->pluck('total');

        return view(Transaction::VIEW[VIEW], [
            'transactions' => $transactions, // paginated
            'totalTransactions' => $totalTransactions, // full count
            'totalBillAmount' => $totalBillAmount,
            'avgBillValue' => $avgBillValue,
            'pointsAwarded' => $pointsAwarded,
            'pointsRedeemed' => $pointsRedeemed,
            'userData' => $userData,
            'userLabels' => $userLabels,
            'label' => $label,
            'range' => $range,
            'rewardDays' => $rewardDays,
            'couponUsed' => $couponUsed,
            'coinsUsed' => $coinsUsed,
            'dates' => $dates,
            'amounts' => $amounts
        ]);
    }
}
