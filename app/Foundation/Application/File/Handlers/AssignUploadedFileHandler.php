<?php

declare(strict_types=1);

namespace App\Foundation\Application\File\Handlers;

use App\Foundation\Application\File\Commands\AssignUploadedFileCommand;
use RuntimeException;
use App\Foundation\Shared\Application\Contracts\ImageUploadService;

final class AssignUploadedFileHandler
{
    public function __construct(
        private readonly ImageUploadService $imageUploadService,
    ) {}

    public function handle(AssignUploadedFileCommand $command): array
    {
        $uploadResult = $this->imageUploadService->upload(
            file: $command->file,
            usageType: $command->usageType,
            usageId: $command->usageId,
            fileName: $command->fileName,
        );

        $fileData = is_array($uploadResult['file_data'] ?? null) ? $uploadResult['file_data'] : null;
        $fileId = isset($fileData['id']) ? (int) $fileData['id'] : null;
        if (! $fileId) {
            throw new RuntimeException('Uploaded file could not be resolved.');
        }

        return [
            'file_id' => $fileId,
            'file_data' => $fileData,
            'source' => 'upload',
        ];
    }
}
