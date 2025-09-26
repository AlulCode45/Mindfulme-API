<?php

namespace App\Enums\Payments;

enum TransactionStatus: string
{
    case PENDING = 'pending';
    case SETTLEMENT = 'settlement';
    case CAPTURE = 'capture';
    case DENY = 'deny';
    case EXPIRE = 'expire';
    case CANCEL = 'cancel';
    case REFUND = 'refund';

    public function isFinal(): bool
    {
        return in_array($this, [self::SETTLEMENT, self::EXPIRE, self::CANCEL, self::REFUND]);
    }
}
