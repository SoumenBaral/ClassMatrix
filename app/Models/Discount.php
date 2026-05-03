<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = ['name', 'type', 'value'];

    protected function casts(): array
    {
        return ['value' => 'decimal:2'];
    }

    public function apply(float $amount): float
    {
        return $this->type === 'percent'
            ? round($amount * ($this->value / 100), 2)
            : min($this->value, $amount);
    }
}
