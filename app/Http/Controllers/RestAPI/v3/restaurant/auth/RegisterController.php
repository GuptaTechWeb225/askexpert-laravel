<?php

namespace App\Http\Controllers\RestAPI\v3\restaurant\auth;

use App\Events\VendorRegistrationEvent;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantSetting;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\RestaurantBillPayment;


class RegisterController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        Log::info('Restaurant login Request Data:', $request->all());
        $validator = Validator::make($request->all(), [
            'owner_name' => 'required|string|max:255',
            'email' => 'required|email|unique:restaurants,email',
            'password' => 'required|min:6',
            'phone' => 'required|string|max:20',
            'restaurant_name' => 'required|string|max:255',
            'logo_image' => 'required|image|mimes:jpeg,png,jpg,webp',
            'bg_image' => 'required|image|mimes:jpeg,png,jpg,webp',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'gst_number' => 'required|string',
            'gst_copy' => 'required|image',
            'tax_number' => 'required|string',
            'tax_copy' => 'required|image',
            'fssai_number' => 'required|string',
            'fssai_copy' => 'required|image',
            'pan_number' => 'required|string',
            'pan_copy' => 'required|image',
            'restaurant_description' => 'required|string',
            'restaurant_menu' => 'required|file|max:10240',
            'restaurant_features' => 'nullable|array',
            'business_hours' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }


        $storage = config('filesystems.disks.default') ?? 'public';
        DB::beginTransaction();
        try {
            $data = $request->all();
            $uploadPath = 'uploads/restaurants/';
            $fileFields = [
                'logo_image',
                'bg_image',
                'gst_copy',
                'tax_copy',
                'fssai_copy',
                'pan_copy',
                'restaurant_menu'
            ];

            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $filename = time() . '_' . $request->file($field)->getClientOriginalName();
                    $request->file($field)->storeAs('restaurants', $filename, 'public');
                    $data[$field] = $filename;
                }
            }

            $data['password'] = Hash::make($data['password']);
            if (!empty($request->restaurant_features)) {
                $data['restaurant_features'] = json_encode($request->restaurant_features);
            }
            if ($request->hasFile('restaurant_images')) {
                $restaurantImages = [];
                foreach ($request->file('restaurant_images') as $image) {
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $image->storeAs('restaurants', $filename, 'public');
                    $restaurantImages[] = $filename;
                }
                $data['restaurant_images'] = json_encode($restaurantImages);
            }


            if (!empty($data['business_hours'])) {
                $businessHours = json_decode($data['business_hours'], true);
                $days = [
                    'monday' => 'mon',
                    'tuesday' => 'tue',
                    'wednesday' => 'wed',
                    'thursday' => 'thu',
                    'friday' => 'fri',
                    'saturday' => 'sat',
                    'sunday' => 'sun',
                ];

                foreach ($days as $dayFull => $dayShort) {
                    if (isset($businessHours[ucfirst($dayFull)])) {
                        $dayData = $businessHours[ucfirst($dayFull)];
                        $data["{$dayShort}_restaurant_hours_from"] = $dayData['isOpen'] ? $dayData['open'] : null;
                        $data["{$dayShort}_restaurant_hours_to"]   = $dayData['isOpen'] ? $dayData['close'] : null;
                    }
                }
            }

            $restaurant = Restaurant::create($data);

            event(new \App\Events\NewRestaurantRegistered($restaurant));

            $token = $restaurant->createToken('restaurant_token')->plainTextToken;
            RestaurantSetting::create([
                'restaurant_id'          => $restaurant->id,
                'coin_value'             => 10,
                'min_order_amount'       => 1000,
                'min_order_active'       => 1,
                'max_coin_usage_percent' => 50,
                'daily_redeem_limit'     => 500,
                'daily_visit_limit'      => 2,
                'monthly_redeem_limit'   => 2000,
                'earning_amount'         => 10,
                'earning_coin'           => 1,
                'status'                 => 'active'
            ]);

            DB::commit();
            $restaurant->average_rating = $restaurant->average_rating;
            $restaurant->reviews_count = $restaurant->reviews()->count();

            // $data = [
            //     'vendorName' => $request['restaurant_name'],
            //     'status' => 'pending',
            //     'subject' => translate('Restaurant_Registration_Successfully_Completed'),
            //     'title' => translate('Restaurant_Registration_Successfully_Completed'),
            //     'userType' => 'restaurant',
            //     'templateName' => 'registration',
            // ];
            // event(new VendorRegistrationEvent(email: $request['email'], data: $data));

            $responseData = [
                'status' => true,
                'message' => 'Restaurant registered successfully!',
                'token' => $token,
                'data' => $restaurant->toArray()
            ];

            Log::info('Restaurant Signup Response:', $responseData);

            return response()->json($responseData, 200);
        } catch (\Exception $e) {
            $errorResponse = [
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ];

            Log::error('Restaurant Signup Failed:', $errorResponse);

            return response()->json($errorResponse, 500);
        }
    }




    public function restaurantUpdate(Request $request, $id): JsonResponse
    {

        Log::info('Restaurant update Request Data:', $request->all());
        Log::info('Restaurant update id Data:', ['id' => $id]);
        Log::info('Files:', $request->allFiles());
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }
        if ($restaurant->status === 'pending' || $restaurant->status == 0) {
            return response()->json([
                'status' => false,
                'message' => 'You can’t update profile until admin approves your request.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'owner_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'restaurant_name' => 'sometimes|string|max:255',
            'logo_image' => 'sometimes|image|mimes:jpeg,png,jpg,webp',
            'bg_image' => 'sometimes|image|mimes:jpeg,png,jpg,webp',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string',
            'state' => 'sometimes|string',
            'zip_code' => 'sometimes|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'restaurant_description' => 'sometimes|string',
            'restaurant_menu' => 'sometimes|file|max:10240',
            'restaurant_features' => 'nullable|array',
            'business_hours' => 'nullable|json',
            'restaurant_images.*'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10000'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            Log::warning('Restaurant update validation failed', [
                'errors'  => $errors,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => implode(' | ', $errors),
                'errors'  => $validator->errors(),
            ], 422);
        }
        Log::info('Restaurant Validation Passed', [
            'input' => $request->all()
        ]);

        $data = $request->only([
            'owner_name',
            'phone',
            'restaurant_name',
            'address',
            'city',
            'state',
            'zip_code',
            'latitude',
            'longitude',
            'restaurant_description',
        ]);
        $data = $request->except('restaurant_images');


        $fileFields = ['logo_image', 'bg_image', 'restaurant_menu'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $filename = time() . '_' . $request->file($field)->getClientOriginalName();
                $request->file($field)->storeAs('restaurants', $filename, 'public');
                $data[$field] = $filename;
            }
        }

        if ($request->hasFile('restaurant_images')) {
            $restaurantImages = [];
            foreach ($request->file('restaurant_images') as $image) {
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('restaurants', $filename, 'public');
                $restaurantImages[] = $filename;
            }
            $data['restaurant_images'] = json_encode($restaurantImages);
        }


        if (!empty($request->restaurant_features)) {
            $data['restaurant_features'] = json_encode($request->restaurant_features);
        }

        if (!empty($request->business_hours)) {
            $businessHours = json_decode($request->business_hours, true);
            $days = [
                'monday' => 'mon',
                'tuesday' => 'tue',
                'wednesday' => 'wed',
                'thursday' => 'thu',
                'friday' => 'fri',
                'saturday' => 'sat',
                'sunday' => 'sun',
            ];

            foreach ($days as $dayFull => $dayShort) {
                if (isset($businessHours[ucfirst($dayFull)])) {
                    $dayData = $businessHours[ucfirst($dayFull)];
                    $data["{$dayShort}_restaurant_hours_from"] = $dayData['isOpen'] ? $dayData['open'] : null;
                    $data["{$dayShort}_restaurant_hours_to"]   = $dayData['isOpen'] ? $dayData['close'] : null;
                }
            }
        }

        $restaurant->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Restaurant updated successfully',
            'data' => $restaurant
        ], 200);
    }




    public function loadRestaurantData($id)
    {

        try {
            $restaurant = Restaurant::find($id);

            if (!$restaurant) {
                return response()->json([
                    'status' => false,
                    'message' => 'Restaurant not found'
                ], 404);
            }

            $restaurant->average_rating = $restaurant->average_rating;
            $restaurant->reviews_count  = $restaurant->reviews()->count();
            $restaurant->reviews->transform(function ($review) {
                $review->customer_name = $review->customer ? $review->customer->f_name . ' ' . $review->customer->l_name : null;
                unset($review->customer);   
                return $review;
            });

            return response()->json([
                'status' => true,
                'data'   => $restaurant
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
