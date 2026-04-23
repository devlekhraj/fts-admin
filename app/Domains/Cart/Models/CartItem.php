<?php

declare(strict_types=1);

namespace App\Domains\Cart\Models;

use App\Domains\Product\Models\Product;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CartItem extends BaseModel
{
    protected $table = 'cart_items';


    protected $casts = [
        'product_attributes' => 'array'
    ];
    
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
