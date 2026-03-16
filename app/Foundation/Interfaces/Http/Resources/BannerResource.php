<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use App\Foundation\Shared\Support\Formatters\ByteSizeFormatter;
use App\Foundation\Shared\Support\Formatters\FileDimensionFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request): array
    {
        $defaultFile = null;
        if ($this->relationLoaded('defaultFile')) {
            $defaultFile = $this->defaultFile->first();
        }

        if ($request->route()?->getName() === 'admin.banners.show') {
            return $this->showResponse($defaultFile);
        }

        return $this->listResponse($defaultFile);
    }

    private function isImageActive($file, string $today): bool
    {
        $rawMeta = is_array($file->pivot?->meta) ? $file->pivot?->meta : [];
        $startDate = $rawMeta['start_date'] ?? null;
        $endDate = $rawMeta['end_date'] ?? null;

        if ($startDate && $endDate && $today >= $startDate && $today <= $endDate) {
            return true;
        }

        return false;
    }

    private function listResponse($defaultFile): array
    {
        $status = (bool) $this->status;
        if ($this->relationLoaded('files')) {
            $today = date('Y-m-d');
            $hasActiveImage = false;
            foreach ($this->files as $file) {
                if ($this->isImageActive($file, $today)) {
                    $hasActiveImage = true;
                    break;
                }
            }
            $status = $hasActiveImage;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $status,
            'created_at' => $this->created_at,
            'total_images' => (int) ($this->total_images ?? 0),
            'thumb' => $defaultFile?->url,
        ];
    }

    private function showResponse($defaultFile): array
    {
        $files = [];
        $overallStatus = false;

        if ($this->relationLoaded('files')) {
            $today = date('Y-m-d');
            $files = $this->files->map(function ($file) use ($today, &$overallStatus) {
                $rawMeta = is_array($file->pivot?->meta) ? $file->pivot?->meta : [];
                $meta = [
                    'link' => $rawMeta['link'] ?? null,
                    'start_date' => $rawMeta['start_date'] ?? null,
                    'end_date' => $rawMeta['end_date'] ?? null,
                ];

                $isActive = $this->isImageActive($file, $today);
                if ($isActive) {
                    $overallStatus = true;
                }

                $fileSize = ByteSizeFormatter::format($file->file_size ?? null);
                $fileDimension = FileDimensionFormatter::format($file->width ?? null, $file->height ?? null);

                return [
                    'id' => $file->pivot?->id,
                    'file_id' => $file->id,
                    'url' => $file->url,
                    'title' => $file->pivot?->title,
                    'alt_text' => $file->pivot?->alt_text,
                    'status' => $isActive,
                    'meta' => $meta,
                    'size_info' => "{$fileSize} | {$fileDimension}",
                ];
            })->values()->all();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $overallStatus,
            'created_at' => $this->created_at,
            'total_images' => (int) ($this->total_images ?? 0),
            'thumb' => $defaultFile?->url,
            'files' => $files,
        ];
    }
}
