<?php

declare(strict_types=1);

namespace App\Domains\Blog\Actions;

use App\Domains\Blog\DTOs\BlogUpdateData;
use App\Domains\Blog\Models\Blog;

final class BlogUpdateAction
{
    public function execute(Blog $blog, BlogUpdateData $data): Blog
    {
        $blog->update($data->attributes);

        return $blog->refresh();
    }
}

