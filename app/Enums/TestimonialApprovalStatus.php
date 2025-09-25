<?php

namespace App\Enums;

enum TestimonialApprovalStatus: string
{
    case PENDING = "pending";
    case APPROVED = "approved";
    case REJECTED = "rejected";

    function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }
}
