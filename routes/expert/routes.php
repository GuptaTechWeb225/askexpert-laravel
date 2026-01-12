<?php

use App\Enums\ViewPaths\Expert\Auth;
use App\Enums\ViewPaths\Expert\Dashboard;
use App\Enums\ViewPaths\Expert\Chat;
use App\Http\Controllers\Expert\Auth\LoginController;
use App\Http\Controllers\Expert\DashboardController;
use App\Http\Controllers\Expert\ExpertChatController;
use App\Http\Controllers\Expert\Auth\ForgotPasswordController;
use App\Http\Controllers\Expert\ExpertSettingsController;
use App\Http\Controllers\Expert\Auth\RegisterController;
use Illuminate\Support\Facades\Route;



Route::group(['prefix' => 'expert', 'as' => 'expert.'], function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::controller(LoginController::class)->group(function () {
            Route::get(Auth::EXPERT_LOGIN[URI], 'getLoginView')->name('login');
            Route::post(Auth::EXPERT_LOGIN[URI], 'login')->name('login');
            Route::get(Auth::EXPERT_LOGOUT[URI], 'logout')->name('logout');
        });
        Route::group(['prefix' => 'registration', 'as' => 'registration.'], function () {
            Route::controller(RegisterController::class)->group(function () {
                Route::get(Auth::EXPERT_REGISTRATION[URI], 'index')->name('index');
                Route::post(Auth::EXPERT_REGISTRATION[URI], 'store')->name('add');
            });
        });

        Route::controller(ForgotPasswordController::class)->group(function () {
            Route::get('recover-password', 'reset_password')->name('recover-password');
            Route::post('forgot-password', 'resetPasswordRequest')->name('forgot-password-send');
            Route::post('verify-recover-password', 'verifyRecoverPassword')->name('verify-recover-password');
            Route::get('otp-verification', 'showOtpForm')->name('otp-verification');
            Route::post('otp-verification', 'verifyOtp');
            Route::get('reset-password/{token}', 'resetPasswordView')->name('reset-password');
            Route::post('password/submit', 'resetPasswordSubmit')->name('password.submit');
            Route::post('resend-otp-reset-password', 'resendPhoneOTPRequest')->name('resend-otp-reset-password');
        });
    });
    Route::group(['middleware' => ['expert.auth']], function () {
        Route::get('earnings', [ExpertSettingsController::class, 'expertEarnings'])->name('earnings');

        Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
            Route::controller(DashboardController::class)->group(function () {
                Route::get(Dashboard::INDEX[URI], 'index')->name('index');
                Route::post(Dashboard::UPDATE_STATUS[URI], 'updateStatus')->name('update-status');
            });
        });
        Route::group(['prefix' => 'chat', 'as' => 'chat.'], function () {
            Route::controller(ExpertChatController::class)->group(function () {
                Route::get(Chat::INDEX[URI] . '/{chat}', 'view')->name('index');
                Route::post(Chat::SEND_MASSAGE[URI], 'sendMessage')->name('send-message');
                Route::post(Chat::MARK_READ[URI], 'markRead')->name('mark-read');
                Route::get(Chat::MASSAGES[URI], 'massagesChat')->name('allmassages');
                Route::post(Chat::END_CHAT[URI], 'endChatByExpert')->name('end-chat');
                Route::post('/{chat}/action',  'performAction')->name('action');
            });
        });

        Route::group(['prefix' => 'massages', 'as' => 'massages.'], function () {
            Route::controller(ExpertChatController::class)->group(function () {
                Route::get(Chat::MASSAGES[URI], 'massagesChat')->name('allmassages');
            });
        });
        Route::group(['prefix' => 'questions', 'as' => 'questions.'], function () {
            Route::controller(ExpertChatController::class)->group(function () {
                Route::get(Chat::QUESTIONS[URI], 'myQuestions')->name('all');
            });
        });
    });

    Route::group(['prefix' => 'settings', 'as' => 'settings.', 'middleware' => 'expert.auth'], function () {
        Route::get('availability', [ExpertSettingsController::class, 'availability'])->name('availability');
        Route::post('availability', [ExpertSettingsController::class, 'updateAvailability'])->name('availability.update');

        Route::get('communication-modes', [ExpertSettingsController::class, 'communicationModes'])->name('communication');
        Route::post('communication-modes', [ExpertSettingsController::class, 'updateCommunicationModes'])->name('communication.update');

        Route::get('notifications', [ExpertSettingsController::class, 'notifications'])->name('notifications');
        Route::post('notifications', [ExpertSettingsController::class, 'updateNotifications'])->name('notifications.update');
        Route::get('profile/update', [ExpertSettingsController::class, 'profileEdit'])->name('profile.edit');
        Route::put('profile/update', [ExpertSettingsController::class, 'profileUpdate'])->name('profile.update');
    });
});


Route::prefix('expert/massages/admin-chat')->group(function () {
    Route::post('/send', [ExpertChatController::class, 'sendToAdmin']); // Already hai tere paas
    Route::post('/mark-read', [ExpertChatController::class, 'markAdminRead']);
    Route::post('/mark-specific-read', [ExpertChatController::class, 'markAdminSpecificRead']);
    Route::get('/messages', [ExpertChatController::class, 'getAdminMessages']); // Initial load
    Route::post('{chat}/end', [ExpertChatController::class, 'endChatByExpert']);
    Route::post('{chat}/generate-token', [ExpertChatController::class, 'generateAgoraToken']);
});
