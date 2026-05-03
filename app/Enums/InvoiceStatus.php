<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function color(): string
    {
        return match ($this) {
            self::Unpaid => 'secondary',
            self::Partial => 'outline',
            self::Paid => 'default',
            self::Overdue => 'destructive',
            self::Cancelled => 'secondary',
        };
    }
}
