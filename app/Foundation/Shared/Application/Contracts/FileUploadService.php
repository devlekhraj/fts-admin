<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Application\Contracts;

use Illuminate\Http\UploadedFile;

interface FileUploadService
{
    /**
     * Upload an image, convert/store as WebP, and return normalized metadata.
     *
     * @return array{
     *   key: string,
     *   file_name: string,
     *   file_path: string,
     *   extension: string,
     *   mime_type: string,
     *   file_size: int,
     *   height: float|null,
     *   width: float|null,
     *   meta: array,
     *   url: string,
     *   already_exists: bool
     * }
     */
    public function uploadImageAsWebp(UploadedFile $file, ?string $directory = null): array;
}
