<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DynamicModel extends Model
{
    public function __construct($table)
    {
        parent::__construct();

        $this->setTable($table);
        $this->setFillableFromTable($table);
        $this->setGuardedFromFillable();

        // Update or create the model file dynamically
        $this->updateOrCreateModelFile($table);
    }

    protected function setFillableFromTable($table)
    {
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $this->fillable = $columns;
    }

    protected function setGuardedFromFillable()
    {
        $this->guarded = array_diff($this->getFillable(), $this->guarded);
    }

    /**
     * Creates or updates the model file in app/Models/
     */
    protected function updateOrCreateModelFile($table)
    {
        $modelName = Str::studly($table);
        $modelFilePath = app_path("Models/{$modelName}.php");

        // Ensure directory exists
        $targetDir = dirname($modelFilePath);
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0777, true);
        }

        // Fetch all columns
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $fillableArray = "protected \$fillable = [\n    '" . implode("',\n    '", $columns) . "'\n];";
        $softDeletesLine = in_array('deleted_at', $columns) ? "use Illuminate\\Database\\Eloquent\\SoftDeletes;\n\n    use SoftDeletes;" : '';

        // If file doesn’t exist, create a new one
        if (!File::exists($modelFilePath)) {
            $content = <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
{$softDeletesLine}

class {$modelName} extends Model
{
    {$fillableArray}
}
PHP;
            File::put($modelFilePath, $content);
            return;
        }

        // --- If file exists, update fillable ---
        try {
            $content = File::get($modelFilePath);

            // Update existing $fillable block
            if (preg_match('/protected\s+\$fillable\s*=\s*\[[^\]]*\];/m', $content)) {
                $content = preg_replace(
                    '/protected\s+\$fillable\s*=\s*\[[^\]]*\];/m',
                    $fillableArray,
                    $content
                );
            } else {
                // Insert if missing
                $content = preg_replace(
                    '/class\s+[A-Za-z_]+\s+extends\s+[A-Za-z_]+/',
                    "$0\n{\n    {$fillableArray}",
                    $content
                );
            }

            // Ensure SoftDeletes included if deleted_at exists
            if (in_array('deleted_at', $columns) && !str_contains($content, 'use SoftDeletes;')) {
                $content = preg_replace(
                    '/use Illuminate\\\\Database\\\\Eloquent\\\\Model;/',
                    "use Illuminate\\Database\\Eloquent\\Model;\nuse Illuminate\\Database\\Eloquent\\SoftDeletes;",
                    $content
                );

                $content = preg_replace(
                    '/class\s+[A-Za-z_]+\s+extends\s+[A-Za-z_]+\s*\{/',
                    "$0\n    use SoftDeletes;",
                    $content
                );
            }

            File::put($modelFilePath, $content);
            Log::info("✅ Model file updated successfully: $modelFilePath");
        } catch (\Exception $e) {
            Log::error("❌ Failed to update model file: " . $e->getMessage());
        }
    }

    /**
     * Refresh fillable fields dynamically from DB
     */
    public function refreshFillableFromTable()
    {
        $table = $this->getTable();
        $this->setFillableFromTable($table);
        $this->setGuardedFromFillable();
        $this->updateOrCreateModelFile($table);
    }
}
