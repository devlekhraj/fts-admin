<?php

declare(strict_types=1);

namespace App\Domains\Order\Models;

use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderReceipent extends BaseModel
{
    protected $table = 'order_receipents';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}

