<?php

use App\Enums\ViewPaths\Web\ProductCompare;
use App\Enums\ViewPaths\Web\ShopFollower;
use App\Http\Controllers\Customer\Auth\CustomerAuthController;
use App\Http\Controllers\Customer\Auth\ForgotPasswordController;
use App\Http\Controllers\Customer\Auth\LoginController;
use App\Http\Controllers\Customer\Auth\RegisterController;
use App\Http\Controllers\Customer\Auth\SocialAuthController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\SystemController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ProductCompareController;
use App\Http\Controllers\Web\Shop\ShopFollowerController;
use App\Http\Controllers\Web\WebController;
use App\Http\Controllers\Web\UserProfileController;
use Illuminate\Support\Facades\Route;
use App\Enums\ViewPaths\Web\Pages;
use App\Enums\ViewPaths\Web\Review;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\AskExpertController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\ChatController;
use App\Http\Controllers\Web\NotificantionsController;
use App\Http\Controllers\Web\ChatbotController;
use App\Http\Controllers\Payment_Methods\SslCommerzPaymentController;
use App\Http\Controllers\Payment_Methods\StripePaymentController;
use App\Http\Controllers\Payment_Methods\PaymobController;
use App\Http\Controllers\Payment_Methods\FlutterwaveV3Controller;
use App\Http\Controllers\Payment_Methods\PaytmController;
use App\Http\Controllers\Payment_Methods\PaypalPaymentController;
use App\Http\Controllers\Payment_Methods\PaytabsController;
use App\Http\Controllers\Payment_Methods\LiqPayController;
use App\Http\Controllers\Payment_Methods\RazorPayController;
use App\Http\Controllers\Payment_Methods\SenangPayController;
use App\Http\Controllers\Payment_Methods\MercadoPagoController;
use App\Http\Controllers\Payment_Methods\BkashPaymentController;
use App\Http\Controllers\Payment_Methods\PaystackController;




Route::get('/test', function () {
    return view('admin-views.deal.clearance-sale.priority-setup');
});

Route::controller(WebController::class)->group(function () {
    Route::get('maintenance-mode', 'maintenance_mode')->name('maintenance-mode');
});



Route::group(['namespace' => 'Web', 'middleware' => ['maintenance_mode', 'guestCheck']], function () {
    Route::group(['prefix' => 'product-compare', 'as' => 'product-compare.'], function () {
        Route::controller(ProductCompareController::class)->group(function () {
            Route::get(ProductCompare::INDEX[URI], 'index')->name('index');
            Route::post(ProductCompare::INDEX[URI], 'add');
            Route::get(ProductCompare::DELETE[URI], 'delete')->name('delete');
            Route::get(ProductCompare::DELETE_ALL[URI], 'deleteAllCompareProduct')->name('delete-all');
        });
    });
    Route::post(ShopFollower::SHOP_FOLLOW[URI], [ShopFollowerController::class, 'followOrUnfollowShop'])->name('shop-follow');
});

