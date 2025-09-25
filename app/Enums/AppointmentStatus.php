<?php

namespace App\Enums;

enum AppointmentStatus
{
    case SCHEDULED = 'scheduled';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::COMPLETED => 'Completed',
            self::CANCELED => 'Canceled',
        };
    }
}
