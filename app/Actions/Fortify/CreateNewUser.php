<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function create(array $input): User
    {
        $rules = [
            ...$this->profileRules(),
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => $this->passwordRules(),
            'role' => ['required', 'in:student,parent'],
        ];

        // Parent must provide a valid admission number
        if (($input['role'] ?? '') === 'parent') {
            $rules['child_admission_no'] = ['required', 'string', 'max:30', 'exists:students,admission_no'];
            $rules['relation'] = ['required', 'in:father,mother,guardian,other'];
        }

        Validator::make($input, $rules, [
            'child_admission_no.required' => 'Your child\'s admission number is required to register as a parent.',
            'child_admission_no.exists' => 'No student found with this admission number. Please check with your school.',
        ])->validate();

        return DB::transaction(function () use ($input) {
            $userType = match ($input['role']) {
                'student' => UserType::Student,
                'parent' => UserType::Parent,
                default => UserType::Student,
            };

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'] ?? null,
                'password' => $input['password'],
                'user_type' => $userType,
            ]);

            $user->assignRole($input['role']);

            if ($input['role'] === 'student') {
                Student::create([
                    'user_id' => $user->id,
                    'admission_no' => 'ADM-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                    'admission_date' => now(),
                ]);
            }

            // Auto-link parent to child via admission number
            if ($input['role'] === 'parent' && ! empty($input['child_admission_no'])) {
                $student = Student::where('admission_no', $input['child_admission_no'])->first();

                if ($student) {
                    $user->children()->attach($student->id, [
                        'relation' => $input['relation'] ?? 'parent',
                        'is_primary' => true,
                    ]);
                }
            }

            return $user;
        });
    }
}
