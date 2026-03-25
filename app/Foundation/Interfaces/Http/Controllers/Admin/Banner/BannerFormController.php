<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Banner;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\BannerModel;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreBannerRequest;
use App\Foundation\Interfaces\Http\Resources\BannerResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BannerFormController extends Controller
{
    public function store(StoreBannerRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $banner = BannerModel::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'status' => false,
        ]);

        return response()->json([
            'message' => 'Banner created successfully.',
            'data' => (new BannerResource($banner))->resolve(),
            'success' => true,
        ], 201);
    }
}
