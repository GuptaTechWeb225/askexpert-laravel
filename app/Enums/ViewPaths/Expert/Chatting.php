<?php

namespace App\Enums\ViewPaths\Restaurant;

enum Chatting
{
    const INDEX = [
        URI => 'index',
        VIEW => 'restaurant-views.chatting.index',
    ];
    const MESSAGE = [
        URI => 'message',
        VIEW => 'restaurant-views.chatting.index',
    ];

    const NEW_NOTIFICATION = [
        URI => 'new-notification',
        VIEW => '',
    ];
}
