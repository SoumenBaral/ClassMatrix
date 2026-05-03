<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();
        $route = match (true) {
            $user->isAdmin() => '/admin/dashboard',
            $user->isTeacher() => '/teacher/dashboard',
            $user->isStudent() => '/student/dashboard',
            $user->isParent() => '/parent/dashboard',
            default => '/dashboard',
        };

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended($route);
    }
}
