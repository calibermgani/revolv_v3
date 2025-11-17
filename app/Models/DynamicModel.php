<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DynamicModel extends Model
{
    /**
     * Constructor: Only initialize model data,
     * DO NOT create files or run shell commands here.
     */
    public function __construct($table = null)
    {
        parent::__construct();

        if ($table) {
            $this->setTable($table);
            $this->setFillableFromTable($table);
            $this->setGuardedFromFillable();
        }
    }

    /**
     * Build fillable fields from table.
     */
    protected function setFillableFromTable($table)
    {
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $this->fillable = $columns;
    }

    /**
     * Guard all except fillable.
     */
    protected function setGuardedFromFillable()
    {
        $this->guarded = array_diff($this->getFillable(), []);
    }

    // protected function createModelFile($table)
    // {
    //     $modelName = Str::studly($table);
    //     //$modelNamespace = "App\\Models\\{$modelName}";
    //     $modelNamespace = "App/Models/{$modelName}";
    //     $modelFilePath = app_path("Models/{$modelName}.php");
    //     $modelTemplatePath = base_path('stubs/model_template.stub');

    //     // Replace placeholders in the template
    //     $modelTemplate = File::get($modelTemplatePath);
    //     $modelTemplate = str_replace('{{MODEL_NAME}}', $modelName, $modelTemplate);
    //     $modelTemplate = str_replace('{{TABLE_PLACEHOLDER}}', $table, $modelTemplate);
    //     $modelTemplate = str_replace('{{SOFT_DELETES_PLACEHOLDER}}', $this->getSoftDeletesStatement(), $modelTemplate);
    //     $modelTemplate = str_replace('{{FILLABLE_COLUMNS_PLACEHOLDER}}', $this->getFillableColumnsStatement(), $modelTemplate);
    //     // ✅ Ensure the Models directory exists and is writable
    //         if (!File::exists(dirname($modelFilePath))) {
    //             File::makeDirectory(dirname($modelFilePath), 0777, true, true);
    //         }

    //         if (!is_writable(dirname($modelFilePath))) {
    //             chmod(dirname($modelFilePath), 0777);
    //         }
    


    //     // Save the modified template as the actual model file
    //     File::put($modelFilePath, $modelTemplate);
        

    //     // Load the created model class
    //     if (File::exists($modelFilePath)) {
    //         require_once $modelFilePath;
    //     }
    //     shell_exec("/usr/bin/php \var\www\html/revolv_v3/artisan make:model {$modelNamespace}");

    //     // Run the Artisan command to make the model
    //     // Artisan::call('make:model', [
    //     //     'name' => $modelNamespace,
    //     //     '--no-interaction' => true,
    //     // ]);
    // }
    public function createModelFile($table)
    {
        $modelName = Str::studly($table);
        $modelFilePath = app_path("Models/{$modelName}.php");
        $modelTemplatePath = base_path('stubs/model_template.stub');

        // Ensure Models directory exists
        if (!File::exists(dirname($modelFilePath))) {
            File::makeDirectory(dirname($modelFilePath), 0777, true, true);
        }

        // Load and replace template placeholders
        $modelTemplate = File::get($modelTemplatePath);
        $modelTemplate = str_replace('{{MODEL_NAME}}', $modelName, $modelTemplate);
        $modelTemplate = str_replace('{{TABLE_PLACEHOLDER}}', $table, $modelTemplate);
        $modelTemplate = str_replace('{{SOFT_DELETES_PLACEHOLDER}}', $this->getSoftDeletesStatement(), $modelTemplate);
        $modelTemplate = str_replace('{{FILLABLE_COLUMNS_PLACEHOLDER}}', $this->getFillableColumnsStatement(), $modelTemplate);

        // Write the model file
        File::put($modelFilePath, $modelTemplate);

        // Fix permissions
        try {
            @chmod($modelFilePath, 0666);
        } catch (\Exception $e) {
            Log::warning("Model file permission fix failed: " . $e->getMessage());
        }

        // Load the new model class immediately
        if (File::exists($modelFilePath)) {
            require_once $modelFilePath;
        }

        return $modelFilePath;
    }

    /**
     * Override create() so it works for dynamic tables.
     */
    public static function create(array $attributes = [])
    {
        return parent::query()->create($attributes);
    }

    /**
     * Determine if SoftDeletes should be included.
     */
    protected function getSoftDeletesStatement()
    {
        return in_array('deleted_at', $this->fillable)
            ? 'use SoftDeletes;'
            : '';
    }

    /**
     * Generate fillable column list for the model file.
     */
    protected function getFillableColumnsStatement()
    {
        return implode(', ', array_map(function ($column) {
            return "'{$column}'";
        }, $this->fillable));
    }

    /**
     * Refresh fillable list on demand.
     */
    public function refreshFillableFromTable()
    {
        $this->setFillableFromTable($this->getTable());
        $this->setGuardedFromFillable();
    }
}
