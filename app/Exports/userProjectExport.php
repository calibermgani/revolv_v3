<?php



namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class userProjectExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $prjDetailsList;
    protected $workDate;
    protected $dates;

    public function __construct($prjDetailsList, $workDate)
    {
        $this->prjDetailsList = $prjDetailsList;
        $this->workDate = $workDate;

        $dateRange = explode(' - ', $workDate);
        $startDate = Carbon::parse($dateRange[0])->format('Y-m-d');
        $endDate = Carbon::parse($dateRange[1])->format('Y-m-d');

        $period = CarbonPeriod::create($startDate, $endDate)->filter(function ($date) {
            return !in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
        });

        $this->dates = collect($period)->map(fn($d) => $d->format('Y-m-d'))->values();
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
                                $aimsCount = $entry['achieved'] ?? 0;
                                break;
                            }
                        }
                    }

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
        return 'Production Report';
    }
}
