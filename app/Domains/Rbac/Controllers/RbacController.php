<?php

declare(strict_types=1);

namespace App\Domains\Rbac\Controllers;

use App\Domains\Rbac\Resources\RoleResource;
use App\Domains\Rbac\Services\RbacService;
use Illuminate\Routing\Controller;

use Illuminate\Http\JsonResponse;

final class RbacController extends Controller
{
    public function __construct(
        private readonly RbacService $rbacService,
    ) {}

    public function indexRoles(): JsonResponse
    {
        $roles = $this->rbacService->roles();

        return response()->json([
            'data' => RoleResource::collection($roles),
        ]);
    }

    public function indexPermissions(): JsonResponse
    {
        // TODO: List permissions.
        return response()->json([]);
    }
}
