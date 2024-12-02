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
class GetTotalARCountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public array $projectIds;
    public function __construct(array $projectIds)
    {
        $this->projectIds = $projectIds;
    }

    public function handle()
    {
        $data = app()->call('App\Http\Controllers\ProjectController@getProjectTotalARCount1', [
            'project_id' => $this->projectIds,
        ]); 
        dd($data);
        // Log::info("Processed Project ID: {$this->projectIds}", $data ?? []);
        // Cache::put("project_{$this->projectIds}_ar_count", $data, now()->addMinutes(30));
        // foreach ($this->projectIds as $projectId) {
        //     Log::info("Processed Project ID: {$projectId}", $data ?? []);
        //     Cache::put("project_{$projectId}_ar_count", $data, now()->addMinutes(30));
        // }
// Log the processed project IDs
Log::info("Processed Project IDs", ['projectIds' => $this->projectIds]);

// Create a unique cache key from project IDs
$cacheKey = 'project_' . $this->projectIds . '_ar_count';dd($cacheKey,$this->projectIds);

// Store the data in the cache
Cache::put($cacheKey, $data, now()->addMinutes(30));
        
    }
}
