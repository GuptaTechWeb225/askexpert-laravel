<?php

namespace App\Enums\ViewPaths\Expert;

enum Auth
{
    const EXPERT_LOGIN = [
        URI => 'login',
        VIEW => 'expert-views.auth.login',
    ];
    const EXPERT_REGISTRATION = [
        URI => '',
        VIEW => 'expert-views.auth.sign-up',
    ];

    const EXPERT_LOGOUT = [
        URI => 'logout',
        VIEW => 'restaurant-views.auth.login'
    ];
    const RECAPTURE = [
        URI => 'recaptcha',
    ];
}
