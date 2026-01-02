<?php

namespace App\Http\Controllers\RestAPI\v1\auth;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\LoginSetupRepositoryInterface;
use App\Contracts\Repositories\PhoneOrEmailVerificationRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\User;
use App\Utils\CartManager;
use App\Utils\Helpers;
use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Traits\PushNotificationTrait;

class SocialAuthController extends Controller
{

        use  PushNotificationTrait;

    public function __construct(
        private readonly CustomerRepositoryInterface                 $customerRepo,
        private readonly PhoneOrEmailVerificationRepositoryInterface $phoneOrEmailVerificationRepo,
        private readonly LoginSetupRepositoryInterface               $loginSetupRepo
    ) {}


    public function social_login(Request $request)
    {
        Log::info('Social Login Request:', $request->all());

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'unique_id' => 'required',
            'medium' => 'required|in:google,facebook,apple',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation Failed:', $validator->errors()->toArray());
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $client = new Client();
        $token = $request['token'];
        $email = $request['email'] ?? null;
        $unique_id = $request['unique_id'];

        try {
            if ($request['medium'] == 'google') {
                Log::info('Google Login API Call', ['token' => $token]);
                $res = $client->request('GET', 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $token);
                $data = json_decode($res->getBody()->getContents(), true);
                Log::info('Google Response:', $data);
            } elseif ($request['medium'] == 'facebook') {
                Log::info('Facebook Login API Call', ['token' => $token, 'unique_id' => $unique_id]);
                $res = $client->request('GET', 'https://graph.facebook.com/' . $unique_id . '?access_token=' . $token . '&&fields=name,email');
                $data = json_decode($res->getBody()->getContents(), true);
                Log::info('Facebook Response:', $data);
            } elseif ($request['medium'] == 'apple') {
                Log::info('Apple Login Start');
                $apple_login = BusinessSetting::where(['type' => 'apple_login'])->first();
                if ($apple_login) {
                    $apple_login = json_decode($apple_login->value)[0];
                }
                Log::info('Apple Credentials:', (array) $apple_login);

                $teamId = $apple_login->team_id;
                $keyId = $apple_login->key_id;
                $sub = $apple_login->client_id;
                $aud = 'https://appleid.apple.com';
                $iat = strtotime('now');
                $exp = strtotime('+60days');
                $keyContent = file_get_contents('storage/app/public/apple-login/' . $apple_login->service_file);

                $token = JWT::encode([
                    'iss' => $teamId,
                    'iat' => $iat,
                    'exp' => $exp,
                    'aud' => $aud,
                    'sub' => $sub,
                ], $keyContent, 'ES256', $keyId);

                $redirect_uri = $apple_login->redirect_url ?? 'www.example.com/apple-callback';
                $res = Http::asForm()->post('https://appleid.apple.com/auth/token', [
                    'grant_type' => 'authorization_code',
                    'code' => $unique_id,
                    'redirect_uri' => $redirect_uri,
                    'client_id' => $sub,
                    'client_secret' => $token,
                ]);

                Log::info('Apple Raw Response:', $res->json());

                $claims = explode('.', $res['id_token'])[1];
                $data = json_decode(base64_decode($claims), true);
                Log::info('Apple Decoded Data:', $data);
            }
        } catch (Exception $exception) {
            Log::error('Social Login Exception:', ['message' => $exception->getMessage()]);
            return response()->json(['error' => translate('wrong_credential')]);
        }

        // yahan se user create/update process ka logging
        Log::info('User Data from Provider:', $data);

        if ($request['medium'] == 'apple' && isset($data['email'])) {
            Log::info('Apple User Email Found:', ['email' => $data['email']]);

            $fast_name = strstr($data['email'], '@', true);
            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                Log::info('Creating new Apple user');
                $user = User::create([
                    'f_name' => $fast_name,
                    'email' => $data['email'],
                    'phone' => '',
                    'password' => bcrypt($data['email']),
                    'is_active' => 1,
                    'login_medium' => $request['medium'],
                    'social_id' => $data['sub'],
                    'is_phone_verified' => 0,
                    'is_email_verified' => 1,
                    'referral_code' => Helpers::generate_referer_code(),
                    'temporary_token' => Str::random(40)
                ]);
            } else {
                Log::info('Apple user already exists, updating token', ['user_id' => $user->id]);
                $user->temporary_token = Str::random(40);
                $user->save();
            }

            if (!isset($user->phone)) {
                return response()->json([
                    'token_type' => 'update phone number',
                    'temporary_token' => $user->temporary_token
                ]);
            }

            $token = self::login_process_passport($user, $user['email'], $data['email']);
            if ($token != null) {
                CartManager::cartListSessionToDatabase($request);
                return response()->json(['token' => $token]);
            }
            return response()->json(['error_message' => translate('customer_not_found_or_account_has_been_suspended')]);
        } elseif (isset($data['email']) && strcmp($email, $data['email']) === 0) {
            Log::info('Google/Facebook Email Match Success', ['email' => $email]);

            $name = explode(' ', $data['name']);
            if (count($name) > 1) {
                $fast_name = implode(" ", array_slice($name, 0, -1));
                $last_name = end($name);
            } else {
                $fast_name = implode(" ", $name);
                $last_name = '';
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                Log::info('Creating new Google/Facebook user');
                $user = User::create([
                    'f_name' => $fast_name,
                    'l_name' => $last_name,
                    'email' => $email,
                    'phone' => '',
                    'password' => bcrypt($data['id']),
                    'is_active' => 1,
                    'login_medium' => $request['medium'],
                    'social_id' => $data['id'],
                    'is_phone_verified' => 0,
                    'is_email_verified' => 1,
                    'referral_code' => Helpers::generate_referer_code(),
                    'temporary_token' => Str::random(40)
                ]);
            } else {
                Log::info('Existing Google/Facebook user found', ['user_id' => $user->id]);
                $user->temporary_token = Str::random(40);
                $user->save();
            }

            if (!isset($user->phone)) {
                return response()->json([
                    'token_type' => 'update phone number',
                    'temporary_token' => $user->temporary_token
                ]);
            }

            $token = self::login_process_passport($user, $user['email'], $data['id']);
            if ($token != null) {
                CartManager::cartListSessionToDatabase($request);
                return response()->json(['token' => $token]);
            }
            return response()->json(['error_message' => translate('customer_not_found_or_account_has_been_suspended')]);
        }

