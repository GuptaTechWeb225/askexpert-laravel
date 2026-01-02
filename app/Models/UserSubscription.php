<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    protected $table = 'user_subscriptions';

    protected $fillable = [
        'user_id',
        'category_id',
        'monthly_fee',
        'stripe_subscription_id',
        'stripe_customer_id',
        'current_period_start',
        'current_period_end',
        'active',
        'auto_renew',
        'canceled_at',
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'canceled_at' => 'datetime',
        'active' => 'boolean',
        'auto_renew' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpertCategory::class);
    }

    // Check if subscription is currently active
    public function isActive(): bool
    {
        return $this->active && $this->current_period_end > now();
    }

    // Scope for active subscriptions
    public function scopeActive($query)
    {
        return $query->where('active', true)
                     ->where('current_period_end', '>', now());
    }
}