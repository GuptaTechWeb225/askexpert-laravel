<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatPayment extends Model
{
    protected $table = 'chat_payments';

    protected $fillable = [
        'chat_session_id',
        'user_id',
        'expert_fee',
        'stripe_payment_intent_id',
        'paid_at',
    ];

    protected $casts = [
        'expert_fee' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}