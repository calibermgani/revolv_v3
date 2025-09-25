<?php

namespace App\Jobs;

use App\Models\ReportTracking;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ProcessProjectReport
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
            // Reconstruct the request
            $requestData = json_decode($reportTracking->request_data, true);
            $request = new Request($requestData);

            // Call controller method
            $controller = app(\App\Http\Controllers\Reports\ReportsController::class);
            $response = $controller->reportClientColumnsList($request);

            // Save report HTML/JSON
            Storage::put("reports/report_{$this->trackingId}.json", json_encode($response->getData(true)));

            // Update status
            $reportTracking->fetch_status = 'End';
            $reportTracking->save();

        } catch (\Exception $e) {
            Log::error("Error processing report {$this->trackingId}: ".$e->getMessage());
            $reportTracking->fetch_status = 'Error';
            $reportTracking->save();
        }
    }
}