Route::group(['namespace' => 'Web', 'middleware' => ['maintenance_mode', 'guestCheck']], function () {

    Route::get('/', function () {
        if (auth('customer')->check()) {
            return redirect()->route('user.home');
        }
        return app(HomeController::class)->index();
    })->name('home');

    Route::controller(WebController::class)->group(function () {
        Route::get('quick-view', 'getQuickView')->name('quick-view');
        Route::get('searched-products', 'getSearchedProducts')->name('searched-products');
    });

    Route::group(['middleware' => ['customer']], function () {
        Route::controller(ReviewController::class)->group(function () {
            Route::post(Review::ADD[URI], 'add')->name('review.store');
            Route::post(Review::ADD_DELIVERYMAN_REVIEW[URI], 'addDeliveryManReview')->name('submit-deliveryman-review');
            Route::post(Review::DELETE_REVIEW_IMAGE[URI], 'deleteReviewImage')->name('delete-review-image');
        });
    });

    Route::post('/ask-expert/start-chat', [AskExpertController::class, 'startChat'])
        ->name('ask.expert.start');
    Route::get('/payment/expert-success', [AskExpertController::class, 'paymentSuccess'])->name('expert.payment.success');
    Route::get('/payment/expert-fail', [AskExpertController::class, 'paymentFail'])->name('expert.payment.fail');
    Route::post('/guest/process-email', [AskExpertController::class, 'processGuestEmail'])
        ->name('guest.process-email');
    Route::get('/chat/{chat}', [ChatController::class, 'view'])
        ->name('chat.view');
    Route::get('/chat/{chat}/experts-online', [ChatController::class, 'check'])
        ->name('chat.expert.online');
    Route::post('/chat/{chat}/end', [ChatController::class, 'endChat'])->name('chat.end');
    Route::post('/chat/{chat}/review', [ChatController::class, 'submitReview'])->name('chat.review');
    // send message
    Route::post('/chat/send-message', [ChatController::class, 'sendMessage'])
        ->name('chat.send');

    Route::post('/chat/mark-read', [ChatController::class, 'markRead'])->name('chat.mark-read');
    Route::post('/chatbot/start', [ChatbotController::class, 'start'])->name('chatbot.start');
    Route::get('/chatbot/full', [ChatbotController::class, 'chatBotFull'])->name('chatbot.full');
    Route::post('/chatbot/message', [ChatbotController::class, 'message'])->name('chatbot.message');
    Route::post('/chat/{chat}/generate-token', [ChatController::class, 'generateAgoraToken'])->name('chat.generate-token');
    Route::post('/chat/{chat}/start-call', [ChatController::class, 'startCall'])->name('chat.start-call'); 
    Route::controller(WebController::class)->group(function () {
        Route::get('checkout-details', 'checkout_details')->name('checkout-details');
        Route::get('checkout-shipping', 'checkout_shipping')->name('checkout-shipping');
        Route::get('checkout-payment', 'checkout_payment')->name('checkout-payment');
        Route::get('checkout-review', 'checkout_review')->name('checkout-review');
        Route::get('checkout-complete', 'getCashOnDeliveryCheckoutComplete')->name('checkout-complete');
        Route::post('offline-payment-checkout-complete', 'getOfflinePaymentCheckoutComplete')->name('offline-payment-checkout-complete');
        Route::get('order-placed', 'order_placed')->name('order-placed');
        Route::get('order-placed-success', 'getOrderPlaceView')->name('order-placed-success');
        Route::get('shop-cart', 'shop_cart')->name('shop-cart')->middleware('customer');
        Route::post('order_note', 'order_note')->name('order_note');
        Route::post('contact/store', 'contact_store')->name('contact.store');
        Route::get('digital-product-download/{id}', 'getDigitalProductDownload')->name('digital-product-download');
        Route::post('digital-product-download-otp-verify', 'getDigitalProductDownloadOtpVerify')->name('digital-product-download-otp-verify');
        Route::post('digital-product-download-otp-reset', 'getDigitalProductDownloadOtpReset')->name('digital-product-download-otp-reset');
        Route::get('pay-offline-method-list', 'pay_offline_method_list')->name('pay-offline-method-list')->middleware('guestCheck');
        Route::get('notifications', 'viewNotifications')->name('notifications')->middleware('customer');

        //wallet payment
        Route::get('checkout-complete-wallet', 'checkout_complete_wallet')->name('checkout-complete-wallet');

        Route::post('subscription', 'subscription')->name('subscription');
        Route::get('search-shop', 'search_shop')->name('search-shop');

        Route::get('categories', 'getAllCategoriesView')->name('categories');
        Route::get('category-ajax/{id}', 'categories_by_category')->name('category-ajax');

        Route::get('brands', 'getAllBrandsView')->name('brands');
        Route::get('vendors', 'getAllVendorsView')->name('vendors');
        Route::get('seller-profile/{id}', 'seller_profile')->name('seller-profile');

        Route::get('flash-deals/{id}', 'getFlashDealsView')->name('flash-deals');
    });

    Route::controller(PageController::class)->group(function () {
        Route::get(Pages::ABOUT_US[URI], 'getAboutUsView')->name('about-us');
        Route::get(Pages::USER_HOME[URI], 'getUserHomeView')->name('user.home')->middleware('customer');
        Route::get(Pages::ALL_EXPERTS[URI], 'getAllExpertView')->name('user.allexpert');
        Route::get(Pages::USER_QUESTIONS[URI], 'getUserQuestionsView')->name('user.questions')->middleware('customer');
        Route::get(Pages::USER_EXPERTS[URI], 'getUserExpertsView')->name('user.experts')->middleware('customer');
        Route::get(Pages::PRICE[URI], 'getPricesView')->name('price');
        Route::get(Pages::EXPERT_VIEW[URI], 'getExpertView')->name('expert');
        Route::get(Pages::CONTACTS[URI], 'getContactView')->name('contacts');
        Route::get(Pages::HELP_TOPIC[URI], 'getHelpTopicView')->name('helpTopic');
        Route::get(Pages::REFUND_POLICY[URI], 'getRefundPolicyView')->name('refund-policy');
        Route::get(Pages::RETURN_POLICY[URI], 'getReturnPolicyView')->name('return-policy');
        Route::get(Pages::PRIVACY_POLICY[URI], 'getPrivacyPolicyView')->name('privacy-policy');
        Route::get(Pages::CANCELLATION_POLICY[URI], 'getCancellationPolicyView')->name('cancellation-policy');
        Route::get(Pages::SHIPPING_POLICY[URI], 'getShippingPolicyView')->name('shipping-policy');
        Route::get(Pages::TERMS_AND_CONDITION[URI], 'getTermsAndConditionView')->name('terms');
        Route::get(Pages::CAREER[URI], 'career')->name('career');
        Route::get(Pages::HELP[URI], 'getHelpView')->name('help');
        Route::get(Pages::KNOW_ALL[URI], 'knowledgeAll')->name('knowledge-base.all');
        Route::get(Pages::KNOW_READ[URI] . '/{id}', 'knowledgeRead')->name('knowledge-base.read');
        Route::get(Pages::CATEGOREY_VIEW[URI] . '/{id}', 'categorieView')->name('category.view');
        Route::get(Pages::CATEGOREY_VIEW_EXPERT[URI], 'categorieExpertView')->name('category.view.expert');
    });
});


