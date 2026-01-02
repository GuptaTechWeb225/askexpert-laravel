<?php

namespace App\Models;

use App\Models\ShippingAddress;
use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string $f_name
 * @property string $l_name
 * @property string $phone
 * @property string $image
 * @property string $email
 * @property $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property $created_at
 * @property $updated_at
 * @property string $street_address
 * @property string $country
 * @property string $city
 * @property string $zip
 * @property string $house_no
 * @property string $apartment_no
 * @property string|null $cm_firebase_token
 * @property bool $is_active
 * @property string|null $payment_card_last_four
 * @property string|null $payment_card_brand
 * @property string|null $payment_card_fawry_token
 * @property string|null $login_medium
 * @property string|null $social_id
 * @property bool $is_phone_verified
 * @property string|null $temporary_token
 * @property bool $is_email_verified
 * @property float $wallet_balance
 * @property float $loyalty_point
 * @property int $login_hit_count
 * @property bool $is_temp_blocked
 * @property $temp_block_time
 * @property string|null $referral_code
 * @property int $referred_by
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use Notifiable, HasApiTokens, StorageTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'f_name',
        'l_name',
        'phone',
        'image',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'last_active_at',
        'street_address',
        'country',
        'city',
        'zip',
        'house_no',
        'apartment_no',
        'cm_firebase_token',
        'is_active',
        'payment_card_last_four',
        'payment_card_brand',
        'payment_card_fawry_token',
        'login_medium',
        'social_id',
        'is_phone_verified',
        'temporary_token',
        'is_email_verified',
        'wallet_balance',
        'loyalty_point',
        'login_hit_count',
        'is_temp_blocked',
        'temp_block_time',
        'referral_code',
        'referred_by',
        'user_name',
        'date_of_birth',
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'f_name' => 'string',
        'l_name' => 'string',
        'phone' => 'string',
        'image' => 'string',
        'email' => 'string',
        'email_verified_at' => 'datetime',
        'password' => 'string',
        'remember_token' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'date_of_birth' => 'datetime',
        'last_active_at' => 'datetime',
        'street_address' => 'string',
        'country' => 'string',
        'city' => 'string',
        'zip' => 'string',
        'house_no' => 'string',
        'apartment_no' => 'string',
        'cm_firebase_token' => 'string',
        'is_active' => 'boolean',
        'payment_card_last_four' => 'string',
        'payment_card_brand' => 'string',
        'payment_card_fawry_token' => 'string',
        'login_medium' => 'string',
        'social_id' => 'string',
        'is_phone_verified' => 'boolean',
        'temporary_token' => 'string',
        'is_email_verified' => 'boolean',
        'wallet_balance' => 'float',
        'loyalty_point' => 'float',
        'login_hit_count' => 'integer',
        'is_temp_blocked' => 'boolean',
        'temp_block_time' => 'datetime',
        'referral_code' => 'string',
        'referred_by' => 'integer',
    ];

    // Old Relation: wish_list


    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function getFullNameAttribute()
    {
        return $this->f_name . ' ' . $this->l_name;
    }
    public function getImageFullUrlAttribute(): array
    {
        $value = $this->image;
        if (count($this->storage) > 0) {
            $storage = $this->storage->where('key', 'image')->first();
        }
        return $this->storageLink('profile', $value, $storage['value'] ?? 'public');
    }
    protected $appends = ['image_full_url'];

    protected static function boot(): void
    {
        parent::boot();
        static::saved(function ($model) {
            if ($model->isDirty('image')) {
                $storage = config('filesystems.disks.default') ?? 'public';
                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'image',
                ], [
                    'value' => $storage,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }


    public function payments()
    {
        return $this->hasMany(UserPayment::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    // Joining fee paid hai ya nahi?
    public function hasPaidJoiningFee(): bool
    {
        return $this->payments()
            ->where('type', 'joining_fee')
            ->paid()
            ->exists();
    }

    // Active membership hai current category ke liye?
    public function hasActiveMembership(?int $categoryId = null): bool
    {
        $query = $this->subscriptions()->active();

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->exists();
    }

    public function hasAnyActiveMembership(): bool
    {
        return $this->subscriptions()
            ->where('active', true)
            ->exists();
    }


    // Stripe customer ID (if needed later)
    public function stripeCustomerId(): ?string
    {
        // Agar multiple subscriptions mein se ek se le sakte ho
        return $this->subscriptions()->latest()->value('stripe_customer_id');
    }

    public function chatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class, 'user_id');
    }

    public function totalChatSessions(): int
    {
        // agar withCount se aaya hai to direct wahi use kare
        if (isset($this->chat_sessions_count)) {
            return (int) $this->chat_sessions_count;
        }

        // warna normal count query
        return $this->chatSessions()->count();
    }
}
