<?php

namespace App\Http\Controllers\RestAPI\v3\restaurant\auth;

use App\Events\PasswordResetEvent;
use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Utils\Helpers;
use App\Utils\SMSModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Gateways\Traits\SmsGateway;
use App\Models\PasswordReset;
use App\Models\Restaurant;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{

    public function reset_password_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            Log::warning('Reset password validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $verification_by = getWebConfig(name: 'forgot_password_verification');
        $otp_interval_time = getWebConfig(name: 'otp_resend_time') ?? 1; //second

        Log::info("Password reset requested", [
            'identity' => $request['email'],
            'verification_by' => $verification_by
        ]);

        // Always check for email only
        $customer = Restaurant::where('email', $request['email'])->first();
        $password_verification_data = PasswordReset::where(['user_type' => 'restaurant'])
            ->where('identity', 'like', "%{$request['email']}%")
            ->latest()
            ->first();

        if ($customer) {
            if (isset($password_verification_data) && Carbon::parse($password_verification_data->created_at)->diffInSeconds() < $otp_interval_time) {
                $time = $otp_interval_time - Carbon::parse($password_verification_data->created_at)->diffInSeconds();
                Log::warning("Password reset requested too soon", [
                    'customer_id' => $customer->id,
                    'wait_time' => $time
                ]);
                return response()->json([
                    'message' => translate('please_try_again_after') . ' ' . CarbonInterval::seconds($time)->cascade()->forHumans()
                ], 200);
            }

            $token = Str::random(120);
            $reset_data = PasswordReset::where(['identity' => $customer['email']])->latest()->first();
            if ($reset_data) {
                $reset_data->token = $token;
                $reset_data->created_at = now();
                $reset_data->updated_at = now();
                $reset_data->save();
                Log::info("Password reset token updated", ['email' => $customer['email']]);
            } else {
                $reset_data = new PasswordReset();
                $reset_data->identity = $customer['email'];
                $reset_data->token = $token;
                $reset_data->user_type = 'restaurant';
                $reset_data->created_at = now();
                $reset_data->updated_at = now();
                $reset_data->save();
                Log::info("Password reset token created", ['email' => $customer['email']]);
            }

$reset_url = url('/') . '/restaurant/auth/reset-password?token=' . $token . '&email=' . base64_encode($customer['email']);
                    Log::info("Password reset email sent", ['url' => $reset_url]);

            $emailServices_smtp = getWebConfig(name: 'mail_config');
            if ($emailServices_smtp['status'] == 0) {
                $emailServices_smtp = getWebConfig(name: 'mail_config_sendgrid');
            }

            if ($emailServices_smtp['status'] == 1) {
                try {
                    $data = [
                        'userType' => 'customer',
                        'templateName' => 'forgot-password',
                        'vendorName' => $customer['f_name'],
                        'subject' => translate('password_reset'),
                        'title' => translate('password_reset'),
                        'passwordResetURL' => $reset_url,
                    ];
                    event(new PasswordResetEvent(email: $customer['email'], data: $data));
                    Log::info("Password reset email sent", ['email' => $customer['email']]);
                    $response = 'Check your email';
                } catch (\Exception $exception) {
                    Log::error("Password reset email failed", ['error' => $exception->getMessage()]);
                    return response()->json([
                        'message' => translate('email_is_not_configured') . ' ' . translate('contact_with_the_administrator')
                    ], 403);
                }
            } else {
                Log::error("Email service not configured");
                $response = translate('email_failed');
            }

            return response()->json(['message' => $response], 200);
        }

        Log::warning("Password reset requested for non-existing user", ['identity' => $request['identity']]);
        return response()->json(['errors' => [
            ['code' => 'not-found', 'message' => translate('user not found') . '!']
        ]], 403);
    }

    public function otp_verification_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $id = $request['identity'];
        $data = DB::table('password_resets')
            ->where('user_type', 'seller')
            ->where(['token' => $request['otp']])
            ->where('identity', 'like', "%{$id}%")
            ->first();

        if (isset($data)) {
            return response()->json(['message' => 'otp verified.'], 200);
        }

        return response()->json(['errors' => [
            ['code' => 'not-found', 'message' => 'invalid OTP']
        ]], 404);
    }

    public function reset_password_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'otp' => 'required',
            'password' => 'required|same:confirm_password|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $data = DB::table('password_resets')
            ->where('user_type', 'seller')
            ->where('identity', 'like', "%{$request['identity']}%")
            ->where(['token' => $request['otp']])->first();

        if (isset($data)) {
            DB::table('sellers')->where('phone', 'like', "%{$data->identity}%")
                ->update([
                    'password' => bcrypt(str_replace(' ', '', $request['password']))
                ]);

            DB::table('password_resets')
                ->where('user_type', 'seller')
                ->where('identity', 'like', "%{$request['identity']}%")
                ->where(['token' => $request['otp']])->delete();

            return response()->json(['message' => 'Password changed successfully.'], 200);
        }
        return response()->json(['errors' => [
            ['code' => 'invalid', 'message' => 'Invalid token.']
        ]], 400);
    }
}
