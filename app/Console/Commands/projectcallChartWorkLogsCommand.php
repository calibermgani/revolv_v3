<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class projectcallChartWorkLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:callchartworklogs';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Project Call Chart Work Logs';

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
        Log::info('Project Call Chart Work Logs Cron started.');
        try {
            $controller = app(\App\Http\Controllers\ProjectController::class);
            $controller->projectCallChartWorkLogs();
            Log::info('Project Call Chart Work Logs Cron finished successfully.');
        } catch (\Exception $e) {
            Log::error('Project Call Chart Work Logs Cron failed: ' . $e->getMessage());
        }
        $this->info('Project Call Chart Work Logs Cron worked successfully.');
    }
}
