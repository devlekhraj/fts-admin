<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentMethodModel extends BaseModel
{
    protected $table = 'payment_methods';

    protected $fillable = [
        'name', 'slug', 'status', 'test_mode', 'is_international', 'config',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_international' => 'boolean',
        'test_mode' => 'boolean',
        'config' => 'array',
    ];
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(FileModel::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'payment_methods')
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->withTimestamps();
    }

    public function defaultFile(): BelongsToMany
    {
        return $this->belongsToMany(FileModel::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'payment_methods')
            ->whereRaw("JSON_EXTRACT(file_usages.meta, '$.is_default') = true")
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->orderByPivot('id', 'asc');
    }
}