Route::group(['middleware' => ['maintenance_mode']], function () {

    Route::controller(NotificantionsController::class)->group(function () {
        Route::group(['prefix' => 'notification', 'as' => 'notification.'], function () {
            Route::get('view/{id}', 'getView')->name('view');
        });
    });

    Route::get('authentication-failed', function () {
        $errors = [];
        array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
        return response()->json([
            'errors' => $errors
        ], 401);
    })->name('authentication-failed');

    Route::group(['namespace' => 'Customer', 'prefix' => 'customer', 'as' => 'customer.'], function () {
        Route::group(['namespace' => 'Auth', 'prefix' => 'auth', 'as' => 'auth.'], function () {
            Route::controller(ForgotPasswordController::class)->group(function () {
                Route::get('recover-password', 'reset_password')->name('recover-password');
                Route::post('forgot-password', 'resetPasswordRequest')->name('forgot-password-send');
                Route::post('verify-recover-password', 'verifyRecoverPassword')->name('verify-recover-password');
                Route::get('otp-verification', 'showOtpForm')->name('otp-verification');
                Route::post('otp-verification', 'otp_verification_submit');
                Route::get('reset-password/{token}', 'resetPasswordView')->name('reset-password');
                Route::post('password/submit', 'resetPasswordSubmit')->name('password.submit');
                Route::post('resend-otp-reset-password', 'resendPhoneOTPRequest')->name('resend-otp-reset-password');
            });
            Route::controller(CustomerAuthController::class)->group(function () {
                Route::get('login', 'loginView')->name('login');
                Route::post('login', 'loginSubmit');
                Route::get('login/verify-account', 'loginVerifyPhone')->name('login.verify-account');
                Route::post('login/verify-account/submit', 'verifyAccount')->name('login.verify-account.submit');
                Route::get('login/update-info', 'updateInfo')->name('login.update-info');
                Route::post('login/update-info', 'updateInfoSubmit');
                Route::post('login/resend-otp-code', 'resendOTPCode')->name('resend-otp-code');
                Route::post('/store-return-url',  'storeReturnUrl')->name('store.return.url');
                Route::post('/clear-pending', function () {
                    session()->forget(['keep_return_url', 'pending_expert_question']);
                    return response()->json(['success' => true]);
                })->name('clear.pending');
            });

            Route::controller(LoginController::class)->group(function () {
                Route::get('/code/captcha/{tmp}', 'captcha')->name('default-captcha');
                Route::get('logout', 'logout')->name('logout');
                Route::get('get-login-modal-data', 'getLoginModalView')->name('get-login-modal-data');
            });

            Route::controller(RegisterController::class)->group(function () {
                Route::get('sign-up', 'getRegisterView')->name('sign-up');
                Route::post('sign-up', 'submitRegisterData');
                Route::post('with-us', 'submitRegisterData')->name('with-us');
                Route::get('check-verification', 'verificationCheckView')->name('check-verification');
                Route::post('verify', 'verifyRegistration')->name('verify');
                Route::post('ajax-verify', 'ajax_verify')->name('ajax_verify');
                Route::post('resend-otp', 'resendOTPToCustomer')->name('resend_otp');
            });

            Route::controller(SocialAuthController::class)->group(function () {
                Route::get('login/{service}', 'redirectToProvider')->name('service-login');
                Route::get('login/{service}/callback', 'handleProviderCallback')->name('service-callback');
                Route::get('login/social/confirmation', 'socialLoginConfirmation')->name('social-login-confirmation');
                Route::post('login/social/confirmation/update', 'updateSocialLoginConfirmation')->name('social-login-confirmation.update');
                Route::post('login/social/verify-account', 'verifyAccount')->name('login.social.verify-account');
            });
        });

        Route::group([], function () {

            Route::controller(SystemController::class)->group(function () {
                Route::get('set-payment-method/{name}', 'setPaymentMethod')->name('set-payment-method');
                Route::get('set-shipping-method', 'setShippingMethod')->name('set-shipping-method');
                Route::post('choose-shipping-address', 'getChooseShippingAddress')->name('choose-shipping-address');
                Route::post('choose-shipping-address-other', 'getChooseShippingAddressOther')->name('choose-shipping-address-other');
                Route::post('choose-billing-address', 'choose_billing_address')->name('choose-billing-address');
                Route::get('set-installtion-charges', 'setInstalltionCharges')->name('set-installtion-charges');
            });

            Route::group(['prefix' => 'reward-points', 'as' => 'reward-points.', 'middleware' => ['auth:customer']], function () {
                Route::get('convert', 'RewardPointController@convert')->name('convert');
            });
        });
    });


    Route::group(['namespace' => 'Expert', 'prefix' => 'expert', 'as' => 'expert.'], function () {

        Route::group(['namespace' => 'Auth', 'prefix' => 'auth', 'as' => 'auth.'], function () {

            Route::controller(CustomerAuthController::class)->group(function () {
                Route::get('login', 'loginView')->name('login');
                Route::post('login', 'loginSubmit');
                Route::get('login/verify-account', 'loginVerifyPhone')->name('login.verify-account');
                Route::post('login/verify-account/submit', 'verifyAccount')->name('login.verify-account.submit');
                Route::get('login/update-info', 'updateInfo')->name('login.update-info');
                Route::post('login/update-info', 'updateInfoSubmit');
                Route::post('login/resend-otp-code', 'resendOTPCode')->name('resend-otp-code');
            });

            Route::controller(LoginController::class)->group(function () {
                Route::get('/code/captcha/{tmp}', 'captcha')->name('default-captcha');
                Route::get('logout', 'logout')->name('logout');
                Route::get('get-login-modal-data', 'getLoginModalView')->name('get-login-modal-data');
            });

            Route::controller(RegisterController::class)->group(function () {
                Route::get('sign-up', 'getRegisterView')->name('sign-up');
                Route::post('sign-up', 'submitRegisterData');
                Route::post('with-us', 'submitRegisterData')->name('with-us');
                Route::get('check-verification', 'verificationCheckView')->name('check-verification');
                Route::post('verify', 'verifyRegistration')->name('verify');
                Route::post('ajax-verify', 'ajax_verify')->name('ajax_verify');
                Route::post('resend-otp', 'resendOTPToCustomer')->name('resend_otp');
            });

            Route::controller(SocialAuthController::class)->group(function () {
                Route::get('login/{service}', 'redirectToProvider')->name('service-login');
                Route::get('login/{service}/callback', 'handleProviderCallback')->name('service-callback');
                Route::get('login/social/confirmation', 'socialLoginConfirmation')->name('social-login-confirmation');
                Route::post('login/social/confirmation/update', 'updateSocialLoginConfirmation')->name('social-login-confirmation.update');
                Route::post('login/social/verify-account', 'verifyAccount')->name('login.social.verify-account');
            });
        });
    });


    Route::controller(UserProfileController::class)->group(function () {
        Route::get('user-profile', 'user_profile')->name('user-profile')->middleware('customer'); //theme_aster
        Route::get('user-account', 'user_account')->name('user-account')->middleware('customer');
        Route::post('user-account-update', 'getUserProfileUpdate')->name('user-update')->middleware('customer');
        Route::post('user-account-picture', 'user_picture')->name('user-picture');
        Route::match(['post', 'delete'], 'account-delete/{id}', 'account_delete')->name('account-delete');
        Route::get('user-plans', 'userPlans')->name('user.plans')->middleware('customer');
        Route::get('user-questions', 'userQuestions')->name('my.questions')->middleware('customer');
        Route::get('user-experts', 'userExperts')->name('my.experts')->middleware('customer');
        Route::post('subscription/cancel-auto-renew/{subscriptionId}', 'cancelAutoRenew')->name('subscription.cancel-auto-renew')->middleware('customer');
        Route::post('user/refund-request', 'storeRefundRequest')->name('user.refund.request.store');
    });

    Route::group(['namespace' => 'Customer', 'prefix' => 'customer', 'as' => 'customer.'], function () {
        Route::controller(PaymentController::class)->group(function () {
            Route::post('/web-payment-request', 'payment')->name('web-payment-request');
            Route::post('/customer-add-fund-request', 'customer_add_to_fund_request')->name('add-fund-request');
        });
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::get('web-payment', 'web_payment_success')->name('web-payment-success');
        Route::get('payment-success', 'success')->name('payment-success');
        Route::get('payment-fail', 'fail')->name('payment-fail');
    });
    Route::post('payment/stripe/intent', [StripePaymentController::class, 'createPaymentIntent'])->name('stripe.payment.intent');
    // routes/web.php
    Route::post('stripe/webhook', [StripePaymentController::class, 'handle']);

    $isGatewayPublished = 0;
    try {
        $full_data = include('Modules/Gateways/Addon/info.php');
        $isGatewayPublished = $full_data['is_published'] == 1 ? 1 : 0;
    } catch (\Exception $exception) {
    }

    if (!$isGatewayPublished) {
        Route::group(['prefix' => 'payment'], function () {
            Route::group(['prefix' => 'stripe', 'as' => 'stripe.'], function () {
                Route::get('pay', [StripePaymentController::class, 'index'])->name('pay');
                Route::get('token', [StripePaymentController::class, 'payment_process_3d'])->name('token');
                Route::get('success', [StripePaymentController::class, 'success'])->name('success');
            });
        });
    }
});
