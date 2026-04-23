<?php

declare(strict_types=1);

namespace App\Domains\BlogCategory\Controllers;

use App\Domains\BlogCategory\DTOs\BlogCategoryImageUpdateData;
use App\Domains\BlogCategory\Requests\UpdateBlogCategoryImageRequest;
use App\Domains\BlogCategory\Services\BlogCategoryImageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class BlogCategoryImageController extends Controller
{
    public function __construct(
        private readonly BlogCategoryImageService $blogCategoryImageService
    ) {}

    public function update(UpdateBlogCategoryImageRequest $request, string $id, string $fileUsageId): JsonResponse
    {
        $dto = BlogCategoryImageUpdateData::fromArray($request->validated());
        $fileUsage = $this->blogCategoryImageService->update($id, $fileUsageId, $dto);

        if ($fileUsage === null) {
            return response()->json([
                'message' => 'Blog category image not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Blog category image updated successfully.',
            'data' => [
                'id' => $fileUsage->id,
            ],
        ]);
    }

    public function delete(string $id, string $fileUsageId): JsonResponse
    {
        $deleted = $this->blogCategoryImageService->delete($id, $fileUsageId);
        if (! $deleted) {
            return response()->json([
                'message' => 'Blog category image not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Blog category image deleted successfully.',
        ]);
    }
}

