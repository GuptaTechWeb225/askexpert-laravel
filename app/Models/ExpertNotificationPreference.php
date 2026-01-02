<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpertNotificationPreference extends Model
{
    protected $fillable = [
        'expert_id', 'type', 'email', 'sms', 'dashboard'
    ];

    protected $casts = [
        'email' => 'boolean',
        'sms' => 'boolean',
        'dashboard' => 'boolean',
    ];

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }
}