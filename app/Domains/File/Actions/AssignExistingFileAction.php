<?php

declare(strict_types=1);

namespace App\Domains\File\Actions;

use App\Domains\File\DTOs\AssignExistingFileData;
use App\Domains\File\Models\File;
use RuntimeException;

final class AssignExistingFileAction
{
    public function execute(AssignExistingFileData $data): array
    {
        $file = File::query()->find($data->imageId);
        if (! $file) {
            throw new RuntimeException('Selected image not found.');
        }

        return [
            'file_id' => (int) $file->id,
            'file_data' => $file->toArray(),
            'source' => 'existing',
        ];
    }
}
