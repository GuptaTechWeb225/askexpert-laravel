<?php

namespace App\Enums\ViewPaths\Vendor;

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
        ROUTE =>'vendor.auth.reset-password',
        VIEW => 'restaurant-views.auth.forgot-password.reset-password-view'
    ];

}
