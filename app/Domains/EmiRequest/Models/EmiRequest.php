<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Models;

use App\Domains\EmiBank\Models\EmiBank;
use App\Domains\File\Models\File;
use App\Domains\Product\Models\Product;
use App\Domains\User\Models\User;
use App\Support\Eloquent\BaseModel;
use App\Models\Concerns\HasActivityLogs;
use App\Models\EmiRequestBank;
use App\Models\EmiRequestCreditCard;
use App\Models\EmiRequestGuarantor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class EmiRequest extends BaseModel
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
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(EmiBank::class, 'bank');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
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
