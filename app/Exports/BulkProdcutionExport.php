<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Http\Helper\Admin\Helpers;
use Carbon\Carbon;

class BulkProdcutionExport implements FromGenerator, WithHeadings, WithMapping
{
    protected $requestData;
    protected $tableName;
    protected $columns;

    public function __construct(array $requestData, string $tableName, array $columns)
    {
        $this->requestData = $requestData;
        $this->tableName   = $tableName;
        $this->columns     = $columns;
    }

    /**
     * Stream rows using a generator to avoid memory issues
     */
    public function generator(): \Generator
    {
        $query = DB::table($this->tableName)
            ->join(
                'caller_charts_work_logs',
                'caller_charts_work_logs.record_id',
                '=',
                $this->tableName . '.parent_id'
            )
            ->select($this->columns)
            ->where('caller_charts_work_logs.project_id', $this->requestData['project_id'])
            ->where('caller_charts_work_logs.sub_project_id', $this->requestData['sub_project_id'])
            ->orderBy('caller_charts_work_logs.record_id', 'asc');

        if (!empty($this->requestData['work_date'])) {
            $work_date = explode(' - ', $this->requestData['work_date']);
            $start_date = date('Y-m-d 08:00:00', strtotime($work_date[0]));
            $end_date   = date('Y-m-d 07:59:00', strtotime($work_date[1] . ' +1 day'));
            $query->whereBetween('caller_charts_work_logs.start_time', [$start_date, $end_date]);
        }

        if (!empty($this->requestData['user'])) {
            $user = $this->requestData['user'];
            $query->where(function ($q) use ($user) {
                $q->where('CE_emp_id', $user)
                  ->orWhere('QA_emp_id', $user);
            });
        }

        if (!empty($this->requestData['client_status'])) {
            $query->where('caller_charts_work_logs.record_status', $this->requestData['client_status']);
        }

        foreach ($query->cursor() as $row) {
            yield $row;
        }
    }

    public function headings(): array
    {
        return $this->columns;
    }

    public function map($row): array
    {
        $mapped = [];
        $agingCount = null;
        $agingRange = null;

        foreach ($this->columns as $col) {
            $data = $row->{$col} ?? '--';

            // ---- Mirror your controller’s transformations ----
            if ($col === 'QA_status_code' && $data !== '--') {
                $data = Helpers::qaStatusById($data)['status_code'] ?? $data;
            }
            if ($col === 'QA_sub_status_code' && $data !== '--') {
                $data = Helpers::qaSubStatusById($data)['sub_status_code'] ?? $data;
            }
            if ($col === 'qa_classification' && $data !== '--') {
                $data = Helpers::qaClassificationById($data)['qa_classification'] ?? $data;
            }
            if ($col === 'qa_category' && $data !== '--') {
                $data = Helpers::qaCategoryById($data)['qa_category'] ?? $data;
            }
            if ($col === 'qa_scope' && $data !== '--') {
                $data = Helpers::qaScopeById($data)['qa_scope'] ?? $data;
            }
            if ($col === 'ar_status_code' && $data !== '--' && $data !== null) {
                $status = Helpers::arStatusById($data);
                $data = $status['status_code'] ?? $data;
            }
            if ($col === 'ar_action_code' && $data !== '--' && $data !== null) {
                $action = Helpers::arActionById($data);
                $data = $action['action_code'] ?? $data;
            }
            if ($col === 'ar_denial_codes' && $data !== '--' && $data !== null) {
                $denial = Helpers::arDenialById($data);
                $data = $denial['denialCode'] ?? $data;
            }
            if ($col === 'ar_substatus_codes' && $data !== '--' && $data !== null) {
                $sub = Helpers::arSubStatusById($data);
                $data = $sub['substatusCode'] ?? $data;
            }
            if ($col === 'chart_status') {
                $recordStatus = $row->{'record_status'};
                if (strpos($recordStatus, 'CE_') === 0) {
                    $data = str_replace('CE_', 'AR ', $recordStatus);
                } elseif (strpos($recordStatus, 'QA_') === 0) {
                    $data = str_replace('QA_', 'QA ', $recordStatus);
                } else {
                    $data = ucwords(str_replace('_', ' ', $recordStatus));
                }
            }
            if ($col === 'qa_work_status') {
                $data = str_replace('_', ' ', $data);
            }
            if ($col === 'work_hours') {
                $data = $row->work_time ?? '--';
            }
            if ($col === 'qa_work_date') {
                $data = ($row->{'record_status'} == "QA_Completed" && $data !== '--')
                    ? date('m/d/y', strtotime($data))
                    : '--';
            }
            if ($col === 'coder_work_date') {
                $data = ($row->{'record_status'} == "CE_Completed" && $data !== '--')
                    ? date('m/d/y', strtotime($data))
                    : '--';
            }
            if ($col === 'dos' && $data !== '--') {
                $data = date('m/d/y', strtotime($data));
                $dosDate = Carbon::parse($row->{'dos'});
                $agingCount = $dosDate->diffInDays(Carbon::now());
                if ($agingCount <= 30) $agingRange = '0-30';
                elseif ($agingCount <= 60) $agingRange = '31-60';
                elseif ($agingCount <= 90) $agingRange = '61-90';
                elseif ($agingCount <= 120) $agingRange = '91-120';
                elseif ($agingCount <= 180) $agingRange = '121-180';
                elseif ($agingCount <= 365) $agingRange = '181-365';
                else $agingRange = '365+';
            }
            if ($col === 'aging') {
                $data = $agingCount;
            }
            if ($col === 'aging_range') {
                $data = $agingRange;
            }

            // replace "_el_" with ","
            if (strpos($data, '_el_') !== false) {
                $data = str_replace('_el_', ' , ', $data);
            }

            $mapped[] = $data;
        }

        return $mapped;
    }
}
