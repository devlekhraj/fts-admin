<?php

declare(strict_types=1);

namespace App\Domains\Developer\Actions;

use App\Domains\Developer\DTOs\ApiKeyUpdateData;
use App\Domains\Developer\Models\ApiKey;

final class ApiKeyUpdateAction
{
    public function execute(ApiKey $apiKey, ApiKeyUpdateData $data): ApiKey
    {
        $apiKey->fill($data->attributes);
        $apiKey->save();

        return $apiKey;
    }
}

