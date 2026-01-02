<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RestaurantSetting extends Model
{
    use HasFactory;


    protected $fillable = [
        'restaurant_id',
        'coin_value',
        'min_order_amount',
        'min_order_active',
        'max_coin_usage_percent',
        'daily_redeem_limit',
        'daily_visit_limit',
        'monthly_redeem_limit',
        'earning_amount',
        'earning_coin',
        'status',
    ];

    /***********************
     *  Relationships
     ***********************/
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /***********************
     *  Scopes
     ***********************/

    // Active settings only
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active');
    }

    // Inactive settings
    public function scopeInactive(Builder $query)
    {
        return $query->where('status', 'inactive');
    }

    // Settings by restaurant
    public function scopeForRestaurant(Builder $query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    // Only those restaurants where min_order restriction is active
    public function scopeWithMinOrderRestriction(Builder $query)
    {
        return $query->where('min_order_active', 1);
    }

    // Where coin usage is allowed above given %
    public function scopeCoinUsageAbove(Builder $query, $percent)
    {
        return $query->where('max_coin_usage_percent', '>=', $percent);
    }

    // Where earning rule is "spend X to earn Y coins"
    public function scopeEarningRule(Builder $query, $amount, $coins)
    {
        return $query->where('earning_amount', $amount)
                     ->where('earning_coin', $coins);
    }

    /***********************
     *  Helper Functions
     ***********************/

    // कितने रुपये पर 1 coin मिलेगा (ratio निकालने के लिए)
    public function getEarningRateAttribute()
    {
        if ($this->earning_coin > 0) {
            return $this->earning_amount / $this->earning_coin;
        }
        return null;
    }

    // ऑर्डर पर कितने coins earn होंगे
    public function calculateEarnedCoins($orderAmount)
    {
        if ($this->earning_amount > 0) {
            return floor(($orderAmount / $this->earning_amount) * $this->earning_coin);
        }
        return 0;
    }

    // कितने coins से कितने रुपये redeem होंगे
    public function calculateRedeemValue($coins)
    {
        if ($this->coin_value > 0) {
            return $coins / $this->coin_value;
        }
        return 0;
    }
}
