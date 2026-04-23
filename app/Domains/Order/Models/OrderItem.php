<?php

declare(strict_types=1);

namespace App\Domains\Order\Models;

use App\Domains\Product\Models\Product;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderItem extends BaseModel
{
    protected $table = 'order_items';

    protected $casts = [
        'product_attributes' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}

