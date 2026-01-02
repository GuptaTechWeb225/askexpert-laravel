<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AdminNotification extends Model
{
    use HasFactory;

    protected $table = 'admin_notifications';

    protected $fillable = [
        'notifiable_id',
        'notifiable_type',
        'notification_for',
        'admin_id',
        'expert_id',
        'customer_id',
        'title',
        'message',
        'status',     
        'is_active',
    ];

    protected $casts = [
        'status' => 'integer',
        'is_active' => 'boolean',
    ];

    /* -------------------------------------------------
     *  Relationships
     * ------------------------------------------------*/
    public function notifiable()
    {
        return $this->morphTo();
    }


    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id'); 
    }
    public function expert()
    {
        return $this->belongsTo(Expert::class, 'expert_id'); 
    }

    /* -------------------------------------------------
     *  Scopes
     * ------------------------------------------------*/
    public function scopeForExpert(Builder $query, int $expertId)
    {
        return $query->where('notification_for', 1)->where('expert_id', $expertId);
    }

    public function scopeForCustomer(Builder $query, int $customerId)
    {
        return $query->where('notification_for', 2)->where('customer_id', $customerId);
    }
    public function scopeForAdmin(Builder $query, int $adminId): Builder
    {
        return $query->where('notification_for', 3)->where('admin_id', $adminId);
    }

    public function scopeUnread(Builder $query)
    {
        return $query->where('status', 0);
    }
}