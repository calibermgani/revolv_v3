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
class FormController extends Controller
{
    public function formConfigurationList() {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                    $formConfiguration = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                                            ->select('project_id', 'sub_project_id', DB::raw('GROUP_CONCAT(label_name) as label_names'))
                                            ->get();
               return view('Form.formConfigList',compact('formConfiguration'));
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
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
                return response()->json(["subProject" => $data, "existingSubProject" => $existingSubProject]);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public static function formConfigurationStore(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userInfo'] && Session::get('loginDetails')['userInfo']['user_id'] !=null) {
            try {
                $data = $request->all();
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
                    formConfiguration::create($requiredData);
                    // $columnName = Str::lower(str_replace([' ', '/'], ['_'], $data['label_name'][$i]));
                    $columnName = Str::lower(str_replace([' ', '/'], ['_', '_else_'], $data['label_name'][$i]));
                    if ($data['input_type'][$i] == 'text' || $data['input_type'][$i] == 'date_range') {
                        $columns[$columnName] = 'TEXT';
                    } else if ($data['input_type'][$i] == 'select' || $data['input_type'][$i] == 'checkbox' || $data['input_type'][$i] == 'radio') {
                        $enumValues = "'" . implode("','", explode(',',$data['options_name'][$i])) . "'";
                        $columns[$columnName] = "ENUM($enumValues)";
                    } else if ($data['input_type'][$i] == 'date') {
                        $columns[$columnName] = 'DATE';
                    } else if ($data['input_type'][$i] == 'textarea') {
                        $columns[$columnName] = 'TEXT';
                    }
                }
                $subProjectName = $data['sub_project_id'] != null ? $subProjectArray->sub_project_name : 'project';
                $tableName = Str::slug(($projectName->project_name.'_'.$subProjectName),'_');
                $tableDataName = Str::slug(($projectName->project_name.'_'.$subProjectName. '_datas'),'_');
                $duplicateTableName = Str::slug(($projectName->project_name . '_' . $subProjectName . '_duplicates'),'_');
                $tableHistoryName =Str::slug(($projectName->project_name.'_'.$subProjectName. '_history'),'_');
                $tableExists = DB::select("SHOW TABLES LIKE '$tableName'");
                    if (empty($tableExists)) {
                        $createTableSQL = "CREATE TABLE $tableName (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createTableSQL .= ", $columnName $columnType";
                        }

                        $createTableSQL .= ", invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";//dd($createTableSQL);
                        DB::statement($createTableSQL);
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
                                                    chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke') DEFAULT 'CE_Assigned',
                                                    duplicate_status VARCHAR(255) NULL,
                                                    ce_hold_reason TEXT NULL,
                                                    qa_hold_reason TEXT NULL,
                                                    qa_work_status VARCHAR(255) NULL,
                                                    QA_required_sampling VARCHAR(255) NULL,
                                                    QA_rework_comments TEXT NULL,
                                                    coder_rework_status VARCHAR(255) NULL,
                                                    coder_rework_reason TEXT NULL,
                                                    coder_error_count VARCHAR(255) NULL,
                                                    qa_error_count VARCHAR(255) NULL,
                                                    tl_error_count VARCHAR(255) NULL,
                                                    tl_comments TEXT NULL,
                                                    QA_status_code VARCHAR(255) NULL,
                                                    QA_sub_status_code VARCHAR(255) NULL,
                                                    QA_followup_date DATE NULL,
                                                    CE_status_code VARCHAR(255) NULL,
                                                    CE_sub_status_code VARCHAR(255) NULL,
                                                    CE_followup_date DATE NULL,
                                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                    updated_at TIMESTAMP NULL,
                                                    deleted_at TIMESTAMP NULL)";
                        DB::statement($createDuplicateTableSQL);
                        $dynamicDuplicateModel = new DynamicModel($duplicateTableName);
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
                        $createTableSQL = "CREATE TABLE $tableDataName (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createTableSQL .= ", $columnName TEXT";
                        }

                        $createTableSQL .= ", parent_id INT NULL,invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";
                        DB::statement($createTableSQL);
                        $dynamicModel = new DynamicModel($tableDataName);
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

