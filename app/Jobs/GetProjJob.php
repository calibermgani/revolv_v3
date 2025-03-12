<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GetProjJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public string $userId;
    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $cacheKey = 'clients_on_user' ;
        // $data = Cache::remember($cacheKey, now()->addMinutes(2), function () {
        //     return app()->call('App\Http\Helper\Admin\Helpers@getProjects', [      
        //         'userId' => $this->userId  
        //     ]); 
        //  });
     
        // Cache::put($cacheKey, $data, now()->addMinutes(2));
 Log::info('prj job');
        $cacheKey = 'clients_on_user';

    $data = Cache::remember($cacheKey, now()->addMinutes(2), function () {
        return app()->call('App\Http\Helper\Admin\Helpers@getProjects', [
            'userId' => $this->userId  
        ]); 
    });

    Cache::put($cacheKey, $data, now()->addMinutes(2));

    // Define the folder path
    $folderPath = storage_path('framework/cache/data');

    // Check if the folder exists, if not, create it
    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    // Set permissions for the folder
    shell_exec("sudo chmod -R 777 {$folderPath} 2>&1");

    Log::info('Folder permission set to 777 for: ' . $folderPath);
  
    }
}
