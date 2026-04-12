<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Developer;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\ApiKeyModel;
use App\Foundation\Interfaces\Http\Resources\ApiKeyResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $data = $this->validated($request);

        $apiKey = ApiKeyModel::query()->create([
            ...$data,
            'test_public_key' => Str::upper(Str::random(32)),
            'test_secret_key' => Str::upper(Str::random(48)),
            'live_public_key' => Str::upper(Str::random(32)),
            'live_secret_key' => Str::upper(Str::random(48)),
        ]);

        return response()->json([
            'message' => 'API key created successfully.',
            'data' => new ApiKeyResource($apiKey),
        ], 201);
    }

    public function update(string $id, Request $request): JsonResponse
    {
        $apiKey = ApiKeyModel::query()->findOrFail($id);
        $data = $this->validated($request);

        $apiKey->fill($data);
        $apiKey->save();

        return response()->json([
            'message' => 'API key updated successfully.',
            'data' => new ApiKeyResource($apiKey),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $apiKey = ApiKeyModel::query()->findOrFail($id);
        $apiKey->delete();

        return response()->json([
            'message' => 'API key deleted successfully.',
            'success' => true,
            'id' => $id,
        ], 200);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'host' => ['nullable', 'string', 'max:255'],
            'mode' => ['required', 'in:test,live'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
