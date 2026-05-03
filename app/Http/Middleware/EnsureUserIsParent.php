<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsParent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isParent()) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
