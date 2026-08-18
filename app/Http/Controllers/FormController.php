<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\subproject;
use App\Models\project;
use App\Models\formConfiguration;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Http\Helper\Admin\Helpers as Helpers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use App\Models\DynamicModel;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\UpdateDynamicModel;
use Symfony\Component\Process\Process;
use Throwable;
use Maatwebsite\Excel\Facades\Excel;

class FormController extends Controller
{
    public function formConfigurationList() {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $formConfiguration = formConfiguration::join(
                        'projects',
                        'form_configurations.project_id',
                        '=',
                        'projects.project_id'
                    )
                    ->where('projects.status', 'Active')
                    ->select(
                        'form_configurations.project_id',
                        'form_configurations.sub_project_id',
                        'form_configurations.project_type',
                        DB::raw(
                            'GROUP_CONCAT(form_configurations.label_name) AS label_names'
                        )
                    )
                    ->groupBy(
                        'form_configurations.project_id',
                        'form_configurations.sub_project_id',
                        'form_configurations.project_type'
                    )
                    ->get();
                return view('Form.formConfigList',compact('formConfiguration'));
            } catch (\Exception $e) {
                Log::error('Form configuration list fetch failed', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);
                return redirect()
                    ->back()
                    ->with('error', 'Unable to fetch form configurations.');
            }
        }
        return redirect('/');
    }
    public function formCreationIndex() {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
               return view('Form.formIndex');
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public static function getSubProjectList(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $existingSubProject = formConfiguration::where('project_id', $request->project_id)->groupBy(['project_id', 'sub_project_id'])
                ->pluck('sub_project_id')->toArray();
                // $data = subproject::where('project_id', $request->project_id)->pluck('sub_project_name', 'id')->prepend(trans('Select'), '')->toArray();
                $data = subproject::where('project_id', $request->project_id)->pluck('sub_project_name', 'sub_project_id')->toArray();
               $existingSubProjectWithDeltedAt = formConfiguration::onlyTrashed()
                    ->where('project_id', $request->project_id)
                    ->distinct()
                    ->pluck('sub_project_id')
                    ->toArray();
                return response()->json(["subProject" => $data, "existingSubProject" => $existingSubProject,"existingSubProjectWithDeltedAt" => $existingSubProjectWithDeltedAt]);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
                return response()->json(['error' => 'Unable to load sub projects.'], 500);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return redirect('/');
    }

    public function formConfigurationCloneStore(Request $request)
    {
        if (!Session::get('loginDetails') || !Session::get('loginDetails')['userInfo'] || Session::get('loginDetails')['userInfo']['user_id'] == null) {
            return redirect('/');
        }

        $request->validate([
            'project_id' => 'required',
            'source_sub_project_id' => 'required',
            'sub_project_id' => 'required',
        ]);

        if ((string) $request->source_sub_project_id === (string) $request->sub_project_id) {
            return redirect('/form_configuration_list' . '?parent=' . $request->parent . '&child=' . $request->child)
                ->with('error', 'Source and target sub project cannot be the same.');
        }

        $additionalLabelArray = [
            'AR Denial Codes',
            'AR SubStatus Codes',
            'Production Type',
        ];

        try {
            $targetExists = formConfiguration::where('project_id', $request->project_id)
                ->where('sub_project_id', $request->sub_project_id)
                ->exists();

            if ($targetExists) {
                return redirect('/form_configuration_list' . '?parent=' . $request->parent . '&child=' . $request->child)
                    ->with('error', 'Selected sub project already has a configuration.');
            }

            $sourceConfigs = formConfiguration::where('project_id', $request->project_id)
                ->where('sub_project_id', $request->source_sub_project_id)
                ->orderBy('id')
                ->get()
                ->filter(function ($row) use ($additionalLabelArray) {
                    return !in_array($row->label_name, $additionalLabelArray, true);
                })
                ->values();

            if ($sourceConfigs->isEmpty()) {
                return redirect('/form_configuration_list' . '?parent=' . $request->parent . '&child=' . $request->child)
                    ->with('error', 'No column configuration found to clone.');
            }

            $first = $sourceConfigs->first();
            $storePayload = [
                'project_id' => $request->project_id,
                'sub_project_id' => $request->sub_project_id,
                'project_type' => $first->project_type,
                'claim_type' => $first->claim_type,
                'label_name' => $sourceConfigs->pluck('label_name')->all(),
                'input_type' => $sourceConfigs->pluck('input_type')->all(),
                'options_name' => $sourceConfigs->pluck('options_name')->all(),
                'field_type' => $sourceConfigs->pluck('field_type')->all(),
                'field_type_1' => $sourceConfigs->pluck('field_type_1')->all(),
                'field_type_2' => $sourceConfigs->pluck('field_type_2')->all(),
                'field_type_3' => $sourceConfigs->pluck('field_type_3')->all(),
                'user_type' => $sourceConfigs->pluck('user_type')->all(),
                'input_type_editable' => $sourceConfigs->pluck('input_type_editable')->all(),
            ];

            $storeRequest = Request::create(
                url('form_configuration_store'),
                'POST',
                $storePayload
            );
            $storeRequest->query->set('parent', $request->query('parent'));
            $storeRequest->query->set('child', $request->query('child'));

            if ($first->project_type === null || $first->project_type === '') {
                $storeRequest->merge([
                    'clone_inventory_source_sub_project_id' => $request->source_sub_project_id,
                ]);
            }

            $storeRequest->merge([
                'clone_col_search_source_sub_project_id' => $request->source_sub_project_id,
            ]);

            return self::processFormConfigurationStore($storeRequest);
        } catch (\Exception $e) {
            Log::error('Form configuration clone store failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect('/form_configuration_list' . '?parent=' . $request->parent . '&child=' . $request->child)
                ->with('error', 'Unable to clone configuration.');
        }
    }

    public static function formConfigurationStore(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userInfo'] && Session::get('loginDetails')['userInfo']['user_id'] !=null) {
            return self::processFormConfigurationStore($request);
        }

        return redirect('/');
    }

    private static function processFormConfigurationStore(Request $request) {
            try {
                  DB::beginTransaction();
                 $data = $request->all();
                $additionalLabelArray = [
                    "AR Denial Codes",                    
                    "AR SubStatus Codes",
                    "Production Type"
                    // "Question Json",
                    //  "Scenario"
                ];
                $additionalInputTypeArray = [
                    "text",
                     "text",
                     "select"
                    //  "longtext",
                    //  "text"
                ];
                $additionalOptionsArray = [
                    null,
                    null,
                    "Calling,Non-Calling,Webportal"
                    // null,
                    // null
                ];
                 $additionalUserTypeArray = [
                    "3",
                     "3",
                     "3"
                    //  "3",
                    //  "3"
                ];
                $additionalInputTypeEditableArray = [
                    "1",
                    "1",
                    "1"
                    // "3",
                    // "3"

                ];
               
               
                $additionalFieldTypeArray = [
                    "editable",
                    "editable",
                    "editable"
                    // "non_editable",
                    // "non_editable"
                ];
                $additionalFieldType1Array = [
                    "single",
                    "single",
                    "single"
                    // "single",
                    // "single"
                ];
                $additionalFieldType2Array = [
                    "mandatory",
                    "non-mandatory",
                    "mandatory"
                    // "non-mandatory",
                    // "non-mandatory"
                ];
                $additionalFieldType3Array = [
                    "popup_non_visible",
                    "popup_non_visible",
                    "popup_visible"
                    // "popup_non_visible",
                    // "popup_non_visible"
                ];
                $data['label_name'] = array_merge($data['label_name'], $additionalLabelArray);
                $data['input_type'] = array_merge($data['input_type'], $additionalInputTypeArray);
                $data['options_name'] = array_merge($data['options_name'], $additionalOptionsArray);
                $data['field_type'] = array_merge($data['field_type'], $additionalFieldTypeArray);
                $data['field_type_1'] = array_merge($data['field_type_1'], $additionalFieldType1Array);
                $data['field_type_2'] = array_merge($data['field_type_2'], $additionalFieldType2Array);
                $data['field_type_3'] = array_merge($data['field_type_3'], $additionalFieldType3Array);
                $data['user_type'] = array_merge($data['user_type'], $additionalUserTypeArray);
                $data['input_type_editable'] = array_merge($data['input_type_editable'], $additionalInputTypeEditableArray);
                // $projectName = project::where('id',$data['project_id'])->first();
                // $subProjectArray = subproject::where('project_id',$data['project_id'])->where('id',$data['sub_project_id'])->first();
                $projectName = project::where('project_id',$data['project_id'])->first();
                $subProjectArray = $data['sub_project_id'] != null ? subproject::where('project_id',$data['project_id'])->where('sub_project_id',$data['sub_project_id'])->first() : $projectName;
                $columns = [];
                for($i=0;$i<count($data['label_name']);$i++) {
                    $requiredData['project_id'] = $data['project_id'];
                    $requiredData['sub_project_id'] = $data['sub_project_id'] != null ? $data['sub_project_id'] : NULL;
                    $requiredData['label_name'] = $data['label_name'][$i];
                    $requiredData['input_type'] = $data['input_type'][$i];
                    $requiredData['options_name'] = $data['options_name'][$i];
                    $requiredData['field_type'] = $data['field_type'][$i];
                    $requiredData['field_type_1'] = $data['field_type_1'][$i];
                    $requiredData['field_type_2'] = $data['field_type_2'][$i];
                    $requiredData['field_type_3'] = $data['field_type_3'][$i];
                    $requiredData['added_by'] = Session::get('loginDetails')['userInfo']['user_id'];
                    $requiredData['user_type'] = $data['user_type'][$i];
                    $requiredData['input_type_editable'] = $data['input_type_editable'][$i];
                    $requiredData['project_type'] = $data['project_type'];
                    $requiredData['claim_type'] = $data['claim_type'];
                    formConfiguration::create($requiredData);
                    // $columnName = Str::lower(str_replace([' ', '/'], ['_'], $data['label_name'][$i]));
                    $columnName = Str::lower(str_replace([' ', '/'], ['_', '_else_'], $data['label_name'][$i]));
                    if ($data['input_type'][$i] == 'text' || $data['input_type'][$i] == 'date_range') {
                        $columns[$columnName] = 'TEXT';
                    } else if ($data['input_type'][$i] == 'select' || $data['input_type'][$i] == 'checkbox' || $data['input_type'][$i] == 'radio') {
                        $enumValues = "'" . implode("','", explode(',',$data['options_name'][$i])) . "'";
                        // $columns[$columnName] = "ENUM($enumValues)";
                        if (!empty($data['options_name'][$i])) {
                            $enumValues = "'" . implode("','", explode(',',$data['options_name'][$i])) . "'";
                            $columns[$columnName] = "ENUM($enumValues)";
                        } else {
                            $columns[$columnName] = "TEXT";
                        }
                    } else if ($data['input_type'][$i] == 'date') {
                        $columns[$columnName] = 'DATE';
                    } else if ($data['input_type'][$i] == 'textarea') {
                        $columns[$columnName] = 'TEXT';
                    } else if ($data['input_type'][$i] == 'datetime' ) {
                        $columns[$columnName] = 'DATETIME';
                    } else if ($data['input_type'][$i] == 'longtext' ) {
                            $columns[$columnName] = 'longtext';
                    }  
                }
                $subProjectName = $data['sub_project_id'] != null ? $subProjectArray->sub_project_name : 'project';
                $tableName = Str::slug(($projectName->project_name.'_'.$subProjectName),'_');
                $tableDataName = Str::slug(($projectName->project_name.'_'.$subProjectName. '_datas'),'_');
                $duplicateTableName = Str::slug(($projectName->project_name . '_' . $subProjectName . '_duplicates'),'_');
                $tableHistoryName =Str::slug(($projectName->project_name.'_'.$subProjectName. '_history'),'_');
                $tableRevokeHistoryName =Str::slug(($projectName->project_name.'_'.$subProjectName. '_revoke_history'),'_');
                $tableExists = DB::select("SHOW TABLES LIKE '$tableName'");
                    if (empty($tableExists)) {
                        $createTableSQL = "CREATE TABLE $tableName (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createTableSQL .= ", $columnName $columnType";
                        }

                        $createTableSQL .= ", invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','AR_non_workable','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke','Rebuttal','Auto_Close') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            qa_classification VARCHAR(255) NULL,
                                            qa_category VARCHAR(255) NULL,
                                            qa_scope VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            annex_coder_trends TEXT NULL,
                                            annex_qa_trends TEXT NULL,
                                            cpt_trends TEXT NULL,
                                            icd_trends TEXT NULL,
                                            modifiers TEXT NULL,
                                            QA_comments_count VARCHAR(255) NULL,
                                            coder_work_date DATE NULL,
                                            qa_work_date DATE NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            ar_status_code VARCHAR(255) NULL,
                                            ar_action_code VARCHAR(255) NULL,
                                            ar_manager_rebuttal_status TEXT NULL,
                                            ar_manager_rebuttal_comments TEXT NULL,
                                            qa_manager_rebuttal_status TEXT NULL,
                                            qa_manager_rebuttal_comments TEXT NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";//dd($createTableSQL);
                        DB::statement($createTableSQL);
                        $dynamicModel = new DynamicModel($tableName);
                        $dynamicModel->createModelFile($tableName);
                    } else {
                        $afterColumn = 'created_at';
                        foreach ($columns as $columnName => $columnType) {
                            $columnExists = DB::select("
                                SELECT COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = '$tableName'
                                AND COLUMN_NAME = '$columnName'
                            ");
                            if (empty($columnExists)) {

                                DB::statement("ALTER TABLE $tableName ADD COLUMN $columnName $columnType AFTER $afterColumn");
                                $dynamicModel = new DynamicModel($tableName);
                                $dynamicModel->refreshFillableFromTable();
                            }
                        }
                    }
                    $duplicateTableExists = DB::select("SHOW TABLES LIKE '$duplicateTableName'");

                    if (empty($duplicateTableExists)) {
                        $createDuplicateTableSQL = "CREATE TABLE $duplicateTableName (id INT AUTO_INCREMENT PRIMARY KEY";

                        foreach ($columns as $columnName => $columnType) {
                            $createDuplicateTableSQL .= ", $columnName $columnType";
                        }

                        $createDuplicateTableSQL .= ", invoke_date DATE NULL,
                                                    CE_emp_id VARCHAR(255) NULL,
                                                    QA_emp_id VARCHAR(255) NULL,
                                                chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','AR_non_workable','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke','Rebuttal','Auto_Close') DEFAULT 'CE_Assigned',
                                                    duplicate_status VARCHAR(255) NULL,
                                                    ce_hold_reason TEXT NULL,
                                                    qa_hold_reason TEXT NULL,
                                                    qa_work_status VARCHAR(255) NULL,
                                                    QA_required_sampling VARCHAR(255) NULL,
                                                    QA_rework_comments TEXT NULL,
                                                    QA_status_code VARCHAR(255) NULL,
                                                    QA_sub_status_code VARCHAR(255) NULL,
                                                    qa_classification VARCHAR(255) NULL,
                                                    qa_category VARCHAR(255) NULL,
                                                    qa_scope VARCHAR(255) NULL,
                                                    QA_followup_date DATE NULL,
                                                    CE_status_code VARCHAR(255) NULL,
                                                    CE_sub_status_code VARCHAR(255) NULL,
                                                    CE_followup_date DATE NULL,
                                                    annex_coder_trends TEXT NULL,
                                                    annex_qa_trends TEXT NULL,
                                                    cpt_trends TEXT NULL,
                                                    icd_trends TEXT NULL,
                                                    modifiers TEXT NULL,
                                                    QA_comments_count VARCHAR(255) NULL,
                                                    coder_work_date DATE NULL,
                                                    qa_work_date DATE NULL,
                                                    coder_rework_status VARCHAR(255) NULL,
                                                    coder_rework_reason TEXT NULL,
                                                    coder_error_count VARCHAR(255) NULL,
                                                    qa_error_count VARCHAR(255) NULL,
                                                    tl_error_count VARCHAR(255) NULL,
                                                    tl_comments TEXT NULL,
                                                    ar_status_code VARCHAR(255) NULL,
                                                    ar_action_code VARCHAR(255) NULL,
                                                    ar_manager_rebuttal_status TEXT NULL,
                                                    ar_manager_rebuttal_comments TEXT NULL,
                                                    qa_manager_rebuttal_status TEXT NULL,
                                                    qa_manager_rebuttal_comments TEXT NULL,
                                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                    updated_at TIMESTAMP NULL,
                                                    deleted_at TIMESTAMP NULL)";
                        DB::statement($createDuplicateTableSQL);
                        $dynamicDuplicateModel = new DynamicModel($duplicateTableName);
                        $dynamicDuplicateModel->createModelFile($duplicateTableName);
                    }  else {
                        $afterColumn = 'created_at';
                        foreach ($columns as $columnName => $columnType) {
                            $columnExists = DB::select("
                                SELECT COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = '$duplicateTableName'
                                AND COLUMN_NAME = '$columnName'
                            ");
                            if (empty($columnExists)) {

                                DB::statement("ALTER TABLE $duplicateTableName ADD COLUMN $columnName $columnType AFTER $afterColumn");
                                $dynamicDuplicateModel = new DynamicModel($duplicateTableName);
                                $dynamicDuplicateModel->refreshFillableFromTable();
                            }
                        }
                    }

                    $tableDatasExists = DB::select("SHOW TABLES LIKE '$tableDataName'");
                    if (empty($tableDatasExists)) {
                        $createDataTableSQL = "CREATE TABLE $tableDataName (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createDataTableSQL .= ", $columnName TEXT";
                        }

                        $createDataTableSQL .= ", parent_id INT NULL,invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','AR_non_workable','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke','Rebuttal','Auto_Close') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            qa_classification VARCHAR(255) NULL,
                                            qa_category VARCHAR(255) NULL,
                                            qa_scope VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            annex_coder_trends TEXT NULL,
                                            annex_qa_trends TEXT NULL,
                                            cpt_trends TEXT NULL,
                                            icd_trends TEXT NULL,
                                            modifiers TEXT NULL,
                                            QA_comments_count VARCHAR(255) NULL,
                                            coder_work_date DATE NULL,
                                            qa_work_date DATE NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            ar_status_code VARCHAR(255) NULL,
                                            ar_action_code VARCHAR(255) NULL,
                                            ar_manager_rebuttal_status TEXT NULL,
                                            ar_manager_rebuttal_comments TEXT NULL,
                                            qa_manager_rebuttal_status TEXT NULL,
                                            qa_manager_rebuttal_comments TEXT NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";
                        DB::statement($createDataTableSQL);
                        $dynamicModel = new DynamicModel($tableDataName);
                         $dynamicModel->createModelFile($tableDataName);
                    } else {
                        $afterColumn = 'created_at';
                        foreach ($columns as $columnName => $columnType) {
                            $columnExists = DB::select("
                                SELECT COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = '$tableDataName'
                                AND COLUMN_NAME = '$columnName'
                            ");
                            if (empty($columnExists)) {

                                DB::statement("ALTER TABLE $tableDataName ADD COLUMN $columnName $columnType AFTER $afterColumn");
                                $dynamicModel = new DynamicModel($tableDataName);
                                $dynamicModel->refreshFillableFromTable();
                            }
                        }
                    }

                    $tableHistoryExists = DB::select("SHOW TABLES LIKE '$tableHistoryName'");
                    if (empty($tableHistoryExists)) {
                        $createTableHistorySQL = "CREATE TABLE $tableHistoryName (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createTableHistorySQL .= ", $columnName TEXT";
                        }

                        $createTableHistorySQL .= ", parent_id INT NULL,invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','AR_non_workable','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke','Rebuttal','Auto_Close') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            qa_classification VARCHAR(255) NULL,
                                            qa_category VARCHAR(255) NULL,
                                            qa_scope VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            annex_coder_trends TEXT NULL,
                                            annex_qa_trends TEXT NULL,
                                            cpt_trends TEXT NULL,
                                            icd_trends TEXT NULL,
                                            modifiers TEXT NULL,
                                            QA_comments_count VARCHAR(255) NULL,
                                            coder_work_date DATE NULL,
                                            qa_work_date DATE NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            ar_status_code VARCHAR(255) NULL,
                                            ar_action_code VARCHAR(255) NULL,
                                            ar_manager_rebuttal_status TEXT NULL,
                                            ar_manager_rebuttal_comments TEXT NULL,
                                            qa_manager_rebuttal_status TEXT NULL,
                                            qa_manager_rebuttal_comments TEXT NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";
                        DB::statement($createTableHistorySQL);
                        $dynamicModel = new DynamicModel($tableHistoryName);
                        $dynamicModel->createModelFile($tableHistoryName);
                    } else {
                        $afterColumn = 'created_at';
                        foreach ($columns as $columnName => $columnType) {
                            $columnExists = DB::select("
                                SELECT COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = '$tableHistoryName'
                                AND COLUMN_NAME = '$columnName'
                            ");
                            if (empty($columnExists)) {

                                DB::statement("ALTER TABLE $tableHistoryName ADD COLUMN $columnName $columnType AFTER $afterColumn");
                                $dynamicModel = new DynamicModel($tableHistoryName);
                                $dynamicModel->refreshFillableFromTable();
                            }
                        }
                    }

                    $tableRevokeHistoryExists = DB::select("SHOW TABLES LIKE '$tableRevokeHistoryName'");
                    if (empty($tableRevokeHistoryExists)) {
                        $createRevokeTableSQL = "CREATE TABLE $tableRevokeHistoryName (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createRevokeTableSQL .= ", $columnName TEXT";
                        }

                        $createRevokeTableSQL .= ", parent_id INT NULL,invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','AR_non_workable','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke','Rebuttal','Auto_Close') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            qa_classification VARCHAR(255) NULL,
                                            qa_category VARCHAR(255) NULL,
                                            qa_scope VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            annex_coder_trends TEXT NULL,
                                            annex_qa_trends TEXT NULL,
                                            cpt_trends TEXT NULL,
                                            icd_trends TEXT NULL,
                                            modifiers TEXT NULL,
                                            QA_comments_count VARCHAR(255) NULL,
                                            coder_work_date DATE NULL,
                                            qa_work_date DATE NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            ar_status_code VARCHAR(255) NULL,
                                            ar_action_code VARCHAR(255) NULL,
                                            ar_manager_rebuttal_status TEXT NULL,
                                            ar_manager_rebuttal_comments TEXT NULL,
                                            qa_manager_rebuttal_status TEXT NULL,
                                            qa_manager_rebuttal_comments TEXT NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";
                        DB::statement($createRevokeTableSQL);
                        $dynamicModel = new DynamicModel($tableRevokeHistoryName);
                         $dynamicModel->createModelFile($tableRevokeHistoryName);
                    } else {
                        $afterColumn = 'created_at';
                        foreach ($columns as $columnName => $columnType) {
                            $columnExists = DB::select("
                                SELECT COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = '$tableRevokeHistoryName'
                                AND COLUMN_NAME = '$columnName'
                            ");
                            if (empty($columnExists)) {

                                DB::statement("ALTER TABLE $tableRevokeHistoryName ADD COLUMN $columnName $columnType AFTER $afterColumn");
                                $dynamicModel = new DynamicModel($tableRevokeHistoryName);
                                $dynamicModel->refreshFillableFromTable();
                            }
                        }
                    }
                    //  if (DB::transactionLevel() > 0) {
                    //     DB::commit();
                    // }
                    if ($request->filled('clone_inventory_source_sub_project_id')) {
                        self::cloneInventoryUploadConfiguration(
                            $data['project_id'],
                            $request->clone_inventory_source_sub_project_id,
                            $data['sub_project_id']
                        );
                    }
                    if ($request->filled('clone_col_search_source_sub_project_id')) {
                        self::cloneProjectColSearchConfigs(
                            $data['project_id'],
                            $request->clone_col_search_source_sub_project_id,
                            $data['sub_project_id']
                        );
                    }
                    if (DB::getPdo()->inTransaction()) {
                        DB::commit();
                    }
                    Helpers::clearConfigMap();
                    return redirect('/form_configuration_list' . '?parent=' . request()->parent . '&child=' . request()->child);
            } catch (\Exception $e) {
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
                return response()->json(['error' => $e->getMessage()], 500);
                Log::debug($e->getMessage());
            }
    }
    public function formEdit($project_id,$sub_project_id) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $projectId = Helpers::encodeAndDecodeID($project_id,'decode');
                $subProjectId = $sub_project_id == '--' ? '--' :Helpers::encodeAndDecodeID($sub_project_id,'decode');
                if($sub_project_id != '--') {
                    $projectDetails = formConfiguration::groupBy(['project_id', 'sub_project_id','project_type','claim_type'])
                    ->where('project_id',$projectId)->where('sub_project_id',$subProjectId)
                    ->select('project_id', 'sub_project_id','project_type','claim_type')
                    ->first();
                    $formDetails = formConfiguration::where('project_id',$projectId)->where('sub_project_id',$subProjectId)
                    ->get();
                } else {
                    $projectDetails = formConfiguration::groupBy(['project_id', 'sub_project_id','project_type','claim_type'])
                    ->where('project_id',$projectId)
                    ->select('project_id', 'sub_project_id','project_type','claim_type')
                    ->first();
                    $formDetails = formConfiguration::where('project_id',$projectId)
                    ->get();
                }
               return view('Form.formEdit',compact('projectDetails','formDetails'));
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public static function formConfigurationUpdate(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userInfo'] && Session::get('loginDetails')['userInfo']['user_id'] !=null) {
            
            try {
                DB::beginTransaction();
                $data = $request->all();
                $additionalLabelArray = [
                        "AR Denial Codes",                    
                        "AR SubStatus Codes",
                        "Production Type",
                        "Question Json",
                        "Scenario"
                    ];
                    $additionalInputTypeArray = [
                        "text",
                        "text",
                        "select",
                        "longtext",
                        "text"
                    ];
                    $additionalOptionsArray = [
                        null,
                        null,
                        "Calling,Non-Calling,Webportal",
                        null,
                        null
                    ];
                    $additionalUserTypeArray = [
                        "3",
                        "3",
                        "3",
                        "3",
                        "3"
                    ];
                    $additionalInputTypeEditableArray = [
                        "1",
                        "1",
                        "1",
                        "3",
                        "3"

                    ];
                
                
                    $additionalFieldTypeArray = [
                        "editable",
                        "editable",
                        "editable",
                        "non_editable",
                        "non_editable"
                    ];
                    $additionalFieldType1Array = [
                        "single",
                        "single",
                        "single",
                        "single",
                        "single"
                    ];
                    $additionalFieldType2Array = [
                        "mandatory",
                        "non-mandatory",
                        "mandatory",
                        "non-mandatory",
                        "non-mandatory"
                    ];
                    $additionalFieldType3Array = [
                        "popup_non_visible",
                        "popup_non_visible",
                        "popup_visible",
                        "popup_non_visible",
                        "popup_non_visible"
                    ];
                $data['label_name'] = array_merge($data['label_name'], $additionalLabelArray);
                $data['input_type'] = array_merge($data['input_type_val'], $additionalInputTypeArray);
                $data['options_name'] = array_merge($data['options_name'], $additionalOptionsArray);
                $data['field_type'] = array_merge($data['field_type'], $additionalFieldTypeArray);
                $data['field_type_1'] = array_merge($data['field_type_1'], $additionalFieldType1Array);
                $data['field_type_2'] = array_merge($data['field_type_2'], $additionalFieldType2Array);
                $data['field_type_3'] = array_merge($data['field_type_3'], $additionalFieldType3Array);
                $data['user_type'] = array_merge($data['user_type'], $additionalUserTypeArray);
                $data['input_type_editable'] = array_merge($data['input_type_editable'], $additionalInputTypeEditableArray);
                // $projectName = project::where('id',$data['project_id_val'])->first();
                // $subProjectArray = subproject::where('project_id',$data['project_id_val'])->where('id',$data['sub_project_id_val'])->first();
                $projectName = project::where('project_id',$data['project_id_val'])->first();
                $subProjectArray = $data['sub_project_id_val'] != null ? subproject::where('project_id',$data['project_id_val'])->where('sub_project_id',$data['sub_project_id_val'])->first() : $projectName;

                $columns = [];
                for($i=0;$i<count($data['label_name']);$i++) {
                    $existingRecord = $data['sub_project_id_val'] != null ? formConfiguration::where('project_id',$data['project_id_val'])->where('sub_project_id',$data['sub_project_id_val'])->where('label_name',$data['label_name'][$i])->first() : formConfiguration::where('project_id',$data['project_id_val'])->where('label_name',$data['label_name'][$i])->first();
                    if($existingRecord)
                    {
                        $requiredData['project_id'] = $data['project_id_val'];
                        $requiredData['sub_project_id'] = $data['sub_project_id_val'] != null ? $data['sub_project_id_val'] : NULL;
                        $requiredData['label_name'] = $data['label_name'][$i];
                        $requiredData['options_name'] = $data['options_name'][$i];
                        $requiredData['field_type'] = $data['field_type'][$i];
                        $requiredData['field_type_1'] = $data['field_type_1'][$i];
                        $requiredData['field_type_2'] = $data['field_type_2'][$i];
                        $requiredData['field_type_3'] = $data['field_type_3'][$i];
                        $requiredData['added_by'] = Session::get('loginDetails')['userInfo']['user_id'];//dd($existingRecord,$requiredData);
                        $requiredData['user_type'] = $data['user_type'][$i];
                        $requiredData['input_type_editable'] = $data['input_type_editable'][$i];
                        $requiredData['project_type'] = $data['project_type_val'];
                        $requiredData['claim_type'] = $data['claim_type_val'];
                        $existingRecord->update($requiredData);
                    } else {
                        $requiredData['project_id'] = $data['project_id_val'];
                        $requiredData['sub_project_id'] = $data['sub_project_id_val'] != null ? $data['sub_project_id_val'] : NULL;
                        $requiredData['label_name'] = $data['label_name'][$i];
                        $requiredData['input_type'] = $data['input_type'][$i];
                        $requiredData['options_name'] = $data['options_name'][$i];
                        $requiredData['field_type'] = $data['field_type'][$i];
                        $requiredData['field_type_1'] = $data['field_type_1'][$i];
                        $requiredData['field_type_2'] = $data['field_type_2'][$i];
                        $requiredData['field_type_3'] = $data['field_type_3'][$i];
                        $requiredData['added_by'] = Session::get('loginDetails')['userInfo']['user_id'];
                        $requiredData['user_type'] = $data['user_type'][$i];
                        $requiredData['input_type_editable'] = $data['input_type_editable'][$i];
                        $requiredData['project_type'] = $data['project_type_val'];
                        $requiredData['claim_type'] = $data['claim_type_val'];
                        formConfiguration::create($requiredData);
                       // $columnName = Str::lower(str_replace([' ', '/'], '_', $data['label_name'][$i]));
                        $columnName = Str::lower(str_replace([' ', '/'], ['_', '_else_'], $data['label_name'][$i]));
                        if ($data['input_type'][$i] == 'text' || $data['input_type'][$i] == 'date_range') {
                            $columns[$columnName] = 'TEXT';
                        } else if ($data['input_type'][$i] == 'select' || $data['input_type'][$i] == 'checkbox' || $data['input_type'][$i] == 'radio') {
                              //$enumValues = "'" . implode("','", explode(',',$data['options_name'][$i])) . "'";
                            //$columns[$columnName] = "ENUM($enumValues)";
                            if (!empty($data['options_name'][$i])) {
                                $options = array_map(function ($opt) {
                                    return str_replace("'", "''", trim($opt));
                                }, explode(',', $data['options_name'][$i]));
                                $enumValues = "'" . implode("','", $options) . "'";
                                $columns[$columnName] = "ENUM($enumValues)";
                            } else {
                                $columns[$columnName] = "TEXT";
                            }
                           
                        } else if ($data['input_type'][$i] == 'date') {
                            $columns[$columnName] = 'DATE';
                        } else if ($data['input_type'][$i] == 'textarea') {
                            $columns[$columnName] = 'TEXT';
                        } else if ($data['input_type'][$i] == 'datetime' ) {
                            $columns[$columnName] = 'DATETIME';
                        } else if ($data['input_type'][$i] == 'longtext' ) {
                            $columns[$columnName] = 'longtext';
                        }  
                    }

                }
                $subProjectName = $data['sub_project_id_val'] != null ? $subProjectArray->sub_project_name : 'project';
                $tableName = Str::slug(($projectName->project_name.'_'.$subProjectName),'_');
                $tableDataName =Str::slug($projectName->project_name.'_'.$subProjectName. '_datas','_');
                $duplicateTableName = Str::slug($projectName->project_name . '_' . $subProjectName . '_duplicates','_');
                $tableHistoryName = Str::slug($projectName->project_name.'_'.$subProjectName. '_history','_');
                $tableRevokeHistoryName =Str::slug(($projectName->project_name.'_'.$subProjectName. '_revoke_history'),'_');
                // new DynamicModel($tableName);
                // new DynamicModel($duplicateTableName);
                // new DynamicModel($tableDataName);
                // new DynamicModel($tableHistoryName);
                // new DynamicModel($tableRevokeHistoryName);
                $tableExists = DB::select("SHOW TABLES LIKE '$tableName'");
                    if (empty($tableExists)) {
                        $createTableSQL = "CREATE TABLE `$tableName` (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createTableSQL .= ", `$columnName` $columnType";
                        }

                        $createTableSQL .= ", parent_id INT NULL,invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','AR_non_workable','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke','Rebuttal','Auto_Close') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            qa_classification VARCHAR(255) NULL,
                                            qa_category VARCHAR(255) NULL,
                                            qa_scope VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            annex_coder_trends TEXT NULL,
                                            annex_qa_trends TEXT NULL,
                                            cpt_trends TEXT NULL,
                                            icd_trends TEXT NULL,
                                            modifiers TEXT NULL,
                                            QA_comments_count VARCHAR(255) NULL,
                                            coder_work_date DATE NULL,
                                            qa_work_date DATE NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            ar_status_code VARCHAR(255) NULL,
                                            ar_action_code VARCHAR(255) NULL,
                                            ar_manager_rebuttal_status TEXT NULL,
                                            ar_manager_rebuttal_comments TEXT NULL,
                                            qa_manager_rebuttal_status TEXT NULL,
                                            qa_manager_rebuttal_comments TEXT NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";
                        try {
                            Log::debug("Executing SQL: " . $createTableSQL);
                            DB::statement($createTableSQL);
                        } catch (\Exception $e) {
                            Log::error("SQL failed: " . $createTableSQL);
                            Log::error($e->getMessage());
                            throw $e;
                        }
                        $dynamicModel = new DynamicModel($tableName);
                    } else {
                        $afterColumn = 'created_at';
                        foreach ($columns as $columnName => $columnType) {
                            $columnExists = DB::select("
                                SELECT COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = '$tableName'
                                AND COLUMN_NAME = '$columnName'
                            ");
                            if (empty($columnExists)) {
                                //DB::statement("ALTER TABLE $tableName ADD COLUMN $columnName $columnType AFTER $afterColumn");
                                DB::statement("ALTER TABLE `$tableName` ADD COLUMN `$columnName` $columnType AFTER `$afterColumn`");
                                $dynamicModel = new UpdateDynamicModel($tableName);
                                $dynamicModel->refreshFillableFromTable();
                            }
                        }
                    }
                    $duplicateTableExists = DB::select("SHOW TABLES LIKE '$duplicateTableName'");

                    if (empty($duplicateTableExists)) {
                        $createDuplicateTableSQL = "CREATE TABLE `$duplicateTableName` (id INT AUTO_INCREMENT PRIMARY KEY";

                        foreach ($columns as $columnName => $columnType) {
                            $createDuplicateTableSQL .= ", `$columnName` TEXT";
                        }

                        $createDuplicateTableSQL .= ", invoke_date DATE NULL,
                                                    CE_emp_id VARCHAR(255) NULL,
                                                    QA_emp_id VARCHAR(255) NULL,
                                                    chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','AR_non_workable','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke','Rebuttal','Auto_Close') DEFAULT 'CE_Assigned',
                                                    duplicate_status VARCHAR(255) NULL,
                                                    ce_hold_reason TEXT NULL,
                                                    qa_hold_reason TEXT NULL,
                                                    qa_work_status VARCHAR(255) NULL,
                                                    QA_required_sampling VARCHAR(255) NULL,
                                                    QA_rework_comments TEXT NULL,
                                                    QA_status_code VARCHAR(255) NULL,
                                                    QA_sub_status_code VARCHAR(255) NULL,
                                                    qa_classification VARCHAR(255) NULL,
                                                    qa_category VARCHAR(255) NULL,
                                                    qa_scope VARCHAR(255) NULL,
                                                    QA_followup_date DATE NULL,
                                                    CE_status_code VARCHAR(255) NULL,
                                                    CE_sub_status_code VARCHAR(255) NULL,
                                                    CE_followup_date DATE NULL,
                                                    annex_coder_trends TEXT NULL,
                                                    annex_qa_trends TEXT NULL,
                                                    cpt_trends TEXT NULL,
                                                    icd_trends TEXT NULL,
                                                    modifiers TEXT NULL,
                                                    QA_comments_count VARCHAR(255) NULL,
                                                    coder_work_date DATE NULL,
                                                    qa_work_date DATE NULL,
                                                    coder_rework_status VARCHAR(255) NULL,
                                                    coder_rework_reason TEXT NULL,
                                                    coder_error_count VARCHAR(255) NULL,
                                                    qa_error_count VARCHAR(255) NULL,
                                                    tl_error_count VARCHAR(255) NULL,
                                                    tl_comments TEXT NULL,
                                                    ar_status_code VARCHAR(255) NULL,
                                                    ar_action_code VARCHAR(255) NULL,
                                                    ar_manager_rebuttal_status TEXT NULL,
                                                    ar_manager_rebuttal_comments TEXT NULL,
                                                    qa_manager_rebuttal_status TEXT NULL,
                                                    qa_manager_rebuttal_comments TEXT NULL,
                                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                    updated_at TIMESTAMP NULL,
                                                    deleted_at TIMESTAMP NULL)";
                        //DB::statement($createDuplicateTableSQL);
                        try {
                            Log::debug("Executing SQL: " . $createDuplicateTableSQL);
                            DB::statement($createDuplicateTableSQL);
                        } catch (\Exception $e) {
                            Log::error("SQL failed: " . $createDuplicateTableSQL);
                            Log::error($e->getMessage());
                            throw $e;
                        }
                        $dynamicDuplicateModel = new DynamicModel($duplicateTableName);
                    }  else {
                        $afterColumn = 'created_at';
                        foreach ($columns as $columnName => $columnType) {
                            $duplicateColumnExists = DB::select("
                                SELECT COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = '$duplicateTableName'
                                AND COLUMN_NAME = '$columnName'
                            ");
                            if (empty($duplicateColumnExists)) {
                                DB::statement("ALTER TABLE `$duplicateTableName` ADD COLUMN `$columnName` $columnType AFTER `$afterColumn`");
                                $dynamicDuplicateModel = new UpdateDynamicModel($duplicateTableName);
                                $dynamicDuplicateModel->refreshFillableFromTable();
                            }
                        }
                    }

                    $tableDatasExists = DB::select("SHOW TABLES LIKE '$tableDataName'");
                    if (empty($tableDatasExists)) {
                        $createDataTableSQL = "CREATE TABLE `$tableDataName` (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createDataTableSQL .= ", `$columnName` TEXT";
                        }

                        $createDataTableSQL .= ", parent_id INT NULL,invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','AR_non_workable','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke','Rebuttal','Auto_Close') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            qa_classification VARCHAR(255) NULL,
                                            qa_category VARCHAR(255) NULL,
                                            qa_scope VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            annex_coder_trends TEXT NULL,
                                            annex_qa_trends TEXT NULL,
                                            cpt_trends TEXT NULL,
                                            icd_trends TEXT NULL,
                                            modifiers TEXT NULL,
                                            QA_comments_count VARCHAR(255) NULL,
                                            coder_work_date DATE NULL,
                                            qa_work_date DATE NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            ar_status_code VARCHAR(255) NULL,
                                            ar_action_code VARCHAR(255) NULL,
                                            ar_manager_rebuttal_status TEXT NULL,
                                            ar_manager_rebuttal_comments TEXT NULL,
                                            qa_manager_rebuttal_status TEXT NULL,
                                            qa_manager_rebuttal_comments TEXT NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";
                        //DB::statement($createTableSQL);
                        try {
                            Log::debug("Executing SQL: " . $createDataTableSQL);
                            DB::statement($createDataTableSQL);
                        } catch (\Exception $e) {
                            Log::error("SQL failed: " . $createDataTableSQL);
                            Log::error($e->getMessage());
                            throw $e;
                        }
                        $dynamicModel = new DynamicModel($tableDataName);
                    } else {
                        $afterColumn = 'created_at';
                        foreach ($columns as $columnName => $columnType) {
                            $dataColumnExists = DB::select("
                                SELECT COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = '$tableDataName'
                                AND COLUMN_NAME = '$columnName'
                            ");
                            if (empty($dataColumnExists)) {

                                DB::statement("ALTER TABLE `$tableDataName` ADD COLUMN `$columnName` $columnType AFTER `$afterColumn`");
                                $dynamicModel = new UpdateDynamicModel($tableDataName);
                                $dynamicModel->refreshFillableFromTable();
                            }
                        }
                    }

                    $tableHistoryExists = DB::select("SHOW TABLES LIKE '$tableHistoryName'");
                    if (empty($tableHistoryExists)) {
                        $createTableSQL = "CREATE TABLE `$tableHistoryName` (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createTableSQL .= ", `$columnName` TEXT";
                        }

                        $createTableSQL .= ", parent_id INT NULL,invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','AR_non_workable','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke','Rebuttal','Auto_Close') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            qa_classification VARCHAR(255) NULL,
                                            qa_category VARCHAR(255) NULL,
                                            qa_scope VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            annex_coder_trends TEXT NULL,
                                            annex_qa_trends TEXT NULL,
                                            cpt_trends TEXT NULL,
                                            icd_trends TEXT NULL,
                                            modifiers TEXT NULL,
                                            QA_comments_count VARCHAR(255) NULL,
                                            coder_work_date DATE NULL,
                                            qa_work_date DATE NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            ar_status_code VARCHAR(255) NULL,
                                            ar_action_code VARCHAR(255) NULL,
                                            ar_manager_rebuttal_status TEXT NULL,
                                            ar_manager_rebuttal_comments TEXT NULL,
                                            qa_manager_rebuttal_status TEXT NULL,
                                            qa_manager_rebuttal_comments TEXT NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";
                        // DB::statement($createTableSQL);
                         try {
                            Log::debug("Executing SQL: " . $createTableSQL);
                            DB::statement($createTableSQL);
                        } catch (\Exception $e) {
                            Log::error("SQL failed: " . $createTableSQL);
                            Log::error($e->getMessage());
                            throw $e;
                        }
                        $dynamicModel = new DynamicModel($tableHistoryName);
                    } else {
                        $afterColumn = 'created_at';
                        foreach ($columns as $columnName => $columnType) {
                            $histortColumnExists = DB::select("
                                SELECT COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = '$tableHistoryName'
                                AND COLUMN_NAME = '$columnName'
                            ");
                            if (empty($histortColumnExists)) {

                                DB::statement("ALTER TABLE `$tableHistoryName` ADD COLUMN `$columnName` $columnType AFTER `$afterColumn`");
                                $dynamicModel = new UpdateDynamicModel($tableHistoryName);
                                $dynamicModel->refreshFillableFromTable();
                            }
                        }
                    }

                    $tableRevokeHistoryExists = DB::select("SHOW TABLES LIKE '$tableRevokeHistoryName'");
                    if (empty($tableRevokeHistoryExists)) {
                        $createTableSQL = "CREATE TABLE `$tableRevokeHistoryName` (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createTableSQL .= ", `$columnName` TEXT";
                        }

                        $createTableSQL .= ", parent_id INT NULL,invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','AR_non_workable','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke','Rebuttal','Auto_Close') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            qa_classification VARCHAR(255) NULL,
                                            qa_category VARCHAR(255) NULL,
                                            qa_scope VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            annex_coder_trends TEXT NULL,
                                            annex_qa_trends TEXT NULL,
                                            cpt_trends TEXT NULL,
                                            icd_trends TEXT NULL,
                                            modifiers TEXT NULL,
                                            QA_comments_count VARCHAR(255) NULL,
                                            coder_work_date DATE NULL,
                                            qa_work_date DATE NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            ar_status_code VARCHAR(255) NULL,
                                            ar_action_code VARCHAR(255) NULL,
                                            ar_manager_rebuttal_status TEXT NULL,
                                            ar_manager_rebuttal_comments TEXT NULL,
                                            qa_manager_rebuttal_status TEXT NULL,
                                            qa_manager_rebuttal_comments TEXT NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";
                        //DB::statement($createTableSQL);
                         try {
                            Log::debug("Executing SQL: " . $createTableSQL);
                            DB::statement($createTableSQL);
                        } catch (\Exception $e) {
                            Log::error("SQL failed: " . $createTableSQL);
                            Log::error($e->getMessage());
                            throw $e;
                        }
                        $dynamicModel = new DynamicModel($tableRevokeHistoryName);
                    } else {
                        $afterColumn = 'created_at';
                        foreach ($columns as $columnName => $columnType) {
                            $revokeColumnExists = DB::select("
                                SELECT COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = '$tableRevokeHistoryName'
                                AND COLUMN_NAME = '$columnName'
                            ");
                            if (empty($revokeColumnExists)) {

                                DB::statement("ALTER TABLE `$tableRevokeHistoryName` ADD COLUMN `$columnName` $columnType AFTER `$afterColumn`");
                                $dynamicModel = new UpdateDynamicModel($tableRevokeHistoryName);
                                $dynamicModel->refreshFillableFromTable();
                            }
                        }
                    }
                    if (DB::getPdo()->inTransaction()) {
                        DB::commit();
                    }
                    Helpers::clearConfigMap();
                    return redirect('/form_configuration_list' . '?parent=' . request()->parent . '&child=' . request()->child);
            } catch (\Exception $e) {
                 if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
                 Log::error('Form configuration update failed: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()], 500);
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function projectConfigDelete(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
              try {
                    DB::beginTransaction();
                    $data = $request->all();
                  //  $projectName =  Helpers::projectName($data['projectId'])->project_name;
                    $faProject = Helpers::projectName($data['projectId']);
                    $projectName = $faProject ? $faProject->project_name : null;
                    $subProjectName = $data['subProjectId'] != null ? Helpers::subProjectName($data['projectId'],$data['subProjectId'])->sub_project_name : 'project';
                    // $subProjectName = $data['subProjectId'] == null ? Helpers::projectName($data['projectId'])->project_name :Helpers::subProjectName($data['projectId'],$data['subProjectId'])->sub_project_name;
                    $table_name= Str::slug((Str::lower($projectName).'_'.Str::lower($subProjectName)),'_');
                    $dataCount = DB::table($table_name)->count();
                    $table_name_datas= Str::slug((Str::lower($projectName).'_'.Str::lower($subProjectName). '_datas'),'_');
                    $table_name_duplicates= Str::slug((Str::lower($projectName).'_'.Str::lower($subProjectName). '_duplicates'),'_');
                    $table_name_history= Str::slug((Str::lower($projectName).'_'.Str::lower($subProjectName).'_history'),'_');
                    $table_name_revoke_history =Str::slug((Str::lower($projectName).'_'.Str::lower($subProjectName). '_revoke_history'),'_');                  
                    $modelName = Str::studly($table_name);
                    $modelNameDatas = Str::studly($table_name_datas);
                    $modelNameDuplicates = Str::studly($table_name_duplicates);
                    $modelNameHistory = Str::studly($table_name_history);
                    $modelNameRevokeHistory = Str::studly($table_name_revoke_history);
                    $existingRecord =  formConfiguration::where('project_id',$data['projectId'])->where('sub_project_id',$data['subProjectId'])->get();

                    if($dataCount == 0) {
                        if (Schema::hasTable($table_name)) {
                            Schema::dropIfExists($table_name);
                        }
                        if (Schema::hasTable($table_name_datas)) {
                            Schema::dropIfExists($table_name_datas);
                        }
                        if (Schema::hasTable($table_name_duplicates)) {
                            Schema::dropIfExists($table_name_duplicates);
                        }
                        if (Schema::hasTable($table_name_history)) {
                            Schema::dropIfExists($table_name_history);
                        }
                        if (Schema::hasTable($table_name_revoke_history)) {
                            Schema::dropIfExists($table_name_revoke_history);
                        }

                        if (class_exists("App\\Models\\" .$modelName)) {
                            unlink(app_path('Models/'.$modelName.'.php'));
                        }
                        if (class_exists("App\\Models\\" .$modelNameDatas)) {
                            unlink(app_path('Models/'.$modelNameDatas.'.php'));
                        }
                        if (class_exists("App\\Models\\" .$modelNameDuplicates)) {
                             unlink(app_path('Models/'.$modelNameDuplicates.'.php'));
                        }
                        if (class_exists("App\\Models\\" .$modelNameHistory)) {
                             unlink(app_path('Models/'.$modelNameHistory.'.php'));
                        }
                        if (class_exists("App\\Models\\" .$modelNameRevokeHistory)) {
                            unlink(app_path('Models/'.$modelNameRevokeHistory.'.php'));
                       }
                        foreach ($existingRecord as $record) {
                            $record->deleted_at = Carbon::now();
                            $record->save();
                        }

                        DB::table('inventory_upload_configuration')
                            ->where('project_id', $data['projectId'])
                            ->where('sub_project_id', $data['subProjectId'])
                            ->whereNull('deleted_at')
                            ->update([
                                'deleted_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);

                        DB::table('project_col_search_configs')
                            ->where('project_id', $data['projectId'])
                            ->where('sub_project_id', $data['subProjectId'])
                            ->whereNull('deleted_at')
                            ->update([
                                'deleted_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);

                        return response()->json(['success' => true]);
                    } else {
                        return response()->json(['error' => true]);
                    }

                    if (DB::transactionLevel() > 0) {
                        DB::commit();
                    }
                } catch (\Exception $e) {
                     if (DB::transactionLevel() > 0) {
                        DB::rollBack();
                     }
                    Log::debug($e->getMessage());
                }
        } else {
            return redirect('/');
        }
    }

    private static function cloneInventoryUploadConfiguration($projectId, $sourceSubProjectId, $targetSubProjectId): void
    {
        $sourceRows = DB::table('inventory_upload_configuration')
            ->where('project_id', $projectId)
            ->where('sub_project_id', $sourceSubProjectId)
            ->whereNull('deleted_at')
            ->get();

        if ($sourceRows->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        foreach ($sourceRows as $row) {
            DB::table('inventory_upload_configuration')->insert([
                'project_id' => $projectId,
                'sub_project_id' => $targetSubProjectId,
                'data_columns' => $row->data_columns,
                'db_columns' => $row->db_columns,
                'required_columns' => $row->required_columns,
                'date_columns' => $row->date_columns,
                'numeric_columns' => $row->numeric_columns,
                'duplicate_columns' => $row->duplicate_columns,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }
    }

    private static function cloneProjectColSearchConfigs($projectId, $sourceSubProjectId, $targetSubProjectId): void
    {
        $sourceRows = DB::table('project_col_search_configs')
            ->where('project_id', $projectId)
            ->where('sub_project_id', $sourceSubProjectId)
            ->whereNull('deleted_at')
            ->get();

        if ($sourceRows->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        foreach ($sourceRows as $row) {
            DB::table('project_col_search_configs')->insert([
                'project_id' => $projectId,
                'sub_project_id' => $targetSubProjectId,
                'column_name' => $row->column_name,
                'column_type' => $row->column_type,
                'status' => $row->status,
                'enabled_by' => $row->enabled_by,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }
    }

    public function runCommands()
    {
        try {
            // $modelNamespace = "App\\Models\\TestModel";
            // Artisan::call('make:model', [
            //     'name' => $modelNamespace,
            //     '--no-interaction' => true,
            // ]);
          return 'hi';
        
        
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getProjectColumns(Request $request) {
        try {
            $projectId = $request->input('project_id');
            $subProjectId = $request->input('sub_project_id');

            if (!$projectId || !$subProjectId) {
                return response()->json(['error' => 'Invalid parameters'], 400);
            }

            $columns = Helpers::getProjectColumns($projectId, $subProjectId);
            return response()->json(['columns' => $columns]);
        } catch (\Exception $e) {
            Log::error('Error in getProjectColumns: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching project columns'], 500);
        }
    }
    
    //ar non project list code
    public function createFromExcel(Request $request)
    {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx',
                'max:10240', // 10 MB
            ],
        ], [
            'file.required' => 'Please select an Excel file.',
            'file.file' => 'The uploaded item must be a valid file.',
            'file.mimes' => 'Please upload only an XLSX Excel file.',
            'file.max' => 'The Excel file must not exceed 10 MB.',
        ]);

        $file = $validated['file'];

        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'xlsx') {
            return response()->json([
                'status' => 'warning',
                'message' => 'Please upload only an XLSX Excel file.',
            ], 422);
        }

        /*
         * Do not trust the user-provided original filename while writing
         * to the server.
         */
        $safeOriginalName = preg_replace(
            '/[^A-Za-z0-9._-]/',
            '_',
            $originalName
        );

        $storedFileName = now()->format('YmdHisv')
            . '_'
            . Str::random(8)
            . '_'
            . $safeOriginalName;

        $uploadDirectory = storage_path(
            'app' . DIRECTORY_SEPARATOR . 'dynamic_table_configurations'
        );

        if (
            !is_dir($uploadDirectory)
            && !mkdir($uploadDirectory, 0775, true)
            && !is_dir($uploadDirectory)
        ) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Unable to create the upload directory.',
            ], 500);
        }

        try {
            $file->move($uploadDirectory, $storedFileName);
        } catch (Throwable $exception) {
            Log::error('Dynamic table Excel file move failed.', [
                'message' => $exception->getMessage(),
                'original_file_name' => $originalName,
            ]);

            return response()->json([
                'status' => 'warning',
                'message' => 'Unable to store the uploaded Excel file.',
            ], 500);
        }

        $filePath = $uploadDirectory
            . DIRECTORY_SEPARATOR
            . $storedFileName;

        $pythonBinary = env('PYTHON_BIN', '/bin/python3');

        $pythonScript = base_path(
            'Python' . DIRECTORY_SEPARATOR . 'create_dynamic_tables.py'
        );

        if (!is_file($pythonScript)) {
            $this->deleteFileSafely($filePath);

            Log::error('Dynamic table Python script not found.', [
                'script' => $pythonScript,
            ]);

            return response()->json([
                'status' => 'warning',
                'message' => 'Dynamic table creation script was not found.',
            ], 500);
        }

        $payload = [
            'file_path' => realpath($filePath) ?: $filePath,
            'file_name' => $storedFileName,
            'original_file_name' => $originalName,

            /*
             * Python validates that the uploaded file is located only
             * inside this approved directory.
             */
            'allowed_upload_directory' => realpath($uploadDirectory)
                ?: $uploadDirectory,
        ];

        /*
         * Pass Laravel DB settings to Python through process environment
         * variables. Do not hard-code database passwords in Python.
         */
        $processEnvironment = array_filter([
            'PATH' => getenv('PATH') ?: null,
            'SystemRoot' => getenv('SystemRoot') ?: null,
            'WINDIR' => getenv('WINDIR') ?: null,

            /*
            * Force Python stdout and stderr to UTF-8.
            */
            'PYTHONUTF8' => '1',
            'PYTHONIOENCODING' => 'utf-8',

            'DYNAMIC_DB_HOST' => config(
                'database.connections.mysql.host'
            ),
            'DYNAMIC_DB_PORT' => (string) config(
                'database.connections.mysql.port'
            ),
            'DYNAMIC_DB_DATABASE' => config(
                'database.connections.mysql.database'
            ),
            'DYNAMIC_DB_USERNAME' => config(
                'database.connections.mysql.username'
            ),
            'DYNAMIC_DB_PASSWORD' => config(
                'database.connections.mysql.password'
            ),
            'DYNAMIC_DB_CHARSET' => config(
                'database.connections.mysql.charset',
                'utf8mb4'
            ),
        ], static fn ($value) => $value !== null);

        $process = new Process(
            [$pythonBinary, $pythonScript],
            base_path(),
            $processEnvironment
        );

        $process->setInput(
            json_encode(
                $payload,
                JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            )
        );

        $process->setTimeout(300);

        try {
            $process->run();
        } catch (Throwable $exception) {
            $this->deleteFileSafely($filePath);

            Log::error('Dynamic table Python process failed to execute.', [
                'message' => $exception->getMessage(),
                'script' => $pythonScript,
            ]);

            return response()->json([
                'status' => 'warning',
                'message' => 'Unable to execute the table creation process.',
            ], 500);
        }

        $stdout = $this->normalizeUtf8(
            trim($process->getOutput())
        );

        $stderr = $this->normalizeUtf8(
            trim($process->getErrorOutput())
        );

        Log::info('Dynamic table Python process completed.', [
            'successful' => $process->isSuccessful(),
            'exit_code' => $process->getExitCode(),
            'stdout' => $stdout,
            'stderr' => $stderr,
        ]);

        /*
         * The configuration workbook is no longer required after Python
         * completes. Remove it even when validation fails.
         */
        $this->deleteFileSafely($filePath);

        $pythonResponse = $this->decodePythonResponse($stdout);

        if (!$process->isSuccessful()) {
            $message = 'Dynamic table creation failed.';
            $errors = [];

            /*
            * Python should return a JSON object through stdout.
            */
            if (is_array($pythonResponse)) {
                $message = $this->normalizeUtf8(
                    $pythonResponse['message'] ?? $message
                );

                $errors = $pythonResponse['errors'] ?? [];
            } elseif ($stderr !== '') {
                /*
                * Fallback when Python did not return valid JSON.
                */
                $cleanStderr = preg_replace(
                    '/^\[dynamic-table\]\s*/m',
                    '',
                    $stderr
                );

                $cleanStderr = $this->normalizeUtf8(
                    trim((string) $cleanStderr)
                );

                if ($cleanStderr !== '') {
                    $message = $cleanStderr;
                }
            }

            return response()->json(
                [
                    'status' => 'warning',
                    'message' => $message,
                    'errors' => $errors,
                ],
                422,
                [],
                JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

       if (!is_array($pythonResponse)) {
            return response()->json(
                [
                    'status' => 'warning',
                    'message' => 'Python returned an invalid response.',
                ],
                500,
                [],
                JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        if (($pythonResponse['status'] ?? null) !== 'success') {
            return response()->json(
                [
                    'status' => 'warning',
                    'message' => $this->normalizeUtf8(
                        $pythonResponse['message']
                            ?? 'Dynamic table creation failed.'
                    ),
                    'errors' => $pythonResponse['errors'] ?? [],
                ],
                422,
                [],
                JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => $pythonResponse['message']
                ?? 'Dynamic tables processed successfully.',
            'data' => $pythonResponse['data'] ?? [],
        ]);
    }

    /**
     * Python should print only one JSON response to stdout.
     * This fallback also supports accidental diagnostic output before JSON.
     */
    private function decodePythonResponse(string $stdout): ?array
    {
        if ($stdout === '') {
            return null;
        }

        $decoded = json_decode($stdout, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $lines = preg_split('/\R/', $stdout) ?: [];

        for ($index = count($lines) - 1; $index >= 0; $index--) {
            $line = trim($lines[$index]);

            if ($line === '') {
                continue;
            }

            $decoded = json_decode($line, true);

            if (
                json_last_error() === JSON_ERROR_NONE
                && is_array($decoded)
            ) {
                return $decoded;
            }
        }

        return null;
    }

    private function deleteFileSafely(?string $filePath): void
    {
        if (
            $filePath
            && is_file($filePath)
            && !@unlink($filePath)
        ) {
            Log::warning('Unable to delete dynamic table upload file.', [
                'file_path' => $filePath,
            ]);
        }
    }

    private function normalizeUtf8(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        /*
        * Windows Python processes may return Windows-1252 bytes.
        */
        $converted = mb_convert_encoding(
            $value,
            'UTF-8',
            [
                'Windows-1252',
                'ISO-8859-1',
            ]
        );

        return is_string($converted)
            ? $converted
            : '';
    }

    public function nonArInventoryConfigurationList()
    {
        if (
            !Session::get('loginDetails')
            || !Session::get('loginDetails')['userDetail']
            || Session::get('loginDetails')['userDetail']['emp_id'] === null
        ) {
            return redirect('/');
        }

        try {
            $nonArConfigurations = DB::table(
                    'non_ar_inventory_upload_configuration as config'
                )
                ->join(
                    'projects as project',
                    'config.project_id',
                    '=',
                    'project.project_id'
                )
                ->join(
                    'subprojects as subproject',
                    function ($join) {
                        $join->on(
                            'config.sub_project_id',
                            '=',
                            'subproject.sub_project_id'
                        );

                        $join->on(
                            'config.project_id',
                            '=',
                            'subproject.project_id'
                        );
                    }
                )
                ->whereNull('config.deleted_at')
                ->where('project.status', 'Active')
                ->select(
                    'config.id',
                    'config.project_id',
                    'config.sub_project_id',
                    'config.data_columns',
                    'config.db_columns',
                    'config.date_columns',
                    'project.project_name',
                    'project.aims_project_name',
                    'subproject.sub_project_name'
                )
                ->orderBy('project.project_name')
                ->orderBy('subproject.sub_project_name')
                ->get();

            return view(
                'Form.nonArInventoryConfigurationList',
                compact('nonArConfigurations')
            );
        } catch (\Exception $exception) {
            Log::error('Non-AR configuration list fetch failed.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Unable to fetch Non-AR inventory configurations.'
                );
        }
    }
    public function downloadNonArInventoryTemplate(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'sub_project_id' => 'required',
        ]);

        try {
            $configuration = DB::table(
                    'non_ar_inventory_upload_configuration'
                )
                ->where('project_id', $request->project_id)
                ->where('sub_project_id', $request->sub_project_id)
                ->whereNull('deleted_at')
                ->first();

            if (!$configuration) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Download failed: configuration not found.'
                    );
            }

            $project = project::where(
                    'project_id',
                    $request->project_id
                )
                ->first();

            if (!$project) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Download failed: project not found.'
                    );
            }

            $subProject = subproject::where(
                    'project_id',
                    $request->project_id
                )
                ->where(
                    'sub_project_id',
                    $request->sub_project_id
                )
                ->first();

            if (!$subProject) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Download failed: sub-project not found.'
                    );
            }

            $dataColumns = trim(
                (string) $configuration->data_columns
            );

            if ($dataColumns === '') {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Download failed: data columns are not configured.'
                    );
            }

            /*
            * Stored format:
            * First_Name,Last_Name,DOB,DOS,...
            */
            $headers = array_values(
                array_filter(
                    array_map(
                        'trim',
                        explode(',', $dataColumns)
                    ),
                    static fn ($value) => $value !== ''
                )
            );

            if (empty($headers)) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Download failed: valid headers were not found.'
                    );
            }

            $fileName = Str::slug(
                $project->project_name
                . '_'
                . $subProject->sub_project_name,
                '_'
            )
            . '-'
            . date('mdY');

            $export = new class($headers) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithHeadings {

                private array $headers;

                public function __construct(array $headers)
                {
                    $this->headers = $headers;
                }

                public function array(): array
                {
                    return [];
                }

                public function headings(): array
                {
                    return $this->headers;
                }
            };

            return \Maatwebsite\Excel\Facades\Excel::download(
                $export,
                $fileName . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            );
        } catch (\Exception $exception) {
            Log::error('Non-AR template download failed.', [
                'project_id' => $request->project_id,
                'sub_project_id' => $request->sub_project_id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Download failed: ' . $exception->getMessage()
                );
        }
    }
    public function deleteNonArInventoryConfiguration(Request $request)
    {
        if (
            !Session::get('loginDetails')
            || !isset(Session::get('loginDetails')['userDetail'])
            || empty(Session::get('loginDetails')['userDetail']['emp_id'])
        ) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Your session has expired.',
            ], 401);
        }

        $validated = $request->validate([
            'project_id' => 'required',
            'sub_project_id' => 'required',
        ]);

        try {
            $projectId = $validated['project_id'];
            $subProjectId = $validated['sub_project_id'];

            $configuration = DB::table(
                    'non_ar_inventory_upload_configuration'
                )
                ->where('project_id', $projectId)
                ->where('sub_project_id', $subProjectId)
                ->whereNull('deleted_at')
                ->first();

            if (!$configuration) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Configuration not found.',
                ], 404);
            }

            $project = project::where(
                    'project_id',
                    $projectId
                )
                ->first();

            if (!$project) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Project not found.',
                ], 404);
            }

            $subProject = subproject::where(
                    'project_id',
                    $projectId
                )
                ->where(
                    'sub_project_id',
                    $subProjectId
                )
                ->first();

            if (!$subProject) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Sub-project not found.',
                ], 404);
            }

            $tableName = Str::slug(
                Str::lower(
                    $project->project_name
                    . '_'
                    . $subProject->sub_project_name
                    . '_datas'
                ),
                '_'
            );

            if (!preg_match('/^[A-Za-z0-9_]+$/', $tableName)) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Generated inventory table name is invalid.',
                ], 422);
            }

            /*
            * Check whether the dynamic table contains data.
            */
            if (Schema::hasTable($tableName)) {
                $dataExists = DB::table($tableName)->exists();

                if ($dataExists) {
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'We cannot delete this configuration because the inventory table contains data.',
                        'table' => $tableName,
                    ], 422);
                }
            }

            /*
            * DROP TABLE performs an implicit MySQL commit.
            * Therefore, do not place this operation inside DB::beginTransaction().
            */
            if (Schema::hasTable($tableName)) {
                Schema::dropIfExists($tableName);
            }

            /*
            * Soft-delete the configuration after dropping the empty table.
            */
            DB::table('non_ar_inventory_upload_configuration')
                ->where('id', $configuration->id)
                ->update([
                    'deleted_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

           $projectDisplayName = trim((string) ($project->project_name ?? ''));
            $subProjectDisplayName = trim((string) ($subProject->sub_project_name ?? ''));

            if ($projectDisplayName === '') {
                $projectDisplayName = 'Project ' . $projectId;
            }

            if ($subProjectDisplayName === '') {
                $subProjectDisplayName = 'Sub Project ' . $subProjectId;
            }

            return response()->json([
                'status' => 'success',
                'message' => $projectDisplayName
                    . '_'
                    . $subProjectDisplayName
                    . ' configuration deleted successfully.',
                'table' => $tableName,
            ]);
        } catch (\Exception $exception) {
            Log::error('Non-AR configuration deletion failed.', [
                'project_id' => $validated['project_id'] ?? null,
                'sub_project_id' => $validated['sub_project_id'] ?? null,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return response()->json([
                'status' => 'warning',
                'message' => 'Unable to delete the configuration.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }
    public function inventoryuploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'project_id' => 'required',
            'sub_project_id' => 'required',
        ]);

        $projectId = $request->project_id;
        $subProjectId = $request->sub_project_id;
        $configurationExists = DB::table('non_ar_inventory_upload_configuration')
                ->where('project_id', $projectId)
                ->where('sub_project_id', $subProjectId)
                ->exists();

        if (!$configurationExists) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Inventory upload configuration not found for selected project and sub project combination.',
            ]);
        }
        $project = DB::table('projects')
            ->where('project_id', $projectId)
            ->first();

        if (!$project) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Selected project not found.',
            ]);
        }

        $subProject = DB::table('subprojects')
            ->where('sub_project_id', $subProjectId)
            ->where('project_id', $projectId)
            ->first();

        if (!$subProject) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Selected project and sub project combination is invalid.',
            ]);
        }

        

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $serverMime = $file->getMimeType();
        $clientMime = $file->getClientMimeType();

        Log::info('UPLOAD ORIGINAL NAME: ' . $file->getClientOriginalName());
        Log::info('UPLOAD EXTENSION: ' . $extension);
        Log::info('UPLOAD SERVER MIME: ' . $serverMime);
        Log::info('UPLOAD CLIENT MIME: ' . $clientMime);
        Log::info('UPLOAD SIZE: ' . $file->getSize());
        if ($extension !== 'csv') {
            return response()->json([
                'status' => 'warning',
                'message' => 'Please upload only CSV file.',
            ]);
        }
        $allowedCsvMimeTypes = [
            'text/csv',
            'text/plain',
            'application/csv',
            'text/x-csv',
            'application/x-csv',
            'application/vnd.ms-excel',
        ];
         if (
            !in_array($serverMime, $allowedCsvMimeTypes, true) &&
            !in_array($clientMime, $allowedCsvMimeTypes, true)
        ) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Invalid CSV file type. Detected type: ' . $serverMime,
            ]);
        }
        $projectName = $project->project_name;
        $subProjectName = $subProject->sub_project_name;
        $expectedFileNameKey = Str::slug(($projectName.'_'.$subProjectName),'_'). '-' . date('mdY');
        $uploadedFileNameKey = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        if (!Str::contains($uploadedFileNameKey, $expectedFileNameKey)) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Uploaded file name is not matching selected project and sub project. File name should contain: ' . $expectedFileNameKey,
            ]);
        }

        $cleanOriginalFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $fileName = time() . '_' . $cleanOriginalFileName;

        $uploadPath = storage_path(
            'app' . DIRECTORY_SEPARATOR .
            'ar_nonproject_inventory_uploads' . DIRECTORY_SEPARATOR .
            $projectId . DIRECTORY_SEPARATOR .
            $subProjectId
        );

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $filePath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

        $file->move($uploadPath, $fileName);

        $payload = [
            'file_path' => $filePath,
            'file_name' => $fileName,
            'project_id' => $projectId,
            'sub_project_id' => $subProjectId,
            'project_name' => $projectName,
            'sub_project_name' => $subProjectName,
        ];

        $python = env('PYTHON_BIN', 'python');
        // $python = '/bin/python3';
        $script = realpath(base_path('Python/nonArProjectInventoryUpload.py'));

        if (!$script || !file_exists($script)) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Python script not found.',
            ], 500);
        }

        Log::info('PYTHON SCRIPT PATH ' . $script);
        Log::info('PYTHON SCRIPT MODIFIED ' . date('Y-m-d H:i:s', filemtime($script)));
        Log::info('PYTHON SCRIPT HASH ' . sha1_file($script));
        Log::info('PYTHON PAYLOAD ' . json_encode($payload));

        $env = [
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            'WINDIR' => getenv('WINDIR') ?: 'C:\\Windows',
            'PATH' => getenv('PATH'),
        ];

        $process = new Process(
            [$python, $script],
            base_path(),
            $env
        );

        $process->setInput(json_encode($payload));
        $process->setTimeout(7200);
        $process->run();

        $stdout = trim($process->getOutput());
        $stderr = trim($process->getErrorOutput());

        Log::info('PYTHON STDOUT ' . $stdout);
        Log::info('PYTHON STDERR ' . $stderr);

        $pythonResponse = $this->decodePythonJsonResponse($stdout);

        if (!$process->isSuccessful()) {
            $message = 'Inventory not uploaded.';

            if (is_array($pythonResponse) && isset($pythonResponse['message'])) {
                $message = $pythonResponse['message'];
            } else {
                $message = $this->cleanPythonUserMessage($stdout, $stderr);
            }

            return response()->json([
                'status' => 'warning',
                'message' => $message,
            ]);
        }

       if (!is_array($pythonResponse)) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Inventory not uploaded. Python returned invalid response.',
            ]);
        }

        if (($pythonResponse['status'] ?? null) === 'warning') {
            return response()->json([
                'status' => 'warning',
                'message' => $pythonResponse['message'] ?? 'Inventory not uploaded.',
            ]);
        }

        $data = $pythonResponse['data'] ?? $pythonResponse;

        return response()->json([
            'status' => 'success',
            'message' => $pythonResponse['message'] ?? 'Inventory uploaded successfully.',
            'file' => $fileName,
            'inserted' => $data['inserted'] ?? 0,
            'total_rows' => $data['total_rows'] ?? 0,
            'table' => $data['table'] ?? null,
        ]);
    }
    private function decodePythonJsonResponse($stdout)
    {
        $stdout = trim((string) $stdout);

        if ($stdout === '') {
            return null;
        }

        $decoded = json_decode($stdout, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // fallback: get last valid JSON line if stdout has extra text
        $lines = preg_split('/\r\n|\r|\n/', $stdout);

        foreach (array_reverse($lines) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, '{') && str_ends_with($line, '}')) {
                $decoded = json_decode($line, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return null;
    }

    private function cleanPythonUserMessage($stdout, $stderr)
    {
        $combined = trim((string) $stdout);

        if ($combined === '') {
            $combined = trim((string) $stderr);
        }

        $combined = html_entity_decode(strip_tags($combined));
        $combined = str_replace(["\r\n", "\r"], "\n", $combined);

        // Prefer clean inventory message from Python output/log
        if (preg_match('/(inventory not uploaded:[^\n]+)/i', $combined, $match)) {
            return trim($match[1]);
        }

        // If Python traceback has Exception line, extract only that line
        if (preg_match('/Exception:\s*([^\n]+)/i', $combined, $match)) {
            return trim($match[1]);
        }

        return 'Inventory not uploaded. Please check uploaded file.';
    }
     
}
