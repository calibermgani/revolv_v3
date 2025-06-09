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
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class userProjectExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize,WithEvents
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    protected $prjDetailsList, $dates, $clientIds, $subPrjIds,$periods;  

    public function __construct($prjDetailsList, $workDate, $subPrjIds, $clientIds,$periods)
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
                            $aimsCount = $entry['achieved'] ?? 0;
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
                        ->count() ?? 0;

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

public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $highestColumn = $sheet->getHighestColumn();

            // Bold styling for both header rows
            $sheet->getStyle("A1:{$highestColumn}1")->getFont()->setBold(true);
            $sheet->getStyle("A2:{$highestColumn}2")->getFont()->setBold(true);

            // Merge static column headers (A1 to E1 with A2 to E2)
            $sheet->mergeCells('A1:A2');
            $sheet->mergeCells('B1:B2');
            $sheet->mergeCells('C1:C2');
            $sheet->mergeCells('D1:D2');
            $sheet->mergeCells('E1:E2');

            // Dynamic merge for each date block (2 columns: Resolv & AIMS)
            $startColIndex = 6; // Column 'F' is index 6

            foreach ($this->dates as $index => $date) {
                $col1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex);
                $col2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex + 1);
                $sheet->mergeCells("{$col1}1:{$col2}1"); // Merge two columns in first row
                $startColIndex += 2;
            }

            // Center alignment for header rows
            $sheet->getStyle("A1:{$highestColumn}2")->applyFromArray([
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);

            // Freeze pane after headers
            $sheet->freezePane('F3');
        }
    ];
}


    public function map($row): array
    {
        return $row->toArray();
    }

    public function title(): string
    {
        return 'Production Report';
    }
}
