<?php
// routes/channels.php
use App\Models\ChatSession;
use App\Models\Admin;
use App\Models\Expert;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = ChatSession::find($chatId);
    if (!$chat) return false;

    if (auth('customer')->check()) {
        $match = (int) auth('customer')->user()->id === (int) $chat->user_id;
        return $match;
    }
    if (auth('expert')->check()) {
        return (int) auth('expert')->user()->id === (int) $chat->expert_id;
    }
    return false;
});

Broadcast::channel('admin-chat.{expertId}', function ($user, $expertId) {
    if (auth('admin')->check()) {
        return true;
    }

    if ($user instanceof Admin) {
        return true;
    }

    if ($user instanceof Expert && (int)$user->id === (int)$expertId) {
        return true;
    }
    return false;
});