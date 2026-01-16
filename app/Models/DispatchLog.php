<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchLog extends Model
{
    protected $table = 'dispatch_logs';

    protected $fillable = [
        'question_id',
        'user_id',
        'dispatch_mode',
    ];

    /**
     * User relation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ChatSession relation
     */
    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'question_id');
    }
}
