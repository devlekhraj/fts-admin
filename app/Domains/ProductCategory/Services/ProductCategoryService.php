<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Services;

use App\Domains\ProductCategory\Actions\CategoryCreateAction;
use App\Domains\ProductCategory\Actions\CategoryDeleteAction;
use App\Domains\ProductCategory\Actions\CategoryDetailAction;
use App\Domains\ProductCategory\Actions\CategoryListAction;
use App\Domains\ProductCategory\Actions\CategoryUpdateAction;
use App\Domains\ProductCategory\DTOs\CategoryCreateData;
use App\Domains\ProductCategory\DTOs\CategoryUpdateData;
use App\Domains\ProductCategory\Models\ProductCategory;

final class ProductCategoryService
{
    public function __construct(
        private readonly CategoryListAction $categoryListAction,
        private readonly CategoryDetailAction $categoryDetailAction,
        private readonly CategoryCreateAction $categoryCreateAction,
        private readonly CategoryUpdateAction $categoryUpdateAction,
        private readonly CategoryDeleteAction $categoryDeleteAction,
    ) {}

    public function list(?string $search, int $perPageParam): array
    {
        return $this->categoryListAction->execute($search, $perPageParam);
    }

    public function detail(string $id): ProductCategory
    {
        return $this->categoryDetailAction->execute($id);
    }

    public function create(CategoryCreateData $data): ProductCategory
    {
        return $this->categoryCreateAction->execute($data);
    }

    public function update(string $id, CategoryUpdateData $data): ProductCategory
    {
        $category = ProductCategory::query()->findOrFail($id);

        return $this->categoryUpdateAction->execute($category, $data);
    }

    public function delete(string $id): void
    {
        $category = ProductCategory::query()->findOrFail($id);

        $this->categoryDeleteAction->execute($category);
    }
}
