<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        app(\App\Http\Controllers\MembershipController::class)->expireOldMemberships();
    })->daily();

    // Lounge hourly reminder + 10-min grace warning
    $schedule->call(function () {
        $sessions = \App\Models\LoungeSession::with('user')
            ->where('status', 'active')
            ->whereNotNull('user_id')
            ->get();

        foreach ($sessions as $session) {
            if (!$session->user || !$session->user->push_token) continue;

            $elapsedMinutes = (int) \Carbon\Carbon::parse($session->checked_in_at)->diffInMinutes(now());
            $minutesIntoCurrentHour = $elapsedMinutes % 60;

            // Hourly reminder (fires at the 0-minute mark of each new hour, after the first)
            if ($minutesIntoCurrentHour === 0 && $elapsedMinutes > 0) {
                $hours = (int) ($elapsedMinutes / 60);
                \App\Services\PushNotificationService::send(
                    $session->user->push_token,
                    'Still in the Lounge? ☕',
                    "You've been here for {$hours} hour(s). Just a heads-up!"
                );
            }

            // 10-minute grace warning (fires at the 50-minute mark of each hour)
            if ($minutesIntoCurrentHour === 50 && !$session->is_free) {
                \App\Services\PushNotificationService::send(
                    $session->user->push_token,
                    '⏰ Heads-up — 10 Minutes Left',
                    'You have 10 minutes before the next hour is charged. Leave before the hour to avoid extra billing. Note: checkout may take 1–3 minutes to process.'
                );
            }
        }
    })->everyMinute();
}

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
