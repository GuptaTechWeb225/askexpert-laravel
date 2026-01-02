<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminExpertMessage extends Model
{
    protected $fillable = ['admin_expert_chat_id', 'sender_id', 'sender_type', 'message', 'image_path', 'is_read', 'sent_at'];

   public function chat()
{
    return $this->belongsTo(AdminExpertChat::class, 'admin_expert_chat_id');
}
}