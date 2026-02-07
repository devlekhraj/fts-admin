<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Application\Contracts;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
