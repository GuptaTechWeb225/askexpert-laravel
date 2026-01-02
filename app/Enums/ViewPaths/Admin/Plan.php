<?php

namespace App\Enums\ViewPaths\Admin;

enum  Plan
{
    const MEMBER_PLAN = [
        URI => 'membership-plan',
        VIEW => 'admin-views.plan.membership-plan',
    ];
    const PLAN_ANALYTICS = [
        URI => 'plan-analytics',
        VIEW => 'admin-views.plan.analytics',
    ];
    const PLAN_EXPORT = [
        URI => 'plan-export',
        VIEW => '',
    ];
    const PLAN_REQUEST_EXPORT = [
        URI => 'plan-request-export',
        VIEW => '',
    ];
    const BOOST_PLAN = [
        URI => 'boost-plan',
        VIEW => 'admin-views.plan.boost-plan',
    ];
    const PLAN_REQUESTS = [
        URI => 'plan-requests',
        VIEW => 'admin-views.plan.plan-requests',
    ];
    const RESTAURANT_PLANS = [
        URI => 'restaurant-plans',
        VIEW => 'admin-views.plan.restaurant-plans',
    ];

    const MEMBER_PLAN_STORE = [
        URI => 'membership-plan-store',
        VIEW => '',
    ];
    const BOOST_PLAN_STORE = [
        URI => 'boost-plan-store',
        VIEW => '',
    ];

     const MEMBER_PLAN_UPDATE = [
        URI => 'membership-plan-update',
        VIEW => '',
    ];
     const BOOST_PLAN_UPDATE = [
        URI => 'boost-plan-update',
        VIEW => '',
    ];
    const MEMBER_PLAN_STATUS = [
        URI => 'membership-status-update',
        VIEW => '',
    ];
    const BOOST_PLAN_STATUS = [
        URI => 'boost-status-update',
        VIEW => '',
    ];
    const RESTAURANT_PURCHASE_PLAN_STATUS = [
        URI => 'restaurant-plan-status-update',
        VIEW => '',
    ];
    const CHANGE_CUSTOMER = [
        URI => 'change-customer',
    ];
    const UPDATE_DISCOUNT = [
        URI => 'update-discount',
    ];
    const COUPON_DISCOUNT = [
        URI => 'coupon-discount',
    ];
    const STORE_KEY = [
        URI => 'store-key',
    ];
    const QUICK_VIEW = [
        URI => 'quick-view',
        VIEW => 'admin-views.pos.partials._quick-view'
    ];
    const SEARCH = [
        URI => 'search-product',
        VIEW => 'admin-views.pos.partials._search-product'
    ];

    const  MEMBER_PLAN_DELETE = [
        URI => 'membership/delete/{id}',
        VIEW => ''
    ];
    const  BOOST_PLAN_DELETE = [
        URI => 'boost/delete/{id}',
        VIEW => ''
    ];
}
