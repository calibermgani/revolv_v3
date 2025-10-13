<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Query\Builder;

class BulkReportExport implements FromQuery, WithHeadings, Responsable
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    protected $query;
    protected $columns;

    public function __construct(Builder $query, array $columns)
    {
        $this->query = $query;
        $this->columns = $columns;
    }

    public function query()
    {
        // ✅ Add orderBy for chunking
        $firstCol = $this->columns[0] ?? 'id';
        return $this->query->orderBy($firstCol, 'asc');
    }

    public function headings(): array
    {
        return array_map(fn($col) => ucfirst(str_replace('_', ' ', $col)), $this->columns);
    }
}
