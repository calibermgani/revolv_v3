<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Http\Request;

class BulkReportExport implements FromQuery, WithHeadings
{
    use Exportable;

    protected $table_name, $request;

    public function __construct($table_name, Request $request)
    {
        $this->table_name = $table_name;
        $this->request = $request;
    }

 public function query()
{
    $table = $this->table_name;
    $allColumns = array_column(DB::select("DESCRIBE `$table`"), 'Field');

    $query = DB::table($table)->select($allColumns);

    if (!empty($this->request->user)) {
        $query->where(function($q) {
            $q->where('CE_emp_id', $this->request->user)
              ->orWhere('QA_emp_id', $this->request->user);
        });
    }

    if (!empty($this->request->client_status) && in_array('chart_status', $allColumns)) {
        $query->where('chart_status', $this->request->client_status);
    }

    // ✅ Add orderBy — required by chunking in FromQuery
    if (in_array('id', $allColumns)) {
        $query->orderBy('id', 'asc');
    } else {
        // If table has no 'id' column, use the first column as a fallback
        $query->orderBy($allColumns[0], 'asc');
    }

    return $query;
}


    public function headings(): array
    {
        $table = $this->table_name;
        $columns = array_column(DB::select("DESCRIBE `$table`"), 'Field');
        return $columns;
    }
}
