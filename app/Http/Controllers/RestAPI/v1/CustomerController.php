<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\DeliveryCountryCode;
use App\Models\DeliveryZipCode;
use App\Models\GuestUser;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Review;
use App\Models\ShippingAddress;
use App\Models\SupportTicket;
use App\Models\SupportTicketConv;
use App\Models\Wishlist;
use App\Traits\CommonTrait;
use App\Traits\PdfGenerator;
use App\Traits\FileManagerTrait;
use App\Models\User;
use App\Models\RestaurantBillPayment;
use App\Utils\CustomerManager;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Traits\PushNotificationTrait;



class CustomerController extends Controller
{
    use CommonTrait, PdfGenerator, FileManagerTrait, PushNotificationTrait;

    public function info(Request $request)
    {

        $reqUser = $request->user();
        $userModel = User::find($reqUser->id);

        if (!$userModel) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $imageName = $userModel->image ?? null;
        $user = [
            'id' => $userModel->id,
            'f_name' => $userModel->f_name,
            'l_name' => $userModel->l_name,
            'user_name' => $userModel->user_name,
            'email' => $userModel->email,
            'phone' => $userModel->phone,
            'date_of_birth' => $userModel->date_of_birth
                ? Carbon::parse($userModel->date_of_birth)->format('Y-m-d')
                : null,
            'image' => $imageName ? asset('storage/profile/' . $imageName) : asset('back-end/img/placeholder/placeholder-1-1.png'),
            'loyalty_point' => $userModel->loyalty_point,
        ];

        $response = response()->json([
            'status' => true,
            'user' => $user
        ], 200);

        return $response;
    }



    public function myTransactions(Request $request)
    {
        $customer = $request->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'user not logged in'
            ], 401);
        }

        // Transactions ke saath restaurant relation eager load karo
        $transactions = RestaurantBillPayment::with('restaurant')
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $transactions = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'restaurant_id' => $transaction->restaurant_id,
                'restaurant_name' => $transaction->restaurant->restaurant_name ?? 'Unknown', // add this
                'restaurant_image' => $transaction->restaurant && $transaction->restaurant->logo_image_url
                    ?  $transaction->restaurant->logo_image_url
                    : url('assets/back-end/img/placeholder/user.png'),
                'customer_id' => $transaction->customer_id,
                'bill_uuid' => $transaction->bill_uuid,
                'original_amount' => $transaction->original_amount,
                'final_amount' => $transaction->final_amount,
                'coupon_id' => $transaction->coupon_id,
                'coupon_code' => $transaction->coupon_code,
                'coins_used' => $transaction->coins_used,
                'coins_earned' => $transaction->coins_earned,
                'status' => $transaction->status,
                'paid_at' => $transaction->paid_at,
                'meta' => $transaction->meta,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
            ];
        });


        return response()->json([
            'success' => true,
            'data' => $transactions,
        ], 200);
    }
    public function myRewards(Request $request)
    {
        $customer = $request->user();

        if (!$customer) {
            return response()->json([
                ~'success' => false,
                'message' => 'user not logged in'
            ], 401);
        }

        $transactions = RestaurantBillPayment::with('restaurant')
            ->where('customer_id', $customer->id)
            ->get();

        $rewards = $transactions->map(function ($transaction) {
            return [
                'restaurant_name' => $transaction->restaurant->restaurant_name ?? 'Unknown',
                'restaurant_image' => $transaction->restaurant->logo_image_url ?? 'Unknown',
                'coins_earned' => $transaction->coins_earned,
                'created_at' => $transaction->created_at
            ];
        });

        return response()->json([
            'success' => true,
            'rewards' => $rewards
        ], 200);
    }




    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'phone' => 'required',
            'user_name' => 'required', // add this
        ], [
            'f_name.required' => translate('First name is required!'),
            'l_name.required' => translate('Last name is required!'),
            'user_name.required' => translate('Username is required!'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $checkEmail = User::where('email', $request['email'])->where('id', '!=', $request->user()->id)->first();
        if ($checkEmail) {
            return response()->json([
                'status'  => false,
                'message' => 'Email already taken',
                'errors'  => $validator->errors()
            ], 422);
        }

        $checkPhone = User::where('phone', $request['phone'])->where('id', '!=', $request->user()->id)->first();
        if ($checkPhone) {
            return response()->json([
                'status'  => false,
                'message' => 'Phone number already taken',
                'errors'  => $validator->errors()
            ], 422);
        }

        $checkUsername = User::where('user_name', $request['user_name'])
            ->where('id', '!=', $request->user()->id)
            ->first();

        if ($checkUsername) {
            return response()->json([
                'status'  => false,
                'message' => 'Username already taken',
                'errors'  => $validator->errors()
            ], 422);
        }


        if ($request->has('image')) {
            $imageName = $this->update('profile/', $request->user()->image, 'webp', $request->file('image'));
        } else {
            $imageName = $request->user()->image;
        }

        if ($request['password'] != null && strlen($request['password']) > 5) {
            $pass = bcrypt($request['password']);
        } else {
            $pass = $request->user()->password;
        }

        $user = User::find($request->user()->id);

        $user->update([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'user_name' => $request->user_name,
            'date_of_birth' => $request->date_of_birth,
            'image' => $imageName,
            'phone' => $user->is_phone_verified ? $user->phone : $request->phone,
            'email' => $request->email,
            'is_phone_verified' => $request->phone == $user->phone ? $user->is_phone_verified : 0,
            'is_email_verified' => $request->email == $user->email ? $user->is_email_verified : 0,
            'email_verified_at' => $request->email == $user->email ? $user->email_verified_at : null,
            'password' => $pass,
            'updated_at' => now(),
        ]);

        $user->refresh();
        if ($user->cm_firebase_token) {
            $this->sendPushNotification(
                $user->cm_firebase_token,
                "Profile Updated",
                "Your profile has been updated successfully."
            );
        }
        return response()->json([
            'message' => translate('successfully updated!'),
            'user' => [
                'id' => $user->id,
                'f_name' => $user->f_name,
                'l_name' => $user->l_name,
                'user_name' => $user->user_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'date_of_birth' => $user->date_of_birth
                    ? Carbon::parse($user->date_of_birth)->format('Y-m-d')
                    : null,
                'image' => $imageName ? asset('storage/profile/' . $imageName) : null,
            ],
        ], 200);
    }
}
