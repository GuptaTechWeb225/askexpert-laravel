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

    // Customer ke liye check
    if (auth('customer')->check()) {
        return (int) auth('customer')->id === (int) $chat->user_id;
    }

    // Expert ke liye check
    if (auth('expert')->check()) {
        return (int) auth('expert')->id === (int) $chat->expert_id;
    }

    // Admin ko optional allow kar sakte ho (agar chahiye)
    // if (auth('admin')->check()) return true;

    return false;
});

Broadcast::channel('admin-chat.{expertId}', function ($user, $expertId) {
    Log::info('Authorization check for admin-chat.' . $expertId, [
        'user' => $user ? get_class($user) . ' (ID: ' . $user->id . ')' : 'NULL',
        'auth_guard' => auth()->guard()->name ?? 'none',
        'is_admin' => auth('admin')->check() ? 'YES' : 'NO'
    ]);

    if (auth('admin')->check()) {
        Log::info('Admin authorized via auth guard');
        return true;
    }

    if ($user instanceof Admin) {
        return true;
    }

    if ($user instanceof Expert && (int)$user->id === (int)$expertId) {
        return true;
    }

    Log::warning('Unauthorized access to admin-chat.' . $expertId);
    return false;
});