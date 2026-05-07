<?php

declare(strict_types=1);

namespace App\Domains\Banner\Controllers;

use App\Domains\Banner\DTOs\BannerCreateData;
use App\Domains\Banner\DTOs\BannerUpdateData;
use App\Domains\Banner\Models\Banner;
use App\Domains\Banner\Requests\StoreBannerRequest;
use App\Domains\Banner\Requests\UpdateBannerRequest;
use App\Domains\Banner\Resources\BannerResource;
use App\Domains\Banner\Services\BannerService;
use Illuminate\Routing\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BannerController extends Controller
{
    public function __construct(
        private readonly BannerService $bannerService
    ) {}

    public function bannerList(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $perPageParam = (int) $request->query('per_page', 15);

        $result = $this->bannerService->list(is_string($search) ? $search : null, $perPageParam);
        $items = $result['items'] ?? [];
        $meta = $result['meta'] ?? null;

        return response()->json([
            'data' => BannerResource::collection($items),
            'meta' => $meta,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $banner = Banner::query()
            ->with(['defaultFile', 'files'])
            ->withCount(['files as total_images'])
            ->findOrFail($id);

        return response()->json((new BannerResource($banner))->resolve());
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        $dto = BannerCreateData::fromArray($request->validated());

        $banner = $this->bannerService->create($dto);

        return response()->json([
            'message' => 'Banner created successfully.',
            'data' => (new BannerResource($banner))->resolve(),
            'success' => true,
        ], 201);
    }

    public function update(string $id, UpdateBannerRequest $request): JsonResponse
    {
        $dto = BannerUpdateData::fromArray($request->validated());

        $banner = $this->bannerService->update($id, $dto);

        return response()->json((new BannerResource($banner))->resolve());
    }

    public function destroy(string $id): JsonResponse
    {
        $this->bannerService->delete($id);

        return response()->json([
            'message' => 'Banner deleted successfully.',
            'success' => true,
        ], 200);
    }
}
