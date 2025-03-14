<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class ProjectHourMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:hourlymail';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Project Hour Mail Cron';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('Project Hourly Mail Cron started.');
        try {
            $currentHour = Carbon::now()->hour;
            if ($currentHour >= 17 || $currentHour < 6) {                
                Log::info("Hourly task running at " . Carbon::now());
                $controller = app(\App\Http\Controllers\ProjectController::class);
                $controller->projectHourlyMail();
                Log::info('Project Hourly Mail Cron finished successfully.');
            } else {
                Log::info("Hourly task skipped at " . Carbon::now());
            }
        } catch (\Exception $e) {
            Log::error('Project Hourly Mail Cron failed: ' . $e->getMessage());
        }
        $this->info('Project Hourly Mail Cron worked successfully.');
    }
}
