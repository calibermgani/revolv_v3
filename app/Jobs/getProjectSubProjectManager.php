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

class getProjectSubProjectManager implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $projectId;
    public string $subProjectId;
    public function __construct(string $projectId,string $subProjectId)
    {
        $this->projectId = $projectId;
        $this->subProjectId = $subProjectId;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mgrData = app()->call('App\Http\Controllers\ProjectController@getProjectSubPrjManager', [
            'token' => '1a32e71a46317b9cc6feb7388238c95d',
            'project_id' => $this->projectId,
            'sub_project_id' => $this->subProjectId,
        ]); 
        $billbleFTEData = app()->call('App\Http\Controllers\ProjectController@getProjectSubPrjBillableFTE', [
            'token' => '1a32e71a46317b9cc6feb7388238c95d',
            'project_id' => $this->projectId,
            'sub_project_id' => $this->subProjectId,
        ]); 
        $slaTargetData = app()->call('App\Http\Controllers\ProjectController@getProjectTotalSlaTargetWeb', [
            'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'project_id' => $this->projectId,
                    'sub_project_id' =>$this->subProjectId,
        ]); 
        Log::info("Processed Project Id and sub Project Id", ['projectId' => $this->projectId,'subProjectId' => $this->subProjectId]);
        $mgrCacheKey = 'project_'.$this->projectId.$this->subProjectId.'Manager' ;    
        Cache::put($mgrCacheKey, $mgrData, now()->addMinutes(30));

        $billbleCacheKey = 'project_'.$this->projectId.$this->subProjectId.'BillableFTE' ;
        Cache::put($billbleCacheKey, $billbleFTEData, now()->addMinutes(30));

        $slaTargetCacheKey = 'project_'.$this->projectId.$this->subProjectId.'SLATarget' ;
        Cache::put($slaTargetCacheKey, $slaTargetData, now()->addMinutes(30));
    }
}
