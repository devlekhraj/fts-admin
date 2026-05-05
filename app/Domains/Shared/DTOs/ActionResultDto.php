<?php

declare(strict_types=1);

namespace App\Domains\Shared\DTOs;

final class ActionResultDto
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly mixed $data = null,
    ) {}
}
