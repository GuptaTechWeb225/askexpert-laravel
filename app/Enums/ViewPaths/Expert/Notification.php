<?php

namespace App\Enums\ViewPaths\Restaurant;

enum Notification
{
    const INDEX = [
        URI => 'index',
        VIEW => 'restaurant-views.notification.index',
        ROUTE => 'restaurant.notification.index',

    ];
    const GET_NOTIFY = [
        URI => 'get-notify',
        VIEW => 'restaurant-views.notification.index',
    ];
    const MAIL_INFO = [
        URI => 'get-mail-info',
        VIEW => 'restaurant-views.notification.mail',
    ];
    const UPDATE = [
        URI => 'update',
        VIEW => 'restaurant-views.notification.update-view'
    ];
    const BOOST_VIEW = [
        URI => 'boostview',
        VIEW => 'restaurant-views.boost.index'
    ];
    const BOOST_TOGGLE = [
        URI => 'boost-toggle',
        VIEW => ''
    ];
    const UPDATE_STATUS = [
        URI => 'update-status',
        VIEW => ''
    ];
    const MAIL_STORE = [
        URI => 'mail-store',
        VIEW => ''
    ];
    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];
    const MAIL_DELETE = [
        URI => 'mail-delete',
        VIEW => ''
    ];
    const RESEND_NOTIFICATION = [
        URI => 'resend-notification',
        VIEW => ''
    ];
}
