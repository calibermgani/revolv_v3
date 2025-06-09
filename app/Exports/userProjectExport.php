<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class userProjectExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $prjDetailsList;
    protected $dates;

    public function __construct($prjDetailsList, $workDate)
    {
        $this->prjDetailsList = $prjDetailsList;

        $dateRange = explode(' - ', $workDate);
        $startDate = Carbon::parse($dateRange[0]);
        $endDate = Carbon::parse($dateRange[1]);

        $period = CarbonPeriod::create($startDate, $endDate)->filter(function ($date) {
            return !in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
        });

        $this->dates = collect($period)->map(fn ($d) => $d->format('Y-m-d'))->values();
    }

    public function collection()
    {
        return collect($this->prjDetailsList)->flatMap(function ($group) {
            return collect($group)->map(function ($project) {
                $row = [
                    $project['emp_id'] ?? '',
                    $project['user_name'] ?? '',
                    $project['manager_name'] ?? '',
                    $project['prj_id'] ?? '',
                    $project['sub_prj_id'] ?? '',
                ];

                foreach ($this->dates as $date) {
                    $aimsCount = 0;
                    if (!empty($project['tool_data'])) {
                        foreach ($project['tool_data'] as $entry) {
                            if ($entry['work_date'] === $date) {
                                $aimsCount = $entry['achieved'];
                                break;
                            }
                        }
                    }

                    // RESOLV COUNT: based on table name logic
                    $decodedClientName = $project['prj_id'] ?? null;
                    $decodedsubProjectName = $project['sub_prj_id'] ?? 'project';

                    $table_name = Str::slug(Str::lower($decodedClientName . '_' . $decodedsubProjectName), '_');
                    $modelName = Str::studly($table_name);
                    $modelClass = "App\\Models\\" . $modelName;

                    $resolvCount = 0;
                    if (class_exists($modelClass) && Schema::hasTable($table_name)) {
                        $arColumnToUse = Schema::hasColumn($table_name, 'ar_at') &&
                            $modelClass::whereNotNull('ar_at')->exists() ? 'ar_at' : 'updated_at';

                        $resolvStartDate = date('Y-m-d 17:00:00', strtotime($date));
                        $resolvEndDate = date('Y-m-d 09:00:00', strtotime($date . ' +1 day'));

                        try {
                            $resolvCount = $modelClass::whereBetween($arColumnToUse, [$resolvStartDate, $resolvEndDate])
                                ->where('CE_emp_id', $project['emp_id'])
                                ->whereIn('chart_status', [
                                    'CE_Inprocess', 'CE_Pending', 'CE_Completed', 'CE_Clarification', 'CE_Hold',
                                    'AR_non_workable', 'Revoke', 'QA_Assigned', 'QA_Inprocess', 'QA_Pending',
                                    'QA_Completed', 'QA_Clarification', 'QA_Hold'
                                ])
                                ->count();
                        } catch (\Exception $e) {
                            $resolvCount = 0; // fallback
                        }
                    }

                    $row[] = $resolvCount;
                    $row[] = $aimsCount;
                }

                return $row;
            });
        });
    }

    public function headings(): array
    {
        $firstRow = ["Emp Id", "Emp Name", "Manager Name", "Project", "Sub Project"];
        foreach ($this->dates as $date) {
            $firstRow[] = Carbon::parse($date)->format('m/d/Y');
            $firstRow[] = '';
        }

        $secondRow = ["", "", "", "", ""];
        foreach ($this->dates as $date) {
            $secondRow[] = "Resolv Count";
            $secondRow[] = "AIMS Count";
        }

        return [$firstRow, $secondRow];
    }

    public function map($row): array
    {
        return $row;
    }

    public function title(): string
    {
        return 'User Project Report';
    }
}
