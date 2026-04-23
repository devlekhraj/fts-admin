<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

final class FieldValidationException extends DomainException
{
    public function __construct(
        private readonly string $field,
        string $message
    ) {
        parent::__construct($message);
    }

    public function field(): string
    {
        return $this->field;
    }
}

