<?php

namespace App\Jobs;

use App\Models\CallerChartsWorkLogs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Helper\Admin\Helpers as Helpers;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;   // ✅ Important
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DateRangeWiseAimsProductionUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $startDate;
    protected $endDate;
    protected $workDate;

    public function __construct($startDate, $endDate, $workDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->workDate = $workDate;
    }

    public function handle()
    {
        try {
            Log::info("Started for {$this->workDate}");
            $BodyDetails = [];
            $callChartResults = CallerChartsWorkLogs::selectRaw("
                                emp_id,  COUNT(DISTINCT record_id) as call_cnt,project_id,sub_project_id,
                                DATE_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(work_time))), '%H:%i:%s') as work_hours
                            ")
                ->whereNotNull('emp_id')
                ->where('emp_id', 'like', '%AM%')
                ->whereBetween('start_time', [$this->startDate, $this->endDate])
                ->whereIn('record_status', ['CE_Assigned', 'CE_Inprocess', 'CE_Pending', 'CE_Completed', 'CE_Clarification', 'CE_Hold', 'Revoke', 'Rebuttal'])
                ->groupBy('emp_id', 'project_id', 'sub_project_id')
                ->get()
                ->toArray(); //here if record id has two entries then count increased
            $BodyDetails[] = [
                'callChartResults' => $callChartResults,
                'workDate' => $this->workDate,
            ];

            Log::info("DateRangeWiseAimsProductionUpdate: " . $this->workDate);
            // ✅ Store per date
            $cacheKey = "date-range-aims-production_{$this->workDate}";
            Cache::put($cacheKey, $BodyDetails, now()->addHours(6));

            Log::info("Completed {$this->workDate}");
        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());
        }
    }
}
