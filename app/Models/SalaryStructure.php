<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
    protected $fillable = [
        'designation', 'basic', 'hra', 'da', 'ta',
        'other_allowances', 'pf', 'tax', 'other_deductions',
    ];

    protected function casts(): array
    {
        return [
            'basic' => 'decimal:2',
            'hra' => 'decimal:2',
            'da' => 'decimal:2',
            'ta' => 'decimal:2',
            'pf' => 'decimal:2',
            'tax' => 'decimal:2',
            'other_allowances' => 'array',
            'other_deductions' => 'array',
        ];
    }

    public function grossSalary(): float
    {
        $allowances = collect($this->other_allowances ?? [])->sum('amount');

        return $this->basic + $this->hra + $this->da + $this->ta + $allowances;
    }

    public function totalDeductions(): float
    {
        $deductions = collect($this->other_deductions ?? [])->sum('amount');

        return $this->pf + $this->tax + $deductions;
    }

    public function netSalary(): float
    {
        return $this->grossSalary() - $this->totalDeductions();
    }
}
