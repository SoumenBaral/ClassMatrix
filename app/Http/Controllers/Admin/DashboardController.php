<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassLevel;
use App\Models\Department;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'totalStudents' => Student::where('status', 'active')->count(),
                'totalTeachers' => User::where('user_type', 'teacher')->count(),
                'totalClasses' => ClassLevel::count(),
                'totalSections' => Section::where('academic_year_id', AcademicYear::current()?->id)->count(),
                'totalSubjects' => Subject::count(),
                'totalDepartments' => Department::count(),
            ],
            'currentAcademicYear' => AcademicYear::current(),
        ]);
    }
}
