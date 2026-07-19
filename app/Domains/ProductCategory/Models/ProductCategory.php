<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Models;

use App\Domains\Product\Models\Product;
use App\Domains\File\Models\File;
use App\Domains\Faq\Models\Faq;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ProductCategory extends BaseModel
{
    use SoftDeletes;

    protected $table = 'product_categories';

    protected $casts = [
        'status' => 'boolean',
        'parent_id' => 'integer',
        'order' => 'integer',
        'featured' => 'boolean',
        'parent_tree' => 'array',
    ];

    protected $fillable = [
        'parent_id',
        'title',
        'seq_no',
        'slug',
        'status',
        'parent_tree',
        'order',
        'featured',
        'description',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'custom_code',
    ];

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable', 'type', 'type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'categories_products', 'product_category_id', 'product_id');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'product_categories')
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->withTimestamps();
    }

    public function banners(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'product_categories')
            ->whereRaw("LOWER(REPLACE(CAST(JSON_EXTRACT(file_usages.meta, '$.type') AS CHAR), '\"', '')) = 'banner'")
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->withTimestamps();
    }

    public function defaultFile(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'product_categories')
            ->where(static function ($builder) {
                $builder->whereRaw("JSON_EXTRACT(file_usages.meta, '$.is_default') = 1")
                    ->orWhereRaw("LOWER(REPLACE(CAST(JSON_EXTRACT(file_usages.meta, '$.is_default') AS CHAR), '\"', '')) = 'true'");
            })
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->orderByPivot('id', 'asc');
    }
}
