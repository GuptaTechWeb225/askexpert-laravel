<?php

namespace App\Providers;

use App\Traits\CacheManagerTrait;
use App\Traits\FileManagerTrait;
use App\Traits\UpdateClass;
use App\Models\LoginSetup;
use App\Utils\Helpers;
use App\Enums\GlobalConstant;
use App\Models\Currency;
use App\Traits\AddonHelper;
use App\Traits\ThemeHelper;
use App\Utils\ProductManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\User;
use App\Models\SocialMedia;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

ini_set('memory_limit', -1);
ini_set('upload_max_filesize', '180M');
ini_set('post_max_size', '200M');

class AppServiceProvider extends ServiceProvider
{

    use AddonHelper;
    use CacheManagerTrait;
    use FileManagerTrait;
    use ThemeHelper;
    use UpdateClass;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Amirami\Localizator\ServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot(): void
    {


        Activity::saving(function (Activity $activity) {

            if (auth('admin')->check()) {
                $activity->causer()->associate(auth('admin')->user());
                $activity->log_name = 'admin';
            } elseif (auth('expert')->check()) {
                $activity->causer()->associate(auth('expert')->user());
                $activity->log_name = 'expert';
            } elseif (auth('customer')->check()) {
                $activity->causer()->associate(auth('customer')->user());
                $activity->log_name = 'customer';
            } elseif (auth()->check()) {
                $activity->causer()->associate(auth()->user());
                $activity->log_name = 'user';
            }
        });

        View::addLocation(resource_path('themes'));

        if (!in_array(request()->ip(), ['127.0.0.1', '::1']) && env('FORCE_HTTPS')) {
            \URL::forceScheme('https');
        }

        if (!defined('DOMAIN_POINTED_DIRECTORY')) {
            define('DOMAIN_POINTED_DIRECTORY', 'public');
        }
        if (!App::runningInConsole()) {
            Paginator::useBootstrap();

            Config::set('addon_admin_routes', $this->getAddonAdminRoutes());
            Config::set('get_payment_publish_status', $this->getPaymentPublishStatus());
            Config::set('get_theme_routes', $this->getThemeRoutesArray());

            try {
                if (Schema::hasTable('business_settings')) {
                    $this->setStorageConnectionEnvironment();
                    $web = $this->cacheBusinessSettingsTable();

                    $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification');
                    $firebaseOTPVerificationStatus = (int)($firebaseOTPVerification && $firebaseOTPVerification['status'] && $firebaseOTPVerification['web_api_key']);

                    $systemColors = getWebConfig('colors');
                    $web_config = [
                        'primary_color' => $systemColors['primary'] ?? '',
                        'secondary_color' => $systemColors['secondary'] ?? '',
                        'primary_color_light' => $systemColors['primary_light'] ?? '',
                        'name' => Helpers::get_settings($web, 'company_name'),
                        'company_name' => getWebConfig(name: 'company_name'),
                        'meta_description' => getWebConfig(name: 'meta_description'),
                        'phone' => getWebConfig(name: 'company_phone'),
                        'web_logo' => getWebConfig(name: 'company_web_logo'),
                        'mob_logo' => getWebConfig(name: 'company_mobile_logo'),
                        'business_mode' => getWebConfig(name: 'business_mode'),
                        'fav_icon' => getWebConfig(name: 'company_fav_icon'),
                        'email' => getWebConfig(name: 'company_email'),
                        'about' => Helpers::get_settings($web, 'about_us'),
                        'footer_logo' => getWebConfig(name: 'company_footer_logo'),
                        'copyright_text' => getWebConfig(name: 'company_copyright_text'),
                        'decimal_point_settings' => !empty(getWebConfig(name: 'decimal_point_settings')) ? getWebConfig(name: 'decimal_point_settings') : 0,
                        'guest_checkout_status' => getWebConfig(name: 'guest_checkout'),
                        'language' => getWebConfig(name: 'language'),
                        'firebase_otp_verification' => $firebaseOTPVerification,
                        'firebase_otp_verification_status' => $firebaseOTPVerificationStatus,
                    ];

                    if ((!Request::is('admin') && !Request::is('admin/*') && !Request::is('seller/*') && !Request::is('vendor/*')) || Request::is('vendor/auth/registration/*')) {
                        $userId = Auth::guard('customer')->user() ? Auth::guard('customer')->id() : 0;

                        $recaptcha = getWebConfig(name: 'recaptcha');
                        $paymentGatewayPublishedStatus = config('get_payment_publish_status') ?? 0;


                        $customerLoginOptions = LoginSetup::where(['key' => 'login_options'])->first()?->value ?? '';
                        $customerSocialLoginOptions = LoginSetup::where(['key' => 'social_media_for_login'])->first()?->value ?? '';
                        $customerSocialLoginOptions = json_decode($customerSocialLoginOptions, true) ?? [];
                        $socialLoginTextShowStatus = false;
                        foreach ($customerSocialLoginOptions as $socialLoginService) {
                            if ($socialLoginService == 1) {
                                $socialLoginTextShowStatus = true;
                            }
                        }

                        $web_config += [
                            'cookie_setting' => Helpers::get_settings($web, 'cookie_setting'),
                            'currency_model' => getWebConfig(name: 'currency_model'),
                            'currencies' => Currency::where(['status' => 1])->get(),
                            'refund_policy' => getWebConfig(name: 'refund-policy'),
                            'return_policy' => getWebConfig(name: 'return-policy'),
                            'cancellation_policy' => getWebConfig(name: 'cancellation-policy'),
                            'recaptcha' => $recaptcha,
                            'socials_login' => getWebConfig(name: 'social_login'),
                            'customer_phone_verification' => getLoginConfig(key: 'phone_verification'),
                            'customer_email_verification' => getLoginConfig(key: 'email_verification'),
                            'default_meta_content' => $this->cacheRobotsMetaContent(page: 'default'),
                            'analytic_scripts' => $this->cacheActiveAnalyticScript(),
                            'customer_social_login_options' => $customerSocialLoginOptions,
                            'customer_login_options' => json_decode($customerLoginOptions, true),
                            'social_media' => SocialMedia::where('active_status', 1)->get(),
                        ];
                    }

                    // Language
                    $language = getWebConfig(name: 'language') ?? [];

                    View::share(['web_config' => $web_config, 'language' => $language]);

                    Schema::defaultStringLength(191);
                }
            } catch (\Exception $exception) {
                Log::warning('Web config share failed', ['error' => $exception->getMessage()]);
            }
        }

        $blogViewPath = base_path('Modules/Blog/resources/views');

        if (is_dir($blogViewPath)) {
            $this->loadViewsFrom($blogViewPath, 'blog');
        }

        /**
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return array
         */

        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });
    }
}
