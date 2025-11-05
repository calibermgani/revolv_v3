<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DynamicModel extends Model
{
    protected $targetDir;

    public function __construct($table)
    {
        $this->setTable($table);
        $this->setFillableFromTable($table);
        $this->setGuardedFromFillable();

        // detect writable location
        $this->targetDir = is_writable(app_path('Models'))
            ? app_path('Models')
            : storage_path('app/Models');

        // create model file (always refresh)
        $this->createOrUpdateModelFile($table);
    }

    protected function setFillableFromTable($table)
    {
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $this->fillable = $columns;
    }

    protected function setGuardedFromFillable()
    {
        $this->guarded = [];
    }

    protected function createOrUpdateModelFile($table)
    {
        $modelName = Str::studly($table);
        $modelFilePath = "{$this->targetDir}/{$modelName}.php";
        $modelTemplatePath = base_path('stubs/model_template.stub');

        if (!File::exists($modelTemplatePath)) {
            throw new \Exception("Model template not found: {$modelTemplatePath}");
        }

        // ensure directory exists
        if (!File::exists($this->targetDir)) {
            File::makeDirectory($this->targetDir, 0755, true);
        }

        $modelTemplate = File::get($modelTemplatePath);
        $modelTemplate = str_replace('{{MODEL_NAME}}', $modelName, $modelTemplate);
        $modelTemplate = str_replace('{{TABLE_PLACEHOLDER}}', $table, $modelTemplate);
        $modelTemplate = str_replace('{{SOFT_DELETES_PLACEHOLDER}}', $this->getSoftDeletesStatement(), $modelTemplate);
        $modelTemplate = str_replace('{{FILLABLE_COLUMNS_PLACEHOLDER}}', $this->getFillableColumnsStatement(), $modelTemplate);

        // overwrite or create the file
        File::put($modelFilePath, $modelTemplate);
    }

    protected function getSoftDeletesStatement()
    {
        $hasDeletedAtColumn = in_array('deleted_at', $this->fillable);
        return $hasDeletedAtColumn ? 'use SoftDeletes;' : '';
    }

    protected function getFillableColumnsStatement()
    {
        return implode(', ', array_map(fn($col) => "'{$col}'", $this->fillable));
    }

    public function refreshFillableFromTable()
    {
        $this->setFillableFromTable($this->getTable());
        $this->setGuardedFromFillable();
        $this->createOrUpdateModelFile($this->getTable()); // ✅ rewrite model file with new fields
    }
}
