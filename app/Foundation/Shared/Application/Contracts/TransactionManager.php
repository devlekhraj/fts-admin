<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Application\Contracts;

interface TransactionManager
{
    public function begin(): void;
    public function commit(): void;
    public function rollBack(): void;
}
