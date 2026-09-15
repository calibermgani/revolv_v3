<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Console\Commands\CleanupOldBulkReports;

class RunPythonReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 7200;

    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        try {
        // $python = 'C:\Users\Vijayalaxmi\AppData\Local\Programs\Python\Python313\python.exe';//local
        // $script = base_path('python\\Reports.py');//local
        $python = '/bin/python3';//server
        $script = base_path('Python/Reports.py');//server

            $process = new Process([$python, $script]);
            $process->setWorkingDirectory(base_path());
            $process->setInput(json_encode($this->payload));
            $process->setTimeout(7200);
            $process->run();

            $output = trim($process->getOutput());
            $error  = trim($process->getErrorOutput());

            Log::info("PY OUTPUT: " . $output);
            Log::error("PY ERROR: " . $error);
            Log::info("job_id: " . $this->payload['job_id']);

            if (!$process->isSuccessful()) {
                throw new \Exception($error);
            }
        // ✅ FIXED: use job_id instead of project_id
            Cache::put('report_' . $this->payload['job_id'], $output, 3600);
            if (!empty($this->payload['reuse_key']) && $output && file_exists($output)) {
                $reuseKey = 'report_reuse_' . $this->payload['reuse_key'];
                $previousPath = Cache::get($reuseKey);
                if (is_string($previousPath) && $previousPath !== $output) {
                    CleanupOldBulkReports::deleteFileIfExists($previousPath);
                }
                Cache::put($reuseKey, $output, 1200);
            }
            Log::info('Cache stored: report_' . $this->payload['job_id']);
        } catch (\Exception $e) {
            Log::error("Job Failed: " . $e->getMessage());
        } finally {
            if (!empty($this->payload['reuse_key'])) {
                Cache::forget('report_running_' . $this->payload['reuse_key']);
            }
        }
    }
}