                                DB::statement("ALTER TABLE $tableDataName ADD COLUMN $columnName TEXT AFTER $afterColumn");
                                $dynamicModel = new DynamicModel($tableDataName);
                                $dynamicModel->refreshFillableFromTable();
                            }
                        }
                    }

                    $tableHistoryExists = DB::select("SHOW TABLES LIKE '$tableHistoryName'");
                    if (empty($tableHistoryExists)) {
                        $createTableSQL = "CREATE TABLE $tableHistoryName (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createTableSQL .= ", $columnName TEXT";
                        }

                        $createTableSQL .= ", parent_id INT NULL,invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";
                        DB::statement($createTableSQL);
                        $dynamicModel = new DynamicModel($tableHistoryName);
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

                                DB::statement("ALTER TABLE $tableHistoryName ADD COLUMN $columnName TEXT AFTER $afterColumn");
                                $dynamicModel = new DynamicModel($tableHistoryName);
                                $dynamicModel->refreshFillableFromTable();
                            }
                        }
                    }

                    return redirect('/form_configuration_list' . '?parent=' . request()->parent . '&child=' . request()->child);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function formEdit($project_id,$sub_project_id) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $projectId = Helpers::encodeAndDecodeID($project_id,'decode');
                $subProjectId = $sub_project_id == '--' ? '--' :Helpers::encodeAndDecodeID($sub_project_id,'decode');
                if($sub_project_id != '--') {
                    $projectDetails = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id',$projectId)->where('sub_project_id',$subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                    $formDetails = formConfiguration::where('project_id',$projectId)->where('sub_project_id',$subProjectId)
                    ->get();
                } else {
                    $projectDetails = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id',$projectId)
                    ->select('project_id', 'sub_project_id')
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
                $data = $request->all();
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
                        formConfiguration::create($requiredData);
                       // $columnName = Str::lower(str_replace([' ', '/'], '_', $data['label_name'][$i]));
                        $columnName = Str::lower(str_replace([' ', '/'], ['_', '_else_'], $data['label_name'][$i]));
                        if ($data['input_type'][$i] == 'text' || $data['input_type'][$i] == 'date_range') {
                            $columns[$columnName] = 'TEXT';
                        } else if ($data['input_type'][$i] == 'select' || $data['input_type'][$i] == 'checkbox' || $data['input_type'][$i] == 'radio') {
                              $enumValues = "'" . implode("','", explode(',',$data['options_name'][$i])) . "'";
                            $columns[$columnName] = "ENUM($enumValues)";
                        } else if ($data['input_type'][$i] == 'date') {
                            $columns[$columnName] = 'DATE';
                        } else if ($data['input_type'][$i] == 'textarea') {
                            $columns[$columnName] = 'TEXT';
                        }
                    }

                }
                $subProjectName = $data['sub_project_id_val'] != null ? $subProjectArray->sub_project_name : 'project';
                $tableName = Str::slug(($projectName->project_name.'_'.$subProjectName),'_');
                $tableDataName =Str::slug($projectName->project_name.'_'.$subProjectName. '_datas','_');
                $duplicateTableName = Str::slug($projectName->project_name . '_' . $subProjectName . '_duplicates','_');
                $tableHistoryName = Str::slug($projectName->project_name.'_'.$subProjectName. '_history','_');

                $tableExists = DB::select("SHOW TABLES LIKE '$tableName'");
                    if (empty($tableExists)) {
                        $createTableSQL = "CREATE TABLE $tableName (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createTableSQL .= ", $columnName $columnType";
                        }

                        $createTableSQL .= ", parent_id INT NULL,invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";
                        DB::statement($createTableSQL);
                        $dynamicModel = new DynamicModel($tableName);
                    } else {
                        $afterColumn = 'created_at';
                        foreach ($columns as $columnName => $columnType) {
                            $columnExists = DB::select("
                                SELECT COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = '$tableName'
                                AND COLUMN_NAME = '$columnName'
                            ");//dd('else',$columns,$columnExists,empty($columnExists),$tableName);
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
                                                    chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke') DEFAULT 'CE_Assigned',
                                                    duplicate_status VARCHAR(255) NULL,
                                                    ce_hold_reason TEXT NULL,
                                                    qa_hold_reason TEXT NULL,
                                                    qa_work_status VARCHAR(255) NULL,
                                                    QA_required_sampling VARCHAR(255) NULL,
                                                    QA_rework_comments TEXT NULL,
                                                    coder_rework_status VARCHAR(255) NULL,
                                                    coder_rework_reason TEXT NULL,
                                                    coder_error_count VARCHAR(255) NULL,
                                                    qa_error_count VARCHAR(255) NULL,
                                                    tl_error_count VARCHAR(255) NULL,
                                                    tl_comments TEXT NULL,
                                                    QA_status_code VARCHAR(255) NULL,
                                                    QA_sub_status_code VARCHAR(255) NULL,
                                                    QA_followup_date DATE NULL,
                                                    CE_status_code VARCHAR(255) NULL,
                                                    CE_sub_status_code VARCHAR(255) NULL,
                                                    CE_followup_date DATE NULL,
                                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                    updated_at TIMESTAMP NULL,
                                                    deleted_at TIMESTAMP NULL)";
                        DB::statement($createDuplicateTableSQL);
                        $dynamicDuplicateModel = new DynamicModel($duplicateTableName);
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
                        $createTableSQL = "CREATE TABLE $tableDataName (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createTableSQL .= ", $columnName TEXT";
                        }

                        $createTableSQL .= ", parent_id INT NULL,invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";
                        DB::statement($createTableSQL);
                        $dynamicModel = new DynamicModel($tableDataName);
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

                                DB::statement("ALTER TABLE $tableDataName ADD COLUMN $columnName TEXT AFTER $afterColumn");
                                $dynamicModel = new DynamicModel($tableDataName);
                                $dynamicModel->refreshFillableFromTable();
                            }
                        }
                    }

                    $tableHistoryExists = DB::select("SHOW TABLES LIKE '$tableHistoryName'");
                    if (empty($tableHistoryExists)) {
                        $createTableSQL = "CREATE TABLE $tableHistoryName (id INT AUTO_INCREMENT PRIMARY KEY";
                        foreach ($columns as $columnName => $columnType) {
                            $createTableSQL .= ", $columnName TEXT";
                        }

                        $createTableSQL .= ", parent_id INT NULL,invoke_date DATE NULL,
                                            CE_emp_id VARCHAR(255) NULL,
                                            QA_emp_id VARCHAR(255) NULL,
                                            chart_status ENUM('CE_Assigned','CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold','Revoke') DEFAULT 'CE_Assigned',
                                            ce_hold_reason TEXT NULL,
                                            qa_hold_reason TEXT NULL,
                                            qa_work_status VARCHAR(255) NULL,
                                            QA_required_sampling VARCHAR(255) NULL,
                                            QA_rework_comments TEXT NULL,
                                            coder_rework_status VARCHAR(255) NULL,
                                            coder_rework_reason TEXT NULL,
                                            coder_error_count VARCHAR(255) NULL,
                                            qa_error_count VARCHAR(255) NULL,
                                            tl_error_count VARCHAR(255) NULL,
                                            tl_comments TEXT NULL,
                                            QA_status_code VARCHAR(255) NULL,
                                            QA_sub_status_code VARCHAR(255) NULL,
                                            QA_followup_date DATE NULL,
                                            CE_status_code VARCHAR(255) NULL,
                                            CE_sub_status_code VARCHAR(255) NULL,
                                            CE_followup_date DATE NULL,
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            updated_at TIMESTAMP NULL,
                                            deleted_at TIMESTAMP NULL)";
                         DB::statement($createTableSQL);
                        $dynamicModel = new DynamicModel($tableHistoryName);
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

                                DB::statement("ALTER TABLE $tableHistoryName ADD COLUMN $columnName TEXT AFTER $afterColumn");
                                $dynamicModel = new DynamicModel($tableHistoryName);
                                $dynamicModel->refreshFillableFromTable();
                            }
                        }
                    }
                    return redirect('/form_configuration_list' . '?parent=' . request()->parent . '&child=' . request()->child);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function projectConfigDelete(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
              try {
                    $data = $request->all();
                    $projectName = $decodedClientName = Helpers::projectName($data['projectId'])->project_name;
                    $subProjectName = $data['subProjectId'] == null ? Helpers::projectName($data['projectId'])->project_name :Helpers::subProjectName($data['projectId'],$data['subProjectId'])->sub_project_name;
                    $table_name= Str::slug((Str::lower($projectName).'_'.Str::lower($subProjectName)),'_');
                    $table_name_datas= Str::slug((Str::lower($projectName).'_'.Str::lower($subProjectName). '_datas'),'_');
                    $table_name_duplicates= Str::slug((Str::lower($projectName).'_'.Str::lower($subProjectName). '_duplicates'),'_');
                    $table_name_history= Str::slug((Str::lower($projectName).'_'.Str::lower($subProjectName).'_history'),'_');
                    $dataCount = DB::table($table_name)->count();
                    $modelName = Str::studly($table_name);
                    $modelNameDatas = Str::studly($table_name_datas);
                    $modelNameDuplicates = Str::studly($table_name_duplicates);
                    $modelNameHistory = Str::studly($table_name_history);
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
                        foreach ($existingRecord as $record) {
                            $record->deleted_at = Carbon::now();
                            $record->save();
                        }
                        return response()->json(['success' => true]);
                    } else {
                        return response()->json(['error' => true]);
                    }


                } catch (\Exception $e) {
                    Log::debug($e->getMessage());
                }
        } else {
            return redirect('/');
        }
    }
}
