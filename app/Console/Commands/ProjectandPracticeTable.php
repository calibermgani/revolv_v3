<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ProjectController;

class ProjectandPracticeTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:practice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Project Practice Table';

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
        $controller = new ProjectController();

        // Call the controller function
        $controller->clientTableUpdate();

        $this->info('Project and Practice tables data update cron executed successfully.');
    }
}
