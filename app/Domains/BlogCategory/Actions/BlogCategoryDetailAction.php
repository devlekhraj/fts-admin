<?php

declare(strict_types=1);

namespace App\Domains\BlogCategory\Actions;

use App\Domains\BlogCategory\Models\BlogCategory;

final class BlogCategoryDetailAction
{
    public function execute(string $id): BlogCategory
    {
        return BlogCategory::query()
            ->with(['defaultFile', 'files'])
            ->findOrFail($id);
    }
}

