<?php

declare(strict_types=1);

namespace App\Domains\Developer\Controllers;

use App\Domains\Developer\DTOs\ApiKeyCreateData;
use App\Domains\Developer\DTOs\ApiKeyUpdateData;
use App\Domains\Developer\Resources\ApiKeyResource;
use App\Domains\Developer\Services\ApiKeyService;
use Illuminate\Routing\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DeveloperController extends Controller
{
    public function __construct(private readonly ApiKeyService $apiKeyService) {}

    public function index(): JsonResponse
    {
        $items = $this->apiKeyService->list();

        return response()->json([
            'data' => ApiKeyResource::collection($items),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $apiKey = $this->apiKeyService->create(ApiKeyCreateData::fromArray($data));

        return response()->json([
            'message' => 'API key created successfully.',
            'data' => new ApiKeyResource($apiKey),
        ], 201);
    }

    public function update(string $id, Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $apiKey = $this->apiKeyService->update($id, ApiKeyUpdateData::fromArray($data));

        return response()->json([
            'message' => 'API key updated successfully.',
            'data' => new ApiKeyResource($apiKey),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->apiKeyService->delete($id);

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
