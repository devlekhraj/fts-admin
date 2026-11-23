<?php

declare(strict_types=1);

namespace App\Domains\Admin\Middleware;

use App\Domains\Admin\Models\Admin;
use App\Domains\Rbac\Support\AccessMap;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks, on every admin API request, that the signed-in admin's role allows it (see AccessMap).
 * It does not sign anyone in: a request with no valid token passes through here and is refused with 401 by the
 * auth:admin_api middleware of the route, exactly as before.
 */
final class EnsureAdminPermission
{
    private const PREFIX = 'api/admin/';

    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        if (! str_starts_with($path, self::PREFIX)) {
            return $next($request);
        }

        $admin = $this->signedInAdmin();
        if (! $admin instanceof Admin) {
            return $next($request);
        }

        $rule = AccessMap::resolve($request->method(), substr($path, strlen(self::PREFIX)));
        if ($rule === null) {
            return $this->deny('This action is not available.');
        }
        if (isset($rule['public']) || $admin->hasAnyPermission($rule['any'])) {
            return $next($request);
        }

        return $this->deny('You do not have permission to do this. Ask an administrator to update your role.');
    }

    private function signedInAdmin(): ?Admin
    {
        try {
            $admin = auth('admin_api')->user();
        } catch (\Throwable) {
            // An expired or broken token: leave it to the route's auth middleware to answer 401.
            return null;
        }

        return $admin instanceof Admin ? $admin->loadMissing('role') : null;
    }

    private function deny(string $message): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], 403);
    }
}
