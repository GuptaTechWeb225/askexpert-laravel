<?php

namespace App\Enums\ViewPaths\Restaurant;

enum Profile
{
    const INDEX = [
        URI => 'index',
        VIEW => 'restaurant-views.profile.index',
        ROUTE => 'vendor.profile.index'
    ];
    const UPDATE = [
        URI => 'update',
        VIEW => 'restaurant-views.profile.update-view'
    ];
    const BANK_INFO_UPDATE = [
        URI => 'update-bank-info',
        VIEW => 'restaurant-views.profile.bank-info-update-view'
    ];
}
