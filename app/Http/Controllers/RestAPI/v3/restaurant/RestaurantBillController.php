<?php

namespace App\Http\Controllers\RestAPI\v3\restaurant;

use App\Enums\WebConfigKey;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Illuminate\Support\Str;
use App\Models\RestaurantBill;
use App\Models\Restaurant;
use App\Models\RestaurantCoupon;
use App\Models\RestaurantSetting;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;
use App\Services\BillPaymentService;
use App\Models\RestaurantNotification;
use App\Models\RestaurantWaitlist;
use App\Traits\PushNotificationTrait;

class RestaurantBillController extends Controller
{
    use  PushNotificationTrait;

    public function generateBill(Request $request): JsonResponse
    {
        $request->validate([
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'restaurant_name' => 'required|string',
            'customer_name' => 'required|string',
            'bill_amount' => 'required|numeric|min:0',
        ]);

        try {
            $restaurant = Restaurant::find($request->restaurant_id);

            if ($restaurant->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'You can’t generate a bill until admin approves your restaurant.',
                ], 403);
            }
            $billUuid = (string) Str::uuid();
            $bill = RestaurantBill::create([
                'restaurant_id' => $request->restaurant_id,
                'restaurant_name' => $request->restaurant_name,
                'customer_name' => $request->customer_name,
                'bill_amount' => $request->bill_amount,
                'expired_at' => now()->addMinutes(30),
                'uuid' => $billUuid,
            ]);

            $qrData = route('api.billDetails', ['uuid' => $billUuid]);
            $qrPath = 'qrcodes/' . $billUuid . '.svg';

            $qrCode = QrCode::create($qrData)->setSize(300);
            $writer = new SvgWriter();
            $result = $writer->write($qrCode);

            Storage::disk('public')->put($qrPath, $result->getString());

            if (!Storage::disk('public')->exists($qrPath)) {
                throw new \Exception('Failed to generate QR code: File not created at ' . $qrPath);
            }

            $bill->update([
                'qr_code' => $qrPath,
            ]);

