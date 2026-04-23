<?php

declare(strict_types=1);

namespace App\Domains\BlogCategory\Services;

use App\Domains\BlogCategory\Actions\BlogCategoryCreateAction;
use App\Domains\BlogCategory\Actions\BlogCategoryDeleteAction;
use App\Domains\BlogCategory\Actions\BlogCategoryDetailAction;
use App\Domains\BlogCategory\Actions\BlogCategoryListAction;
use App\Domains\BlogCategory\Actions\BlogCategoryUpdateAction;
use App\Domains\BlogCategory\DTOs\BlogCategoryCreateData;
use App\Domains\BlogCategory\DTOs\BlogCategoryUpdateData;
use App\Domains\BlogCategory\Models\BlogCategory;

final class BlogCategoryService
{
    public function __construct(
        private readonly BlogCategoryListAction $blogCategoryListAction,
        private readonly BlogCategoryDetailAction $blogCategoryDetailAction,
        private readonly BlogCategoryCreateAction $blogCategoryCreateAction,
        private readonly BlogCategoryUpdateAction $blogCategoryUpdateAction,
        private readonly BlogCategoryDeleteAction $blogCategoryDeleteAction,
    ) {}

    public function list(?string $search, int $perPageParam): array
    {
        return $this->blogCategoryListAction->execute($search, $perPageParam);
    }

    public function detail(string $id): BlogCategory
    {
        return $this->blogCategoryDetailAction->execute($id);
    }

    public function create(BlogCategoryCreateData $data): BlogCategory
    {
        return $this->blogCategoryCreateAction->execute($data);
    }

    public function update(string $id, BlogCategoryUpdateData $data): BlogCategory
    {
        $category = BlogCategory::query()->findOrFail($id);

        return $this->blogCategoryUpdateAction->execute($category, $data);
    }

    public function delete(string $id): void
    {
        $category = BlogCategory::query()->findOrFail($id);

        $this->blogCategoryDeleteAction->execute($category);
    }
}

