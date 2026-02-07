<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Blog\ValueObjects;

final class BlogPostId
{
    public function __construct(private string $value)
    {
        // TODO: Validate value.
    }

    public function value(): string
    {
        return $this->value;
    }
}
