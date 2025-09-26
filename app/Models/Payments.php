<?php

namespace App\Models;

use App\Enums\PaymentType;
use App\Enums\TransactionStatus;
use App\Enums\FraudStatus;
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
        'payment_type' => PaymentType::class,
        'transaction_status' => TransactionStatus::class,
        'fraud_status' => FraudStatus::class,
    ];
}
