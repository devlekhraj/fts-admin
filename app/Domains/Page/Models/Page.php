<?php

declare(strict_types=1);

namespace App\Domains\Page\Models;

use App\Support\Eloquent\BaseModel;

final class Page extends BaseModel
{
    protected $table = 'pages';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];

    protected $casts = [];
}
