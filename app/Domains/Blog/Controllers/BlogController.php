<?php

declare(strict_types=1);

namespace App\Domains\Blog\Controllers;

use App\Domains\Blog\DTOs\BlogCreateData;
use App\Domains\Blog\DTOs\BlogUpdateData;
use App\Domains\Blog\Requests\StoreBlogPostRequest;
use App\Domains\Blog\Requests\UpdateBlogPostRequest;
use App\Domains\Blog\Resources\BlogResource;
use App\Domains\Blog\Services\BlogService;
use Illuminate\Routing\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BlogController extends Controller
{
    public function __construct(
        private readonly BlogService $blogService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPageParam = (int) $request->query('per_page', 15);
        $search = $request->query('search');
        $result = $this->blogService->list(is_string($search) ? $search : null, $perPageParam);

        return response()->json([
            'data' => BlogResource::collection($result['items'] ?? []),
            'meta' => $result['meta'] ?? null,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $blog = $this->blogService->detail($id);

        return response()->json([
            'data' => (new BlogResource($blog)),
            'success' => true,
        ], 200);
    }

    public function store(StoreBlogPostRequest $request): JsonResponse
    {
        $dto = BlogCreateData::fromArray($request->validated());
        $blog = $this->blogService->create($dto);

        return response()->json([
            'message' => 'Blog created successfully.',
            'data' => new BlogResource($blog),
            'success' => true,
        ], 201);
    }

    public function update(UpdateBlogPostRequest $request, string $id): JsonResponse
    {
        $dto = BlogUpdateData::fromArray($request->validated());
        $blog = $this->blogService->update($id, $dto);

        return response()->json([
            'message' => 'Blog updated successfully.',
            'data' => new BlogResource($blog),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->blogService->delete($id);

        return response()->json([
            'message' => 'Blog deleted successfully.',
            'success' => true,
        ], 200);
    }
}
