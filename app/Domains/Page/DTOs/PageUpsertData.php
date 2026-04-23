<?php

declare(strict_types=1);

namespace App\Domains\Page\DTOs;

final class PageUpsertData
{
    public function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $content = null,
        public readonly ?string $excerpt = null,
        public readonly ?string $metaTitle = null,
        public readonly ?string $metaKeywords = null,
        public readonly ?string $metaDescription = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: (string) ($data['title'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            content: $data['content'] ?? null,
            excerpt: $data['excerpt'] ?? null,
            metaTitle: $data['meta_title'] ?? null,
            metaKeywords: $data['meta_keywords'] ?? null,
            metaDescription: $data['meta_description'] ?? null,
        );
    }

    public function toPayload(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content ?? '',
            'meta_title' => $this->metaTitle ?? '',
            'meta_keywords' => $this->metaKeywords ?? '',
            'meta_description' => $this->metaDescription ?? '',
        ];
    }
}

