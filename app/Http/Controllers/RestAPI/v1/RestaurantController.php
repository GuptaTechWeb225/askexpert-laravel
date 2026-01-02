<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\Restaurant;
use App\Models\Shop;
use App\Traits\InHouseTrait;
use App\Utils\Helpers;
use App\Utils\ProductManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use App\Models\RestaurantReview;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\RestaurantNotification;
use App\Models\RestaurantWaitlist;
use App\Traits\PushNotificationTrait;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RestaurantController extends Controller
{
    use InHouseTrait, PushNotificationTrait;

    public function __construct(
        private Restaurant       $restaurant,
    ) {}



    public function getRestaurantList(Request $request)
    {
        $latitude  = $request->get('latitude');
        $longitude = $request->get('longitude');

        // 1️⃣ Boosted Restaurants
        $boosted = Restaurant::where('is_active', 1)
            ->withAvg('reviews', 'rating')
            ->withSum('billPayments as total_coins_used', 'coins_used')
            ->withSum('billPayments as total_coins_earned', 'coins_earned')
            ->where('boost', 1)
            ->get()
            ->filter(function ($restaurant) use ($latitude, $longitude) {
                if ($latitude && $longitude) {
                    $distance = 6371 * acos(
                        cos(deg2rad($latitude)) * cos(deg2rad($restaurant->latitude)) *
                            cos(deg2rad($restaurant->longitude) - deg2rad($longitude)) +
                            sin(deg2rad($latitude)) * sin(deg2rad($restaurant->latitude))
                    );
                    return $distance <= 30;
                }
                return true;
            })
            ->map(function ($restaurant) {
                $restaurant->reviews_avg_rating = round((float) ($restaurant->reviews_avg_rating ?? 0), 1);
                $restaurant->boost_score =
                    ($restaurant->reviews_avg_rating * 0.5) +
                    (($restaurant->total_coins_used ?? 0) * 0.25) +
                    (($restaurant->total_coins_earned ?? 0) * 0.25);
                $restaurant->is_boosted = true;
                return $restaurant;
            })
            ->sortByDesc('boost_score')
            ->take(100);

        // 2️⃣ Normal Restaurants
        $normal = Restaurant::where('is_active', 1)
            ->withAvg('reviews', 'rating')
            ->where('boost', 0) // non-boosted only
            ->get()
            ->map(function ($restaurant) {
                $restaurant->reviews_avg_rating = round((float) ($restaurant->reviews_avg_rating ?? 0), 1);
                $restaurant->is_boosted = false;
                return $restaurant;
            })
            ->sortByDesc('reviews_avg_rating');

        // 3️⃣ Merge dono list
        $restaurants = $boosted->merge($normal)->values();

        return response()->json([
            'status'  => true,
            'message' => 'Restaurants fetched successfully.',
            'data'    => $restaurants
        ], 200);
    }


    public function getRestaurantDetails($id)
    {
        $restaurant = Restaurant::with(['coupons', 'reviews.customer', 'tables'])->find($id);

        if (!$restaurant) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found.'
            ], 404);
        }

        $reviews = $restaurant->reviews->map(function ($review) {
            $imageName = $review->customer->image ?? null;
            return [
                'rating'   => $review->rating,
                'message'  => $review->message,
                'customer' => [
                    'id'    => $review->customer->id ?? null,
                    'name'  => $review->customer->name ?? 'Buio',
                    'image' => $imageName
                        ? asset('storage/profile/' . $imageName)
                        : asset('back-end/img/placeholder/placeholder-1-1.png'),
                ],
                'created_at' => $review->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Restaurant details fetched successfully.',
            'data' => [
                'restaurant'      => $restaurant,
                'coupons'         => $restaurant->coupons,
                'reviews'         => $reviews,
                'average_rating'  => round($restaurant->reviews->avg('rating'), 1),
                'total_reviews'   => $restaurant->reviews->count(),
                'tables'          => $restaurant->tables,
            ]
        ], 200);
    }


    public function storeReview(Request $request)
    {
        Log::info('Request Data:', $request->all());
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'customer_id'   => 'required|exists:users,id',
            'rating'        => 'required|integer|min:1|max:5',
            'message'       => 'nullable|string|max:1000',
        ], [
            'restaurant_id.required' => translate('Restaurant id is required!'),
            'customer_id.required' => translate('Customer id is required!'),
            'rating.required' => translate('Please give any rating '),
        ]);
        if ($validator->fails()) {
            Log::warning('Review validation failed', [
                'input' => $request->all(),
                'errors' => $validator->errors()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }
        $review = RestaurantReview::create([
            'restaurant_id' => $request->restaurant_id,
            'customer_id'   => $request->customer_id,
            'rating'        => $request->rating,
            'message'       => $request->message,
        ]);

        RestaurantNotification::create([
            'restaurant_id' => $request->restaurant_id,
            'title' => 'New Review Submitted',
            'message' => 'A customer submitted a rating of ' . $request->rating,
        ]);
        $restaurant = Restaurant::find($request->restaurant_id);
        if ($restaurant && !empty($restaurant->cm_firebase_token)) {
            $title = "⭐ New Review Received";
            $body  = "You received a {$request->rating}-star review. " .
                (!empty($request->message) ? "Review: {$request->message}" : "");

            $this->sendPushNotification(
                $restaurant->cm_firebase_token,
                $title,
                $body
            );
        }
        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully.',
            'data'    => $review,
        ], 201);
    }
    public function joinWaitlist(Request $request, $restaurantId)
    {
        Log::info("🎯 Join Waitlist Request Received", [
            'restaurant_id' => $restaurantId,
            'payload'       => $request->all()
        ]);


        $request->validate([
            'user_id'    => 'required|integer',
            'party_size' => 'required|integer|min:1',
        ]);

        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User does not exist.'
            ], 404);
        }

        $restaurant = Restaurant::with('tables')->find($restaurantId);
        if (!$restaurant) {
            return response()->json([
                'status'  => false,
                'message' => 'Restaurant not found.'
            ], 404);
        }

        if (!$this->isRestaurantOpen($restaurant)) {
            return response()->json([
                'status'  => false,
                'message' => 'Restaurant is closed for the day.'
            ], 400);
        }

        $partySize = $request->party_size;

        // ✅ exact table group only (no >= allowed)
        $tableGroup = $restaurant->tables
            ->where('table_size', $partySize)
            ->first();

        if (!$tableGroup) {
            return response()->json([
                'status'  => false,
                'message' => 'No suitable tables available for this party size.'
            ], 400);
        }

        $turnoverTime = $tableGroup->avg_turnover_time ?? 15;

        // ✅ check active bookings in this exact table group
        $bookedCount = RestaurantWaitlist::where('restaurant_id', $restaurantId)
            ->where('status', 'booked')
            ->where('table_size', $tableGroup->table_size)
            ->where('expires_at', '>', now())
            ->count();

        $availableTableCount = max(0, $tableGroup->table_count - $bookedCount);

        if ($availableTableCount > 0) {
            // 🎉 Direct booking → arrival window 15 minutes
            $arrivalDeadline = now()->addMinutes(15);

            $waitlist = RestaurantWaitlist::create([
                'restaurant_id' => $restaurantId,
                'user_id'       => $user->id,
                'party_size'    => $partySize,
                'table_size'    => $tableGroup->table_size,
                'position'      => 1,
                'status'        => 'booked',
                'expires_at'    => $arrivalDeadline,
                'expected_time' => $arrivalDeadline
            ]);

            // ✅ Format expected_time
            $formattedTime = $waitlist->expires_at->format('j M Y g:i A');
            $restaurantPhone = preg_replace('/\D/', '', $restaurant->phone);
            $phoneLink = "tel:" . $restaurantPhone;

            $customMessage = "✅ Your table is booked at {$restaurant->restaurant_name}, please arrive before {$formattedTime}.\n
                          If you have any query or face any problem, please call on {$phoneLink}.\nThank you!";
            if (!empty($user->cm_firebase_token)) {
                $this->sendPushNotification(
                    $user->cm_firebase_token,
                    "Table Booked ✅",
                    "Your table is reserved at {$restaurant->restaurant_name}, arrive before {$formattedTime}"
                );
            }

            if (!empty($restaurant->cm_firebase_token)) {
                $this->sendPushNotification(
                    $restaurant->cm_firebase_token,
                    "New Booking 📥",
                    "A table for {$user->name} ({$partySize} people) is booked. Arrival before {$formattedTime}"
                );
            }

            return response()->json([
                'status'  => true,
                'message' => "✅ Your table is booked at {$restaurant->restaurant_name}, please arrive before {$formattedTime}. Thank you!",
                'data'    => [
                    'message'       => $customMessage,
                    'id'            => $waitlist->id,
                    'status'        => $waitlist->status,
                    'expires_at'    => $formattedTime,
                    'expected_time' => $formattedTime,
                ]
            ]);
        }


        // ⚡ all tables full → add to waiting
        $lastBooking = RestaurantWaitlist::where('restaurant_id', $restaurantId)
            ->where('table_size', $tableGroup->table_size) // ✅ exact table group only
            ->whereIn('status', ['booked', 'waiting'])
            ->orderByDesc(DB::raw("COALESCE(expires_at, expected_time)"))
            ->first();

        if ($lastBooking) {
            $lastTime = $lastBooking->expires_at ?? $lastBooking->expected_time;
            $expectedTime = Carbon::parse($lastTime)->addMinutes($turnoverTime);
        } else {
            $expectedTime = now()->addMinutes($turnoverTime);
        }

        $estimatedWaitTime = now()->diffInMinutes($expectedTime, false);

        $partiesAhead = RestaurantWaitlist::where('restaurant_id', $restaurantId)
            ->where('table_size', $tableGroup->table_size)
            ->where('status', 'waiting')
            ->count();

        $waitlist = RestaurantWaitlist::create([
            'restaurant_id'        => $restaurantId,
            'user_id'              => $user->id,
            'party_size'           => $partySize,
            'table_size'           => $tableGroup->table_size,
            'position'             => $partiesAhead + 1,
            'status'               => 'waiting',
            'estimated_wait_time'  => max(0, $estimatedWaitTime),
            'expected_time'        => $expectedTime,
            'expires_at'           => $expectedTime
        ]);
        if (!empty($user->cm_firebase_token)) {
            $this->sendPushNotification(
                $user->cm_firebase_token,
                "Added to Waitlist ⏳",
                "You are added to the waitlist at {$restaurant->restaurant_name}. Estimated wait {$waitlist->estimated_wait_time} min."
            );
        }

        if (!empty($restaurant->cm_firebase_token)) {
            $this->sendPushNotification(
                $restaurant->cm_firebase_token,
                "New Waitlist Entry 👥",
                "{$user->name} ({$partySize} people) joined the waitlist. Position: {$waitlist->position}"
            );
        }
        return response()->json([
            'status'  => true,
            'message' => '⏳ Added to waitlist.',
            'data'    => [
                'message' => "You are added to waitlist: estimated wait time :{$waitlist->estimated_wait_time} Min, expected time : {$waitlist->expected_time->format('j M Y g:i A')} , Please arrive on time",
                'id'                  => $waitlist->id,
                'position'            => $waitlist->position,
                'status'              => $waitlist->status,
                'party_size'          => $waitlist->party_size,
                'estimated_wait_time' => $waitlist->estimated_wait_time,
                'expected_time' => $waitlist->expected_time->format('j M Y g:i A'),
            ]
        ]);
    }




  private function isRestaurantOpen($restaurant): bool
{
    $day = strtolower(Carbon::now()->format('D')); // mon, tue, ...
    $fromField = $day . '_restaurant_hours_from';
    $toField   = $day . '_restaurant_hours_to';

    $openTime  = $restaurant->$fromField;
    $closeTime = $restaurant->$toField;

    if (!$openTime || !$closeTime) {
        return false; // working hours not set → closed
    }

    try {
        $now   = Carbon::now();
        // Carbon can parse both "09:00" (24h) and "09:00 AM" (12h)
        $open  = Carbon::parse($now->format('Y-m-d') . ' ' . $openTime);
        $close = Carbon::parse($now->format('Y-m-d') . ' ' . $closeTime);

        return $now->between($open, $close);
    } catch (\Exception $e) {
        Log::error("⚠️ Could not parse restaurant hours", [
            'restaurant_id' => $restaurant->id,
            'from' => $openTime,
            'to'   => $closeTime,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}
    public function getWaitlist($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $waitlist = RestaurantWaitlist::with('restaurant')
            ->where('user_id', $userId)
            ->latest()
            ->first();

        if (!$waitlist) {
            return response()->json([
                'status' => false,
                'message' => 'No waitlist or booking found for this user.'
            ], 404);
        }

        $responseData = [
            'waitlist_id' => $waitlist->id,
            'status' => $waitlist->status,
            'party_size' => $waitlist->party_size,
            'position' => $waitlist->position,
            'restaurant' => [
                'id' => $waitlist->restaurant->id,
                'name' => $waitlist->restaurant->restaurant_name,
                'address' => $waitlist->restaurant->address ?? null,
            ]
        ];

        if ($waitlist->status === 'waiting') {
            $remainingTime = max(0, $waitlist->expected_time->diffInMinutes(now(), false) * -1);
            $responseData['estimated_wait_time'] = $waitlist->estimated_wait_time;
            $responseData['expected_time'] = $waitlist->expected_time->toDateTimeString();
            $responseData['remaining_wait_time'] = $remainingTime;
        } elseif ($waitlist->status === 'booked') {
            $responseData['arrival_time'] = $waitlist->expires_at
                ? $waitlist->expires_at->toDateTimeString()
                : null;
        } elseif ($waitlist->status === 'cancelled') {
            $responseData['message'] = 'Your booking has been cancelled.';
        }

        return response()->json([
            'status' => true,
            'message' => 'Waitlist status fetched successfully.',
            'data' => $responseData
        ], 200);
    }



    public function cancelWaitlist(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'waitlist_id' => 'required|integer|exists:restaurant_waitlists,id',
        ]);
        $user = User::find($request->user_id);

        $waitlist = RestaurantWaitlist::where('id', $request->waitlist_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$waitlist) {
            return response()->json([
                'status' => false,
                'message' => 'No matching waitlist found for this user.'
            ], 404);
        }

        if ($waitlist->status === 'cancelled') {
            return response()->json([
                'status' => false,
                'message' => 'This waitlist is already cancelled.'
            ], 400);
        }

        $waitlist->status = 'cancelled';
        $waitlist->save();
        if ($user->cm_firebase_token) {
            $this->sendPushNotification(
                $user->cm_firebase_token,
                "Table Cancelled",
                "Your table has been cancelled successfully",
            );
        }

        if ($waitlist->restaurant && !empty($waitlist->restaurant->cm_firebase_token)) {
            $title = "❌ Table Cancelled";
            $body  = "Customer {$user->name} cancelled their booking for {$waitlist->party_size} people.";
            $this->sendPushNotification(
                $waitlist->restaurant->cm_firebase_token,
                $title,
                $body
            );
        }
        return response()->json([
            'status' => true,
            'message' => 'Your booking/waitlist has been cancelled successfully.',
            'data' => [
                'waitlist_id' => $waitlist->id,
                'status' => $waitlist->status,
                'restaurant' => [
                    'id' => $waitlist->restaurant->id ?? null,
                    'name' => $waitlist->restaurant->restaurant_name ?? null,
                ]
            ]
        ], 200);
    }
}
