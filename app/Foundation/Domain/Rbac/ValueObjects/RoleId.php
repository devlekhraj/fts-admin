<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Rbac\ValueObjects;

use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;

final class RoleId
{
    public function __construct(private int $value)
    {
        if ($value <= 0) {
            throw new FieldValidationException('role_id', 'Role id must be positive.');
        }

        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }
}
