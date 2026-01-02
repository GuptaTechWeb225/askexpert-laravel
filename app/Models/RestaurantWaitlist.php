<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantWaitlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'party_size',
        'table_size',
        'position',
        'estimated_wait_time',
        'expected_time',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'estimated_wait_time' => 'integer',
        'expected_time'       => 'datetime',
        'expires_at'          => 'datetime',
    ];
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
