<?php

declare(strict_types=1);

namespace App\Domains\Blog\DTOs;

final class BlogCreateData
{
    public function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly bool $status,
    ) {}

    public static function fromArray(array $data): self
    {
        $status = array_key_exists('status', $data) ? (bool) ($data['status'] ?? false) : true;

        return new self(
            title: (string) ($data['title'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            status: $status,
        );
    }
}

