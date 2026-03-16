<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\SettingModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingUpdateController extends Controller
{
    public function update(Request $request, string $module): JsonResponse
    {
        $setting = SettingModel::query()
            ->where('module', $module)
            ->first();

        if (!$setting) {
            $setting = new SettingModel();
            $setting->module = $module;
        }

        $setting->settings = $request->input('settings', []);
        $setting->save();

        return response()->json([
            'message' => 'Settings updated successfully.',
            'data' => $setting,
        ]);
    }
}
