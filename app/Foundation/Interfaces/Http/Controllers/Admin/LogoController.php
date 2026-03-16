<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\FileUsageModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class LogoController extends Controller
{
    public function index(): JsonResponse
    {
        $usages = FileUsageModel::query()
            ->with('file')
            ->where('usage_type', 'logo')
            ->latest('id')
            ->get()
            ->map(function ($usage) {
                return [
                    'id' => $usage->id,
                    'file_id' => $usage->file_id,
                    'usage_type' => $usage->usage_type,
                    'usage_id' => $usage->usage_id,
                    'title' => $usage->title,
                    'alt_text' => $usage->alt_text,
                    'meta' => $usage->meta,
                    'url' => $usage->file?->url ?? null,
                    'size_info' => $usage->file ? "{$usage->file->width}x{$usage->file->height} (" . round($usage->file->file_size / 1024, 2) . " KB)" : null,
                    'created_at' => $usage->created_at,
                    'updated_at' => $usage->updated_at,
                ];
            });

        return response()->json([
            'data' => $usages,
        ]);
    }
}
