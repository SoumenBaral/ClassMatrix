<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions grouped by module
        $permissions = [
            // Academic
            'academic_years.view', 'academic_years.create', 'academic_years.edit', 'academic_years.delete',
            'classes.view', 'classes.create', 'classes.edit', 'classes.delete',
            'sections.view', 'sections.create', 'sections.edit', 'sections.delete',
            'subjects.view', 'subjects.create', 'subjects.edit', 'subjects.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',

            // Students
            'students.view', 'students.create', 'students.edit', 'students.delete',
            'students.enroll', 'students.promote',

            // Staff
            'staff.view', 'staff.create', 'staff.edit', 'staff.delete',

            // Attendance
            'attendance.view', 'attendance.mark', 'attendance.edit',

            // Exams
            'exams.view', 'exams.create', 'exams.edit', 'exams.delete',
            'marks.view', 'marks.entry', 'marks.edit',

            // Fees
            'fees.view', 'fees.create', 'fees.edit', 'fees.collect',
            'invoices.view', 'invoices.create',

            // Communication
            'notices.view', 'notices.create', 'notices.edit', 'notices.delete',

            // Reports
            'reports.view', 'reports.export',

            // Settings
            'settings.view', 'settings.edit',
            'roles.view', 'roles.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        // super-admin gets everything automatically via Gate::before in AuthServiceProvider

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        $principal = Role::firstOrCreate(['name' => 'principal']);
        $principal->syncPermissions([
            'academic_years.view', 'classes.view', 'sections.view', 'subjects.view', 'departments.view',
            'students.view', 'staff.view',
            'attendance.view',
            'exams.view', 'marks.view',
            'fees.view', 'invoices.view',
            'notices.view', 'notices.create', 'notices.edit',
            'reports.view', 'reports.export',
            'settings.view',
        ]);

        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->syncPermissions([
            'classes.view', 'sections.view', 'subjects.view',
            'students.view',
            'attendance.view', 'attendance.mark',
            'exams.view', 'marks.view', 'marks.entry',
            'notices.view',
        ]);

        $accountant = Role::firstOrCreate(['name' => 'accountant']);
        $accountant->syncPermissions([
            'students.view',
            'fees.view', 'fees.create', 'fees.edit', 'fees.collect',
            'invoices.view', 'invoices.create',
            'reports.view', 'reports.export',
        ]);

        $librarian = Role::firstOrCreate(['name' => 'librarian']);
        $librarian->syncPermissions([
            'students.view', 'staff.view',
        ]);

        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'parent']);
    }
}
