<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Controllers;

use App\Domains\Campaign\DTOs\CampaignCreateData;
use App\Domains\Campaign\DTOs\CampaignUpdateData;
use App\Domains\Campaign\Resources\CampaignResource;
use App\Domains\Campaign\Services\CampaignService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CampaignController extends Controller
{
    public function __construct(private readonly CampaignService $campaignService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $items = $this->campaignService->list($request->filled('name') ? (string) $request->get('name') : null);

        return response()->json([
            'success' => true,
            'data' => CampaignResource::collection($items),
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateCreate($request);
        $campaign = $this->campaignService->create(CampaignCreateData::fromArray($validated));

        return response()->json([
            'message' => 'Campaign created successfully.',
            'data' => $campaign,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $this->validateUpdate($request, $id);
        $campaign = $this->campaignService->update($id, CampaignUpdateData::fromArray($validated));

        return response()->json([
            'message' => 'Campaign updated successfully.',
            'data' => $campaign,
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $campaign = $this->campaignService->detail($id);

        return response()->json([
            'success' => true,
            'data' => new CampaignResource($campaign),
        ], 200);
    }

    public function togglePublished(string $id, Request $request): JsonResponse
    {
        $campaign = $this->campaignService->togglePublished($id, $request->boolean('is_published'));
        $status = $campaign->is_published ? 'published' : 'unpublished';

        return response()->json([
            'success' => true,
            'message' => "{$campaign->title} is now {$status}",
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $campaign = $this->campaignService->detail($id);
        $title = (string) $campaign->title;

        $this->campaignService->delete($id);

        return response()->json([
            'success' => true,
            'message' => $title.' is deleted',
        ], 200);
    }

    private function validateCreate(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:discount_campaigns,slug',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_published' => 'nullable|boolean',
        ]);
    }

    private function validateUpdate(Request $request, string $id): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:discount_campaigns,slug,'.$id,
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_published' => 'nullable|boolean',
        ]);
    }
}

