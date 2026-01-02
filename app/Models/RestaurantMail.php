<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantMail extends Model
{
    use HasFactory, StorageTrait, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'subject',
        'body',
        'sent_to',
        'receiver_ids',
        'image',
        'status',
    ];

    protected $casts = [
        'receiver_ids' => 'array',
    ];

    protected $dates = ['deleted_at'];


    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    public function getImageFullUrlAttribute(): array|null
    {
        $value = $this->image;
        if (count($this->storage) > 0) {
            $storage = $this->storage->where('key', 'image')->first();
        }
        return $this->storageLink('restaurant-mails', $value, $storage['value'] ?? 'public');
    }

    protected $appends = ['image_full_url'];
}
