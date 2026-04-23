<?php

declare(strict_types=1);

namespace App\Domains\Page\Actions;

use App\Domains\Page\Models\Page;

final class PageShowAction
{
    public function execute(string $id): Page
    {
        return Page::query()->findOrFail($id);
    }
}

