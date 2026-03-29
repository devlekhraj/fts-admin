<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class PageModel extends BaseModel
{
    protected $table = 'pages';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'content',
        'meta',
    ];

    protected $casts = [
        'status' => 'boolean',
        'meta' => 'array',
    ];
}
