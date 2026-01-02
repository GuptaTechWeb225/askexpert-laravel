<?php

namespace App\Http\Controllers\Restaurant\Customer;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\RestaurantRepositoryInterface;
use App\Enums\ViewPaths\Restaurant\Customer;
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
use App\Models\RestaurantWaitlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;

class CustomerController extends BaseController
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
        return $this->getListView($request);
    }

    public function getListView(Request $request): View|RedirectResponse
    {
        $restaurantId = auth('restaurant')->id();


        if (!\App\Utils\Helpers::addon_permission_check('Customer Analytics', $restaurantId)) {
            Toastr::error(translate('Access Denied: Your plan has no customer Analytics') . '!');
            return back();
        }
        $search = $request->input('searchValue');

        $transactions = RestaurantBillPayment::with('customer')
            ->where('restaurant_id', $restaurantId)
            ->orderBy('created_at', 'desc')
            ->get();

        $uniqueCustomers = $transactions->pluck('customer')->unique('id');

        if ($search) {
            $uniqueCustomers = $uniqueCustomers->filter(function ($customer) use ($search) {
                return str_contains(strtolower($customer->f_name), strtolower($search))
                    || str_contains(strtolower($customer->l_name), strtolower($search))
                    || str_contains(strtolower($customer->email), strtolower($search))
                    || str_contains(strtolower($customer->phone), strtolower($search));
            })->values();
        }

        $uniqueCustomers->map(function ($customer) use ($transactions) {
            $customerTransactions = $transactions->where('customer_id', $customer->id);

            $customer->visits = $customerTransactions->count();
            $customer->points_earned = $customerTransactions->sum('coins_earned');
            $customer->points_redeemed = $customerTransactions->sum('coins_used');
            $customer->last_visit = $customerTransactions->max('created_at');

            return $customer;
        });

        $totalCustomers = $uniqueCustomers->count();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentPageItems = $uniqueCustomers->slice(($page - 1) * $perPage, $perPage)->values();
        $customers = new LengthAwarePaginator(
            $currentPageItems,
            $uniqueCustomers->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view(Customer::LIST[VIEW], [
            'customers' => $customers,
            'totalCustomers' => $totalCustomers,
        ]);
    }


    public function getView(Request $request, $id): View|RedirectResponse
    {
        $restaurantId = auth('restaurant')->id(); // सिर्फ auth restaurant

        $customer = $this->customerRepo->getFirstWhere(
            params: ['id' => $id],
        );

        if (!$customer) {
            Toastr::error(translate('customer_Not_Found'));
            return back();
        }

        // सभी orders केवल auth restaurant के लिए
        $orders = RestaurantBillPayment::with('restaurant')
            ->where('customer_id', $customer->id)
            ->where('restaurant_id', $restaurantId) // सिर्फ auth restaurant
            ->latest()
            ->paginate(getWebConfig(name: WebConfigKey::PAGINATION_LIMIT));

        $latestOrder = $orders->first();

        // सभी orders केवल auth restaurant के लिए
        $allOrders = RestaurantBillPayment::where('customer_id', $customer->id)
            ->where('restaurant_id', $restaurantId) // सिर्फ auth restaurant
            ->get();

        $restaurantVisit = $allOrders->pluck('restaurant_id')->unique()->count();
        $repeatVisit = $allOrders->pluck('restaurant_id')->count() - $restaurantVisit;

        $tableBookings = RestaurantWaitlist::where('user_id', $customer->id)
            ->where('restaurant_id', $restaurantId)
            ->where('status', 'booked')
            ->count();

        $bookingCancelled = RestaurantWaitlist::where('user_id', $customer->id)
            ->where('restaurant_id', $restaurantId)
            ->where('status', 'cancelled')
            ->count();

        $orderStatusArray = [
            'restaurant_visit'  => $restaurantVisit,
            'total_reward'      => $allOrders->sum('coins_earned'),
            'total_redemption'  => $allOrders->sum('coins_used'),
            'table_bookings'    => $tableBookings,
            'repeat_visit'      => $repeatVisit,
            'booking_cancelled' => $bookingCancelled,
        ];


        return view(Customer::VIEW[VIEW], compact(
            'customer',
            'orders',       // Paginated orders
            'latestOrder',  // Latest order ऊपर दिखाने के लिए
            'orderStatusArray'
        ));
    }


    public function export(Request $request)
    {
        $restaurantId = auth('restaurant')->id();

        $transactions = RestaurantBillPayment::with('customer')
            ->where('restaurant_id', $restaurantId)
            ->orderBy('created_at', 'desc')
            ->get();

        $uniqueCustomers = $transactions->pluck('customer')->unique('id');

        $exportData = $uniqueCustomers->map(function ($customer) use ($transactions) {
            $customerTransactions = $transactions->where('customer_id', $customer->id);

            return [
                'Name'            => $customer->f_name . ' ' . $customer->l_name,
                'Email'           => $customer->email,
                'Phone'           => $customer->phone,
                'Total Visits'    => $customerTransactions->count(),
                'Points Earned'   => $customerTransactions->sum('coins_earned'),
                'Points Redeemed' => $customerTransactions->sum('coins_used'),
                'Last Visit'      => optional($customerTransactions->max('created_at'))->format('d M Y, h:i A'),
                'Status'          => $customer->is_active ? 'Active' : 'Inactive',
            ];
        });

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers_export.csv"',
        ];

        $callback = function () use ($exportData) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_keys($exportData->first() ?? []));

            foreach ($exportData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}
