<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class EmiBankModel extends BaseModel
{
    protected $table = 'emi_banks';

    protected $casts = [
        'tenures' => 'array',
    ];
}
