<?php

namespace App\Enums\Payments;

enum PaymentType: string
{
    case BANK_TRANSFER = 'bank_transfer';
    case GOPAY = 'gopay';
    case QRIS = 'qris';
    case CREDIT_CARD = 'credit_card';
    case SHOPEEPAY = 'shopeepay';
    case OTHER = 'other';
}
