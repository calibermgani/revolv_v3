<?php

namespace App\Jobs;

use App\Http\Helper\Admin\Helpers as Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ProcessDayWiseAimsProductionNonArProjects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 900;

    public $failOnTimeout = true;

    protected $startDate;

    protected $endDate;

    protected $workDate;

    public function __construct(
        $startDate,
        $endDate,
        $workDate
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->workDate = $workDate;
    }

    public function handle()
    {
        Log::info(
            'Started ProcessDayWiseAimsProductionNonArProjects',
            [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'work_date' => $this->workDate,
            ]
        );

        try {
            $projects = collect(
                app(
                    'App\Http\Controllers\ProjectController'
                )->getNonArProjects()
            );

            $bodyDetails = [];

            foreach ($projects as $project) {
                try {
                    $prjDetails = Helpers::projectName(
                        $project['id']
                    );

                   $prjName = $prjDetails->project_name ?? null;

                    if (!$prjName) {
                        Log::warning(
                            'Project name not found',
                            [
                                'project_id' => $project['id'],
                            ]
                        );

                        continue;
                    }

                    $subProjects = !empty(
                        $project['subprject_name']
                    )
                        ? $project['subprject_name']
                        : [0 => 'project'];

                    foreach (
                        $subProjects as $subKey => $subProject
                    ) {
                        $startTime = microtime(true);

                        $tableName = Str::slug(
                            Str::lower(
                                $prjName
                                . '_'
                                . $subProject
                                . '_datas'
                            ),
                            '_'
                        );

                        if (!Schema::hasTable($tableName)) {
                            Log::warning(
                                'Dynamic table not found',
                                [
                                    'table' => $tableName,
                                    'project_id' => $project['id'],
                                    'sub_project_id' => $subKey,
                                ]
                            );

                            continue;
                        }

                        $requiredColumns = [
                            'emp_id',
                            'work_date',
                            'charge_status',
                        ];

                        $missingColumns = [];

                        foreach ($requiredColumns as $column) {
                            if (
                                !Schema::hasColumn(
                                    $tableName,
                                    $column
                                )
                            ) {
                                $missingColumns[] = $column;
                            }
                        }

                        if (!empty($missingColumns)) {
                            Log::warning(
                                'Required columns are missing',
                                [
                                    'table' => $tableName,
                                    'missing_columns' =>
                                        $missingColumns,
                                ]
                            );

                            continue;
                        }

                        /*
                         * The separate existingPrjUsers query is not
                         * required. The same employee filters can be
                         * applied directly to the count query.
                         */
                        $nonArData = DB::table($tableName)
                            ->selectRaw(
                                'emp_id, COUNT(*) as cnt'
                            )
                            ->whereNotNull('emp_id')
                            ->where('emp_id', '!=', '0')
                            ->where('emp_id', 'like', '%AM%')
                            ->whereBetween(
                                'work_date',
                                [
                                    $this->startDate,
                                    $this->endDate,
                                ]
                            )
                            ->where(
                                'charge_status',
                                'CE_Completed'
                            )
                            ->groupBy('emp_id')
                            ->get()
                            ->toArray();

                        $bodyDetails[] = [
                            'project_id' => $project['id'],
                            'sub_project_id' => $subKey,
                            'nonArData' => $nonArData,
                            'workDate' => $this->workDate,
                        ];

                        Log::info(
                            'Processed non-AR project table',
                            [
                                'project_id' => $project['id'],
                                'sub_project_id' => $subKey,
                                'table' => $tableName,
                                'employee_count' =>
                                    count($nonArData),
                                'duration_seconds' => round(
                                    microtime(true) - $startTime,
                                    2
                                ),
                            ]
                        );
                    }
                } catch (Throwable $innerException) {
                    Log::error(
                        'Non-AR project processing failed',
                        [
                            'project_id' =>
                                $project['id'] ?? null,
                            'message' =>
                                $innerException->getMessage(),
                            'file' =>
                                $innerException->getFile(),
                            'line' =>
                                $innerException->getLine(),
                        ]
                    );

                    /*
                     * Continue processing other projects.
                     */
                }
            }

            $cacheKey =
                "non_ar_aims_production_{$this->workDate}";

            Cache::put(
                $cacheKey,
                $bodyDetails,
                now()->addHours(6)
            );

            Log::info(
                'Completed ProcessDayWiseAimsProductionNonArProjects',
                [
                    'work_date' => $this->workDate,
                    'result_count' => count($bodyDetails),
                    'cache_key' => $cacheKey,
                ]
            );
        } catch (Throwable $e) {
            Log::error(
                'ProcessDayWiseAimsProductionNonArProjects failed',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }
}