<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRefundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_session_id',
        'user_id',
        'chat_payment_id',
        'requested_amount',
        'reason',
        'status',
        'approved_at',
        'rejected_at',
        'admin_note',
    ];

    public function chatSession()
    {
        return $this->belongsTo(ChatSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatPayment()
    {
        return $this->belongsTo(ChatPayment::class);
    }
}
