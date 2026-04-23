<?php

declare(strict_types=1);

namespace App\Domains\Admin\Providers;

use App\Domains\Admin\Events\AdminBasicUpdated;
use App\Domains\Admin\Events\AdminCreated;
use App\Domains\Admin\Events\AdminEmailUpdated;
use App\Domains\Admin\Events\AdminPasswordUpdated;
use App\Domains\Admin\Listeners\SendAdminBasicUpdatedMail;
use App\Domains\Admin\Listeners\SendAdminCreatedAuditMailToCreator;
use App\Domains\Admin\Listeners\SendAdminCreatedMail;
use App\Domains\Admin\Listeners\SendAdminEmailUpdatedMail;
use App\Domains\Admin\Listeners\SendAdminPasswordUpdatedMail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

final class AdminEventServiceProvider extends EventServiceProvider
{
    protected $listen = [
        AdminCreated::class => [
            SendAdminCreatedMail::class,
            SendAdminCreatedAuditMailToCreator::class,
        ],
        AdminEmailUpdated::class => [
            SendAdminEmailUpdatedMail::class,
        ],
        AdminPasswordUpdated::class => [
            SendAdminPasswordUpdatedMail::class,
        ],
        AdminBasicUpdated::class => [
            SendAdminBasicUpdatedMail::class,
        ],
    ];
}
