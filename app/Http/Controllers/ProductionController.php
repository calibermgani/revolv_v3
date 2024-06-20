<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use App\Http\Helper\Admin\Helpers as Helpers;
use Illuminate\Support\Facades\Session;
use App\Models\InventoryWound;
use App\Models\InventoryWoundDuplicate;
use App\Models\project;
use App\Models\subproject;
use App\Models\formConfiguration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\CallerChartsWorkLogs;
use App\Models\QualitySampling;

class ProductionController extends Controller
{
    public function dashboard() {
       if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
            return view('productions/dashboard');
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clients() {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] !=null ? Session::get('loginDetails')['userDetail']['id']:"";
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'user_id' => $userId
                ];
                $client = new Client();
                $response = $client->request('POST',  config("constants.PRO_CODE_URL").'/api/v1_users/get_clients_on_user', [
                    'json' => $payload
                ]);
                if ($response->getStatusCode() == 200) {
                     $data = json_decode($response->getBody(), true);
                } else {
                     return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                }
                $projects = $data['clientList'];
                  return view('productions/clients',compact('projects'));
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function getSubProjects(Request $request) {
        try {
            $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
            $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $request->project_id
            ];
            $client = new Client();
            $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_practice_on_client', [
                'json' => $payload
            ]);
            if ($response->getStatusCode() == 200) {
                 $data = json_decode($response->getBody(), true);
            } else {
                 return response()->json(['error' => 'API request failed'], $response->getStatusCode());
            }
            $subprojects = $data['practiceList'];
            $clientDetails = $data['clientInfo'];

          //  $subprojects = subproject::with(['clientName'])->where('project_id',$request->project_id)->where('status','Active')->get();
            $subProjectsWithCount = [];
            foreach ($subprojects as $key => $data) {
                $subProjectsWithCount[$key]['client_id'] =$clientDetails['id'];
                $subProjectsWithCount[$key]['client_name'] =$clientDetails['client_name'];
                $subProjectsWithCount[$key]['sub_project_id'] =$data['id'];
                $subProjectsWithCount[$key]['sub_project_name'] = $data['name'];
                $projectName = $subProjectsWithCount[$key]['client_name'];
                // $model_name = ucfirst($projectName) . ucfirst($subProjectsWithCount[$key]['sub_project_name']);
                // $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',$model_name);
                $table_name = Str::slug((Str::lower($projectName).'_'.Str::lower($subProjectsWithCount[$key]['sub_project_name'])),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" .  $modelName; $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();
                    if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                        if (class_exists($modelClass)) {
                            $subProjectsWithCount[$key]['assignedCount'] = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                            $subProjectsWithCount[$key]['CompletedCount'] = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->whereBetween('updated_at',[$startDate,$endDate])->count();
                            $subProjectsWithCount[$key]['PendingCount'] = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                            $subProjectsWithCount[$key]['holdCount'] = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        } else {
                            $subProjectsWithCount[$key]['assignedCount'] ='--';
                            $subProjectsWithCount[$key]['CompletedCount'] = '--';
                            $subProjectsWithCount[$key]['PendingCount'] = '--';
                            $subProjectsWithCount[$key]['holdCount'] = '--';
                        }
                    } else if($loginEmpId) {
                        if (class_exists($modelClass)) {
                            $subProjectsWithCount[$key]['assignedCount'] = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                            $subProjectsWithCount[$key]['CompletedCount'] = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                            $subProjectsWithCount[$key]['PendingCount'] = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                            $subProjectsWithCount[$key]['holdCount'] = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        } else {
                            $subProjectsWithCount[$key]['assignedCount'] ='--';
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

    public function handleRowClick(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $databaseConnection = Str::lower($request->client_name);
                Config::set('database.connections.mysql.database', $databaseConnection);
                return response()->json(['success' => true]);
                } catch (\Exception $e) {
                    log::debug($e->getMessage());
                }
        } else {
            return redirect('/');
        }
    }
    public function clientTabs($clientName) {
        try {
            $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
            $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
            $decodedClientName = Helpers::encodeAndDecodeID($clientName, 'decode');
            $databaseConnection = Str::lower($decodedClientName);
            Config::set('database.connections.mysql.database',$databaseConnection);

            if ($loginEmpId && $loginEmpId == "Admin") {
                $assignedProjectDetails = InventoryWound::where('status','CE_Inprocess')->orderBy('id','desc')->get();
                $pendingProjectDetails = InventoryWound::where('status','CE_Pending')->orderBy('id','desc')->get();
                $completedProjectDetails = InventoryWound::where('status','CE_Completed')->orderBy('id','desc')->get();
                $holdProjectDetails = InventoryWound::where('status','CE_Hold')->orderBy('id','desc')->get();
            } elseif ($loginEmpId) {
                $assignedProjectDetails = InventoryWound::where('status','CE_Inprocess')->where('CE_emp_id',$loginEmpId)->orderBy('id','desc')->get();
                $pendingProjectDetails = InventoryWound::where('status','CE_Pending')->where('CE_emp_id',$loginEmpId)->orderBy('id','desc')->get();
                $completedProjectDetails = InventoryWound::where('status','CE_Completed')->where('CE_emp_id',$loginEmpId)->orderBy('id','desc')->get();
                $holdProjectDetails = InventoryWound::where('status','CE_Hold')->where('CE_emp_id',$loginEmpId)->orderBy('id','desc')->get();
            }
            if ($loginEmpId == "Admin") {
               $duplicateProjectDetails = InventoryWoundDuplicate::whereNotIn('status',['agree','dis_agree'])->orderBy('id','desc')->get();
            } else {
                $duplicateProjectDetails = [];
            }
            return view('productions/clientAssignedTab',compact('assignedProjectDetails','completedProjectDetails','holdProjectDetails','duplicateProjectDetails','databaseConnection'));
        } catch (\Exception $e) {
            log::debug($e->getMessage());
        }
    }
    public function clientAssignedTab($clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           $client = new Client();
           try {
               $resourceName = request('resourceName') != null ? Helpers::encodeAndDecodeID(request('resourceName'), 'decode') : null;
               $userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] !=null ? Session::get('loginDetails')['userDetail']['id']:"";
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
               $column_names = DB::select("DESCRIBE $table_name");
               $columns = array_column($column_names, 'Field');
               $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date','updated_at','created_at', 'deleted_at'];
               $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                   return !in_array($column, $columnsToExclude);
               });
           }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" .  $modelName;
               $modelClassDatas = "App\\Models\\" .  $modelName.'Datas'; $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();
               $assignedProjectDetails = collect();$assignedDropDown=[];$dept= Session::get('loginDetails')['userInfo']['department']['id'];$existingCallerChartsWorkLogs = [];$assignedProjectDetailsStatus = [];$unAssignedCount = 0;
               $duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                    if (class_exists($modelClass)) {
                       $modelClassDuplcates = "App\\Models\\" . $modelName.'Duplicates';
                           if($resourceName != null) {
                                $assignedProjectDetails = $modelClass::where('chart_status','CE_Assigned')->where('CE_emp_id',$resourceName)->orderBy('id','ASC')->limit(2000)->get();
                                $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','desc')->pluck('record_id')->toArray();
                                $assignedCount = $modelClass::where('chart_status','CE_Assigned')->where('CE_emp_id',$resourceName)->count();
                                $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$resourceName)->where('qa_work_status', 'Sampling')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$resourceName)->where('updated_at','<=',$yesterDayDate)->count();
                                $duplicateCount = $modelClassDuplcates::count();
                                $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                                $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','ASC')->limit(2000)->pluck('chart_status')->toArray(); 
                               } else {
                               $assignedProjectDetails = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->orderBy('id','ASC')->limit(2000)->get();
                               $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','desc')->pluck('record_id')->toArray();
                               $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                               $completedCount = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->whereBetween('updated_at',[$startDate,$endDate])->count();
                               $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                               $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                               $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                               $duplicateCount = $modelClassDuplcates::count();
                               $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                               $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','ASC')->limit(2000)->pluck('chart_status')->toArray();      
                           }
                           $assignedDropDownIds = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->select('CE_emp_id')->groupBy('CE_emp_id')->pluck('CE_emp_id')->toArray();
                        $payload = [
                           'token' => '1a32e71a46317b9cc6feb7388238c95d',
                           'client_id' => $decodedProjectName,
                           'user_id' => $userId
                       ];

                        $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_resource_name', [
                           'json' => $payload
                       ]);
                       if ($response->getStatusCode() == 200) {
                            $data = json_decode($response->getBody(), true);
                       } else {
                           return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                       }
                       $assignedDropDown = array_filter($data['userDetail']);
                   }
               } elseif ($loginEmpId) {
                   if (class_exists($modelClass)) {
                       $assignedProjectDetails = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->orderBy('id','ASC')->limit(2000)->get();
                       $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','desc')->pluck('record_id')->toArray();
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                       $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->orderBy('id','ASC')->limit(2000)->pluck('chart_status')->toArray();
                  }
               }
               $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
               ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
               ->select('project_id', 'sub_project_id')
               ->first();
               $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('user_type',[3,$dept])->where('field_type','non_editable')->where('field_type_3','popup_visible')->get();
               $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('user_type',[3,$dept])->where('field_type','editable')->where('field_type_3','popup_visible')->get();

                   return view('productions/clientAssignedTab',compact('assignedProjectDetails','columnsHeader','popUpHeader','popupNonEditableFields','popupEditableFields','modelClass','clientName','subProjectName','assignedDropDown','existingCallerChartsWorkLogs','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','assignedProjectDetailsStatus','unAssignedCount'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
   }
    public function clientPendingTab($clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date','updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" . $modelName;
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();
               $pendingProjectDetails = collect(); $duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$existingCallerChartsWorkLogs = [];$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;$unAssignedCount = 0;
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                   if (class_exists($modelClass)) {
                       $pendingProjectDetails = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','ASC')->limit(2000)->get();
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                       $modelClassDuplcates = "App\\Models\\" .$modelName.'Duplicates';
                       $duplicateCount = $modelClassDuplcates::count();
                       $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                       $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','CE_Pending')->orderBy('id','desc')->pluck('record_id')->toArray();
                   }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                      $pendingProjectDetails = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->where('CE_emp_id',$loginEmpId)->orderBy('id','ASC')->limit(2000)->get();
                      $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                      $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->where('qa_work_status', 'Sampling')->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                      $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','CE_Pending')->orderBy('id','desc')->pluck('record_id')->toArray();
                   }
                 }
                 $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                 $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                 ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                 ->select('project_id', 'sub_project_id')
                 ->first();
                 $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('user_type',[3,$dept])->where('field_type','non_editable')->where('field_type_3','popup_visible')->get();
                 $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('user_type',[3,$dept])->where('field_type','editable')->where('field_type_3','popup_visible')->get();
                return view('productions/clientPendingTab',compact('pendingProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','existingCallerChartsWorkLogs','popUpHeader','popupNonEditableFields','popupEditableFields','unAssignedCount'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }
    public function clientHoldTab($clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date','updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" . $modelName;
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
               $holdProjectDetails = collect();$duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$existingCallerChartsWorkLogs = [];$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                   if (class_exists($modelClass)) {
                       $holdProjectDetails = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','ASC')->limit(2000)->get();
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                       $modelClassDuplcates = "App\\Models\\" . $modelName.'Duplicates';
                       $duplicateCount = $modelClassDuplcates::count();
                       $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                       $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','CE_Hold')->orderBy('id','desc')->pluck('record_id')->toArray();
                   }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                      $holdProjectDetails = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->where('CE_emp_id',$loginEmpId)->orderBy('id','ASC')->limit(2000)->get();
                      $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                      $completedCount = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                      $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','CE_Hold')->orderBy('id','desc')->pluck('record_id')->toArray();
                   }
                 }
                 $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                 $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                 ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                 ->select('project_id', 'sub_project_id')
                 ->first();
                 $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('user_type',[3,$dept])->where('field_type','non_editable')->where('field_type_3','popup_visible')->get();
                 $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('user_type',[3,$dept])->where('field_type','editable')->where('field_type_3','popup_visible')->get();
                return view('productions/clientOnholdTab',compact('holdProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','popUpHeader','popupNonEditableFields','popupEditableFields','existingCallerChartsWorkLogs','unAssignedCount'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }
    public function clientCompletedTab($clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date','updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
               }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" . $modelName;
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
               $completedProjectDetails = collect();$duplicateCount = 0;$assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                   if (class_exists($modelClass)) {
                       $completedProjectDetails = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','ASC')->limit(2000)->get();
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                       $modelClassDuplcates = "App\\Models\\" . $modelName.'Duplicates';
                       $duplicateCount = $modelClassDuplcates::count();
                       $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                   }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                      $completedProjectDetails = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','ASC')->limit(2000)->get();
                      $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                      $completedCount = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                   }
                 }
                 $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                 $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                 ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                 ->select('project_id', 'sub_project_id')
                 ->first();
                 $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('user_type',[3,$dept])->where('field_type','non_editable')->where('field_type_3','popup_visible')->get();
                 $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('user_type',[3,$dept])->where('field_type','editable')->where('field_type_3','popup_visible')->get();

                return view('productions/clientCompletedTab',compact('completedProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','popUpHeader','popupNonEditableFields','popupEditableFields','unAssignedCount'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }

    public function clientReworkTab($clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_rework_comments','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date','updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" . $modelName;
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();$yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
               $revokeProjectDetails = collect(); $duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$existingCallerChartsWorkLogs = [];$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                   if (class_exists($modelClass)) {
                       $revokeProjectDetails = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->orderBy('id','ASC')->limit(2000)->get();
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                       $modelClassDuplcates = "App\\Models\\" .$modelName.'Duplicates';
                       $duplicateCount = $modelClassDuplcates::count();
                       $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                       $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','Revoke')->orderBy('id','desc')->pluck('record_id')->toArray();
                   }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                      $revokeProjectDetails = $modelClass::where('chart_status','Revoke')->whereNull('tl_error_count')->where('CE_emp_id',$loginEmpId)->where('updated_at','<=',$yesterDayDate)->orderBy('id','ASC')->limit(2000)->get();
                      $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                      $completedCount = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $reworkCount = $modelClass::where('chart_status','Revoke')->whereNull('tl_error_count')->where('CE_emp_id',$loginEmpId)->where('updated_at','<=',$yesterDayDate)->count();
                      $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','Revoke')->orderBy('id','desc')->pluck('record_id')->toArray();
                   }
                 }
                 $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                 $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                 ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                 ->select('project_id', 'sub_project_id')
                 ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type', [3, $dept])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('user_type',[3,$dept])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('user_type',  10)->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal = Helpers::qaSubStatusList();
                 return view('productions/clientReworkTab',compact('revokeProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','existingCallerChartsWorkLogs','popUpHeader','popupNonEditableFields','popupEditableFields','popupQAEditableFields','qaSubStatusListVal','unAssignedCount'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }
    public function clientDuplicateTab($clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['id','QA_emp_id','duplicate_status','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date','updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
               }
               $modelName = Str::studly($table_name);
               //    $modelClassDuplcates = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName))."Duplicates";
               //    $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName));
               $modelClassDuplcates = "App\\Models\\" . $modelName."Duplicates";
               $modelClass = "App\\Models\\" .$modelName;
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();$yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
               $duplicateProjectDetails = collect();$duplicateCount = 0;$assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                   if (class_exists($modelClassDuplcates)) {
                        //   $duplicateProjectDetails =  $modelClass::whereNotIn('status',['agree','dis_agree'])->orderBy('id','desc')->get();
                        $duplicateProjectDetails =  $modelClassDuplcates::orderBy('id','ASC')->whereBetween('updated_at',[$startDate,$endDate])->limit(2000)->get();
                        $assignedCount =  $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                        $completedCount = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount =   $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                        $duplicateCount = $modelClassDuplcates::count();
                        $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                   }
                } elseif ($loginEmpId) {
                    if (class_exists($modelClassDuplcates)) {
                       $duplicateProjectDetails = $modelClassDuplcates::where('chart_status','CE_Assigned')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','ASC')->limit(2000)->get();
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->where('qa_work_status', 'Sampling')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                    }
                }

                return view('productions/clientDuplicateTab',compact('duplicateProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','unAssignedCount'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }
    public function clientsDuplicateStatus(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {

            try {
                $status = $request['dropdownStatus'];
                $decodedProjectName = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                // $decodedPracticeName = Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                $decodedPracticeName = $request['subProjectName'] == '--' ? NULL : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                // $decodedsubProjectName = Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClassDuplcates = "App\\Models\\" .$modelName."Duplicates";
                $modelClass = "App\\Models\\" . $modelName;
                // $modelClassDuplcates = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName))."Duplicates";
                // $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName));
             //   dd( $request->all(),$decodedClientName,$decodedsubProjectName );
                // $databaseConnection = $request['dbConn'];
                // Config::set('database.connections.mysql.database',$databaseConnection);
                foreach($request['checkedRowValues'] as $data) {
                    $duplicateRecord = $modelClassDuplcates::where('id',$data['value'])->first();
                    $duplicateRecord->update(['duplicate_status' => $status]);
                }
                return response()->json(['success' => true]);
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
                $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName =  $subProjectName == '--' ? NULL : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                // $decodedsubProjectName = Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project':Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
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
                $coderCompletedRecords = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->get();
                $coderCompletedRecordsCount = count($coderCompletedRecords);
                if( $data['chart_status'] == "CE_Completed") {
                    if($decodedPracticeName == NULL) {
                        $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                         ->where(function($query) use ($loginEmpId) {
                            $query->where('coder_emp_id', $loginEmpId)
                                ->orWhereNull('coder_emp_id');
                        })->orderBy('id', 'desc')->get();
                        // $qasamplingDetailsList  = QualitySampling::where('project_id',$decodedProjectName)->whereIn('coder_emp_id',[$loginEmpId,NULL])->orderBy('id','desc')->get();
                        // if( count($qasamplingDetailsList) == 0) {
                        //     $qasamplingDetailsList  = QualitySampling::where('project_id',$decodedProjectName)->orderBy('id','desc')->get();
                        // }
                        $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL;
                        foreach ($qasamplingDetailsList as $qasamplingDetails) {
                            if($qasamplingDetails != null) {
                                $qaPercentage = $qasamplingDetails["qa_percentage"];
                                $qarecords = $coderCompletedRecordsCount*$qaPercentage/100;
                                // $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                                $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                                $samplingRecordCount =  count($samplingRecord);
                                if($qarecords > $samplingRecordCount) {
                                    $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                    $data['qa_work_status'] = "Sampling";
                                    $data['chart_status'] = "CE_Completed";
                                    break;
                                } else {
                                    //$data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                    $data['qa_work_status'] = "Auto_Close";
                                    // $data['chart_status'] = "QA_Completed";

                                }
                            }
                        }
                    } else {
                        // $qasamplingDetailsList  = QualitySampling::where('project_id',$decodedProjectName)->where('sub_project_id', $decodedPracticeName)->whereIn('coder_emp_id',[$loginEmpId,NULL])->orderBy('id','desc')->get();
                        $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                                                ->where('sub_project_id', $decodedPracticeName)
                                                ->where(function($query) use ($loginEmpId) {
                                                    $query->where('coder_emp_id', $loginEmpId)
                                                        ->orWhereNull('coder_emp_id');
                                                })->orderBy('id', 'desc')->get();
                        // if( count($qasamplingDetailsList) == 0) {
                        //     $qasamplingDetailsList = QualitySampling::where('project_id',$decodedProjectName)->where('sub_project_id',$decodedPracticeName)->orderBy('id','desc')->get();
                        // }
                        $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL;
                        foreach ($qasamplingDetailsList as $qasamplingDetails) {
                            if($qasamplingDetails != null) {
                                $qaPercentage = $qasamplingDetails["qa_percentage"];
                                $qarecords = $coderCompletedRecordsCount*$qaPercentage/100;
                                $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                                $samplingRecordCount =  count($samplingRecord);
                                if($qarecords > $samplingRecordCount ) {
                                    $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                    $data['qa_work_status'] = "Sampling";
                                    $data['chart_status'] = "CE_Completed";
                                    break;
                                } else {
                                    //$data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                    $data['qa_work_status'] = "Auto_Close";
                                    // $data['chart_status'] = "QA_Completed";

                                }
                            }
                        }
                    }

                }
                $record = $originalModelClass::where('id', $data['parent_id'])->first();
                $qaData = $originalModelClass::where('id', $data['parent_id'])->first()->toArray();
                $excludeKeys = ['id', 'created_at', 'updated_at', 'deleted_at'];
                $filteredQAData = collect($qaData)->except($excludeKeys)->toArray();
                $data = array_merge($data, array_diff_key($filteredQAData, $data));
                 if($datasRecord != null) {
                    $datasRecord->update($data);
                  ($data['chart_status'] == "CE_Completed") ? $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status']]) : $record->update( ['chart_status' => $data['chart_status'],'ce_hold_reason' => $data['ce_hold_reason']] );
                } else {
                   ($data['chart_status'] == "CE_Completed") ? $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status']]) : $record->update( ['chart_status' => $data['chart_status'],'ce_hold_reason' => $data['ce_hold_reason']] );
                   $modelClass::create($data);
                }
                // $originalModelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName));

                // $record = $originalModelClass::where('id', $data['parent_id'])->first();//dd($record);
                // $record->update( ['chart_status' => $data['chart_status']] );
                $currentTime = Carbon::now();
                $callChartWorkLogExistingRecord = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                ->where('project_id', $decodedProjectName)
                ->where('sub_project_id', $decodedPracticeName)
                ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->first();//dd($callChartWorkLogExistingRecord,$decodedProjectName,$decodedPracticeName, $currentTime->format('Y-m-d H:i:s'));
                if($callChartWorkLogExistingRecord && $callChartWorkLogExistingRecord != null) {
                    $start_time = Carbon::parse($callChartWorkLogExistingRecord->start_time);
                    $time_difference = $currentTime->diff($start_time);//dd(  $time_difference,$time_difference->format('%H:%I:%S'),$time_difference->h.":".$time_difference->i.":".$time_difference->s );
                    $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                    $callChartWorkLogExistingRecord->update( ['record_status' => $data['chart_status'],'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time] );
                }
                return redirect('/projects_assigned/'.$clientName.'/'.$subProjectName);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function assigneeChange(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {

            try {
                $assigneeId = $request['assigneeId'];
                $decodedProjectName = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                // $decodedPracticeName = Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                $decodedPracticeName = $request['subProjectName'] == '--' ? '--' : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelHistory = "App\\Models\\" . $modelName.'History';
                // $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName));
                // $modelHistory = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName)).'History';
                foreach($request['checkedRowValues'] as $data) {
                    $existingRecord = $modelClass::where('id',$data['value'])->first();
                    $historyRecord = $existingRecord->toArray();
                    $historyRecord['parent_id']= $historyRecord['id'];
                    unset($historyRecord['id']);
                    $modelHistory::create($historyRecord);
                    $existingRecord->update(['CE_emp_id' => $assigneeId]);
                }
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function callerChartWorkLogs(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data =  $request->all();
                $currentTime = Carbon::now();
                $data['emp_id'] = Session::get('loginDetails')['userDetail']['emp_id'];
                $data['project_id'] = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                $data['sub_project_id'] = $data['subProjectName'] == '--' ? NULL : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                $decodedClientName = Helpers::projectName($data['project_id'])->project_name;
                $decodedsubProjectName = $data['sub_project_id'] == NULL ? 'project' :Helpers::subProjectName($data['project_id'] ,$data['sub_project_id'])->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $originalModelClass = "App\\Models\\" . $modelName;
                // $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName));
                $data['start_time'] = $currentTime->format('Y-m-d H:i:s');
                $data['record_status'] = $modelClass::where('id',$data['record_id'])->pluck('chart_status')->toArray()[0];//dd($data,$modelClass);
                // $existingRecordId = CallerChartsWorkLogs::where('record_id',$data['record_id'])->where('record_status',"CE_Assigned")->first();
                $existingRecordId = CallerChartsWorkLogs::where('project_id', $data['project_id'])->where('sub_project_id',$data['sub_project_id'])->where('record_id',$data['record_id'])->where('record_status',$data['record_status'])->where('end_time',NULL)->first();

                if(empty($existingRecordId)) {
                    $startTimeVal = $data['start_time'];
                    $save_flag = CallerChartsWorkLogs::create($data);
                } else {
                    $startTimeVal = $existingRecordId->start_time;
                    $save_flag = 1;
                }

             //   dd($data);
                if($save_flag) {
                   return response()->json(['success' => true,'startTimeVal'=>$startTimeVal]);
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

    public function clientCompletedDatasDetails(Request $request) {
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
                $data['record_status'] = $data['urlDynamicValue'] == "Revoke" ? "Revoke" : 'CE_'.ucwords($data['urlDynamicValue']) ;//dd($data['urlDynamicValue'],$data['record_status']);
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

    public function clientsUpdate(Request $request,$clientName,$subProjectName) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                 $data = $request->all();
                 $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
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
                // $coderCompletedRecords = $originalModelClass::where('chart_status','CE_Completed')->get();
                $coderCompletedRecords = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->get();
                $coderCompletedRecordsCount = count($coderCompletedRecords);
                if( $data['chart_status'] == "CE_Completed") {
                    if($decodedPracticeName == NULL) {
                        $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                                                ->where(function($query) use ($loginEmpId) {
                                                    $query->where('coder_emp_id', $loginEmpId)
                                                        ->orWhereNull('coder_emp_id');
                                                })->orderBy('id', 'desc')->get();
                        // $qasamplingDetailsList = QualitySampling::where('project_id',$decodedProjectName)->where('coder_emp_id',$loginEmpId)->orderBy('id','desc')->get();
                        // if(count($qasamplingDetailsList) == 0) {
                        //     $qasamplingDetailsList = QualitySampling::where('project_id',$decodedProjectName)->orderBy('id','desc')->get();
                        // }
                        $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL;
                        foreach ($qasamplingDetailsList as $qasamplingDetails) {
                            if($qasamplingDetails != null) {
                                $qaPercentage = $qasamplingDetails["qa_percentage"];
                                $qarecords = $coderCompletedRecordsCount*$qaPercentage/100;
                                // $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                                $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                                $samplingRecordCount =  count($samplingRecord);
                                if($qarecords > $samplingRecordCount ) {
                                    $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                    $data['qa_work_status'] = "Sampling";
                                    $data['chart_status'] = "CE_Completed";
                                    break;
                                } else {
                                    //$data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                    $data['qa_work_status'] = "Auto_Close";
                                    // $data['chart_status'] = "QA_Completed";

                                }
                            }
                        }
                    } else {
                        $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                                                ->where('sub_project_id', $decodedPracticeName)
                                                ->where(function($query) use ($loginEmpId) {
                                                    $query->where('coder_emp_id', $loginEmpId)
                                                        ->orWhereNull('coder_emp_id');
                                                })->orderBy('id', 'desc')->get();
                        // $qasamplingDetailsList = QualitySampling::where('project_id',$decodedProjectName)->where('sub_project_id',$decodedPracticeName)->where('coder_emp_id',$loginEmpId)->orderBy('id','desc')->get();
                        // if( count($qasamplingDetailsList) == 0) {
                        //     $qasamplingDetailsList = QualitySampling::where('project_id',$decodedProjectName)->where('sub_project_id',$decodedPracticeName)->orderBy('id','desc')->get();
                        // }
                        $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL;
                        foreach ($qasamplingDetailsList as $qasamplingDetails) {
                            if($qasamplingDetails != null) {
                                $qaPercentage = $qasamplingDetails["qa_percentage"];
                                $qarecords = $coderCompletedRecordsCount*$qaPercentage/100;
                                // $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                                $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                                $samplingRecordCount =  count($samplingRecord);
                                if($qarecords > $samplingRecordCount) {
                                    $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                    $data['qa_work_status'] = "Sampling";
                                    $data['chart_status'] = "CE_Completed";
                                    break;
                                } else {
                                    //$data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                    $data['qa_work_status'] = "Auto_Close";
                                    // $data['chart_status'] = "QA_Completed";

                                }
                            }
                        }
                    }
                }
                $record = $originalModelClass::where('id', $data['parent_id'])->first();
                $qaData = $originalModelClass::where('id', $data['parent_id'])->first()->toArray();
                $excludeKeys = ['id', 'created_at', 'updated_at', 'deleted_at'];
                $filteredQAData = collect($qaData)->except($excludeKeys)->toArray();
                $data = array_merge($data, array_diff_key($filteredQAData, $data));
                if($datasRecord != null) {
                  $datasRecord->update($data);
                  ($data['chart_status'] == "CE_Completed") ? $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status'],'QA_required_sampling' => $data['QA_required_sampling'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code']]) : $record->update( ['chart_status' => $data['chart_status'],'ce_hold_reason' => $data['ce_hold_reason']] );
                } else {
                    $data['parent_id'] = $data['idValue'];
                    ($data['chart_status'] == "CE_Completed") ? $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status'],'QA_required_sampling' => $data['QA_required_sampling'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code']]) : $record->update( ['chart_status' => $data['chart_status'],'ce_hold_reason' => $data['ce_hold_reason']] );
                    $modelClass::create($data);
                }
                $currentTime = Carbon::now();
                $callChartWorkLogExistingRecord = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                ->where('record_status',$data['record_old_status'])
                ->where('project_id', $decodedProjectName)
                ->where('sub_project_id', $decodedPracticeName)
                ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->first();
                  if($callChartWorkLogExistingRecord && $callChartWorkLogExistingRecord != null) {
                    $start_time = Carbon::parse($callChartWorkLogExistingRecord->start_time);
                    $time_difference = $currentTime->diff($start_time);
                    $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                    $callChartWorkLogExistingRecord->update([
                        'record_status' => $data['chart_status'],
                        'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time
                    ]);
                }
                $tabUrl = $data['record_old_status'] == "Revoke" ? $data['record_old_status'] : lcfirst(str_replace('CE_', '', $data['record_old_status']));
                return redirect('/projects_'.$tabUrl.'/'.$clientName.'/'.$subProjectName);
             } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function clientViewDetails(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data =  $request->all();
                $decodedProjectName = Helpers::encodeAndDecodeID($data['clientName'], 'decode');
                $decodedPracticeName = $data['subProjectName'] == '--' ? '--' : Helpers::encodeAndDecodeID($data['subProjectName'], 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
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

    public function clientReworkDatasDetails(Request $request) {
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
                $data['record_status'] = $data['urlDynamicValue'] == "Revoke" ? "Revoke" : 'CE_'.ucwords($data['urlDynamicValue']) ;//dd($data['urlDynamicValue'],$data['record_status']);
                $existingRecordId = CallerChartsWorkLogs::where('project_id', $data['project_id'])->where('sub_project_id',$data['sub_project_id'])->where('record_id',$data['record_id'])->where('record_status',$data['record_status'])->where('end_time',NULL)->first();

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

    public function clientsReworkUpdate(Request $request,$clientName,$subProjectName) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                 $data = $request->all();
                 $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
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

                $data['parent_id'] = $data['parentId'];
                $datasRecord = $modelClass::where('parent_id', $data['parent_id'])->orderBy('id','desc')->first();
                $record = $originalModelClass::where('id', $data['parent_id'])->first();
                $qaData = $originalModelClass::where('id', $data['parent_id'])->first()->toArray();
                $excludeKeys = ['id', 'created_at', 'updated_at', 'deleted_at'];
                $filteredQAData = collect($qaData)->except($excludeKeys)->toArray();
                $data = array_merge($data, array_diff_key($filteredQAData, $data));
                if($data['coder_rework_status'] == 'Accept' && $datasRecord['tl_error_count'] == NULL) {//coder accepted
                    $data['chart_status'] = "QA_Completed";
                    $data['QA_required_sampling'] = "Auto_Close";
                    $data['coder_error_count'] = 1;
                    $data['qa_error_count'] = NULL;
                    $data['tl_comments'] = $datasRecord['tl_comments'];
                 } else if($data['coder_rework_status'] == 'Accept' &&  $datasRecord['tl_error_count'] == 1) {//maanger assigned to coder
                    $data['chart_status'] = "QA_Completed";
                    $data['QA_required_sampling'] = "Auto_Close";
                    $data['qa_error_count'] = NULL;
                    $data['coder_error_count'] = 1;
                    $data['tl_comments'] = $data['coder_rework_reason'].'@'.$loginEmpId;
                    $data['coder_rework_reason'] = $datasRecord['coder_rework_reason'];
                 }
                  else if($data['coder_rework_status'] == 'Rebuttal' &&  $datasRecord['tl_error_count'] == 1) {//maanger assigned to QA
                    $data['chart_status'] = "QA_Completed";
                    $data['QA_required_sampling'] = "Auto_Close";
                    $data['qa_error_count'] = 1;
                    $data['coder_error_count'] = NULL;
                    $data['tl_comments'] = $data['coder_rework_reason'].'@'.$loginEmpId;
                    $data['coder_rework_reason'] = $datasRecord['coder_rework_reason'];
                 } else {
                    $data['chart_status'] = "CE_Completed";
                    $data['QA_required_sampling'] = "Sampling";
                    $data['coder_error_count'] = NULL;
                    $data['qa_error_count'] = NULL;
                    $data['tl_comments'] = $datasRecord['tl_comments'];
                }
                $datasRecord->update( ['chart_status' => $data['chart_status'],'QA_required_sampling' => $data['QA_required_sampling'],'coder_rework_status' => $data['coder_rework_status'],'coder_rework_reason' => $data['coder_rework_reason'],'coder_error_count' => $data['coder_error_count'],'qa_error_count' => $data['qa_error_count'], 'tl_comments' => $data['tl_comments']] );
                $record->update( ['chart_status' => $data['chart_status'],'QA_required_sampling' => $data['QA_required_sampling'],'coder_rework_status' => $data['coder_rework_status'],'coder_rework_reason' => $data['coder_rework_reason'],'coder_error_count' => $data['coder_error_count'],'qa_error_count' => $data['qa_error_count'],'tl_comments' => $data['tl_comments']] );

                return redirect('/projects_Revoke/'.$clientName.'/'.$subProjectName);
                // $tabUrl = $data['record_old_status'] == "Revoke" ? $data['record_old_status'] : lcfirst(str_replace('CE_', '', $data['record_old_status']));
                // return redirect('/projects_'.$tabUrl.'/'.$clientName.'/'.$subProjectName);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function clientUnAssignedTab($clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           $client = new Client();
           try {
               $userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] !=null ? Session::get('loginDetails')['userDetail']['id']:"";
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
               $column_names = DB::select("DESCRIBE $table_name");
               $columns = array_column($column_names, 'Field');
               $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date','updated_at','created_at', 'deleted_at'];
               $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                   return !in_array($column, $columnsToExclude);
               });
           }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" .  $modelName;
               $modelClassDatas = "App\\Models\\" .  $modelName.'Datas'; $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();
               $unAssignedProjectDetails = collect();$assignedDropDown=[];$dept= Session::get('loginDetails')['userInfo']['department']['id'];$existingCallerChartsWorkLogs = [];$unAssignedProjectDetailsStatus = [];$unAssignedCount = 0;
               $duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                    if (class_exists($modelClass)) {
                       $modelClassDuplcates = "App\\Models\\" . $modelName.'Duplicates';
                       $unAssignedProjectDetails = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->orderBy('id','ASC')->limit(2000)->get();
                       $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','desc')->pluck('record_id')->toArray();
                       $assignedDropDownIds = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->select('CE_emp_id')->groupBy('CE_emp_id')->pluck('CE_emp_id')->toArray();
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                       $duplicateCount = $modelClassDuplcates::count();
                       $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                       $unAssignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','ASC')->limit(2000)->pluck('chart_status')->toArray();
                       $payload = [
                           'token' => '1a32e71a46317b9cc6feb7388238c95d',
                           'client_id' => $decodedProjectName,
                           'user_id' => $userId
                       ];

                        $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_resource_name', [
                           'json' => $payload
                       ]);
                       if ($response->getStatusCode() == 200) {
                            $data = json_decode($response->getBody(), true);
                       } else {
                           return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                       }
                       $assignedDropDown = array_filter($data['userDetail']);
                   }
               } elseif ($loginEmpId) {
                   if (class_exists($modelClass)) {
                       $unAssignedProjectDetails = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->orderBy('id','ASC')->limit(2000)->get();
                       $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','desc')->pluck('record_id')->toArray();
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                       $unAssignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->orderBy('id','ASC')->limit(2000)->pluck('chart_status')->toArray();
                  }
               }
               $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
               ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
               ->select('project_id', 'sub_project_id')
               ->first();
               $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('user_type',[3,$dept])->where('field_type','non_editable')->where('field_type_3','popup_visible')->get();
               $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('user_type',[3,$dept])->where('field_type','editable')->where('field_type_3','popup_visible')->get();

                   return view('productions/clientUnAssignedTab',compact('unAssignedProjectDetails','columnsHeader','popUpHeader','popupNonEditableFields','popupEditableFields','modelClass','clientName','subProjectName','assignedDropDown','existingCallerChartsWorkLogs','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','unAssignedProjectDetailsStatus','unAssignedCount'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
   }
}
