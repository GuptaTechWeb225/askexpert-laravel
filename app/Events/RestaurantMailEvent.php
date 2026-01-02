<?php

namespace App\Events;

use App\Models\RestaurantMail;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RestaurantMailEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mail;

    public function __construct(RestaurantMail $mail)
    {
        $this->mail = $mail;
    }
}
