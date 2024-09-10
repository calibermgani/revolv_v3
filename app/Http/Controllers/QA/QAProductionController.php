<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Http\Helper\Admin\Helpers as Helpers;
use App\Models\CallerChartsWorkLogs;
use App\Models\formConfiguration;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\project;
use App\Models\subproject;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Models\QualitySampling;
use App\Models\QASubStatus;
use Illuminate\Support\Facades\Mail;
use App\Mail\ManagerRebuttalMail;
use App\Models\CCEmailIds;

ini_set('memory_limit', '1024M');
class QAProductionController extends Controller
{
    public function clients()
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'user_id' => $userId,
                ];
                $client = new Client();
                $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_clients_on_user', [
                    'json' => $payload,
                ]);
                if ($response->getStatusCode() == 200) {
                    $data = json_decode($response->getBody(), true);
                } else {
                    return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                }
                $projects = $data['clientList'];
                return view('QAProduction/clients', compact('projects'));
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function getSubProjects(Request $request)
    {
        try {
            $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
            $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $request->project_id,
            ];
            $client = new Client();
            $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_practice_on_client', [
                'json' => $payload,
            ]);
            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
            } else {
                return response()->json(['error' => 'API request failed'], $response->getStatusCode());
            }
            $subprojects = $data['practiceList'];
            $clientDetails = $data['clientInfo'];

            $subProjectsWithCount = [];
            foreach ($subprojects as $key => $data) {
                $subProjectsWithCount[$key]['client_id'] = $clientDetails['id'];
                $subProjectsWithCount[$key]['client_name'] = Helpers::projectName($clientDetails["id"])->project_name;//$clientDetails['client_name'];
                $subProjectsWithCount[$key]['sub_project_id'] = $data['id'];
                $subProjectsWithCount[$key]['sub_project_name'] = $data['name'];
                $projectName = $subProjectsWithCount[$key]['client_name'];
                $table_name = Str::slug((Str::lower($projectName) . '_' . Str::lower($subProjectsWithCount[$key]['sub_project_name'])), '_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                    if (class_exists($modelClass)) {
                        $subProjectsWithCount[$key]['assignedCount'] = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->count();
                        $subProjectsWithCount[$key]['CompletedCount'] = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $subProjectsWithCount[$key]['PendingCount'] = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $subProjectsWithCount[$key]['holdCount'] = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    } else {
                        $subProjectsWithCount[$key]['assignedCount'] = '--';
                        $subProjectsWithCount[$key]['CompletedCount'] = '--';
                        $subProjectsWithCount[$key]['PendingCount'] = '--';
                        $subProjectsWithCount[$key]['holdCount'] = '--';
                    }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $subProjectsWithCount[$key]['assignedCount'] = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $subProjectsWithCount[$key]['CompletedCount'] = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $subProjectsWithCount[$key]['PendingCount'] = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $subProjectsWithCount[$key]['holdCount'] = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    } else {
                        $subProjectsWithCount[$key]['assignedCount'] = '--';
                        $subProjectsWithCount[$key]['CompletedCount'] = '--';
                        $subProjectsWithCount[$key]['PendingCount'] = '--';
                        $subProjectsWithCount[$key]['holdCount'] = '--';
                    }
                }

            }
            return response()->json(['subprojects' => $subProjectsWithCount]);
        } catch (\Exception $e) {
            log::debug($e->getMessage());
        }

    }

    public function clientAssignedTab($clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            $client = new Client();
            try {
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName);
                if($decodedsubProjectName != null &&  $decodedsubProjectName != 'project') {
                    $decodedsubProjectName= $decodedsubProjectName->sub_project_name;
                   }
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $columnsHeader = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_rework_comments','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at', 'created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                }
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDatas = "App\\Models\\" . $modelName . 'Datas';
                $assignedProjectDetails = collect();
                $assignedDropDown = [];
                $dept = Session::get('loginDetails')['userInfo']['department']['id'];
                $existingCallerChartsWorkLogs = [];
                $assignedProjectDetailsStatus = [];
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;
                $autoCloseCount = 0;
                $unAssignedCount = 0;
                $subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                // if($decodedPracticeName == '--') {
                // $qasamplingDetails = QualitySampling::where('project_id',$decodedProjectName)->first();//dd($qasamplingDetails,$decodedProjectName,$decodedPracticeName);
                // } else {
                //     $qasamplingDetails = QualitySampling::where('project_id',$decodedProjectName)->where('sub_project_id',$decodedPracticeName)->first();//dd($qasamplingDetails,$decodedProjectName,$decodedPracticeName,'else');
                // }

                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                    if (class_exists($modelClass)) {
                        $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                        $assignedProjectDetails = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->orderBy('id', 'ASC')->get();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->whereIn('record_status', ['QA_Assigned','QA_Inprocess'])->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $assignedDropDownIds = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->select('QA_emp_id')->groupBy('QA_emp_id')->pluck('QA_emp_id')->toArray();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $duplicateCount = $modelClassDuplcates::count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->orderBy('id', 'ASC')->pluck('chart_status')->toArray();
                        $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                        // $payload = [
                        //     'token' => '1a32e71a46317b9cc6feb7388238c95d',
                        //     'client_id' => $decodedProjectName,
                        //     'user_id' => $userId,
                        // ];

                        // $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_resource_name', [
                        //     'json' => $payload,
                        // ]);
                        // if ($response->getStatusCode() == 200) {
                        //     $data = json_decode($response->getBody(), true);
                        // } else {
                        //     return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                        // }
                        // $assignedDropDown = array_filter($data['userDetail']);
                    } else {
                        return redirect()->back();
                       }
                } elseif ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $assignedProjectDetails = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->orderBy('id', 'ASC')->get();//dd($assignedProjectDetails);
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->whereIn('record_status', ['QA_Assigned','QA_Inprocess'])->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->orderBy('id', 'ASC')->pluck('chart_status')->toArray();
                    } else {
                        return redirect()->back();
                       }
                }
                $popUpHeader = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type', [3, $dept,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('user_type',  $dept)->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal = Helpers::qaSubStatusList();
                return view('QAProduction/qaClientAssignedTab', compact('assignedProjectDetails', 'columnsHeader', 'popUpHeader', 'popupNonEditableFields', 'popupEditableFields', 'modelClass', 'clientName', 'subProjectName', 'assignedDropDown', 'existingCallerChartsWorkLogs', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount', 'assignedProjectDetailsStatus','popupQAEditableFields','qaSubStatusListVal','autoCloseCount','unAssignedCount'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientPendingTab($clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $column_names = DB::select("DESCRIBE $table_name");
                $columns = array_column($column_names, 'Field');
                $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                'coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                'updated_at', 'created_at', 'deleted_at'];
                $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                    return !in_array($column, $columnsToExclude);
                });
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $pendingProjectDetails = collect();
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;
                $autoCloseCount = 0;
                $unAssignedCount = 0;
                $existingCallerChartsWorkLogs = [];
                $subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                    if (class_exists($modelClass)) {
                        $pendingProjectDetails = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id', 'ASC')->get();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                        $duplicateCount = $modelClassDuplcates::count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->where('record_status', 'QA_Pending')->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                    }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $pendingProjectDetails = $modelClass::where('chart_status', 'QA_Pending')->orderBy('id', 'ASC')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->get();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->where('record_status', 'QA_Pending')->orderBy('id', 'desc')->pluck('record_id')->toArray();
                    }
                }
                $dept = Session::get('loginDetails')['userInfo']['department']['id'];
                $popUpHeader = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type', [3, $dept,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('user_type',  $dept)->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal =  Helpers::qaSubStatusList();
                return view('QAProduction/qaClientPendingTab', compact('pendingProjectDetails', 'columnsHeader', 'clientName', 'subProjectName', 'modelClass', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount', 'existingCallerChartsWorkLogs', 'popUpHeader', 'popupNonEditableFields', 'popupEditableFields','popupQAEditableFields','qaSubStatusListVal','autoCloseCount','unAssignedCount'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientHoldTab($clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $column_names = DB::select("DESCRIBE $table_name");
                $columns = array_column($column_names, 'Field');
                $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date', 
                'coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                'updated_at', 'created_at', 'deleted_at'];
                $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                    return !in_array($column, $columnsToExclude);
                });
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $holdProjectDetails = collect();
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;
                $autoCloseCount = 0;
                $unAssignedCount = 0;
                $existingCallerChartsWorkLogs = [];
                $subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                    if (class_exists($modelClass)) {
                        $holdProjectDetails = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id', 'ASC')->get();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                        $duplicateCount = $modelClassDuplcates::count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->where('record_status', 'QA_Hold')->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                    }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $holdProjectDetails = $modelClass::where('chart_status', 'QA_Hold')->orderBy('id', 'ASC')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->get();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->where('record_status', 'QA_Hold')->orderBy('id', 'desc')->pluck('record_id')->toArray();
                    }
                }
                $dept = Session::get('loginDetails')['userInfo']['department']['id'];
                $popUpHeader = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type', [3, $dept,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('user_type',  $dept)->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal =  Helpers::qaSubStatusList();
                return view('QAProduction/qaClientOnholdTab', compact('holdProjectDetails', 'columnsHeader', 'clientName', 'subProjectName', 'modelClass', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount', 'popUpHeader', 'popupNonEditableFields', 'popupEditableFields', 'existingCallerChartsWorkLogs','popupQAEditableFields','qaSubStatusListVal','autoCloseCount','unAssignedCount'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientCompletedTab($clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $column_names = DB::select("DESCRIBE $table_name");
                $columns = array_column($column_names, 'Field');
                $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                'coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                'updated_at', 'created_at', 'deleted_at'];
                $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                    return !in_array($column, $columnsToExclude);
                });
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $completedProjectDetails = collect();
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;
                $autoCloseCount = 0;
                $unAssignedCount = 0;
                $subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                    if (class_exists($modelClass)) {
                        $completedProjectDetails = $modelClass::where('chart_status', 'QA_Completed')->orderBy('id', 'ASC')->whereBetween('updated_at',[$startDate,$endDate])->get();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                        $duplicateCount = $modelClassDuplcates::count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                    }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $completedProjectDetails = $modelClass::where('chart_status', 'QA_Completed')->orderBy('id', 'ASC')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->get();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                }
                $dept = Session::get('loginDetails')['userInfo']['department']['id'];
                $popUpHeader = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type', [3, $dept,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('user_type',  $dept)->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal = Helpers::qaSubStatusList();
                $qaStatusList = Helpers::qaStatusList();
                return view('QAProduction/qaClientCompletedTab', compact('completedProjectDetails', 'columnsHeader', 'clientName', 'subProjectName', 'modelClass', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount', 'popUpHeader', 'popupNonEditableFields', 'popupEditableFields','popupQAEditableFields','qaSubStatusListVal','qaStatusList','autoCloseCount','unAssignedCount'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientReworkTab($clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $column_names = DB::select("DESCRIBE $table_name");
                $columns = array_column($column_names, 'Field');
                $columnsToExclude = ['id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                'coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                'updated_at', 'created_at', 'deleted_at'];
                $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                    return !in_array($column, $columnsToExclude);
                });
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $revokeProjectDetails = collect();
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                    if (class_exists($modelClass)) {
                        $revokeProjectDetails = $modelClass::where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id', 'ASC')->get();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $modelClassDuplcates = "App\\Models\\" . $modelName;
                        $duplicateCount = $modelClassDuplcates::count();
                    }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $revokeProjectDetails = $modelClass::where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id', 'ASC')->get();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                }

                return view('QAProduction/qaClientReworkTab', compact('revokeProjectDetails', 'columnsHeader', 'clientName', 'subProjectName', 'modelClass', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientDuplicateTab($clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $column_names = DB::select("DESCRIBE $table_name");
                $columns = array_column($column_names, 'Field');
                $columnsToExclude = ['id', 'duplicate_status','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                'coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                'updated_at', 'created_at', 'deleted_at'];
                $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                    return !in_array($column, $columnsToExclude);
                });
                $modelName = Str::studly($table_name);
                $modelClassDuplcates = "App\\Models\\" . $modelName . "Duplicates";
                $modelClass = "App\\Models\\" . $modelName;
                $duplicateProjectDetails = collect();
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                    if (class_exists($modelClassDuplcates)) {
                        $duplicateProjectDetails = $modelClassDuplcates::orderBy('id', 'ASC')->whereBetween('updated_at',[$startDate,$endDate])->get();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $duplicateCount = $modelClassDuplcates::count();
                    }
                } elseif ($loginEmpId) {
                    if (class_exists($modelClassDuplcates)) {
                        $duplicateProjectDetails = $modelClassDuplcates::where('chart_status', 'CE_Assigned')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id', 'ASC')->get();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                }

                return view('QAProduction/qaClientDuplicateTab', compact('duplicateProjectDetails', 'columnsHeader', 'clientName', 'subProjectName', 'modelClass', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function qaClientCompletedDatasDetails(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data =  $request->all();
                $currentTime = Carbon::now();
                $data['emp_id'] = Session::get('loginDetails')['userDetail']['emp_id'];
                $data['project_id'] = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                $data['sub_project_id'] = $data['subProjectName'] == '--' ? NULL : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                $decodedClientName = Helpers::projectName($data['project_id'])->project_name;
                $decodedsubProjectName = $data['sub_project_id'] == NULL ? 'project' :Helpers::subProjectName($data['project_id'] ,$data['sub_project_id'])->sub_project_name;
                $data['start_time'] = $currentTime->format('Y-m-d H:i:s');
                $data['record_status'] = 'QA_'.ucwords($data['urlDynamicValue']);
                 $existingRecordId = CallerChartsWorkLogs::where('project_id', $data['project_id'])->where('sub_project_id',$data['sub_project_id'])->where('record_id',$data['record_id'])->where('record_status',$data['record_status'])->where('end_time',NULL)->first();

                if(empty($existingRecordId)) {
                    $startTimeVal = $data['start_time'];
                    $save_flag = CallerChartsWorkLogs::create($data);
                } else {
                    $startTimeVal = $existingRecordId->start_time;
                    $save_flag = 1;
                }
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDatas = "App\\Models\\" . $modelName.'Datas';
                $clientData = $modelClassDatas::where('parent_id',$data['record_id'])->orderBy('id','desc')->first();
                if($clientData != null) {
                    $clientData = $clientData->toArray();
                } else {
                    $clientData = $modelClass::where('id',$data['record_id'])->first();
                }
                if(isset($clientData) && !empty($clientData)) {
                   return response()->json(['success' => true,'clientData'=>$clientData,'startTimeVal'=>$startTimeVal]);
                } else {
                    return response()->json(['success' => false]);
                }
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function qaclientViewDetails(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data =  $request->all();
                $decodedProjectName = Helpers::encodeAndDecodeID($data['clientName'], 'decode');
                $decodedPracticeName = $data['subProjectName'] == '--' ? '--' : Helpers::encodeAndDecodeID($data['subProjectName'], 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                // $decodedsubProjectName = Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDatas = "App\\Models\\" . $modelName.'Datas';
                // $modelClassDatas = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName)).'Datas';
                // $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName));
                $clientData = $modelClassDatas::where('parent_id',$data['record_id'])->orderBy('id','desc')->first();
                if($clientData != null) {
                    $clientData = $clientData->toArray();
                } else {
                    $clientData = $modelClass::where('id',$data['record_id'])->first();
                }
                if(isset($clientData) && !empty($clientData)) {
                   return response()->json(['success' => true,'clientData'=>$clientData]);
                } else {
                    return response()->json(['success' => false]);
                }
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function clientsStore(Request $request,$clientName,$subProjectName) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                // $data = $request->all();
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName =  $subProjectName == '--' ? NULL : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                // $decodedsubProjectName = Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName.'Datas';
                $originalModelClass = "App\\Models\\" . $modelName;
                // $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName)).'Datas';
                $data = [];
                foreach ($request->except('_token', 'parent', 'child') as $key => $value) {
                    if (is_array($value)) {
                        $data[$key] = implode('_el_', $value);
                    } else {
                        $data[$key] = $value;
                    }
                }
                $data['invoke_date'] = date('Y-m-d',strtotime($data['invoke_date']));
                $data['parent_id'] = $data['idValue'];
                $datasRecord = $modelClass::where('parent_id', $data['parent_id'])->orderBy('id','desc')->first();
                $record = $originalModelClass::where('id', $data['parent_id'])->first();
                $data['QA_rework_comments']=$data['QA_rework_comments'] != null ? str_replace("\r\n", '_el_', $data['QA_rework_comments']) : $data['QA_rework_comments'];
                $data['QA_rework_comments'] = preg_replace('/(_el_){2,}/', '_el_', $data['QA_rework_comments']);
                $data['QA_comments_count'] = $data['QA_rework_comments'] != null ? count(explode('_el_', $data['QA_rework_comments'])) : 0;
                if($data['chart_status'] == "QA_Completed") {
                    $data['qa_work_date'] = Carbon::now()->format('Y-m-d');
                }
                // if($data['chart_status'] == "Revoke") {
                //     if($datasRecord['coder_error_count'] >= 1) {
                //         $data['tl_error_count'] = $datasRecord['tl_error_count']+1;
                //         $data['coder_error_count'] = $datasRecord['coder_error_count'];
                //      } else {dd($datasRecord['coder_error_count'],'coder_error_count');
                //          $data['coder_error_count'] = $datasRecord['coder_error_count']+1;
                //          $data['tl_error_count'] = $datasRecord['tl_error_count'];
                //      }
                // } else {
                //     $data['coder_error_count'] = $datasRecord['coder_error_count'];
                //     $data['tl_error_count'] = $datasRecord['tl_error_count'];
                // }

                 if($data['chart_status'] == "QA_Completed" &&  $datasRecord['coder_rework_status'] == "Rebuttal") {
                    $data['qa_error_count'] = 1;
                } else {//dd($datasRecord['qa_error_count']);
                    $data['qa_error_count'] = $datasRecord['qa_error_count'];
                }
                if($data['chart_status'] == "Revoke" &&  $datasRecord['coder_rework_status'] == "Rebuttal") {
                    $data['tl_error_count'] = 1;
                    // $toMailId = "mgani@caliberfocus.com";
                    // $ccMailId = "vijayalaxmi@caliberfocus.com";
                    // $mailHeader = "Rebuttal Mail";dd($mailHeader,$data);
                    // Mail::to($toMailId)->cc($ccMailId)->send(new ManagerRebuttalMail($mailHeader));
                } else {
                    $data['tl_error_count'] = $datasRecord['tl_error_count'];
                }
                if(isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null) {
                  $data['annex_coder_trends'] = isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null ? str_replace("\r\n", '_el_', $data['annex_coder_trends']) : null;
                }
                if(isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null) {
                   $annex_qa_trends = isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null ?  explode('_el_',str_replace("\r\n", '_el_', $data['annex_qa_trends'])) : null;
                }
                   // $data['annex_qa_trends_count'] = $data['annex_qa_trends'] != null ? count(explode('_el_', $data['annex_qa_trends'])) : 0;
                if(isset($annex_qa_trends) && $annex_qa_trends != null) {
                    foreach( $annex_qa_trends as $trend){
                        if (str_contains($trend, 'CPT -') && !str_contains($trend, 'modifier')) {
                            $array[]= $trend;
                            $data['qa_cpt_trends'] = implode('_el_', $array);
                        }
                        if (str_contains($trend, 'ICD -') && !str_contains($trend, 'modifier')) {
                            $a1[]= $trend;
                            $data['qa_icd_trends'] =implode('_el_', $a1);
                        }
                        if (str_contains($trend, 'modifier ')) {
                            $a2[]= $trend;
                            $data['qa_modifiers'] = implode('_el_', $a2);
                        }
                    }
               }
                if(isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null) {
                    $data['annex_qa_trends'] = $data['annex_qa_trends'] != null ?  str_replace("\r\n", '_el_', $data['annex_qa_trends']) : null;      
                }// dd($data);
                if($datasRecord != null) {
                    $datasRecord->update($data);
                    $record->update( ['chart_status' => $data['chart_status'],'qa_hold_reason' => $data['qa_hold_reason'],'QA_rework_comments' => $data['QA_rework_comments'],'qa_error_count' => $data['qa_error_count'],'tl_error_count' => $data['tl_error_count'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code'],'QA_comments_count' => $data['QA_comments_count']]);
                } else {
                    $record->update( ['chart_status' => $data['chart_status'],'qa_hold_reason' => $data['qa_hold_reason'],'QA_rework_comments' => $data['QA_rework_comments'],'qa_error_count' => $data['qa_error_count'],'tl_error_count' => $data['tl_error_count'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code'],'QA_comments_count' => $data['QA_comments_count']]);
                    $modelClass::create($data);
                }
                if($data['chart_status'] == "Revoke" &&  $datasRecord['coder_rework_status'] == "Rebuttal") {
                    $client = new Client();
                    $payload = [
                        'token' => '1a32e71a46317b9cc6feb7388238c95d',
                        'client_id' => $decodedProjectName
                    ];
                     $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_emails_above_tl_level', [
                        'json' => $payload
                    ]);
                    if ($response->getStatusCode() == 200) {
                        $apiData = json_decode($response->getBody(), true);
                    } else {
                        return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                    }
                    $toMailId = $apiData['people_email'];
                    $reportingPerson = $apiData['reprting_person'];
                    // $toMailId = ["prabaharan@annexmed.net","rajeswari@annexmed.net","ram@annexmed.net"];
                    // $ccMailId = ["vijayalaxmi@caliberfocus.com","mgani@caliberfocus.com","elan@caliberfocus.com"];
                    $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'manager rebuttal')->first();
                    $ccMailId = explode(",", $ccMail->cc_emails);
                    //$mailHeader = $decodedClientName." Rebuttal Mail";
                    $mailHeader = "Assistance Needed: ".$decodedClientName." Audit Rebuttal";
                    $mailBody = $record;
                    if(isset($toMailId) && !empty($toMailId)) {
                       Mail::to($toMailId)->cc($ccMailId)->send(new ManagerRebuttalMail($mailHeader, $mailBody, $reportingPerson));
                    }
                }
                 $currentTime = Carbon::now();
                $callChartWorkLogExistingRecord = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                ->where('project_id', $decodedProjectName)
                ->where('sub_project_id', $decodedPracticeName)
                ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->first();
                if($callChartWorkLogExistingRecord && $callChartWorkLogExistingRecord != null) {
                    $start_time = Carbon::parse($callChartWorkLogExistingRecord->start_time);
                    $time_difference = $currentTime->diff($start_time);
                    $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                    $callChartWorkLogExistingRecord->update( ['record_status' => $data['chart_status'],'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time] );

                }
                return redirect('qa_production/qa_projects_assigned/'.$clientName.'/'.$subProjectName);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientsUpdate(Request $request,$clientName,$subProjectName) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                 $data = $request->all();//dd($data);
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName =  $subProjectName == '--' ? NULL : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $originalModelClass = "App\\Models\\" . $modelName;
                $modelClass = "App\\Models\\" . $modelName.'Datas';
                $data = [];
                foreach ($request->except('_token', 'parent', 'child') as $key => $value) {
                    if (is_array($value)) {
                        $data[$key] = implode('_el_', $value);
                    } else {
                        $data[$key] = $value;
                    }
                }
                $data['invoke_date'] = date('Y-m-d',strtotime($data['invoke_date']));
                $data['parent_id'] = $data['parentId'];
                $datasRecord = $modelClass::where('parent_id', $data['parent_id'])->orderBy('id','desc')->first();
                $data['QA_rework_comments']=$data['QA_rework_comments'] != null ? str_replace("\r\n", '_el_', $data['QA_rework_comments']) : $data['QA_rework_comments'];
                $data['QA_rework_comments'] = preg_replace('/(_el_){2,}/', '_el_', $data['QA_rework_comments']);
                $data['QA_comments_count'] = $data['QA_rework_comments'] != null ? count(explode('_el_', $data['QA_rework_comments'])) : 0;
                if($data['chart_status'] == "QA_Completed") {
                    $data['qa_work_date'] = Carbon::now()->format('Y-m-d');
                }
                // if($data['chart_status'] == "Revoke") {
                //   $data['coder_error_count'] = $datasRecord['coder_error_count']+1;
                // }
               if($data['chart_status'] == "QA_Completed" &&  $datasRecord['coder_rework_status'] == "Rebuttal") {
                    $data['qa_error_count'] = 1;
                } else {
                    $data['qa_error_count'] = $datasRecord['qa_error_count'];
                }
                if($data['chart_status'] == "Revoke" &&  $datasRecord['coder_rework_status'] == "Rebuttal") {
                    $data['tl_error_count'] = 1;
                } else {
                    $data['tl_error_count'] = $datasRecord['tl_error_count'];
                }
                if(isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null) {
                    $data['annex_coder_trends'] = isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null ? str_replace("\r\n", '_el_', $data['annex_coder_trends']) : null ;
                 }  
                if(isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null) {
                  $annex_qa_trends = isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null ?  explode('_el_',str_replace("\r\n", '_el_', $data['annex_qa_trends'])) : null ;
                }
                //$data['annex_qa_trends_count'] = isset($data['annex_qa_trends_count']) && $data['annex_qa_trends'] != null ?? count(explode('_el_', $data['annex_qa_trends'])) ;
               
                if(isset($annex_qa_trends)  && $annex_qa_trends != null) {
                    foreach( $annex_qa_trends as $trend){
                        if (str_contains($trend, 'CPT -') && !str_contains($trend, 'modifier')) {
                            $array[]= $trend;
                            $data['qa_cpt_trends'] = implode('_el_', $array);
                        }
                        if (str_contains($trend, 'ICD -') && !str_contains($trend, 'modifier')) {
                            $a1[]= $trend;
                            $data['qa_icd_trends'] =implode('_el_', $a1);
                        }
                        if (str_contains($trend, 'modifier ')) {
                            $a2[]= $trend;
                            $data['qa_modifiers'] = implode('_el_', $a2);
                        }
                    }
               }
               if(isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null) {
                 $data['annex_qa_trends'] = isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null ?  str_replace("\r\n", '_el_', $data['annex_qa_trends']) : null ;//dd($data);
               }
                if($datasRecord != null) {
                    $fieldsToExclude = [
                        'annex_coder_trends',
                        'coder_cpt_trends',
                        'coder_icd_trends',
                        'coder_modifiers',
                        'qa_cpt_trends',
                        'qa_icd_trends',
                        'qa_modifiers',
                        'annex_qa_trends',
                    ];
                    
                    $data = array_diff_key($data, array_flip($fieldsToExclude));
                  $datasRecord->update($data);
                  $record = $originalModelClass::where('id', $data['parent_id'])->first();
                  $record->update( ['chart_status' => $data['chart_status'],'qa_hold_reason' => $data['qa_hold_reason'],'QA_rework_comments' => $data['QA_rework_comments'],'qa_error_count' => $data['qa_error_count'],'tl_error_count' => $data['tl_error_count'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code'],'QA_comments_count' => $data['QA_comments_count']]);
                 } else {
                    $data['parent_id'] = $data['idValue'];
                    $record = $originalModelClass::where('id', $data['parent_id'])->first();
                    $record->update( ['chart_status' => $data['chart_status'],'qa_hold_reason' => $data['qa_hold_reason'],'QA_rework_comments' => $data['QA_rework_comments'],'qa_error_count' => $data['qa_error_count'],'tl_error_count' => $data['tl_error_count'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code'],'QA_comments_count' => $data['QA_comments_count']] );
                    $modelClass::create($data);
                }
                if($data['chart_status'] == "Revoke" &&  $datasRecord['coder_rework_status'] == "Rebuttal") {
                    $client = new Client();
                    $payload = [
                        'token' => '1a32e71a46317b9cc6feb7388238c95d',
                        'client_id' => $decodedProjectName
                    ];
                     $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_emails_above_tl_level', [
                        'json' => $payload
                    ]);
                    if ($response->getStatusCode() == 200) {
                        $apiData = json_decode($response->getBody(), true);
                    } else {
                        return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                    }
                    $toMailId = $apiData['people_email'];
                    $reportingPerson = $apiData['reprting_person'];
                    // $ccMailId = ["vijayalaxmi@caliberfocus.com","mgani@caliberfocus.com","elan@caliberfocus.com"];
                    $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'manager rebuttal')->first();
                    $ccMailId = explode(",", $ccMail->cc_emails);
                    $mailHeader = "Assistance Needed: ".$decodedClientName." Audit Rebuttal";
                    $mailBody = $record;
                    if(isset($toMailId) && !empty($toMailId)) {
                        Mail::to($toMailId)->cc($ccMailId)->send(new ManagerRebuttalMail($mailHeader, $mailBody, $reportingPerson));
                    }
                }
                $currentTime = Carbon::now();
                $callChartWorkLogExistingRecord = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                ->where('record_status',$data['record_old_status'])
                ->where('project_id', $decodedProjectName)
                ->where('sub_project_id', $decodedPracticeName)
                ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->first();
                $start_time = Carbon::parse($callChartWorkLogExistingRecord->start_time);
                $time_difference = $currentTime->diff($start_time);
                $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                if($callChartWorkLogExistingRecord && $callChartWorkLogExistingRecord != null) {
                    $callChartWorkLogExistingRecord->update([
                        'record_status' => $data['chart_status'],
                        'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time
                    ]);
                }
                $tabUrl = lcfirst(str_replace('QA_', '', $data['record_old_status']));
                return redirect('qa_production/qa_projects_'.$tabUrl.'/'.$clientName.'/'.$subProjectName);
             } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public static function qaSubStatusList(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data = QASubStatus::where('status_code_id', $request['status_code_id'])->pluck('sub_status_code', 'id')->toArray();
                return response()->json(["subStatus" => $data]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function clientAutoClose($clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            $client = new Client();
            try {
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $columnsHeader = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_rework_comments','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date', 'coder_rework_status','QA_status_code','QA_sub_status_code',
                    'coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at', 'created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                }
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDatas = "App\\Models\\" . $modelName . 'Datas';
                $assignedProjectDetails = collect();
                $assignedDropDown = [];$userDetail = [];
                $dept = Session::get('loginDetails')['userInfo']['department']['id'];
                $existingCallerChartsWorkLogs = [];
                $assignedProjectDetailsStatus = [];
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;
                $autoCloseCount = 0;
                $unAssignedCount = 0;
                $subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                // if($decodedPracticeName == '--') {
                // $qasamplingDetails = QualitySampling::where('project_id',$decodedProjectName)->first();//dd($qasamplingDetails,$decodedProjectName,$decodedPracticeName);
                // } else {
                //     $qasamplingDetails = QualitySampling::where('project_id',$decodedProjectName)->where('sub_project_id',$decodedPracticeName)->first();//dd($qasamplingDetails,$decodedProjectName,$decodedPracticeName,'else');
                // }
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'client_id' => $decodedProjectName,
                 ];

                $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_resource_name_on_designation', [
                    'json' => $payload,
                ]);
                if ($response->getStatusCode() == 200) {
                    $data = json_decode($response->getBody(), true);
                } else {
                    return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                }
                $userDetail  = array_filter($data['userDetail']);
                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                    if (class_exists($modelClass)) {
                        $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                        $autoCloseProjectDetails = $modelClass::where('qa_work_status', 'Auto_Close')->orderBy('id', 'ASC')->get();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->whereIn('record_status', ['QA_Assigned','QA_Inprocess'])->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $duplicateCount = $modelClassDuplcates::count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->orderBy('id', 'ASC')->pluck('chart_status')->toArray();
                        $assignedDropDown = $userDetail;
                        $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                    }
                } elseif ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $autoCloseProjectDetails = $modelClass::where('qa_work_status', 'Auto_Close')->orderBy('id', 'ASC')->get();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->whereIn('record_status', ['CE_Completed'])->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->orderBy('id', 'ASC')->pluck('chart_status')->toArray();
                        if (isset($userDetail[$loginEmpId])) {
                            $assignedDropDown[$loginEmpId] = $userDetail[$loginEmpId];
                          }
                    }
                }
                $popUpHeader = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type', [3, $dept,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('user_type',  $dept)->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal = Helpers::qaSubStatusList();
                return view('QAProduction/qaClientAutoClose', compact('autoCloseProjectDetails', 'columnsHeader', 'popUpHeader', 'popupNonEditableFields', 'popupEditableFields', 'modelClass', 'clientName', 'subProjectName', 'assignedDropDown', 'existingCallerChartsWorkLogs', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount', 'assignedProjectDetailsStatus','popupQAEditableFields','qaSubStatusListVal','autoCloseCount','unAssignedCount'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function samplingAssignee(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {

            try {

                $assigneeId = $request['assigneeId'];
                $decodedProjectName = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                $decodedPracticeName = $request['subProjectName'] == '--' ? '--' : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDatas = "App\\Models\\" . $modelName.'Datas';
                $modelHistory = "App\\Models\\" . $modelName.'History';
                foreach($request['checkedRowValues'] as $data) {
                    $existingRecord = $modelClass::where('id',$data['value'])->first();
                    $historyRecord = $existingRecord->toArray();
                    $historyRecord['parent_id']= $historyRecord['id'];
                    unset($historyRecord['id']);
                    $modelHistory::create($historyRecord);
                    $existingModelClassDatasRecord = $modelClassDatas::where('parent_id',$data['value'])->first();
                    $existingRecord->update(['QA_emp_id' => $assigneeId,'qa_work_status' => 'Sampling','chart_status' => 'CE_Completed']);
                    $existingModelClassDatasRecord->update(['QA_emp_id' => $assigneeId,'qa_work_status' => 'Sampling','chart_status' => 'CE_Completed']);
                    
                }
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function clientUnAssignedTab($clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            $client = new Client();
            try {
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $columnsHeader = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_rework_comments','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at', 'created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                }
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDatas = "App\\Models\\" . $modelName . 'Datas';
                $assignedProjectDetails = collect();
                $assignedDropDown = [];
                $dept = Session::get('loginDetails')['userInfo']['department']['id'];
                $existingCallerChartsWorkLogs = [];
                $assignedProjectDetailsStatus = [];
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;
                $autoCloseCount = 0;
                $unAssignedCount = 0;
                $subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                // if($decodedPracticeName == '--') {
                // $qasamplingDetails = QualitySampling::where('project_id',$decodedProjectName)->first();//dd($qasamplingDetails,$decodedProjectName,$decodedPracticeName);
                // } else {
                //     $qasamplingDetails = QualitySampling::where('project_id',$decodedProjectName)->where('sub_project_id',$decodedPracticeName)->first();//dd($qasamplingDetails,$decodedProjectName,$decodedPracticeName,'else');
                // }

                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                    if (class_exists($modelClass)) {
                        $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                        $unAssignedProjectDetails = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->orderBy('id', 'ASC')->get();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->whereIn('record_status', ['QA_Assigned','QA_Inprocess'])->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $assignedDropDownIds = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->select('QA_emp_id')->groupBy('QA_emp_id')->pluck('QA_emp_id')->toArray();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                        $duplicateCount = $modelClassDuplcates::count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->whereNull('qa_work_status')->orderBy('id', 'ASC')->pluck('chart_status')->toArray();
                        // $payload = [
                        //     'token' => '1a32e71a46317b9cc6feb7388238c95d',
                        //     'client_id' => $decodedProjectName,
                        //     'user_id' => $userId,
                        // ];

                        // $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_resource_name', [
                        //     'json' => $payload,
                        // ]);
                        // if ($response->getStatusCode() == 200) {
                        //     $data = json_decode($response->getBody(), true);
                        // } else {
                        //     return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                        // }
                        // $assignedDropDown = array_filter($data['userDetail']);
                    }
                } elseif ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $unAssignedProjectDetails = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->orderBy('id', 'ASC')->get();//dd($assignedProjectDetails);
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->whereIn('record_status', ['QA_Assigned','QA_Inprocess'])->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->orderBy('id', 'ASC')->pluck('chart_status')->toArray();
                    }
                }
                $popUpHeader = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type', [3, $dept,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('user_type',  $dept)->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal = Helpers::qaSubStatusList();
                return view('QAProduction/qaClientUnAssignedTab', compact('unAssignedProjectDetails', 'columnsHeader', 'popUpHeader', 'popupNonEditableFields', 'popupEditableFields', 'modelClass', 'clientName', 'subProjectName', 'assignedDropDown', 'existingCallerChartsWorkLogs', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount', 'assignedProjectDetailsStatus','popupQAEditableFields','qaSubStatusListVal','autoCloseCount','unAssignedCount'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function assigneeDropdown(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            $client = new Client();
            try {
                $userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] !=null ? Session::get('loginDetails')['userDetail']['id']:"";
                $decodedProjectName = Helpers::encodeAndDecodeID($request->clientName, 'decode');
                    $payload = [
                        'token' => '1a32e71a46317b9cc6feb7388238c95d',
                        'client_id' => $decodedProjectName,
                        'user_id' => $userId,
                    ];

                    $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_resource_name', [
                        'json' => $payload,
                    ]);
                    if ($response->getStatusCode() == 200) {
                        $data = json_decode($response->getBody(), true);
                    } else {
                        return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                    }
                    $assignedDropDown = array_filter($data['userDetail']);
                    return response()->json(['assignedDropDown' => $assignedDropDown]);
                } catch (\Exception $e) {
                    log::debug($e->getMessage());
                }
        } else {
            return redirect('/');
        }
   }
}
