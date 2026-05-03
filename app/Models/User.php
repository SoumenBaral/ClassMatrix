<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Enums\UserType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_type',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'user_type' => UserType::class,
            'status' => UserStatus::class,
        ];
    }

    // --- Relationships ---

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    // --- Helpers ---

    public function isAdmin(): bool
    {
        return $this->user_type === UserType::Admin;
    }

    public function isTeacher(): bool
    {
        return $this->user_type === UserType::Teacher;
    }

    public function isStudent(): bool
    {
        return $this->user_type === UserType::Student;
    }

    public function isParent(): bool
    {
        return $this->user_type === UserType::Parent;
    }

    public function dashboardRoute(): string
    {
        return match ($this->user_type) {
            UserType::Admin => 'admin.dashboard',
            UserType::Teacher => 'teacher.dashboard',
            UserType::Student => 'student.dashboard',
            UserType::Parent => 'parent.dashboard',
            default => 'dashboard',
        };
    }
}
