<?php

namespace App\Services;

use App\Models\RestaurantBill;
use App\Models\RestaurantSetting;
use App\Models\RestaurantCoupon;
use App\Models\RestaurantBillPayment;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class BillPaymentService
{
    protected $bill;
    protected $settings;
    protected $coupon;
    protected $customerId;

    public function __construct(RestaurantBill $bill, RestaurantSetting $settings, ?RestaurantCoupon $coupon = null, ?int $customerId = null)
    {
        $this->bill = $bill;
        $this->settings = $settings;
        $this->coupon = $coupon;
        $this->customerId = $customerId;
    }

    /**
     * Validate bill against restaurant settings and coupon
     */
    public function validate($coinsUsed = 0)
    {
        // common checks (bill status, expired, etc.)
        if ($this->bill->status === 'failed' || $this->bill->status === 'paid') {
            throw ValidationException::withMessages(['bill' => 'Bill is already closed or failed.']);
        }

        if ($this->bill->expired_at && now()->greaterThan($this->bill->expired_at)) {
            throw ValidationException::withMessages(['bill' => 'Bill has expired.']);
        }

        // mode decide karna
        if ($this->coupon) {
            $this->validateCouponMode($coinsUsed);
        } elseif ($coinsUsed > 0) {
            $this->validateCoinMode($coinsUsed);
        }
    }

    protected function validateCouponMode($coinsUsed)
    {
        // ✅ only coupon rules
        if ($this->coupon->status != 1) {
            throw ValidationException::withMessages(['coupon' => 'Coupon is not active.']);
        }

        if (now()->lt($this->coupon->start_date) || now()->gt($this->coupon->end_date)) {
            throw ValidationException::withMessages(['coupon' => 'Coupon is not valid right now.']);
        }

        if ($this->bill->bill_amount < $this->coupon->minimum_purchase) {
            throw ValidationException::withMessages(['coupon' => "Minimum purchase required is ₹{$this->coupon->minimum_purchase}."]);
        }

        $usedCount = RestaurantBillPayment::where('customer_id', $this->customerId)
            ->where('coupon_id', $this->coupon->id)
            ->count();

        if ($usedCount >= $this->coupon->limit_for_same_user) {
            throw ValidationException::withMessages(['coupon' => 'You have already used this coupon maximum allowed times.']);
        }

        if ($this->customerId) {
            $user = User::find($this->customerId);
            if (!$user) {
                throw ValidationException::withMessages(['customer' => 'Customer not found.']);
            }

            $loyaltyPoints = $user->loyalty_point;

            if ($loyaltyPoints < $this->coupon->min_point_require) {
                throw ValidationException::withMessages([
                    'coupon' => "You must have at least {$this->coupon->min_point_require} loyalty points to use this coupon."
                ]);
            }
        }
    }


    protected function validateCoinMode($coinsUsed)
    {
        // ✅ only restaurant setting rules
        if ($this->settings->min_order_active && $this->bill->bill_amount < $this->settings->min_order_amount) {
            throw ValidationException::withMessages(['amount' => "Minimum order amount is ₹{$this->settings->min_order_amount}."]);
        }

        $maxAllowed = ($this->bill->bill_amount * $this->settings->max_coin_usage_percent) / 100;
        $redeemValue = $this->settings->calculateRedeemValue($coinsUsed);

        if ($redeemValue > $maxAllowed) {
            throw ValidationException::withMessages(['coins' => "You can only use coins worth up to ₹{$maxAllowed} on this bill."]);
        }

        if ($this->customerId) {
            $today = now()->toDateString();
            $month = now()->format('Y-m');

            $dailyRedeemed = RestaurantBillPayment::where('customer_id', $this->customerId)
                ->where('restaurant_id', $this->bill->restaurant_id)
                ->whereDate('created_at', $today)
                ->sum('coins_used');

            if ($this->settings->daily_redeem_limit && ($dailyRedeemed + $coinsUsed) > $this->settings->daily_redeem_limit) {
                throw ValidationException::withMessages(['coins' => "Daily redeem limit exceeded. Max allowed: {$this->settings->daily_redeem_limit} coins"]);
            }

            $monthlyRedeemed = RestaurantBillPayment::where('customer_id', $this->customerId)
                ->where('restaurant_id', $this->bill->restaurant_id)
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
                ->sum('coins_used');

            if ($this->settings->monthly_redeem_limit && ($monthlyRedeemed + $coinsUsed) > $this->settings->monthly_redeem_limit) {
                throw ValidationException::withMessages(['coins' => "Monthly redeem limit exceeded. Max allowed: {$this->settings->monthly_redeem_limit} coins"]);
            }

            $dailyVisits = RestaurantBillPayment::where('customer_id', $this->customerId)
                ->where('restaurant_id', $this->bill->restaurant_id)
                ->whereDate('created_at', $today)
                ->count();

            if ($this->settings->daily_visit_limit && $dailyVisits >= $this->settings->daily_visit_limit) {
                throw ValidationException::withMessages(['visit' => "Daily visit limit reached. Max allowed: {$this->settings->daily_visit_limit} visits"]);
            }

            $user = User::find($this->customerId);
            if (!$user) throw ValidationException::withMessages(['customer' => 'Customer not found.']);

            if ($user->loyalty_point < $coinsUsed) {
                throw ValidationException::withMessages(['coins' => 'Not enough coins available.']);
            }
        }
    }


    /**
     * Apply coupon discount and calculate final amount
     */
    public function calculateFinalAmount($coinsUsed = 0)
    {
        $final = $this->bill->bill_amount;

        if ($this->coupon) {
            if ($this->coupon->discount_type === 'percent') {
                $final -= ($final * ($this->coupon->discount_amount / 100));
            } else {
                $final -= $this->coupon->discount_amount;
            }
        }

        if ($coinsUsed > 0) {
            $final -= $this->settings->calculateRedeemValue($coinsUsed);
        }

        return max($final, 0);
    }

    public function pay($coinsUsed = 0)
    {
        return DB::transaction(function () use ($coinsUsed) {
            $this->validate($coinsUsed);

            $finalAmount = $this->calculateFinalAmount($coinsUsed);

            $coinsEarned = $this->settings->calculateEarnedCoins($finalAmount);

            $user = User::find($this->customerId);
            Log::info('Fetched User: ', [$user]);

            Log::info('Customer ID in BillPaymentService pay(): ' . $this->customerId);

            if ($this->coupon && $this->coupon->min_point_require > 0) {
                $coinsToDeduct = $this->coupon->min_point_require;
            } else {
                $coinsToDeduct = $coinsUsed;
            }

            // Deduct from user loyalty points if > 0
            if ($coinsToDeduct > 0) {
                if ($user->loyalty_point < $coinsToDeduct) {
                    throw ValidationException::withMessages(['coins' => 'Not enough loyalty points.']);
                }
                $user->loyalty_point -= $coinsToDeduct;
            }

            if ($coinsEarned > 0) {
                $user->loyalty_point += $coinsEarned;
            }

            $user->save();

            $this->bill->update([
                'status' => 'paid',
                'paid_amount' => $finalAmount,
                'paid_at' => now(),
            ]);

            $payment = RestaurantBillPayment::create([
                'restaurant_id'   => $this->bill->restaurant_id,
                'customer_id'     => $this->customerId,
                'bill_uuid'       => $this->bill->uuid,
                'original_amount' => $this->bill->bill_amount,
                'final_amount'    => $finalAmount,
                'coupon_id'       => $this->coupon?->id,
                'coupon_code'     => $this->coupon?->coupon_code,
                'coins_used'      => $coinsToDeduct,
                'coins_earned'    => $coinsEarned,
                'status'          => 'paid',
                'paid_at'         => now(),
            ]);

            return $payment;
        });
    }
}
