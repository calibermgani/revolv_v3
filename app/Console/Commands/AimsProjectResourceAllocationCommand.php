<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AimsProjectResourceAllocationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:resourceallocation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Project Resource Allocation';

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
        Log::info('Project Resource Allocation Sync Cron started.');
        try {
            $controller = app(\App\Http\Controllers\ProjectDuTargetSyncController::class);
            $controller->syncProjectResourceAllocation();
            Log::info('Project Resource Allocation Sync Cron finished successfully.');
        } catch (\Exception $e) {
            Log::error('Project Resource Allocation Sync Cron failed: ' . $e->getMessage());
        }
        $this->info('Project Resource Allocation Sync Cron worked successfully.');
    }
}
