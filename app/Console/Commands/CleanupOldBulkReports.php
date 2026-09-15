<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CleanupOldBulkReports extends Command
{
    protected $signature = 'reports:cleanup-old';

    protected $description = 'Delete leftover tmp download copies older than 20 minutes from storage/app/reports';

    public function handle()
    {
        $deleted = self::deleteExpiredTempCopies();
        $this->info("Deleted {$deleted} old temp file(s).");
        Log::info("Bulk report temp cleanup deleted {$deleted} file(s).");

        return 0;
    }

    public static function deleteFileIfExists($path): void
    {
        if (!is_string($path) || $path === '') {
            return;
        }
        $path = trim($path);
        if (is_file($path)) {
            if (!@unlink($path)) {
                Log::warning('Could not delete bulk report file: ' . $path);
            }
        }
    }

    public static function resolveReportPath($output)
    {
        $output = trim((string) $output);
        if ($output === '') {
            return null;
        }

        $lines = preg_split("/\r\n|\n|\r/", $output);
        $candidate = trim((string) end($lines));
        if ($candidate === '') {
            return null;
        }

        if (is_file($candidate)) {
            return $candidate;
        }

        $fromStorage = storage_path('app/reports/' . basename($candidate));
        if (is_file($fromStorage)) {
            return $fromStorage;
        }

        return null;
    }

    public static function deletePreviousProjectFiles($newPath): void
    {
        if (!$newPath || !is_file($newPath)) {
            return;
        }

        $filename = basename($newPath);
        if (!preg_match('/^(.+)_(\d{14})\.(zip|xlsx|csv)$/i', $filename, $matches)) {
            return;
        }

        $prefix = $matches[1];
        $directory = storage_path('app/reports');
        if (!File::isDirectory($directory)) {
            return;
        }

        foreach (File::files($directory) as $file) {
            $name = $file->getFilename();
            if (strcasecmp($name, $filename) === 0) {
                continue;
            }
            if (!preg_match('/^' . preg_quote($prefix, '/') . '_\d{14}\.(zip|xlsx|csv)$/i', $name)) {
                continue;
            }

            try {
                File::delete($file->getPathname());
                Log::info('Deleted previous bulk report file: ' . $name);
            } catch (\Exception $e) {
                Log::warning('Could not delete previous bulk report file: ' . $file->getPathname());
            }
        }
    }

    public static function deletePreviousForCombination($reuseKey, $newPath): void
    {
        if ($reuseKey === null || $reuseKey === '' || !$newPath) {
            return;
        }

        self::deletePreviousProjectFiles($newPath);

        $dir = storage_path('app/reports/.reuse');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        $marker = $dir . DIRECTORY_SEPARATOR . preg_replace('/[^a-f0-9]/i', '', (string) $reuseKey);
        @file_put_contents($marker, $newPath);
    }

    public static function deleteExpiredTempCopies(): int
    {
        $directory = storage_path('app/reports');
        if (!File::isDirectory($directory)) {
            return 0;
        }

        $cutoff = time() - 1200;
        $deleted = 0;

        foreach (File::files($directory) as $file) {
            $name = $file->getFilename();
            if (!Str::startsWith($name, 'tmp_')) {
                continue;
            }
            if ($file->getMTime() >= $cutoff) {
                continue;
            }

            try {
                File::delete($file->getPathname());
                $deleted++;
            } catch (\Exception $e) {
                Log::warning('Could not delete old temp report file: ' . $file->getPathname());
            }
        }

        return $deleted;
    }
}
