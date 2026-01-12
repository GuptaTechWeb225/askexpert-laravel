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

Broadcast::channel('admin-chat.*', function ($user) {
    return $user instanceof \App\Models\Admin || $user instanceof \App\Models\Expert;
});