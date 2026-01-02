<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PurchasedPlan extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'restaurant_id',
        'plan_id',
        'plan_code',
        'plan_name',
        'plan_amount',
        'transaction_id',
        'boost_days_used',
        'boost_days_total',
        'push_notification_used',
        'push_notification_total',
        'mail_used',
        'mail_total',
        'payment_method',
        'payment_status',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
    public function remainingBoostDays()
    {
        return max(0, $this->boost_days_total - $this->boost_days_used);
    }

    public function remainingMails()
{
    return max(0, $this->mail_total - $this->mail_used);
}

}
