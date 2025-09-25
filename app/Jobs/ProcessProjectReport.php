<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\ReportTracking;
use Illuminate\Http\Request;

class ProcessProjectReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $inputValues;
    protected $reportTracking;

    /**
     * Create a new job instance.
     */
    public function __construct(array $inputValues, ReportTracking $reportTracking)
    {
        $this->inputValues = $inputValues;
        $this->reportTracking = $reportTracking;
    }

    /**
     * Execute the job.
     */
public function handle()
{
    try {
        $controller = app(\App\Http\Controllers\Reports\ReportsController::class);

        // Wrap the array in a Request object
        $request = new Request($this->inputValues);

        $response = $controller->reportClientColumnsList($request);

        // Save HTML report to storage
        Storage::put("reports/report_{$this->reportTracking->id}.json", json_encode($response->getData(true)));

        $this->reportTracking->update(['fetch_status' => 'End']);
    } catch (\Exception $e) {
        $this->reportTracking->update(['fetch_status' => 'Error']);
        Log::error("Report processing failed: ".$e->getMessage());
    }
}
}
