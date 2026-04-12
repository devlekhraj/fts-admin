<?php

declare(strict_types=1);

namespace App\Foundation\Application\Page\Handlers;

use App\Foundation\Application\Page\DTO\PageDto;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\PageModel;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;

final class PageStoreHandler
{
    public function handle(PageDto $dto, ?int $id = null): PageModel
    {
        $slugExists = PageModel::query()
            ->where('slug', $dto->slug)
            ->when($id, fn($query) => $query->where('id', '!=', $id))
            ->exists();

        if ($slugExists) {
            throw new FieldValidationException('slug', 'Slug already exists.');
        }

        $payload = [
            'title' => $dto->title,
            'slug' => $dto->slug,
            'excerpt' => $dto->excerpt,
            'content' => $dto->content ?? '',
            'meta_title' => $dto->metaTitle ?? '',
            'meta_keywords' => $dto->metaKeywords ?? '',
            'meta_description' => $dto->metaDescription ?? '',
        ];

        if ($id) {
            $page = PageModel::query()->find($id);
            if (!$page) {
                throw new FieldValidationException('id', 'Page not found.');
            }

            $page->fill($payload);
            $page->save();

            return $page;
        }

        return PageModel::create($payload);
    }
}
