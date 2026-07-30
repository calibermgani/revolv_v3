<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class RunQualityExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public array $payload;

    /*
     * Maximum number of attempts.
     */
    public int $tries = 1;

    /*
     * Laravel queue job timeout.
     */
    public int $timeout = 3600;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(): void
    {
        $jobId = $this->payload['job_id'];

        try {
            $scriptPath = base_path('Python/assignedTabExport.py');

            if (!file_exists($scriptPath)) {
                throw new \RuntimeException(
                    'Python report script was not found: ' .
                    $scriptPath
                );
            }

            /*
             * Configure this in .env when required:
             *
             * Windows:
             * PYTHON_EXECUTABLE=python
             *
             * Linux:
             * PYTHON_EXECUTABLE=/usr/bin/python3
             */
            $pythonExecutable = env(
                'PYTHON_EXECUTABLE',
                PHP_OS_FAMILY === 'Windows'
                    ? 'python'
                    : '/usr/bin/python3'
            );

            $process = new Process([
                $pythonExecutable,
                $scriptPath,
            ]);

            $process->setInput(
                json_encode(
                    $this->payload,
                    JSON_THROW_ON_ERROR
                )
            );

            /*
             * Python process timeout.
             */
            $process->setTimeout(3500);

            /*
             * Pass current Laravel database configuration.
             */
            $process->setEnv([
                'DB_HOST'     => config('database.connections.mysql.host'),
                'DB_PORT'     => (string) config('database.connections.mysql.port'),
                'DB_DATABASE' => config('database.connections.mysql.database'),
                'DB_USER'     => config('database.connections.mysql.username'),
                'DB_PASSWORD' => config('database.connections.mysql.password'),
            ]);

            Log::info('Starting quality Python export.', [
                'job_id'     => $jobId,
                'table_name' => $this->payload['table_name'],
            ]);

            $process->run();

            if (!$process->isSuccessful()) {
                throw new \RuntimeException(
                    trim($process->getErrorOutput())
                    ?: trim($process->getOutput())
                    ?: 'Python report generation failed.'
                );
            }

            /*
             * Python prints the absolute report path as its final output.
             */
            $outputLines = preg_split(
                '/\r\n|\r|\n/',
                trim($process->getOutput())
            );

            $filePath = trim(end($outputLines));

            if (!$filePath || !file_exists($filePath)) {
                throw new \RuntimeException(
                    'Python completed but the Excel file was not found.'
                );
            }

            Cache::put(
                'quality_report_' . $jobId,
                [
                    'status'  => 'completed',
                    'file'    => $filePath,
                    'message' => null,
                ],
                now()->addHours(3)
            );

            Log::info('Quality Python export completed.', [
                'job_id' => $jobId,
                'file'   => $filePath,
            ]);
        } catch (Throwable $e) {
            Cache::put(
                'quality_report_' . $jobId,
                [
                    'status'  => 'failed',
                    'file'    => null,
                    'message' => $e->getMessage(),
                ],
                now()->addHours(3)
            );

            Log::error('Quality Python export failed.', [
                'job_id'  => $jobId,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        $jobId = $this->payload['job_id'] ?? null;

        if (!$jobId) {
            return;
        }

        Cache::put(
            'quality_report_' . $jobId,
            [
                'status'  => 'failed',
                'file'    => null,
                'message' => $exception->getMessage(),
            ],
            now()->addHours(3)
        );
    }
}