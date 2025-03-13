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

        // Create the model file dynamically
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
        $modelFilePath = app_path("Models/{$modelName}.php");
        $modelTemplatePath = base_path('stubs/model_template.stub');
    
        // Ensure the "Models" directory exists
        if (!File::exists(app_path('Models'))) {
            File::makeDirectory(app_path('Models'), 0755, true, true);
        }
    
        // Read and modify the template
        if (File::exists($modelTemplatePath)) {
            $modelTemplate = File::get($modelTemplatePath);
            $modelTemplate = str_replace('{{MODEL_NAME}}', $modelName, $modelTemplate);
            $modelTemplate = str_replace('{{TABLE_PLACEHOLDER}}', $table, $modelTemplate);
            $modelTemplate = str_replace('{{SOFT_DELETES_PLACEHOLDER}}', $this->getSoftDeletesStatement(), $modelTemplate);
            $modelTemplate = str_replace('{{FILLABLE_COLUMNS_PLACEHOLDER}}', $this->getFillableColumnsStatement(), $modelTemplate);
    
            // Save the modified template as the actual model file
            File::put($modelFilePath, $modelTemplate);
        } else {
            throw new \Exception("Model template file not found: $modelTemplatePath");
        }
    
        // Run the Artisan command to make the model
        Artisan::call('make:model', [
            'name' => "App\\Models\\{$modelName}",
            '--no-interaction' => true,
        ]);
    }
    

    // Override the create method to prevent the default record insertion
    public static function create(array $attributes = [])
    {
        return parent::query()->create($attributes);
    }
    protected function getSoftDeletesStatement()
    {
        // Check if the table has 'deleted_at' column
        $hasDeletedAtColumn = in_array('deleted_at', $this->fillable);

        return $hasDeletedAtColumn ? 'use SoftDeletes;' : '';
    }

    protected function getFillableColumnsStatement()
    {
        return implode(', ', array_map(function ($column) {
            return "'{$column}'";
        }, $this->fillable));
    }

    public function refreshFillableFromTable()
    {
        $table = $this->getTable();
        $this->setFillableFromTable($table);
        $this->setGuardedFromFillable();
    }
}
