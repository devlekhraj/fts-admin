<?php

declare(strict_types=1);

namespace App\Domains\Developer\Actions;

use App\Domains\Developer\Models\ApiKey;
use Illuminate\Database\Eloquent\Collection;

final class ApiKeyListAction
{
    public function execute(): Collection
    {
        return ApiKey::query()
            ->latest('id')
            ->get();
    }
}

