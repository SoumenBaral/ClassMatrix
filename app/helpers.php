<?php

use App\Models\AcademicYear;

if (! function_exists('current_academic_year')) {
    function current_academic_year(): ?AcademicYear
    {
        return cache()->rememberForever('current_academic_year', function () {
            return AcademicYear::current();
        });
    }
}
