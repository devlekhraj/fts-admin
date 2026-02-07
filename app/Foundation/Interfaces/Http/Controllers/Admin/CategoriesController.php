<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class CategoriesController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([]);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(['id' => $id]);
    }

    public function store(): JsonResponse
    {
        return response()->json([], 201);
    }

    public function update(string $id): JsonResponse
    {
        return response()->json(['id' => $id]);
    }

    public function destroy(string $id): JsonResponse
    {
        return response()->json(null, 204);
    }
}
