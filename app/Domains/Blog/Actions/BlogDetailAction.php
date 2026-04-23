<?php

declare(strict_types=1);

namespace App\Domains\Blog\Actions;

use App\Domains\Blog\Models\Blog;

final class BlogDetailAction
{
    public function execute(string $id): Blog
    {
        return Blog::query()
            ->with(['defaultFile', 'files', 'category'])
            ->findOrFail($id);
    }
}

