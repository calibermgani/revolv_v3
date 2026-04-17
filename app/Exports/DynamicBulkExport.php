<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;


class DynamicBulkExport implements FromQuery, WithHeadings, WithChunkReading
{
    private $table;
    private $columns;
    private $filters;

    public function __construct($table, $columns, $filters = [])
    {
        $this->table = $table;
        $this->columns = $columns;
        $this->filters = $filters;
    }

    public function query()
    {
        $query = DB::table($this->table)->select($this->columns)->orderBy('id', 'asc');dd($query);

        // Apply filters dynamically
        if (!empty($this->filters['work_date'])) {
            $dates = explode(' - ', $this->filters['work_date']);
            $start = date('Y-m-d 08:00:00', strtotime($dates[0]));
            $end   = date('Y-m-d 07:59:00', strtotime($dates[1] . ' +1 day'));
            if (in_array('updated_at', $this->columns)) {
                $query->whereBetween('updated_at', [$start, $end]);
            }
        }

        if (!empty($this->filters['user'])) {
            $query->where(function ($q) {
                $q->where('CE_emp_id', $this->filters['user'])
                  ->orWhere('QA_emp_id', $this->filters['user']);
            });
        }

        if (!empty($this->filters['client_status']) && in_array('chart_status', $this->columns)) {
            $query->where('chart_status', $this->filters['client_status']);
        }

        return $query;
    }

    public function headings(): array
    {
        return $this->columns;
    }

    public function chunkSize(): int
    {
        return 10000; // process 10k rows per chunk
    }
}
