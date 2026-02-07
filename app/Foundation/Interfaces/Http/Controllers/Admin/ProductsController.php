<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class ProductsController extends Controller
{
    public function index(): JsonResponse
    {
        // TODO: List products.
        return response()->json([]);
    }

    public function show(string $id): JsonResponse
    {
        // TODO: Show single product.
        return response()->json(['id' => $id]);
    }

    public function store(): JsonResponse
    {
        // TODO: Create product.
        return response()->json([], 201);
    }

    public function update(string $id): JsonResponse
    {
        // TODO: Update product.
        return response()->json(['id' => $id]);
    }

    public function destroy(string $id): JsonResponse
    {
        // TODO: Delete product.
        return response()->json(null, 204);
    }
}
