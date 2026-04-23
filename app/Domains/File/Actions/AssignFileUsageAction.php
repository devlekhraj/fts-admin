<?php

declare(strict_types=1);

namespace App\Domains\File\Actions;

use App\Domains\File\DTOs\AssignFileUsageData;
use Illuminate\Support\Facades\DB;

final class AssignFileUsageAction
{
    public function execute(AssignFileUsageData $data): array
    {
        $caption = is_string($data->caption) ? trim($data->caption) : '';
        $description = is_string($data->description) ? trim($data->description) : '';

        if ($data->isDefault) {
            DB::table('file_usages')
                ->where('usage_type', $data->usageType)
                ->where('usage_id', $data->usageId)
                ->update(['meta->is_default' => false]);
        }

        DB::table('file_usages')->upsert(
            [[
                'file_id' => $data->fileId,
                'usage_type' => $data->usageType,
                'usage_id' => $data->usageId,
                'title' => $caption !== '' ? $caption : null,
                'alt_text' => $data->altText,
                'meta' => json_encode(array_filter([
                    'caption' => $caption !== '' ? $caption : null,
                    'description' => $description !== '' ? $description : null,
                    'is_default' => $data->isDefault ?: null,
                ], fn($v) => ! is_null($v)), JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['file_id', 'usage_type', 'usage_id'],
            ['title', 'alt_text', 'meta', 'updated_at']
        );

        $usage = DB::table('file_usages')
            ->where('file_id', $data->fileId)
            ->where('usage_type', $data->usageType)
            ->where('usage_id', $data->usageId)
            ->first();

        return [
            'usage' => $usage ? (array) $usage : null,
        ];
    }
}
