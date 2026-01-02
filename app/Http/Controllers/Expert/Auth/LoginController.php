<?php

namespace App\Http\Controllers\Expert\Auth;

use App\Contracts\Repositories\ExpertRepositoryInterface;
use App\Enums\SessionKey;
use App\Enums\ViewPaths\Expert\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Http\Requests\Vendor\LoginRequest;
use App\Repositories\VendorWalletRepository;
use App\Services\ExpertService;
use App\Traits\RecaptchaTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use RecaptchaTrait;

    public function __construct(
        private readonly ExpertRepositoryInterface $expertRepo,
        private readonly ExpertService             $expertService,

    ) {
        $this->middleware('guest:expert', ['except' => ['logout']]);
    }

    public function generateReCaptcha(): void
    {
        $recaptchaBuilder = $this->generateDefaultReCaptcha(4);
        if (Session::has(SessionKey::RESTAURANT_RECAPTCHA_KEY)) {
            Session::forget(SessionKey::RESTAURANT_RECAPTCHA_KEY);
        }
        Session::put(SessionKey::RESTAURANT_RECAPTCHA_KEY, $recaptchaBuilder->getPhrase());
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $recaptchaBuilder->output();
    }

    public function getLoginView(): View
    {
        $recaptchaBuilder = $this->generateDefaultReCaptcha(4);
        $recaptcha = getWebConfig(name: 'recaptcha');
        Session::put(SessionKey::RESTAURANT_RECAPTCHA_KEY, $recaptchaBuilder->getPhrase());
        return view(Auth::EXPERT_LOGIN[VIEW], compact('recaptchaBuilder', 'recaptcha'));
    }


    public function login(Request $request): RedirectResponse
    {

        $expert = $this->expertRepo->getFirstWhere(['email' => $request['email']]);
        if (!$expert) {
            Toastr::error(translate('credentials_doesnt_match') . '!');
            return back();
        }
        $passwordCheck = Hash::check($request['password'], $expert['password']);
        if ($passwordCheck && $expert['status'] == 'pending') {
            Toastr::error(translate('Account_Not_approve_yet') . '!');
            return back();
        }
        if ($passwordCheck && $expert['status'] == 'rejected') {
            Toastr::error(translate('Your_account_is_reject_by_admin') . '!');
            return back();
        }
        if ($this->expertService->isLoginSuccessful($request->email, $request->password, $request->remember)) {

            logActivity(
                'Expert logged in',
                auth('expert')->user(),
                [
                    'ip' => $request->ip(),
                    'agent' => $request->userAgent(),
                ]
            );

            Toastr::info(translate('welcome_to_your_dashboard') . '.');
            return redirect()->route('expert.dashboard.index');
        } else {
            Toastr::error(translate('credentials_doesnt_match') . '!');
            return back();
        }
    }

    public function logout(): RedirectResponse
    {
        $this->expertService->logout();
        Toastr::success(translate('logged_out_successfully') . '.');
        return redirect()->route('home');
    }
}
