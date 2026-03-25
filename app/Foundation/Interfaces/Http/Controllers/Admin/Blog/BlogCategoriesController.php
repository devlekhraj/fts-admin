<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Blog;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\BlogCategoryModel;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateBlogCategoryRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreBlogCategoryRequest;
use App\Foundation\Interfaces\Http\Resources\BlogCategoryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogCategoriesController extends Controller
{
    public function categoryList(Request $request): JsonResponse
    {
        $query = BlogCategoryModel::query()
            ->with(['defaultFile'])
            ->orderByDesc('created_at');

        if ($search = $request->query('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $perPageParam = (int) $request->query('per_page', 15);
        if ($perPageParam === -1) {
            $items = $query->get();

            return response()->json([
                'data' => BlogCategoryResource::collection($items),
                'meta' => [
                    'current_page' => 1,
                    'per_page' => $items->count(),
                    'total' => $items->count(),
                    'last_page' => 1,
                    'from' => $items->count() > 0 ? 1 : null,
                    'to' => $items->count() > 0 ? $items->count() : null,
                ],
            ]);
        }

        $perPage = max(1, min($perPageParam, 100));
        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => BlogCategoryResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function categoryShow(string $id)
    {
        $category = BlogCategoryModel::query()
            ->with(['defaultFile','files'])
            ->findOrFail($id);

        return Response([
            "data" => (new BlogCategoryResource($category)),
            "success" => true,
        ],200);
    }

    public function store(StoreBlogCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $category = BlogCategoryModel::query()->create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'short_desc' => "",
            'content' => "",
            'status' => array_key_exists('status', $validated) ? (bool) $validated['status'] : true,
        ]);

        return response()->json([
            'message' => 'Blog category created successfully.',
            'data' => new BlogCategoryResource($category),
            'success' => true,
        ], 201);
    }

    public function update(UpdateBlogCategoryRequest $request, string $id): JsonResponse
    {
        $category = BlogCategoryModel::query()->findOrFail($id);
        $category->update($request->validated());

        return response()->json([
            'message' => 'Blog category updated successfully.',
            'data' => new BlogCategoryResource($category),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $category = BlogCategoryModel::query()->findOrFail($id);

        try {
            $category->files()->detach();
        } catch (\Throwable $e) {
            // Best effort cleanup; proceed to delete category even if detach fails.
        }

        $category->delete();

        return response()->json([
            'message' => 'Blog category deleted successfully.',
            'success' => true,
        ], 200);
    }
}
