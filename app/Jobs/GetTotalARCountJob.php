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
    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function handle()
    {
        $data = app()->call('App\Http\Controllers\ProjectController@getProjectTotalARCount', [
            'project_id' => $this->projectId,
        ]);
dd($data);
        Log::info("Processed Project ID: {$this->projectId}", $data ?? []);
        Cache::put("project_{$this->projectId}_ar_count", $data, now()->addMinutes(30));
    }
}
