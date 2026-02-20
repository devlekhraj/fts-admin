<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Application\Contracts;

interface PasswordHasher
{
    public function hash(string $plain): string;
}
