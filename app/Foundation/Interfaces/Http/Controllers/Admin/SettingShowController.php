<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\SettingModel;
use App\Foundation\Interfaces\Http\Resources\SettingResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class SettingShowController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = SettingModel::query()->get();

        return response()->json([
            'data' => SettingResource::collection($settings),
        ]);
    }

    public function details(Request $request, $id): JsonResponse
    {
        // TODO: List permissions.
        return response()->json([]);
    }
}
