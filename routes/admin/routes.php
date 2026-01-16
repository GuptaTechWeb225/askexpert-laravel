<?php

use App\Enums\ViewPaths\Admin\FirebaseOTPVerification;
use App\Enums\ViewPaths\Admin\Mail;
use App\Enums\ViewPaths\Admin\Pages;
use App\Http\Controllers\Admin\Settings\FirebaseOTPVerificationController;
use App\Http\Controllers\FirebaseController;
use Illuminate\Support\Facades\Route;
use App\Enums\ViewPaths\Admin\Contact;
use App\Enums\ViewPaths\Admin\Profile;
use App\Enums\ViewPaths\Admin\SiteMap;
use App\Enums\ViewPaths\Admin\Chatting;
use App\Enums\ViewPaths\Admin\Currency;
use App\Enums\ViewPaths\Admin\Customer;
use App\Enums\ViewPaths\Admin\Employee;
use App\Enums\ViewPaths\Admin\Dashboard;
use App\Enums\ViewPaths\Admin\ErrorLogs;
use App\Enums\ViewPaths\Admin\Notifications;
use App\Enums\ViewPaths\Admin\HelpTopic;
use App\Enums\ViewPaths\Admin\Recaptcha;
use App\Enums\ViewPaths\Admin\BackupRestore;
use App\Enums\ViewPaths\Admin\SMSModule;
use App\Enums\ViewPaths\Admin\AddonSetup;
use App\Enums\ViewPaths\Admin\Transaction;
use App\Enums\ViewPaths\Admin\CustomRole;
use App\Enums\ViewPaths\Admin\FileManager;
use App\Enums\ViewPaths\Admin\SEOSettings;
use App\Enums\ViewPaths\Admin\SocialMedia;
use App\Enums\ViewPaths\Admin\SystemSetup;
use App\Enums\ViewPaths\Admin\SocialMediaChat;
use App\Http\Controllers\SharedController;
use App\Enums\ViewPaths\Admin\GoogleMapAPI;
use App\Enums\ViewPaths\Admin\Notification;
use App\Enums\ViewPaths\Admin\EmailTemplate;
use App\Enums\ViewPaths\Admin\PaymentMethod;
use App\Enums\ViewPaths\Admin\SupportTicket;
use App\Enums\ViewPaths\Admin\CustomerWallet;
use App\Enums\ViewPaths\Admin\DatabaseSetting;
use App\Enums\ViewPaths\Admin\FeaturesSection;
use App\Enums\ViewPaths\Admin\BusinessSettings;
use App\Enums\ViewPaths\Admin\PushNotification;
use App\Enums\ViewPaths\Admin\NotificationSetup;
use App\Enums\ViewPaths\Admin\RobotsMetaContent;
use App\Http\Controllers\Admin\ProfileController;
use App\Enums\ViewPaths\Admin\Restaurant;
use App\Enums\ViewPaths\Admin\Expert;
use App\Http\Controllers\Admin\Settings\PagesController;
use App\Http\Controllers\Admin\Settings\FeaturesSectionController;
use App\Http\Controllers\Admin\Settings\EnvironmentSettingsController;
use App\Enums\ViewPaths\Admin\EnvironmentSettings;
use App\Enums\ViewPaths\Admin\SocialLoginSettings;
use App\Http\Controllers\Admin\ChattingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Enums\ViewPaths\Admin\StorageConnectionSettings;
use App\Http\Controllers\Admin\EmailTemplatesController;
use App\Http\Controllers\Admin\Settings\AddonController;
use App\Http\Controllers\Admin\ThirdParty\MailController;
use App\Http\Controllers\Admin\Settings\SiteMapController;
use App\Http\Controllers\Admin\Customer\CustomerController;
use App\Http\Controllers\Admin\Employee\EmployeeController;
use App\Http\Controllers\Admin\Settings\CurrencyController;
use App\Http\Controllers\Admin\Settings\ErrorLogsController;
use App\Http\Controllers\Admin\Employee\CustomRoleController;
use App\Http\Controllers\Admin\Settings\FileManagerController;
use App\Http\Controllers\Admin\Settings\SEOSettingsController;
use App\Http\Controllers\Admin\ThirdParty\RecaptchaController;
use App\Http\Controllers\Admin\ThirdParty\SMSModuleController;
use App\Http\Controllers\Admin\HelpAndSupport\ContactController;
use App\Http\Controllers\Admin\Customer\CustomerWalletController;
use App\Http\Controllers\Admin\ThirdParty\GoogleMapAPIController;
use App\Http\Controllers\Admin\Customer\CustomerLoyaltyController;
use App\Http\Controllers\Admin\HelpAndSupport\HelpTopicController;
use App\Http\Controllers\Admin\Settings\DatabaseSettingController;
use App\Http\Controllers\Admin\ThirdParty\PaymentMethodController;
use App\Http\Controllers\Admin\Notification\NotificationController;
use App\Http\Controllers\Admin\Settings\BusinessSettingsController;
use App\Http\Controllers\Admin\Settings\RobotsMetaContentController;
use App\Http\Controllers\Admin\ThirdParty\SocialMediaChatController;
use App\Http\Controllers\Admin\HelpAndSupport\SupportTicketController;
use App\Http\Controllers\Admin\Settings\SocialMediaSettingsController;
use App\Http\Controllers\Admin\SystemSetup\SystemLoginSetupController;
use App\Http\Controllers\Admin\Notification\NotificationSetupController;
use App\Http\Controllers\Admin\ThirdParty\SocialLoginSettingsController;
use App\Http\Controllers\Admin\Backup\BackupController;
use App\Http\Controllers\Admin\Settings\StorageConnectionSettingsController;
use App\Http\Controllers\Admin\Notification\PushNotificationSettingsController;
use App\Http\Controllers\Admin\Plan\PlanController;
use App\Http\Controllers\Admin\Transaction\TransactionController;
use App\Http\Controllers\Admin\Cms\HomeController;
use App\Http\Controllers\Admin\Cms\AfterLoginCmsController;
use App\Http\Controllers\Admin\Cms\AboutController;
use App\Http\Controllers\Admin\Cms\HelpController;
use App\Http\Controllers\Admin\ExpertCategoryController;
use App\Http\Controllers\Admin\TaskNotificationsController;
use App\Http\Controllers\Admin\AdminRefundController;
use App\Http\Controllers\Admin\Cms\ExpertCmsController;
use App\Http\Controllers\Admin\Expert\ExpertController;
use App\Http\Controllers\Admin\Expert\ExpertPayoutController;
use App\Http\Controllers\Admin\Questions\QuestionController;
use App\Http\Controllers\Admin\Expert\ExpertChatController;
use App\Http\Controllers\Admin\Cms\PricingController;
use App\Http\Controllers\Admin\Job\JobScheduleController;

