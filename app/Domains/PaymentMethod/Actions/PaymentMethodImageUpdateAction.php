<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\Actions;

use App\Domains\File\Models\FileUsage;
use App\Domains\PaymentMethod\DTOs\PaymentMethodImageUpdateData;
use Illuminate\Support\Facades\DB;

final class PaymentMethodImageUpdateAction
{
    public function execute(string $paymentMethodId, string $fileUsageId, PaymentMethodImageUpdateData $data): ?FileUsage
    {
        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', 'payment_methods')
            ->where('usage_id', $paymentMethodId)
            ->first();

        if (! $fileUsage) {
            return null;
        }

        $isDefault = $data->isDefault;
        $meta = is_array($fileUsage->meta) ? $fileUsage->meta : [];
        $meta['is_default'] = $isDefault;

        DB::transaction(function () use ($fileUsage, $data, $meta, $isDefault): void {
            if ($isDefault) {
                FileUsage::query()
                    ->where('usage_type', 'payment_methods')
                    ->where('usage_id', $fileUsage->usage_id)
                    ->where('id', '!=', $fileUsage->id)
                    ->update([
                        'meta->is_default' => false,
                    ]);
            }

            $fileUsage->alt_text = $data->altText;
            $fileUsage->meta = $meta;
            $fileUsage->save();
        });

        return $fileUsage;
    }
}

