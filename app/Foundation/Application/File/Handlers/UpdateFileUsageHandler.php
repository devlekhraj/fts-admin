<?php

declare(strict_types=1);

namespace App\Foundation\Application\File\Handlers;

use App\Foundation\Application\File\Commands\UpdateFileUsageCommand;
use App\Foundation\Shared\Application\DTO\ActionResult;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;
use Illuminate\Support\Facades\DB;

final class UpdateFileUsageHandler
{
    public function handle(UpdateFileUsageCommand $command): ActionResult
    {
        if (
            $command->startDate !== null &&
            $command->startDate !== '' &&
            $command->endDate !== null &&
            $command->endDate !== '' &&
            strtotime($command->endDate) <= strtotime($command->startDate)
        ) {
            throw new FieldValidationException('end_date', 'End date must be greater than start date.');
        }

        $usage = DB::table('file_usages')
            ->where('id', $command->fileUsageId)
            ->first();

        if (! $usage) {
            throw new FieldValidationException('id', 'File usage not found.');
        }

        $link = trim((string) ($command->link ?? ''));
        $startDate = trim((string) ($command->startDate ?? ''));
        $endDate = trim((string) ($command->endDate ?? ''));

        $meta = [
            'link' => $link !== '' ? $link : null,
            'start_date' => $startDate !== '' ? $startDate : null,
            'end_date' => $endDate !== '' ? $endDate : null,
            'seq_no' => $command->seqNo,
            'is_active' => $command->isActive,
        ];

        DB::table('file_usages')
            ->where('id', (int) $usage->id)
            ->update([
                'alt_text' => $command->altText,
                'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);

        return new ActionResult(true, 'File usage updated successfully.');
    }
}
