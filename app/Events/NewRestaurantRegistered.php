<?php

namespace App\Events;

use App\Models\Restaurant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewRestaurantRegistered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $restaurant;

    /**
     * Create a new event instance.
     */
    public function __construct(Restaurant $restaurant)
    {
        $this->restaurant = $restaurant;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-dashboard'), // public channel
            // agar private channel chahiye to: new PrivateChannel('admin-dashboard')
        ];
    }

    public function broadcastAs(): string
    {
        return 'new-restaurant';
    }

    /**
     * Optional: Data to send
     */
    public function broadcastWith(): array
    {
        return [
            'restaurant' => $this->restaurant->only(['id', 'restaurant_name', 'email', 'phone'])
        ];
    }
}
