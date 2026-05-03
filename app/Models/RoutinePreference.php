<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutinePreference extends Model
{
    protected $fillable = [
        'user_id', 'wake_up_time', 'sleep_time', 'focus_minutes',
        'peak_focus', 'blocked_times', 'learning_goals',
        'subject_priorities', 'include_weekend',
    ];

    protected function casts(): array
    {
        return [
            'blocked_times' => 'array',
            'learning_goals' => 'array',
            'subject_priorities' => 'array',
            'include_weekend' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
