<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminExpertChat extends Model
{
    protected $fillable = ['admin_id', 'expert_id'];

    public function messages()
    {
        return $this->hasMany(AdminExpertMessage::class)->orderBy('sent_at');
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(AdminExpertMessage::class)
            ->latest('sent_at');
    }
}
