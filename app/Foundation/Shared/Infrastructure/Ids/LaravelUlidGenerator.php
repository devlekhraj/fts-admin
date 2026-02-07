<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Infrastructure\Ids;

use App\Foundation\Shared\Application\Contracts\IdGenerator;

class LaravelUlidGenerator implements IdGenerator
{
    public function generate(): string
    {
        // TODO: Generate ULID using Laravel helpers.
        return (string) \Illuminate\Support\Str::ulid();
    }
}
