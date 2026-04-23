<?php

declare(strict_types=1);

namespace App\Domains\Faq\Models;

use App\Domains\Product\Models\Product;
use App\Domains\ProductBrand\Models\ProductBrand;
use App\Domains\ProductCategory\Models\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Faq extends Model
{
    protected $table = 'faqs';

    protected $fillable = [
        'type',
        'type_id',
        'question',
        'answer',
    ];

    public function faqable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'type', 'type_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class, 'type_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'type_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'type_id');
    }
}
