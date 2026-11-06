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
        public readonly ?array $schemaMarkup = null,
        public readonly bool $hasSchemaMarkup = false,
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
            schemaMarkup: $data['schema_markup'] ?? null,
            hasSchemaMarkup: array_key_exists('schema_markup', $data),
        );
    }

    public function toPayload(): array
    {
        $payload = [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content ?? '',
            'meta_title' => $this->metaTitle ?? '',
            'meta_keywords' => $this->metaKeywords ?? '',
            'meta_description' => $this->metaDescription ?? '',
        ];

        // Only touch the stored schema when the request sent it, so saving other page fields never wipes it.
        if ($this->hasSchemaMarkup) {
            $payload['schema_markup'] = $this->schemaMarkup;
        }

        return $payload;
    }
}

