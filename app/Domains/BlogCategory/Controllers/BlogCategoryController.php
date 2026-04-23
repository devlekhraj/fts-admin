<?php

declare(strict_types=1);

namespace App\Domains\BlogCategory\Controllers;

use App\Domains\BlogCategory\DTOs\BlogCategoryCreateData;
use App\Domains\BlogCategory\DTOs\BlogCategoryUpdateData;
use App\Domains\BlogCategory\Requests\StoreBlogCategoryRequest;
use App\Domains\BlogCategory\Requests\UpdateBlogCategoryRequest;
use App\Domains\BlogCategory\Resources\BlogCategoryResource;
use App\Domains\BlogCategory\Services\BlogCategoryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BlogCategoryController extends Controller
{
    public function __construct(
        private readonly BlogCategoryService $blogCategoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPageParam = (int) $request->query('per_page', 15);
        $search = $request->query('search');
        $result = $this->blogCategoryService->list(is_string($search) ? $search : null, $perPageParam);

        return response()->json([
            'data' => BlogCategoryResource::collection($result['items'] ?? []),
            'meta' => $result['meta'] ?? null,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $category = $this->blogCategoryService->detail($id);

        return response()->json([
            'data' => (new BlogCategoryResource($category)),
            'success' => true,
        ], 200);
    }

    public function store(StoreBlogCategoryRequest $request): JsonResponse
    {
        $dto = BlogCategoryCreateData::fromArray($request->validated());
        $category = $this->blogCategoryService->create($dto);

        return response()->json([
            'message' => 'Blog category created successfully.',
            'data' => new BlogCategoryResource($category),
            'success' => true,
        ], 201);
    }

    public function update(UpdateBlogCategoryRequest $request, string $id): JsonResponse
    {
        $dto = BlogCategoryUpdateData::fromArray($request->validated());
        $category = $this->blogCategoryService->update($id, $dto);

        return response()->json([
            'message' => 'Blog category updated successfully.',
            'data' => new BlogCategoryResource($category),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->blogCategoryService->delete($id);

        return response()->json([
            'message' => 'Blog category deleted successfully.',
            'success' => true,
        ], 200);
    }
}

