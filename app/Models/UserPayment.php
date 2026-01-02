<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPayment extends Model
{
    protected $table = 'user_payments';

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope for paid payments
    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_at');
    }
}