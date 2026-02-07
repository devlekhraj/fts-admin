<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Application\Contracts;

interface IdGenerator
{
    public function generate(): string;
}
