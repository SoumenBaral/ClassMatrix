<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['hostel_id', 'room_no', 'capacity', 'type', 'rent'];

    protected function casts(): array
    {
        return ['rent' => 'decimal:2'];
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(HostelAllocation::class);
    }

    public function activeAllocations(): HasMany
    {
        return $this->hasMany(HostelAllocation::class)->whereNull('vacated_at');
    }

    public function occupancy(): int
    {
        return $this->activeAllocations()->count();
    }
}
