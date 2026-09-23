<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AimsUserSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:aimsusers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync AIMS active users into aims_users';

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
        Log::info('AIMS Users Sync Cron started.');
        try {
            $controller = app(\App\Http\Controllers\AimsUserSyncController::class);
            $controller->syncAimsUsers();
            Log::info('AIMS Users Sync Cron finished successfully.');
        } catch (\Exception $e) {
            Log::error('AIMS Users Sync Cron failed: ' . $e->getMessage());
        }
        $this->info('AIMS Users Sync Cron worked successfully.');
    }
}
