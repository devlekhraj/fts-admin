<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Application\Contracts;

use Illuminate\Http\UploadedFile;

interface ImageUploadService
{
    /**
     * Upload an image to storage and persist a new files row.
     *
     * Stores under "{usageType}/{usageId}/{fileName}" on the configured disk.
     *
     * @return array{file_id:int, file_data:array}
     */
    public function upload(UploadedFile $file, string $usageType, int $usageId, ?string $fileName = null): array;
}

