<?php

namespace App\Enums\Payments;

enum FraudStatus: string
{
    case ACCEPT = 'accept';
    case CHALLENGE = 'challenge';
    case DENY = 'deny';
}