Route::controller(SharedController::class)->group(function () {
    Route::post('change-language', 'changeLanguage')->name('change-language');
    Route::post('get-session-recaptcha-code', 'getSessionRecaptchaCode')->name('get-session-recaptcha-code');
    Route::post('g-recaptcha-response-store', 'storeRecaptchaResponse')->name('g-recaptcha-response-store');
});

Route::controller(FirebaseController::class)->group(function () {
    Route::post('system/subscribe-to-topic', 'subscribeToTopic')->name('system.subscribeToTopic');
});

Route::group(['prefix' => 'login'], function () {
    Route::get('{loginUrl}', [LoginController::class, 'index']);
    Route::get('recaptcha/{tmp}', [LoginController::class, 'generateReCaptcha'])->name('recaptcha');
    Route::post('/', [LoginController::class, 'login'])->name('login');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['admin']], function () {
    Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
        Route::controller(DashboardController::class)->group(function () {
            Route::get(Dashboard::VIEW[URI], 'index')->name('index');
            Route::get(Dashboard::GRAPH_DATA[URI], 'graphData')->name('graph-data');
        });
    });
    Route::group(['prefix' => 'content-management', 'as' => 'content-management.'], function () {
        Route::controller(HomeController::class)->group(function () {
            Route::get('/home',  'index')->name('home');
        });
        Route::controller(AfterLoginCmsController::class)->group(function () {
            Route::get('/after-login',  'index')->name('after-login');
        });
        Route::controller(AboutController::class)->group(function () {
            Route::get('/about',  'index')->name('about');
        });
        Route::controller(HelpController::class)->group(function () {
            Route::get('/help',  'index')->name('help');
        });
        Route::controller(ExpertCmsController::class)->group(function () {
            Route::get('/expert-cms',  'index')->name('expert');
        });
        Route::controller(PricingController::class)->group(function () {
            Route::get('/pricing',  'index')->name('pricing');
        });
    });

    Route::resource('expert-category', ExpertCategoryController::class);
    Route::post('expert-category/toggle/{id}', [ExpertCategoryController::class, 'toggleStatus'])->name('expert-category.toggle');



    Route::group(['prefix' => 'home-cms', 'as' => 'home-cms.'], function () {
        Route::controller(HomeController::class)->group(function () {
            Route::get('{section}/add',  'addItem')->name('add');
            Route::get('edit-data/{section}/{item_id}',  'edit')->name('edit');
            Route::post('{section}/{item_id?}', 'update')->name('update');
            Route::delete('{section}/{item_id}',  'destroy')->name('destroy');
        });
    });
    Route::group(['prefix' => 'after-login-cms', 'as' => 'after-login-cms.'], function () {
        Route::controller(AfterLoginCmsController::class)->group(function () {
            Route::get('{section}/add',  'addItem')->name('add');
            Route::get('edit-data/{section}/{item_id}',  'edit')->name('edit');
            Route::post('{section}/{item_id?}', 'update')->name('update');
            Route::delete('{section}/{item_id}',  'destroy')->name('destroy');
        });
    });

    Route::controller(AboutController::class)->group(function () {
        Route::group(['prefix' => 'about-cms', 'as' => 'about-cms.'], function () {
            Route::get('/edit-data/{section}/{item_id}', 'editData');
            Route::post('/{section}/{item_id?}', 'update')->name('update');
            Route::delete('/destroy/{section}/{item_id}', 'destroy')->name('destroy');
            Route::post('/toggle-status', 'toggleStatus')->name('toggle-status');
        });
    });
    Route::controller(ExpertCmsController::class)->group(function () {
        Route::group(['prefix' => 'expert-cms', 'as' => 'expert-cms.'], function () {
            Route::get('/edit-data/{section}/{item_id?}'::class, 'editData')->name('edit-data');
            Route::post('/{section}/{item_id?}'::class, 'update')->name('update');
            Route::delete('/{section}/{item_id}'::class, 'destroy')->name('destroy');
            Route::post('/toggle-status'::class, 'toggleStatus')->name('toggle-status');
        });
    });
    Route::controller(HelpController::class)->group(function () {
        Route::group(['prefix' => 'help-cms', 'as' => 'help-cms.'], function () {
            Route::get('/edit-data/{section}/{item_id?}',  'editData');
            Route::post('/{section}/{item_id?}',  'update')->name('update');
            Route::delete('/destroy/{section}/{item_id}',  'destroy')->name('destroy');
        });
    });

    Route::controller(PricingController::class)->group(function () {
        Route::prefix('pricing-cms')->name('pricing-cms.')->group(function () {
            Route::get('/'::class, 'index')->name('index');
            Route::get('/edit-data/{section}/{item_id?}',  'editData')->name('edit');
            Route::post('/{section}/{item_id?}',  'update')->name('update');
            Route::delete('/destroy/{section}/{item_id}',  'destroy')->name('destroy');
            Route::post('/toggle-status',  'toggleStatus')->name('toggle-status');
        });
    });


    Route::get('logout', [LoginController::class, 'logout'])->name('logout');

    Route::group(['prefix' => 'expert', 'as' => 'expert.'], function () {
        Route::controller(ExpertController::class)->group(function () {
            Route::get(Expert::EXPERTS[URI], 'index')->name('index');
            Route::get(Expert::EXPERT_VIEW[URI] . '/{id}', 'expertView')->name('view');
            Route::get(Expert::EXPERT_REQUEST[URI], 'expertRequest')->name('request');
            Route::post(Expert::EXPERT_APPROVE[URI], 'requestApprove')->name('approve');
            Route::post(Expert::EXPERT_REJECT[URI], 'requestReject')->name('reject');
            Route::post(Expert::EXPERT_STATUS[URI], 'expertStatus')->name('status');
        });
    });

    Route::group(['prefix' => 'expert-payouts', 'as' => 'expert-payouts.'], function () {
        Route::controller(ExpertPayoutController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('view/{expert}',  'viewExpertPayout')->name('view');
            Route::get('setup/{expert}',  'setupPayout')->name('setup');
            Route::post('pay',  'payEarnings')->name('pay');
        });
    });

    Route::group(['prefix' => 'expert-chat', 'as' => 'expert-chat.'], function () {
        Route::controller(ExpertChatController::class)->group(function () {
            Route::get(Expert::EXPERT_CHATS[URI], 'index')->name('index');
            Route::get(Expert::EXPERT_MASSAGE[URI] . '/{expertId}', 'getMessages');
            Route::post(Expert::MASSAGE_SEND[URI], 'sendMessage');
            Route::post(Expert::MASSAGE_READ[URI], 'markRead');
        });
    });
    Route::group(['prefix' => 'expert', 'as' => 'expert.'], function () {
        Route::get('questions', [QuestionController::class, 'questions'])->name('questions');
        Route::get('miscategorized', [QuestionController::class, 'missCategorieQuo'])->name('miscategorized');
        Route::get('question/detail/{id}', [QuestionController::class, 'detail'])->name('question.detail');
        Route::post('question/assign-expert', [QuestionController::class, 'assignExpert'])->name('question.assign-expert');
        Route::post('question/assign-category', [QuestionController::class, 'assignCategory'])->name('question.assign-category');
    });

    Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::get(Profile::INDEX[URI], 'index')->name('index');
            Route::get(Profile::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            Route::post(Profile::UPDATE[URI] . '/{id}', 'update');
            Route::patch(Profile::UPDATE[URI] . '/{id}', 'updatePassword');
        });
    });
    Route::group(['prefix' => 'backup', 'as' => 'backup.'], function () {
        Route::controller(BackupController::class)->group(function () {
            Route::get(BackupRestore::INDEX[URI], 'index')->name('index');
            Route::get(BackupRestore::DOWNLOAD[URI] . '/{file}', 'download')->where('file', '.*')->name('download');
            Route::post(BackupRestore::DELETE[URI] . '/{file}', 'delete')->name('delete');
            Route::post(BackupRestore::RUN[URI], 'run')->name('run');
        });
    });
    Route::group(['prefix' => 'refunds', 'as' => 'refunds.'], function () {
        Route::controller(AdminRefundController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}', 'view')->name('view');
            Route::post('/{id}/process', 'processRefund')->name('update');
            Route::post('/{id}/reject', 'rejectRefund')->name('reject');
            Route::get('/{id}/data',  'getRefundData')->name('data');
        });
    });


    // Customer Routes, Customer wallet Routes, Customer Loyalty Routes
    Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['module:user_section']], function () {
        Route::controller(CustomerController::class)->group(function () {
            Route::get(Customer::LIST[URI], 'getListView')->name('list');
            Route::get(Customer::ANALYTICS[URI], 'getAnalyticsView')->name('analytics');
            Route::get(Customer::VIEW[URI] . '/{user_id}', 'getView')->name('view');
            Route::get(Customer::ORDER_LIST_EXPORT[URI] . '/{user_id}', 'exportOrderList')->name('order-list-export');
            Route::post(Customer::UPDATE[URI], 'updateStatus')->name('status-update');
            Route::delete(Customer::DELETE[URI], 'delete')->name('delete');
            Route::get(Customer::SUBSCRIBER_LIST[URI], 'getSubscriberListView')->name('subscriber-list');
            Route::get(Customer::SUBSCRIBER_EXPORT[URI], 'exportSubscribersList')->name('subscriber-list.export');
            Route::get(Customer::EXPORT[URI], 'exportList')->name('export');
            Route::get(Customer::SEARCH[URI], 'getCustomerList')->name('customer-list-search');
            Route::get(Customer::SEARCH_WITHOUT_ALL_CUSTOMER[URI], 'getCustomerListWithoutAllCustomerName')->name('customer-list-without-all-customer');
            Route::post(Customer::ADD[URI], 'add')->name('add');
        });

        Route::group(['prefix' => 'wallet', 'as' => 'wallet.'], function () {
            Route::controller(CustomerWalletController::class)->group(function () {
                Route::get(CustomerWallet::REPORT[URI], 'index')->name('report');
                Route::post(CustomerWallet::ADD[URI], 'addFund')->name('add-fund');
                Route::get(CustomerWallet::EXPORT[URI], 'exportList')->name('export');
                Route::get(CustomerWallet::BONUS_SETUP[URI], 'getBonusSetupView')->name('bonus-setup');
                Route::post(CustomerWallet::BONUS_SETUP[URI], 'addBonusSetup');
                Route::post(CustomerWallet::BONUS_SETUP_UPDATE[URI], 'update')->name('bonus-setup-update');
                Route::post(CustomerWallet::BONUS_SETUP_STATUS[URI], 'updateStatus')->name('bonus-setup-status');
                Route::get(CustomerWallet::BONUS_SETUP_EDIT[URI] . '/{id}', 'getUpdateView')->name('bonus-setup-edit');
                Route::delete(CustomerWallet::BONUS_SETUP_DELETE[URI], 'deleteBonus')->name('bonus-setup-delete');
            });
        });

        Route::group(['prefix' => 'loyalty', 'as' => 'loyalty.'], function () {
            Route::controller(CustomerLoyaltyController::class)->group(function () {
                Route::get(Customer::LOYALTY_REPORT[URI], 'index')->name('report');
                Route::get(Customer::LOYALTY_EXPORT[URI], 'exportList')->name('export');
            });
        });
    });



    Route::group(['middleware' => ['module:system_settings']], function () {
        Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
            Route::controller(CustomerController::class)->group(function () {
                Route::get(Customer::SETTINGS[URI], 'getCustomerSettingsView')->name('customer-settings');
                Route::post(Customer::SETTINGS[URI], 'update');
            });
        });
    });

    Route::group(['prefix' => 'job-schedule', 'as' => 'job-schedule.'], function () {
        Route::controller(JobScheduleController::class)->group(function () {
            Route::get(Employee::LIST[URI], 'index')->name('list');
            Route::get(Employee::ADD[URI], 'getAddView')->name('add-new');
            Route::post(Employee::ADD[URI], 'add')->name('add-new-post');
            Route::get(Employee::EXPORT[URI], 'exportList')->name('export');
            Route::get(Employee::VIEW[URI] . '/{id}', 'getView')->name('view');
            Route::get(Employee::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            Route::post(Employee::UPDATE[URI] . '/{id}', 'update');
            Route::post(Employee::STATUS[URI], 'updateStatus')->name('status');
        });
    });





    Route::group(['prefix' => 'employee', 'as' => 'employee.'], function () {
        Route::controller(EmployeeController::class)->group(function () {
            Route::get(Employee::LIST[URI], 'index')->name('list');
            Route::get(Employee::ADD[URI], 'getAddView')->name('add-new');
            Route::post(Employee::ADD[URI], 'add')->name('add-new-post');
            Route::get(Employee::EXPORT[URI], 'exportList')->name('export');
            Route::get(Employee::VIEW[URI] . '/{id}', 'getView')->name('view');
            Route::get(Employee::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            Route::post(Employee::UPDATE[URI] . '/{id}', 'update');
            Route::post(Employee::STATUS[URI], 'updateStatus')->name('status');
        });
    });

    Route::group(['prefix' => 'custom-role', 'as' => 'custom-role.', 'middleware' => ['module:user_section']], function () {
        Route::controller(CustomRoleController::class)->group(function () {
            Route::get(CustomRole::ADD[URI], 'index')->name('create');
            Route::post(CustomRole::ADD[URI], 'add')->name('store');
            Route::get(CustomRole::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            Route::post(CustomRole::UPDATE[URI] . '/{id}', 'update');
            Route::post(CustomRole::STATUS[URI], 'updateStatus')->name('employee-role-status');
            Route::post(CustomRole::DELETE[URI], 'delete')->name('delete');
            Route::get(CustomRole::EXPORT[URI], 'exportList')->name('export');
        });
    });

    /*  report */




    /** Notification and push notification */
    Route::group(['prefix' => 'push-notification', 'as' => 'push-notification.', 'middleware' => ['module:promotion_management']], function () {
        Route::controller(PushNotificationSettingsController::class)->group(function () {
            Route::get(PushNotification::INDEX[URI], 'index')->name('index');
            Route::post(PushNotification::UPDATE[URI], 'updatePushNotificationMessage')->name('update');
            Route::get(PushNotification::FIREBASE_CONFIGURATION[URI], 'getFirebaseConfigurationView')->name('firebase-configuration');
            Route::post(PushNotification::FIREBASE_CONFIGURATION[URI], 'getFirebaseConfigurationUpdate')->name('update-firebase-configuration');
        });
    });

    Route::group(['prefix' => 'notification', 'as' => 'notification.', 'middleware' => ['module:promotion_management']], function () {
        Route::controller(NotificationController::class)->group(function () {
            Route::get(Notification::INDEX[URI], 'index')->name('index');
            Route::post(Notification::INDEX[URI], 'add');
            Route::get(Notification::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            Route::post(Notification::UPDATE[URI] . '/{id}', 'update');
            Route::post(Notification::DELETE[URI], 'delete')->name('delete');
            Route::post(Notification::UPDATE_STATUS[URI], 'updateStatus')->name('update-status');
            Route::post(Notification::RESEND_NOTIFICATION[URI], 'resendNotification')->name('resend-notification');
        });
    });

    Route::group(['prefix' => 'notification-setup', 'as' => 'notification-setup.', 'middleware' => ['module:promotion_management']], function () {
        Route::controller(NotificationSetupController::class)->group(function () {
            Route::get(NotificationSetup::INDEX[URI] . '/{type}', 'index')->name('index');
        });
    });
    /* end notification */

    Route::group(['prefix' => 'support-ticket', 'as' => 'support-ticket.', 'middleware' => ['module:support_section']], function () {
        Route::controller(SupportTicketController::class)->group(function () {
            Route::get(SupportTicket::LIST[URI], 'index')->name('view');
            Route::post(SupportTicket::STATUS[URI], 'updateStatus')->name('status');
            Route::get(SupportTicket::VIEW[URI] . '/{id}', 'getView')->name('singleTicket');
            Route::post(SupportTicket::VIEW[URI] . '/{id}', 'reply')->name('replay');
        });
    });

    Route::group(['prefix' => 'messages', 'as' => 'messages.'], function () {
        Route::controller(ChattingController::class)->group(function () {
            Route::get(Chatting::INDEX[URI] . '/{type}', 'index')->name('index');
            Route::get(Chatting::MESSAGE[URI], 'getMessageByUser')->name('message');
            Route::post(Chatting::MESSAGE[URI], 'addAdminMessage');
            Route::get(Chatting::NEW_NOTIFICATION[URI], 'getNewNotification')->name('new-notification');
        });
    });

    Route::group(['prefix' => 'contact', 'as' => 'contact.', 'middleware' => ['module:support_section']], function () {
        Route::controller(ContactController::class)->group(function () {
            Route::get(Contact::LIST[URI], 'index')->name('list');
            Route::get(Contact::VIEW[URI] . '/{id}', 'getView')->name('view');
            Route::post(Contact::FILTER[URI], 'getListByFilter')->name('filter');
            Route::post(Contact::DELETE[URI], 'delete')->name('delete');
            Route::post(Contact::UPDATE[URI] . '/{id}', 'update')->name('update');
            Route::post(Contact::ADD[URI], 'add')->name('store');
            Route::post(Contact::SEND_MAIL[URI] . '/{id}', 'sendMail')->name('send-mail');
        });
    });



    Route::group(['prefix' => 'system-setup', 'as' => 'system-setup.'], function () {
        Route::group(['middleware' => ['module:system_settings']], function () {

            Route::group(['prefix' => 'login-settings', 'as' => 'login-settings.'], function () {
                Route::controller(SystemLoginSetupController::class)->group(function () {
                    Route::get(SystemSetup::CUSTOMER_LOGIN_SETUP[URI], 'getCustomerLoginSetupView')->name('customer-login-setup');
                    Route::post(SystemSetup::CUSTOMER_LOGIN_SETUP[URI], 'updateCustomerLoginSetup');
                    Route::post(SystemSetup::CUSTOMER_CONFIG_VALIDATION[URI], 'getConfigValidation')->name('config-status-validation');

                    Route::get(SystemSetup::OTP_SETUP[URI], 'getOtpSetupView')->name('otp-setup');
                    Route::post(SystemSetup::OTP_SETUP[URI], 'updateOtpSetup');

                    Route::get(SystemSetup::LOGIN_URL_SETUP[URI], 'getLoginSetupView')->name('login-url-setup');
                    Route::post(SystemSetup::LOGIN_URL_SETUP[URI], 'updateLoginSetupView');
                });
            });
        });
    });

    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {
        Route::group(['middleware' => ['module:system_settings']], function () {

            Route::controller(PagesController::class)->group(function () {
                Route::get(Pages::TERMS_CONDITION[URI], 'index')->name('terms-condition');
                Route::post(Pages::TERMS_CONDITION[URI], 'updateTermsCondition')->name('update-terms');

                Route::get(Pages::PRIVACY_POLICY[URI], 'getPrivacyPolicyView')->name('privacy-policy');
                Route::post(Pages::PRIVACY_POLICY[URI], 'updatePrivacyPolicy')->name('privacy-policy-update');

                Route::get(Pages::ABOUT_US[URI], 'getAboutUsView')->name('about-us');
                Route::post(Pages::ABOUT_US[URI], 'updateAboutUs')->name('about-update');

                Route::get(Pages::DISPATCH[URI], 'getDispatchView')->name('dispatch-view');
                Route::post(Pages::DISPATCH[URI], 'updateDispatch')->name('dispatch-update');

                Route::get(Pages::VIEW[URI] . '/{page}', 'getPageView')->name('page');
                Route::post(Pages::VIEW[URI] . '/{page}', 'updatePage')->name('page-update');
            });

            Route::controller(SocialMediaSettingsController::class)->group(function () {
                Route::get(SocialMedia::VIEW[URI], 'index')->name('social-media');
                Route::get(SocialMedia::LIST[URI], 'getList')->name('fetch');
                Route::post(SocialMedia::ADD[URI], 'add')->name('social-media-store');
                Route::post(SocialMedia::GET_UPDATE[URI], 'getUpdate')->name('social-media-edit');
                Route::post(SocialMedia::UPDATE[URI], 'update')->name('social-media-update');
                Route::post(SocialMedia::DELETE[URI], 'delete')->name('social-media-delete');
                Route::post(SocialMedia::STATUS[URI], 'updateStatus')->name('social-media-status-update');
            });

            Route::controller(BusinessSettingsController::class)->group(function () {
                Route::post(BusinessSettings::MAINTENANCE_MODE[URI], 'updateSystemMode')->name('maintenance-mode');

                Route::get(BusinessSettings::COOKIE_SETTINGS[URI], 'getCookieSettingsView')->name('cookie-settings');
                Route::post(BusinessSettings::COOKIE_SETTINGS[URI], 'updateCookieSetting');

                Route::get(BusinessSettings::ANALYTICS_INDEX[URI], 'getAnalyticsView')->name('analytics-index');
                Route::post(BusinessSettings::ANALYTICS_UPDATE[URI], 'updateAnalytics')->name('analytics-update');
            });

            Route::controller(RecaptchaController::class)->group(function () {
                Route::get(Recaptcha::VIEW[URI], 'index')->name('captcha');
                Route::post(Recaptcha::VIEW[URI], 'update');
            });

            Route::controller(GoogleMapAPIController::class)->group(function () {
                Route::get(GoogleMapAPI::VIEW[URI], 'index')->name('map-api');
                Route::post(GoogleMapAPI::VIEW[URI], 'update');
            });

            Route::controller(FeaturesSectionController::class)->group(function () {
                Route::get(FeaturesSection::VIEW[URI], 'index')->name('features-section');
                Route::post(FeaturesSection::UPDATE[URI], 'update')->name('features-section.submit');
                Route::post(FeaturesSection::DELETE[URI], 'delete')->name('features-section.icon-remove');

                Route::get(FeaturesSection::COMPANY_RELIABILITY[URI], 'getCompanyReliabilityView')->name('company-reliability');
                Route::post(FeaturesSection::COMPANY_RELIABILITY[URI], 'updateCompanyReliability');
            });
        });

        Route::group(['prefix' => 'web-config', 'as' => 'web-config.', 'middleware' => ['module:system_settings']], function () {
            Route::controller(BusinessSettingsController::class)->group(function () {
                Route::get(BusinessSettings::INDEX[URI], 'index')->name('index');
                Route::post(BusinessSettings::INDEX[URI], 'updateSettings')->name('update');

                Route::get(BusinessSettings::APP_SETTINGS[URI], 'getAppSettingsView')->name('app-settings');
                Route::post(BusinessSettings::APP_SETTINGS[URI], 'updateAppSettings');
            });

            Route::controller(EnvironmentSettingsController::class)->group(function () {
                Route::get(EnvironmentSettings::VIEW[URI], 'index')->name('environment-setup');
                Route::post(EnvironmentSettings::VIEW[URI], 'update');
                Route::post(EnvironmentSettings::FORCE_HTTPS[URI], 'updateForceHttps')->name('environment-https-setup');
                Route::post(EnvironmentSettings::OPTIMIZE_SYSTEM[URI], 'optimizeSystem')->name('optimize-system');
                Route::post(EnvironmentSettings::INSTALL_PASSPORT[URI], 'installPassport')->name('install-passport');
            });

            Route::controller(DatabaseSettingController::class)->group(function () {
                Route::get(DatabaseSetting::VIEW[URI], 'index')->name('db-index');
                Route::post(DatabaseSetting::DELETE[URI], 'delete')->name('clean-db');
            });
        });
    });
    Route::group(['prefix' => 'transaction', 'as' => 'transaction.', 'middleware' => ['module:pos_management']], function () {
        Route::controller(TransactionController::class)->group(function () {
            Route::get(Transaction::INDEX[URI], 'index')->name('index');
            Route::get(Transaction::ANALYTICS_VIEW[URI], 'getAnalyticsView')->name('analytics');
            Route::get(Transaction::EXPORT[URI], 'export')->name('export');
        });
    });
    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {

        Route::group(['middleware' => ['module:system_settings']], function () {
            Route::controller(SMSModuleController::class)->group(function () {
                Route::get(SMSModule::VIEW[URI], 'index')->name('sms-module');
                Route::put(SMSModule::UPDATE[URI], 'update')->name('addon-sms-set');
            });
        });



        Route::group(['prefix' => 'mail', 'as' => 'mail.', 'middleware' => ['module:system_settings']], function () {
            Route::controller(MailController::class)->group(function () {
                Route::get(Mail::VIEW[URI], 'index')->name('index');
                Route::post(Mail::UPDATE[URI], 'update')->name('update');
                Route::post(Mail::UPDATE_SENDGRID[URI], 'updateSendGrid')->name('update-sendgrid');
                Route::post(Mail::SEND[URI], 'send')->name('send');
            });
        });

        Route::group(['prefix' => 'pythonapi', 'as' => 'pythonapi.', 'middleware' => ['module:system_settings']], function () {
            Route::controller(BusinessSettingsController::class)->group(function () {
                Route::get('/', 'pythoConfig')->name('index');
                Route::post('update', 'pythonUpdate')->name('update');
            });
        });



        Route::group(['prefix' => 'payment-method', 'as' => 'payment-method.', 'middleware' => ['module:system_settings']], function () {
            Route::controller(PaymentMethodController::class)->group(function () {
                Route::get(PaymentMethod::LIST[URI], 'index')->name('index');
                Route::get(PaymentMethod::PAYMENT_OPTION[URI], 'getPaymentOptionView')->name('payment-option');
                Route::post(PaymentMethod::PAYMENT_OPTION[URI], 'updatePaymentOption');
                Route::put(PaymentMethod::UPDATE_CONFIG[URI], 'UpdatePaymentConfig')->name('addon-payment-set');
            });
        });




        Route::group(['prefix' => 'email-templates', 'as' => 'email-templates.', 'middleware' => ['module:system_settings']], function () {
            Route::controller(EmailTemplatesController::class)->group(function () {
                Route::get('index', 'index')->name('index');
                Route::get(EmailTemplate::VIEW[URI] . '/{type}' . '/{tab}', 'getView')->name('view');
                Route::post(EmailTemplate::UPDATE[URI] . '/{type}' . '/{tab}', 'update')->name('update');
                Route::post(EmailTemplate::UPDATE_STATUS[URI] . '/{type}' . '/{tab}', 'updateStatus')->name('update-status');
            });
        });
    });



    Route::group(['prefix' => 'currency', 'as' => 'currency.', 'middleware' => ['module:system_settings']], function () {
        Route::controller(CurrencyController::class)->group(function () {
            Route::get(Currency::LIST[URI], 'index')->name('view');
            Route::post(Currency::ADD[URI], 'add')->name('store');
            Route::get(Currency::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            Route::post(Currency::UPDATE[URI] . '/{id}', 'update');
            Route::post(Currency::DELETE[URI], 'delete')->name('delete');
            Route::post(Currency::STATUS[URI], 'status')->name('status');
            Route::post(Currency::DEFAULT[URI], 'updateSystemCurrency')->name('system-currency-update');
        });
    });
   Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function () {
        Route::controller(TaskNotificationsController::class)->group(function () {
            Route::get(Notifications::LIST[URI], 'list')->name('list');
            Route::get(Notifications::VIEW[URI] . '/{id}', 'view')->name('view');
            Route::get(Notifications::TICKET_VIEW[URI] . '/{id}', 'getConversationReview')->name('ticket');
        });
    });
    Route::group(['prefix' => 'addon', 'as' => 'addon.', 'middleware' => ['module:system_settings']], function () {
        Route::controller(AddonController::class)->group(function () {
            Route::get(AddonSetup::VIEW[URI], 'index')->name('index');
            Route::post(AddonSetup::PUBLISH[URI], 'publish')->name('publish');
            Route::post(AddonSetup::ACTIVATION[URI], 'activation')->name('activation');
            Route::post(AddonSetup::UPLOAD[URI], 'upload')->name('upload');
            Route::post(AddonSetup::DELETE[URI], 'delete')->name('delete');
        });
    });

    Route::group(['prefix' => 'social-login', 'as' => 'social-login.', 'middleware' => ['module:system_settings']], function () {
        Route::controller(SocialLoginSettingsController::class)->group(function () {
            Route::get(SocialLoginSettings::VIEW[URI], 'index')->name('view');
            Route::post(SocialLoginSettings::UPDATE[URI] . '/{service}', 'update')->name('update');
            Route::post(SocialLoginSettings::APPLE_UPDATE[URI] . '/{service}', 'updateAppleLogin')->name('update-apple');
        });
    });

    Route::group(['prefix' => 'storage-connection-settings', 'as' => 'storage-connection-settings.', 'middleware' => ['module:system_settings']], function () {
        Route::controller(StorageConnectionSettingsController::class)->group(function () {
            Route::get(StorageConnectionSettings::INDEX[URI], 'index')->name('index');
            Route::post(StorageConnectionSettings::STORAGE_TYPE[URI], 'updateStorageType')->name('update-storage-type');
            Route::post(StorageConnectionSettings::S3_STORAGE_CREDENTIAL[URI], 'updateS3Credential')->name('s3-credential');
        });
    });

    Route::group(['prefix' => 'firebase-otp-verification', 'as' => 'firebase-otp-verification.', 'middleware' => ['module:system_settings']], function () {
        Route::controller(FirebaseOTPVerificationController::class)->group(function () {
            Route::get(FirebaseOTPVerification::INDEX[URI], 'index')->name('index');
            Route::post(FirebaseOTPVerification::UPDATE[URI], 'updateConfig')->name('update');
            Route::post(FirebaseOTPVerification::FIREBASE_CONFIG_VALIDATION[URI], 'getConfigValidation')->name('config-status-validation');
        });
    });

    Route::group(['prefix' => 'social-media-chat', 'as' => 'social-media-chat.', 'middleware' => ['module:system_settings']], function () {
        Route::controller(SocialMediaChatController::class)->group(function () {
            Route::get(SocialMediaChat::VIEW[URI], 'index')->name('view');
            Route::post(SocialMediaChat::UPDATE[URI] . '/{service}', 'update')->name('update');
        });
    });



    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.', 'middleware' => ['module:promotion_management']], function () {
        Route::controller(BusinessSettingsController::class)->group(function () {
            Route::get(BusinessSettings::ANNOUNCEMENT[URI], 'getAnnouncementView')->name('announcement');
            Route::post(BusinessSettings::ANNOUNCEMENT[URI], 'updateAnnouncement');
        });
    });

    Route::group(['prefix' => 'seo-settings', 'as' => 'seo-settings.'], function () {
        Route::controller(SEOSettingsController::class)->group(function () {
            Route::get(SEOSettings::WEB_MASTER_TOOL[URI], 'index')->name('web-master-tool');
            Route::post(SEOSettings::WEB_MASTER_TOOL[URI], 'updateWebMasterTool');
            Route::get(SEOSettings::ROBOT_TXT[URI], 'getRobotTxtView')->name('robot-txt');
            Route::post(SEOSettings::ROBOT_TXT[URI], 'updateRobotText');
        });

        Route::group(['prefix' => 'robots-meta-content', 'as' => 'robots-meta-content.'], function () {
            Route::controller(RobotsMetaContentController::class)->group(function () {
                Route::get(RobotsMetaContent::ROBOTS_META_CONTENT[URI], 'index')->name('index');
                Route::post(RobotsMetaContent::ADD_PAGE[URI], 'addPage')->name('add-page');
                Route::get(RobotsMetaContent::DELETE_PAGE[URI], 'getPageDelete')->name('delete-page');
                Route::get(RobotsMetaContent::PAGE_CONTENT_VIEW[URI], 'getPageAddContentView')->name('page-content-view');
                Route::post(RobotsMetaContent::PAGE_CONTENT_UPDATE[URI], 'getPageContentUpdate')->name('page-content-update');
            });
        });

        Route::controller(SiteMapController::class)->group(function () {
            Route::get(SiteMap::SITEMAP[URI], 'index')->name('sitemap');
            Route::get(SiteMap::GENERATE_AND_DOWNLOAD[URI], 'getGenerateAndDownload')->name('sitemap-generate-download');
            Route::get(SiteMap::GENERATE_AND_UPLOAD[URI], 'getGenerateAndUpload')->name('sitemap-generate-upload');
            Route::post(SiteMap::UPLOAD[URI], 'getUpload')->name('sitemap-manual-upload');
            Route::get(SiteMap::DOWNLOAD[URI], 'getDownload')->name('sitemap-download');
            Route::get(SiteMap::DELETE[URI], 'getDelete')->name('sitemap-delete');
        });
    });

    Route::group(['prefix' => 'error-logs', 'as' => 'error-logs.'], function () {
        Route::controller(ErrorLogsController::class)->group(function () {
            Route::get(ErrorLogs::INDEX[URI], 'index')->name('index');
            Route::post(ErrorLogs::INDEX[URI], 'update');
            Route::delete(ErrorLogs::INDEX[URI], 'delete');
            Route::delete(ErrorLogs::DELETE_SELECTED_ERROR_LOGS[URI], 'deleteSelectedErrorLogs')->name('delete-selected-error-logs');
        });
    });

    Route::group(['prefix' => 'file-manager', 'as' => 'file-manager.', 'middleware' => ['module:system_settings']], function () {
        Route::controller(FileManagerController::class)->group(function () {
            Route::get(FileManager::VIEW[URI] . '/{folderPath?}', 'getFoldersView')->name('index');
            Route::get(FileManager::DOWNLOAD[URI] . '/{file_name}', 'download')->name('download');
            Route::post(FileManager::IMAGE_UPLOAD[URI], 'upload')->name('image-upload');
        });
    });

    Route::group(['prefix' => 'helpTopic', 'as' => 'helpTopic.', 'middleware' => ['module:system_settings']], function () {
        Route::controller(HelpTopicController::class)->group(function () {
            Route::get(HelpTopic::LIST[URI], 'index')->name('list');
            Route::post(HelpTopic::ADD[URI], 'add')->name('add-new');
            Route::get(HelpTopic::STATUS[URI] . '/{id}', 'updateStatus')->name('status');
            Route::get(HelpTopic::UPDATE[URI] . '/{id}', 'getUpdateResponse')->name('update');
            Route::post(HelpTopic::UPDATE[URI] . '/{id}', 'update');
            Route::post(HelpTopic::DELETE[URI], 'delete')->name('delete');
        });
    });
});
