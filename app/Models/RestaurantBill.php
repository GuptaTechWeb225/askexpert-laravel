<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RestaurantBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'customer_id',
        'restaurant_name',
        'customer_name',
        'bill_amount',
        'qr_code',
        'status',
        'expired_at',
    ];


    protected $casts = [
        'expired_at' => 'datetime',
    ];

   
}
