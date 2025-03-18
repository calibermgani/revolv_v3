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

class GetArResourceName implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $projectId;
    public function __construct(string $projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cacheKey = 'project_'.$this->projectId.'arResourceName' ;
        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () {
            return app()->call('App\Http\Helper\Admin\Helpers@getArResourceName', [
                    'project_id' => $this->projectId
                ]); 
            });      
            try {
                shell_exec("chmod -R 777 " . storage_path());
               Cache::put($cacheKey, $data, now()->addMinutes(10));
            } catch (\Exception $e) {
                Log::error('Cache write failed in arResourceName', [
                    'error' => $e->getMessage(),
                    'cacheKey' => $cacheKey,
                ]);
            }
  
    }
}
