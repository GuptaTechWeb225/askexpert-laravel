<?php

namespace App\Enums\ViewPaths\Restaurant;

enum Transaction
{
    const LIST = [
        URI => 'list',
        VIEW => 'expert-views.transaction.list'
    ];

    const VIEW = [
        URI => 'view',
        VIEW => 'expert-views.transaction.analytics-view'
    ];
    const EXPORT = [
        URI => 'export',
        VIEW => ''
    ];

}
