<?php

declare(strict_types=1);

namespace App\Domains\Wishlist\Models;

use App\Domains\Product\Models\Product;
use App\Domains\User\Models\User;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Wishlist extends BaseModel
{
    protected $table = 'wishlists';

    protected $fillable = ['user_id', 'product_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
