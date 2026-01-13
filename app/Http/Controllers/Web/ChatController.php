<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\ExpertEarning;
use App\Models\ExpertReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Events\ChatMessageSent;
use App\Events\MessageRead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use App\Services\ExpertService;
use App\Services\Agora\RtcTokenBuilder;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Send chat message (User → Expert)
     */

    public function __construct(
        private readonly ExpertService             $expertService,
    ) {}
    public function sendMessage(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chat_sessions,id',
            'message' => 'nullable|string',
            'image' => 'nullable|image'
        ]);
        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat-images', 'public');
        }

        $msg = ChatMessage::create([
            'chat_session_id' => $request->chat_id,
            'sender_type' => 'user',
            'sender_id' => auth('customer')->id(),
            'message' => $request->message ?? $path,
            'sent_at' => now()
        ]);


        broadcast(new ChatMessageSent($msg))->toOthers();
        return response()->json([
            'success' => true,
            'message_data' => $msg
        ]);
    }

    /**
     * View chat screen
     */
    public function view(ChatSession $chat)
    {
        if ($chat->user_id !== auth('customer')->id()) {
            abort(403);
        }

        $expert = $chat->expert;
        $messages = $chat->messages()->orderBy('sent_at')->get();

        return view(VIEW_FILE_NAMES['chat_bot'], compact('chat', 'expert', 'messages'));
    }


    public function check(ChatSession $chat)
    {


        $expert = $chat->expert;

        if (!$expert) {
            return response()->json([
                'expertOnline' => false,
                'reason' => 'Expert not assigned'
            ]);
        }

        $isRecentlyActive = $expert->last_active_at &&
            Carbon::parse($expert->last_active_at)->gt(now()->subMinutes(2));

        $expertOnline = $expert->is_online;


        return response()->json([
            'expertOnline' => $expertOnline,
            'isBusy' => (bool) $expert->is_busy,
            'lastActiveAt' => $expert->last_active_at
        ]);
    }

    public function markRead(Request $request)
    {
        $chatId = $request->chat_id;
        $messageId = $request->message_id;

        // Agar chatId nahi aaya toh messageId se nikaal lo
        if (!$chatId && $messageId) {
            $chatId = ChatMessage::find($messageId)->chat_session_id;
        }

        $query = ChatMessage::where('chat_session_id', $chatId)
            ->where('sender_type', 'expert');

        if ($messageId) {
            $query->where('id', $messageId);
        }

        $query->update(['is_read' => 1]);

        // Broadcast tabhi karo jab chatId mil jaye
        if ($chatId) {
            broadcast(new MessageRead($chatId, $messageId))->toOthers();
        }

        return response()->json(['success' => true]);
    }
  public function generateAgoraToken($chatId)
{
    Log::info('Agora Token Request Started', [
        'chat_id' => $chatId,
        'customer_auth' => auth('customer')->check(),
        'expert_auth' => auth('expert')->check()
    ]);

    $customer = auth('customer')->user();
    $expert   = auth('expert')->user();

    if (!$customer && !$expert) {
        Log::warning('Unauthorized Agora token request');
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $role = $customer ? 'customer' : 'expert';

    // 🔥 FIX: UNIQUE UID PER ROLE
    if ($customer) {
        $uid = 100000 + intval($customer->id);
    } else {
        $uid = 200000 + intval($expert->id);
    }

    $appId   = config('services.agora.app_id');
    $appCert = config('services.agora.app_certificate');

    if (!$appId || !$appCert) {
        Log::error('Agora config missing');
        return response()->json(['error' => 'Agora configuration missing'], 500);
    }

    $channelName = 'chat_' . $chatId;

    $expireTimeInSeconds = 3600;
    $privilegeExpiredTs = now()->timestamp + $expireTimeInSeconds;

    try {
        $token = RtcTokenBuilder::buildTokenWithUid(
            $appId,
            $appCert,
            $channelName,
            $uid,
            RtcTokenBuilder::RolePublisher,
            $privilegeExpiredTs
        );

        Log::info('Agora Token Generated Successfully', [
            'channel' => $channelName,
            'uid' => $uid,
            'role' => $role
        ]);

        return response()->json([
            'token'   => $token,
            'app_id'  => $appId,
            'channel' => $channelName,
            'uid'     => $uid,
            'role'    => $role
        ]);
    } catch (\Throwable $e) {
        Log::error('Agora Token Generation Failed', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'error' => 'Failed to generate Agora token'
        ], 500);
    }
}



    public function endChat(Request $request, $chatId)
    {
        $chat = ChatSession::where('id', $chatId)
            ->where('user_id', auth('customer')->id())
            ->whereIn('status', ['active', 'waiting'])
            ->firstOrFail();

        DB::transaction(function () use ($chat) {
            $chat->update([
                'status' => 'ended',
                'ended_at' => now()
            ]);
            if ($chat->expert) {
                $chat->expert->update([
                    'is_busy' => false,
                    'current_chat_id' => null
                ]);
            }
            $systemMessage = ChatMessage::create([
                'chat_session_id' => $chat->id,
                'sender_type' => 'system',
                'sender_id' => auth('customer')->id(),
                'message' => 'This chat has been ended by the user.',
                'sent_at' => now(),
                'is_read' => true
            ]);
            broadcast(new ChatMessageSent($systemMessage))->toOthers();
            if ($chat->expert) {
                $this->expertService->createExpertEarning($chat, 'user');
            }
            $notificationRepo = app(\App\Contracts\Repositories\AdminNotificationRepositoryInterface::class);
            $recipients = [
                ['type' => 'admin', 'id' => 1],
                ['type' => 'expert', 'id' => $chat->expert_id],
                ['type' => 'customer', 'id' => $chat->user_id],
            ];

            $titleAdmin = "Chat End";
            $messageAdmin = "Chat has been ended from user side";

            $notificationRepo->notifyRecipients(
                $chat->id,
                ChatSession::class,
                $titleAdmin,
                $messageAdmin,
                [['type' => 'admin', 'id' => 1]]
            );
            if ($chat->expert) {

                $titleExpert = "User end the chat";
                $messageExpert = "chat has been ended by user";

                $notificationRepo->notifyRecipients(
                    $chat->id,
                    ChatSession::class,
                    $titleExpert,
                    $messageExpert,
                    [['type' => 'expert', 'id' => $chat->expert_id]]
                );
            }
            $titleUser = "your chat is ended now";
            $messageUser = "you end the chat from expert";

            $notificationRepo->notifyRecipients(
                $chat->id,
                ChatSession::class,
                $titleUser,
                $messageUser,
                [['type' => 'customer', 'id' => $chat->user_id]]
            );
        });
        return response()->json([
            'success' => true,
            'message' => 'Chat ended successfully!'
        ]);
    }

    public function submitReview(Request $request, $chatId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->all()
            ], 422);
        };
        $chat = ChatSession::where('id', $chatId)
            ->where('user_id', auth('customer')->id())
            ->where('status', 'ended')
            ->firstOrFail();

        if ($chat->review()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted a review for this chat.'
            ], 422);
        }
        if ($chat->expert) {
            ExpertReview::create([
                'chat_session_id' => $chat->id,
                'user_id' => auth('customer')->id(),
                'expert_id' => $chat->expert_id,
                'rating' => $request->rating,
                'review' => $request->review
            ]);

            $this->expertService->addPremiumIfEligible($chat, $request->rating);
        }

        $notificationRepo = app(\App\Contracts\Repositories\AdminNotificationRepositoryInterface::class);

        $recipients = [
            ['type' => 'admin', 'id' => 1],
            ['type' => 'expert', 'id' => $chat->expert_id],
            ['type' => 'customer', 'id' => $chat->user_id],
        ];

        // 1️⃣ Admin notification
        $notificationRepo->notifyRecipients(
            $chat->id,
            ChatSession::class,
            "New Review Submitted",
            "User has submitted a review for chat #{$chat->id}",
            [['type' => 'admin', 'id' => 1]]
        );


        if ($chat->expert) {
            $notificationRepo->notifyRecipients(
                $chat->id,
                ChatSession::class,
                "New Review Received",
                "You have received a new review from {$chat->customer?->f_name} {$chat->customer?->l_name}",
                [['type' => 'expert', 'id' => $chat->expert_id]]
            );
        }
        $notificationRepo->notifyRecipients(
            $chat->id,
            ChatSession::class,
            "Review Submitted",
            "Thank you for submitting a review for your chat with {$chat->expert?->f_name} {$chat->expert?->l_name}",
            [['type' => 'customer', 'id' => $chat->user_id]]
        );

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your review!'
        ]);
    }
}
