<?php

namespace App\Enums\ViewPaths\Restaurant;

enum Customer
{
    const LIST = [
        URI => 'list',
        VIEW => 'restaurant-views.customer.list'
    ];

    const VIEW = [
        URI => 'view',
        VIEW => 'restaurant-views.customer.customer-view'
    ];
    const EXPORT = [
        URI => 'export',
        VIEW => ''
    ];
   

}
