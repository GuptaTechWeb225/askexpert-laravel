<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StorageTrait;
use Illuminate\Support\Facades\Storage;

class ExpertCategory extends Model
{
    use HasFactory, SoftDeletes, StorageTrait;

    protected $table = 'expert_categories';

    protected $fillable = [
        'name',
        'icon',
        'primary_specialty',
        'description',
        'free_follow_up_duration',
        'is_refundable',
        'monthly_subscription_fee',
        'expert_fee',
        'joining_fee',
        'cms_heading',
        'cms_description',
        'card_description',
        'card_image',
        'cms_image',
        'is_active',
        'sub_categorys',
        'expert_premium',
        'expert_basic',
        'stripe_subscription_price_id ',
        'stripe_product_id',
    ];

    protected $casts = [
        'is_refundable' => 'boolean',
        'is_active' => 'boolean',
        'monthly_subscription_fee' => 'decimal:2',
        'expert_fee' => 'decimal:2',
        'expert_basic' => 'decimal:2',
        'expert_premium' => 'decimal:2',
        'joining_fee' => 'decimal:2',
        'sub_categorys' => 'array', 
    ];


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    protected $appends = ['icon_url', 'cms_image_url', 'card_image_url', 'active_experts_count'];

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon
            ? asset('storage/expert-categories/' . $this->icon)
            : null;
    }

    public function getCmsImageUrlAttribute(): ?string
    {
        return $this->cms_image
            ? asset('storage/expert-categories/' . $this->cms_image)
            : null;
    }
    public function getCardImageUrlAttribute(): ?string
    {
        return $this->card_image
            ? asset('storage/expert-categories/' . $this->card_image)
            : null;
    }

    // ExpertCategory.php

public function expertsCount(): int
{
    return $this->experts()->count();
}


    public function experts()
{
    return $this->hasMany(Expert::class, 'category_id')
        ->where('status', 'approved')
        ->where('is_active', true);
}

    public function scopeRefundable($query)
    {
        return $query->where('is_refundable', true);
    }

    public function getActiveExpertsCountAttribute()
{
    return $this->experts()->count();  // yeh relation already hai tumhare model mein
}

    public function scopeSearch($query, $keyword)
    {
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('primary_specialty', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }
        return $query;
    }


    public function scopeSpecialty($query, $specialty)
    {
        if (!empty($specialty)) {
            $query->where('primary_specialty', $specialty);
        }
        return $query;
    }

    public function scopeSubscriptionBetween($query, $min, $max)
    {
        if ($min !== null && $max !== null) {
            $query->whereBetween('monthly_subscription_fee', [$min, $max]);
        }
        return $query;
    }


    public function getSubCategorysAttribute($value)
    {
        if (is_array($value)) return $value;
        if (is_null($value)) return [];
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : explode(',', $value);
    }

    public function setSubCategorysAttribute($value)
    {
        $this->attributes['sub_categorys'] = json_encode(
            is_array($value) ? array_values($value) : explode(',', $value)
        );
    }
}
