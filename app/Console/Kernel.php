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
        // Генерируем новый отзыв каждые 5-10 минут (случайно)
        $schedule->command('reviews:generate')
            ->everyFiveMinutes()
            ->when(function () {
                // 50% шанс выполнения (эмуляция 5-10 минут)
                return rand(0, 1) === 1;
            });
        $schedule->command('telegram:check-proxy')
            ->everyFiveMinutes()
            ->withoutOverlapping();
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
