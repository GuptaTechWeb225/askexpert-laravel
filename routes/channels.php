<?php
// routes/channels.php
use App\Models\ChatSession;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = ChatSession::find($chatId);
    if (!$chat) return false;
    $isCustomer = (int) $user->id === (int) $chat->user_id;
    $isExpert = (int) $user->id === (int) $chat->expert_id;

    return $isCustomer || $isExpert;
});

Broadcast::channel('admin-chat.{expertId}', function ($user, $expertId) {
    // Agar expert login hai aur uska hi channel hai
    if (auth('expert')->check() && auth('expert')->id() == (int) $expertId) {
        return ['id' => auth('expert')->id(), 'name' => auth('expert')->user()->f_name];
    }

    if (auth('admin')->check()) {
        return ['id' => auth('admin')->id(), 'name' => 'Admin'];
    }

    return false;
});