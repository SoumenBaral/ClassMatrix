<?php

namespace App\Enums;

enum UserType: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Student = 'student';
    case Parent = 'parent';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Teacher => 'Teacher',
            self::Student => 'Student',
            self::Parent => 'Parent',
            self::Staff => 'Staff',
        };
    }
}
