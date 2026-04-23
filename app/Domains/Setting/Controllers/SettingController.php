<?php

declare(strict_types=1);

namespace App\Domains\Setting\Controllers;

use App\Domains\Setting\DTOs\SettingUpdateData;
use App\Domains\Setting\Resources\SettingResource;
use App\Domains\Setting\Services\SettingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settingService)
    {
    }

    public function index(): JsonResponse
    {
        $settings = $this->settingService->list();

        return response()->json([
            'data' => SettingResource::collection($settings),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return response()->json([]);
    }

    public function update(Request $request, string $module): JsonResponse
    {
        $setting = $this->settingService->update(SettingUpdateData::fromArray($module, $request->all()));

        return response()->json([
            'message' => 'Settings updated successfully.',
            'data' => $setting,
        ]);
    }
}

