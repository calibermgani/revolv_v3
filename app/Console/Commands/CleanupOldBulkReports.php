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
        if (is_string($path) && $path !== '' && file_exists($path) && is_file($path)) {
            @unlink($path);
        }
    }

    public static function deletePreviousForCombination($reuseKey, $newPath): void
    {
        if ($reuseKey === null || $reuseKey === '' || !$newPath) {
            return;
        }

        $dir = storage_path('app/reports/.reuse');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        $marker = $dir . DIRECTORY_SEPARATOR . preg_replace('/[^a-f0-9]/i', '', (string) $reuseKey);
        $oldPath = null;
        if (is_file($marker)) {
            $oldPath = trim((string) @file_get_contents($marker));
        }

        if ($oldPath) {
            $oldReal = is_file($oldPath) ? realpath($oldPath) : $oldPath;
            $newReal = realpath($newPath) ?: $newPath;
            if ($oldReal !== $newReal) {
                self::deleteFileIfExists($oldPath);
                $sameFolderCopy = storage_path('app/reports/' . basename($oldPath));
                if ($sameFolderCopy !== $oldPath) {
                    self::deleteFileIfExists($sameFolderCopy);
                }
            }
        }

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
