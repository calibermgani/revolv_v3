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
        ]);dd($data);
        Log::info("Processed Project ID: {$this->projectIds}", $data ?? []);
        Cache::put("project_{$this->projectIds}_ar_count", $data, now()->addMinutes(30));
    }
}
