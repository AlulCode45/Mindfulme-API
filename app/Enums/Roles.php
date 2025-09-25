<?php
namespace App\Enums;

enum Roles: string
{
    case SUPERADMIN = 'superadmin';
    case USER = 'user';
    case PSYCHOLOGIST = 'psychologist';

    public function label(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'Super Admin',
            self::USER => 'User',
            self::PSYCHOLOGIST => 'Psikolog',
        };
    }
}