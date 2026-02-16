<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Pdf\Registry;

use App\Foundation\Infrastructure\Pdf\Contracts\BankPdfGenerator;
use InvalidArgumentException;

final class BankPdfGeneratorRegistry
{
    /** @var list<BankPdfGenerator> */
    private array $generators;

    /** @param iterable<BankPdfGenerator> $generators */
    public function __construct(iterable $generators)
    {
        $this->generators = [];

        foreach ($generators as $generator) {
            $this->generators[] = $generator;
        }
    }

    public function for(string $bankCode): BankPdfGenerator
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($bankCode)) {
                return $generator;
            }
        }

        throw new InvalidArgumentException("No PDF generator registered for bank code '{$bankCode}'.");
    }
}

