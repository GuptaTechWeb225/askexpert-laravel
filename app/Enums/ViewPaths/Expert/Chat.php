<?php

namespace App\Enums\ViewPaths\Expert;

enum  Chat
{
    const INDEX = [
        URI => '/',
        VIEW => 'expert-views.chatbot.index',
    ];
    const MASSAGES = [
        URI => 'all',
        VIEW => 'expert-views.chatting.index',
    ];
    const SEND_MASSAGE = [
        URI => 'send-message',
        VIEW => ''
    ];
    const ADMIN_MASSAGE_SENT = [
        URI => 'admin-chat/send',
        VIEW => ''
    ];
    const MARK_READ = [
        URI => 'mark-read',
        VIEW => ''
    ];
    const QUESTIONS = [
        URI => '',
        VIEW => 'expert-views.chatbot.all-question'
    ];

    const END_CHAT = [
        URI => '/{chatId}/end',
        VIEW => ''
    ];
}
