<?php

declare(strict_types=1);

namespace App\Domains\File\DTOs;

final class ListFilesData
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?string $search,
        public readonly ?string $tag,
    ) {}

    public static function fromArray(array $validated): self
    {
        $search = isset($validated['search']) ? trim((string) $validated['search']) : null;
        $tag = isset($validated['tag']) ? trim((string) $validated['tag']) : null;

        return new self(
            page: (int) ($validated['page'] ?? 1),
            perPage: (int) ($validated['per_page'] ?? 24),
            search: is_string($search) && $search !== '' ? $search : null,
            tag: is_string($tag) && $tag !== '' ? $tag : null,
        );
    }
}

