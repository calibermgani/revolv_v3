<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection, WithHeadings, WithMapping, WithEvents,
    ShouldAutoSize, WithTitle, WithStrictNullComparison
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Str;
use Schema;

class userProjectExport implements FromCollection, WithHeadings, WithMapping, WithEvents, ShouldAutoSize, WithTitle, WithStrictNullComparison
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    protected $prjDetailsList, $dates, $clientIds, $subPrjIds;

    public function __construct($prjDetailsList, $workDate, $userName, $formConfigurationDetails, $formProjectIds, $subPrjIds, $clientIds, $projectId, $subProjectId)
    {
        $this->prjDetailsList = $prjDetailsList;
        $this->clientIds = $clientIds;
        $this->subPrjIds = $subPrjIds;

        // Generate date list (weekdays only)
        $workDates = explode(' - ', $workDate);
        $start = Carbon::parse($workDates[0]);
        $end = Carbon::parse($workDates[1]);

        $this->dates = CarbonPeriod::create($start, $end)->filter(function ($date) {
            return !in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
        })->toArray();
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

                foreach ($this->dates as $date) {
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

        return collect($rows);
    }

    public function headings(): array
    {
        $headers = ['Emp Id', 'Emp Name', 'Manager Name', 'Project', 'Sub Project'];
        foreach ($this->dates as $date) {
            $headers[] = $date->format('m/d/Y') . ' - Resolv Count';
            $headers[] = $date->format('m/d/Y') . ' - AIMS Count';
        }
        return $headers;
    }

    public function title(): string
    {
        return 'Project Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
                $sheet->freezePane('F2'); // Freeze after static columns
            }
        ];
    }

    public function map($row): array
    {
        return $row->toArray();
    }
}
