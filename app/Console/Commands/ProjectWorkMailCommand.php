<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Log;

class ProjectWorkMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:workmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Project Work Mail';

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
        Log::info('Project Work Mail Cron started.');
        try {
            $controller = app(\App\Http\Controllers\ProjectController::class);
            $controller->projectWorkMail();
            Log::info('Project Work Mail Cron finished successfully.');
        } catch (\Exception $e) {
            Log::error('Project Work Mail Cron failed: ' . $e->getMessage());
        }
        $this->info('Project Work Mail Cron worked successfully.');
    }
}
