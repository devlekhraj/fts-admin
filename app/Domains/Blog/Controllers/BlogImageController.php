<?php

declare(strict_types=1);

namespace App\Domains\Blog\Controllers;

use App\Domains\Blog\DTOs\BlogImageUpdateData;
use App\Domains\Blog\Requests\UpdateBlogImageRequest;
use App\Domains\Blog\Services\BlogImageService;
use Illuminate\Routing\Controller;

use Illuminate\Http\JsonResponse;

final class BlogImageController extends Controller
{
    public function __construct(
        private readonly BlogImageService $blogImageService
    ) {}

    public function update(UpdateBlogImageRequest $request, string $id, string $fileUsageId): JsonResponse
    {
        $dto = BlogImageUpdateData::fromArray($request->validated());
        $fileUsage = $this->blogImageService->update($id, $fileUsageId, $dto);

        if ($fileUsage === null) {
            return response()->json([
                'message' => 'Blog image not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Blog image updated successfully.',
            'data' => [
                'id' => $fileUsage->id,
            ],
        ]);
    }

    public function delete(string $id, string $fileUsageId): JsonResponse
    {
        $deleted = $this->blogImageService->delete($id, $fileUsageId);
        if (! $deleted) {
            return response()->json([
                'message' => 'Blog image not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Blog image deleted successfully.',
        ]);
    }
}
