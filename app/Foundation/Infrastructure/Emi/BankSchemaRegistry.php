<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Emi;

use App\Foundation\Domain\Emi\Banks\Contracts\BankApplicationSchema;
use InvalidArgumentException;

final class BankSchemaRegistry
{
    /** @var array<string, BankApplicationSchema> */
    private array $schemas = [];

    /** @param iterable<BankApplicationSchema> $schemas */
    public function __construct(iterable $schemas)
    {
        foreach ($schemas as $schema) {
            $this->schemas[strtoupper($schema->bankCode())] = $schema;
        }
    }

    public function get(string $bankCode): BankApplicationSchema
    {
        $normalized = strtoupper($bankCode);

        if (!isset($this->schemas[$normalized])) {
            throw new InvalidArgumentException("No schema registered for bank code '{$bankCode}'.");
        }

        return $this->schemas[$normalized];
    }
}

