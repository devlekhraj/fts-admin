<?php

declare(strict_types=1);

namespace App\Domains\Setting\Models;

use App\Support\Eloquent\BaseModel;

final class Setting extends BaseModel
{
    // TODO: Define table, fillable, casts, relations.
    protected $table = 'settings';

    protected $casts = [
        'settings' => 'array',
    ];
}
