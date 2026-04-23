<?php

declare(strict_types=1);

namespace App\Domains\File\Actions;

use App\Domains\File\DTOs\AssignUploadedFileData;
use RuntimeException;
use App\Domains\File\Services\FileUploadService;

final class AssignUploadedFileAction
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function execute(AssignUploadedFileData $data): array
    {
        $uploadResult = $this->fileUploadService->upload(
            file: $data->file,
            directory: trim($data->usageType, '/').'/'.(string) $data->usageId,
            preferredFileName: $data->fileName,
            dedupeByContentHash: false,
        );

        $fileData = is_array($uploadResult['file_data'] ?? null) ? $uploadResult['file_data'] : null;
        $fileId = (int) ($uploadResult['file_id'] ?? 0);
        if (! $fileId && isset($fileData['id'])) {
            $fileId = (int) $fileData['id'];
        }
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
