<?php

declare(strict_types=1);

namespace App\Domains\File\Services;

use App\Domains\File\Actions\ImageUploadAction;
use App\Domains\File\DTOs\ImageAssignData;

final class ImageAssignService
{
    public function __construct(
        private readonly ImageUploadAction $imageUploadAction
    ) {}

    public function assign(ImageAssignData $data): array
    {
        return $this->imageUploadAction->execute($data);
    }
}

