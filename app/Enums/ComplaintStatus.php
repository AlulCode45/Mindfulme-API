<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case NEW = 'new';
    case IN_REVIEW = 'in_review';
    case RESOLVED = 'resolved';

    function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::IN_REVIEW => 'In Review',
            self::RESOLVED => 'Resolved',
        };
    }
}
