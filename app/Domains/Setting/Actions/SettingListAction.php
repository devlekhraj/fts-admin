<?php

declare(strict_types=1);

namespace App\Domains\Setting\Actions;

use App\Domains\Setting\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

final class SettingListAction
{
    public function execute(): Collection
    {
        return Setting::query()->get();
    }
}

