<?php

namespace App\Console\Commands;

use App\Services\EmailCampaignDispatcher;
use Illuminate\Console\Command;

class DispatchEmailCampaigns extends Command
{
    protected $signature = 'email-campaigns:dispatch';

    protected $description = 'Dispatch queued campaign emails within the daily sending limit';

    public function handle(EmailCampaignDispatcher $dispatcher): int
    {
        $this->info($dispatcher->dispatchAvailable().' campaign emails dispatched.');

        return self::SUCCESS;
    }
}
