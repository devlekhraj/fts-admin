<?php

declare(strict_types=1);

namespace App\Foundation\Application\File\Handlers;

use App\Foundation\Application\File\Commands\AssignUploadedFileCommand;
use App\Foundation\Shared\Application\Contracts\FileUploadService;
use RuntimeException;

final class AssignUploadedFileHandler
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function handle(AssignUploadedFileCommand $command): array
    {
        $uploadResult = $this->fileUploadService->uploadImageAsWebp(
            $command->file,
            $command->directory
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
