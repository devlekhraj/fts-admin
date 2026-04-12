<?php

declare(strict_types=1);

namespace App\Foundation\Application\Page\DTO;

use Illuminate\Http\Request;

final class PageDto
{
    public function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $content = null,
        public readonly ?string $excerpt = null,
        public readonly ?string $metaTitle = null,
        public readonly ?string $metaKeywords = null,
        public readonly ?string $metaDescription = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = method_exists($request, 'validated') ? $request->validated() : $request->all();

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

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'meta_title' => $this->metaTitle,
            'meta_keywords' => $this->metaKeywords,
            'meta_description' => $this->metaDescription,
        ];
    }
}
