<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Domain\ValueObjects;

use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;

final class PasswordHash
{
    public function __construct(private string $value)
    {
        $value = trim($value);
        if ($value === '') {
            throw new FieldValidationException('password', 'Password hash cannot be empty.');
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
