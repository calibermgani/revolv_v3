<?php

namespace App\Jobs;

use App\Models\ReportTracking;
use App\Exports\BulkProdcutionExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Helper\Admin\Helpers as Helpers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class BulkProdcutionReport
{
    protected $trackingId;

    public function __construct($trackingId)
    {
        $this->trackingId = $trackingId;
    }

    public function handle()
    {
        $reportTracking = ReportTracking::find($this->trackingId);
        if (!$reportTracking) return;

        try {
            // Reconstruct request data
            $requestData = json_decode($reportTracking->request_data, true);

            $paProject = Helpers::projectName($requestData["project_id"]);
            $decodedClientName = $paProject ? $paProject->project_name : null;
            $decodedsubProjectName = $requestData["sub_project_id"] == null ? 'project' : (Helpers::subProjectName($requestData["project_id"], $requestData["sub_project_id"])->sub_project_name ?? null);

            $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)) . '_datas', '_');

            $columns = $requestData['checkedValues'] ?? [];
            if (isset($columns[0]) && $columns[0] === 'all') {
                $columns = array_diff($columns, ['all']);
            }
            $columns = Helpers::excludePopupNonVisiblePatientColumns($columns, $requestData["project_id"] ?? null, $requestData["sub_project_id"] ?? null);

            // Always include mandatory fields
            $columns[] = "caller_charts_work_logs.work_time";
            $columns[] = "caller_charts_work_logs.record_status";

            // Add optional columns if exist
            if (Schema::hasColumn($table_name, 'qa_cpt_trends')) $columns[] = 'qa_cpt_trends';
            if (Schema::hasColumn($table_name, 'qa_icd_trends')) $columns[] = 'qa_icd_trends';
            if (Schema::hasColumn($table_name, 'qa_modifiers')) $columns[] = 'qa_modifiers';

            // Generate Excel file name
            $fileName = "report_{$this->trackingId}.xlsx";
            $filePath = storage_path("app/reports/" . $fileName);
            try {
                // Export Excel directly
                Excel::store(new BulkProdcutionExport($requestData, $table_name, $columns), "reports/" . $fileName);

                // Update status
                $reportTracking->fetch_status = 'End';
                $reportTracking->report_file = $fileName; // optional: store file name
                $reportTracking->save();
            } catch (\Exception $e) {
                Log::error("Excel Export failed for report {$this->trackingId}: " . $e->getMessage());
                $reportTracking->fetch_status = 'Error';
                $reportTracking->save();
                return; // exit job
            }
        } catch (\Exception $e) {
            Log::error("Error processing report {$this->trackingId}: " . $e->getMessage());
            $reportTracking->fetch_status = 'Error';
            $reportTracking->save();
        }
    }
}
