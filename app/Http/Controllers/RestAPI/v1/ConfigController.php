<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Traits\CacheManagerTrait;
use App\Traits\MaintenanceModeTrait;
use App\Traits\SettingsTrait;
use App\Utils\Helpers;
use App\Utils\ProductManager;
use Illuminate\Http\JsonResponse;
use function App\Utils\payment_gateways;
use App\Traits\PushNotificationTrait;

class ConfigController extends Controller
{
    use SettingsTrait, MaintenanceModeTrait, CacheManagerTrait, PushNotificationTrait;
    function cleanAndFormat($html)
    {
        $html = str_ireplace(['<br>', '<br/>', '<br />'], "\n", $html);
        $html = str_ireplace(['</p>', '</div>'], "\n\n", $html);

        $html = str_ireplace(['<ul>', '<ol>'], "\n", $html);
        $html = str_ireplace('</li>', "\n", $html);
        $html = preg_replace('/<li>(.*?)<\/li>/', "• $1", $html);

        $html = preg_replace('/<b>(.*?)<\/b>/', "**$1**", $html);
        $html = preg_replace('/<strong>(.*?)<\/strong>/', "**$1**", $html);
        $html = preg_replace('/<i>(.*?)<\/i>/', "_$1_", $html);

        $html = strip_tags($html);
        return trim(preg_replace("/\n{3,}/", "\n\n", $html));
    }
    public function configuration(): JsonResponse
    {
        $socialLoginConfig = [];
        foreach (getWebConfig(name: 'social_login') as $social) {
            $config = [
                'login_medium' => $social['login_medium'],
                'status' => (bool)$social['status']
            ];
            $socialLoginConfig[] = $config;
        }

        foreach (getWebConfig(name: 'apple_login') as $social) {
            $config = [
                'login_medium' => $social['login_medium'],
                'status' => (bool)$social['status']
            ];
            $socialLoginConfig[] = $config;
        }

        $paymentMethods = payment_gateways();
        $paymentMethods->map(function ($payment) {
            $payment->additional_datas = json_decode($payment->additional_data);

            unset(
                $payment->additional_data,
                $payment->live_values,
                $payment->test_values,
                $payment->id,
                $payment->settings_type,
                $payment->mode,
                $payment->is_active,
                $payment->created_at,
                $payment->updated_at
            );
        });
        $companyLogo = getWebConfig(name: 'company_mobile_logo');

        $loginOptions = getLoginConfig(key: 'login_options');
        $socialMediaLoginOptions = getLoginConfig(key: 'social_media_for_login');

        foreach ($socialMediaLoginOptions as $socialMediaLoginKey => $socialMediaLogin) {
            $socialMediaLoginOptions[$socialMediaLoginKey] = (int)$socialMediaLogin;
        }

        $customerLogin = [
            'login_option' => $loginOptions,
            'social_media_login_options' => $socialMediaLoginOptions
        ];

        $emailVerification = getLoginConfig(key: 'email_verification') ?? 0;
        $phoneVerification = getLoginConfig(key: 'phone_verification') ?? 0;

        $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification');
        $customerVerification = [
            'status' => (int)($emailVerification == 1 || $phoneVerification == 1) ? 1 : 0,
            'phone' => (int)$phoneVerification,
            'email' => (int)$emailVerification,
            'firebase' => (int)($firebaseOTPVerification && $firebaseOTPVerification['status'] && $firebaseOTPVerification['web_api_key']),
        ];

        $systemColors = getWebConfig('colors');
        return response()->json([
            'primary_color' => $systemColors['primary'],
            'secondary_color' => $systemColors['secondary'],
            'primary_color_light' => $systemColors['primary_light'] ?? '',
            'digital_payment' => (bool)getWebConfig(name: 'digital_payment')['status'] ?? 0,
            'company_name' => getWebConfig(name: 'company_name') ?? '',
            'company_phone' => getWebConfig(name: 'company_phone') ?? '',
            'company_email' => getWebConfig(name: 'company_email') ?? '',
            'company_logo' => $companyLogo,
            'base_urls' => [
                'customer_image_url' => dynamicStorage(path: 'storage/app/public/profile'),
                'banner_image_url' => dynamicStorage(path: 'storage/app/public/banner'),
                'review_image_url' => dynamicStorage(path: 'storage/app/public'),
                'notification_image_url' => dynamicStorage(path: 'storage/app/public/notification'),
                'support_ticket_image_url' => dynamicStorage(path: 'storage/app/public/support-ticket'),
                'chatting_image_url' => dynamicStorage(path: 'storage/app/public/chatting'),
            ],
            'about_us' => $this->cleanAndFormat(getWebConfig(name: 'about_us')),
            'privacy_policy' => $this->cleanAndFormat(getWebConfig(name: 'privacy_policy')),
            'terms_&_conditions' => $this->cleanAndFormat(getWebConfig(name: 'terms_condition')),
            'faq' => $this->cacheHelpTopicTable(),
            'email_verification' => (bool)getLoginConfig(key: 'email_verification'),
            'phone_verification' => (bool)getLoginConfig(key: 'phone_verification'),
            'social_login' => $socialLoginConfig,
            'forgot_password_verification' => getWebConfig(name: 'forgot_password_verification'),
            'announcement' => getWebConfig(name: 'announcement'),
            'pixel_analytics' => getWebConfig(name: 'pixel_analytics'),
            'payment_methods' => $paymentMethods,
            'payment_method_image_path' => dynamicStorage(path: 'storage/app/public/payment_modules/gateway_image'),
            'system_timezone' => getWebConfig(name: 'timezone'),
            'map_api_status' => (int)getWebConfig(name: 'map_api_status'),
            'default_location' => getWebConfig(name: 'default_location'),
            'customer_login' => $customerLogin,
            'customer_verification' => $customerVerification,
            'otp_resend_time' => getWebConfig(name: 'otp_resend_time') ?? 60,
        ]);
    }
}
