<?php

namespace App\Enums\ViewPaths\Expert;

enum Notifications
{
    const LIST = [
        URI => 'list',
        VIEW => 'expert-views.task-notifications.list'
    ];
    const VIEW = [
        URI => 'view',
        VIEW => 'expert-views.task-notifications.view'
    ];   
    const TICKET_VIEW = [
        URI => 'ticket',
        VIEW => 'expert-views.task-notifications.ticket-view'
    ];
}
