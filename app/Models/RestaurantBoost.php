<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantBoost extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'restaurant_id',
        'plan_id',
        'boost_days',
        'remaining_days',
        'radius_km',
        'start_date',
        'end_date',
        'status',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)->whereDate('end_date', '>=', now());
    }
}
