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
    $isCustomer = (int) $user->id === (int) $chat->user_id;
    $isExpert = (int) $user->id === (int) $chat->expert_id;

    return $isCustomer || $isExpert;
});
Broadcast::channel('admin-chat.{expertId}', function ($user, $expertId) {
    // TEMP: Admin ko hamesha allow (testing ke liye)
    if ($user instanceof Admin) {
        return true;
    }

    // Expert ko apne channel pe
    if ($user instanceof Expert) {
        return (int) $user->id === (int) $expertId;
    }

    // Debug ke liye log daal do
    Log::info('Channel authorization check for admin-chat.' . $expertId, [
        'user_type' => get_class($user ?? null),
        'user_id' => $user->id ?? 'no user',
        'expertId' => $expertId,
        'allowed' => false
    ]);

    return false;
});