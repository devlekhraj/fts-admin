<?php

declare(strict_types=1);

namespace App\Domains\File\Actions;

use App\Domains\File\DTOs\UpdateFileUsageData;
use App\DTO\ActionResultDto;
use App\Support\Exceptions\FieldValidationException;
use Illuminate\Support\Facades\DB;

final class UpdateFileUsageAction
{
    public function execute(UpdateFileUsageData $data): ActionResultDto
    {
        if (
            $data->startDate !== null &&
            $data->startDate !== '' &&
            $data->endDate !== null &&
            $data->endDate !== '' &&
            strtotime($data->endDate) <= strtotime($data->startDate)
        ) {
            throw new FieldValidationException('end_date', 'End date must be greater than start date.');
        }

        $usage = DB::table('file_usages')
            ->where('id', $data->fileUsageId)
            ->first();

        if (! $usage) {
            throw new FieldValidationException('id', 'File usage not found.');
        }

        $link = trim((string) ($data->link ?? ''));
        $startDate = trim((string) ($data->startDate ?? ''));
        $endDate = trim((string) ($data->endDate ?? ''));

        $meta = array_filter([
            'link' => $link !== '' ? $link : null,
            'start_date' => $startDate !== '' ? $startDate : null,
            'end_date' => $endDate !== '' ? $endDate : null,
            'seq_no' => $data->seqNo,
            'is_active' => $data->isActive,
            'is_default' => $data->isDefault,
        ], fn($v) => ! is_null($v));

        if ($data->isDefault) {
            DB::table('file_usages')
                ->where('usage_type', $usage->usage_type)
                ->where('usage_id', $usage->usage_id)
                ->where('id', '!=', (int) $usage->id)
                ->update(['meta->is_default' => false]);
        }

        DB::table('file_usages')
            ->where('id', (int) $usage->id)
            ->update([
                'title' => $data->title !== '' ? $data->title : null,
                'alt_text' => $data->altText,
                'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);

        return new ActionResultDto(true, 'File usage updated successfully.');
    }
}
