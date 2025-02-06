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

class GetUserNameByEmpId implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $empId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($empId)
    {
        $this->empId = $empId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cacheKey = "emp_name_{$this->empId}";

        // Check if the result is already cached
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () {
            return app()->call('App\Http\Helper\Admin\Helpers@getUserNameByEmpId', [
                    'id' => $this->empId,
                ]);
        });

        Cache::put("emp_name_{$this->empId}", $data, now()->addMinutes(30));
    }
}
