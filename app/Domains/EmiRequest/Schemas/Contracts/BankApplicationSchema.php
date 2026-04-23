<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Schemas\Contracts;

use App\Support\Exceptions\DomainException;

interface BankApplicationSchema
{
    public function bankCode(): string;

    /** @throws DomainException */
    public function validate(array $payload): void;

    /** Returns a strongly-typed data object (per bank). */
    public function toData(array $payload): object;
}

