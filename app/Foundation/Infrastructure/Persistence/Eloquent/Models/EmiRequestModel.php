<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmiRequestModel extends BaseModel
{
    protected $table = 'emi_requests';

    protected $casts = [
        'product_attributes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_PENDING = 0;

    public const STATUS_PROCESSING = 1;

    public const STATUS_APPROVED = 2;

    public const STATUS_FINISHED = 3;

    public const STATUS_CANCELLED = 4;

    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_FINISHED => 'Finished',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
    public function bank(): BelongsTo
    {
        return $this->belongsTo(EmiBankModel::class, 'bank');
    }
}
