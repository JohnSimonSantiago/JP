<?php

namespace App\Console\Commands;

use App\Models\LoungeSession;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyConsumableTime extends Command
{
    protected $signature = 'lounge:notify-consumable';
    protected $description = 'Warn consumable-time members at 10, 5, and 1 minute remaining';

    public function handle()
    {
        $thresholds = [10, 5, 1];

        $sessions = LoungeSession::with('user')
            ->where('status', 'active')
            ->where('billing_mode', 'consumable')
            ->get();

        foreach ($sessions as $session) {
            $user = $session->user;
            if (!$user || !$user->push_token) continue;

            $elapsed   = Carbon::parse($session->checked_in_at)->diffInMinutes(now());
            $remaining = ($user->consumable_minutes ?? 0) - $elapsed;

            $sent = $session->notified_thresholds ?? [];

            foreach ($thresholds as $mark) {
                if ($remaining > $mark) continue;
                if (in_array($mark, $sent)) continue;

                PushNotificationService::send(
                    $user->push_token,
                    $mark === 1 ? 'Time almost up' : "{$mark} minutes left",
                    $mark === 1
                        ? 'You have about 1 minute of lounge time left. Please start wrapping up.'
                        : "You have about {$mark} minutes of lounge time left.",
                    ['type' => 'consumable_warning', 'minutes_left' => $mark]
                );

                $sent[] = $mark;
            }

            if ($sent !== ($session->notified_thresholds ?? [])) {
                $session->update(['notified_thresholds' => $sent]);
            }
        }

        return self::SUCCESS;
    }
}