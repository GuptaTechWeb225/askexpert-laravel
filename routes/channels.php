<?php
// routes/channels.php
use App\Models\ChatSession;
use App\Models\Admin;
use App\Models\Expert;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = ChatSession::find($chatId);
    if (!$chat) return false;
    $isCustomer = (int) $user->id === (int) $chat->user_id;
    $isExpert = (int) $user->id === (int) $chat->expert_id;

    return $isCustomer || $isExpert;
});
Broadcast::channel('admin-chat.{expertId}', function ($user, $expertId) {
    if ($user instanceof Admin) {
        return true;
    }

    // Expert ko sirf apne channel pe permission
    if ($user instanceof Expert) {
        return (int) $user->id === (int) $expertId;
    }

    return false;
});