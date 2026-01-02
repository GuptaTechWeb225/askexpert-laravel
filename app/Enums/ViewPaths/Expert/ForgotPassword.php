<?php

namespace App\Enums\ViewPaths\Restaurant;

enum ForgotPassword
{
    const INDEX = [
      URI => 'index',
      VIEW => 'restaurant-views.auth.forgot-password.index'
    ];
    const OTP_VERIFICATION = [
      URI => 'otp-verification',
      VIEW => 'restaurant-views.auth.forgot-password.verify-otp-view'
    ];
    const RESET_PASSWORD = [
        URI => 'reset-password',
        ROUTE =>'restaurant.auth.reset-password',
        VIEW => 'restaurant-views.auth.forgot-password.reset-password-view'
    ];

}
