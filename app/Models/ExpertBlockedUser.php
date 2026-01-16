<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpertBlockedUser extends Model
{
    protected $fillable = [
        'expert_id',
        'user_id',
    ];

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
