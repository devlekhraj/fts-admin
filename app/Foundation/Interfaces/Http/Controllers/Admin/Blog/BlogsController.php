<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Blog;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\BlogModel;
use App\Foundation\Interfaces\Http\Resources\BlogResource;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
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
        return response()->json(['id' => $id]);
    }

    public function blogStore(): JsonResponse
    {
        return response()->json([], 201);
    }

    public function blogUpdate(string $id): JsonResponse
    {
        return response()->json(['id' => $id]);
    }

    public function blogDestroy(string $id): JsonResponse
    {
        return response()->json(null, 204);
    }
}
