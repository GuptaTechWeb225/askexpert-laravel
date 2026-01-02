<?php

namespace App\Http\Controllers\Admin\Plan;


use App\Http\Controllers\Controller;
use App\Contracts\Repositories\PlanRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Enums\WebConfigKey;
use Illuminate\Http\Request;
use App\Enums\ViewPaths\Admin\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Traits\EmailTemplateTrait;
use App\Traits\PaginatorTrait;
use Brian2694\Toastr\Facades\Toastr;
use App\Services\RestaurantService;
use Illuminate\Support\Facades\DB; // <-- yeh line add karo
use App\Services\PlanService;
use App\Services\BoostPlanService;
use App\Http\Requests\PlanRequest;
use App\Http\Requests\BoostPlanRequest;
use App\Http\Requests\BoostPlanUpdateRequest;
use App\Http\Requests\PlanUpdateRequest;
use App\Models\PurchasedPlan;
use App\Models\RestaurantPlan;
use App\Contracts\Repositories\RestaurantRepositoryInterface;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\RestaurantPlansExport;
use Symfony\Component\HttpFoundation\StreamedResponse;



class PlanController extends Controller
{

    use PaginatorTrait, EmailTemplateTrait;

    public function __construct(
        private readonly PlanRepositoryInterface        $planRepo,
        private readonly PlanService        $planService,
        private readonly BoostPlanService        $boostPlanService,
        private readonly CustomerRepositoryInterface        $customerRepo,
        private readonly PurchasedPlan        $PurchasedPlan,
        private readonly RestaurantRepositoryInterface        $restaurantRepo,


    ) {}
    public function index(Request|null $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->membershipPlans($request);
    }

    public function membershipPlans(Request $request): View|RedirectResponse
    {
        $plans = $this->planRepo->getListWhere(
            filters: ['type' => 'membership'],
            orderBy: ['id' => 'desc'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );

        return view(Plan::MEMBER_PLAN[VIEW], [
            'plans' => $plans,
        ]);
    }
    public function getAnalyticsView(Request $request): View|RedirectResponse
    {
        $plans = $this->planRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );
        $restaurants = $this->restaurantRepo->getListWhereBetween(
            searchValue: $request['searchValue'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
            appends: $request->all(),
        );
        $dataLimit =  getWebConfig(name: WebConfigKey::PAGINATION_LIMIT);
        $planGraphData = $this->getPlanChartData();
        $paymentGraphData = $this->getPaymentStatistics();
        $searchValue = $request->get('searchValue');

        $purchasedPlans = PurchasedPlan::with(['restaurant', 'plan'])
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->whereHas('restaurant', function ($q) use ($searchValue) {
                    $q->where('restaurant_name', 'like', "%{$searchValue}%")
                        ->orWhere('owner_name', 'like', "%{$searchValue}%")
                        ->orWhere('phone', 'like', "%{$searchValue}%");
                });
            })
            ->paginate($dataLimit)
            ->appends($request->all());

