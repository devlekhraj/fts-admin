<?php

declare(strict_types=1);

namespace App\Foundation\Application\File\Commands;

final class AssignExistingFileCommand
{
    public function __construct(
        public readonly int $imageId,
    ) {}
}
