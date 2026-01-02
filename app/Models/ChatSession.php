<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'expert_id',
        'category_id',
        'status',
        'started_at',
        'payment_status',
        'total_charged',
        'ended_at'
    ];
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'total_charged' => 'decimal:2',
        'payment_status' => 'string',
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpertCategory::class, 'category_id');
    }

    public function refundRequest()
    {
        return $this->hasOne(ChatRefundRequest::class, 'chat_session_id');
    }

    public function firstMessage()
    {
        return $this->hasOne(ChatMessage::class)->oldest('sent_at');
    }

    // ChatSession.php mein yeh method add kar do

    public function review()
    {
        return $this->hasOne(ExpertReview::class, 'chat_session_id');
    }
    public function payment()
    {
        return $this->hasOne(ChatPayment::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
