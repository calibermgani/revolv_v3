<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DynamicModel extends Model
{
    public function __construct($table)
    {
        $this->setTable($table);
        $this->setFillableFromTable($table);
        $this->setGuardedFromFillable();

        // Create the model file dynamically (safe with fallback)
        $this->createModelFile($table);
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

    protected function createModelFile($table)
    {
        $modelName = Str::studly($table);
        $preferredPath = app_path('Models');
        $fallbackPath = storage_path('app/Models');

        // ✅ 1. Choose safe writable directory
        $targetDir = is_writable($preferredPath) ? $preferredPath : $fallbackPath;

        // ✅ 2. Create directory if not exists
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $modelFilePath = "{$targetDir}/{$modelName}.php";
        $modelTemplatePath = base_path('stubs/model_template.stub');

        if (!File::exists($modelTemplatePath)) {
            throw new \Exception("Model template not found: {$modelTemplatePath}");
        }

        // ✅ 3. Prepare model content
        $modelTemplate = File::get($modelTemplatePath);
        $modelTemplate = str_replace('{{MODEL_NAME}}', $modelName, $modelTemplate);
        $modelTemplate = str_replace('{{TABLE_PLACEHOLDER}}', $table, $modelTemplate);
        $modelTemplate = str_replace('{{SOFT_DELETES_PLACEHOLDER}}', $this->getSoftDeletesStatement(), $modelTemplate);
        $modelTemplate = str_replace('{{FILLABLE_COLUMNS_PLACEHOLDER}}', $this->getFillableColumnsStatement(), $modelTemplate);

        // ✅ 4. Write model file safely
        try {
            File::put($modelFilePath, $modelTemplate);
        } catch (\Exception $e) {
            throw new \Exception("Failed to write model file to {$modelFilePath}. Error: " . $e->getMessage());
        }
            dd([
            'modelFilePath' => $modelFilePath,
            'exists' => File::exists(dirname($modelFilePath)),
            'isWritable' => is_writable(dirname($modelFilePath)),
            'owner' => fileowner(dirname($modelFilePath)),
            'currentUser' => get_current_user(),
        ],File::exists($modelFilePath) && str_contains($targetDir, 'app/Models'));


        // ✅ 5. Load the created model class (only if inside app/Models)
        if (File::exists($modelFilePath) && str_contains($targetDir, 'app/Models')) {
            require_once $modelFilePath;
        }

        // ⚠️ Remove artisan shell_exec to avoid duplicate model creation
        // shell_exec("/usr/bin/php /var/www/html/revolv_v3/artisan make:model {$modelNamespace}");
    }

    // Override the create method to prevent the default record insertion
    public static function create(array $attributes = [])
    {
        return parent::query()->create($attributes);
    }

    protected function getSoftDeletesStatement()
    {
        $hasDeletedAtColumn = in_array('deleted_at', $this->fillable);
        return $hasDeletedAtColumn ? 'use SoftDeletes;' : '';
    }

    protected function getFillableColumnsStatement()
    {
        return implode(', ', array_map(fn($column) => "'{$column}'", $this->fillable));
    }

    public function refreshFillableFromTable()
    {
        $table = $this->getTable();
        $this->setFillableFromTable($table);
        $this->setGuardedFromFillable();
    }
}
