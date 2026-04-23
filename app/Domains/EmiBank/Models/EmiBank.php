<?php

declare(strict_types=1);

namespace App\Domains\EmiBank\Models;

use App\Support\Eloquent\BaseModel;

final class EmiBank extends BaseModel
{
    protected $table = 'emi_banks';

    protected $casts = [
        'tenures' => 'array',
    ];
}
