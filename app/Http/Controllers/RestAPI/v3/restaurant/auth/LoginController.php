<?php

namespace App\Http\Controllers\RestAPI\v3\restaurant\auth;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\SellerWallet;
use App\Utils\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\GuestUser;


class LoginController extends Controller
{
    public function login(Request $request)
    {
        Log::info('Restaurant Register Request Data:', $request->all());

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $restaurant = Restaurant::with('reviews')->where('email', $request->email)->first();

        if (!$restaurant || !Hash::check($request->password, $restaurant->password)) {
            return response()->json([
                'errors' => [
                    ['code' => 'auth-001', 'message' => 'Invalid credentials.']
                ],
                'loginStatus' => 'unauthorized'
            ], 401);
        }

        if ($restaurant->status === 'rejected') {
            return response()->json([
                'errors' => [
                    [
                        'code' => 'auth-003',
                        'message' => 'Your account is rejected. Reason: ' . ($restaurant->reject_resone ?? 'No reason provided')
                    ]
                ],
                'loginStatus' => 'rejected'
            ], 401);
        }

        if ($restaurant->status !== 'approved') {
            return response()->json([
                'errors' => [
                    ['code' => 'auth-002', 'message' => 'Your account is not approved yet.']
                ],
                'loginStatus' => 'pending'
            ], 401);
        }

        $tokenResult = $restaurant->createToken('restaurant_token', ['*']);
        $token = $tokenResult->plainTextToken;

        DB::table('personal_access_tokens')
            ->where('id', $tokenResult->accessToken->id ?? null)
            ->update([
                'expires_at' => now()->addDays(120) // 120 din baad expire hoga
            ]);

        $restaurant->average_rating = $restaurant->average_rating;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'data' => $restaurant
        ], 200);
    }



    public function update_cm_firebase_token(Request $request)
    {
        Log::info("📥 FCM Token Update Request", [
            'token'    => $request->token,
            'restaurant_id' => $request->restaurant_id ?? null,
            'headers'  => $request->headers->all(),
        ]);

        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $restaurant = Helpers::getRestaurantInformation($request);

        if ($restaurant == 'offline') {
            Log::warning("⚠️ Restaurant Offline Detected", [
                'restaurant_id' => $request->restaurant_id
            ]);
        } else {
            DB::table('restaurants')->where('id', $restaurant->id)->update([
                'cm_firebase_token' => $request['token'],
            ]);

            Log::info("✅ Restaurant FCM Token Updated", [
                'restaurant_id' => $restaurant->id,
                'token'   => $request['token']
            ]);
        }

        return response()->json(['message' => translate('successfully updated!')], 200);
    }
}
