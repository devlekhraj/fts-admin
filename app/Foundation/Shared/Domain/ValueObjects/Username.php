<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Domain\ValueObjects;

use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;

final class Username
{
    public function __construct(private string $value)
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > 255) {
            throw new FieldValidationException('username', 'Invalid username.');
        }
        if (preg_match('/\s/', $value)) {
            throw new FieldValidationException('username', 'Username must not contain spaces.');
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
