<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeScheme extends Model
{
    protected $fillable = ['name', 'is_default'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function ranges(): HasMany
    {
        return $this->hasMany(GradeRange::class)->orderByDesc('min_pct');
    }

    public static function default(): ?static
    {
        return static::where('is_default', true)->first();
    }

    public function gradeFor(float $percentage): ?string
    {
        foreach ($this->ranges as $range) {
            if ($percentage >= $range->min_pct && $percentage <= $range->max_pct) {
                return $range->grade;
            }
        }

        return null;
    }

    public function gpaFor(float $percentage): ?float
    {
        foreach ($this->ranges as $range) {
            if ($percentage >= $range->min_pct && $percentage <= $range->max_pct) {
                return $range->gpa;
            }
        }

        return null;
    }
}
