<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use App\Models\Concerns\HasActivityLogs;
use App\Models\EmiRequestBank;
use App\Models\EmiRequestCreditCard;
use App\Models\EmiRequestGuarantor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmiRequestModel extends BaseModel
{
    use HasActivityLogs;
    
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

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(FileModel::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'emi_requests')
            ->withPivot(['title'])
            ->withTimestamps();
    }

    public function guarantors(): HasMany
    {
        return $this->hasMany(EmiRequestGuarantor::class, 'emi_request_id', 'id');
    }

    public function creditCard(): HasOne
    {
        return $this->hasOne(EmiRequestCreditCard::class, 'emi_request_id', 'id');
    }

    public function requestBank(): HasOne
    {
        return $this->hasOne(EmiRequestBank::class, 'emi_request_id', 'id');
    }
}
