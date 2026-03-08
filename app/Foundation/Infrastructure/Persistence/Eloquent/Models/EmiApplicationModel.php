<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class EmiApplicationModel extends BaseModel
{
    protected $table = 'emi_applications';

    protected $casts = [
        'application_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $application): void {
            if (is_string($application->application_id) && trim($application->application_id) !== '') {
                return;
            }

            $application->application_id = self::generateApplicationId();
        });
    }

    private static function generateApplicationId(): string
    {
        $last = self::query()
            ->where('application_id', 'like', 'EMIAPP-%')
            ->orderByDesc('id')
            ->value('application_id');

        $next = self::extractSequence($last) + 1;

        do {
            $id = 'EMI-APP-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (self::query()->where('application_id', $id)->exists());

        return $id;
    }

    private static function extractSequence(mixed $value): int
    {
        if (!is_string($value)) {
            return 0;
        }

        if (!preg_match('/^EMIAPP-(\d{6})$/', $value, $matches)) {
            return 0;
        }

        return (int) ($matches[1] ?? 0);
    }

    public function emiRequest()
    {
        return $this->belongsTo(EmiRequestModel::class, 'emi_request_id');
    }

    public function emiBank()
    {
        return $this->belongsTo(EmiBankModel::class, 'emi_bank_id');
    }
}
