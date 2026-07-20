<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProjectDuTargetSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:dutargetsync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Project DU Target Sync';

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
          Log::info('Project DU Target Sync Cron started.');
        try {
            $controller = app(\App\Http\Controllers\ProjectDuTargetSyncController::class);
            $controller->syncProjectDuTargets();
            Log::info('Project DU Target Sync Cron finished successfully.');
        } catch (\Exception $e) {
            Log::error('Project DU Target Sync Cron failed: ' . $e->getMessage());
        }
        $this->info('Project DU Target Sync Cron worked successfully.');
    }
}
