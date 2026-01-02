<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanSuggestionLog extends Model
{
    use HasFactory;
    public $timestamps = false; // <--- yeh line daal do

    protected $fillable = [
        'restaurant_id',
        'plan_id',
        'shown_at',
    ];

    protected $casts = [
        'shown_at' => 'datetime',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // 📅 Scope: get today's logs for a restaurant
    public function scopeToday($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId)
            ->whereDate('shown_at', now()->toDateString());
    }
}
