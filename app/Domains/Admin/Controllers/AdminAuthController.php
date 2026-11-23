<?php

declare(strict_types=1);

namespace App\Domains\Admin\Controllers;

use App\Domains\Admin\Requests\AdminLoginRequest;
use App\Domains\Admin\Resources\AdminResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Tymon\JWTAuth\JWTGuard;

final class AdminAuthController extends Controller
{
    private function getGuard(): JWTGuard
    {
        return auth('admin_api');
    }

    public function login(AdminLoginRequest $request): JsonResponse
    {
        $guard = $this->getGuard();
        $credentials = $request->only('email', 'password');

        $token = $guard->attempt($credentials);

        if (! $token) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me(): JsonResponse
    {
        $admin = $this->getGuard()->user();

        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'data' => null,
            ], 401);
        }

        $admin->loadMissing('role');

        return response()->json([
            'success' => true,
            // permissions and is_super drive which menus and buttons the admin screens show; the server checks every request again
            'data' => array_merge((new AdminResource($admin))->resolve(), [
                'is_super' => $admin->isSuper(),
                'permissions' => $admin->permissionList(),
            ]),
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->getGuard()->logout();

        return response()->json(['message' => 'Logged out']);
    }

    public function refresh(): JsonResponse
    {
        $token = $this->getGuard()->refresh();

        return $this->respondWithToken($token);
    }

    private function respondWithToken(string $token): JsonResponse
    {
        $guard = $this->getGuard();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
        ]);
    }
}

