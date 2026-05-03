<?php

namespace App\Enums;

enum LeaveType: string
{
    case Casual = 'casual';
    case Sick = 'sick';
    case Earned = 'earned';
    case Unpaid = 'unpaid';
}
