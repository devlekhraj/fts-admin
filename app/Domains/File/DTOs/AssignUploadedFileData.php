<?php

declare(strict_types=1);

namespace App\Domains\File\DTOs;

use Illuminate\Http\UploadedFile;

final class AssignUploadedFileData
{
    public function __construct(
        public readonly UploadedFile $file,
        public readonly string $usageType,
        public readonly int $usageId,
        public readonly ?string $fileName = null,
    ) {}
}
