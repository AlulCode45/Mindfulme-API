<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory, HasUuids;
    public $incrementing = false;
    protected $primaryKey = 'payment_id';
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'order_id',
        'midtrans_transaction_id',
        'payment_type',
        'transaction_status',
        'fraud_status',
        'gross_amount',
        'payment_details',
        'transaction_time',
        'expiry_time',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'transaction_time' => 'datetime',
        'expiry_time' => 'datetime',
        'payment_type' => \App\Enum\Payments\PaymentType::class,
        'transaction_status' => \App\Enums\Payments\TransactionStatus::class,
        'fraud_status' => \App\Enums\Payments\FraudStatus::class,
    ];
}
