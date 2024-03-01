<?php

namespace App\Listeners\Webhook;

use App\Events\SpeedtestCompleted;
use App\Settings\GeneralSettings;
use App\Settings\NotificationSettings;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookServer\WebhookCall;

class SendSpeedtestCompletedNotification
{
    /**
     * Handle the event.
     */
    public function handle(SpeedtestCompleted $event): void
    {
        $generalSettings = new GeneralSettings();

        $notificationSettings = new NotificationSettings();

        if (! $notificationSettings->webhook_enabled) {
            return;
        }

        if (! $notificationSettings->webhook_on_speedtest_run) {
            return;
        }

        if (! count($notificationSettings->webhook_urls)) {
            Log::warning('Webhook urls not found, check webhook notification channel settings.');

            return;
        }

        foreach ($notificationSettings->webhook_urls as $url) {
            WebhookCall::create()
                ->url($url['url'])
                ->payload([
                    'result_id' => $event->result->id,
                    'site_name' => $generalSettings->site_name,
                    'ping' => $event->result->ping,
                    'download' => $event->result->downloadBits,
                    'upload' => $event->result->uploadBits,
                ])
                ->doNotSign()
                ->dispatch();
        }
    }
}