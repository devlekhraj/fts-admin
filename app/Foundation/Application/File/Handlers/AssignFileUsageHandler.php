<?php

declare(strict_types=1);

namespace App\Foundation\Application\File\Handlers;

use App\Foundation\Application\File\Commands\AssignFileUsageCommand;
use Illuminate\Support\Facades\DB;

final class AssignFileUsageHandler
{
    public function handle(AssignFileUsageCommand $command): array
    {
        $caption = is_string($command->caption) ? trim($command->caption) : '';
        $description = is_string($command->description) ? trim($command->description) : '';

        if ($command->isDefault) {
            DB::table('file_usages')
                ->where('usage_type', $command->usageType)
                ->where('usage_id', $command->usageId)
                ->update(['meta->is_default' => false]);
        }

        DB::table('file_usages')->upsert(
            [[
                'file_id' => $command->fileId,
                'usage_type' => $command->usageType,
                'usage_id' => $command->usageId,
                'title' => $caption !== '' ? $caption : null,
                'alt_text' => $command->altText,
                'meta' => json_encode([
                    'caption' => $caption !== '' ? $caption : null,
                    'description' => $description !== '' ? $description : null,
                    'is_default' => $command->isDefault,
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['file_id', 'usage_type', 'usage_id'],
            ['title', 'alt_text', 'meta', 'updated_at']
        );

        $usage = DB::table('file_usages')
            ->where('file_id', $command->fileId)
            ->where('usage_type', $command->usageType)
            ->where('usage_id', $command->usageId)
            ->first();

        return [
            'usage' => $usage ? (array) $usage : null,
        ];
    }
}
