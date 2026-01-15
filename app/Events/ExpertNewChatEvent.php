<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ExpertNewChatEvent implements ShouldBroadcastNow
{
    use SerializesModels;

    public $chatId;
    public $userName;
    public $expertId;

    public function __construct($chatId, $userName, $expertId)
    {
        $this->chatId   = $chatId;
        $this->userName = $userName;
        $this->expertId = $expertId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('expert-chat.' . $this->expertId);
    }

    public function broadcastAs()
    {
        return 'new-chat';
    }
}
