<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;



class RestaurantPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'plan_id',
        'plan_duration',
        'plan_expires_at',
        'plan_expiry_reminder',
        'status',
    ];

    protected $casts = [
        'plan_expires_at' => 'datetime',
        'plan_expiry_reminder' => 'datetime',
        'status' => 'string',
    ];

    protected $dates = ['plan_expires_at', 'plan_expiry_reminder'];


    // Relations
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
        return $query->where('status', 1)
            ->where('plan_expires_at', '>', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->where('plan_expires_at', '<=', Carbon::now());
    }



    // Accessor for reminder days left
    public function getReminderDaysLeftAttribute()
    {
        if (!$this->plan_expires_at) {
            return null;
        }

        $now = Carbon::now();
        $expiry = Carbon::parse($this->plan_expires_at);

        $daysLeft = $now->diffInDays($expiry, false); // negative agar expire ho gya ho
        return $daysLeft > 0 ? $daysLeft : 0;
    }
}
