<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in-progress';
    case COMPLETED = 'completed';
    case URGENT = 'urgent';

    function label(): string
    {
        return match ($this) {
            self::NEW => 'Baru',
            self::IN_PROGRESS => 'Diproses',
            self::COMPLETED => 'Selesai',
            self::URGENT => 'Urgent',
        };
    }
}
