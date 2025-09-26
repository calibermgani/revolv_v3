<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BulkProdcutionExport implements FromQuery, WithHeadings, WithMapping
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

public function query()
{
    $query = DB::table($this->tableName)
        ->join('caller_charts_work_logs', 'caller_charts_work_logs.record_id', '=', $this->tableName.'.parent_id')
        ->select($this->columns)
        ->where('caller_charts_work_logs.project_id', $this->requestData['project_id'])
        ->where('caller_charts_work_logs.sub_project_id', $this->requestData['sub_project_id']);

    if (!empty($this->requestData['work_date'])) {
        $work_date = explode(' - ', $this->requestData['work_date']);
        $start_date = date('Y-m-d 08:00:00', strtotime($work_date[0]));
        $end_date   = date('Y-m-d 07:59:00', strtotime($work_date[1].' +1 day'));
        $query->whereBetween('caller_charts_work_logs.start_time', [$start_date, $end_date]);
    }

    if (!empty($this->requestData['user'])) {
        $query->where(function($q) {
            $q->where('CE_emp_id', $this->requestData['user'])
              ->orWhere('QA_emp_id', $this->requestData['user']);
        });
    }

    if (!empty($this->requestData['client_status'])) {
        $query->where('caller_charts_work_logs.record_status', $this->requestData['client_status']);
    }

    // **Add default orderBy to avoid Maatwebsite\Excel error**
    return $query->orderBy('caller_charts_work_logs.record_id', 'asc');
}



    public function headings(): array
    {
        return $this->columns;
    }

    public function map($row): array
    {
        $mapped = [];
        foreach ($this->columns as $col) {
            $mapped[] = $row->{$col} ?? '--';
        }
        return $mapped;
    }
}
