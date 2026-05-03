<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        $currentYear = AcademicYear::current();

        $query = Student::with(['user:id,name,email,phone,status', 'section.classLevel']);

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('admission_no', 'like', "%{$search}%")
                ->orWhere('roll_no', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")));
        }
        if ($sectionId = $request->input('section_id')) {
            $query->where('current_section_id', $sectionId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $sections = Section::with('classLevel')
            ->where('academic_year_id', $currentYear?->id)
            ->get()
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->classLevel->name . ' - ' . $s->name]);

        return Inertia::render('admin/Students/Index', [
            'students' => $query->orderByDesc('created_at')->paginate(20)->through(fn ($s) => [
                'id' => $s->id,
                'name' => $s->user->name,
                'email' => $s->user->email,
                'phone' => $s->user->phone,
                'admission_no' => $s->admission_no,
                'roll_no' => $s->roll_no,
                'class_section' => $s->section ? $s->section->classLevel->name . ' - ' . $s->section->name : 'Not assigned',
                'section_id' => $s->current_section_id,
                'gender' => $s->gender,
                'dob' => $s->dob?->format('Y-m-d'),
                'status' => $s->status->value,
                'user_status' => $s->user->status->value,
                'user_id' => $s->user_id,
            ]),
            'sections' => $sections,
            'filters' => $request->only('search', 'section_id', 'status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'roll_no' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'section_id' => 'nullable|exists:sections,id',
            'blood_group' => 'nullable|string|max:5',
            'address' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
                'user_type' => UserType::Student,
            ]);

            $user->assignRole('student');

            Student::create([
                'user_id' => $user->id,
                'admission_no' => 'ADM-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                'roll_no' => $validated['roll_no'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'current_section_id' => $validated['section_id'] ?? null,
                'blood_group' => $validated['blood_group'] ?? null,
                'address_current' => $validated['address'] ?? null,
                'admission_date' => now(),
            ]);
        });

        return back()->with('flash', ['type' => 'success', 'message' => 'Student created.']);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$student->user_id}",
            'phone' => 'nullable|string|max:20',
            'roll_no' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'section_id' => 'nullable|exists:sections,id',
            'blood_group' => 'nullable|string|max:5',
            'status' => 'required|in:active,alumni,withdrawn',
        ]);

        DB::transaction(function () use ($student, $validated) {
            $student->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);

            $student->update([
                'roll_no' => $validated['roll_no'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'current_section_id' => $validated['section_id'] ?? null,
                'blood_group' => $validated['blood_group'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return back()->with('flash', ['type' => 'success', 'message' => 'Student updated.']);
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->user->delete(); // soft deletes user, cascades to student

        return back()->with('flash', ['type' => 'success', 'message' => 'Student deleted.']);
    }
}
