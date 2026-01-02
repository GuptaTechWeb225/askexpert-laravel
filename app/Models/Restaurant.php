<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use App\Traits\StorageTrait;
use Illuminate\Support\Facades\DB;




class Restaurant extends Authenticatable
{
    use SoftDeletes, HasApiTokens, StorageTrait;


    protected $table = 'restaurants';
    protected $fillable = [
        'owner_name',
        'email',
        'password',
        'phone',
        'restaurant_name',
        'logo_image',
        'bg_image',
        'address',
        'city',
        'state',
        'zip_code',
        'latitude',
        'longitude',
        'gst_number',
        'gst_copy',
        'tax_number',
        'tax_copy',
        'fssai_number',
        'fssai_copy',
        'pan_number',
        'pan_copy',
        'plan_id',
        'plan_name',
        'website_url',
        'restaurant_description',
        'restaurant_menu',
        'restaurant_features',
        'restaurant_images',
        'restaurant_hours_from',
        'restaurant_hours_to',
        'sun_restaurant_hours_from',
        'sun_restaurant_hours_to',
        'mon_restaurant_hours_from',
        'mon_restaurant_hours_to',
        'tue_restaurant_hours_from',
        'tue_restaurant_hours_to',
        'wed_restaurant_hours_from',
        'wed_restaurant_hours_to',
        'thu_restaurant_hours_from',
        'thu_restaurant_hours_to',
        'fri_restaurant_hours_from',
        'fri_restaurant_hours_to',
        'sat_restaurant_hours_from',
        'sat_restaurant_hours_to',
        'is_active',
        'status',
        'reject_resone',
        'notified',
        'boost',
        'temp_block_time',
        'is_temp_blocked',
        'login_hit_count',
        'temporary_token',
        'cm_firebase_token',
        'email_verified_at',
        'is_email_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'restaurant_features' => 'array',
        'restaurant_images' => 'array',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'logo_image_url',
        'bg_image_url',
        'gst_copy_url',
        'tax_copy_url',
        'fssai_copy_url',
        'pan_copy_url',
        'restaurant_menu_url',
        'restaurant_images_url',
    ];



    public function getFileUrl($field)
    {
        if ($this->{$field}) {
            $path = 'restaurants/' . $this->{$field};

            return asset(Storage::url($path));
        }
    }

    public function getLogoImageUrlAttribute()
    {
        return $this->getFileUrl('logo_image');
    }

    public function getBgImageUrlAttribute()
    {
        return $this->getFileUrl('bg_image');
    }

    public function getGstCopyUrlAttribute()
    {
        return $this->getFileUrl('gst_copy');
    }

    public function getTaxCopyUrlAttribute()
    {
        return $this->getFileUrl('tax_copy');
    }

    public function getFssaiCopyUrlAttribute()
    {
        return $this->getFileUrl('fssai_copy');
    }

    public function getPanCopyUrlAttribute()
    {
        return $this->getFileUrl('pan_copy');
    }

    public function getRestaurantMenuUrlAttribute()
    {
        return $this->getFileUrl('restaurant_menu');
    }

    public function getRestaurantImagesUrlAttribute()
    {
        if (!$this->restaurant_images) {
            return null;
        }

        $images = json_decode($this->restaurant_images, true);

        return array_map(function ($path) {
            if (!str_starts_with($path, 'restaurants/')) {
                $path = 'restaurants/' . ltrim($path, '/');
            }

            return asset(Storage::url($path));
        }, $images);
    }


    public function billPayments()
    {
        return $this->hasMany(RestaurantBillPayment::class, 'restaurant_id');
    }



    public function getImageFullUrlAttribute(): array
    {
        $value = $this->logo_image;
        if (count($this->storage) > 0) {
            $storage = $this->storage->where('key', 'image')->first();
        }
        return $this->storageLink('restaurants', $value, $storage['value'] ?? 'public');
    }

    public function coupons()
    {
        return $this->hasMany(RestaurantCoupon::class, 'restaurant_id');
    }

    public function reviews()
    {
        return $this->hasMany(RestaurantReview::class);
    }

    public function getAverageRatingAttribute()
    {
        if ($this->reviews->count() == 0) {
            return 0;
        }

        return round($this->reviews->avg('rating'), 1); // 1 decimal तक
    }

    public function scopeBoosted($query)
    {
        return $query->orderByDesc('boost'); // 1 wale top pe
    }
    public function scopeBoostedWithRating($query)
    {
        return $query
            ->withAvg('reviews', 'rating')   // rating ka join
            ->orderByDesc('boost')           // boost = 1 pehle
            ->orderByDesc('reviews_avg_rating'); // fir rating ke hisaab se
    }


    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function tables()
    {
        return $this->hasMany(RestaurantTable::class);
    }

    public function bookings()
    {
        return $this->hasMany(RestaurantBooking::class);
    }

    public function waitlists()
    {
        return $this->hasMany(RestaurantWaitlist::class);
    }

    public function scopeBoostedWithCustomRanking($query, $latitude = null, $longitude = null)
    {
        return $query
            ->when($latitude && $longitude, function ($q) use ($latitude, $longitude) {
                $q->selectRaw(
                    "restaurants.*, 
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) 
                + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                    [$latitude, $longitude, $latitude]
                )
                    ->having('distance', '<=', 30);
            })
            ->where('boost', 1)
            ->selectRaw('restaurants.*,
            ( (COALESCE((select avg(rating) from restaurant_reviews where restaurant_reviews.restaurant_id = restaurants.id),0) * 0.5) +
              (COALESCE((select sum(coins_used) from restaurant_bill_payments where restaurant_bill_payments.restaurant_id = restaurants.id),0) * 0.25) +
              (COALESCE((select sum(coins_earned) from restaurant_bill_payments where restaurant_bill_payments.restaurant_id = restaurants.id),0) * 0.25)
            ) as boost_score')
            ->orderByDesc('boost_score');
    }


    protected static function booted()
    {
        static::deleting(function ($restaurant) {
            $restaurant->billPayments()->delete();
            $restaurant->coupons()->delete();
            $restaurant->reviews()->delete();
            $restaurant->tables()->delete();
            $restaurant->bookings()->delete();
            $restaurant->waitlists()->delete();

            $imagesToDelete = [
                $restaurant->logo_image,
                $restaurant->bg_image,
                $restaurant->gst_copy,
                $restaurant->tax_copy,
                $restaurant->fssai_copy,
                $restaurant->pan_copy,
                $restaurant->restaurant_menu,
            ];

            if (is_array($restaurant->restaurant_images)) {
                $imagesToDelete = array_merge($imagesToDelete, $restaurant->restaurant_images);
            } elseif (!empty($restaurant->restaurant_images)) {
                $decoded = json_decode($restaurant->restaurant_images, true);
                if (is_array($decoded)) {
                    $imagesToDelete = array_merge($imagesToDelete, $decoded);
                }
            }

            foreach ($imagesToDelete as $file) {
                if ($file && Storage::exists('restaurants/' . $file)) {
                    Storage::delete('restaurants/' . $file);
                }
            }
        });
    }
}
