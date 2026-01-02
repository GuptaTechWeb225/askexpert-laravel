<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertReview extends Model
{
    protected $fillable = [
        'chat_session_id', 'user_id', 'expert_id', 'rating', 'review'
    ];

    public function chatSession()
    {
        return $this->belongsTo(ChatSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }
}