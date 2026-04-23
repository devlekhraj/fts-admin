<?php

declare(strict_types=1);

namespace App\Domains\Developer\Services;

use App\Domains\Developer\Actions\ApiKeyCreateAction;
use App\Domains\Developer\Actions\ApiKeyDeleteAction;
use App\Domains\Developer\Actions\ApiKeyListAction;
use App\Domains\Developer\Actions\ApiKeyUpdateAction;
use App\Domains\Developer\DTOs\ApiKeyCreateData;
use App\Domains\Developer\DTOs\ApiKeyUpdateData;
use App\Domains\Developer\Models\ApiKey;
use Illuminate\Database\Eloquent\Collection;

final class ApiKeyService
{
    public function __construct(
        private readonly ApiKeyListAction $listAction,
        private readonly ApiKeyCreateAction $createAction,
        private readonly ApiKeyUpdateAction $updateAction,
        private readonly ApiKeyDeleteAction $deleteAction,
    ) {
    }

    public function list(): Collection
    {
        return $this->listAction->execute();
    }

    public function create(ApiKeyCreateData $data): ApiKey
    {
        return $this->createAction->execute($data);
    }

    public function update(string $id, ApiKeyUpdateData $data): ApiKey
    {
        $apiKey = ApiKey::query()->findOrFail($id);

        return $this->updateAction->execute($apiKey, $data);
    }

    public function delete(string $id): void
    {
        $apiKey = ApiKey::query()->findOrFail($id);
        $this->deleteAction->execute($apiKey);
    }
}

