<?php

namespace App\Enums\ViewPaths\Restaurant;

enum Transaction
{
    const LIST = [
        URI => 'list',
        VIEW => 'restaurant-views.transaction.list'
    ];

    const VIEW = [
        URI => 'view',
        VIEW => 'restaurant-views.transaction.analytics-view'
    ];
    const EXPORT = [
        URI => 'export',
        VIEW => ''
    ];

}
