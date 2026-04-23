<?php

declare(strict_types=1);

namespace App\Domains\User\Models;

use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ShippingAddress extends BaseModel
{
    protected $table = 'user_shipping_addresses';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

