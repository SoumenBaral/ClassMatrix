<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Staff::with(['user:id,name,email,phone,status', 'department:id,name']);

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('employee_no', 'like', "%{$search}%")
                ->orWhere('designation', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")));
        }
        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        return Inertia::render('admin/Staff/Index', [
            'staff' => $query->orderByDesc('created_at')->paginate(20)->through(fn ($s) => [
                'id' => $s->id,
                'name' => $s->user->name,
                'email' => $s->user->email,
                'phone' => $s->user->phone,
                'employee_no' => $s->employee_no,
                'designation' => $s->designation,
                'department' => $s->department?->name,
                'department_id' => $s->department_id,
                'qualification' => $s->qualification,
                'joining_date' => $s->joining_date?->format('Y-m-d'),
                'experience_years' => $s->experience_years,
                'gender' => $s->gender,
                'status' => $s->status,
                'user_status' => $s->user->status->value,
                'user_id' => $s->user_id,
            ]),
            'departments' => Department::select('id', 'name')->orderBy('name')->get(),
            'filters' => $request->only('search', 'department_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'designation' => 'required|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'qualification' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'experience_years' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
                'user_type' => UserType::Teacher,
            ]);

            $user->assignRole('teacher');

            Staff::create([
                'user_id' => $user->id,
                'employee_no' => 'EMP-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                'designation' => $validated['designation'],
                'department_id' => $validated['department_id'] ?? null,
                'qualification' => $validated['qualification'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'experience_years' => $validated['experience_years'] ?? 0,
                'joining_date' => now(),
            ]);
        });

        return back()->with('flash', ['type' => 'success', 'message' => 'Teacher/Staff created.']);
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$staff->user_id}",
            'phone' => 'nullable|string|max:20',
            'designation' => 'required|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'qualification' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'experience_years' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($staff, $validated) {
            $staff->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);

            $staff->update([
                'designation' => $validated['designation'],
                'department_id' => $validated['department_id'] ?? null,
                'qualification' => $validated['qualification'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'experience_years' => $validated['experience_years'] ?? 0,
                'status' => $validated['status'],
            ]);
        });

        return back()->with('flash', ['type' => 'success', 'message' => 'Staff updated.']);
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $staff->user->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Staff deleted.']);
    }
}
