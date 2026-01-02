<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantBillPayment extends Model
{
    use HasFactory;


    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'bill_uuid',
        'original_amount',
        'final_amount',
        'coupon_id',
        'coupon_code',
        'coins_used',
        'coins_earned',
        'status',
        'paid_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'paid_at' => 'datetime',
    ];

    /******** Relations ********/
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function coupon()
    {
        return $this->belongsTo(RestaurantCoupon::class, 'coupon_id');
    }
}
