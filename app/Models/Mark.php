<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends Model
{
    protected $fillable = [
        'exam_schedule_id', 'student_id',
        'marks_obtained', 'grade', 'remarks', 'entered_by',
    ];

    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function percentage(): ?float
    {
        if ($this->marks_obtained === null) {
            return null;
        }

        $fullMarks = $this->examSchedule->full_marks;

        return $fullMarks > 0 ? round(($this->marks_obtained / $fullMarks) * 100, 2) : 0;
    }

    public function isPassing(): bool
    {
        return $this->marks_obtained !== null
            && $this->marks_obtained >= $this->examSchedule->pass_marks;
    }
}
