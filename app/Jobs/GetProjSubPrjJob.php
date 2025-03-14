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

class GetProjSubPrjJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $projectId;
    public string $subProjectId;
    public function __construct(string $projectId,string $subProjectId)
    {
        $this->projectId = $projectId;
        $this->subProjectId = $subProjectId;
    }
   
    public function handle()
    {
        $cacheKey = 'project_'.$this->projectId.$this->subProjectId.'totalDetails';

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () {
            return app()->call('App\Http\Controllers\ProjectController@getProjectTotalDetailedInformationForHourlyWeb', [
                'project_id' => $this->projectId,
                'sub_project_id' => $this->subProjectId,
            ]);
        });
        try {
            Cache::lock($cacheKey)->get(function () use ($cacheKey, $data) {
                Cache::put($cacheKey, $data, now()->addMinutes(10));
            });            
        } catch (\Exception $e) {
            Log::error('Cache write failed in hourly web', [
                'error' => $e->getMessage(),
                'cacheKey' => $cacheKey,
            ]);
        }
    }
}
