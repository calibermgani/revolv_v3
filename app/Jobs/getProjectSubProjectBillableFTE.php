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

class getProjectSubProjectBillableFTE implements ShouldQueue
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
        // $data = app()->call('App\Http\Controllers\ProjectController@getProjectSubPrjBillableFTE', [
        //     'project_id' => $this->projectId,
        //     'sub_project_id' => $this->subProjectId,
        // ]);
        $data = app()->call('App\Http\Controllers\ProjectController@getProjectTotalDetailedInformation', [
            'project_id' => $this->projectId,
            'sub_project_id' => $this->subProjectId,
        ]);  
        Log::info("Processed Project Id and sub Project Id", ['projectId' => $this->projectId,'subProjectId' => $this->subProjectId]);
        $cacheKey = 'project_'.$this->projectId.$this->subProjectId.'BillableFTE' ;
        Cache::put($cacheKey, $data, now()->addMinutes(30));
    }
}
