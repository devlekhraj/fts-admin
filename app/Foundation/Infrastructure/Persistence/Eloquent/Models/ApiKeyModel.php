<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class ApiKeyModel extends BaseModel
{
    protected $table = 'api_keys';

    protected $fillable = [
        'vendor_id',
        'host',
        'test_public_key',
        'test_secret_key',
        'live_public_key',
        'live_secret_key',
        'mode',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
