<?php

namespace App\Enums\ViewPaths\Expert;

enum Dashboard
{
    const INDEX = [
        URI => '/',
        VIEW => 'expert-views.dashboard.index',
        ROUTE => 'expert.dashboard.index'
    ];

    const ORDER_STATUS = [
        URI => 'order-status',
        VIEW => 'restaurant-views.partials._dashboard-order-status'
    ];
    const EARNING_STATISTICS = [
        URI => 'earning-statistics',
        VIEW => 'restaurant-views.dashboard.partials.earning-statistics'
    ];
    const WITHDRAW_REQUEST = [
            URI => 'withdraw-request',
        VIEW => ''
    ];
    const METHOD_LIST = [
        URI => 'method-list',
        VIEW => ''
    ];

    const REAL_TIME_ACTIVITIES = [
        URI => 'real-time-activities',
        VIEW => ''
    ];
    const UPDATE_STATUS = [
        URI => 'update-status',
        VIEW => ''
    ];

}
