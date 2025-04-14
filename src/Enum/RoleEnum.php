<?php

namespace App\Enum;

enum RoleEnum: string
{
    case ADMIN = 'ROLE_ADMIN';
    case FAN = 'ROLE_USER';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrateur',
            self::FAN => 'Fan',
        };
    }
}
