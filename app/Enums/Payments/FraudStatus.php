<?php

namespace App\Enums;

enum FraudStatus: string
{
    case ACCEPT = 'accept';
    case CHALLENGE = 'challenge';
    case DENY = 'deny';
}
