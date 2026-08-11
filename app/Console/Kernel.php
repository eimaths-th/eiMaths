<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('affiliate:release-commissions')
            ->everyMinute()
            ->withoutOverlapping()
            ->when(fn (): bool => (bool) config('affiliate.features.commission_release', false));
        $schedule->command('affiliate:reconcile-wallets')
            ->dailyAt('01:30')
            ->withoutOverlapping()
            ->when(fn (): bool => (bool) config('affiliate.features.wallet_withdrawals', false));
        $schedule->command('affiliate:prune-click-events')
            ->dailyAt('02:15')
            ->withoutOverlapping()
            ->when(fn (): bool => (bool) config('affiliate.features.click_tracking', false));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
        /* $this->commands([
            \App\Console\Commands\RunFreshSeeders::class,
        ]); */
    }
}
