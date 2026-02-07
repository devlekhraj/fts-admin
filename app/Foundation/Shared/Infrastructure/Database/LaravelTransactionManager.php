<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Infrastructure\Database;

use App\Foundation\Shared\Application\Contracts\TransactionManager;
use Illuminate\Support\Facades\DB;

class LaravelTransactionManager implements TransactionManager
{
    public function begin(): void
    {
        DB::beginTransaction();
    }

    public function commit(): void
    {
        DB::commit();
    }

    public function rollBack(): void
    {
        DB::rollBack();
    }
}
