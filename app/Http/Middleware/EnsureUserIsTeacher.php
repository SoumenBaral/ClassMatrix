<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsTeacher
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isTeacher()) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
