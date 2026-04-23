<?php

declare(strict_types=1);

namespace App\Domains\Setting\Actions;

use App\Domains\Setting\DTOs\SettingUpdateData;
use App\Domains\Setting\Models\Setting;

final class SettingUpdateAction
{
    public function execute(SettingUpdateData $data): Setting
    {
        $setting = Setting::query()
            ->where('module', $data->module)
            ->first();

        if (! $setting) {
            $setting = new Setting();
            $setting->module = $data->module;
        }

        $setting->settings = $data->settings;
        $setting->save();

        return $setting;
    }
}

