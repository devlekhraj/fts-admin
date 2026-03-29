<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItemModel extends BaseModel
{
    protected $table = 'cart_items';


    protected $casts = [
        'product_attributes' => 'array'
    ];
    
    public function cart(): BelongsTo
    {
        return $this->belongsTo(CartModel::class,'cart_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class);
    }
}
