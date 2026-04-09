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

    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;

    const STATUS_DRAFT = 2;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'sku',
        'price',
        'original_price',
        'brand_id',
        'vendor_id',
        'quantity',
        'pre_order',
        'pre_order_price',
        'unit',
        'highlights',
        'product_video_url',
        'weight',
        'length',
        'width',
        'height',
        'status',
        'is_featured',
        'emi_enabled',
        'attributes',
        'attribute_class_id',
        'variant_attributes',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'custom_code',
        'warranty_description',
    ];

    protected $casts = [
        'status' => 'boolean',
        'emi_enabled' => 'boolean',
        'pre_order' => 'boolean',
        'is_featured' => 'boolean',
        'attributes' => 'array',
        'price' => 'float',
        'original_price' => 'float',
        'pre_order_price' => 'float',
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
            ->withTimestamps()
            ->orderByPivot('id', 'desc');
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

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategoryModel::class, 'categories_products', 'product_id', 'product_category_id');
    }

    public function campaignProducts(): HasMany
    {
        return $this->hasMany(DiscountCampaignProductModel::class, 'product_id');
    }

    public function getThumbAttribute(): ?string
    {
        return $this->defaultFile->first()?->url;
    }
}
