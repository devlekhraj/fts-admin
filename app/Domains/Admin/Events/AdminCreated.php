<?php

declare(strict_types=1);

namespace App\Domains\Admin\Events;

use App\Domains\Admin\Models\Admin;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AdminCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Admin $admin,
        public readonly string $plaintextPassword,
        public readonly ?Admin $createdBy = null,
    ) {}
}
