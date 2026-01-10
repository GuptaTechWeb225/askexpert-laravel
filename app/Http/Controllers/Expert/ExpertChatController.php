<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\AdminExpertChat;
use App\Models\AdminExpertMessage;
use App\Models\ChatMessage;
use App\Models\ExpertEarning;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Events\ChatMessageSent;
use App\Events\AdminExpertMessageSent;
use App\Events\MessageRead;
use Illuminate\Support\Facades\Storage;
use App\Enums\ViewPaths\Expert\Chat;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Services\ExpertService;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface;


class ExpertChatController extends Controller
{

    public function __construct(
        private readonly ExpertService             $expertService,
        private readonly AdminNotificationRepositoryInterface   $notificationRepo,

    ) {}

    public function view(ChatSession $chat)
    {

        if ($chat->expert_id !== auth('expert')->id()) {
            abort(403);
        }

        $customer = $chat->customer;
        $messages = $chat->messages()->orderBy('sent_at')->get();

        return view(Chat::INDEX[VIEW], compact('chat', 'customer', 'messages'));
    }
    public function massagesChat()
    {
                Log::info('this 2 is called');

        $expertId = auth('expert')->id();
        if (!$expertId) {
            abort(403, 'Unauthorized');
        }
        $chat = AdminExpertChat::where('expert_id', $expertId)->first();
        $superAdmin = Admin::where('id', 1)->first();

        $messages = collect();

        if ($chat) {
            $messages = AdminExpertMessage::where('admin_expert_chat_id', $chat->id)
                ->orderBy('sent_at', 'asc')
                ->get();
        }
        return view(Chat::MASSAGES[VIEW], compact('messages', 'chat', 'superAdmin'));
    }

    public function sendMessage(Request $request)
    {

                Log::info('this 3 is called');

        $request->validate([
            'chat_id' => 'required|exists:chat_sessions,id',
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:5120'
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat-images', 'public');
        }

        $msg = ChatMessage::create([
            'chat_session_id' => $request->chat_id,
            'sender_type' => 'expert',
            'sender_id' => auth('expert')->id(),
            'message' => $request->message ?? $path,
            'sent_at' => now(),
            'is_read' => 0
        ]);

        broadcast(new ChatMessageSent($msg))->toOthers();

        return response()->json([
            'success' => true,
            'message_data' => $msg
        ]);
    }


    public function markRead(Request $request)
    {

                Log::info('this 4 is called');

        $chatId = $request->chat_id;
        $messageId = $request->message_id;

        // Agar chatId nahi aaya toh messageId se nikaal lo
        if (!$chatId && $messageId) {
            $chatId = ChatMessage::find($messageId)->chat_session_id;
        }

        $query = ChatMessage::where('chat_session_id', $chatId)
            ->where('sender_type', 'user');

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

    public function sendToAdmin(Request $request)
    {
        Log::info('this 5 is called');

        Log::info('SendMessage request received', [
            'message' => $request->message,
            'expert_id' => auth('expert')->id()
        ]);

        $request->validate([
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:5120'
        ]);
        $adminId = 1;
        $chat = AdminExpertChat::firstOrCreate([
            'admin_id' => $adminId,
            'expert_id' => auth('expert')->id()
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('admin-expert-images', 'public');
        }

        $msg = AdminExpertMessage::create([
            'admin_expert_chat_id' => $chat->id,
            'sender_id' => auth('expert')->id(),
            'sender_type' => 'expert',
            'message' => $request->message,
            'image_path' => $imagePath,
            'sent_at' => now()
        ]);

        broadcast(new AdminExpertMessageSent($msg))->toOthers();

        $expert = auth('expert')->user();

        $title = 'New Expert Massage';
        $message = "Expert {$expert->f_name} {$expert->l_name} send you a massage.";

        $recipients = [
            ['type' => 'admin', 'id' => 1],
        ];

        $this->notificationRepo->notifyRecipients(
            1,
            Admin::class,
            $title,
            $message,
            $recipients
        );

        return response()->json(['success' => true, 'message_data' => $msg]);
    }
    public function getAdminMessages()
    {

                Log::info(message: 'this 6 is called');

        $expertId = auth('expert')->id();

        $chat = AdminExpertChat::where('expert_id', $expertId)->first();

        if (!$chat) {
            return response()->json(['messages' => []]);
        }

        $messages = AdminExpertMessage::where('admin_expert_chat_id', $chat->id)
            ->orderBy('sent_at')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function markAdminRead(Request $request)
    {

                Log::info('this 7 is called');

        $expertId = auth('expert')->id();

        $chat = AdminExpertChat::where('expert_id', $expertId)->first();

        if ($chat) {
            AdminExpertMessage::where('admin_expert_chat_id', $chat->id)
                ->where('sender_type', 'admin')
                ->update(['is_read' => 1]);
        }

        return response()->json(['success' => true]);
    }

    public function markAdminSpecificRead(Request $request)
    {

                Log::info('this 8 is called');

        $request->validate(['message_id' => 'required']);

        AdminExpertMessage::where('id', $request->message_id)
            ->where('sender_type', 'admin')
            ->update(['is_read' => 1]);

        return response()->json(['success' => true]);
    }


    public function myQuestions()
    {

                Log::info('this 9 is called');

        $expert = auth('expert')->user(); // authenticated expert

        $expertId = $expert->id;
        $assignedChat = ChatSession::where('expert_id', $expertId)
            ->with(['messages', 'customer'])
            ->latest()
            ->first();

        $oldChats = ChatSession::where('expert_id', $expertId)
            ->with(['customer'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        $assignedQuestions = ChatMessage::whereHas('session', function ($q) use ($expertId) {
            $q->where('expert_id', $expertId);
        })
            ->with('session.customer')
            ->get();

        $unreadMessages = ChatMessage::whereHas('session', function ($q) use ($expertId) {
            $q->where('expert_id', $expertId);
        })
            ->where('sender_type', 'customer')
            ->where('is_read', false)
            ->get();

        $averageRating = $expert->average_rating;
        $totalEarning = $expert->total_earned; // accessor se fetch
        return view(Chat::QUESTIONS[VIEW], compact(
            'assignedChat',
            'oldChats',
            'assignedQuestions',
            'unreadMessages',
            'averageRating',
            'totalEarning',
        ));
    }

    public function endChatByExpert(Request $request, $chatId)
    {

                Log::info('this 10 is called');

        $chat = ChatSession::where('id', $chatId)
            ->where('expert_id', auth('expert')->id())
            ->whereIn('status', ['active', 'pending'])
            ->firstOrFail();

        DB::transaction(function () use ($chat) {
            $chat->update([
                'status' => 'ended',
                'ended_at' => now()
            ]);

            // Expert free karo
            auth('expert')->user()->update([
                'is_busy' => false,
                'current_chat_id' => null
            ]);

            // System message add karo
            ChatMessage::create([
                'chat_session_id' => $chat->id,
                'sender_type' => 'system',
                'sender_id' => auth('expert')->id(),
                'message' => 'This chat has been ended by the expert.',
                'sent_at' => now(),
                'is_read' => true
            ]);



            $existingEarning = ExpertEarning::where('chat_session_id', $chat->id)->first();

            if (!$existingEarning) {
                $this->expertService->createExpertEarning($chat, 'expert');
            }
        });
        return response()->json([
            'success' => true,
            'message' => 'Chat ended successfully!'
        ]);
    }
}
