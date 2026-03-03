<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Banner;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\BannerModel;
use App\Foundation\Interfaces\Http\Resources\BannerResource;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function bannerList(Request $request): JsonResponse
    {
        $query = BannerModel::query()
            ->select(['id', 'name', 'slug', 'status', 'created_at'])
            ->with(['defaultFile'])
            ->withCount(['files as total_images'])
            ->orderByDesc('created_at');

        if ($search = $request->query('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $perPageParam = (int) $request->query('per_page', 15);
        if ($perPageParam === -1) {
            $items = $query->get();

            return response()->json([
                'data' => BannerResource::collection($items),
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
            'data' => BannerResource::collection($paginator->items()),
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

    public function show(string $id): JsonResponse
    {
        $banner = BannerModel::query()
            ->with(['defaultFile', 'files'])
            ->withCount(['files as total_images'])
            ->findOrFail($id);

        return response()->json((new BannerResource($banner))->resolve());
    }

    public function store(): JsonResponse
    {
        return response()->json([], 201);
    }

    public function update(string $id): JsonResponse
    {
        return response()->json(['id' => $id]);
    }

    public function destroy(string $id): JsonResponse
    {
        return response()->json(null, 204);
    }
}
