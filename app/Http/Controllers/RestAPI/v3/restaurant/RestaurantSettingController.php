<?php

namespace App\Http\Controllers\RestAPI\v3\restaurant;

use App\Enums\WebConfigKey;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\RestaurantSetting;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RestaurantSettingController extends Controller
{
    public function updateOrCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id'          => 'required|exists:restaurants,id',
            'coin_value'             => 'required|integer|min:1',
            'min_order_amount'       => 'required|numeric|min:0',
            'min_order_active'       => 'required|boolean',
            'max_coin_usage_percent' => 'required|integer|min:0|max:100',
            'daily_redeem_limit'     => 'required|integer|min:0',
            'daily_visit_limit'      => 'required|integer|min:0',
            'monthly_redeem_limit'   => 'required|integer|min:0',
            'earning_amount'         => 'required|integer|min:1',
            'earning_coin'           => 'required|integer|min:1',
            'status'                 => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $restaurant = Restaurant::find($request->restaurant_id);
        if ($restaurant && $restaurant->status === 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Restaurant is pending, settings cannot be updated.'
            ], 403);
        }
        $setting = RestaurantSetting::updateOrCreate(
            ['restaurant_id' => $request->restaurant_id], // condition
            $request->all() // values
        );

        return response()->json([
            'status'  => true,
            'message' => $setting->wasRecentlyCreated
                ? 'Restaurant setting created successfully'
                : 'Restaurant setting updated successfully',
            'data'    => $setting
        ], 200);
    }
    public function restaurantSetting($id)
    {

        $setting = RestaurantSetting::where('restaurant_id', $id)->first();

        if (!$setting) {
            return response()->json([
                'status'  => false,
                'message' => 'No settings found for this restaurant'
            ], 404);
        }
        return response()->json([
            'status'  => true,
            'message' => 'Restaurant setting fetched successfully',
            'data'    => $setting
        ], 200);
    }


    public function saveTables(Request $request, $restaurantId)
    {
      

        $request->validate([
            'tables' => 'required|array',
            'tables.*.table_size' => 'required|integer|min:1',
            'tables.*.table_count' => 'required|integer|min:1',
            'tables.*.avg_turnover_time' => 'required|integer|min:10',
        ]);

        $restaurant = Restaurant::find($restaurantId);

        if (!$restaurant) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found.'
            ], 404);
        }

        foreach ($request->tables as $table) {
            RestaurantTable::create([
                'restaurant_id' => $restaurantId,
                'table_size' => $table['table_size'],
                'table_count' => $table['table_count'],
                'avg_turnover_time' => $table['avg_turnover_time'],
            ]);
        }

        $tables = RestaurantTable::where('restaurant_id', $restaurantId)->get();

     
        return response()->json([
            'status' => true,
            'message' => 'Table settings updated successfully.',
            'tables' => $tables
        ], 200);
    }


    public function getTables($restaurantId)
    {
        $restaurant = Restaurant::with('tables')->find($restaurantId);

        if (!$restaurant) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Table settings fetched successfully.',
            'data' => $restaurant->tables,
        ], 200);
    }


    public function deleteTable(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|integer',
            'table_id' => 'required|integer',
        ]);

        $restaurantId = $request->restaurant_id;
        $tableId = $request->table_id;

        $restaurant = Restaurant::find($restaurantId);
        if (!$restaurant) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found.'
            ], 404);
        }

        $table = RestaurantTable::where('restaurant_id', $restaurantId)
            ->where('id', $tableId)
            ->first();

        if (!$table) {
            return response()->json([
                'status' => false,
                'message' => 'Table not found for this restaurant.'
            ], 404);
        }

        $table->delete();

        return response()->json([
            'status' => true,
            'message' => 'Table deleted successfully.',
            'deleted_table_id' => $tableId
        ], 200);
    }
}
