<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Foundation\Interfaces\Http\Resources\RoleResource;

class RbacController extends Controller
{
    public function roles(): JsonResponse
    {
        $roles = RoleModel::query()->get();

        return response()->json([
            'data' => RoleResource::collection($roles),
        ]);
    }

    public function permissions(): JsonResponse
    {
        // TODO: List permissions.
        return response()->json([]);
    }
}
