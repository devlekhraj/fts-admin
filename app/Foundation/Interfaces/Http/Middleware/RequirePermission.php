<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        // TODO: Check permission.
        return $next($request);
    }
}
