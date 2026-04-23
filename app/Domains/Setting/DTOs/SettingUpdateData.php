<?php

declare(strict_types=1);

namespace App\Domains\Setting\DTOs;

final class SettingUpdateData
{
    public function __construct(
        public readonly string $module,
        public readonly array $settings,
    ) {
    }

    public static function fromArray(string $module, array $data): self
    {
        return new self(
            module: $module,
            settings: is_array($data['settings'] ?? null) ? $data['settings'] : [],
        );
    }
}

