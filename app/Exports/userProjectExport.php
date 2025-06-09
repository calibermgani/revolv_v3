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
    use \Maatwebsite\Excel\Concerns\Exportable;

    protected $prjDetailsList, $dates, $clientIds, $subPrjIds,$workDate, $userName, $formConfigurationDetails, $formProjectIds, $projectId, $subProjectId,$periods;  

    public function __construct($prjDetailsList, $workDate, $userName, $formConfigurationDetails, $formProjectIds, $subPrjIds, $clientIds, $projectId, $subProjectId)
    {
        $this->prjDetailsList = $prjDetailsList;
        $this->clientIds = $clientIds;
        $this->subPrjIds = $subPrjIds;
        $dateRange = explode(' - ', $workDate);
        $startDate = Carbon::parse($dateRange[0]);
        $endDate = Carbon::parse($dateRange[1]);

        $period = CarbonPeriod::create($startDate, $endDate)->filter(function ($date) {
            return !in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
        });
        $this->periods = $period->toArray();
        $this->dates = collect($period)->map(fn ($d) => $d->format('Y-m-d'))->values();
    }

    public function collection()
    {
     $rows = [];

        foreach ($this->prjDetailsList as $projectDetails) {
            foreach ($projectDetails as $project) {
                $subProjectName = $project['prj_id'] != null && $project['sub_prj_id'] != null
                    ? \App\Http\Helper\Admin\Helpers::subProjectName($project['prj_id'], $project['sub_prj_id'])['sub_project_name']
                    : '--';

                $matchKey = array_keys($this->clientIds, $project['prj_id']);
                if ($subProjectName === '--' || empty($matchKey) || !in_array($project['sub_prj_id'], $this->subPrjIds[$matchKey[0]])) {
                    continue;
                }

                $row = [
                    $project['emp_id'],
                    $project['user_name'],
                    $project['manager_name'],
                    \App\Http\Helper\Admin\Helpers::projectName($project['prj_id'])['aims_project_name'],
                    $subProjectName,
                ];

                foreach ($this->periods as $date) {
                    $aimsCount = 0;
                    foreach ($project['tool_data'] ?? [] as $entry) {
                        if ($entry['work_date'] === $date->format('Y-m-d')) {
                            $aimsCount = $entry['achieved'];
                            break;
                        }
                    }

                    // Resolv Count calculation
                    $resolvStartDate = $date->copy()->setTime(17, 0, 0);
                    $resolvEndDate = $date->copy()->addDay()->setTime(9, 0, 0);
                    $paProject = \App\Http\Helper\Admin\Helpers::projectName($project['prj_id']);
                    $table_name = Str::slug(Str::lower($paProject['project_name']) . '_' . Str::lower($subProjectName), '_');
                    $modelClass = "App\\Models\\" . Str::studly($table_name);
                    $arColumn = Schema::hasColumn($table_name, 'ar_at') && $modelClass::whereNotNull('ar_at')->exists()
                        ? 'ar_at'
                        : 'updated_at';

                    $resolvCount = $modelClass::whereBetween($arColumn, [$resolvStartDate, $resolvEndDate])
                        ->where('CE_emp_id', $project['emp_id'])
                        ->whereIn('chart_status', [
                            'CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold',
                            'AR_non_workable','Revoke','QA_Assigned','QA_Inprocess','QA_Pending',
                            'QA_Completed','QA_Clarification','QA_Hold'
                        ])
                        ->count();

                    $row[] = $resolvCount;
                    $row[] = $aimsCount;
                }

                $rows[] = collect($row);
            }
        }

        return $rows;
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
