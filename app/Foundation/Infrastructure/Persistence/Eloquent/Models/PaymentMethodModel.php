<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class PaymentMethodModel extends BaseModel
{
    protected $table = 'payment_methods';

    protected $casts = [
        'status' => 'boolean',
        'is_international' => 'boolean',
        'test_mode' => 'boolean',
    ];

   
}
