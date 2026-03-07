<?php

declare(strict_types=1);

namespace App\Foundation\Application\File\Handlers;

use App\Foundation\Application\File\Commands\AssignExistingFileCommand;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\FileModel;
use RuntimeException;

final class AssignExistingFileHandler
{
    public function handle(AssignExistingFileCommand $command): array
    {
        $file = FileModel::query()->find($command->imageId);
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
