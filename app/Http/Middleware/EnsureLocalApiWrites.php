<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLocalApiWrites
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $request->isMethodSafe() || app()->environment(['local', 'testing']),
            403,
            'API writes are only available in local and testing environments.'
        );

        return $next($request);
    }
}
