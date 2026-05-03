<?php

namespace App\Models;

use App\Enums\LeaveType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = ['staff_id', 'year', 'type', 'total', 'used'];

    protected function casts(): array
    {
        return ['type' => LeaveType::class];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function remaining(): float
    {
        return $this->total - $this->used;
    }
}
