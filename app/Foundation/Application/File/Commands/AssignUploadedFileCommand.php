<?php

declare(strict_types=1);

namespace App\Foundation\Application\File\Commands;

use Illuminate\Http\UploadedFile;

final class AssignUploadedFileCommand
{
    public function __construct(
        public readonly UploadedFile $file,
        public readonly ?string $directory = null,
    ) {}
}
