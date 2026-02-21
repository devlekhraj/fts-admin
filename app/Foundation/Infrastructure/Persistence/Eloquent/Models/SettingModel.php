<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class SettingModel extends BaseModel
{
    // TODO: Define table, fillable, casts, relations.
    protected $table = 'settings';

    protected $casts = [
        'settings' => 'array',
    ];
}
