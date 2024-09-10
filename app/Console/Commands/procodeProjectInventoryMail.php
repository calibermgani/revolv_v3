<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
class procodeProjectInventoryMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procode Project Inventory Mail';

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
        Log::info('Procode Project Inventory Mail Cron started.');
        try {
            $controller = app(\App\Http\Controllers\ProjectController::class);
            $controller->procodeProjectInventoryRecords();
            Log::info('Procode Project Inventory Mail Cron finished successfully.');
        } catch (\Exception $e) {
            Log::error('Procode Project Inventory Mail Cron failed: ' . $e->getMessage());
        }
        $this->info('Procode Project Inventory Mail worked successfully.');
    }
}
