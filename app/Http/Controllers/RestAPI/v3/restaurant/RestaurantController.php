<?php

namespace App\Http\Controllers\RestAPI\v3\restaurant;

use App\Events\VendorRegistrationEvent;
use App\Http\Controllers\Controller;
use App\Models\RestaurantSetting;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Restaurant;
use App\Models\RestaurantBillPayment;
use App\Models\RestaurantCoupon;
use App\Models\Plan;
use App\Models\RestaurantWaitlist;
use App\Models\RestaurantPlan;
use App\Models\PlanSuggestionLog;
use Carbon\Carbon;


class RestaurantController extends Controller
{

    public function remove_account( $id): JsonResponse
    {
        $restaurant = Restaurant::find($id);
        if (isset($restaurant)) {
            $restaurant->delete();
        } else {
            return response()->json(['status_code' => 404, 'message' => translate('Not found')], 200);
        }
        return response()->json(['status_code' => 200, 'message' => translate('Successfully deleted')], 200);
    }

    public function billTransactions($id): JsonResponse
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {


            return response()->json([
                'success' => false,
                'message' => 'You are not registered',
            ], 404);
        }
        try {



            $transactions = RestaurantBillPayment::where('restaurant_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($transactions->isEmpty()) {

                return response()->json([
                    'success' => true,
                    'message' => 'No transactions found for this restaurant.',
                    'data'    => [],
                ]);
            }



            return response()->json([
                'success' => true,
                'count'   => $transactions->count(),
                'data'    => $transactions,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching transactions', [
                'restaurant_id' => $id,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions.',
            ], 500);
        }
    }



    public function getPlansForRestaurant($id)
    {
        Log::info('Fetching plans for restaurant', ['restaurant_id' => $id]);

        $restaurantPlan = RestaurantPlan::with('plan')
            ->where('restaurant_id', $id)
            ->active()
            ->first();

        Log::info('Restaurant current plan fetched', ['plan' => $restaurantPlan]);

        $membershipPlans = Plan::where('status', 1)
            ->where('type', 'membership')
            ->get();

        Log::info('Membership plans fetched', ['count' => $membershipPlans->count()]);

        $boostPlans = Plan::where('status', 1)
            ->where('type', 'boost')
            ->get();

        Log::info('Boost plans fetched', ['count' => $boostPlans->count()]);

        return response()->json([
            'current_plan'      => $restaurantPlan ? $restaurantPlan->plan : null,
            'membership_plans'  => $membershipPlans,
            'boost_plans'       => $boostPlans,
        ]);
    }

    private function shouldShowPopup($restaurantId)
    {
        $todayCount = PlanSuggestionLog::where('restaurant_id', $restaurantId)
            ->whereDate('shown_at', now()->toDateString())
            ->count();

        return $todayCount < 200;
    }

    public function getUpgradeSuggestion($restaurantId)
    {
        $restaurantPlan = RestaurantPlan::with('plan')
            ->active()
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$restaurantPlan) {
            return Plan::where('plan_type', 'free')->first();
        }

        $currentPlan = $restaurantPlan->plan;

        return Plan::where('price', '>', $currentPlan->price)
            ->orderBy('price', 'asc')
            ->first();
    }

    public function showSuggestion($restaurantId)
    {

        if (!$this->shouldShowPopup($restaurantId)) {


            return response()->json(['show_popup' => false, 'message' => 'Daily limit reached']);
        }

        $nextPlan = $this->getUpgradeSuggestion($restaurantId);

        if (!$nextPlan) {

            return response()->json(['show_popup' => false, 'message' => 'No higher plan available']);
        }

        $log = PlanSuggestionLog::create([
            'restaurant_id' => $restaurantId,
            'plan_id' => $nextPlan->id,
            'shown_at' => now(),
        ]);

        Log::info("Plan suggestion popup shown", [
            'restaurant_id' => $restaurantId,
            'plan_id' => $nextPlan->id,
            'shown_at' => $log->shown_at->toDateTimeString(),
        ]);

        return response()->json([
            'show_popup' => true,
            'suggested_plan' => $nextPlan,
            'shown_at' => $log->shown_at->toDateTimeString(),
        ]);
    }


    public function getWaitlist($id)
    {

        $today = Carbon::today();

        $waitlistQuery = RestaurantWaitlist::with('user:id,name,phone')
            ->where('restaurant_id', $id)
            ->whereIn('status', ['booked', 'waiting']) // ✅ only booked & waiting
            ->whereDate('created_at', $today)          // ✅ only today's entries
            ->orderByRaw("FIELD(status, 'waiting', 'booked')") // ✅ waiting first, then booked
            ->orderBy('position', 'asc')               // ✅ inside each group by position
            ->get();



        $waitlist = $waitlistQuery->map(function ($item) {
            return [
                'party_size'          => $item->party_size,
                'position'            => $item->position,
                'estimated_wait_time' => $item->estimated_wait_time,
                'status'              => $item->status,
                'expected_time'       => $item->expected_time
                    ? Carbon::parse($item->expected_time)->format('d M Y h:i A')
                    : null,
                'booked_at'           => $item->created_at
                    ? Carbon::parse($item->created_at)->format('d M Y h:i A')
                    : null,
                'user_name'           => $item->user->name ?? null,
                'user_phone'          => $item->user->phone ?? null,
            ];
        });


        return response()->json([
            'status'  => true,
            'message' => 'Waitlist fetched successfully.',
            'data'    => $waitlist,
        ], 200);
    }



    public function cancelWaitlist($id)
    {
        $entry = RestaurantWaitlist::find($id);

        if (!$entry) {
            return response()->json([
                'status' => false,
                'message' => 'Waitlist entry not found.'
            ], 404);
        }

        $entry->update(['status' => 'cancelled']);

        RestaurantWaitlist::where('restaurant_id', $entry->restaurant_id)
            ->where('status', 'waiting')
            ->where('position', '>', $entry->position)
            ->decrement('position');

        return response()->json([
            'status' => true,
            'message' => 'Waitlist entry cancelled and positions updated.'
        ], 200);
    }

    public function getRestaurantAnalytics($restaurantId)
    {

        try {
            $today = now();
            $startOfWeek = $today->copy()->startOfWeek();
            $endOfWeek = $today->copy()->endOfWeek();

            $weeklyRevenueRaw = RestaurantBillPayment::where('restaurant_id', $restaurantId)
                ->where('status', 'paid')
                ->whereBetween('paid_at', [$startOfWeek, $endOfWeek])
                ->selectRaw('DAYNAME(paid_at) as day, SUM(final_amount) as total')
                ->groupBy('day')
                ->pluck('total', 'day');

            $daysOfWeek = [
                'Monday'    => 0,
                'Tuesday'   => 0,
                'Wednesday' => 0,
                'Thursday'  => 0,
                'Friday'    => 0,
                'Saturday'  => 0,
                'Sunday'    => 0,
            ];

            $weeklyRevenue = array_merge($daysOfWeek, $weeklyRevenueRaw->toArray());

            // 2. Total revenue
            $totalRevenue = RestaurantBillPayment::where('restaurant_id', $restaurantId)
                ->where('status', 'paid')
                ->sum('final_amount');

            // 3. Avg order amount
            $avgOrderAmount = RestaurantBillPayment::where('restaurant_id', $restaurantId)
                ->where('status', 'paid')
                ->avg('final_amount');

            // 4. Growth % (compare this month vs last month)
            $thisMonthRevenue = RestaurantBillPayment::where('restaurant_id', $restaurantId)
                ->where('status', 'paid')
                ->whereMonth('paid_at', $today->month)
                ->whereYear('paid_at', $today->year)
                ->sum('final_amount');

            $lastMonthRevenue = RestaurantBillPayment::where('restaurant_id', $restaurantId)
                ->where('status', 'paid')
                ->whereMonth('paid_at', $today->copy()->subMonth()->month)
                ->whereYear('paid_at', $today->copy()->subMonth()->year)
                ->sum('final_amount');

            $growthPercent = $lastMonthRevenue > 0
                ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2)
                : 100;

            $growthPercentWithSign = ($growthPercent >= 0 ? '+' : '') . number_format($growthPercent, 2);


            // 5. Customer visits (unique customers)
            $totalVisits = RestaurantBillPayment::where('restaurant_id', $restaurantId)
                ->where('status', 'paid')
                ->distinct('customer_id')
                ->count('customer_id');

            // 6. New customers this month
            $newCustomers = RestaurantBillPayment::where('restaurant_id', $restaurantId)
                ->where('status', 'paid')
                ->whereMonth('paid_at', $today->month)
                ->whereYear('paid_at', $today->year)
                ->distinct('customer_id')
                ->count('customer_id');

            // 7. Popular coupons (top 5)
            $popularCoupons = RestaurantBillPayment::where('restaurant_id', $restaurantId)
                ->where('status', 'paid')
                ->whereNotNull('coupon_code')
                ->selectRaw('coupon_code, COUNT(*) as usage_count')
                ->groupBy('coupon_code')
                ->orderByDesc('usage_count')
                ->limit(5)
                ->get();

            // 8. Peak hours (hourly user count)
            $peakHours = RestaurantBillPayment::where('restaurant_id', $restaurantId)
                ->where('status', 'paid')
                ->selectRaw('HOUR(paid_at) as hour, COUNT(DISTINCT customer_id) as users')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Analytics fetched successfully.',
                'data' => [
                    'weekly_revenue'   => $weeklyRevenue,
                    'total_revenue'    => $totalRevenue,
                    'avg_order_amount' => round($avgOrderAmount, 2),
                    'growth_percent'   => $growthPercentWithSign,
                    'customer_visits'  => $totalVisits,
                    'new_customers'    => $newCustomers,
                    'popular_coupons'  => $popularCoupons,
                    'peak_hours'       => $peakHours,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error("❌ Restaurant Analytics Fetch Failed", [
                'restaurant_id' => $restaurantId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch analytics.',
            ], 500);
        }
    }
}
