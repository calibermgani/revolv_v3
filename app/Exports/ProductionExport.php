<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;
use App\Http\Helper\Admin\Helpers as Helpers;
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
                if (isset($field) == 'dos' && $record->dos != null) {
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
                $empId = "CE_emp_id";
                $chartStatus = 'chart_status';
                // if (str_contains($record->{$field}, '-') && strtotime($record->{$field})) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $record->{$field})) {
                    $exportRow[$headerField] = date('m/d/Y', strtotime($record->{$field}));
                } else if ($field == 'chart_status' && str_contains($record->{$field}, 'CE_') && is_null($record->{$empId})) {
                    $exportRow[$headerField] = 'Un '.str_replace('CE_', '', $record->{$field});
                } else if ($field == 'chart_status' && str_contains($record->{$field}, 'CE_') && !is_null($record->{$empId})) {
                    $exportRow[$headerField] = 'AR '.str_replace('CE_', '', $record->{$field});
                } else if ($field == 'chart_status' && str_contains($record->{$field}, 'QA_')) {
                    $exportRow[$headerField] = 'QA '.str_replace('QA_', '', $record->{$field});
                } else if ($field == 'chart_status' && str_contains($record->{$field}, 'AR_non_workable')) {
                    $exportRow[$headerField] = 'Non Workable';
                } else if ($field == 'chart_status' && str_contains($record->{$field}, 'Auto_Close')) {
                    $exportRow[$headerField] = 'Auto Close';
                } else if(($record->{$chartStatus} == 'AR_non_workable') && ($field == 'ar_notes' || $field == 'notes' || $field == 'remarks' || $field =='comments')){
                    if($record->{$field} != null) {
                        $exportRow[$headerField] = Helpers::nonWorkableReasonName($record->{$field})->reason_type;
                    } else {
                        $exportRow[$headerField]= '';
                    }
                }
                else if ($field == 'aging') {
                    $exportRow[$headerField] = $agingCount;
                } elseif ($field == 'aging_range') {
                    $exportRow[$headerField] =  $agingRange;
                } else if($field == 'QA_status_code') {
                    //  $exportRow[$headerField] = Helpers::qaStatusById($record->{$field})['status_code'] ;
                    $statusCode = Helpers::qaStatusById($record->{$field});
                    $exportRow[$headerField] = $statusCode['status_code'] ?? '';
                } else if($field == 'QA_sub_status_code') {
                    $result = Helpers::qaSubStatusById($record->{$field});
                    $exportRow[$headerField] = $result['sub_status_code'] ?? '';
                    // $exportRow[$headerField] = Helpers::qaSubStatusById($record->{$field})['sub_status_code'] ;
                } else if($field == 'qa_classification') {
                    $qaClassification = Helpers::qaClassificationById($record->{$field});
                    $exportRow[$headerField] = $qaClassification['qa_classification'] ?? '';
                   //  $exportRow[$headerField] = Helpers::qaClassificationById($record->{$field})['qa_classification'] ;
                } else if($field == 'qa_category') {
                      $qaCategory = Helpers::qaCategoryById($record->{$field});
                      $exportRow[$headerField] = $qaCategory['qa_category'] ?? '';
                   //  $exportRow[$headerField] = Helpers::qaCategoryById($record->{$field})['qa_category'] ;
                } else if($field == 'qa_scope') {
                      $qaScope = Helpers::qaScopeById($record->{$field});
                      $exportRow[$headerField] = $qaScope['qa_scope'] ?? '';
                   //  $exportRow[$headerField] = Helpers::qaScopeById($record->{$field})['qa_scope'] ;
                }
                else {
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
            if ($field == 'CE_emp_id') {
                $field = 'AR_emp_id';
            } else if($field == 'chart_status') {
                $field = 'charge_status';
            } else if($field == 'coder_work_date') {
                $field = 'ar_work_date';
            } else if($field == 'coder_rework_status') {
                $field = 'ar_rework_status';
            } 
            $headerField = ucwords(str_replace(['_else_', '_'], ['/', ' '], $field));
            // Convert the field name from snake_case to words with first letter capitalized
            return $headerField;
        }, $this->fields);
    }
}
