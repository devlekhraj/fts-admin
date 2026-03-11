<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductModel extends BaseModel
{
    protected $table = 'products';

    protected $casts = [
        'status' => 'boolean',
        'emi_enabled' => 'boolean',
        'pre_order' => 'boolean',
        'is_featured' => 'boolean',
        'attributes' => 'array'
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrandModel::class, 'brand_id');
    }
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariantModel::class, 'product_id');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(FileModel::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'products')
             ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->withTimestamps();
    }

    public function defaultFile(): BelongsToMany
    {
        return $this->belongsToMany(FileModel::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'products')
            ->whereRaw("JSON_EXTRACT(file_usages.meta, '$.is_default') = true")
             ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
              ->orderByPivot('id', 'asc');
    }



    public function attribute(): BelongsTo
    {
        return $this->belongsTo(AttributeClassModel::class, 'attribute_class_id');
    }
}
