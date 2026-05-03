<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeRange extends Model
{
    protected $fillable = ['grade_scheme_id', 'grade', 'min_pct', 'max_pct', 'gpa'];

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(GradeScheme::class, 'grade_scheme_id');
    }
}
