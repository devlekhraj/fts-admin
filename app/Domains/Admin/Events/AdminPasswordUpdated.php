<?php

declare(strict_types=1);

namespace App\Domains\Admin\Events;

use App\Domains\Admin\Models\Admin;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AdminPasswordUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Admin $admin,
    ) {}
}
