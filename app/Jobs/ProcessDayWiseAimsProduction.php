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

class ProcessDayWiseAimsProduction implements ShouldQueue
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
            Log::info("Started ProcessDayWiseAimsProduction for date {$this->workDate}");

            // Fetch project list from your existing helper
            $projects = collect(app('App\Http\Controllers\ProjectController')->getProjects());
            $BodyDetails = [];

            $projects->each(function ($project) use (&$BodyDetails) {
                try {
                    $prjDetails = Helpers::projectName($project['id']);
                    $prjName = $prjDetails->project_name ?? null;

                    if (!$prjName) return;

                    $subProjects = count($project['subprject_name']) > 0
                        ? $project['subprject_name']
                        : ['project'];

                    foreach ($subProjects as $subKey => $subProject) {
                        $startTime = microtime(true);
                        $tableName = Str::slug(Str::lower($prjName . '_' . $subProject), '_');
                        $modelClass = "App\\Models\\" . Str::studly($tableName);

                        if (!class_exists($modelClass)) {
                            Log::warning("Model not found for table {$tableName}");
                            continue;
                        }

                        $existingPrjUsers = $modelClass::where('CE_emp_id', '!=', '0')
                            ->whereNotNull('CE_emp_id')
                            ->where('CE_emp_id', 'like', '%AM%')
                            ->groupBy('CE_emp_id')
                            ->pluck('CE_emp_id')
                            ->toArray();

                        $arColumnExists = Schema::hasColumn($tableName, 'ar_at');
                        $hasNonNullArAt = $arColumnExists && $modelClass::whereNotNull('ar_at')->exists();
                        $arColumnToUse = $hasNonNullArAt ? 'ar_at' : 'updated_at';
                        $startDate1 = "2025-11-11 08:00:00";
                        $endDate1 = "2025-11-12 07:59:00";
                        //

                        // ----- Query results -----
                        $arData = $modelClass::selectRaw("CE_emp_id, COUNT(*) as cnt")
                            ->whereIn('CE_emp_id', $existingPrjUsers)
                            // ->whereBetween($arColumnToUse, [$this->startDate, $this->endDate])
                            ->whereBetween($arColumnToUse, [$startDate1, $endDate1])
                            ->groupBy('CE_emp_id')
                            ->get()
                            ->toArray();

                        // ----- Caller work logs -----
                        $callChartResults = CallerChartsWorkLogs::selectRaw("
                                emp_id, COUNT(*) as call_cnt,
                                DATE_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(work_time))), '%H:%i:%s') as work_hours
                            ")
                            ->whereIn('emp_id', $existingPrjUsers)
                            ->where('project_id', $project['id'])
                            ->where('sub_project_id', $subKey)
                            ->whereBetween('start_time', [$startDate1, $endDate1])
                            ->groupBy('emp_id')
                            ->get()
                            ->toArray();
$workDate1 = "2025-11-11";
                        $BodyDetails[] = [
                            'project_id' => $project['id'],
                            'sub_project_id' => $subKey,
                            'arData' => $arData,
                            'callChartResults' => $callChartResults,
                            'workDate' => $this->workDate,
                        ];

                        $duration = round(microtime(true) - $startTime, 2);
                        Log::info("Processed project {$project['id']} subproject {$subKey} in {$duration}s");
                    }
                } catch (\Exception $innerEx) {
                    Log::error("Inner loop error for project {$project['id']}: " . $innerEx->getMessage());
                }
            });

            // Cache final results (for later retrieval)
            $cacheKey = "aims_production_{$this->workDate}";
            Cache::put($cacheKey, $BodyDetails, now()->addHours(6));

            Log::info("Completed ProcessDayWiseAimsProduction for date {$this->workDate}");

        } catch (\Exception $e) {
            Log::error("Error in ProcessDayWiseAimsProduction: " . $e->getMessage());
        }
    }
}
