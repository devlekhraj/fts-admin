<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Controllers;

use App\Domains\Campaign\DTOs\CampaignImageStoreData;
use App\Domains\Campaign\Services\CampaignImageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CampaignImageController extends Controller
{
    public function __construct(private readonly CampaignImageService $campaignImageService)
    {
    }

    public function store(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|image|max:10240',
            'link' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'alt_text' => 'nullable|string',
        ]);

        try {
            $this->campaignImageService->store($id, CampaignImageStoreData::fromArray([
                ...$validated,
                'image' => $request->file('image'),
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully.',
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: '.$e->getMessage(),
            ], 500);
        }
    }
}

