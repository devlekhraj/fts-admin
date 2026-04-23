<?php

declare(strict_types=1);

namespace App\Domains\Page\Services;

use App\Domains\Page\Actions\PageDeleteAction;
use App\Domains\Page\Actions\PageListAction;
use App\Domains\Page\Actions\PageShowAction;
use App\Domains\Page\Actions\PageUpsertAction;
use App\Domains\Page\DTOs\PageUpsertData;
use App\Domains\Page\Models\Page;

final class PageService
{
    public function __construct(
        private readonly PageListAction $listAction,
        private readonly PageShowAction $showAction,
        private readonly PageUpsertAction $upsertAction,
        private readonly PageDeleteAction $deleteAction,
    ) {
    }

    public function list(?string $search, int $perPageParam): array
    {
        return $this->listAction->execute($search, $perPageParam);
    }

    public function show(string $id): Page
    {
        return $this->showAction->execute($id);
    }

    public function upsert(PageUpsertData $data, ?int $id = null): Page
    {
        return $this->upsertAction->execute($data, $id);
    }

    public function delete(string $id): void
    {
        $page = Page::query()->findOrFail($id);
        $this->deleteAction->execute($page);
    }
}

