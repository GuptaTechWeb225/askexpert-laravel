<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    use HasUuid;
    use HasFactory;

    protected $table = 'payment_requests';

    protected $primaryKey = 'id';
    public $incrementing = false;        // Important: UUID ke liye
    protected $keyType = 'string';       // Important: id string hai

    protected $fillable = [
        'id',
        'payer_id',
        'receiver_id',
        'payment_amount',
        'gateway_callback_url',
        'success_hook',
        'failure_hook',
        'transaction_id',
        'currency_code',
        'payment_method',
        'additional_data',
        'is_paid',
        'payer_information',
        'external_redirect_link',
        'receiver_information',
        'attribute_id',
        'attribute',
        'payment_platform',
    ];

    // Agar additional_data json hai to cast kar do
    protected $casts = [
        'additional_data' => 'array',
        'payer_information' => 'array',
        'receiver_information' => 'array',
        'is_paid' => 'boolean',
        'payment_amount' => 'decimal:2',
    ];

    // Timestamps agar manually manage nahi kar rahe
    public $timestamps = true;
}
