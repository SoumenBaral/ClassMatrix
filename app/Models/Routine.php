<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Routine extends Model
{
    protected $fillable = [
        'user_id', 'academic_year_id', 'week_start_date', 'type',
        'status', 'weekly_goals', 'study_tips', 'ai_summary',
        'blocks', 'model_used', 'total_tokens', 'cost_usd',
    ];

    protected function casts(): array
    {
        return [
            'week_start_date' => 'date',
            'weekly_goals' => 'array',
            'study_tips' => 'array',
            'blocks' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
