<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Emails the team about new exchange (trade-in) requests. Needs the server cron entry for `schedule:run`.
Schedule::command('exchange:notify-team')->everyMinute()->withoutOverlapping();
