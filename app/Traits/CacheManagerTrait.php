<?php

namespace App\Traits;

use App\Models\AnalyticScript;
use App\Models\BusinessSetting;
use App\Models\Color;
use App\Models\HelpTopic;
use App\Models\Review;
use App\Models\RobotsMetaContent;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait CacheManagerTrait
{
    public function cacheBusinessSettingsTable()
    {
        return Cache::remember(CACHE_BUSINESS_SETTINGS_TABLE, CACHE_FOR_3_HOURS, function () {
            return BusinessSetting::all();
        });
    }

    public function cacheColorsList()
    {
        return Cache::remember(CACHE_FOR_ALL_COLOR_LIST, CACHE_FOR_3_HOURS, function () {
            return Color::all();
        });
    }

   

    public function cacheProductsReviews()
    {
        return Cache::remember(CACHE_FOR_ALL_PRODUCTS_REVIEW_LIST, CACHE_FOR_3_HOURS, function () {
            return Review::active()->whereNull('delivery_man_id')->get();
        });
    }

   

    public function cacheHelpTopicTable()
    {
        return Cache::remember(CACHE_HELP_TOPICS_TABLE, CACHE_FOR_3_HOURS, function () {
            return HelpTopic::where(['type' => 'default', 'status' => 1])->get();
        });
    }



   

    public function cacheRobotsMetaContent(string $page)
    {
        $config = null;
        $settings = Cache::remember(CACHE_ROBOTS_META_CONTENT_TABLE, CACHE_FOR_3_HOURS, function () {
            return RobotsMetaContent::all();
        });
        $data = $settings?->firstWhere('page_name', $page);
        if (!$data) {
            $data = $settings?->firstWhere('page_name', 'default');
        }
        return $data ?? $config;
    }

   

    public function cacheActiveAnalyticScript()
    {
        return Cache::remember(CACHE_FOR_ANALYTIC_SCRIPT_ACTIVE_LIST, CACHE_FOR_3_HOURS, function () {
            return AnalyticScript::where(['is_active' => 1])->get();
        });
    }

    private function getUpdateLatestProductWithFlashDeal($latestProducts)
    {
        $currentDate = date('Y-m-d H:i:s');
        return $latestProducts?->map(function ($product) use ($currentDate) {
            $flashDealStatus = 0;
            $flashDealEndDate = 0;
            if (count($product->flashDealProducts) > 0) {
                $flashDeal = $product->flashDealProducts[0]->flashDeal;
                if ($flashDeal) {
                    $startDate = date('Y-m-d H:i:s', strtotime($flashDeal->start_date));
                    $endDate = date('Y-m-d H:i:s', strtotime($flashDeal->end_date));
                    $flashDealStatus = $flashDeal->status == 1 && (($currentDate >= $startDate) && ($currentDate <= $endDate)) ? 1 : 0;
                    $flashDealEndDate = $flashDeal->end_date;
                }
            }
            $product['flash_deal_status'] = $flashDealStatus;
            $product['flash_deal_end_date'] = $flashDealEndDate;
            return $product;
        });
    }
}
