<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\AcademicYear;
use App\Models\ClassLevel;
use App\Models\Department;
use App\Models\ExamType;
use App\Models\GradeScheme;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // Create Super Admin
        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@classmatrix.com',
            'user_type' => UserType::Admin,
        ]);
        $admin->assignRole('super-admin');

        // Create a teacher
        $teacher = User::factory()->create([
            'name' => 'John Teacher',
            'email' => 'teacher@classmatrix.com',
            'user_type' => UserType::Teacher,
        ]);
        $teacher->assignRole('teacher');

        // Academic Year
        $year = AcademicYear::create([
            'name' => '2026-2027',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'is_current' => true,
        ]);

        $year->terms()->createMany([
            ['name' => 'Term 1', 'start_date' => '2026-04-01', 'end_date' => '2026-09-30'],
            ['name' => 'Term 2', 'start_date' => '2026-10-01', 'end_date' => '2027-03-31'],
        ]);

        // Departments
        $departments = collect([
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'Languages', 'code' => 'LANG'],
            ['name' => 'Social Studies', 'code' => 'SOC'],
            ['name' => 'Arts', 'code' => 'ART'],
            ['name' => 'Physical Education', 'code' => 'PE'],
        ])->map(fn ($d) => Department::create($d));

        // Class Levels (Grade 1-12)
        foreach (range(1, 12) as $i) {
            ClassLevel::create([
                'name' => "Grade {$i}",
                'numeric_order' => $i,
            ]);
        }

        // Subjects
        $subjects = [
            ['name' => 'English', 'code' => 'ENG', 'department_id' => $departments[2]->id],
            ['name' => 'Mathematics', 'code' => 'MAT', 'department_id' => $departments[1]->id],
            ['name' => 'Physics', 'code' => 'PHY', 'department_id' => $departments[0]->id],
            ['name' => 'Chemistry', 'code' => 'CHE', 'department_id' => $departments[0]->id],
            ['name' => 'Biology', 'code' => 'BIO', 'department_id' => $departments[0]->id],
            ['name' => 'History', 'code' => 'HIS', 'department_id' => $departments[3]->id],
            ['name' => 'Geography', 'code' => 'GEO', 'department_id' => $departments[3]->id],
            ['name' => 'Computer Science', 'code' => 'CS', 'department_id' => $departments[0]->id],
            ['name' => 'Hindi', 'code' => 'HIN', 'department_id' => $departments[2]->id],
            ['name' => 'Art', 'code' => 'ART', 'department_id' => $departments[4]->id],
            ['name' => 'Physical Education', 'code' => 'PHE', 'department_id' => $departments[5]->id],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }

        // Exam Types
        ExamType::create(['name' => 'Unit Test', 'weight' => 20]);
        ExamType::create(['name' => 'Mid-Term', 'weight' => 30]);
        ExamType::create(['name' => 'Final', 'weight' => 50]);

        // Default Grade Scheme
        $scheme = GradeScheme::create(['name' => 'Standard (A-F)', 'is_default' => true]);
        $scheme->ranges()->createMany([
            ['grade' => 'A+', 'min_pct' => 90, 'max_pct' => 100, 'gpa' => 4.0],
            ['grade' => 'A',  'min_pct' => 80, 'max_pct' => 89.99, 'gpa' => 3.7],
            ['grade' => 'B+', 'min_pct' => 70, 'max_pct' => 79.99, 'gpa' => 3.3],
            ['grade' => 'B',  'min_pct' => 60, 'max_pct' => 69.99, 'gpa' => 3.0],
            ['grade' => 'C+', 'min_pct' => 50, 'max_pct' => 59.99, 'gpa' => 2.5],
            ['grade' => 'C',  'min_pct' => 40, 'max_pct' => 49.99, 'gpa' => 2.0],
            ['grade' => 'D',  'min_pct' => 33, 'max_pct' => 39.99, 'gpa' => 1.5],
            ['grade' => 'F',  'min_pct' => 0,  'max_pct' => 32.99, 'gpa' => 0.0],
        ]);
    }
}
