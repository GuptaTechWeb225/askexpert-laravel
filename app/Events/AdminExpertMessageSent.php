<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\AdminExpertMessage;
use Illuminate\Support\Facades\Log;
class AdminExpertMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(AdminExpertMessage $message)
    {
        $this->message = $message->load('chat'); // optional
    }

   public function broadcastOn()
{
    $expertId = $this->message->chat?->expert_id ?? 0;

    if (!$expertId) {
        Log::error('Expert ID missing in broadcast', ['message_id' => $this->message->id]);
        return new PrivateChannel('admin-chat.0');
    }

    return new PrivateChannel('admin-chat.' . $expertId);
}

     public function broadcastWith()
    {
        return ['message' => $this->message];
    }
}
