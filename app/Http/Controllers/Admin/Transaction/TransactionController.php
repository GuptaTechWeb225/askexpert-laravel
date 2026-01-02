<?php

namespace App\Http\Controllers\Admin\Transaction;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\ExportFileNames\Admin\Report;
use App\Enums\ViewPaths\Admin\Transaction;
use App\Enums\WebConfigKey;
use App\Exports\RefundTransactionReportExport;
use App\Http\Controllers\BaseController;
use App\Services\RefundTransactionService;
use App\Traits\PdfGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\View as PdfView;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\RestaurantBillPayment;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class TransactionController extends BaseController
{
    use PdfGenerator;
    public function __construct(
        private readonly RefundTransactionService $refundTransactionService,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    ) {}
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getListView($request);
    }
    public function getListView(Request $request): View
    {

        $perPage = getWebConfig(name: WebConfigKey::PAGINATION_LIMIT);
        $ordersQuery = RestaurantBillPayment::with('restaurant')->orderBy('created_at', 'ASC');

        if ($request->filled('searchValue')) {
            $search = $request->input('searchValue');
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                    ->orWhere('final_amount', 'like', "%$search%")
                    ->orWhere('coupon_code', 'like', "%$search%")
                    ->orWhereHas('restaurant', function ($q2) use ($search) {
                        $q2->where('restaurant_name', 'like', "%$search%");
                    });
            });
        }

        if ($request->filled('transaction_date')) {
            $dates = explode(' - ', $request->input('transaction_date'));
            if (count($dates) == 2) {
                $ordersQuery->whereBetween('created_at', [
                    Carbon::parse($dates[0])->startOfDay(),
                    Carbon::parse($dates[1])->endOfDay()
                ]);
            }
        }

        if ($request->filled('choose_first')) {
            $ordersQuery->take(intval($request->input('choose_first')));
        }

        $orders = $ordersQuery->paginate($perPage);
        $totalCount = $orders->total();
        return view(Transaction::INDEX[VIEW], compact(
            'orders',
            'totalCount'
        ));
    }
    public function getAnalyticsView(Request $request): View
    {
        // Pichle 30 din ka data uthana
        $startDate = Carbon::now()->subDays(30)->startOfDay();

        $transactions = RestaurantBillPayment::select(
            DB::raw('DAY(created_at) as day'),
            DB::raw('COUNT(id) as total_transactions'),
            DB::raw('SUM(final_amount) as total_amount')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->get();

        // chart ke liye arrays banane
        $dates = $transactions->pluck('day')->toArray();   // sirf din ki value (1,2,3...)
        $amounts = $transactions->pluck('total_amount')->toArray();
        $rewardData = RestaurantBillPayment::select(
            DB::raw('DAY(created_at) as day'),
            DB::raw('SUM(CASE WHEN coupon_code IS NOT NULL THEN 1 ELSE 0 END) as coupon_used'),
            DB::raw('SUM(CASE WHEN coupon_code IS NULL THEN 1 ELSE 0 END) as coins_used')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->get();

        $perPage = getWebConfig(name: WebConfigKey::PAGINATION_LIMIT);
        $ordersQuery = RestaurantBillPayment::with('restaurant')->orderBy('created_at', 'ASC');

        if ($request->filled('searchValue')) {
            $search = $request->input('searchValue');
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                    ->orWhere('final_amount', 'like', "%$search%")
                    ->orWhere('coupon_code', 'like', "%$search%")
                    ->orWhereHas('restaurant', function ($q2) use ($search) {
                        $q2->where('restaurant_name', 'like', "%$search%");
                    });
            });
        }

        $orders = $ordersQuery->paginate($perPage);
        $totalCount = $orders->total();
        $rewardDays = $rewardData->pluck('day')->toArray();
        $couponUsed = $rewardData->pluck('coupon_used')->toArray();
        $coinsUsed = $rewardData->pluck('coins_used')->toArray();
        return view(Transaction::ANALYTICS_VIEW[VIEW], compact(
            'dates',
            'amounts',
            'rewardDays',
            'couponUsed',
            'coinsUsed',
            'orders',
            'totalCount'
        ));
    }


    public function export(Request $request): StreamedResponse
    {
        $query = RestaurantBillPayment::with('restaurant')->orderBy('created_at', 'ASC');

        if ($request->filled('searchValue')) {
            $search = $request->input('searchValue');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                    ->orWhere('final_amount', 'like', "%$search%")
                    ->orWhere('coupon_code', 'like', "%$search%")
                    ->orWhereHas('restaurant', function ($q2) use ($search) {
                        $q2->where('restaurant_name', 'like', "%$search%");
                    });
            });
        }

        if ($request->filled('transaction_date')) {
            $dates = explode(' - ', $request->input('transaction_date'));
            if (count($dates) == 2) {
                $query->whereBetween('created_at', [
                    Carbon::parse($dates[0])->startOfDay(),
                    Carbon::parse($dates[1])->endOfDay()
                ]);
            }
        }

        if ($request->filled('choose_first')) {
            $query->take((int)$request->input('choose_first'));
        }

        $transactions = $query->get();

        $filename = "transactions_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'ID',
            'Restaurant Name',
            'Original Amount',
            'Final Amount',
            'Coins Used',
            'Coins Earned',
            'Coupon Code',
            'Date',
            'Status'
        ];

        $callback = function () use ($transactions, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->id,
                    $t->restaurant?->restaurant_name ?? 'N/A',
                    number_format($t->original_amount, 2),
                    number_format($t->final_amount, 2),
                    $t->coins_used,
                    $t->coins_earned,
                    $t->coupon_code ?? 'No Coupon Used',
                    $t->created_at->format('d M Y'),
                    ucfirst($t->status),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
