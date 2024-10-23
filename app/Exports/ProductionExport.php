<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class ProductionExport implements FromCollection, WithHeadings
{
    protected $exportResult;
    protected $fields; // Dynamic fields

    public function __construct($fields, $exportResult)
    {
        $this->exportResult = $exportResult;
        $this->fields = $fields;  // The dynamic fields to include in export
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Map the export result to include only dynamic fields
        return $this->exportResult->map(function ($record) {
            $exportRow = [];

            foreach ($this->fields as $field) {
                $headerField = ucwords(str_replace(['_else_', '_'], ['/', ' '], $field));
                if (isset($field) == 'dos') {
                    $dosDate = Carbon::parse($record->dos);
                    $currentDate = Carbon::now();
                    $agingCount = $dosDate->diffInDays($currentDate);
                    if ($agingCount <= 30) {
                        $agingRange = '0-30';
                    } elseif ($agingCount <= 60) {
                        $agingRange = '31-60';
                    } elseif ($agingCount <= 90) {
                        $agingRange = '61-90';
                    } elseif ($agingCount <= 120) {
                        $agingRange = '91-120';
                    } elseif ($agingCount <= 180) {
                        $agingRange = '121-180';
                    } elseif ($agingCount <= 365) {
                        $agingRange = '181-365';
                    } else {
                        $agingRange = '365+';
                    }
                } else {
                    $agingCount = '--';
                    $agingRange = '--';
                }

                if (str_contains($record->{$field}, '-') && strtotime($record->{$field})) {
                    $exportRow[$headerField] = date('m/d/Y', strtotime($record->{$field}));
                } else if ($field == 'chart_status' && str_contains($record->{$field}, 'CE_')) {
                    $exportRow[$headerField] = str_replace('CE_', '', $record->{$field});
                } else if ($field == 'aging') {
                    $exportRow[$headerField] = $agingCount;
                } elseif ($field == 'aging_range') {
                    $exportRow[$headerField] =  $agingRange;
                } else {
                    $exportRow[$headerField] = $record->{$field};
                }
                // Dynamically get the values based on field names

            }
            
            return $exportRow;
        });
    }

    /**
     * Add the headers for the Excel export dynamically
     */
    public function headings(): array
    {
        // Convert field names to headers with capitalized first letters of each word
        return array_map(function ($field) {
            $headerField = ucwords(str_replace(['_else_', '_'], ['/', ' '], $field));
            // Convert the field name from snake_case to words with first letter capitalized
            return $headerField;
        }, $this->fields);
    }
}
