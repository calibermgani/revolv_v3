<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;
use App\Http\Helper\Admin\Helpers as Helpers;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductionBulkExport implements FromCollection, WithHeadings, WithStyles
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
                if ($field === 'record_status') {
                    continue;
                }
                $headerField = ucwords(str_replace(['_else_', '_'], ['/', ' '], $field));

                //   if ($field === 'dos' && !empty($record->dos)) {
                //         $dosDate = Carbon::parse($record->dos);
                //         $currentDate = Carbon::now();
                //         $agingCount = $dosDate->diffInDays($currentDate);
                //         if ($agingCount <= 30) {
                //             $agingRange = '0-30';
                //         } elseif ($agingCount <= 60) {
                //             $agingRange = '31-60';
                //         } elseif ($agingCount <= 90) {
                //             $agingRange = '61-90';
                //         } elseif ($agingCount <= 120) {
                //             $agingRange = '91-120';
                //         } elseif ($agingCount <= 180) {
                //             $agingRange = '121-180';
                //         } elseif ($agingCount <= 365) {
                //             $agingRange = '181-365';
                //         } else {
                //             $agingRange = '365+';
                //         }
                //     } else {
                //         $agingCount = '--';
                //         $agingRange = '--';
                //     }
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $record->{$field})) {
                    $exportRow[$headerField] = date('m/d/Y', strtotime($record->{$field}));
                } else if ($field === 'chart_status') {
                    $recordStatus = $record->{'record_status'};
                    if (strpos($recordStatus, 'CE_') === 0) {
                        $data = str_replace('CE_', 'AR ', $recordStatus);
                    } elseif (strpos($recordStatus, 'QA_') === 0) {
                        $data = str_replace('QA_', 'QA ', $recordStatus);
                    } elseif (strpos($recordStatus, 'QA_') === 0) {
                        $data = str_replace('QA_', 'QA ', $recordStatus);
                    } else {
                        $data =  str_replace('_', ' ', $recordStatus);
                        $data = ucwords($data);
                    }
                    $exportRow[$headerField] = $data;
                }
              
                else if ($field == 'QA_status_code') {
                    $statusCode = Helpers::qaStatusById($record->{$field});
                    $exportRow[$headerField] = $statusCode['status_code'] ?? '';
                } else if ($field == 'QA_sub_status_code') {
                    $result = Helpers::qaSubStatusById($record->{$field});
                    $exportRow[$headerField] = $result['sub_status_code'] ?? '';
                } else if ($field == 'qa_classification') {
                    $qaClassification = Helpers::qaClassificationById($record->{$field});
                    $exportRow[$headerField] = $qaClassification['qa_classification'] ?? '';
                } else if ($field == 'qa_category') {
                    $qaCategory = Helpers::qaCategoryById($record->{$field});
                    $exportRow[$headerField] = $qaCategory['qa_category'] ?? '';
                } else if ($field == 'qa_scope') {
                    $qaScope = Helpers::qaScopeById($record->{$field});
                    $exportRow[$headerField] = $qaScope['qa_scope'] ?? '';
                } else if ($field == 'ar_substatus_codes') {
                    $substatusCode = Helpers::arSubStatusById($record->{$field});
                    $exportRow[$headerField] = $substatusCode['substatusCode'] ?? '';
                } else if ($field == 'ar_denial_codes') {
                    $denialCode = Helpers::arDenialById($record->{$field});
                    $exportRow[$headerField] = $denialCode['denialCode'] ?? '';
                } else if ($field == 'ar_status_code') {
                    $status = Helpers::arStatusById($record->{$field});
                    $exportRow[$headerField] = $status['status_code'] ?? '';
                } else if ($field == 'ar_action_code') {
                    $action = Helpers::arActionById($record->{$field});
                    $exportRow[$headerField] = $action['action_code'] ?? '';
                } else if ($field == 'ar_manager_rebuttal_status' && str_contains($record->{$field}, 'dis_agree')) {
                    $exportRow[$headerField] = 'Disagree';
                }
                //    else if ($field === 'aging') {dd($headerField,$field);
                //     $exportRow[$headerField] = $agingCount;
                // } else if ($field == 'aging_range') {
                //     $exportRow[$headerField] =  $agingRange;
                // } 
                else {
                    $exportRow[$headerField] = $record->{$field};
                }
            }
            Log::info('Export Row:', $exportRow);
            return $exportRow;
        });
    }


    public function headings(): array
    {
        // Convert field names to headers with capitalized first letters of each word
        $headings = array_map(function ($field) {
            // Skip record_status column
            if ($field === 'record_status') {
                return null; // We'll filter nulls later
            }

            if ($field == 'CE_emp_id') {
                $field = 'AR_emp_id';
            } else if ($field == 'chart_status') {
                $field = 'charge_status';
            } else if ($field == 'coder_work_date') {
                $field = 'ar_work_date';
            } else if ($field == 'coder_rework_status') {
                $field = 'ar_rework_status';
            } else if ($field == 'aging') {
                $field = 'Aging';
            } else if ($field == 'aging_range') {
                $field = 'Aging Range';
            } else if ($field == 'ce_hold_reason') {
                $field = 'AR_Hold_Reason ';
            } else if ($field == 'coder_rework_reason') {
                $field = 'AR_Rework_Reason';
            } else if ($field == 'coder_error_count') {
                $field = 'AR_Error_Count';
            } else if ($field == 'ar_status_code') {
                $field = 'Status_Code';
            } else if ($field == 'ar_action_code') {
                $field = 'Action_Code';
            } else if ($field == 'ar_denial_codes') {
                $field = 'Denial_Codes';
            } else if ($field == 'ar_substatus_codes') {
                $field = 'Substatus_Codes';
            } else if ($field == 'work_time') {
                $field = 'work_hours';
            }

            $headerField = ucwords(str_replace(['_else_', '_'], ['/', ' '], $field));
            return $headerField;
        }, $this->fields);

        // Remove any nulls (i.e., skipped fields)
        return array_filter($headings);
    }

    public function styles(Worksheet $sheet)
    {
        // Make the first row (headers) bold
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
