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


class GetTotalQACountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $projectIds;
    public function __construct(array $projectIds)
    {
        $this->projectIds = $projectIds;
    }

    public function handle()
    {
        $cacheKey = 'project_' . implode('_', $this->projectIds) . '_qa_count';
       // Cache::forget($cacheKey);
        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () {
                return app()->call('App\Http\Controllers\ProjectController@getProjectTotalQACount1', [
                'project_id' => $this->projectIds,
            ]);
        });        
        Cache::put($cacheKey, $data, now()->addMinutes(10));
        
    }
}
