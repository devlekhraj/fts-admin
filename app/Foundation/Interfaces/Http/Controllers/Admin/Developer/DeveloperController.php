<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Developer;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\ApiKeyModel;
use App\Foundation\Interfaces\Http\Resources\ApiKeyResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeveloperController extends Controller
{
    public function index(): JsonResponse
    {
        $items = ApiKeyModel::query()
            ->latest('id')
            ->get();

        return response()->json([
            'data' => ApiKeyResource::collection($items),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'API key created successfully.',
            'data' => $request->all(),
        ], 201);
    }

    public function destroy(string $id): JsonResponse
    {
        return response()->json([
            'message' => 'API key deleted successfully.',
            'id' => $id,
        ]);
    }
}
