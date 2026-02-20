<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Auth\Hashing;

use App\Foundation\Shared\Application\Contracts\PasswordHasher;

final class LaravelPasswordHasher implements PasswordHasher
{
    public function hash(string $plain): string
    {
        return bcrypt($plain);
    }
}
