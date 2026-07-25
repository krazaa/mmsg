<?php

namespace App\Services;

use App\Jobs\SendEmailCampaignRecipient;
use App\Models\EmailCampaignRecipient;
use Illuminate\Support\Facades\DB;

class EmailCampaignDispatcher
{
    public function dispatchAvailable(): int
    {
        $limit = max(1, (int) config('mail.bulk_daily_limit', 300));
        $reservedToday = EmailCampaignRecipient::whereDate('dispatched_at', today())->count();
        $available = max(0, $limit - $reservedToday);
        if ($available === 0) {
            return 0;
        }

        $ids = DB::transaction(function () use ($available) {
            $recipients = EmailCampaignRecipient::where('status', 'queued')
                ->whereNull('dispatched_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->limit($available)
                ->get();

            EmailCampaignRecipient::whereKey($recipients->modelKeys())->update([
                'status' => 'dispatched',
                'dispatched_at' => now(),
            ]);

            return $recipients->modelKeys();
        });

        foreach ($ids as $id) {
            SendEmailCampaignRecipient::dispatch($id);
        }

        return count($ids);
    }
}
