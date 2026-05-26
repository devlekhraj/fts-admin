<?php

declare(strict_types=1);

namespace App\Domains\Product\Models;

use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductGift extends BaseModel
{
    protected $table = 'product_gifts';

    protected $fillable = [
        'product_id',
        'gift_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function gift(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'gift_id');
    }
}
