<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Exchange\Services\ExchangeNotifier;
use Illuminate\Console\Command;

final class NotifyNewExchangeRequestsCommand extends Command
{
    protected $signature = 'exchange:notify-team';

    protected $description = 'Email the team about new exchange requests it has not been told about yet';

    public function handle(ExchangeNotifier $notifier): int
    {
        $count = $notifier->notifyTeamOfNewRequests();
        if ($count > 0) {
            $this->info("Announced {$count} new exchange request(s).");
        }

        return self::SUCCESS;
    }
}
