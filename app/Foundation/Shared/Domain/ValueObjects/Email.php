<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Domain\ValueObjects;

use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;

final class Email
{
    public function __construct(private string $value)
    {
        $value = trim($value);
        if ($value === '' || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new FieldValidationException('email', 'Invalid email address.');
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
