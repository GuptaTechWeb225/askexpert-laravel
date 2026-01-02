<?php

namespace App\Enums\ViewPaths\Admin;

enum  Transaction
{
  

    const INDEX =[
        URI => 'transactions',
        VIEW => 'admin-views.transaction.index'
    ];
  
    const ANALYTICS_VIEW = [
        URI => 'analytics-view',
        VIEW => 'admin-views.transaction.analytics'
    ];
    const EXPORT = [
        URI => 'export',
        VIEW => ''
    ];

}
