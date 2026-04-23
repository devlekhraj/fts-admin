<?php

declare(strict_types=1);

namespace App\Domains\Page\Actions;

use App\Domains\Page\Models\Page;

final class PageDeleteAction
{
    public function execute(Page $page): void
    {
        $page->delete();
    }
}

