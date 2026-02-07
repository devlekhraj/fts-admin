<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Foundation\Interfaces\Http\Requests\Auth\AdminLoginRequest;
use Illuminate\Http\JsonResponse;

class AdminAuthController extends Controller
{
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $token = auth('admin_api')->attempt($credentials);

        if (!$token) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me(): JsonResponse
    {
        return response()->json(auth('admin_api')->user());
    }

    public function logout(): JsonResponse
    {
        auth('admin_api')->logout();

        return response()->json(['message' => 'Logged out']);
    }

    public function refresh(): JsonResponse
    {
        $token = auth('admin_api')->refresh();

        return $this->respondWithToken($token);
    }

    private function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('admin_api')->factory()->getTTL() * 60,
        ]);
    }
}