        return view(Plan::PLAN_ANALYTICS[VIEW], compact('plans', 'restaurants', 'planGraphData', 'paymentGraphData', 'purchasedPlans'));
    }



    public function boostPlan(Request $request): View|RedirectResponse
    {
        // multiple filter ke liye
        $plans = $this->planRepo->getListWhere(
            filters: ['type' => 'boost'],
            orderBy: ['id' => 'desc'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );
        return view(Plan::BOOST_PLAN[VIEW], [
            'plans' => $plans,
        ]);
    }
    public function getPaymentStatistics(): array
    {
        // payment_method ke hisaab se count
        $paymentData = DB::table('purchased_plans')
            ->select('payment_method', DB::raw('COUNT(*) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();

        $totalTransactions = array_sum($paymentData);

        $series = [];
        $labels = [];
        foreach ($paymentData as $method => $count) {
            $series[] = $count; // chart ke liye values
            $labels[] = $method; // legend me label
        }

        return [
            'series' => $series,
            'labels' => $labels,
            'total' => $totalTransactions
        ];
    }


    public function getPlanChartData(): array
    {
        // ---- WEEK (Mon - Sun) ----
        $weekData = DB::table('purchased_plans')
            ->select(DB::raw('DAYNAME(created_at) as day_name'), DB::raw('SUM(plan_amount) as total'))
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->groupBy('day_name')
            ->pluck('total', 'day_name')
            ->toArray();

        $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $weekSeries = [];
        foreach ($weekDays as $day) {
            $weekSeries[] = $weekData[$day] ?? 0;
        }

        // ---- MONTH (Last 30 days) ----
        $monthData = DB::table('purchased_plans')
            ->select(DB::raw('DATE(created_at) as date_label'), DB::raw('SUM(plan_amount) as total'))
            ->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->groupBy('date_label')
            ->orderBy('date_label')
            ->pluck('total', 'date_label')
            ->toArray();

        $monthDates = [];
        $monthSeries = [];
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays(29 - $i)->toDateString();
            $monthDates[] = date('d', strtotime($date));
            $monthSeries[] = $monthData[$date] ?? 0;
        }

        // ---- YEAR (Jan - Dec) ----
        $yearData = DB::table('purchased_plans')
            ->select(DB::raw('MONTHNAME(created_at) as month_name'), DB::raw('SUM(plan_amount) as total'))
            ->whereYear('created_at', now()->year)
            ->groupBy('month_name')
            ->pluck('total', 'month_name')
            ->toArray();

        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $yearSeries = [];
        foreach ($months as $m) {
            $yearSeries[] = $yearData[$m] ?? 0;
        }

        return [
            'week' => [
                'series' => $weekSeries,
                'categories' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
            ],
            'month' => [
                'series' => $monthSeries,
                'categories' => $monthDates
            ],
            'year' => [
                'series' => $yearSeries,
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            ],
        ];
    }


    public function planRequests(Request $request): View|RedirectResponse
    {

        $datalimit = getWebConfig(name: WebConfigKey::PAGINATION_LIMIT);
        $plans = PurchasedPlan::with(['restaurant', 'plan'])
            ->when($request->filled('transaction_date'), function ($query) use ($request) {

                $dates = explode(' - ', $request->transaction_date);
                if (count($dates) === 2) {
                    $from = Carbon::parse($dates[0])->startOfDay();
                    $to   = Carbon::parse($dates[1])->endOfDay();
                    $query->whereBetween('created_at', [$from, $to]);
                }
            })
            ->when($request->filled('payment_status'), function ($query) use ($request) {
                $query->where('payment_status', $request->payment_status);
            })
            ->when($request->filled('choose_first'), function ($query) use ($request) {
                $query->take((int) $request->choose_first);
            })
            ->when($request->filled('searchValue'), function ($query) use ($request) {
                $search = $request->searchValue;
                $query->whereHas('restaurant', function ($q) use ($search) {
                    $q->where('restaurant_name', 'like', "%{$search}%")
                        ->orWhere('owner_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($datalimit)
            ->appends($request->all());

        return view(Plan::PLAN_REQUESTS[VIEW], compact('plans'));
    }



    public function planrequestExport(Request $request): StreamedResponse
    {
        $plans = PurchasedPlan::with(['restaurant', 'plan'])
            ->when($request->filled('transaction_date'), function ($query) use ($request) {
                $dates = explode(' - ', $request->transaction_date);
                if (count($dates) === 2) {
                    $from = Carbon::parse($dates[0])->startOfDay();
                    $to   = Carbon::parse($dates[1])->endOfDay();
                    $query->whereBetween('created_at', [$from, $to]);
                }
            })
            ->when($request->filled('payment_status'), function ($query) use ($request) {
                $query->where('payment_status', $request->payment_status);
            })
            ->when($request->filled('choose_first'), function ($query) use ($request) {
                $query->take((int) $request->choose_first);
            })
            ->when($request->filled('searchValue'), function ($query) use ($request) {
                $search = $request->searchValue;
                $query->whereHas('restaurant', function ($q) use ($search) {
                    $q->where('restaurant_name', 'like', "%{$search}%")
                        ->orWhere('owner_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $filename = "plan_requests_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Restaurant Name',
            'Owner Name',
            'Plan Name',
            'Plan Type',
            'Plan Code',
            'Price',
            'Transaction ID',
            'Payment Method',
            'Payment Status'
        ];

        $callback = function () use ($plans, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($plans as $plan) {
                fputcsv($file, [
                    $plan->restaurant->restaurant_name ?? 'N/A',
                    $plan->restaurant->owner_name ?? 'N/A',
                    $plan->plan->plan_name ?? 'N/A',
                    $plan->plan->type ?? 'N/A',
                    $plan->plan_code,
                    number_format($plan->plan_amount, 2),
                    $plan->transaction_id,
                    ucfirst($plan->payment_method),
                    ucfirst($plan->payment_status),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function restaurantPlans(Request $request): View|RedirectResponse
    {
        $datalimit = getWebConfig(name: WebConfigKey::PAGINATION_LIMIT);

        $plans = RestaurantPlan::with(['restaurant', 'plan'])
            // Purchase Date filter
            ->when($request->filled('purchase_date'), function ($query) use ($request) {
                $dates = explode(' - ', $request->purchase_date);
                if (count($dates) === 2) {
                    $from = Carbon::parse($dates[0])->startOfDay();
                    $to   = Carbon::parse($dates[1])->endOfDay();
                    $query->whereBetween('created_at', [$from, $to]);
                }
            })
            // Expiry Date filter
            ->when($request->filled('expiry_date'), function ($query) use ($request) {
                $dates = explode(' - ', $request->expiry_date);
                if (count($dates) === 2) {
                    $from = Carbon::parse($dates[0])->startOfDay();
                    $to   = Carbon::parse($dates[1])->endOfDay();
                    $query->whereBetween('expiry_date', [$from, $to]); // column hona chahiye
                }
            })
            // Plan Status filter
            ->when($request->filled('plan_status'), function ($query) use ($request) {
                $query->where('status', $request->plan_status);
            })
            // Choose first N
            ->when($request->filled('choose_first'), function ($query) use ($request) {
                $query->take((int) $request->choose_first);
            })
            // Search filter
            ->when($request->filled('searchValue'), function ($query) use ($request) {
                $search = $request->searchValue;
                $query->whereHas('restaurant', function ($q) use ($search) {
                    $q->where('restaurant_name', 'like', "%{$search}%")
                        ->orWhere('owner_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                })->orWhereHas('plan', function ($q) use ($search) {
                    $q->where('plan_name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($datalimit)
            ->appends($request->all());

        return view(Plan::RESTAURANT_PLANS[VIEW], compact('plans'));
    }



    public function membershipPlanStore(PlanRequest $request,): View|RedirectResponse
    {

        $planData = $this->planService->getPlanData((object) $request);

        $this->planRepo->add($planData);
        Toastr::success(translate('plan_created_successfully'));

        return redirect()->back();
    }
    public function boostPlanStore(BoostPlanRequest $request,): View|RedirectResponse
    {

        $planData = $this->boostPlanService->getPlanData((object) $request);

        $this->planRepo->add($planData);

        Toastr::success(translate('plan_created_successfully'));

        return redirect()->back();
    }

    public function membershipPlanUpdate(PlanUpdateRequest $request, $id): RedirectResponse
    {

        $plan = $this->planRepo->find($id);

        $planData = $this->planService->getPlanData(
            (object) $request,
            $plan->image
        );
        $this->planRepo->update($id, $planData);

        Toastr::success(translate('plan_updated_successfully'));
        return redirect()->back();
    }
    public function boostPlanUpdate(BoostPlanUpdateRequest $request, $id): RedirectResponse
    {

        $plan = $this->planRepo->find($id);

        $planData = $this->boostPlanService->getPlanData(
            (object) $request,
            $plan->image
        );
        $this->planRepo->update($id, $planData);

        Toastr::success(translate('plan_updated_successfully'));
        return redirect()->back();
    }


    public function updateMemberShipStatus(Request $request): JsonResponse
    {

        $this->planRepo->update(id: $request['id'], data: ['status' => $request->get('is_active', 0)]);

        return response()->json(['message' => translate('update_successfully')]);
    }
    public function updateBoostStatus(Request $request): JsonResponse
    {

        $this->planRepo->update(id: $request['id'], data: ['status' => $request->get('is_active', 0)]);

        return response()->json(['message' => translate('update_successfully')]);
    }
    public function updateRestaurantPStatus(Request $request): JsonResponse
    {

        $plan = RestaurantPlan::findOrFail($request->id);

        $plan->status = $request->status == 1 ? 'active' : 'inactive';
        $plan->save();

        return response()->json(['message' => translate('update_successfully')]);
    }


    public function deleteMemberPlan($id): RedirectResponse
    {
        $this->planRepo->delete(params: ['id' => $id]);
        Toastr::success(translate('Plan_deleted_successfully'));
        return back();
    }
    public function deleteBoostPlan($id): RedirectResponse
    {
        $this->planRepo->delete(params: ['id' => $id]);
        Toastr::success(translate('Plan_deleted_successfully'));
        return back();
    }


    public function exportRestaurantPlans(Request $request): BinaryFileResponse
    {
        $purchaseStartDate = '';
        $purchaseEndDate = '';
        if ($request->filled('purchase_date')) {
            $dates = explode(' - ', $request->purchase_date);
            $purchaseStartDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
            $purchaseEndDate   = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
        }

        $expiryStartDate = '';
        $expiryEndDate = '';
        if ($request->filled('expiry_date')) {
            $dates = explode(' - ', $request->expiry_date);
            $expiryStartDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
            $expiryEndDate   = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
        }

        $plans = RestaurantPlan::with(['restaurant', 'plan'])
            ->when(
                $purchaseStartDate && $purchaseEndDate,
                fn($q) =>
                $q->whereBetween('created_at', [$purchaseStartDate, $purchaseEndDate])
            )
            ->when(
                $expiryStartDate && $expiryEndDate,
                fn($q) =>
                $q->whereBetween('expiry_date', [$expiryStartDate, $expiryEndDate])
            )
            ->when(
                $request->filled('plan_status'),
                fn($q) =>
                $q->where('status', $request->plan_status)
            )
            ->when(
                $request->filled('choose_first'),
                fn($q) =>
                $q->take((int) $request->choose_first)
            )
            ->when($request->filled('searchValue'), function ($query) use ($request) {
                $search = $request->searchValue;
                $query->whereHas('restaurant', function ($q) use ($search) {
                    $q->where('restaurant_name', 'like', "%{$search}%")
                        ->orWhere('owner_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                })->orWhereHas('plan', function ($q) use ($search) {
                    $q->where('plan_name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $data = [
            'plans' => $plans,
            'searchValue' => $request->get('searchValue'),
            'purchaseStartDate' => $purchaseStartDate,
            'purchaseEndDate' => $purchaseEndDate,
            'expiryStartDate' => $expiryStartDate,
            'expiryEndDate' => $expiryEndDate,
            'planStatus' => $request->get('plan_status'),
            'chooseFirst' => $request->get('choose_first'),
        ];

        return Excel::download(new RestaurantPlansExport($data), 'restaurant-plans.xlsx');
    }
}
