<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Http\Helper\Admin\Helpers as Helpers;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class NonArDateRangeWiseAimsProduction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   
    protected $startDate;

    protected $endDate;

    protected $workDate;

    public function __construct( $startDate,$endDate,$workDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->workDate = $workDate;
    }

    public function handle()
    {
         Log::info(
            'Started NonArDateRangeWiseAimsProduction',
            [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'work_date' => $this->workDate,
            ]
        );
        try {
            Log::info("Started for {$this->workDate}");

            $projects = collect(app('App\Http\Controllers\ProjectController')->getNonArProjects());
            $BodyDetails = [];
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

                    foreach ($subProjects as $subKey => $subProject) {
                        try {
                            $startTime = microtime(true);

                            $tableName = Str::slug(
                                Str::lower(
                                    str_replace(
                                        ['/', '\\'],
                                        ' ',
                                        $prjName
                                        . '_'
                                        . $subProject
                                        . '_datas'
                                    )
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

                            $existingPrjUsers = DB::table($tableName)
                                ->where('emp_id', '!=', '0')
                                ->whereNotNull('emp_id')
                                ->where('emp_id', 'like', '%AM%')
                                ->groupBy('emp_id')
                                ->pluck('emp_id')
                                ->toArray();

                            $nonArData = DB::table($tableName)
                                ->selectRaw(
                                    'emp_id, COUNT(*) as cnt'
                                )
                                ->whereIn(
                                    'emp_id',
                                    $existingPrjUsers
                                )
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

                            $BodyDetails[] = [
                                'project_id' => $project['id'],
                                'sub_project_id' => $subKey,
                                'nonArData' => $nonArData,
                                'workDate' => $this->workDate,
                            ];

                            Log::info(
                                'Processed non-AR project table',
                                [
                                    'project_id' =>
                                        $project['id'],
                                    'sub_project_id' =>
                                        $subKey,
                                    'table' => $tableName,
                                    'employee_count' =>
                                        count($nonArData),
                                    'duration_seconds' =>
                                        round(
                                            microtime(true)
                                                - $startTime,
                                            2
                                        ),
                                ]
                            );
                        } catch (\Throwable $subProjectException) {
                            Log::error(
                                'Non-AR subproject processing failed',
                                [
                                    'project_id' =>
                                        $project['id'] ?? null,
                                    'sub_project_id' =>
                                        $subKey ?? null,
                                    'sub_project_name' =>
                                        $subProject ?? null,
                                    'message' =>
                                        $subProjectException
                                            ->getMessage(),
                                    'file' =>
                                        $subProjectException
                                            ->getFile(),
                                    'line' =>
                                        $subProjectException
                                            ->getLine(),
                                ]
                            );

                            continue;
                        }
                    }
                } catch (\Throwable $innerException) {
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

                    continue;
                }
            }

            $cacheKey =
                "date-range-non-ar-aims-production_{$this->workDate}";

            Cache::put(
                $cacheKey,
                $BodyDetails,
                now()->addHours(6)
            );

            Log::info("Completed {$this->workDate}");
        } catch (\Throwable $e) {
            Log::error(
                'NonArDateRangeWiseAimsProduction failed',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );
        }
    }
}