        Log::warning('Email does not match:', ['input_email' => $email, 'provider_email' => $data['email'] ?? null]);
        return response()->json(['error' => translate('email_does_not_match')]);
    }


    public static function login_process_passport($user, $email, $password)
    {
        $token = null;
        if (isset($user)) {
            auth()->login($user);
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
        }

        return $token;
    }

    public function update_phone(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'temporary_token' => 'required',
            'phone' => 'required|min:11|max:14'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $user = User::where(['temporary_token' => $request->temporary_token])->first();
        $user->phone = $request->phone;
        $user->save();


        $phoneVerification = getLoginConfig(key: 'phone_verification');

        if ($phoneVerification == 1) {
            return response()->json([
                'token_type' => 'phone verification on',
                'temporary_token' => $request['temporary_token']
            ]);
        } else {
            return response()->json(['message' => translate('phone_number_updated_successfully')]);
        }
    }



    public function customerSocialLogin(Request $request): JsonResponse
    {
        Log::info('Customer Social Login Request:', $request->all());

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'unique_id' => 'required',
            'email' => 'required_if:medium,google,facebook',
            'medium' => 'required|in:google,facebook,apple',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation Failed:', $validator->errors()->toArray());
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $token = $request['token'];
        $email = $request['email'] ?? null;
        $uniqueId = $request['unique_id'];
        $data = null;

        try {
            if ($request['medium'] == 'google') {
                // ✅ Google Token decode
                $parts = explode(".", $token);
                if (count($parts) !== 3) {
                    throw new \Exception("Invalid Google Token");
                }
                $payload = json_decode(base64_decode($parts[1]), true);

                $data = [
                    'email' => $payload['email'] ?? null,
                    'name' => $payload['name'] ?? null,
                    'picture' => $payload['picture'] ?? null,
                    'id' => $payload['sub'] ?? null,
                ];
            } elseif ($request['medium'] == 'facebook') {
                $res = Http::get("https://graph.facebook.com/{$uniqueId}", [
                    'access_token' => $token,
                    'fields' => 'name,email'
                ]);
                $data = $res->json();
            } elseif ($request['medium'] == 'apple') {
                $apple_login = getWebConfig(name: 'apple_login');

                $teamId = $apple_login['team_id'];
                $keyId = $apple_login['key_id'];
                $sub = $apple_login['client_id'];
                $aud = 'https://appleid.apple.com';
                $iat = strtotime('now');
                $exp = strtotime('+60days');
                $keyContent = file_get_contents('storage/app/public/apple-login/' . $apple_login['service_file']);

                $clientSecret = JWT::encode([
                    'iss' => $teamId,
                    'iat' => $iat,
                    'exp' => $exp,
                    'aud' => $aud,
                    'sub' => $sub,
                ], $keyContent, 'ES256', $keyId);

                $redirect_uri = $apple_login['redirect_url'] ?? 'www.example.com/apple-callback';

                $res = Http::asForm()->post('https://appleid.apple.com/auth/token', [
                    'grant_type' => 'authorization_code',
                    'code' => $uniqueId,
                    'redirect_uri' => $redirect_uri,
                    'client_id' => $sub,
                    'client_secret' => $clientSecret,
                ]);

                $claims = explode('.', $res['id_token'])[1];
                $data = json_decode(base64_decode($claims), true);
            }
        } catch (Exception $exception) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'Invalid Token']]], 401);
        }

        if (!$data || !isset($data['email'])) {
            return response()->json(['error' => 'Email not found in provider data'], 400);
        }

        // ✅ Check if user exists
        $user = $this->customerRepo->getFirstWhere(params: ['email' => $data['email']]);

        if (!$user) {
            // ✅ New user → register
            $nameParts = explode(" ", $data['name'] ?? "");
            $firstName = $nameParts[0] ?? 'User';
            $lastName = $nameParts[1] ?? '';

            $user = $this->customerRepo->add([
                'f_name' => $firstName,
                'l_name' => $lastName,
                'name' => $firstName  . ' ' . $lastName,
                'email' => $data['email'],
                'user_name' => $request['user_name'] ?? Str::slug($firstName) . rand(100, 999),
                'image' => $data['picture'] ?? null,
                'is_active' => 1,
                'is_email_verified' => 1,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(8)),
            ]);

            Log::info('New User Registered via Social Login', ['id' => $user->id]);

            try {
                $companyName = getWebConfig(name: 'company_name') ?? 'Buio';
                $companyLogo = getWebConfig(name: 'company_mobile_logo');

                $title    = "Welcome to " . $companyName;
                $body     = "Start your food journey and earn loyalty points";
                $imageUrl = $companyLogo ? asset('storage/company/' . $companyLogo) : null;

                $tokens = $user->cm_firebase_token;

                // agar array ya json stored hai to decode kar
                if (is_string($tokens) && $this->isJson($tokens)) {
                    $tokens = json_decode($tokens, true);
                }

                // ab ensure karlo ki hamesha array hi hai
                $tokens = is_array($tokens) ? $tokens : [$tokens];

                foreach ($tokens as $token) {
                    if (!empty($token)) {
                        $this->sendPushNotificationV1($token, $title, $body, $imageUrl);
                        Log::info("📩 Welcome notification sent", [
                            'user_id' => $user->id,
                            'token'   => $token,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error("❌ Failed to send welcome notification", [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        } else {
            // ✅ Existing user → update verification
            $this->customerRepo->updateWhere(params: ['email' => $data['email']], data: [
                'is_email_verified' => 1,
                'email_verified_at' => now()
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['error' => 'User is not active'], 403);
        }

        $token = $user->createToken('LaravelAuthApp')->plainTextToken;

        return response()->json([
            'status' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->f_name,
                'last_name' => $user->l_name,
                'email' => $user->email,
                'username' => $user->user_name,
                'phone' => $user->phone,
                'points' => $user->points ?? 0,
                'date_of_birth' => $user->date_of_birth
                    ? Carbon::parse($user->date_of_birth)->format('Y-m-d')
                    : null,
                'image' => $user->image ? asset('storage/profile/' . $user->image) : null,
            ]
        ]);
    }




    public function existingAccountCheck(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'user_response' => 'required|in:0,1',
            'medium' => 'required|in:google,facebook,apple',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $user = $this->customerRepo->getFirstWhere(params: ['email' => $request['email']]);

        $temporaryToken = Str::random(40);
        if (!$user) {
            return response()->json(['temp_token' => $temporaryToken, 'status' => false]);
        }

        if ($request['user_response'] == 1) {
            $this->customerRepo->updateWhere(params: ['id' => $user['id']], data: [
                'email_verified_at' => now(),
                'login_medium' => $request['medium'],
            ]);

            $token = $user->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['token' => $token, 'status' => true]);
        }

        $this->customerRepo->updateWhere(params: ['id' => $user['id']], data: [
            'email' => null,
            'email_verified_at' => null,
        ]);

        return response()->json(['temp_token' => $temporaryToken, 'status' => false]);
    }

    public function registrationWithSocialMedia(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|min:6|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $isPhoneExist = $this->customerRepo->getFirstWhere(params: ['phone' => $request['phone']]);

        if ($isPhoneExist) {
            return response()->json(['errors' => [
                ['code' => 'email', 'message' => translate('This phone has already been used in another account!')]
            ]], 403);
        }
        $temporaryToken = Str::random(40);

        $user = $this->customerRepo->add(data: [
            'name' => $request['name'],
            'f_name' => $request['name'],
            'l_name' => '',
            'email' => $request['email'],
            'phone' => $request['phone'],
            'password' => bcrypt(rand(11111111, 99999999)),
            'temporary_token' => $temporaryToken,
            'email_verified_at' => now(),
            'referral_code' => Helpers::generate_referer_code(),
            'login_medium' => 'social',
        ]);

        $phoneVerificationStatus = getLoginConfig(key: 'phone_verification') ?? 0;
        if ($phoneVerificationStatus) {
            return response()->json(['temp_token' => $temporaryToken, 'status' => false]);
        }

        $token = $user->createToken('LaravelAuthApp')->accessToken;
        return response()->json(['token' => $token]);
    }
}
