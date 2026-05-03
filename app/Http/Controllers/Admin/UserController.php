<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query();

        if ($type = $request->input('type')) {
            $query->where('user_type', $type);
        }
        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $isSuperAdmin = $request->user()->hasRole('super-admin');

        return Inertia::render('admin/Users/Index', [
            'users' => $query->orderByDesc('created_at')
                ->paginate(20)
                ->through(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'user_type' => $u->user_type->value,
                    'status' => $u->status->value,
                    'created_at' => $u->created_at->format('Y-m-d'),
                    'roles' => $u->getRoleNames(),
                ]),
            'filters' => $request->only('type', 'search', 'status'),
            'isSuperAdmin' => $isSuperAdmin,
            'departments' => fn () => Department::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $isSuperAdmin = $request->user()->hasRole('super-admin');

        // Super Admin can create admin + teacher, Admin can only create teacher
        $allowedRoles = $isSuperAdmin ? 'admin,teacher' : 'teacher';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'string', 'min:8'],
            'role' => "required|in:{$allowedRoles}",
            // Teacher-specific fields
            'designation' => 'required_if:role,teacher|nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'qualification' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $userType = $validated['role'] === 'admin' ? UserType::Admin : UserType::Teacher;

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
                'user_type' => $userType,
            ]);

            $user->assignRole($validated['role']);

            // Create staff profile for teachers
            if ($validated['role'] === 'teacher') {
                Staff::create([
                    'user_id' => $user->id,
                    'employee_no' => 'EMP-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                    'designation' => $validated['designation'] ?? 'Teacher',
                    'department_id' => $validated['department_id'] ?? null,
                    'qualification' => $validated['qualification'] ?? null,
                    'joining_date' => now(),
                ]);
            }
        });

        $label = $validated['role'] === 'admin' ? 'Admin' : 'Teacher';

        return back()->with('flash', ['type' => 'success', 'message' => "{$label} account created."]);
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->with('flash', ['type' => 'error', 'message' => 'You cannot deactivate your own account.']);
        }

        $newStatus = $user->status === UserStatus::Active ? UserStatus::Suspended : UserStatus::Active;
        $user->update(['status' => $newStatus]);

        $action = $newStatus === UserStatus::Active ? 'activated' : 'suspended';

        return back()->with('flash', ['type' => 'success', 'message' => "User {$action}."]);
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('flash', ['type' => 'success', 'message' => "Password reset for {$user->name}."]);
    }
}
