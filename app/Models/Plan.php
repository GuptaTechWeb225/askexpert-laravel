<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StorageTrait;
use Illuminate\Support\Facades\DB;

class Plan extends Model
{
    use SoftDeletes, StorageTrait;

    protected $fillable = [
        'plan_name',
        'type',
        'code',
        'plan_type',
        'description',
        'boost_type',
        'price',
        'boost_duration',
        'plan_duration',
        'mail_count',
        'push_notification_count',
        'plan_expiry_reminder',
        'mail_campaing_price',
        'plan_addons',
        'image',
        'status',
    ];

    protected $casts = [
        'plan_addons' => 'array',
        'status' => 'boolean',
    ];

    public function getImageFullUrlAttribute(): array
    {
        $value = $this->image;
        if (count($this->storage) > 0) {
            $storage = $this->storage->where('key', 'image')->first();
        }
        return $this->storageLink('plan', $value, $storage['value'] ?? 'public');
    }

    public function scopeBoost($query)
    {
        return $query->where('type', 'boost');
    }

    public function scopeMembership($query)
    {
        return $query->where('type', 'membership');
    }

    protected $appends = ['image_full_url'];
}
