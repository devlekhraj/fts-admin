<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\Models;

use App\Domains\File\Models\File;
use App\Domains\File\Models\FileUsage;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class PaymentMethod extends BaseModel
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
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->using(FileUsage::class)
            ->wherePivot('usage_type', 'payment_methods')
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->withTimestamps();
    }

    public function defaultFile(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->using(FileUsage::class)
            ->wherePivot('usage_type', 'payment_methods')
            ->whereRaw("JSON_EXTRACT(file_usages.meta, '$.is_default') = true")
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->orderByPivot('id', 'asc');
    }
}
