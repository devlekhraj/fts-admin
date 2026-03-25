<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Blog;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\BlogCategoryModel;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\BlogModel;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\FileUsageModel;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreBlogPostRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateBlogPostRequest;
use App\Foundation\Interfaces\Http\Resources\BlogResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogsController extends Controller
{
    public function blogList(Request $request): JsonResponse
    {
        $query = BlogModel::query()
            ->with(['defaultFile', 'category'])
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
                'data' => BlogResource::collection($items),
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
            'data' => BlogResource::collection($paginator->items()),
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

    public function blogShow(string $id): JsonResponse
    {
        $blog = BlogModel::query()
            ->with(['defaultFile', 'files', 'category'])
            ->findOrFail($id);

        return response()->json([
            'data' => (new BlogResource($blog)),
            'success' => true,
        ], 200);
    }

    public function blogStore(StoreBlogPostRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Ensure default "Unlisted" category exists (restoring if soft-deleted).
        $category = BlogCategoryModel::query()
            ->withTrashed()
            ->where('slug', 'unlisted')
            ->first();

        if ($category?->trashed()) {
            $category->restore();
        }

        if (! $category) {
            $category = BlogCategoryModel::query()->create([
                'title' => 'Unlisted',
                'slug' => 'unlisted',
                'short_desc' => '',
                'content' => '',
                'status' => false,
            ]);
        }

        $blog = BlogModel::query()->create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'status' => array_key_exists('status', $validated) ? (bool) $validated['status'] : true,
            'category_id' => $category->id,
            //   'short_desc' =>null,
            'content' =>'',
            'author' =>''

        ]);

        return response()->json([
            'message' => 'Blog created successfully.',
            'data' => new BlogResource($blog),
            'success' => true,
        ], 201);
    }

    public function blogUpdate(UpdateBlogPostRequest $request, string $id): JsonResponse
    {
        $blog = BlogModel::query()->findOrFail($id);
        $blog->update($request->validated());

        return response()->json([
            'message' => 'Blog updated successfully.',
            'data' => new BlogResource($blog),
        ]);
    }

    public function blogDestroy(string $id): JsonResponse
    {
        $blog = BlogModel::query()->findOrFail($id);

        try {
            FileUsageModel::query()
                ->where('usage_type', 'blogs')
                ->where('usage_id', $blog->id)
                ->delete();
            $blog->files()->detach();
        } catch (\Throwable $e) {
            // proceed even if cleanup partially fails
        }

        $blog->delete();

        return response()->json([
            'message' => 'Blog deleted successfully.',
            'success' => true,
        ], 200);
    }
}
