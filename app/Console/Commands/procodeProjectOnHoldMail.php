<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
class procodeProjectOnHoldMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:holdrecords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procode Project On Hold Mail';

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
        Log::info('Procode Project On Hold Mail Cron started.');
        try {
            $controller = app(\App\Http\Controllers\ProjectController::class);
            $controller->procodeProjectOnHoldMail();
            Log::info('Procode Project On Hold Mail Cron finished successfully.');
        } catch (\Exception $e) {
            Log::error('Procode Project On Hold Mail Cron failed: ' . $e->getMessage());
        }
        $this->info('Procode Project On Hold Mail worked successfully.');
    }
}
