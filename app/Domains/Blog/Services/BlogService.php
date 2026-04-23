<?php

declare(strict_types=1);

namespace App\Domains\Blog\Services;

use App\Domains\Blog\Actions\BlogCreateAction;
use App\Domains\Blog\Actions\BlogDeleteAction;
use App\Domains\Blog\Actions\BlogDetailAction;
use App\Domains\Blog\Actions\BlogListAction;
use App\Domains\Blog\Actions\BlogUpdateAction;
use App\Domains\Blog\DTOs\BlogCreateData;
use App\Domains\Blog\DTOs\BlogUpdateData;
use App\Domains\Blog\Models\Blog;

final class BlogService
{
    public function __construct(
        private readonly BlogListAction $blogListAction,
        private readonly BlogDetailAction $blogDetailAction,
        private readonly BlogCreateAction $blogCreateAction,
        private readonly BlogUpdateAction $blogUpdateAction,
        private readonly BlogDeleteAction $blogDeleteAction,
    ) {}

    public function list(?string $search, int $perPageParam): array
    {
        return $this->blogListAction->execute($search, $perPageParam);
    }

    public function detail(string $id): Blog
    {
        return $this->blogDetailAction->execute($id);
    }

    public function create(BlogCreateData $data): Blog
    {
        return $this->blogCreateAction->execute($data);
    }

    public function update(string $id, BlogUpdateData $data): Blog
    {
        $blog = Blog::query()->findOrFail($id);

        return $this->blogUpdateAction->execute($blog, $data);
    }

    public function delete(string $id): void
    {
        $blog = Blog::query()->findOrFail($id);

        $this->blogDeleteAction->execute($blog);
    }
}

