<?php

declare(strict_types=1);

namespace App\Domains\Blog\Actions;

use App\Domains\Blog\DTOs\BlogUpdateData;
use App\Domains\Blog\Models\Blog;
use Illuminate\Support\Carbon;

final class BlogUpdateAction
{
    public function execute(Blog $blog, BlogUpdateData $data): Blog
    {
        $attributes = $data->attributes;

        if (array_key_exists('publish_date', $attributes)) {
            $attributes['publish_date'] = $this->normalizeDate($attributes['publish_date']);
        }

        $isActive = array_key_exists('status', $attributes)
            ? (bool) $attributes['status']
            : (bool) $blog->status;
        $publishDate = array_key_exists('publish_date', $attributes)
            ? $attributes['publish_date']
            : $blog->publish_date;

        // An active blog must always carry a publish date, otherwise the website cannot place it in the list.
        if ($isActive && $publishDate === null) {
            $attributes['publish_date'] = now()->toDateTimeString();
        }

        $blog->update($attributes);

        return $blog->refresh();
    }

    private function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value, config('app.timezone'))->format('Y-m-d H:i:s');
    }
}
