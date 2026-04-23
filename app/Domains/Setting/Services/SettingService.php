<?php

declare(strict_types=1);

namespace App\Domains\Setting\Services;

use App\Domains\Setting\Actions\SettingListAction;
use App\Domains\Setting\Actions\SettingUpdateAction;
use App\Domains\Setting\DTOs\SettingUpdateData;
use App\Domains\Setting\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

final class SettingService
{
    public function __construct(
        private readonly SettingListAction $listAction,
        private readonly SettingUpdateAction $updateAction,
    ) {
    }

    public function list(): Collection
    {
        return $this->listAction->execute();
    }

    public function update(SettingUpdateData $data): Setting
    {
        return $this->updateAction->execute($data);
    }
}

