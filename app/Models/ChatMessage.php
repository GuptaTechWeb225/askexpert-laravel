<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

     protected $fillable = [
        'chat_session_id',
        'sender_type',
        'sender_id',
        'message',
        'sent_at',
        'is_read',
    ];


    public function sender()
    {
        // Assuming your users/customers are in the User model or Customer model
        return $this->belongsTo(User::class, 'sender_id');
    }
    
    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }
    public function chat()
    {
        return $this->belongsTo(ChatSession::class);
    }
}
