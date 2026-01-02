<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertEarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_session_id', 'expert_id', 'category_id',
        'basic_amount', 'premium_amount', 'total_amount',
        'status', 'paid_at', 'note'
    ];

       protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function chat()
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpertCategory::class);
    }
}
