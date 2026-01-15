<?php

namespace App\Enums\ViewPaths\Admin;

enum  Expert
{
    const EXPERTS = [
        URI => 'list',
        VIEW => 'admin-views.expert.expert-list',
    ];
    const EXPERT_REJECT = [
        URI => 'expert-reject',
        VIEW => '',
    ];
    const EXPERT_APPROVE = [
        URI => 'expert-add',
        VIEW => '',
    ];
    const EXPERT_REQUEST = [
        URI => 'applications',
        VIEW => 'admin-views.expert.expert-request',
    ];
    const EXPERT_CHATS = [
        URI => 'all',
        VIEW => 'admin-views.internal-messages.index',
    ];
    const EXPERT_MASSAGE = [
        URI => 'messages',
        VIEW => '',
    ];
    const MASSAGE_READ = [
        URI => 'mark-read',
        VIEW => '',
    ];
    const MASSAGE_SEND = [
        URI => 'send',
        VIEW => '',
    ];
    const EXPERT_STATUS = [
        URI => 'expert-status',
        VIEW => '',
    ];
    const EXPERT_VIEW = [
        URI => 'view',
        VIEW => 'admin-views.expert.expert-view',
    ];
   
}
