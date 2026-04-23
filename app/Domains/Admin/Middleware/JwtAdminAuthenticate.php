<?php

declare(strict_types=1);

namespace App\Domains\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class JwtAdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('admin_api')->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return $next($request);
    }
}
