<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Application\DTO;


final class ActionResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
    ) {}
}
