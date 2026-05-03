<?php

namespace App\Enums;

enum EventType: string
{
    case Holiday = 'holiday';
    case Exam = 'exam';
    case Meeting = 'meeting';
    case Cultural = 'cultural';
    case Sports = 'sports';
    case Other = 'other';

    public function color(): string
    {
        return match ($this) {
            self::Holiday => '#ef4444',
            self::Exam => '#f59e0b',
            self::Meeting => '#3b82f6',
            self::Cultural => '#8b5cf6',
            self::Sports => '#10b981',
            self::Other => '#6b7280',
        };
    }
}
