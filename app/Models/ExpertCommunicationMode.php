<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpertCommunicationMode extends Model
{
    protected $fillable = [
        'expert_id', 'mode', 'available', 'on_break', 'vacation_mode'
    ];

    protected $casts = [
        'available' => 'boolean',
        'on_break' => 'boolean',
        'vacation_mode' => 'boolean',
    ];

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }
}