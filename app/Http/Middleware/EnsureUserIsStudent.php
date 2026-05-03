<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isStudent()) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
