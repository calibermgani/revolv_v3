<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
              Commands\ProjectandPracticeTable::class,
              Commands\ProjectWorkMailCommand::class,
              Commands\procodeProjectOnHoldMail::class,
              Commands\ProjectDuTargetSyncCommand::class,
              Commands\AimsProjectResourceAllocationCommand::class,
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
        $schedule->command('project:practice')->dailyAt('10:00');
        $schedule->command('project:holdrecords')->dailyAt('10:00');
        $schedule->command('project:inventory')->hourly();
        $schedule->command('project:hourlymail')->hourly();
        $schedule->command('project:callchartworklogs')->dailyAt('09:00');
        $schedule->command('project:workmail')->dailyAt('09:00');
        // $schedule->command('project:duonehourlymail')->hourly();
        $schedule->command('project:dutargetsync')->dailyAt('09:00');
         $schedule->command('project:resourceallocation')->dailyAt('15:00');
      

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
