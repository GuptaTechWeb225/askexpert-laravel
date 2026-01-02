<?php

namespace App\Http\Controllers\RestAPI\v3\restaurant;

use App\Enums\WebConfigKey;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Illuminate\Support\Str;
use App\Models\RestaurantCoupon;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Validator;
use App\Models\Restaurant;


class RestaurantCouponController extends Controller
{
    public function couponStore(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'restaurant_id'        => 'required|integer|exists:restaurants,id',
            'restaurant_name'      => 'required|string|max:255',
            'coupon_title'         => 'required|string|max:255',
            'coupon_description'   => 'nullable|string',
            'coupon_code'          => 'required|string|max:50|unique:restaurant_coupons,coupon_code',
            'limit_for_same_user'  => 'required|integer|min:1',
            'discount_type'        => 'required|in:flat,percent',
            'discount_amount'      => 'required|numeric|min:0',
            'minimum_purchase'     => 'nullable|numeric|min:0',
            'min_point_require'    => 'nullable|integer|min:0',
            'max_point_use'        => 'nullable|integer|min:0',
            'start_date'           => 'required|date',
            'end_date'             => 'required|date|after_or_equal:start_date',
        ]);


        $validator->after(function ($validator) use ($request) {
            if ($request->discount_type === 'flat' && $request->minimum_purchase < $request->discount_amount) {
                $validator->errors()->add('minimum_purchase', 'Minimum purchase cannot be less than discount amount.');
            }
            if ($request->min_point_require && $request->max_point_use && $request->min_point_require > $request->max_point_use) {
                $validator->errors()->add('min_point_require', 'Minimum points required cannot be greater than maximum points allowed.');
            }
        });

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
         
            return response()->json([
                'success' => false,
                'message' => implode(' | ', $errors), 
                'errors'  => $validator->errors(),
            ], 422);
        }
        $restaurant = Restaurant::find($request->restaurant_id);
        if ($restaurant && $restaurant->status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'You can’t create a coupon until admin approves your request.',
            ], 403);
        }

        $coupon = RestaurantCoupon::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Coupon created successfully',
            'data'    => $coupon,
        ], 201);
    }

    public function couponAll(Request $request, $id)
    {

        $coupons = RestaurantCoupon::where('restaurant_id', $id)->get();

        if ($coupons->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No coupons available for this restaurant',
                'data'    => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupons fetched successfully',
            'data'    => $coupons,
        ], 200);
    }
    public function couponShow($id)
    {
        $coupon = RestaurantCoupon::findOrFail($id);

        if ($coupon->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No coupons found',
                'data'    => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupons fetched successfully',
            'data'    => $coupon,
        ], 200);
    }
    public function couponDelete($id)
    {
        $coupon = RestaurantCoupon::findOrFail($id);

        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupon deleted successfully',
        ], 200);
    }
}
