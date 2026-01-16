<?php

namespace App\Http\Controllers\Admin\Expert;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Enums\ViewPaths\Admin\Expert as ExpertPath;
use App\Models\AdminExpertChat;
use App\Models\AdminExpertMessage;
use Illuminate\Http\Request;
use App\Events\AdminExpertMessageSent;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface;

class ExpertChatController extends Controller
{
    public function __construct(
        private readonly AdminNotificationRepositoryInterface   $notificationRepo,

    ) {}

    public function index()
    {
        $adminId = auth('admin')->id();

        // Experts jinke sath admin ki chat hui hai
        $activeExpertIds = AdminExpertChat::where('admin_id', $adminId)
            ->pluck('expert_id');

        // Experts load unread_count aur last chat timestamp ke saath
        $experts = Expert::withCount([
            'messages as unread_count' => function ($q) use ($adminId) {
                $q->where('sender_type', 'expert')
                    ->where('is_read', 0)
                    ->whereHas('chat', function ($q2) use ($adminId) {
                        $q2->where('admin_id', $adminId);
                    });
            }
        ])
            ->whereIn('id', $activeExpertIds)
            ->with(['chats' => function ($q) use ($adminId) {
                // Last chat with this admin
                $q->where('admin_id', $adminId)
                    ->latest('updated_at')
                    ->limit(1);
            }])
            ->get()
            ->sortByDesc(function ($expert) {
                // Sort by last chat updated_at
                return optional($expert->chats->first())->updated_at;
            })
            ->values(); // reindex collection

        $allExperts = Expert::all();

        return view(ExpertPath::EXPERT_CHATS[VIEW], compact('experts', 'allExperts'));
    }


    public function getMessages($expertId)
    {
        $adminId = 1; // Ya auth('admin')->id() agar admin login ho

        $chat = AdminExpertChat::where('admin_id', $adminId)
            ->where('expert_id', $expertId)
            ->first();

        if (!$chat) {
            return response()->json(['messages' => []]);
        }

        $messages = AdminExpertMessage::where('admin_expert_chat_id', $chat->id)
            ->orderBy('sent_at')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'expert_id' => 'required|exists:experts,id',
            'message' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $adminId = 1; // Ya auth('admin')->id()

        $chat = AdminExpertChat::firstOrCreate([
            'admin_id' => $adminId,
            'expert_id' => $request->expert_id,
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('admin-expert-images', 'public');
        }

        $msg = AdminExpertMessage::create([
            'admin_expert_chat_id' => $chat->id,
            'sender_id' => $adminId,
            'sender_type' => 'admin',
            'message' => $request->message,
            'image_path' => $imagePath,
            'sent_at' => now(),
            'is_read' => 0,
        ]);

        broadcast(new AdminExpertMessageSent($msg))->toOthers();


        $expertId = $chat->expert_id ?? 1;
        $title = 'New Admin Massage';
        $message = "Admin send you a massage.";

        $recipients = [
            ['type' => 'expert', 'id' =>  $expertId],
        ];

        $this->notificationRepo->notifyRecipients(
            1,
            Expert::class,
            $title,
            $message,
            $recipients
        );
        return response()->json([
            'success' => true,
            'message_data' => $msg
        ]);
    }

    public function markRead(Request $request)
    {
        $request->validate(['expert_id' => 'required']);
        $adminId = 1;

        $chat = AdminExpertChat::where('admin_id', $adminId)
            ->where('expert_id', $request->expert_id)
            ->first();

        if ($chat) {
            AdminExpertMessage::where('admin_expert_chat_id', $chat->id)
                ->where('sender_type', 'expert')
                ->where('is_read', 0)
                ->update(['is_read' => 1]);
        }

        return response()->json(['success' => true]);
    }


    public function markSpecificRead(Request $request)
    {
        $request->validate(['message_id' => 'required']);

        AdminExpertMessage::where('id', $request->message_id)
            ->where('sender_type', 'expert')
            ->update(['is_read' => 1]);

        // Optional: broadcast tick update if needed
        return response()->json(['success' => true]);
    }
}
