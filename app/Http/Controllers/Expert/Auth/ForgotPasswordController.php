<?php

namespace App\Http\Controllers\Expert\Auth;

use App\Contracts\Repositories\PasswordResetRepositoryInterface;
use App\Contracts\Repositories\RestaurantRepositoryInterface;
use App\Contracts\Repositories\PhoneOrEmailVerificationRepositoryInterface;
use App\Enums\SessionKey;
use App\Enums\ViewPaths\Vendor\Auth;
use App\Enums\ViewPaths\Vendor\ForgotPassword;
use App\Events\PasswordResetEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\PasswordResetRequest;
use App\Http\Requests\Vendor\VendorPasswordRequest;
use App\Services\PasswordResetService;
use App\Traits\EmailTemplateTrait;
use App\Traits\SmsGateway;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Gateways\Traits\SmsGateway as AddonSmsGateway;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\PasswordReset;
use App\Models\Expert;
use Carbon\Carbon;

class ForgotPasswordController extends BaseController
{
    use SmsGateway, EmailTemplateTrait;

    /**
     * @param PasswordResetRepositoryInterface $passwordResetRepo
     * @param PasswordResetService $passwordResetService
     */
    public function __construct(
        private readonly PasswordResetRepositoryInterface $passwordResetRepo,
        private readonly PasswordResetService             $passwordResetService,
        private readonly RestaurantRepositoryInterface             $restaurantrepo,
        private readonly PhoneOrEmailVerificationRepositoryInterface             $phoneOrEmailVerificationRepo,
    ) {
        $this->middleware('guest:expert', ['except' => ['logout']]);
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->reset_password();
    }

    /**
     * @return View
     */
    public function reset_password()
    {
        $verification_by = getWebConfig(name: 'forgot_password_verification');
        return view('expert-views.auth.forgot-password.recover-password', compact('verification_by'));
    }

   public function resetPasswordRequest(Request $request)
{
    Log::info('Reset password request started', ['email' => $request->email]);

    $request->validate([
        'email' => 'required|email'
    ]);

    $user = Expert::where('email', $request->email)->first();
    if (!$user) {
        Log::warning('No Expert found for this email', ['email' => $request->email]);
        Toastr::error(translate('no_user_found_for_this_email'));
        return back();
    }

    Log::info('User found for password reset', ['user_id' => $user->id, 'email' => $user->email]);

    $otp = rand(100000, 999999);
    Log::info('Generated OTP', ['otp' => $otp]);

    PasswordReset::updateOrCreate(
        ['identity' => $user->email],
        [
            'token' => $otp,
            'created_at' => now()
        ]
    );
    Log::info('Password reset token saved', ['email' => $user->email]);

    try {
        Mail::raw(
            "Your OTP for password reset is:' $otp ' Valid for 15 minutes.",
            function ($message) use ($user) {
                $message->to($user->email)->subject('Password Reset OTP');
            }
        );

         session(['forgot_password_email' => $user->email]);
        Log::info('Password reset email sent', ['email' => $user->email]);
    } catch (\Exception $e) {
        Log::error('Password reset email failed', [
            'email' => $user->email,
            'error' => $e->getMessage()
        ]);
    }

    // Hamesha same message dikhao — registered ho ya na ho
    Toastr::success('If your email is registered, an OTP has been sent.');
    Log::info('Redirecting to OTP verification page', ['email' => $user->email]);

    return redirect()->route('expert.auth.otp-verification');
}

    public function showOtpForm(Request $request)
    {               

        if (!session('forgot_password_email')) {
            return redirect()->route('expert.auth.recover-password');
        }

        return view('expert-views.auth.forgot-password.verify-otp');
    }
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6'
        ]);

        $email = session('forgot_password_email');

        $reset = PasswordReset::where('identity', $email)
            ->where('token', $request->otp)
            ->first();

        if (!$reset || Carbon::parse($reset->created_at)->addMinutes(15)->isPast()) {
            Toastr::error('Invalid or expired OTP');
            return back();
        }

        // OTP valid → go to reset password
        return redirect()->route('expert.auth.reset-password', ['token' => $reset->token]);
    }
  

    public function resetPasswordView($token): View|RedirectResponse
    {


        $reset = PasswordReset::where('token', $token)->first();

        if (!$reset || Carbon::parse($reset->created_at)->addMinutes(15)->isPast()) {
            Toastr::error(translate('Invalid_credentials_or_expired_link'));
            return view(VIEW_FILE_NAMES['reset_password_expire']);
        }

        Log::info('Valid password reset entry found', [
            'token' => $token
        ]);

        return view('expert-views.auth.forgot-password.reset-password', compact('token'));
    }



    public function resetPasswordSubmit(Request $request): View|RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);
        $reset = PasswordReset::where('token', $request->token)->first();
        if (!$reset || Carbon::parse($reset->created_at)->addMinutes(15)->isPast()) {
            Toastr::error('Invalid or expired token');
            return view(VIEW_FILE_NAMES['reset_password_expire']);
        }

        $user = Expert::where('email', $reset->identity)->first();
        $user->password = bcrypt($request->password);
        $user->save();
        PasswordReset::where('identity', $user->email)->delete();

        Toastr::success('Password reset successfully! Please login.');
        return redirect()->route('expert.auth.login');
    }

}
