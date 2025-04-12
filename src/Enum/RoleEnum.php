<?php

namespace App\Enum;

enum RoleEnum: string
{
    case Admin = 'ROLE_ADMIN';
    case Fan = 'ROLE_USER';
}