            return response()->json([
                'bill_id' => $bill->id,
                'bill_amount' => $bill->bill_amount,
                'qr_code'     => asset('storage/qrcodes/' . basename($bill->qr_code)),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate bill or QR code.',
                'message' => $e->getMessage() ?: 'Unknown error occurred.',
            ], 500);
        }
    }

    public function billDetails($uuid)
    {
        $bill = RestaurantBill::where('uuid', $uuid)->firstOrFail();

        if ($bill->expired_at && now()->greaterThan($bill->expired_at)) {
            $bill->update(['status' => 'failed']);
        }

        $coupons = RestaurantCoupon::where('restaurant_id', $bill->restaurant_id)
            ->where('status', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        $settings = RestaurantSetting::forRestaurant($bill->restaurant_id)
            ->active()
            ->first();

        return response()->json([
            'restaurant_name' => $bill->restaurant_name,
            'restaurant_id' => $bill->restaurant_id,
            'customer_name'   => $bill->customer_name,
            'amount'          => $bill->bill_amount,
            'status'          => $bill->status,
            'uuid'            => $bill->uuid,
            'expired_at'      => $bill->expired_at,
            'coupons'         => $coupons,
            'settings'        => $settings,
        ]);
    }



    public function payBill(Request $request, $uuid)
    {
        $bill = RestaurantBill::where('uuid', $uuid)->first();

        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found',
            ], 404);
        }

        $settings = RestaurantSetting::forRestaurant($bill->restaurant_id)->active()->first();

        $coupon = null;
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $id = $user->id;

        if ($request->has('coupon_code')) {
            $coupon = RestaurantCoupon::where('restaurant_id', $bill->restaurant_id)
                ->where('coupon_code', $request->coupon_code)
                ->first();
        }

        $coinsUsed = $request->input('coins_used', 0);

        $service = new BillPaymentService($bill, $settings, $coupon, $id);

        try {
            $payment = $service->pay($coinsUsed);

            // Restaurant Notification
            RestaurantNotification::create([
                'restaurant_id' => $bill->restaurant_id,
                'title'         => 'New Bill Paid',
                'message'       => 'A New bill has been paid successfully. Final Amount: ' . (string)$payment->final_amount,
                'is_read'       => 0,
            ]);

            // Firebase Notifications
            $restaurant = Restaurant::find($bill->restaurant_id);
            if ($restaurant && !empty($restaurant->cm_firebase_token)) {
                $this->sendPushNotification(
                    $restaurant->cm_firebase_token,
                    "💰 New Bill Paid",
                    "Customer {$user->name} paid {$payment->final_amount}. Total Bill: {$payment->original_amount}"
                );
            }

            if (!empty($user->cm_firebase_token)) {
                $this->sendPushNotification(
                    $user->cm_firebase_token,
                    "✅ Bill Paid Successfully",
                    "You earned {$payment->coins_earned} coins for this transaction."
                );
            }

        
            $allUserTables = RestaurantWaitlist::where('restaurant_id', $bill->restaurant_id)
                ->where('user_id', $user->id)
                ->whereIn('status', ['booked', 'seated'])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($allUserTables->isNotEmpty()) {
                $lastTable = $allUserTables->first(); // ✅ last booked/seated table
                $lastTableSize = $lastTable->table_size;

                // Mark last table as finished
                $lastTable->status = 'finished';
                $lastTable->save();

                // Cancel all other tables for this user
                foreach ($allUserTables->skip(1) as $table) {
                    $table->status = 'cancelled';
                    $table->save();
                }

                // Handle next waiting users for this table size
                $waitingUsers = RestaurantWaitlist::where('restaurant_id', $bill->restaurant_id)
                    ->where('table_size', $lastTableSize)
                    ->whereIn('status', ['waiting', 'notified'])
                    ->orderBy('position')
                    ->get();

                $restaurantObj = Restaurant::find($bill->restaurant_id);
                $turnoverTime = $restaurantObj->tables
                    ->where('table_size', $lastTableSize)
                    ->first()
                    ->avg_turnover_time ?? 15;

                $currentTime = now();

                foreach ($waitingUsers as $index => $waiter) {
                    if ($index === 0) {
                        // First waiting user → notified
                        $waiter->status = 'notified';
                        $waiter->expected_time = $currentTime->copy()->addMinutes(15);
                        $waiter->save();

                        if (!empty($waiter->user->cm_firebase_token)) {
                            $this->sendPushNotification(
                                $waiter->user->cm_firebase_token,
                                "Table Available ✅",
                                "A table is now available for you at {$restaurantObj->restaurant_name}. Please arrive within 15 minutes."
                            );
                        }

                        $currentTime = $waiter->expected_time->copy();
                    } else {
                        // Remaining waiting users → shift expected_time
                        $waiter->expected_time = $currentTime->copy()->addMinutes($turnoverTime);
                        $waiter->save();
                        $currentTime = $waiter->expected_time->copy();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bill paid successfully',
                'payment' => $payment,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }





    public function getBills($id): JsonResponse
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            Log::warning('Restaurant not found while fetching bills', [
                'restaurant_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No restaurant exist on this id.',
            ], 404);
        }

        try {
            Log::info('Restaurant found for bills', [
                'restaurant_id'   => $restaurant->id,
                'restaurant_name' => $restaurant->name ?? null,
            ]);

            $bills = RestaurantBill::where('restaurant_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($bills->isEmpty()) {
                Log::info('No bills found for restaurant', [
                    'restaurant_id' => $id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'No bills found for this restaurant.',
                    'data'    => [],
                ]);
            }

            Log::info('Bills fetched successfully', [
                'restaurant_id' => $id,
                'count'         => $bills->count(),
            ]);

            return response()->json([
                'success' => true,
                'count'   => $bills->count(),
                'data'    => $bills,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching bills', [
                'restaurant_id' => $id,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bills.',
            ], 500);
        }
    }
}
