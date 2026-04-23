<?php

declare(strict_types=1);

namespace App\Domains\Developer\Actions;

use App\Domains\Developer\DTOs\ApiKeyCreateData;
use App\Domains\Developer\Models\ApiKey;
use Illuminate\Support\Str;

final class ApiKeyCreateAction
{
    public function execute(ApiKeyCreateData $data): ApiKey
    {
        return ApiKey::query()->create([
            ...$data->attributes,
            'test_public_key' => Str::upper(Str::random(32)),
            'test_secret_key' => Str::upper(Str::random(48)),
            'live_public_key' => Str::upper(Str::random(32)),
            'live_secret_key' => Str::upper(Str::random(48)),
        ]);
    }
}

