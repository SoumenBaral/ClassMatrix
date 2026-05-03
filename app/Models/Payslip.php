<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    protected $fillable = [
        'staff_id', 'month', 'year', 'basic',
        'allowances', 'deductions', 'gross', 'net',
        'status', 'generated_at', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'allowances' => 'array',
            'deductions' => 'array',
            'basic' => 'decimal:2',
            'gross' => 'decimal:2',
            'net' => 'decimal:2',
            'generated_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function monthName(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }
}
