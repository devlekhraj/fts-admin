<?php

declare(strict_types=1);

namespace App\Domains\Page\Actions;

use App\Domains\Page\DTOs\PageUpsertData;
use App\Domains\Page\Models\Page;
use App\Support\Exceptions\FieldValidationException;

final class PageUpsertAction
{
    public function execute(PageUpsertData $data, ?int $id = null): Page
    {
        $slugExists = Page::query()
            ->where('slug', $data->slug)
            ->when($id, fn ($query) => $query->where('id', '!=', $id))
            ->exists();

        if ($slugExists) {
            throw new FieldValidationException('slug', 'Slug already exists.');
        }

        $payload = $data->toPayload();

        if ($id) {
            $page = Page::query()->find($id);
            if (! $page) {
                throw new FieldValidationException('id', 'Page not found.');
            }

            $page->fill($payload);
            $page->save();

            return $page;
        }

        return Page::query()->create($payload);
    }
}
