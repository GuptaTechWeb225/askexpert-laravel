<?php

namespace App\Enums\ViewPaths\Admin;

enum Restaurant
{
    const RESTAURANTS = [
        URI => 'list',
        VIEW => 'admin-views.restaurants.restaurant-list'
    ];
    const RESTAURANT_ANALYTICS = [
        URI => 'analytics',
        VIEW => 'admin-views.restaurants.analytics'
    ];


    const RESTAURANT_DETAILS = [
        URI => 'restaurant-details',
        VIEW => 'admin-views.restaurants.restaurant-details'
    ];
    const RESTAURANT_REQUEST = [
        URI => 'restaurant-request',
        VIEW => 'admin-views.restaurants.restaurant-request'
    ];
    const RESTAURANT_REQUEST_DETAILS = [
        URI => 'restaurant-request-detail',
        VIEW => 'admin-views.restaurants.restaurant-request-details'
    ];

    const UPDATE = [
        URI => 'status-update',
        VIEW => ''
    ];
    const RESTAURANT_EXPORT = [
        URI => 'restaurant-export',
        VIEW => ''
    ];
    const BOOST_UPDATE = [
        URI => 'boost-status-update',
        VIEW => ''
    ];
    const RESTAURANT_REQUEST_APPROVE = [
        URI => 'restaurant-request-approve',
        VIEW => ''
    ];
    const RESTAURANT_REQUEST_REJECT = [
        URI => 'restaurant-request-reject',
        VIEW => ''
    ];
    const DELETE = [
        URI => 'delete/{id}',
        VIEW => ''
    ];
}
