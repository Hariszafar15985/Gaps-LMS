<?php

namespace App\Console;

use App\Console\Commands\StudentInactiveNotification;
use App\Console\Commands\StudyInActiveNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        StudentInactiveNotification::class,
        StudyInActiveNotification::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('StudentInactiveNotification', ['daily'])->dailyAt('23:00');
        $schedule->command('StudentInactiveNotification', ['weekly'])->weekly();
        $schedule->command('StudentInactiveNotification', ['monthly'])->monthly();
        $schedule->command('StudentInactiveNotification', ['fortnightly'])
            ->weeklyOn(5, '00:01')->when(function () {
                return (time() / 604800 % 2);
            });

        $schedule->command('StudyInActiveNotification', ['daily'])->dailyAt('23:59');
        $schedule->command('StudyInActiveNotification', ['weekly'])->weekly();
        $schedule->command('StudyInActiveNotification', ['monthly'])->monthly();
        $schedule->command('StudyInActiveNotification', ['fortnightly'])
            ->weeklyOn(5, '00:01')->when(function () {
                return (time() / 604800 % 2);
            });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
