<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class OrderItemModel extends BaseModel
{
    protected $table = 'order_items';

}
