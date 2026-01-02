<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'title',
        'message',
        'is_read',
    ];
}
