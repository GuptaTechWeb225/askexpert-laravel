<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantCoupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'restaurant_name',
        'coupon_title',
        'coupon_description',
        'coupon_code',
        'limit_for_same_user',
        'discount_type',
        'discount_amount',
        'minimum_purchase',
        'min_point_require',
        'max_point_use',
        'start_date',
        'end_date',
        'status',
    ];
}
