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
        $cacheKey = 'clients_on_user' ;
        $data = Cache::remember($cacheKey, now()->addMinutes(2), function () {
            return app()->call('App\Http\Helper\Admin\Helpers@getProjects', [      
                'userId' => $this->userId  
            ]); 
         });
         try {
            shell_exec("chmod -R 777 " . storage_path()); 
             Cache::put($cacheKey, $data, now()->addMinutes(2));
        } catch (\Exception $e) {
            Log::error('Cache write failed in clients_on_user', [
                'error' => $e->getMessage(),
                'cacheKey' => $cacheKey,
            ]);
        }
  
    }
}
