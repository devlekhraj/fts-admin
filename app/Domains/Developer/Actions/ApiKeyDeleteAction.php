<?php

declare(strict_types=1);

namespace App\Domains\Developer\Actions;

use App\Domains\Developer\Models\ApiKey;

final class ApiKeyDeleteAction
{
    public function execute(ApiKey $apiKey): void
    {
        $apiKey->delete();
    }
}

