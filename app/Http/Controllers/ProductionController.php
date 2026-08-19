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
use App\Models\ARActionCodes;
use App\Models\ProjectColSearchConfig;
use App\Exports\ProductionExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ManualProjectDuplicate;
use App\Jobs\GetProjJob;
use Illuminate\Support\Facades\Cache;
use App\Jobs\GetSubPrjJob;
use App\Models\ClaimHistoryUniqueColumn;
// use App\Models\QuestionAndAnswer;
use App\Jobs\RunQualityExportJob;

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 120);
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
                $data = retry(3, function () use ($payload) {
                    $client = new Client(['verify' => false]);
                    $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_clients_on_user', [
                        'json' => $payload,
                    ]);
                    if ($response->getStatusCode() == 200) {
                        $responseData = json_decode($response->getBody(), true);
                        $clientList = $responseData['clientList'] ?? [];
                        if (empty($clientList)) {
                            return [
                                'projects' => [],
                                'message' => 'No clients found for this user.'
                            ];
                        }

                        $filteredProjects = Helpers::getFilteredClientProjects($clientList);

                        if (empty($filteredProjects)) {
                            return [
                                'projects' => [],
                                'message' => 'No configured projects found. Please complete form configuration for this project/subproject.'
                            ];
                        }

                        return [
                            'projects' => $filteredProjects,
                            'message' => null
                        ];
                    } elseif ($response->getStatusCode() == 429) {
                        $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                        sleep($retryAfter);
                        throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                    } else {
                        throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                    }
                }, 4000);
                 $projects = $data['projects'];
                 $message = $data['message'];
                  return view('productions/clients',compact('projects','message'));
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
            $client = new Client(['verify' => false]);
            $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_practice_on_client', [
                'json' => $payload
            ]);
            if ($response->getStatusCode() == 200) {
                 $data = json_decode($response->getBody(), true);
            } else {
                 return response()->json(['error' => 'API request failed'], $response->getStatusCode());
            }
            $data['practiceList'] = Helpers::filterPracticeList(
                $data['practiceList'] ?? [],
                $data['clientInfo']['id'] ?? null
            );
            $subprojects = $data['practiceList'];
            $clientDetails = $data['clientInfo'];
            if (empty($subprojects)) {
                    return response()->json([
                        'subprojects' => [],
                        'message' => 'No configured subprojects found for this project.'
                    ]);
                }

          //  $subprojects = subproject::with(['clientName'])->where('project_id',$request->project_id)->where('status','Active')->get();
            $subProjectsWithCount = [];
            foreach ($subprojects as $key => $data) {
                 $configurationExists = DB::table('inventory_upload_configuration')
                ->where('project_id',$clientDetails['id'])
                ->where('sub_project_id', $data['id'])
                ->exists();
                $paProject = Helpers::projectName($clientDetails['id']);
                $paProjectName = $paProject ? $paProject->project_name : null;
                $projectType = formConfiguration::where('project_id',$clientDetails['id'])->where('sub_project_id',$data['id'])->first('project_type');
                $subProjectsWithCount[$key]['client_id'] =$clientDetails['id'];
                // $subProjectsWithCount[$key]['client_name'] = Helpers::projectName($clientDetails["id"])->project_name;//$clientDetails['client_name'];
                $subProjectsWithCount[$key]['client_name'] = $paProjectName;//$clientDetails['client_name'];
                $subProjectsWithCount[$key]['sub_project_id'] =$data['id'];
                $subProjectsWithCount[$key]['sub_project_name'] = $data['name'];
                $subProjectsWithCount[$key]['project_type'] =$projectType ? $projectType->project_type : '--';
                $subProjectsWithCount[$key]['inventory_upload_config'] = !$configurationExists ? 'no icon' : 'yes';
                $projectName = $subProjectsWithCount[$key]['client_name'];
                // $model_name = ucfirst($projectName) . ucfirst($subProjectsWithCount[$key]['sub_project_name']);
                // $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',$model_name);
                $table_name = Str::slug((Str::lower($projectName).'_'.Str::lower($subProjectsWithCount[$key]['sub_project_name'])),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" .  $modelName; $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();
                   if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                        if (class_exists($modelClass)) {
                            $subProjectsWithCount[$key]['assignedCount'] = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                            $subProjectsWithCount[$key]['CompletedCount'] = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
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
                            $subProjectsWithCount[$key]['CompletedCount'] = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
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
            Log::debug('getSubProjects error: ' . $e->getMessage());

            return response()->json([
                'subprojects' => [],
                'message' => 'Unable to load subprojects. Please try again.'
            ], 500);
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
    public function clientAssignedTab(Request $request,$clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           $client = new Client(['verify' => false]);
           try {
               $resourceName = request('resourceName') != null ? Helpers::encodeAndDecodeID(request('resourceName'), 'decode') : NULL;
               $userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] !=null ? Session::get('loginDetails')['userDetail']['id']:"";
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
              // $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $paProject = Helpers::projectName($decodedProjectName);
               $decodedClientName = $paProject ? $paProject->project_name : null;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName);
               if($decodedsubProjectName != null &&  $decodedsubProjectName != 'project') {
                $decodedsubProjectName= $decodedsubProjectName->sub_project_name;
               }
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers','ar_status_code','ar_action_code',
                    'updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" .  $modelName;
               if (class_exists($modelClass)) {
                    $query = $modelClass::query();
                    $searchData = [];
                    if($request['_token'] != null) {
                            foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                            $searchData[$key] = $value;
                                if (is_array($value)) {
                                    $value = implode('_el_', $value); 
                                }

                                // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                                if (is_numeric($value) || is_bool($value)) {
                                    $query->where($key, $value);  // Exact match for numeric/boolean
                                } elseif ($this->isDate($value)) {  // Check if it's a date
                                    $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                                } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                                    $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                                } else {
                                    if($value != null) {
                                    $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                                    }
                                }
                            }
                        }
                    } else {
                        return redirect()->back();
                    }
               $modelClassDatas = "App\\Models\\" .  $modelName.'Datas'; $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();
               $assignedProjectDetails = collect();$assignedDropDown=[];$dept= Session::get('loginDetails')['userInfo']['department']['id'];$existingCallerChartsWorkLogs = [];$assignedProjectDetailsStatus = [];$unAssignedCount = 0;
               $arAutoCloseCount=0; $rebuttalCount =0; $arNonWorkableCount =0; $duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
                $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
                $columnsHeader = array_values(
                    array_diff($columnsHeader, $excludeColumns)
                );
                $model = $query->getModel();
                $allColumns = Schema::getColumnListing($model->getTable());  
                $selectColumns = array_diff($allColumns, $excludeColumns);
            
                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                    if (class_exists($modelClass)) {
                       $modelClassDuplcates = "App\\Models\\" . $modelName.'Duplicates';
                           if($resourceName != null) {
                            $existingCallerChartsWorkLogsInprocess=[];
                            $existingCallerChartsWorkLogs=[];
                                    // $existingCallerChartsWorkLogsInprocess = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('record_status','CE_Inprocess')->orderBy('id','DESC')->pluck('record_id')->toArray();
                                    // $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','DESC')->pluck('record_id')->toArray();
                                    $assignedProjectDetails = $query->whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$resourceName);
                                    // if (!empty($existingCallerChartsWorkLogs) && $existingCallerChartsWorkLogs[0] != null) {
                                    //     $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                                    // }
                                    // if (!empty($existingCallerChartsWorkLogsInprocess) && $existingCallerChartsWorkLogsInprocess[0] != null) {
                                    //     $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogsInprocess) . ') DESC');
                                    // }
                                    $assignedProjectDetails = $assignedProjectDetails->orderBy('id', 'ASC')->paginate(25);
                                    // $assignedCount = $modelClass::where('chart_status','CE_Assigned')->where('CE_emp_id',$resourceName)->count();
                                    // $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    // $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    // $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    // $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$resourceName)->where('updated_at','<=',$yesterDayDate)->count();
                                    // $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    // $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                                    // $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                                    // $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    // $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                    //                                                         $query->whereNull('ar_manager_rebuttal_status')
                                    //                                                             ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                                    //                                                 })->where('CE_emp_id',$resourceName)
                                    //                                                 // ->whereBetween('updated_at',[$startDate,$endDate])
                                    //                                                 ->count();
                                    // $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();                                                
                                    $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','ASC')->pluck('chart_status')->toArray(); 
                              
                                    } else {
                                $existingCallerChartsWorkLogsInprocess=[];
                                $existingCallerChartsWorkLogs=[];
                                    // $existingCallerChartsWorkLogsInprocess = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('record_status','CE_Inprocess')->orderBy('id','DESC')->pluck('record_id')->toArray();
                                    // $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','DESC')->pluck('record_id')->toArray();
                                    
                                    $assignedProjectDetails = $query->whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id');                        
                                    // if (!empty($existingCallerChartsWorkLogs) && $existingCallerChartsWorkLogs[0] != null) {
                                    //         $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                                    // }
                                    // if (!empty($existingCallerChartsWorkLogsInprocess) && $existingCallerChartsWorkLogsInprocess[0] != null) {
                                    //     $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogsInprocess) . ') DESC');
                                    // }
                                    $assignedProjectDetails = $assignedProjectDetails->orderBy('id', 'ASC')->paginate(25);
                                    // $assignedProjectDetails = $query->whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->orderBy('id','ASC')->paginate(50);
                                    // $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                                    // $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    // $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    // $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    //    $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                                //     $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                //     $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                                //     $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                                //     $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->whereBetween('updated_at',[$startDate,$endDate])->count();   
                                //     $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                //                                                     $query->whereNull('ar_manager_rebuttal_status')
                                //                                                         ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                                //                                             })
                                //                                            // ->whereBetween('updated_at',[$startDate,$endDate])
                                //                                             ->count();
                                //    $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();   
                                    $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','ASC')->pluck('chart_status')->toArray();   
                         
                                   }
                   } else {
                        return redirect()->back();
                   }
               } elseif ($loginEmpId) {
                   if (class_exists($modelClass)) {
                     $existingCallerChartsWorkLogsInprocess=[];
                                $existingCallerChartsWorkLogs=[];

                    //    $existingCallerChartsWorkLogsInprocess = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('record_status','CE_Inprocess')->orderBy('id','DESC')->pluck('record_id')->toArray();
                    //    $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','DESC')->pluck('record_id')->toArray();
                       
                       $assignedProjectDetails = $query->whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId);
                    //     if (!empty($existingCallerChartsWorkLogs) && $existingCallerChartsWorkLogs[0] != null) {
                    //         $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                    //     }
                    //     $existingCallerChartsWorkLogsInprocess = array_filter($existingCallerChartsWorkLogsInprocess, function ($value) {
                    //     return !is_null($value) && $value !== '';
                    //     });

                    //     if (!empty($existingCallerChartsWorkLogsInprocess)) {
                    //     $assignedProjectDetails = $assignedProjectDetails->orderByRaw(
                    //     'FIELD(id, ' . implode(',', $existingCallerChartsWorkLogsInprocess) . ') DESC'
                    //     );
                    //     }
                       /*  if (!empty($existingCallerChartsWorkLogsInprocess) && $existingCallerChartsWorkLogsInprocess[0] != null) {
                            $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogsInprocess) . ') DESC');
                        } */
                       $assignedProjectDetails = $assignedProjectDetails->orderBy('id', 'ASC')->paginate(25);
                    //    $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                    //    $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //    $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //    $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //    $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                    //    $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //    $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //    $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                    //                 $query->whereNull('ar_manager_rebuttal_status')
                    //                     ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                    //         })->where('CE_emp_id',$loginEmpId)
                    //         // ->whereBetween('updated_at',[$startDate,$endDate])
                    //         ->count();
                    //     $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->orderBy('id','ASC')->pluck('chart_status')->toArray();
                  
                        } else {
                    return redirect()->back();
                  }
               }
               $assignedProjectDetails->getCollection()->transform(function ($item) use ($excludeColumns) {

                    foreach ($excludeColumns as $column) {
                        unset($item->{$column});
                    }

                    return $item;
                });

               $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
               ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
               ->select('project_id', 'sub_project_id')
               ->first();
               $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('field_type_3','popup_visible')->where('field_type','non_editable')->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->get();
               $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('field_type_3','popup_visible')->where('field_type','editable')->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->get();
               $projectColSearchFields = Helpers::excludePopupNonVisibleSearchFields(ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get(), $decodedProjectName, $subProjectId);
               $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();
               $projectTypeSettings = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->first();
               $attributes = [];
               $manualDuplciate = ManualProjectDuplicate::select('duplicate_column')->where('project_id', $decodedProjectName)
               ->where('sub_project_id', $decodedPracticeName)->get();
               if(count($manualDuplciate) > 0) {
                    foreach($manualDuplciate as $duplicateColumn) {        
                            $attributes[$duplicateColumn['duplicate_column']]= $duplicateColumn['duplicate_column'];
                    }
                }
                    $claimHistoryAttributes = [];
                    $clmnHistoryField = ClaimHistoryUniqueColumn::select('unique_column')->where('project_id', $decodedProjectName)
                    ->where('sub_project_id',$decodedPracticeName)->get();
                    if(count($clmnHistoryField) > 0) {
                            foreach($clmnHistoryField as $historyColumn) {        
                                    $claimHistoryAttributes[$historyColumn['unique_column']]= $historyColumn['unique_column'];
                            }
                        }
                $popupMulineFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('field_type_3','popup_visible')->where('field_type','editable')->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->get();
                $arNonWorkableReasonList = Helpers::getArNonWorkableReasonList();
                return view('productions/clientAssignedTab',compact('assignedProjectDetails','columnsHeader','popUpHeader','popupNonEditableFields','popupEditableFields','modelClass','clientName','subProjectName','assignedDropDown','existingCallerChartsWorkLogs','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','assignedProjectDetailsStatus','unAssignedCount','arNonWorkableCount','rebuttalCount','projectColSearchFields','searchData','resourceName','projectTypeSettings','existingCallerChartsWorkLogsInprocess','attributes','popupMulineFields','arAutoCloseCount','claimHistoryAttributes','arNonWorkableReasonList'));
           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }
   
    public function clientAssignedTab_bk_live(Request $request,$clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           $client = new Client(['verify' => false]);
           try {
               $resourceName = request('resourceName') != null ? Helpers::encodeAndDecodeID(request('resourceName'), 'decode') : NULL;
               $userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] !=null ? Session::get('loginDetails')['userDetail']['id']:"";
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
              // $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $paProject = Helpers::projectName($decodedProjectName);
               $decodedClientName = $paProject ? $paProject->project_name : null;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName);
               if($decodedsubProjectName != null &&  $decodedsubProjectName != 'project') {
                $decodedsubProjectName= $decodedsubProjectName->sub_project_name;
               }
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers','ar_status_code','ar_action_code',
                    'updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" .  $modelName;
               if (class_exists($modelClass)) {
                    $query = $modelClass::query();
                    $searchData = [];
                    if($request['_token'] != null) {
                            foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                            $searchData[$key] = $value;
                                if (is_array($value)) {
                                    $value = implode('_el_', $value); 
                                }

                                // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                                if (is_numeric($value) || is_bool($value)) {
                                    $query->where($key, $value);  // Exact match for numeric/boolean
                                } elseif ($this->isDate($value)) {  // Check if it's a date
                                    $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                                } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                                    $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                                } else {
                                    if($value != null) {
                                    $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                                    }
                                }
                            }
                        }
                    } else {
                        return redirect()->back();
                    }
               $modelClassDatas = "App\\Models\\" .  $modelName.'Datas'; $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();
               $assignedProjectDetails = collect();$assignedDropDown=[];$dept= Session::get('loginDetails')['userInfo']['department']['id'];$existingCallerChartsWorkLogs = [];$assignedProjectDetailsStatus = [];$unAssignedCount = 0;
               $duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
              if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                    if (class_exists($modelClass)) {
                       $modelClassDuplcates = "App\\Models\\" . $modelName.'Duplicates';
                           if($resourceName != null) {
                                    $existingCallerChartsWorkLogsInprocess = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('record_status','CE_Inprocess')->orderBy('id','DESC')->pluck('record_id')->toArray();
                                    $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','DESC')->pluck('record_id')->toArray();
                                    $assignedProjectDetails = $query->whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$resourceName);
                                    if (!empty($existingCallerChartsWorkLogs) && $existingCallerChartsWorkLogs[0] != null) {
                                        $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                                    }
                                    if (!empty($existingCallerChartsWorkLogsInprocess) && $existingCallerChartsWorkLogsInprocess[0] != null) {
                                        $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogsInprocess) . ') DESC');
                                    }
                                    $assignedProjectDetails = $assignedProjectDetails->orderBy('id', 'ASC')->paginate(50);
                                    $assignedCount = $modelClass::where('chart_status','CE_Assigned')->where('CE_emp_id',$resourceName)->count();
                                    $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    // $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$resourceName)->where('updated_at','<=',$yesterDayDate)->count();
                                    $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                                    $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                                    $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','ASC')->pluck('chart_status')->toArray(); 
                                    $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                                                                            $query->whereNull('ar_manager_rebuttal_status')
                                                                                                ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                                                                                    })->where('CE_emp_id',$resourceName)
                                                                                    // ->whereBetween('updated_at',[$startDate,$endDate])
                                                                                    ->count();
                                    $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();                                                
                               } else {
                                    $existingCallerChartsWorkLogsInprocess = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('record_status','CE_Inprocess')->orderBy('id','DESC')->pluck('record_id')->toArray();
                                    $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','DESC')->pluck('record_id')->toArray();
                                    
                                    $assignedProjectDetails = $query->whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id');                        
                                    if (!empty($existingCallerChartsWorkLogs) && $existingCallerChartsWorkLogs[0] != null) {
                                            $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                                    }
                                    if (!empty($existingCallerChartsWorkLogsInprocess) && $existingCallerChartsWorkLogsInprocess[0] != null) {
                                        $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogsInprocess) . ') DESC');
                                    }
                                    $assignedProjectDetails = $assignedProjectDetails->orderBy('id', 'ASC')->paginate(50);
                                    $assignedProjectDetails = $query->whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->orderBy('id','ASC')->paginate(50);
                                    $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                                    $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    //    $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                                    $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                    $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                                    $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                                    $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','ASC')->pluck('chart_status')->toArray();   
                                    $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->whereBetween('updated_at',[$startDate,$endDate])->count();   
                                    $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                                                                    $query->whereNull('ar_manager_rebuttal_status')
                                                                                        ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                                                                            })
                                                                           // ->whereBetween('updated_at',[$startDate,$endDate])
                                                                            ->count();
                                   $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();   
                           }
                   } else {
                        return redirect()->back();
                   }
               } elseif ($loginEmpId) {
                   if (class_exists($modelClass)) {
                       $existingCallerChartsWorkLogsInprocess = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('record_status','CE_Inprocess')->orderBy('id','DESC')->pluck('record_id')->toArray();
                       $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','DESC')->pluck('record_id')->toArray();
                       
                       $assignedProjectDetails = $query->whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId);
                        if (!empty($existingCallerChartsWorkLogs) && $existingCallerChartsWorkLogs[0] != null) {
                            $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                        }
                        $existingCallerChartsWorkLogsInprocess = array_filter($existingCallerChartsWorkLogsInprocess, function ($value) {
                        return !is_null($value) && $value !== '';
                        });

                        if (!empty($existingCallerChartsWorkLogsInprocess)) {
                        $assignedProjectDetails = $assignedProjectDetails->orderByRaw(
                        'FIELD(id, ' . implode(',', $existingCallerChartsWorkLogsInprocess) . ') DESC'
                        );
                        }
                       /*  if (!empty($existingCallerChartsWorkLogsInprocess) && $existingCallerChartsWorkLogsInprocess[0] != null) {
                            $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogsInprocess) . ') DESC');
                        } */
                       $assignedProjectDetails = $assignedProjectDetails->orderBy('id', 'ASC')->paginate(50);
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //    $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->orderBy('id','ASC')->pluck('chart_status')->toArray();
                       $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                    $query->whereNull('ar_manager_rebuttal_status')
                                        ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                            })->where('CE_emp_id',$loginEmpId)
                            // ->whereBetween('updated_at',[$startDate,$endDate])
                            ->count();
                        $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                  } else {
                    return redirect()->back();
                  }
               }
               $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
               ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
               ->select('project_id', 'sub_project_id')
               ->first();
               $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('field_type_3','popup_visible')->where('field_type','non_editable')->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->get();
               $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('field_type_3','popup_visible')->where('field_type','editable')->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->get();
               $projectColSearchFields = Helpers::excludePopupNonVisibleSearchFields(ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get(), $decodedProjectName, $subProjectId);
               $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();
               $projectTypeSettings = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->first();
               $attributes = [];
               $manualDuplciate = ManualProjectDuplicate::select('duplicate_column')->where('project_id', $decodedProjectName)
               ->where('sub_project_id', $decodedPracticeName)->get();
               if(count($manualDuplciate) > 0) {
                    foreach($manualDuplciate as $duplicateColumn) {        
                            $attributes[$duplicateColumn['duplicate_column']]= $duplicateColumn['duplicate_column'];
                    }
                }
                    $claimHistoryAttributes = [];
                    $clmnHistoryField = ClaimHistoryUniqueColumn::select('unique_column')->where('project_id', $decodedProjectName)
                    ->where('sub_project_id',$decodedPracticeName)->get();
                    if(count($clmnHistoryField) > 0) {
                            foreach($clmnHistoryField as $historyColumn) {        
                                    $claimHistoryAttributes[$historyColumn['unique_column']]= $historyColumn['unique_column'];
                            }
                        }
                $popupMulineFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('field_type_3','popup_visible')->where('field_type','editable')->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->get();
                $arNonWorkableReasonList = Helpers::getArNonWorkableReasonList();
                return view('productions/clientAssignedTab',compact('assignedProjectDetails','columnsHeader','popUpHeader','popupNonEditableFields','popupEditableFields','modelClass','clientName','subProjectName','assignedDropDown','existingCallerChartsWorkLogs','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','assignedProjectDetailsStatus','unAssignedCount','arNonWorkableCount','rebuttalCount','projectColSearchFields','searchData','resourceName','projectTypeSettings','existingCallerChartsWorkLogsInprocess','attributes','popupMulineFields','arAutoCloseCount','claimHistoryAttributes','arNonWorkableReasonList'));
           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
   }
    public function clientPendingTab(Request $request,$clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $paProject = Helpers::projectName($decodedProjectName);
               $decodedClientName = $paProject ? $paProject->project_name : null;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" . $modelName;
               $query = $modelClass::query();
               $searchData = [];
               if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                       $searchData[$key] = $value;
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                        }

                        // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  // Exact match for numeric/boolean
                        } elseif ($this->isDate($value)) {  // Check if it's a date
                            $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            if($value != null) {
                              $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                            }
                        }
                    }
                }
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();
               $pendingProjectDetails = collect(); $duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$existingCallerChartsWorkLogs = [];$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;$unAssignedCount = 0;
                $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
                    $columnsHeader = array_values(
                        array_diff($columnsHeader, $excludeColumns)
                    );
                $model = $query->getModel();
                $allColumns = Schema::getColumnListing($model->getTable());  
                $selectColumns = array_diff($allColumns, $excludeColumns);
           
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                   if (class_exists($modelClass)) {
                    //    $pendingProjectDetails = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','ASC')->get();
                       $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','CE_Pending')->orderBy('id','DESC')->pluck('record_id')->toArray();
                       $pendingProjectDetails = $query->where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate]);
                            if (!empty($existingCallerChartsWorkLogs)) {
                                $pendingProjectDetails = $pendingProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                            }
                       $pendingProjectDetails = $pendingProjectDetails->orderBy('id', 'DESC')->paginate(50);
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //    $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $modelClassDuplcates = "App\\Models\\" .$modelName.'Duplicates';
                       $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                       $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                       $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                    $query->whereNull('ar_manager_rebuttal_status')
                                        ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                            })
                            // ->whereBetween('updated_at',[$startDate,$endDate])
                            ->count();
                       $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();                                                
                     
                   }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                    //   $pendingProjectDetails = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->where('CE_emp_id',$loginEmpId)->orderBy('id','ASC')->get();
                      $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','CE_Pending')->orderBy('id','DESC')->pluck('record_id')->toArray();
                      $pendingProjectDetails = $query->where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->where('CE_emp_id',$loginEmpId);
                      if (!empty($existingCallerChartsWorkLogs)) {
                          $pendingProjectDetails = $pendingProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                      }
                      $pendingProjectDetails = $pendingProjectDetails->orderBy('id', 'DESC')->paginate(50);
                      $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                      $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //   $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                      $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                $query->whereNull('ar_manager_rebuttal_status')
                                    ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                        })->where('CE_emp_id',$loginEmpId)
                        // ->whereBetween('updated_at',[$startDate,$endDate])
                        ->count();
                        $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();                                                
                     
                    }
                }
                $pendingProjectDetails->getCollection()->transform(function ($item) use ($excludeColumns) {
                    foreach ($excludeColumns as $column) {
                        unset($item->{$column});
                        }

                        return $item;
                    });
                 $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                 $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                 ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                 ->select('project_id', 'sub_project_id')
                 ->first();
                 $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','non_editable')->where('field_type_3','popup_visible')->get();
                 $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','editable')->where('field_type_3','popup_visible')->get();
                 $arStatusList = Helpers::arStatusList();
                 $arActionListVal = Helpers::arActionList();
                 $arDenialList = Helpers::arDenialList();
                 $arSubStatusList = Helpers::arSubStatusList();
                 $projectColSearchFields = Helpers::excludePopupNonVisibleSearchFields(ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get(), $decodedProjectName, $subProjectId);
                 $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();  
                 $projectTypeSettings = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->first();
                return view('productions/clientPendingTab',compact('pendingProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','existingCallerChartsWorkLogs','popUpHeader','popupNonEditableFields','popupEditableFields','unAssignedCount','arStatusList','arActionListVal','arNonWorkableCount','rebuttalCount','projectColSearchFields','projectColSearchFieldsType','searchData','arAutoCloseCount','arDenialList','arSubStatusList','projectTypeSettings'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }
    public function clientHoldTab(Request $request,$clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $resourceName = request('resourceName') != null ? Helpers::encodeAndDecodeID(request('resourceName'), 'decode') : null;
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
              // $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
              $paProject = Helpers::projectName($decodedProjectName);
              $decodedClientName = $paProject ? $paProject->project_name : null;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" . $modelName;
               $query = $modelClass::query();
               $searchData = [];
               if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                       $searchData[$key] = $value;
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                        }

                        // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  // Exact match for numeric/boolean
                        } elseif ($this->isDate($value)) {  // Check if it's a date
                            $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            if($value != null) {
                              $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                            }
                        }
                    }
                }
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
               $holdProjectDetails = collect();$duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$existingCallerChartsWorkLogs = [];$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
               $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
               $columnsHeader = array_values(
                   array_diff($columnsHeader, $excludeColumns)
               );
           $model = $query->getModel();
           $allColumns = Schema::getColumnListing($model->getTable());  
           $selectColumns = array_diff($allColumns, $excludeColumns);
           
                 if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                   if (class_exists($modelClass)) {
                        if($resourceName != null) {
                            $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','CE_Hold')->orderBy('id','DESC')->pluck('record_id')->toArray();
                            // $holdProjectDetails = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','ASC')->get();
                            $holdProjectDetails = $query->where('chart_status','CE_Hold')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate]);
                            if (!empty($existingCallerChartsWorkLogs)) {
                                $holdProjectDetails = $holdProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                            }
                            $holdProjectDetails = $holdProjectDetails->orderBy('id', 'DESC')->paginate(50);
                            $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$resourceName)->whereNotNull('CE_emp_id')->count();
                            $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                            $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                            $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                            // $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$resourceName)->where('updated_at','<=',$yesterDayDate)->count();
                            $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                            $modelClassDuplcates = "App\\Models\\" . $modelName.'Duplicates';
                            $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                            $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->where('CE_emp_id',$resourceName)->whereNull('CE_emp_id')->count();
                            $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                            $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                        $query->whereNull('ar_manager_rebuttal_status')
                                            ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                                })->where('CE_emp_id',$resourceName)
                                // ->whereBetween('updated_at',[$startDate,$endDate])
                                ->count();
                                $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$resourceName)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        } else {
                            // $holdProjectDetails = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','ASC')->get();
                            $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','CE_Hold')->orderBy('id','DESC')->pluck('record_id')->toArray();
                            $holdProjectDetails = $query->where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate]);
                            if (!empty($existingCallerChartsWorkLogs)) {
                                $holdProjectDetails = $holdProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                            }
                            $holdProjectDetails = $holdProjectDetails->orderBy('id', 'DESC')->paginate(50);
                            $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                            $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                            $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                            $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                            // $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                            $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                            $modelClassDuplcates = "App\\Models\\" . $modelName.'Duplicates';
                            $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                            $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                            $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->whereBetween('updated_at',[$startDate,$endDate])->count(); 
                            $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                        $query->whereNull('ar_manager_rebuttal_status')
                                            ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                                })
                                //->whereBetween('updated_at',[$startDate,$endDate])
                                ->count();
                            $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        }
                    }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                    //   $holdProjectDetails = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->where('CE_emp_id',$loginEmpId)->orderBy('id','ASC')->get();
                    $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','CE_Hold')->orderBy('id','DESC')->pluck('record_id')->toArray();
                    $holdProjectDetails = $query->where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->where('CE_emp_id',$loginEmpId);
                    if (!empty($existingCallerChartsWorkLogs)) {
                        $holdProjectDetails = $holdProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                    }
                    $holdProjectDetails = $holdProjectDetails->orderBy('id', 'DESC')->paginate(50);
                    $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                    $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //   $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                    $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                $query->whereNull('ar_manager_rebuttal_status')
                                    ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                        })->where('CE_emp_id',$loginEmpId)
                        // ->whereBetween('updated_at',[$startDate,$endDate])
                        ->count();
                        $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                }
                $holdProjectDetails->getCollection()->transform(function ($item) use ($excludeColumns) {
                    foreach ($excludeColumns as $column) {
                        unset($item->{$column});
                    }
                    return $item;
                });
                 $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                 $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                 ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                 ->select('project_id', 'sub_project_id')
                 ->first();
                 $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','non_editable')->where('field_type_3','popup_visible')->get();
                 $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','editable')->where('field_type_3','popup_visible')->get();
                 $arStatusList = Helpers::arStatusList();
                 $arActionListVal = Helpers::arActionList();
                 $arDenialList = Helpers::arDenialList();
                 $arSubStatusList = Helpers::arSubStatusList();
                 $projectColSearchFields = Helpers::excludePopupNonVisibleSearchFields(ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get(), $decodedProjectName, $subProjectId);
                 $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();  
                 $projectTypeSettings = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->first();
                return view('productions/clientOnholdTab',compact('holdProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','popUpHeader','popupNonEditableFields','popupEditableFields','existingCallerChartsWorkLogs','unAssignedCount','arStatusList','arActionListVal','arNonWorkableCount','rebuttalCount','projectColSearchFields','projectColSearchFieldsType','searchData','arAutoCloseCount','arDenialList','arSubStatusList','projectTypeSettings'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }
    public function clientCompletedTab(Request $request,$clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $paProject = Helpers::projectName($decodedProjectName);
               $decodedClientName = $paProject ? $paProject->project_name : null;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
               }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" . $modelName;
               $query = $modelClass::query();
               $searchData = [];
               if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                       $searchData[$key] = $value;
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                        }

                        // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  // Exact match for numeric/boolean
                        } elseif ($this->isDate($value)) {  // Check if it's a date
                            $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            if($value != null) {
                              $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                            }
                        }
                    }
                }
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
               $completedProjectDetails = collect();$duplicateCount = 0;$assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
               $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
                $columnsHeader = array_values(
                    array_diff($columnsHeader, $excludeColumns)
                );
            $model = $query->getModel();
            $allColumns = Schema::getColumnListing($model->getTable());  
           $selectColumns = array_diff($allColumns, $excludeColumns);
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                   if (class_exists($modelClass)) {
                       $completedProjectDetails = $query->where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','DESC')->paginate(50); 
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //    $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $modelClassDuplcates = "App\\Models\\" . $modelName.'Duplicates';
                       $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                       $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                       $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                $query->whereNull('ar_manager_rebuttal_status')
                                    ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                        })
                        // ->whereBetween('updated_at',[$startDate,$endDate])
                        ->count();
                        $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                   }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                      $completedProjectDetails = $query->where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','DESC')->paginate(50);
                      $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                      $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        //   $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                      $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                    $query->whereNull('ar_manager_rebuttal_status')
                                        ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                            })->where('CE_emp_id',$loginEmpId)
                            // ->whereBetween('updated_at',[$startDate,$endDate])
                            ->count();
                      $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                 }
                 $completedProjectDetails->getCollection()->transform(function ($item) use ($excludeColumns) {
                    foreach ($excludeColumns as $column) {
                        unset($item->{$column});
                    }
                    return $item;
                });
                 $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                 $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                 ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                 ->select('project_id', 'sub_project_id')
                 ->first();
                 $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','non_editable')->where('field_type_3','popup_visible')->get();
                 $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','editable')->where('field_type_3','popup_visible')->get();
                 $arStatusList = Helpers::arStatusList();
                 $arActionListVal = Helpers::arActionList();
                 $arDenialList = Helpers::arDenialList();
                 $arSubStatusList = Helpers::arSubStatusList();
                 $projectColSearchFields = Helpers::excludePopupNonVisibleSearchFields(ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get(), $decodedProjectName, $subProjectId);
                 $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();  
                return view('productions/clientCompletedTab',compact('completedProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','popUpHeader','popupNonEditableFields','popupEditableFields','unAssignedCount','arStatusList','arActionListVal','arNonWorkableCount','rebuttalCount','projectColSearchFields','projectColSearchFieldsType','searchData','arAutoCloseCount','arDenialList','arDenialList','arSubStatusList'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }

    public function clientReworkTab(Request $request,$clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $paProject = Helpers::projectName($decodedProjectName);
               $decodedClientName = $paProject ? $paProject->project_name : null;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_rework_comments','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" . $modelName;
               $query = $modelClass::query();
               $searchData = [];
               if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                       $searchData[$key] = $value;
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                        }

                        // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  // Exact match for numeric/boolean
                        } elseif ($this->isDate($value)) {  // Check if it's a date
                            $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            if($value != null) {
                              $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                            }
                        }
                    }
                }
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();$yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
               $revokeProjectDetails = collect(); $duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$existingCallerChartsWorkLogs = [];$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
                    $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
                    $columnsHeader = array_values(
                        array_diff($columnsHeader, $excludeColumns)
                    );
                $model = $query->getModel();
                $allColumns = Schema::getColumnListing($model->getTable());  
                $selectColumns = array_diff($allColumns, $excludeColumns);
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                   if (class_exists($modelClass)) {
                    //    $revokeProjectDetails = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','ASC')->paginate(50);
                       $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','Revoke')->orderBy('id','DESC')->pluck('record_id')->toArray();
                       $revokeProjectDetails = $query->where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate]);
                        if (!empty($existingCallerChartsWorkLogs)) {
                            $revokeProjectDetails = $revokeProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                        }
                       $revokeProjectDetails = $revokeProjectDetails->orderBy('id', 'DESC')->paginate(50);
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $modelClassDuplcates = "App\\Models\\" .$modelName.'Duplicates';
                       $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                       $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                       $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                    $query->whereNull('ar_manager_rebuttal_status')
                                        ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                            })
                            // ->whereBetween('updated_at',[$startDate,$endDate])
                            ->count();
                        $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                     //   $revokeProjectDetails = $modelClass::where('chart_status','Revoke')->whereNull('tl_error_count')->where('CE_emp_id',$loginEmpId)->orderBy('id','ASC')->paginate(50);
                      $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','Revoke')->orderBy('id','DESC')->pluck('record_id')->toArray();
                      $revokeProjectDetails = $query->where('chart_status','Revoke')->whereNull('tl_error_count')->where('CE_emp_id',$loginEmpId);
                      if (!empty($existingCallerChartsWorkLogs)) {
                          $revokeProjectDetails = $revokeProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                      }
                      $revokeProjectDetails = $revokeProjectDetails->orderBy('id', 'DESC')->paginate(50);
                      $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                      $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $reworkCount = $modelClass::where('chart_status','Revoke')->whereNull('tl_error_count')->where('CE_emp_id',$loginEmpId)->count();
                      $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                $query->whereNull('ar_manager_rebuttal_status')
                                    ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                        })->where('CE_emp_id',$loginEmpId)
                        // ->whereBetween('updated_at',[$startDate,$endDate])
                        ->count();
                      $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                 }
                 $revokeProjectDetails->getCollection()->transform(function ($item) use ($excludeColumns) {
                    foreach ($excludeColumns as $column) {
                        unset($item->{$column});
                    }
                    return $item;
                });
                 $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                 $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                 ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                 ->select('project_id', 'sub_project_id')
                 ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('user_type',  10)->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal = Helpers::qaSubStatusList();
                $qaStatusList = Helpers::qaStatusList();
                $arStatusList = Helpers::arStatusList();
                $arActionListVal = Helpers::arActionList();
                $arDenialList = Helpers::arDenialList();
                $arSubStatusList = Helpers::arSubStatusList();
                $qaClassificationVal = Helpers::qaClassification();
                $qaCategoryVal = Helpers::qaCategory();
                $qaScopeVal = Helpers::qaScope();
                $projectColSearchFields = Helpers::excludePopupNonVisibleSearchFields(ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get(), $decodedProjectName, $subProjectId);
                $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();  
                 return view('productions/clientReworkTab',compact('revokeProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','existingCallerChartsWorkLogs','popUpHeader','popupNonEditableFields','popupEditableFields','popupQAEditableFields','qaSubStatusListVal','unAssignedCount','qaStatusList','arStatusList','arActionListVal','arNonWorkableCount','rebuttalCount','qaClassificationVal','qaCategoryVal','qaScopeVal','projectColSearchFields','projectColSearchFieldsType','searchData','arAutoCloseCount','arDenialList','arSubStatusList'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }
    public function clientDuplicateTab(Request $request,$clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
              // $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
              $paProject = Helpers::projectName($decodedProjectName);
              $decodedClientName = $paProject ? $paProject->project_name : null;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['id','QA_emp_id','duplicate_status','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
               }
               $modelName = Str::studly($table_name);
               $modelClassDuplcates = "App\\Models\\" . $modelName."Duplicates";
               $modelClass = "App\\Models\\" .$modelName;
               $query = $modelClassDuplcates::query();
               $searchData = [];
               $duplicateTableColumns = class_exists($modelClassDuplcates)
                    ? Schema::getColumnListing((new $modelClassDuplcates)->getTable())
                    : [];
               if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                       $searchData[$key] = $value;
                        if (!in_array($key, $duplicateTableColumns, true)) {
                            continue;
                        }
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                        }
                        if ($value === null || $value === '' || $value === 'null') {
                            continue;
                        }

                        // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  // Exact match for numeric/boolean
                        } elseif ($this->isDate($value)) {
                            $query->whereDate($key, '=', $value);
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                        }
                    }
                }
                $query->where(function ($statusQuery) {
                    $statusQuery->whereNull('duplicate_status')->orWhere('duplicate_status', 'dis_agree');
                });
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();$yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
               $duplicateProjectDetails = collect();$duplicateCount = 0;$assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
                $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
                $columnsHeader = array_values(
                    array_diff($columnsHeader, $excludeColumns)
                );
            $model = $query->getModel();
            $allColumns = Schema::getColumnListing($model->getTable());  
           $selectColumns = array_diff($allColumns, $excludeColumns);
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                   if (class_exists($modelClassDuplcates)) {
                        //   $duplicateProjectDetails =  $modelClass::whereNotIn('status',['agree','dis_agree'])->orderBy('id','DESC')->get();
                        // $duplicateProjectDetails = $query->orderBy('id','ASC')->whereBetween('created_at',[$startDate,$endDate])->paginate(50);
                        $duplicateProjectDetails = $query->orderBy('id','DESC')->paginate(50);
                        $assignedCount =  $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                        $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount =   $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        // $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                        $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                        $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                        $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                    $query->whereNull('ar_manager_rebuttal_status')
                                        ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                            })
                            // ->whereBetween('updated_at',[$startDate,$endDate])
                            ->count();
                        $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                   }
                } elseif ($loginEmpId) {
                    if (class_exists($modelClassDuplcates)) {
                       $duplicateProjectDetails = $query->where('chart_status','CE_Assigned')->where('CE_emp_id',$loginEmpId)->orderBy('id','DESC')->paginate(50);
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        //    $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                        $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                $query->whereNull('ar_manager_rebuttal_status')
                                    ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                        })->where('CE_emp_id',$loginEmpId)
                        // ->whereBetween('updated_at',[$startDate,$endDate])
                        ->count();
                        $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                }
                $duplicateProjectDetails->getCollection()->transform(function ($item) use ($excludeColumns) {
                    foreach ($excludeColumns as $column) {
                        unset($item->{$column});
                    }
                    return $item;
                });
                $projectColSearchFields = Helpers::excludePopupNonVisibleSearchFields(ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get(), $decodedProjectName, $subProjectId);
                $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();  
                return view('productions/clientDuplicateTab',compact('duplicateProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','unAssignedCount','arNonWorkableCount','rebuttalCount','projectColSearchFields','projectColSearchFieldsType','searchData','arAutoCloseCount'));

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
               // $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $paProject = Helpers::projectName($decodedProjectName);
               $decodedClientName = $paProject ? $paProject->project_name : null;
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
                   // $duplicateRecord->update(['duplicate_status' => $status]);
                   $dupStatus = $duplicateRecord['duplicate_status'];
                   unset($duplicateRecord['id']);
                   unset($duplicateRecord['duplicate_status']); 
                    $duplicateRecord->update(['duplicate_status' => $status]);
                    if($dupStatus == NULL &&  $status="agree") {
                        //dd($dupStatus);
                       $modelClass::create($duplicateRecord->toArray());
                    }
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
               // $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $paProject = Helpers::projectName($decodedProjectName);
               $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project':Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName.'Datas';
                $originalModelClass = "App\\Models\\" . $modelName;
                // $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName)).'Datas';
                // $data = [];
                // foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                //     if (is_array($value)) {
                //         // $data[$key] = implode('_el_', $value);
                //         $data[$key] = in_array(null, $value, true) ? null : implode('_el_', $value);
                //     } else {
                //         $data[$key] = $value;
                //     }
                // }
                $data = [];
                foreach ($request->except('_token', 'parent', 'child', 'page') as $key => $value) {
                    if (is_array($value)) {
                        // Remove empty/null values from array
                        $filteredValue = array_filter($value, function ($item) {
                            return $item !== null && $item !== '';
                        });
                        // If all values are empty, store null
                        $data[$key] = count($filteredValue) > 0
                            ? implode('_el_', $filteredValue)
                            : null;
                    } else {
                        $data[$key] = $value === '' ? null : $value;
                    }
                }
                $data['invoke_date'] = date('Y-m-d',strtotime($data['invoke_date']));
                $data['parent_id'] = $data['idValue'];//dd($modelClass::first(),$originalModelClass::first(),$data);
                $datasRecord = $modelClass::where('parent_id', $data['parent_id'])->orderBy('id','DESC')->first();
                $coderCompletedRecords = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->get();
                $coderCompletedRecordsCount = count($coderCompletedRecords);
                 $data['coder_work_date'] = $data['ar_at'] = NULL;
                $autoCloseRecords = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->get();
                $autoCloseRecordsCount = count($autoCloseRecords);
                // $data['ar_at'] = Carbon::now()->format('Y-m-d H:i:s');
                if( $data['chart_status'] == "CE_Completed") {
                    $data['coder_work_date'] = Carbon::now()->format('Y-m-d');
                    $data['ar_at'] = Carbon::now()->format('Y-m-d H:i:s');
                    if($decodedPracticeName == NULL) {
                        $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                         ->where('qa_percentage','!=', 0)
                         ->where(function($query) use ($loginEmpId) {
                            $query->where('coder_emp_id', $loginEmpId)
                                ->orWhereNull('coder_emp_id');
                        })->orderBy('id', 'DESC')->get();
                        // $qasamplingDetailsList  = QualitySampling::where('project_id',$decodedProjectName)->whereIn('coder_emp_id',[$loginEmpId,NULL])->orderBy('id','DESC')->get();
                        // if( count($qasamplingDetailsList) == 0) {
                        //     $qasamplingDetailsList  = QualitySampling::where('project_id',$decodedProjectName)->orderBy('id','DESC')->get();
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
                        $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL; 
                         $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                                                ->where('sub_project_id', $decodedPracticeName)
                                                ->where('qa_percentage','!=', 0)
                                                ->where(function($query) use ($loginEmpId) {
                                                    $query->where('coder_emp_id', $loginEmpId)
                                                        ->orWhereNull('coder_emp_id');
                                                })->orderBy('id', 'DESC')->get();
                        $qasamplingDetailsPercentage = QualitySampling::where('project_id', $decodedProjectName)
                            ->where('sub_project_id', $decodedPracticeName)
                            ->where('qa_percentage','!=', 0)
                            ->where(function($query) use ($loginEmpId) {
                                $query->where('coder_emp_id', $loginEmpId)
                                    ->orWhereNull('coder_emp_id');
                            })->sum('qa_percentage');                         
                        foreach ($qasamplingDetailsList as $qKey => $qasamplingDetails) {
                            if($qasamplingDetails != null) {
                                $allCompletedRecordsTotalCount = $coderCompletedRecordsCount*$qasamplingDetailsPercentage/100;
                                $allCompletedRecords = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId);
                                $qaDynamicColumns =  $qasamplingDetails["qa_sample_column_name"];//office_keys,worklist,insurance_balance
                                $qaDynamicValues =  $qasamplingDetails["qa_sample_column_value"];//off1,work2,ins2
                                $qaDynamicColumnDataType =  $qasamplingDetails["qa_sample_column_data_type"];
                                $qaDynamicColumnCondition =  $qasamplingDetails["qa_sample_column_condition"];
                                
                                if($qaDynamicColumns != null && $qaDynamicValues != null && $qaDynamicColumnDataType != null && $qaDynamicColumnCondition != null) {
                                    $qaDynamicColumns = explode(',', $qasamplingDetails["qa_sample_column_name"]); // Convert to array
                                    $qaDynamicValues = explode(',', $qasamplingDetails["qa_sample_column_value"]); // Convert to array
                                    $qaDynamicColumnDataType =  explode(',',$qasamplingDetails["qa_sample_column_data_type"]);
                                    $qaDynamicColumnCondition =  explode(',',$qasamplingDetails["qa_sample_column_condition"]);
                                    if(count($qaDynamicColumns) === count($qaDynamicValues) && count($qaDynamicValues) === count($qaDynamicColumnDataType) && count($qaDynamicColumnDataType) === count($qaDynamicColumnCondition)) {
                                        //$mergedArray = array_combine($qaDynamicColumns, $qaDynamicValues);
                                        $mergedArray = $qaDynamicColumns;
                                    } else {
                                        $mergedArray = []; // Handle mismatched array lengths
                                       
                                    }
                                    $allKeysExist = true;
                                    $allValuesMatch = true;
                                    foreach ($mergedArray as $key => $value) {
                                        if (!array_key_exists($value, $data)) {
                                            $allKeysExist = false;
                                            break;
                                        }                   
                                        if ($qaDynamicColumnDataType[$key] == "string" && $data[$value] !== $qaDynamicValues[$key]) {
                                            $allValuesMatch = false;
                                        }
                                    }
                                    $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)
                                    ->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])
                                    ->where('qa_work_status','Sampling');
                                    foreach ($qaDynamicColumns as $index => $column) {
                                        // $allCompletedRecords->where($column, $qaDynamicValues[$index] ?? null);
                                        // $samplingRecord->where($column, $qaDynamicValues[$index] ?? null);
                                        $allCompletedRecords->where($column,$qaDynamicColumnCondition[$index],$qaDynamicValues[$index] ?? null);
                                        $samplingRecord->where($column,$qaDynamicColumnCondition[$index],$qaDynamicValues[$index] ?? null);
                                    }
                                    $allCompletedRecords = $allCompletedRecords->get();
                                    $samplingRecord = $samplingRecord->get();
                                    $samplingRecordCount =  count($samplingRecord); 
                                    $allCompletedRecordsCount = count($allCompletedRecords);
                                    $qaPercentage = $qasamplingDetails["qa_percentage"];
                                    if($allCompletedRecordsCount != 0) {
                                       $qarecords = $allCompletedRecordsCount*(int)$qasamplingDetails["qa_percentage"]/100;  
                                    } else {
                                       $qarecords = $allCompletedRecordsTotalCount;
                                    }
                                } else {
                                    $allKeysExist = true;
                                    $allValuesMatch = true;
                                    $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])
                                    ->where('qa_work_status','Sampling');
                                    $allCompletedRecords = $allCompletedRecords->get();
                                    $samplingRecord = $samplingRecord->get();
                                    $samplingRecordCount =  count($samplingRecord);   
                                    $allCompletedRecordsCount = count($coderCompletedRecords);
                                    $qaPercentage = $qasamplingDetails["qa_percentage"];
                                    // $qarecords = $allCompletedRecordsCount*$qasamplingDetailsPercentage/100;
                                    $qarecords = $allCompletedRecordsTotalCount;
                                }                                                                       
                                 if($qarecords >= $samplingRecordCount && $allKeysExist && $allValuesMatch) {  
                                    $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                    $data['qa_work_status'] = "Sampling";
                                    $data['chart_status'] = "CE_Completed";
                                   break;
                                  
                                } else {
                                    $data['qa_work_status'] = "Auto_Close";
                                   // break;
                                }
                            }
                        }
                    }

                } else if( $data['chart_status'] == "Auto_Close") {
                    $data['coder_work_date'] = Carbon::now()->format('Y-m-d');
                     $data['ar_at'] = Carbon::now()->format('Y-m-d H:i:s');
                    if($decodedPracticeName == NULL) {
                        // $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                        //  ->where(function($query) use ($loginEmpId) {
                        //     $query->where('coder_emp_id', $loginEmpId)
                        //         ->orWhereNull('coder_emp_id');
                        // })->orderBy('id', 'DESC')->get();
                        $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL;$data['chart_status'] = "Auto_Close";
                        // foreach ($qasamplingDetailsList as $qasamplingDetails) {
                        //     if($qasamplingDetails != null) {
                        //         $qaPercentage = $qasamplingDetails["qa_percentage"];
                        //         $qarecords = $autoCloseRecordsCount*$qaPercentage/100;
                        //         $samplingRecord = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                        //         $samplingRecordCount =  count($samplingRecord);
                        //         if($qarecords > $samplingRecordCount) {
                        //             $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                        //             $data['qa_work_status'] = "Sampling";
                        //             $data['chart_status'] = "Auto_Close";
                        //             break;
                        //         } else {
                        //              $data['qa_work_status'] = "Auto_Close";
                        //              $data['chart_status'] = "Auto_Close";

                        //         }
                        //     }
                        // }
                    } else {
                    //    $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                    //                             ->where('sub_project_id', $decodedPracticeName)
                    //                             ->where(function($query) use ($loginEmpId) {
                    //                                 $query->where('coder_emp_id', $loginEmpId)
                    //                                     ->orWhereNull('coder_emp_id');
                    //                             })->orderBy('id', 'DESC')->get();
                          $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL;$data['chart_status'] = "Auto_Close";
                        // foreach ($qasamplingDetailsList as $qasamplingDetails) {
                        //     if($qasamplingDetails != null) {
                        //         $qaPercentage = $qasamplingDetails["qa_percentage"];
                        //         $qarecords = $autoCloseRecordsCount*$qaPercentage/100;
                        //         $samplingRecord = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                        //         $samplingRecordCount =  count($samplingRecord);
                        //         if($qarecords >= $samplingRecordCount ) {
                        //             $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                        //             $data['qa_work_status'] = "Sampling";
                        //             $data['chart_status'] = "Auto_Close";
                        //             break;
                        //         } else {
                        //             $data['qa_work_status'] = "Auto_Close";
                        //             $data['chart_status'] = "Auto_Close";

                        //         }
                        //     }
                        // }
                    }
                }
                $record = $originalModelClass::where('id', $data['parent_id'])->first();
                if($record != null) {
                $qaData = $originalModelClass::where('id', $data['parent_id'])->first()->toArray();
                $excludeKeys = ['id', 'created_at', 'updated_at', 'deleted_at'];
                $filteredQAData = collect($qaData)->except($excludeKeys)->toArray();
                $data = array_merge($data, array_diff_key($filteredQAData, $data));
                if(isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null) {
                  $annex_coder_trends = isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null ?  explode('_el_',str_replace("\r\n", '_el_', $data['annex_coder_trends'])) :null;
                }
                if(isset($annex_coder_trends) && $annex_coder_trends != null) {
                    foreach( $annex_coder_trends as $trend){
                        if (str_contains($trend, 'CPT -') && !str_contains($trend, 'modifier')) {
                            $array[]= $trend;
                            $data['coder_cpt_trends'] = implode('_el_', $array);
                        }
                        if (str_contains($trend, 'ICD -') && !str_contains($trend, 'modifier')) {
                            $a1[]= $trend;
                            $data['coder_icd_trends'] =implode('_el_', $a1);
                        }
                        if (str_contains($trend, 'modifier ')) {
                            $a2[]= $trend;
                            $data['coder_modifiers'] = implode('_el_', $a2);
                        }
                    }
                }
                if(isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null) {
                   $data['annex_coder_trends'] = isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null ?  str_replace("\r\n", '_el_', $data['annex_coder_trends']) : null;
                }
                 if($datasRecord != null) {
                    $datasRecord->update($data);
                  ($data['chart_status'] == "CE_Completed" || $data['chart_status'] == "Auto_Close") ? $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status'],'coder_work_date' => $data['coder_work_date'],'ar_at' => $data['ar_at']]) : $record->update( ['chart_status' => $data['chart_status'],'ce_hold_reason' => $data['ce_hold_reason'],'ar_at' => $data['ar_at']] );
                } else {
                   ($data['chart_status'] == "CE_Completed" || $data['chart_status'] == "Auto_Close") ? $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status'],'coder_work_date' => $data['coder_work_date'],'ar_at' => $data['ar_at']]) : $record->update( ['chart_status' => $data['chart_status'],'ce_hold_reason' => $data['ce_hold_reason'],'ar_at' => $data['ar_at']] );
                   $modelClass::create($data);
                }
                $currentTime = Carbon::now();
                $callChartWorkLogExistingRecords = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                ->where('project_id', $decodedProjectName)
                ->where('sub_project_id', $decodedPracticeName)
                ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->get();
                    if ($callChartWorkLogExistingRecords->isNotEmpty()) {
                        foreach ($callChartWorkLogExistingRecords as $callChartWorkLog) {
                            $start_time = Carbon::parse($callChartWorkLog->start_time);
                            $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                            $callChartWorkLog->update( ['record_status' => $data['chart_status'],'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time] );
                        }
                   }
                //    return redirect('/projects_assigned/'.$clientName.'/'.$subProjectName.'?parent=' .request()->parent .'&child=' .request()->child);
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Data updated successfully',
                        'redirect_url' => url('/projects_assigned/'.$clientName.'/'.$subProjectName.'?parent=' . request()->parent . '&child=' . request()->child)
                    ]);
            } else {
                
                $data['invoke_date'] = date('Y-m-d');
                $data['CE_emp_id'] =  Session::get('loginDetails')['userDetail']['emp_id'];
                $originalData= $data;
                $originalData['ar_status_code'] = NULL;
                $originalData['ar_action_code'] = NULL;
               $manualDuplciate = ManualProjectDuplicate::where('project_id', $decodedProjectName)
               ->where('sub_project_id', $decodedPracticeName)->get();
               $attributes = [];
               if(count($manualDuplciate) > 0) {
                    foreach($manualDuplciate as $duplicateColumn) {              
                        $attributes [$duplicateColumn['duplicate_column']]= isset($data[$duplicateColumn['duplicate_column']]) && $data[$duplicateColumn['duplicate_column']] != "NULL" ? $data[$duplicateColumn['duplicate_column']] : NULL;
                        $attributes ['CE_emp_id'] = $data['CE_emp_id'];
                    }
                }
                $duplicateRecordExisting = $originalModelClass::where($attributes)->exists();
                
                if (!$duplicateRecordExisting) {
                    $orginalData = $originalModelClass::create($originalData);
                    
                    $data['parent_id'] =   $orginalData->id;
                    $modelClass::create($data);
                    $currentTime = Carbon::now();                
                    $callChartWorkLogExistingRecords = CallerChartsWorkLogs::where('project_id', $decodedProjectName)
                    ->where('sub_project_id', $decodedPracticeName)
                    ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->get();
                    if($data['chart_status'] != 'CE_Inprocess') {
                        if ($callChartWorkLogExistingRecords->isNotEmpty()) {
                            foreach ($callChartWorkLogExistingRecords as $callChartWorkLog) {
                                $start_time = Carbon::parse($callChartWorkLog->start_time);
                                $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                                $existingCallRecord = CallerChartsWorkLogs::where('record_id', $orginalData->id)->where('project_id', $decodedProjectName)
                                ->where('sub_project_id', $decodedPracticeName)
                                ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('record_status',$data['chart_status'])->first();
                                if(!$existingCallRecord) {                                
                                   $callChartWorkLog->update( ['record_status' => $data['chart_status'],'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time,'record_id' => $orginalData->id] );
                                }
                            }
                       }
                    } else {
                        if ($callChartWorkLogExistingRecords->isNotEmpty()) {
                            foreach ($callChartWorkLogExistingRecords as $callChartWorkLog) {
                                $start_time = Carbon::parse($callChartWorkLog->start_time);
                                $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                                $existingCallRecord = CallerChartsWorkLogs::where('record_id', $orginalData->id)->where('project_id', $decodedProjectName)
                                ->where('sub_project_id', $decodedPracticeName)
                                ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('record_status',$data['chart_status'])->first();
                                if(!$existingCallRecord) {   
                                   $callChartWorkLog->update( ['record_status' => $data['chart_status'],'record_id' => $orginalData->id] );
                                }
                            }
                        }
                    }
                } else {
                    if(!empty($attributes) && count($attributes) > 0) {
                        // $duplicateParentRecord  =  $originalModelClass::where($attributes)->where('chart_status',"CE_Inprocess")->first();
                        // $duplicateDatasRecord  =  $modelClass::where($attributes)->where('chart_status',"CE_Inprocess")->first();
                        // if ($duplicateParentRecord) {
                        //     $duplicateParentRecord->update($originalData);
                        // } 
                        // if ($duplicateDatasRecord) {
                        //     $duplicateDatasRecord->update($data);
                        // } 
                        $duplicateMsg = 'Duplicate Entry';
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json([
                                'status' => 'error',
                                'message' => $duplicateMsg
                            ], 409);
                        }
                        return redirect('/projects_assigned/'.$clientName.'/'.$subProjectName)->with('error', $duplicateMsg);
                    } else {
                        $orginalData = $originalModelClass::create($originalData);
                        $data['parent_id'] =   $orginalData->id;
                        $modelClass::create($data);
                        $currentTime = Carbon::now();                
                        $callChartWorkLogExistingRecords = CallerChartsWorkLogs::where('project_id', $decodedProjectName)
                        ->where('sub_project_id', $decodedPracticeName)
                        ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->get();
                        if($data['chart_status'] != 'CE_Inprocess') {
                            if ($callChartWorkLogExistingRecords->isNotEmpty()) {
                                foreach ($callChartWorkLogExistingRecords as $callChartWorkLog) {
                                    $start_time = Carbon::parse($callChartWorkLog->start_time);
                                    $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                                    $existingCallRecord = CallerChartsWorkLogs::where('record_id', $orginalData->id)->where('project_id', $decodedProjectName)
                                    ->where('sub_project_id', $decodedPracticeName)
                                    ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('record_status',$data['chart_status'])->first();
                                    if(!$existingCallRecord) {   
                                        $callChartWorkLog->update( ['record_status' => $data['chart_status'],'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time,'record_id' => $orginalData->id] );
                                    }
                               }
                            }
                        } else {
                            if ($callChartWorkLogExistingRecords->isNotEmpty()) {
                                foreach ($callChartWorkLogExistingRecords as $callChartWorkLog) {
                                    $start_time = Carbon::parse($callChartWorkLog->start_time);
                                    $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                                    $existingCallRecord = CallerChartsWorkLogs::where('record_id', $orginalData->id)->where('project_id', $decodedProjectName)
                                    ->where('sub_project_id', $decodedPracticeName)
                                    ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('record_status',$data['chart_status'])->first();
                                    if(!$existingCallRecord) {   
                                        $callChartWorkLog->update( ['record_status' => $data['chart_status'],'record_id' => $orginalData->id] );
                                    }
                                }
                            }
                        }
                    }
                }
                
                // return redirect('/projects_assigned/'.$clientName.'/'.$subProjectName.'?parent=' .request()->parent .'&child=' .request()->child);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data updated successfully',
                    'redirect_url' => url('/projects_assigned/'.$clientName.'/'.$subProjectName.'?parent=' . request()->parent . '&child=' . request()->child)
                ]);
            }
                // $originalModelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName));

                // $record = $originalModelClass::where('id', $data['parent_id'])->first();//dd($record);
                // $record->update( ['chart_status' => $data['chart_status']] );
                // $callChartWorkLogExistingRecord = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                // ->where('project_id', $decodedProjectName)
                // ->where('sub_project_id', $decodedPracticeName)
                // ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->first();
                // if($callChartWorkLogExistingRecord && $callChartWorkLogExistingRecord != null) {
                //     $start_time = Carbon::parse($callChartWorkLogExistingRecord->start_time);
                //     $time_difference = $currentTime->diff($start_time);
                //     $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                //     $callChartWorkLogExistingRecord->update( ['record_status' => $data['chart_status'],'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time] );
                // }
                
            } catch (\Exception $e) {
                \Log::error('clientsStore error', [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'request_data' => $request->except('_token')
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ], 500);
                }

                return redirect('/projects_assigned/'.$clientName.'/'.$subProjectName.'?parent=' .request()->parent .'&child=' .request()->child)
                    ->with('error','An unexpected error occurred. Please recheck data once.');
            }
           
       } else {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session expired. Please login again.',
                    'redirect_url' => url('/')
                ], 401);
            }

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
               // $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $paProject = Helpers::projectName($decodedProjectName);
               $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelHistory = "App\\Models\\" . $modelName.'History';
                // $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName));
                // $modelHistory = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName)).'History';
                $checkedValues = json_decode($request->input('checkedRowValues'), true);
                foreach($checkedValues as $data) {
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
               // $decodedClientName = Helpers::projectName($data['project_id'])->project_name;
               $paProject = Helpers::projectName($data['project_id']);
               $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $data['sub_project_id'] == NULL ? 'project' :Helpers::subProjectName($data['project_id'] ,$data['sub_project_id'])->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $originalModelClass = "App\\Models\\" . $modelName;
                // $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName));
                $data['start_time'] = $currentTime->format('Y-m-d H:i:s');
                $data['record_status'] = $modelClass::where('id',$data['record_id'])->pluck('chart_status')->toArray()[0];//dd($data,$modelClass);
                // $existingRecordId = CallerChartsWorkLogs::where('record_id',$data['record_id'])->where('record_status',"CE_Assigned")->first();
                $existingRecordId = CallerChartsWorkLogs::where('project_id', $data['project_id'])->where('sub_project_id',$data['sub_project_id'])->where('record_id',$data['record_id'])
                ->where('record_status',$data['record_status'])->where('end_time',NULL)->first();

                // if(empty($existingRecordId) || !isset($existingRecordId->start_time) || $existingRecordId->start_time == null) {
                if (!$existingRecordId) {
                        $save_flag = CallerChartsWorkLogs::create($data);
                        $startTimeVal = $data['start_time'];          
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
               // $decodedClientName = Helpers::projectName($data['project_id'])->project_name;
                $paProject = Helpers::projectName($data['project_id']);
                $decodedClientName = $paProject ? $paProject->project_name : null;
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
                $clientData = $modelClassDatas::where('parent_id',$data['record_id'])->orderBy('id','DESC')->first();
                if($clientData != null) {
                    $clientData = $clientData->toArray();
                } else {
                    $clientData = $modelClass::where('id',$data['record_id'])->first();
                }
                if(isset($clientData) && !empty($clientData)) {
                   $clientData = Helpers::hidePopupNonVisiblePatientFromRecords($clientData, Helpers::getPopupNonVisiblePatientColumns($data['project_id'], $data['sub_project_id']));
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
               // $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $originalModelClass = "App\\Models\\" . $modelName;
                $modelClass = "App\\Models\\" . $modelName.'Datas';
                $data = [];
                foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                    if (is_array($value)) {
                        $data[$key] = in_array(null, $value, true) ? null : implode('_el_', $value);
                    } else {
                        $data[$key] = $value;
                    }
                }
                $data['invoke_date'] = date('Y-m-d',strtotime($data['invoke_date']));
                $data['parent_id'] = $data['parentId'];
                $datasRecord = $modelClass::where('parent_id', $data['parent_id'])->orderBy('id','DESC')->first();
                // $coderCompletedRecords = $originalModelClass::where('chart_status','CE_Completed')->get();
                $coderCompletedRecords = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->get();
                $coderCompletedRecordsCount = count($coderCompletedRecords); $data['coder_work_date'] = $data['ar_at'] = NULL;
               // $data['ar_at'] = Carbon::now()->format('Y-m-d H:i:s');
                $autoCloseRecords = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->get();
                $autoCloseRecordsCount = count($autoCloseRecords);
                if( $data['chart_status'] == "CE_Completed") {
                    $data['coder_work_date'] = Carbon::now()->format('Y-m-d');
                     $data['ar_at'] = Carbon::now()->format('Y-m-d H:i:s');
                    if($decodedPracticeName == NULL) {
                        $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                                                ->where('qa_percentage','!=', 0)
                                                ->where(function($query) use ($loginEmpId) {
                                                    $query->where('coder_emp_id', $loginEmpId)
                                                        ->orWhereNull('coder_emp_id');
                                                })->orderBy('id', 'DESC')->get();
                        // $qasamplingDetailsList = QualitySampling::where('project_id',$decodedProjectName)->where('coder_emp_id',$loginEmpId)->orderBy('id','DESC')->get();
                        // if(count($qasamplingDetailsList) == 0) {
                        //     $qasamplingDetailsList = QualitySampling::where('project_id',$decodedProjectName)->orderBy('id','DESC')->get();
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
                        $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL;
                        $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                                                ->where('sub_project_id', $decodedPracticeName)
                                                ->where('qa_percentage','!=', 0)
                                                ->where(function($query) use ($loginEmpId) {
                                                    $query->where('coder_emp_id', $loginEmpId)
                                                        ->orWhereNull('coder_emp_id');
                                                })->orderBy('id', 'DESC')->get();
                        $qasamplingDetailsPercentage = QualitySampling::where('project_id', $decodedProjectName)
                        ->where('sub_project_id', $decodedPracticeName)
                        ->where('qa_percentage','!=', 0)
                        ->where(function($query) use ($loginEmpId) {
                            $query->where('coder_emp_id', $loginEmpId)
                                ->orWhereNull('coder_emp_id');
                        })->sum('qa_percentage');     
                        foreach ($qasamplingDetailsList as $qKey => $qasamplingDetails) {
                            if($qasamplingDetails != null) {
                                $allCompletedRecordsTotalCount = $coderCompletedRecordsCount*$qasamplingDetailsPercentage/100;
                                $allCompletedRecords = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId);
                                $qaDynamicColumns =  $qasamplingDetails["qa_sample_column_name"];//office_keys,worklist,insurance_balance
                                $qaDynamicValues =  $qasamplingDetails["qa_sample_column_value"];//off1,work2,ins2
                                $qaDynamicColumnDataType =  $qasamplingDetails["qa_sample_column_data_type"];
                                $qaDynamicColumnCondition =  $qasamplingDetails["qa_sample_column_condition"];
                              
                                // if($qaDynamicColumns != null && $qaDynamicValues != null) {
                                if($qaDynamicColumns != null && $qaDynamicValues != null && $qaDynamicColumnDataType != null && $qaDynamicColumnCondition != null) {
                                    $qaDynamicColumns = explode(',', $qasamplingDetails["qa_sample_column_name"]); // Convert to array
                                    $qaDynamicValues = explode(',', $qasamplingDetails["qa_sample_column_value"]); // Convert to array
                                    $qaDynamicColumnDataType =  explode(',',$qasamplingDetails["qa_sample_column_data_type"]);
                                    $qaDynamicColumnCondition =  explode(',',$qasamplingDetails["qa_sample_column_condition"]);
                                    if(count($qaDynamicColumns) === count($qaDynamicValues) && count($qaDynamicValues) === count($qaDynamicColumnDataType) && count($qaDynamicColumnDataType) === count($qaDynamicColumnCondition)) {
                                      //  $mergedArray = array_combine($qaDynamicColumns, $qaDynamicValues);
                                         $mergedArray = $qaDynamicColumns;
                                    } else {
                                        $mergedArray = []; // Handle mismatched array lengths
                                       
                                    }
                                    $allKeysExist = true;
                                    $allValuesMatch = true;

                                    foreach ($mergedArray as $key => $value) {
                                        // if (!array_key_exists($key, $data)) {
                                        if (!array_key_exists($value, $data)) {
                                            $allKeysExist = false;
                                            break;
                                        }                                        
                                        // if ($data[$key] !== $value) {
                                        if ($qaDynamicColumnDataType[$key] == "string" && $data[$value] !== $qaDynamicValues[$key]) {
                                            $allValuesMatch = false;
                                        }
                                    }
                                    $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])
                                    ->where('qa_work_status','Sampling');
                                    foreach ($qaDynamicColumns as $index => $column) {
                                        // $allCompletedRecords->where($column, $qaDynamicValues[$index] ?? null);
                                        // $samplingRecord->where($column, $qaDynamicValues[$index] ?? null);
                                        $allCompletedRecords->where($column,$qaDynamicColumnCondition[$index],$qaDynamicValues[$index] ?? null);
                                        $samplingRecord->where($column,$qaDynamicColumnCondition[$index],$qaDynamicValues[$index] ?? null);
                                    }
                                    $allCompletedRecords = $allCompletedRecords->get();
                                    $samplingRecord = $samplingRecord->get();
                                    $samplingRecordCount =  count($samplingRecord); 
                                    $allCompletedRecordsCount = count($allCompletedRecords);
                                    $qaPercentage = $qasamplingDetails["qa_percentage"];
                                    //$qarecords = $allCompletedRecordsTotalCount*(int)$qasamplingDetails["qa_percentage"]/80;
                                    if($allCompletedRecordsCount != 0) {
                                        $qarecords = $allCompletedRecordsCount*(int)$qasamplingDetails["qa_percentage"]/100;  
                                     } else {
                                        $qarecords = $allCompletedRecordsTotalCount;
                                     }
                                }   else {
                                    $allKeysExist = true;
                                    $allValuesMatch = true;
                                    $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])
                                    ->where('qa_work_status','Sampling');
                                    $allCompletedRecords = $allCompletedRecords->get();
                                    $samplingRecord = $samplingRecord->get();
                                    $samplingRecordCount =  count($samplingRecord);   
                                    $allCompletedRecordsCount = count($coderCompletedRecords);
                                    $qaPercentage = $qasamplingDetails["qa_percentage"];
                                    // $qarecords = $allCompletedRecordsCount*$qasamplingDetailsPercentage/100;
                                    $qarecords = $allCompletedRecordsTotalCount;
                                }                                                                       
                                                 
                                if($qarecords >= $samplingRecordCount && $allKeysExist && $allValuesMatch) {     
                                    $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                    $data['qa_work_status'] = "Sampling";
                                    $data['chart_status'] = "CE_Completed";
                                    break;
                                } else {
                                    $data['qa_work_status'] = "Auto_Close";
                                }
                            }
                        }
                    }
                } else if( $data['chart_status'] == "Auto_Close") {
                    $data['coder_work_date'] = Carbon::now()->format('Y-m-d');
                     $data['ar_at'] = Carbon::now()->format('Y-m-d H:i:s');
                    if($decodedPracticeName == NULL) {
                        // $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                        //                         ->where(function($query) use ($loginEmpId) {
                        //                             $query->where('coder_emp_id', $loginEmpId)
                        //                                 ->orWhereNull('coder_emp_id');
                        //                         })->orderBy('id', 'DESC')->get();
                         $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL; $data['chart_status'] = "Auto_Close";
                        // foreach ($qasamplingDetailsList as $qasamplingDetails) {
                        //     if($qasamplingDetails != null) {
                        //         $qaPercentage = $qasamplingDetails["qa_percentage"];
                        //         $qarecords = $autoCloseRecordsCount*$qaPercentage/100;
                        //         $samplingRecord = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                        //         $samplingRecordCount =  count($samplingRecord);
                        //         if($qarecords > $samplingRecordCount ) {
                        //             $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                        //             $data['qa_work_status'] = "Sampling";
                        //             $data['chart_status'] = "Auto_Close";
                        //             break;
                        //         } else {
                        //             //$data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                        //             $data['qa_work_status'] = "Auto_Close";
                        //             $data['chart_status'] = "Auto_Close";

                        //         }
                        //     }
                        // }
                    } else {
                        // $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                        //                         ->where('sub_project_id', $decodedPracticeName)
                        //                         ->where(function($query) use ($loginEmpId) {
                        //                             $query->where('coder_emp_id', $loginEmpId)
                        //                                 ->orWhereNull('coder_emp_id');
                        //                         })->orderBy('id', 'DESC')->get();
                        $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL;$data['chart_status'] = "Auto_Close";
                        // foreach ($qasamplingDetailsList as $qasamplingDetails) {
                        //     if($qasamplingDetails != null) {
                        //         $qaPercentage = $qasamplingDetails["qa_percentage"];
                        //         $qarecords = $autoCloseRecordsCount*$qaPercentage/100;
                        //         $samplingRecord = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                        //         $samplingRecordCount =  count($samplingRecord);
                        //         if($qarecords > $samplingRecordCount) {
                        //             $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                        //             $data['qa_work_status'] = "Sampling";
                        //             $data['chart_status'] = "Auto_Close";
                        //             break;
                        //         } else {
                        //              $data['qa_work_status'] = "Auto_Close";
                        //              $data['chart_status'] = "Auto_Close";

                        //         }
                        //     }
                        // }
                    }
                }
                $record = $originalModelClass::where('id', $data['parent_id'])->first();
                $qaData = $originalModelClass::where('id', $data['parent_id'])->first()->toArray();
                $excludeKeys = ['id', 'created_at', 'updated_at', 'deleted_at'];
                $filteredQAData = collect($qaData)->except($excludeKeys)->toArray();
                $data = array_merge($data, array_diff_key($filteredQAData, $data));//dd($data);
                if(isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null) {
                  $annex_coder_trends = isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null ?  explode('_el_',str_replace("\r\n", '_el_', $data['annex_coder_trends'])) : null;
                }
                  if(isset($annex_coder_trends) && $annex_coder_trends != null) {
                    foreach( $annex_coder_trends as $trend){
                        if (str_contains($trend, 'CPT -') && !str_contains($trend, 'modifier')) {
                            $array[]= $trend;
                            $data['coder_cpt_trends'] = implode('_el_', $array);
                        }
                        if (str_contains($trend, 'ICD -') && !str_contains($trend, 'modifier')) {
                            $a1[]= $trend;
                            $data['coder_icd_trends'] =implode('_el_', $a1);
                        }
                        if (str_contains($trend, 'modifier ')) {
                            $a2[]= $trend;
                            $data['coder_modifiers'] = implode('_el_', $a2);
                        }
                    }
                }
                    if(isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null) {
                        $data['annex_coder_trends'] = isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null ?  str_replace("\r\n", '_el_', $data['annex_coder_trends']) : null;
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
                    
                    $data = array_diff_key($data, array_flip($fieldsToExclude));//dd($data);
                    $datasRecord->update($data);
                    ($data['chart_status'] == "CE_Completed" || $data['chart_status'] == "Auto_Close") ? $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status'],'QA_required_sampling' => $data['QA_required_sampling'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code'],'coder_work_date' => $data['coder_work_date'],'ar_at' => $data['ar_at']]) : $record->update( ['chart_status' => $data['chart_status'],'ce_hold_reason' => $data['ce_hold_reason'],'ar_at' => $data['ar_at']] );
                } else {
                    $data['parent_id'] = $data['idValue'];
                    ($data['chart_status'] == "CE_Completed" || $data['chart_status'] == "Auto_Close") ? $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status'],'QA_required_sampling' => $data['QA_required_sampling'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code'],'coder_work_date' => $data['coder_work_date'],'ar_at' => $data['ar_at']]) : $record->update( ['chart_status' => $data['chart_status'],'ce_hold_reason' => $data['ce_hold_reason'],'ar_at' => $data['ar_at']] );
                    $modelClass::create($data);
                }
                $currentTime = Carbon::now();
                // $callChartWorkLogExistingRecord = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                // ->where('record_status',$data['record_old_status'])
                // ->where('project_id', $decodedProjectName)
                // ->where('sub_project_id', $decodedPracticeName)
                // ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->first();
                //   if($callChartWorkLogExistingRecord && $callChartWorkLogExistingRecord != null) {
                //     $start_time = Carbon::parse($callChartWorkLogExistingRecord->start_time);
                //     $time_difference = $currentTime->diff($start_time);
                //     $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                //     $callChartWorkLogExistingRecord->update([
                //         'record_status' => $data['chart_status'],
                //         'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time
                //     ]);
                // }
                $callChartWorkLogExistingRecords = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                ->where('record_status',$data['record_old_status'])
                ->where('project_id', $decodedProjectName)
                ->where('sub_project_id', $decodedPracticeName)
                ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->get();
                    if ($callChartWorkLogExistingRecords->isNotEmpty()) {
                        foreach ($callChartWorkLogExistingRecords as $callChartWorkLog) {
                            $start_time = Carbon::parse($callChartWorkLog->start_time);
                            $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                            $callChartWorkLog->update( ['record_status' => $data['chart_status'],'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time] );
                        }
                   }
                $tabUrl = $data['record_old_status'] == "Revoke" ? $data['record_old_status'] : lcfirst(str_replace('CE_', '', $data['record_old_status']));
                return redirect('/projects_'.$tabUrl.'/'.$clientName.'/'.$subProjectName.'?parent=' .request()->parent .'&child=' .request()->child);
             } catch (\Exception $e) {
                log::debug($e->getMessage());
                $data = $request->all();
                $tabUrl = $data['record_old_status'] == "Revoke" ? $data['record_old_status'] : lcfirst(str_replace('CE_', '', $data['record_old_status']));
                return redirect('/projects_'.$tabUrl.'/'.$clientName.'/'.$subProjectName.'?parent=' .request()->parent .'&child=' .request()->child)->with('error','An unexpected error occurred. Please recheck data once.');
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
                //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDatas = "App\\Models\\" . $modelName.'Datas';
                $clientData = $modelClassDatas::where('parent_id',$data['record_id'])->orderBy('id','DESC')->first();
                if($clientData != null) {
                    $clientData = $clientData->toArray();
                } else {
                    $clientData = $modelClass::where('id',$data['record_id'])->first();
                }
                if(isset($clientData) && !empty($clientData)) {
                   $clientData = Helpers::hidePopupNonVisiblePatientFromRecords($clientData, Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $decodedPracticeName == '--' ? null : $decodedPracticeName));
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
                //$decodedClientName = Helpers::projectName($data['project_id'])->project_name;
                $paProject = Helpers::projectName($data['project_id']);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $data['sub_project_id'] == NULL ? 'project' :Helpers::subProjectName($data['project_id'] ,$data['sub_project_id'])->sub_project_name;
                $data['start_time'] = $currentTime->format('Y-m-d H:i:s');
                $data['record_status'] = $data['urlDynamicValue'] == "Revoke" ? "Revoke" : 'CE_'.ucwords($data['urlDynamicValue']) ;//dd($data['urlDynamicValue'],$data['record_status']);
                $existingRecordId = CallerChartsWorkLogs::where('project_id', $data['project_id'])->where('sub_project_id',$data['sub_project_id'])->where('record_id',$data['record_id'])->where('record_status',$data['record_status'])->where('end_time',NULL)->first();

                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDatas = "App\\Models\\" . $modelName.'Datas';
                $clientData = $modelClassDatas::where('parent_id',$data['record_id'])->orderBy('id','DESC')->first();
                if($clientData != null) {
                    $clientData = $clientData->toArray();
                } else {
                    $clientData = $modelClass::where('id',$data['record_id'])->first();
                }
                if(isset($clientData) && !empty($clientData)) {
                   $clientData = Helpers::hidePopupNonVisiblePatientFromRecords($clientData, Helpers::getPopupNonVisiblePatientColumns($data['project_id'], $data['sub_project_id']));
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

    // public function clientsReworkUpdate(Request $request,$clientName,$subProjectName) {
    //     if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
    //         try {
    //              $data = $request->all();
    //              $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
    //             $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
    //             $decodedPracticeName =  $subProjectName == '--' ? NULL : Helpers::encodeAndDecodeID($subProjectName, 'decode');
    //             $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
    //             $decodedsubProjectName = $decodedPracticeName == NULL ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
    //             $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
    //             $modelName = Str::studly($table_name);
    //             $originalModelClass = "App\\Models\\" . $modelName;
    //             $modelClass = "App\\Models\\" . $modelName.'Datas';
    //             $data = [];
    //             foreach ($request->except('_token', 'parent', 'child') as $key => $value) {
    //                 if (is_array($value)) {
    //                     $data[$key] = implode('_el_', $value);
    //                 } else {
    //                     $data[$key] = $value;
    //                 }
    //             }

    //             $data['parent_id'] = $data['parentId'];
    //             $datasRecord = $modelClass::where('parent_id', $data['parent_id'])->orderBy('id','DESC')->first();
    //             $record = $originalModelClass::where('id', $data['parent_id'])->first();
    //             $qaData = $originalModelClass::where('id', $data['parent_id'])->first()->toArray();
    //             $excludeKeys = ['id', 'created_at', 'updated_at', 'deleted_at'];
    //             $filteredQAData = collect($qaData)->except($excludeKeys)->toArray();
    //             $data = array_merge($data, array_diff_key($filteredQAData, $data));
    //             if($data['coder_rework_status'] == 'Accept' && $datasRecord['tl_error_count'] == NULL) {//coder accepted
    //                 $data['chart_status'] = "QA_Completed";
    //                 $data['QA_required_sampling'] = "Auto_Close";
    //                 $data['coder_error_count'] = 1;
    //                 $data['qa_error_count'] = NULL;
    //                 $data['tl_comments'] = $datasRecord['tl_comments'];
    //              } else if($data['coder_rework_status'] == 'Accept' &&  $datasRecord['tl_error_count'] == 1) {//maanger assigned to coder
    //                 $data['chart_status'] = "QA_Completed";
    //                 $data['QA_required_sampling'] = "Auto_Close";
    //                 $data['qa_error_count'] = NULL;
    //                 $data['coder_error_count'] = 1;
    //                 $data['tl_comments'] = $data['coder_rework_reason'].'@'.$loginEmpId;
    //                 $data['coder_rework_reason'] = $datasRecord['coder_rework_reason'];
    //              }
    //               else if($data['coder_rework_status'] == 'Rebuttal' &&  $datasRecord['tl_error_count'] == 1) {//maanger assigned to QA
    //                 $data['chart_status'] = "QA_Completed";
    //                 $data['QA_required_sampling'] = "Auto_Close";
    //                 $data['qa_error_count'] = 1;
    //                 $data['coder_error_count'] = NULL;
    //                 $data['tl_comments'] = $data['coder_rework_reason'].'@'.$loginEmpId;
    //                 $data['coder_rework_reason'] = $datasRecord['coder_rework_reason'];
    //              } else {
    //                 $data['chart_status'] = "CE_Completed";
    //                 $data['QA_required_sampling'] = "Sampling";
    //                 $data['coder_error_count'] = NULL;
    //                 $data['qa_error_count'] = NULL;
    //                 $data['tl_comments'] = $datasRecord['tl_comments'];
    //             }
    //             $datasRecord->update( ['chart_status' => $data['chart_status'],'QA_required_sampling' => $data['QA_required_sampling'],'coder_rework_status' => $data['coder_rework_status'],'coder_rework_reason' => $data['coder_rework_reason'],'coder_error_count' => $data['coder_error_count'],'qa_error_count' => $data['qa_error_count'], 'tl_comments' => $data['tl_comments']] );
    //             $record->update( ['chart_status' => $data['chart_status'],'QA_required_sampling' => $data['QA_required_sampling'],'coder_rework_status' => $data['coder_rework_status'],'coder_rework_reason' => $data['coder_rework_reason'],'coder_error_count' => $data['coder_error_count'],'qa_error_count' => $data['qa_error_count'],'tl_comments' => $data['tl_comments']] );

    //             return redirect('/projects_Revoke/'.$clientName.'/'.$subProjectName);
    //             // $tabUrl = $data['record_old_status'] == "Revoke" ? $data['record_old_status'] : lcfirst(str_replace('CE_', '', $data['record_old_status']));
    //             // return redirect('/projects_'.$tabUrl.'/'.$clientName.'/'.$subProjectName);
    //         } catch (\Exception $e) {
    //             log::debug($e->getMessage());
    //         }
    //     } else {
    //         return redirect('/');
    //     }
    // }
    public function clientsReworkUpdate(Request $request,$clientName,$subProjectName) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                 $data = $request->all();
                 $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName =  $subProjectName == '--' ? NULL : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $originalModelClass = "App\\Models\\" . $modelName;
                $modelClass = "App\\Models\\" . $modelName.'Datas';
                $modelClassRevokeHistory = "App\\Models\\" . $modelName.'RevokeHistory';
                $data = [];
                foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                    if (is_array($value)) {
                          $data[$key] = in_array(null, $value, true) ? null : implode('_el_', $value);
                    } else {
                        $data[$key] = $value;
                    }
                }
                $data['invoke_date'] = date('Y-m-d',strtotime($data['invoke_date']));
                $data['parent_id'] = $data['parentId'];
                $datasRecord = $modelClass::where('parent_id', $data['parent_id'])->orderBy('id','DESC')->first();
                $coderCompletedRecords = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->get();
                $coderCompletedRecordsCount = count($coderCompletedRecords); $data['coder_work_date'] =  NULL;
                //$data['ar_at'] = Carbon::now()->format('Y-m-d H:i:s');
                if( $data['chart_status'] == "CE_Completed" || $data['chart_status'] == "Auto_Close") {
                    $data['coder_work_date'] = Carbon::now()->format('Y-m-d');
                    $data['qa_work_status'] = "Sampling";
                }
                $record = $originalModelClass::where('id', $data['parent_id'])->first();
                $qaData = $originalModelClass::where('id', $data['parent_id'])->first()->toArray();
                $excludeKeys = ['id', 'created_at', 'updated_at', 'deleted_at'];
                $filteredQAData = collect($qaData)->except($excludeKeys)->toArray();
                $data = array_merge($data, array_diff_key($filteredQAData, $data));
                if(isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null) {
                  $annex_coder_trends = isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null ?  explode('_el_',str_replace("\r\n", '_el_', $data['annex_coder_trends'])) : null;
                }
                  if(isset($annex_coder_trends) && $annex_coder_trends != null) {
                    foreach( $annex_coder_trends as $trend){
                        if (str_contains($trend, 'CPT -') && !str_contains($trend, 'modifier')) {
                            $array[]= $trend;
                            $data['coder_cpt_trends'] = implode('_el_', $array);
                        }
                        if (str_contains($trend, 'ICD -') && !str_contains($trend, 'modifier')) {
                            $a1[]= $trend;
                            $data['coder_icd_trends'] =implode('_el_', $a1);
                        }
                        if (str_contains($trend, 'modifier ')) {
                            $a2[]= $trend;
                            $data['coder_modifiers'] = implode('_el_', $a2);
                        }
                    }
                }
                    if(isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null) {
                        $data['annex_coder_trends'] = isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null ?  str_replace("\r\n", '_el_', $data['annex_coder_trends']) : null;
                    }
                    if($data['chart_status'] == "CE_Completed" || $data['chart_status'] == "Auto_Close") {                     
                        if($datasRecord != null) {
                            $newDataRecord = $datasRecord->getAttributes();
                            unset($newDataRecord["id"]);
                           $modelClassRevokeHistory::create($newDataRecord);
                        } else {
                            $modelClassRevokeHistory::create($data);
                        }
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
                    ($data['chart_status'] == "CE_Completed" || $data['chart_status'] == "Auto_Close") ? $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status'],'QA_required_sampling' => $data['QA_required_sampling'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code'],'coder_work_date' => $data['coder_work_date'],'ar_at' => $data['ar_at']]) : $record->update( ['chart_status' => $data['chart_status'],'ce_hold_reason' => $data['ce_hold_reason'],'ar_at' => $data['ar_at']] );
                } else {
                    $data['parent_id'] = $data['idValue'];
                    ($data['chart_status'] == "CE_Completed" || $data['chart_status'] == "Auto_Close") ? $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status'],'QA_required_sampling' => $data['QA_required_sampling'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code'],'coder_work_date' => $data['coder_work_date'],'ar_at' => $data['ar_at']]) : $record->update( ['chart_status' => $data['chart_status'],'ce_hold_reason' => $data['ce_hold_reason'],'ar_at' => $data['ar_at']] );
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

    public function clientUnAssignedTab(Request $request,$clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           $client = new Client(['verify' => false]);
           try {
               $userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] !=null ? Session::get('loginDetails')['userDetail']['id']:"";
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $paProject = Helpers::projectName($decodedProjectName);
               $decodedClientName = $paProject ? $paProject->project_name : null;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" .  $modelName;
               $query = $modelClass::query();
               $searchData = [];
               if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                       $searchData[$key] = $value;
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                        }

                        // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  // Exact match for numeric/boolean
                        } elseif ($this->isDate($value)) {  // Check if it's a date
                            $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            if($value != null) {
                              $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                            }
                        }
                    }
                }
               $modelClassDatas = "App\\Models\\" .  $modelName.'Datas'; $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();
               $unAssignedProjectDetails = collect();$assignedDropDown=[];$dept= Session::get('loginDetails')['userInfo']['department']['id'];$existingCallerChartsWorkLogs = [];$unAssignedProjectDetailsStatus = [];$unAssignedCount = 0;
               $duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
               $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
                    $columnsHeader = array_values(
                        array_diff($columnsHeader, $excludeColumns)
                    );
                $model = $query->getModel();
                $allColumns = Schema::getColumnListing($model->getTable());  
                $selectColumns = array_diff($allColumns, $excludeColumns);
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                    if (class_exists($modelClass)) {
                       $modelClassDuplcates = "App\\Models\\" . $modelName.'Duplicates';
                       $unAssignedProjectDetails = $query->where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->orderBy('id','DESC')->paginate(50);
                       $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','DESC')->pluck('record_id')->toArray();
                    //    $assignedDropDownIds = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->select('CE_emp_id')->groupBy('CE_emp_id')->pluck('CE_emp_id')->toArray();
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        //    $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                        $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                       $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                       $unAssignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','DESC')->pluck('chart_status')->toArray();
                       $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                $query->whereNull('ar_manager_rebuttal_status')
                                    ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                        })
                        //->whereBetween('updated_at',[$startDate,$endDate])
                        ->count();
                        $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //    $payload = [
                    //        'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    //        'client_id' => $decodedProjectName,
                    //        'user_id' => $userId
                    //    ];

                    //     $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_resource_name', [
                    //        'json' => $payload
                    //    ]);
                    //    if ($response->getStatusCode() == 200) {
                    //         $data = json_decode($response->getBody(), true);
                    //    } else {
                    //        return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                    //    }
                    //    $assignedDropDown = array_filter($data['userDetail']);
                   }
               } elseif ($loginEmpId) {
                   if (class_exists($modelClass)) {
                       $unAssignedProjectDetails = $query->whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->orderBy('id','DESC')->paginate(50);
                       $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->whereIn('record_status',['CE_Assigned','CE_Inprocess'])->orderBy('id','DESC')->pluck('record_id')->toArray();
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        //    $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                        $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $unAssignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->orderBy('id','DESC')->pluck('chart_status')->toArray();
                       $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                    $query->whereNull('ar_manager_rebuttal_status')
                                        ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                            })->where('CE_emp_id',$loginEmpId)
                            //->whereBetween('updated_at',[$startDate,$endDate])
                            ->count();
                       $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
               }
               $unAssignedProjectDetails->getCollection()->transform(function ($item) use ($excludeColumns) {
                    foreach ($excludeColumns as $column) {
                        unset($item->{$column});
                    }
                    return $item;
                });
               $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
               ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
               ->select('project_id', 'sub_project_id')
               ->first();
               $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','non_editable')->where('field_type_3','popup_visible')->get();
               $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','editable')->where('field_type_3','popup_visible')->get();
               $projectColSearchFields = Helpers::excludePopupNonVisibleSearchFields(ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get(), $decodedProjectName, $subProjectId);
               $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();
               $projectTypeSettings = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->first();
                   return view('productions/clientUnAssignedTab',compact('unAssignedProjectDetails','columnsHeader','popUpHeader','popupNonEditableFields','popupEditableFields','modelClass','clientName','subProjectName','assignedDropDown','existingCallerChartsWorkLogs','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','unAssignedProjectDetailsStatus','unAssignedCount','arNonWorkableCount','rebuttalCount','projectColSearchFields','projectColSearchFieldsType','searchData','arAutoCloseCount','projectTypeSettings'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
   }

   public function assigneeDropdown(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            $client = new Client(['verify' => false]);
            try {
                $userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] !=null ? Session::get('loginDetails')['userDetail']['id']:"";
                $decodedProjectName = Helpers::encodeAndDecodeID($request->clientName, 'decode');
                    $payload = [
                        'token' => '1a32e71a46317b9cc6feb7388238c95d',
                        'client_id' => $decodedProjectName,
                        'user_id' => $userId
                    ];

                    $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_resource_name_resolv', [
                        'json' => $payload
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
   
    public static function arActionCodeList(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data = ARActionCodes::where('status_code_id', $request['status_code_id'])->pluck('action_code', 'id')->toArray();
                return response()->json(["subStatus" => $data]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientNonWorkableTab(Request $request,$clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $paProject = Helpers::projectName($decodedProjectName);
               $decodedClientName = $paProject ? $paProject->project_name : null;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
               }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" . $modelName;
               $query = $modelClass::query();
               $searchData = [];
               if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                       $searchData[$key] = $value;
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                        }

                        // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  // Exact match for numeric/boolean
                        } elseif ($this->isDate($value)) {  // Check if it's a date
                            $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            if($value != null) {
                              $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                            }
                        }
                    }
                }
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
               $completedProjectDetails = collect();$duplicateCount = 0;$assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
               $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
                    $columnsHeader = array_values(
                        array_diff($columnsHeader, $excludeColumns)
                    );
                $model = $query->getModel();
                $allColumns = Schema::getColumnListing($model->getTable());  
                $selectColumns = array_diff($allColumns, $excludeColumns);
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                   if (class_exists($modelClass)) {
                       $arNonWorkableProjectDetails =  $query->where('chart_status','AR_non_workable')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','DESC')->paginate(50);
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //    $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $modelClassDuplcates = "App\\Models\\" . $modelName.'Duplicates';
                       $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                       $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                       $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                $query->whereNull('ar_manager_rebuttal_status')
                                    ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                        })
                        //->whereBetween('updated_at',[$startDate,$endDate])
                        ->count();
                        $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                   }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                      $arNonWorkableProjectDetails = $query->where('chart_status','AR_non_workable')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','DESC')->paginate(50);
                      $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                      $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //   $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                    $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                $query->whereNull('ar_manager_rebuttal_status')
                                    ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                        })->where('CE_emp_id',$loginEmpId)
                        //->whereBetween('updated_at',[$startDate,$endDate])
                        ->count();
                    $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                   }
                 }
                 $arNonWorkableProjectDetails->getCollection()->transform(function ($item) use ($excludeColumns) {
                    foreach ($excludeColumns as $column) {
                        unset($item->{$column});
                    }
                    return $item;
                });
                 $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                 $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                 ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                 ->select('project_id', 'sub_project_id')
                 ->first();
                 $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','non_editable')->where('field_type_3','popup_visible')->get();
                 $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','editable')->where('field_type_3','popup_visible')->get();
                 $arStatusList = Helpers::arStatusList();
                 $arActionListVal = Helpers::arActionList();
                 $arDenialList = Helpers::arDenialList();
                 $arSubStatusList = Helpers::arSubStatusList();
                 $projectColSearchFields = Helpers::excludePopupNonVisibleSearchFields(ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get(), $decodedProjectName, $subProjectId);
                 $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();  
                return view('productions/clientNonWorkableTab',compact('arNonWorkableProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','popUpHeader','popupNonEditableFields','popupEditableFields','unAssignedCount','arStatusList','arActionListVal','arNonWorkableCount','rebuttalCount','projectColSearchFields','projectColSearchFieldsType','searchData','arAutoCloseCount','arDenialList','arSubStatusList'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }

    public function clientRebuttalTab(Request $request,$clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $paProject = Helpers::projectName($decodedProjectName);
               $decodedClientName = $paProject ? $paProject->project_name : null;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_rework_comments','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" . $modelName;
               $searchQuery = $modelClass::query();
               $searchData = [];
               if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                       $searchData[$key] = $value;
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                        }

                        // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                        if (is_numeric($value) || is_bool($value)) {
                            $searchQuery->where($key, $value);  // Exact match for numeric/boolean
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $searchQuery->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            $searchQuery->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                        }
                    }
                }
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();$yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
               $revokeProjectDetails = collect(); $duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$existingCallerChartsWorkLogs = [];$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
               $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
                    $columnsHeader = array_values(
                        array_diff($columnsHeader, $excludeColumns)
                    );
                $model = $searchQuery->getModel();
                $allColumns = Schema::getColumnListing($model->getTable());  
                $selectColumns = array_diff($allColumns, $excludeColumns);
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                   if (class_exists($modelClass)) {
                       $rebuttalProjectDetails = $searchQuery->where('chart_status','Rebuttal')->orderBy('id','DESC')->where(function ($query) {
                                    $query->whereNull('ar_manager_rebuttal_status')
                                        ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                            })->paginate(50);
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->whereBetween('updated_at',[$startDate,$endDate])->count();   
                       $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                    $query->whereNull('ar_manager_rebuttal_status')
                                        ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                            })
                            //->whereBetween('updated_at',[$startDate,$endDate])
                            ->count();
                       $modelClassDuplcates = "App\\Models\\" .$modelName.'Duplicates';
                       $duplicateCount = $modelClassDuplcates::whereNull('duplicate_status')->orWhere('duplicate_status','dis_agree')->count();
                       $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                       $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('end_time',NULL)->where('record_status','Revoke')->orderBy('id','DESC')->pluck('record_id')->toArray();
                       $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                   }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                      $rebuttalProjectDetails = $searchQuery->where('chart_status','Rebuttal')->where(function ($query) {
                                $query->whereNull('ar_manager_rebuttal_status')
                                    ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                        })->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','DESC')->paginate(50);
                      $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                      $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                    $query->whereNull('ar_manager_rebuttal_status')
                                        ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                            })->where('CE_emp_id',$loginEmpId)
                            //->whereBetween('updated_at',[$startDate,$endDate])
                            ->count();
                    $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('end_time',NULL)->where('record_status','Revoke')->orderBy('id','DESC')->pluck('record_id')->toArray();
                    $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                   }
                }
                 $rebuttalProjectDetails->getCollection()->transform(function ($item) use ($excludeColumns) {
                    foreach ($excludeColumns as $column) {
                        unset($item->{$column});
                    }
                    return $item;
                });
                 $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                 $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                 ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                 ->select('project_id', 'sub_project_id')
                 ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('user_type',  10)->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal = Helpers::qaSubStatusList();
                $qaStatusList = Helpers::qaStatusList();
                $arStatusList = Helpers::arStatusList();
                $arActionListVal = Helpers::arActionList();
                $arDenialList = Helpers::arDenialList();
                $arSubStatusList = Helpers::arSubStatusList();
                $qaClassificationVal = Helpers::qaClassification();
                $qaCategoryVal = Helpers::qaCategory();
                $qaScopeVal = Helpers::qaScope();
                $projectColSearchFields = Helpers::excludePopupNonVisibleSearchFields(ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get(), $decodedProjectName, $subProjectId);
                $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();  
                 return view('productions/clientRebuttalTab',compact('rebuttalProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','existingCallerChartsWorkLogs','popUpHeader','popupNonEditableFields','popupEditableFields','popupQAEditableFields','qaSubStatusListVal','unAssignedCount','qaStatusList','arNonWorkableCount','rebuttalCount','arStatusList','arActionListVal','qaClassificationVal','qaCategoryVal','qaScopeVal','projectColSearchFields','projectColSearchFieldsType','searchData','arAutoCloseCount','arDenialList','arSubStatusList'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }

     public function arRebuttalUpdate(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $decodedProjectName = Helpers::encodeAndDecodeID($request->clientName, 'decode');
                $decodedPracticeName =  $request->subProjectName == '--' ? NULL : Helpers::encodeAndDecodeID($request->subProjectName, 'decode');
                //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $originalModelClass = "App\\Models\\" . $modelName;
                $modelClass = "App\\Models\\" . $modelName.'Datas';
                $datasRecord = $modelClass::where('parent_id', $request->parentId)->orderBy('id','DESC')->first();
                $record = $originalModelClass::where('id', $request->parentId)->first();                
                if($request->ar_manager_rebuttal_status == 'agree') {
                    $chargeStatus  =  $request->chargeStatus;
                    $arErrorCount = $datasRecord['coder_error_count'];
                 } else {
                     $chargeStatus = 'Revoke';
                     $arErrorCount = $datasRecord['coder_error_count'] + 1;
                 }              
                $datasRecord->update( ['chart_status' => $chargeStatus,'coder_error_count' => $arErrorCount,'ar_manager_rebuttal_status' => $request->ar_manager_rebuttal_status,'ar_manager_rebuttal_comments' => $request->ar_manager_rebuttal_comments] );
                $record->update( ['chart_status' => $chargeStatus,'coder_error_count' => $arErrorCount,'ar_manager_rebuttal_status' => $request->ar_manager_rebuttal_status,'ar_manager_rebuttal_comments' => $request->ar_manager_rebuttal_comments] );

                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function clientExport(Request $request) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                
             
                $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && 
                Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                $decodedProjectName = Helpers::encodeAndDecodeID($request->clientName, 'decode');
                $decodedPracticeName = $request->subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($request->subProjectName, 'decode');
                //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName);
                $exportProjectName = $paProject ? $paProject->aims_project_name : NULL;
                // $exportSubProjectName = Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->new_sub_project_name != null ? Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->new_sub_project_name : Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $exportSubProjectName = Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name != null ? Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name : Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->new_sub_project_name;
                $exportFileName = $exportProjectName != NULL ? $exportProjectName .' _ '.$exportSubProjectName : 'Resolv';
                if($decodedsubProjectName != null &&  $decodedsubProjectName != 'project') {
                    $decodedsubProjectName= $decodedsubProjectName->sub_project_name;
                }
                $subProjectId = $request->subProjectName == '--' ?  NULL : $decodedPracticeName;
                $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');   
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $query = $modelClass::query();
                if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','clientName','subProjectName','recordStatusVal','resourceName','page') as $key => $value) {
                      
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  
                        }
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  // Exact match for numeric/boolean
                        } elseif ($this->isDate($value)) {  // Check if it's a date
                            $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            if($value != null) {
                              $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                            }
                        }
                    }
                }
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
          
                    if($request->recordStatusVal == "unassigned") {
                        $exportResult = $query->whereIn('chart_status',[$request->chart_status,'CE_Inprocess'])->whereNull('CE_emp_id')->get();
                        $exStatus = 'Un'.str_replace('CE_', '', $request['chart_status']);
                    } else {
                        if($request->recordStatusVal == "assigned") {
                            if($request->resourceName == 'null') {
                              $exportResult = $query->whereIn('chart_status',[$request->chart_status,'CE_Inprocess'])->whereNotNull('CE_emp_id')->get();
                            } else {
                                $exportResult = $query->whereIn('chart_status',[$request->chart_status,'CE_Inprocess'])->where('CE_emp_id',$request->resourceName)->get();
                            }
                            $exStatus = str_replace('CE_', '', $request['chart_status']);
                        } else {
                            if($request->chart_status == "Rebuttal") {
                                $exportResult = $query->where('chart_status',$request->chart_status)->whereNull('ar_manager_rebuttal_status')->orWhere('ar_manager_rebuttal_status', '!=', 'agree')
                                ->whereBetween('updated_at',[$startDate,$endDate])->get();
                            } else {
                                $exportResult = $query->where('chart_status',$request->chart_status)->whereBetween('updated_at',[$startDate,$endDate])->get();
                            }
                             if(str_contains($request['chart_status'], 'CE_')) {
                                $exStatus = str_replace('CE_', '', $request['chart_status']);
                             } else if(str_contains($request['chart_status'],'AR_')) {
                                $exStatus = str_replace('AR_', '', $request['chart_status']);
                             } else if($request['chart_status'] == 'Revoke') {
                                $exStatus = 'Rework';
                             } else {
                                $exStatus = $request['chart_status'];
                             }
                        }
                       
                    }
                  
                } else if ($loginEmpId) {
                    if($request->recordStatusVal == "assigned") {
                       $exportResult = $query->whereIn('chart_status',[$request->chart_status,'CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->get();
                       $exStatus = str_replace('CE_', '', $request['chart_status']);
                    } else {
                        if($request->chart_status == "Rebuttal") {
                            $exportResult = $query->where('chart_status',$request->chart_status)->whereNull('ar_manager_rebuttal_status')->orWhere('ar_manager_rebuttal_status', '!=', 'agree')
                            ->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->get();
                        } else {
                           $exportResult = $query->where('chart_status',$request->chart_status)->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->get();
                        }
                        if(str_contains($request['chart_status'],'CE_')) {
                            $exStatus = str_replace('CE_', '', $request['chart_status']);
                         } else if(str_contains($request['chart_status'],'AR_')) {
                            $exStatus = str_replace('AR_', '', $request['chart_status']);
                         } else if($request['chart_status'] == 'Revoke') {
                                $exStatus = 'Rework';
                         } else {
                            $exStatus = $request['chart_status'];
                         }
                    }
                    
                }
                $fields = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['id','QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers','ar_status_code','ar_action_code','ar_denial_codes','ar_substatus_codes',
                    'updated_at','created_at', 'deleted_at'];
                    $fields = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($fields,'aging','aging_range');
                }
                $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
                $selectColumns = array_values(array_diff($fields, $excludeColumns));
                return Excel::download(new ProductionExport($selectColumns,$exportResult), $exportFileName.' _ '.$exStatus.'_export.xlsx');
                } catch (\Exception $e) {
                    log::debug($e->getMessage());
                }
            } else {
                return redirect('/');
            }       
    }
    public function clientDuplicateExport(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
             
                $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && 
                Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                $decodedProjectName = Helpers::encodeAndDecodeID($request->clientName, 'decode');
                $decodedPracticeName = $request->subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($request->subProjectName, 'decode');
                //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName);
                if($decodedsubProjectName != null &&  $decodedsubProjectName != 'project') {
                    $decodedsubProjectName= $decodedsubProjectName->sub_project_name;
                }
                $subProjectId = $request->subProjectName == '--' ?  NULL : $decodedPracticeName;
                $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');   
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName."Duplicates";
                $query = $modelClass::query();
                $duplicateTableColumns = class_exists($modelClass)
                    ? Schema::getColumnListing((new $modelClass)->getTable())
                    : [];
                if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','clientName','subProjectName','recordStatusVal','resourceName','chart_status','page') as $key => $value) {
                        if (!in_array($key, $duplicateTableColumns, true)) {
                            continue;
                        }
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  
                        }
                        if ($value === null || $value === '' || $value === 'null') {
                            continue;
                        }
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  // Exact match for numeric/boolean
                        } elseif ($this->isDate($value)) {  // Check if it's a date
                            $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                        }
                    }
                }
                $query->where(function ($statusQuery) {
                    $statusQuery->whereNull('duplicate_status')->orWhere('duplicate_status', 'dis_agree');
                });
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                    $exportResult = $query->orderBy('id','DESC')->get();
                } else if ($loginEmpId) {
                    $exportResult = $query->where('CE_emp_id',$loginEmpId)->orderBy('id','DESC')->get();
                }
                $fields = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['id','QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers','ar_status_code','ar_action_code','ar_denial_codes','ar_substatus_codes',
                    'updated_at','created_at', 'deleted_at'];
                    $fields = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($fields,'aging','aging_range');
                }
                $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
                $selectColumns = array_values(array_diff($fields, $excludeColumns));
                return Excel::download(new ProductionExport($selectColumns,$exportResult), 'Resolv_Duplicate_Records_Export.xlsx');
                } catch (\Exception $e) {
                    log::debug($e->getMessage());
                }
            } else {
                return redirect('/');
            }       
    }
    // protected function isDate($value) {
    //     return strtotime($value) ? true : false;
    // }
    protected function isDate($value, $format = 'Y-m-d') {
        $date = \DateTime::createFromFormat($format, $value);
        return $date && $date->format($format) === $value;
    }

    public function manualCallerChartWorkLogs(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data =  $request->all();
                $currentTime = Carbon::now();
                $data['emp_id'] = Session::get('loginDetails')['userDetail']['emp_id'];
                $data['project_id'] = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                $data['sub_project_id'] = $data['subProjectName'] == '--' ? NULL : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                $data['start_time'] = $currentTime->format('Y-m-d H:i:s');
                $data['record_status'] = "CE_Inprocess";
                // $existingRecordId = CallerChartsWorkLogs::where('record_id',$data['record_id'])->where('record_status',"CE_Assigned")->first();
                $existingRecordId = CallerChartsWorkLogs::where('project_id', $data['project_id'])->where('sub_project_id',$data['sub_project_id'])->where('emp_id',$data['emp_id'] )
                ->where('record_status',$data['record_status'])->where('end_time',NULL)->first();

                // if(empty($existingRecordId) || !isset($existingRecordId->start_time) || $existingRecordId->start_time == null) {
                if (!$existingRecordId) {
                        $save_flag = CallerChartsWorkLogs::create($data);
                        $startTimeVal = $data['start_time'];          
                } else {
                    $startTimeVal =  $data['start_time'];
                    $save_flag = 1;
                }
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

    public function manualDuplicateColumnCheck(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data =  $request->all();
                $decodedProjectName = Helpers::encodeAndDecodeID($request->clientName, 'decode');
                $decodedPracticeName =  $request->subProjectName == '--' ? NULL : Helpers::encodeAndDecodeID($request->subProjectName, 'decode');
                //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project':Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName.'Datas';
                $originalModelClass = "App\\Models\\" . $modelName;
                
                // $duplicateParentRecord  =  $originalModelClass::where($request->duplicateColumnData)->first();
                // $duplicateDatasRecord  =  $modelClass::where($request->duplicateColumnData)->first();
                unset($data['clientName'], $data['subProjectName']);
                $data['invoke_date'] = carbon::now()->format('Y-m-d');
                $duplicateRecordExisting = $originalModelClass::where($data)->exists();
            //    if($duplicateParentRecord || $duplicateDatasRecord) {
                if($duplicateRecordExisting) {
                    $duplicateMsg = 'Duplicate Entry';
                    return response()->json(['error' => true,'responseText'=>$duplicateMsg]);
               }else {
                return response()->json(['success' => true]);
               }
            
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function nonworkableStatusUpdate(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {

            try {
                $decodedProjectName = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                $decodedPracticeName = $request['subProjectName'] == '--' ? '--' : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                $checkedValues = json_decode($request->input('checkedRowValues'), true);
                $column_value = $request['selectedValue'];
                $columns = Schema::getColumnListing($table_name);
                $possibleColumns = ['ar_notes', 'notes', 'remarks', 'comments'];
                $columnToUpdate = null;
                foreach ($possibleColumns as $column) {
                    if (in_array($column, $columns)) {
                        $columnToUpdate = $column;
                        break; // Stop at the first found column
                    }
                }
                if($request['selectedRecords'] == "none") {
                   if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                        foreach($checkedValues as $data) {
                            $existingRecord = $modelClass::where('id',$data['value'])->where('chart_status','CE_Assigned')->first();
                           // $existingRecord->update(['chart_status' => 'AR_non_workable']);
                           if ($existingRecord) {
                                $updateData = ['chart_status' => 'AR_non_workable'];                            
                                if ($columnToUpdate) {
                                    $updateData[$columnToUpdate] = $column_value;
                                }                            
                              $existingRecord->update($updateData);
                           }
                        }
                    } else {
                        foreach($checkedValues as $data) {
                            $existingRecord = $modelClass::where('id',$data['value'])->where('CE_emp_id',$loginEmpId)->where('chart_status','CE_Assigned')->first();
                            //$existingRecord->update(['chart_status' => 'AR_non_workable']);
                            if ($existingRecord) {
                                $updateData = ['chart_status' => 'AR_non_workable'];                            
                                if ($columnToUpdate) {
                                    $updateData[$columnToUpdate] = $column_value;
                                }                            
                              $existingRecord->update($updateData);
                           }
                        }
                    }   
                } else {
                    $query = $modelClass::query();
                    $searchData = []; 
                    foreach ($request->except('_token', 'checkedRowValues', 'clientName','subProjectName','selectedRecords','selectedValue') as $key => $value) {
                        $searchData[$key] = $value;
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  
                        } elseif ($this->isDate($value)) {  
                            $query->whereDate($key, '=', $value);  
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); 
                        } else {
                            if($value != null) {
                            $query->where($key, 'like', '%' . $value . '%'); 
                            }
                        }
                    }
                   if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                        $query->where('chart_status', 'CE_Assigned')->update(['chart_status' => 'AR_non_workable']);
                    //     $existingRecord =$query->where('chart_status', 'CE_Assigned')->first();
                    //     if ($existingRecord) {
                    //         $updateData = ['chart_status' => 'AR_non_workable'];                            
                    //         if ($columnToUpdate) {
                    //             $updateData[$columnToUpdate] = $column_value;
                    //         }                            
                    //        $existingRecord->update($updateData);
                    //    }

                    } else {
                       $query->where('CE_emp_id',$loginEmpId)->where('chart_status','CE_Assigned')->update(['chart_status' => 'AR_non_workable']);
                        // $existingRecord =$query->where('CE_emp_id',$loginEmpId)->where('chart_status', 'CE_Assigned')->first();
                        // if ($existingRecord) {
                        //     $updateData = ['chart_status' => 'AR_non_workable'];                            
                        //     if ($columnToUpdate) {
                        //         $updateData[$columnToUpdate] = $column_value;
                        //     }                            
                        //     $existingRecord->update($updateData);
                        // }
                    }
                }
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function workableStatusUpdate(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {

            try {
                $decodedProjectName = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                $decodedPracticeName = $request['subProjectName'] == '--' ? '--' : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $datasModelClass =  "App\\Models\\" . $modelName.'Datas';
                $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                $checkedValues = json_decode($request->input('checkedRowValues'), true);
                $columns = Schema::getColumnListing($table_name);
                $possibleColumns = ['ar_notes', 'notes', 'remarks', 'comments'];
                $columnToUpdate = null;
                foreach ($possibleColumns as $column) {
                    if (in_array($column, $columns)) {
                        $columnToUpdate = $column;
                        break; // Stop at the first found column
                    }
                }
                if($request['selectedRecords'] == "none") {
                   if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                        foreach($checkedValues as $data) {
                            $existingRecord = $modelClass::where('id',$data['value'])->where('chart_status','AR_non_workable')->first();
                            $updateData = ['chart_status' => 'CE_Assigned'];                            
                            if ($columnToUpdate) {
                                $updateData[$columnToUpdate] = NULL;
                            }            
                            // $existingRecord->update(['chart_status' => 'CE_Assigned']);
                            $existingRecord->update($updateData);
                            $datasExistingRecord = $datasModelClass::where('parent_id',$data['value'])->where('chart_status','AR_non_workable')->first();
                            if ($datasExistingRecord) {
                                $datasExistingRecord->forceDelete();
                            }
                        }
                    } else {
                        foreach($checkedValues as $data) {
                            $existingRecord = $modelClass::where('id',$data['value'])->where('CE_emp_id',$loginEmpId)->where('chart_status','AR_non_workable')->first();
                            $updateData = ['chart_status' => 'CE_Assigned'];                            
                            if ($columnToUpdate) {
                                $updateData[$columnToUpdate] = NULL;
                            }  
                            //$existingRecord->update(['chart_status' => 'CE_Assigned']);
                            $existingRecord->update($updateData);
                            $datasExistingRecord = $datasModelClass::where('parent_id',$data['value'])->where('CE_emp_id',$loginEmpId)->where('chart_status','AR_non_workable')->first();
                            if ($datasExistingRecord) {
                                $datasExistingRecord->forceDelete();
                            }
                        }
                    }   
                } else {
                    $query = $modelClass::query();
                    $dataQuery = $datasModelClass::query();
                    $searchData = []; 
                    foreach ($request->except('_token', 'checkedRowValues', 'clientName','subProjectName','selectedRecords') as $key => $value) {
                        $searchData[$key] = $value;
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  
                            $dataQuery->where($key, $value);  
                        } elseif ($this->isDate($value)) {  
                            $query->whereDate($key, '=', $value);  
                            $dataQuery->whereDate($key, '=', $value);  
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); 
                            $dataQuery->where($key, $value);  
                        } else {
                            if($value != null) {
                            $query->where($key, 'like', '%' . $value . '%'); 
                            $dataQuery->where($key, 'like', '%' . $value . '%'); 
                            }
                        }
                    }
                   if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                            $updateData = ['chart_status' => 'CE_Assigned'];                            
                            if ($columnToUpdate) {
                                $updateData[$columnToUpdate] = NULL;
                            }  
                            // $query->where('chart_status', 'AR_non_workable')->update(['chart_status' => 'CE_Assigned']);
                            $query->where('chart_status', 'AR_non_workable')->update($updateData);
                            $datasExistingRecord = $dataQuery->where('chart_status','AR_non_workable')->first();
                            if ($datasExistingRecord) {
                                $datasExistingRecord->forceDelete();
                            }

                    } else {
                        $updateData = ['chart_status' => 'CE_Assigned'];                            
                        if ($columnToUpdate) {
                            $updateData[$columnToUpdate] = NULL;
                        }  
                        //$query->where('CE_emp_id',$loginEmpId)->where('chart_status','AR_non_workable')->update(['chart_status' => 'CE_Assigned']);
                        $query->where('CE_emp_id',$loginEmpId)->where('chart_status','AR_non_workable')->update($updateData);
                        $datasExistingRecord = $dataQuery->where('CE_emp_id',$loginEmpId)->where('chart_status','AR_non_workable')->first();
                        if ($datasExistingRecord) {
                            $datasExistingRecord->forceDelete();
                        }
                    }
                }
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function projectCallChartWorkLogs(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {

            try {
                 $decodedProjectId = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                 $decodedPracticeId = $request['subProjectName'] == '--' ? '--' : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                 $endTimeCallerChartsWorkLogs = CallerChartsWorkLogs::where('emp_id',$request['assigneeAr'])->where('project_id',$decodedProjectId)->where('sub_project_id',$decodedPracticeId)->whereNull('end_time')->get();
                if($endTimeCallerChartsWorkLogs && count($endTimeCallerChartsWorkLogs)) {
                    foreach($endTimeCallerChartsWorkLogs as $data) {             
                        $startTime = Carbon::parse($data->start_time);
                        $endTime = $startTime->addMinute();    
                        $workTime = "00:01:00";  
                        $data->update([
                            'end_time' => $endTime,
                            'work_time' => $workTime,
                        ]);
                    }  
                    return response()->json(['success' => true]); 
                } else {
                    return response()->json(['success' => false,'message' => 'No claims found']);
                }                                   
               
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientMultiStore(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $checkedValues = json_decode($request->input('checkedRowValues'), true);            
                $decodedProjectName = Helpers::encodeAndDecodeID($request->clientName, 'decode');
                $decodedPracticeName =  $request->subProjectName == '--' ? NULL : Helpers::encodeAndDecodeID($request->subProjectName, 'decode');
                //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project':Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName.'Datas';
                $originalModelClass = "App\\Models\\" . $modelName;
                $data = $callData = $originDataArray = [];
                $callData['project_id'] = $decodedProjectName; 
                $callData['sub_project_id'] = $decodedPracticeName; 
                foreach ($request->except('_token', 'parent', 'child','page','clientName','subProjectName','checkedRowValues') as $key => $value) {
                    if (is_array($value)) {
                        $originDataArray[$key] = in_array(null, $value, true) ? null : implode('_el_', $value);
                    } else {
                        $originDataArray[$key] = $value;
                    }
                }   
              
                foreach($checkedValues as $originId) {
                    $originDataArray['parent_id'] = $originId['value']; 
                    $callData['record_id'] = $originId['value']; 
                    $originData = $originalModelClass::where('id',$originDataArray['parent_id'])->first()->toArray();
                    unset($originData['id']);
                    unset($originData['created_at']);
                    unset($originData['updated_at']);
                    $data = array_merge($originData,$originDataArray);
                   // dd($originDataArray,$data,$checkedValues);
                    $loginEmpId = $originalModelClass::where('id',$data['parent_id'])->first()->CE_emp_id;
                    $datasRecord = $modelClass::where('parent_id', $data['parent_id'])->orderBy('id','DESC')->first();
                    $coderCompletedRecords = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->get();
                    $coderCompletedRecordsCount = count($coderCompletedRecords); $data['coder_work_date'] = $data['ar_at'] = NULL;
                   // $data['ar_at'] = Carbon::now()->format('Y-m-d H:i:s');
                    $autoCloseRecords = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->get();
                    $autoCloseRecordsCount = count($autoCloseRecords); 
                    if( $data['chart_status'] == "CE_Completed") {
                        $data['coder_work_date'] = Carbon::now()->format('Y-m-d');
                         $data['ar_at'] = Carbon::now()->format('Y-m-d H:i:s');
                        if($decodedPracticeName == NULL) {
                            $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                            ->where('qa_percentage','!=', 0)
                            ->where(function($query) use ($loginEmpId) {
                                $query->where('coder_emp_id', $loginEmpId)
                                    ->orWhereNull('coder_emp_id');
                            })->orderBy('id', 'DESC')->get();
                            $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL;
                            foreach ($qasamplingDetailsList as $qasamplingDetails) {
                                if($qasamplingDetails != null) {
                                    $qaPercentage = $qasamplingDetails["qa_percentage"];
                                    $qarecords = $coderCompletedRecordsCount*$qaPercentage/100;
                                    $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                                    $samplingRecordCount =  count($samplingRecord);
                                    if($qarecords > $samplingRecordCount) {
                                        $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                        $data['qa_work_status'] = "Sampling";
                                        $data['chart_status'] = "CE_Completed";
                                        break;
                                    } else {
                                        $data['qa_work_status'] = "Auto_Close";
                                    }
                                }
                            }
                        } else {
                            $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL; 
                            $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                                                    ->where('sub_project_id', $decodedPracticeName)
                                                    ->where('qa_percentage','!=', 0)
                                                    ->where(function($query) use ($loginEmpId) {
                                                        $query->where('coder_emp_id', $loginEmpId)
                                                            ->orWhereNull('coder_emp_id');
                                                    })->orderBy('id', 'DESC')->get();
                            $qasamplingDetailsPercentage = QualitySampling::where('project_id', $decodedProjectName)
                            ->where('sub_project_id', $decodedPracticeName)
                            ->where('qa_percentage','!=', 0)
                            ->where(function($query) use ($loginEmpId) {
                                $query->where('coder_emp_id', $loginEmpId)
                                    ->orWhereNull('coder_emp_id');
                            })->sum('qa_percentage');                    
                            foreach ($qasamplingDetailsList as $qKey => $qasamplingDetails) {
                                if($qasamplingDetails != null) {
                                    $allCompletedRecordsTotalCount = $coderCompletedRecordsCount*$qasamplingDetailsPercentage/100;
                                    $allCompletedRecords = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId);
                                    $qaDynamicColumns =  $qasamplingDetails["qa_sample_column_name"];//office_keys,worklist,insurance_balance
                                    $qaDynamicValues =  $qasamplingDetails["qa_sample_column_value"];//off1,work2,ins2
                                    $qaDynamicColumnDataType =  $qasamplingDetails["qa_sample_column_data_type"];
                                    $qaDynamicColumnCondition =  $qasamplingDetails["qa_sample_column_condition"];
                                  
                                    if($qaDynamicColumns != null && $qaDynamicValues != null && $qaDynamicColumnDataType != null && $qaDynamicColumnCondition != null) {
                                        $qaDynamicColumns = explode(',', $qasamplingDetails["qa_sample_column_name"]); // Convert to array
                                        $qaDynamicValues = explode(',', $qasamplingDetails["qa_sample_column_value"]); // Convert to array
                                        $qaDynamicColumnDataType =  explode(',',$qasamplingDetails["qa_sample_column_data_type"]);
                                        $qaDynamicColumnCondition =  explode(',',$qasamplingDetails["qa_sample_column_condition"]);
                                        if(count($qaDynamicColumns) === count($qaDynamicValues) && count($qaDynamicValues) === count($qaDynamicColumnDataType) && count($qaDynamicColumnDataType) === count($qaDynamicColumnCondition)) {
                                            $mergedArray = $qaDynamicColumns;
                                        } else {
                                            $mergedArray = []; // Handle mismatched array lengths
                                           
                                        }
                                        $allKeysExist = true;
                                        $allValuesMatch = true;
    
                                        foreach ($mergedArray as $key => $value) {
                                            if (!array_key_exists($value, $data)) {
                                                $allKeysExist = false;
                                                break;
                                            }                                        
                                            if ($qaDynamicColumnDataType[$key] == "string" && $data[$value] !== $qaDynamicValues[$key]) {
                                                $allValuesMatch = false;
                                            }
                                        }
                                        $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])
                                        ->where('qa_work_status','Sampling');
                                        foreach ($qaDynamicColumns as $index => $column) {
                                            $allCompletedRecords->where($column,$qaDynamicColumnCondition[$index],$qaDynamicValues[$index] ?? null);
                                            $samplingRecord->where($column,$qaDynamicColumnCondition[$index],$qaDynamicValues[$index] ?? null);
                                        }
                                        $allCompletedRecords = $allCompletedRecords->get();
                                        $samplingRecord = $samplingRecord->get();
                                        $samplingRecordCount =  count($samplingRecord); 
                                        $allCompletedRecordsCount = count($allCompletedRecords);
                                        $qaPercentage = $qasamplingDetails["qa_percentage"];
                                        if($allCompletedRecordsCount != 0) {
                                            $qarecords = $allCompletedRecordsCount*(int)$qasamplingDetails["qa_percentage"]/100;
                                        } else {
                                            $qarecords = $allCompletedRecordsTotalCount;
                                        }
                                    } else {
                                        $allKeysExist = true;
                                        $allValuesMatch = true;
                                        $samplingRecord = $originalModelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])
                                        ->where('qa_work_status','Sampling');
                                        $allCompletedRecords = $allCompletedRecords->get();
                                        $samplingRecord = $samplingRecord->get();
                                        $samplingRecordCount =  count($samplingRecord);   
                                        $allCompletedRecordsCount = count($coderCompletedRecords);
                                        $qaPercentage = $qasamplingDetails["qa_percentage"];
                                        $qarecords = $allCompletedRecordsTotalCount;
                                    }                                                                                
                                    if($qarecords >= $samplingRecordCount && $allKeysExist && $allValuesMatch) {
                                        $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                        $data['qa_work_status'] = "Sampling";
                                        $data['chart_status'] = "CE_Completed";
                                        break;
                                    } else {
                                         $data['qa_work_status'] = "Auto_Close";
                                    }
                                }
                            }
                        }

                    }else if($data['chart_status'] == "Auto_Close") {
                        $data['coder_work_date'] = Carbon::now()->format('Y-m-d');
                         $data['ar_at'] = Carbon::now()->format('Y-m-d H:i:s');
                        if($decodedPracticeName == NULL) {
                            // $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                            // ->where(function($query) use ($loginEmpId) {
                            //     $query->where('coder_emp_id', $loginEmpId)
                            //         ->orWhereNull('coder_emp_id');
                            // })->orderBy('id', 'DESC')->get();
                            $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL; $data['qa_work_status'] = "Auto_Close";
                            // foreach ($qasamplingDetailsList as $qasamplingDetails) {
                            //     if($qasamplingDetails != null) {
                            //         $qaPercentage = $qasamplingDetails["qa_percentage"];
                            //         $qarecords = $autoCloseRecordsCount*$qaPercentage/100;
                            //         $samplingRecord = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                            //         $samplingRecordCount =  count($samplingRecord);
                            //         if($qarecords > $samplingRecordCount) {
                            //             $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                            //             $data['qa_work_status'] = "Sampling";
                            //             $data['chart_status'] = "Auto_Close";
                            //             break;
                            //         } else {
                            //             $data['qa_work_status'] = "Auto_Close";
                            //             $data['chart_status'] = "Auto_Close";
                            //         }
                            //     }
                            // }
                        } else {
                            // $qasamplingDetailsList = QualitySampling::where('project_id', $decodedProjectName)
                            //                         ->where('sub_project_id', $decodedPracticeName)
                            //                         ->where(function($query) use ($loginEmpId) {
                            //                             $query->where('coder_emp_id', $loginEmpId)
                            //                                 ->orWhereNull('coder_emp_id');
                            //                         })->orderBy('id', 'DESC')->get();
                            $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL; $data['chart_status'] = "Auto_Close";
                            // foreach ($qasamplingDetailsList as $qasamplingDetails) {
                            //     if($qasamplingDetails != null) {
                            //         $qaPercentage = $qasamplingDetails["qa_percentage"];
                            //         $qarecords = $autoCloseRecordsCount*$qaPercentage/100;
                            //         $samplingRecord = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                            //         $samplingRecordCount =  count($samplingRecord);
                            //         if($qarecords >= $samplingRecordCount ) {
                            //             $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                            //             $data['qa_work_status'] = "Sampling";
                            //             $data['chart_status'] = "Auto_Close";
                            //             break;
                            //         } else {
                            //             $data['qa_work_status'] = "Auto_Close";
                            //             $data['chart_status'] = "Auto_Close";
                        
                            //         }
                            //     }
                            // }
                        }

                    }
                    $record = $originalModelClass::where('id', $data['parent_id'])->first();
               
                    $data['invoke_date'] = date('Y-m-d');
                    $data['CE_emp_id'] = $loginEmpId;
                    $callData['emp_id'] = $loginEmpId; 
                    $qaData = $originalModelClass::where('id', $data['parent_id'])->first()->toArray();
                    $excludeKeys = ['id', 'created_at', 'updated_at', 'deleted_at'];
                    $filteredQAData = collect($qaData)->except($excludeKeys)->toArray();
                    $data = array_merge($data, array_diff_key($filteredQAData, $data));
                    $currentTime = Carbon::now();
                    $callData['start_time'] = $currentTime->format('Y-m-d H:i:s'); 
                    $callData['end_time'] = $currentTime->format('Y-m-d H:i:s'); 
                    $callData['work_time'] = "00:00:00"; 
                    $callData['record_status'] =  $data['chart_status']; 
              //   dd($request->all(),$checkedValues,$data,'if',$datasRecord,$loginEmpId,$callData);
                    if($datasRecord != null) {
                        $datasRecord->update($data);
                    ($data['chart_status'] == "CE_Completed" || $data['chart_status'] == "Auto_Close") ? $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status'],'coder_work_date' => $data['coder_work_date'],'ar_at' => $data['ar_at']]) : $record->update( ['chart_status' => $data['chart_status'],'ce_hold_reason' => $data['ce_hold_reason'],'ar_at' => $data['ar_at']] );
                    } else {
                    ($data['chart_status'] == "CE_Completed" || $data['chart_status'] == "Auto_Close") ? $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status'],'coder_work_date' => $data['coder_work_date'],'ar_at' => $data['ar_at']]) : $record->update( ['chart_status' => $data['chart_status'],'ce_hold_reason' => $data['ce_hold_reason'],'ar_at' => $data['ar_at']] );
                    $modelClass::create($data);
                    }
                    $callChartWorkLogExistingRecords = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                    ->where('project_id', $decodedProjectName)
                    ->where('sub_project_id', $decodedPracticeName)
                    ->where('emp_id', $loginEmpId)->where('end_time',NULL)->get();
                        if ($callChartWorkLogExistingRecords->isNotEmpty()) {
                            foreach ($callChartWorkLogExistingRecords as $callChartWorkLog) {
                                $start_time = Carbon::parse($callChartWorkLog->start_time);
                                $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                                $callChartWorkLog->update( ['record_status' => $data['chart_status'],'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time] );
                            }
                       } else {
                           CallerChartsWorkLogs::create($callData);
                       }
                    
           
              }           
              return response()->json(['success' => true]); 
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    
    public function arAutoClose(Request $request,$clientName,$subProjectName) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
               $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
               $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
               $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
               $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
               //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
               $paProject = Helpers::projectName($decodedProjectName);
               $decodedClientName = $paProject ? $paProject->project_name : null;
               $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
               $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
               $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
               }
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" . $modelName;
               $query = $modelClass::query();
               $searchData = [];
               if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                       $searchData[$key] = $value;
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                        }

                        // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  // Exact match for numeric/boolean
                        } elseif ($this->isDate($value)) {  // Check if it's a date
                            $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            if($value != null) {
                              $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                            }
                        }
                    }
                }
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
               $arAutoCloseProjectDetails = collect();$duplicateCount = 0;$assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
               $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
                    $columnsHeader = array_values(
                        array_diff($columnsHeader, $excludeColumns)
                    );
                $model = $query->getModel();
                $allColumns = Schema::getColumnListing($model->getTable());  
                $selectColumns = array_diff($allColumns, $excludeColumns);
                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                   if (class_exists($modelClass)) {
                       $arAutoCloseProjectDetails =  $query->where('chart_status','Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','DESC')->paginate(50);
                       $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                       $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //    $reworkCount = $modelClass::where('chart_status','Revoke')->where('updated_at','<=',$yesterDayDate)->count();
                       $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $modelClassDuplcates = "App\\Models\\" . $modelName.'Duplicates';
                       $duplicateCount = $modelClassDuplcates::count();
                       $unAssignedCount = $modelClass::where('chart_status','CE_Assigned')->whereNull('CE_emp_id')->count();
                       $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                       $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                $query->whereNull('ar_manager_rebuttal_status')
                                    ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                        })
                        //->whereBetween('updated_at',[$startDate,$endDate])
                        ->count();
                   }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                      $arAutoCloseProjectDetails = $query->where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','DESC')->paginate(50);
                      $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                      $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                      $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    //   $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->where('updated_at','<=',$yesterDayDate)->count();
                    $reworkCount = $modelClass::where('chart_status','Revoke')->where('CE_emp_id',$loginEmpId)->whereNull('tl_error_count')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $arAutoCloseCount = $modelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $arNonWorkableCount = $modelClass::where('chart_status','AR_non_workable')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where(function ($query) {
                                $query->whereNull('ar_manager_rebuttal_status')
                                    ->orWhere('ar_manager_rebuttal_status', '!=', 'agree');
                        })->where('CE_emp_id',$loginEmpId)
                        //->whereBetween('updated_at',[$startDate,$endDate])
                        ->count();
                   }
                 }
                 $arAutoCloseProjectDetails->getCollection()->transform(function ($item) use ($excludeColumns) {
                    foreach ($excludeColumns as $column) {
                        unset($item->{$column});
                    }
                    return $item;
                });
                 $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                 $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                 ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                 ->select('project_id', 'sub_project_id')
                 ->first();
                 $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','non_editable')->where('field_type_3','popup_visible')->get();
                 $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','editable')->where('field_type_3','popup_visible')->get();
                 $arStatusList = Helpers::arStatusList();
                 $arActionListVal = Helpers::arActionList();
                 $arDenialList = Helpers::arDenialList();
                 $arSubStatusList = Helpers::arSubStatusList();
                 $projectColSearchFields = Helpers::excludePopupNonVisibleSearchFields(ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get(), $decodedProjectName, $subProjectId);
                 $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();  
             
                 return view('productions/arAutoClose',compact('arAutoCloseProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','popUpHeader','popupNonEditableFields','popupEditableFields','unAssignedCount','arStatusList','arActionListVal','arNonWorkableCount','rebuttalCount','projectColSearchFields','projectColSearchFieldsType','searchData','arAutoCloseCount','arDenialList','arSubStatusList'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }
    
    public function autoCloseStatusUpdate(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {

            try {
                $decodedProjectName = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                $decodedPracticeName = $request['subProjectName'] == '--' ? '--' : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $datasModelClass =  "App\\Models\\" . $modelName.'Datas';;
                $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                $checkedValues = json_decode($request->input('checkedRowValues'), true);
                if($request['selectedRecords'] == "none") {
                   if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                        foreach($checkedValues as $data) {
                            $existingRecord = $modelClass::where('id',$data['value'])->where('chart_status','Auto_Close')->first();
                            $existingRecord->update(['chart_status' => 'CE_Assigned','QA_emp_id' => NULL,'qa_work_status' => NULL,'coder_work_date' => NULL,'ar_at' => NULL,'updated_at' => NULL]);
                            $datasExistingRecord = $datasModelClass::where('parent_id',$data['value'])->where('chart_status','Auto_Close')->first();
                            if ($datasExistingRecord) {
                                $datasExistingRecord->forceDelete();
                            }
                        }
                    } else {
                        foreach($checkedValues as $data) {
                            $existingRecord = $modelClass::where('id',$data['value'])->where('CE_emp_id',$loginEmpId)->where('chart_status','Auto_Close')->first();
                            $existingRecord->update(['chart_status' => 'CE_Assigned','QA_emp_id' => NULL,'qa_work_status' => NULL,'coder_work_date' => NULL,'ar_at' => NULL,'updated_at' => NULL]);
                            $datasExistingRecord = $datasModelClass::where('parent_id',$data['value'])->where('chart_status','Auto_Close')->first();
                            if ($datasExistingRecord) {
                                $datasExistingRecord->forceDelete();
                            }
                            // $existingRecord->update(['chart_status' => 'CE_Assigned']);
                        }
                    }   
                } else {
                    $query = $modelClass::query();
                    $searchData = []; 
                    foreach ($request->except('_token', 'checkedRowValues', 'clientName','subProjectName','selectedRecords') as $key => $value) {
                        $searchData[$key] = $value;
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  
                        } elseif ($this->isDate($value)) {  
                            $query->whereDate($key, '=', $value);  
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); 
                        } else {
                            if($value != null) {
                            $query->where($key, 'like', '%' . $value . '%'); 
                            }
                        }
                       if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                            // $query->where('chart_status', 'Auto_Close')->update(['chart_status' => 'CE_Assigned']);
                            $query->where('id',$value)->where('chart_status', 'Auto_Close')->update(['chart_status' => 'CE_Assigned','QA_emp_id' => NULL,'qa_work_status' => NULL,'coder_work_date' => NULL,'ar_at' => NULL,'updated_at' => NULL]);
                            $datasExistingRecord = $datasModelClass::where('parent_id',$value)->where('chart_status','Auto_Close')->first();
                            if ($datasExistingRecord) {
                                $datasExistingRecord->forceDelete();
                            }
                        } else {
                            // $query->where('CE_emp_id',$loginEmpId)->where('chart_status','Auto_Close')->update(['chart_status' => 'CE_Assigned']);
                            $query->where('id',$value)->where('CE_emp_id',$loginEmpId)->where('chart_status', 'Auto_Close')->update(['chart_status' => 'CE_Assigned','QA_emp_id' => NULL,'qa_work_status' => NULL,'coder_work_date' => NULL,'ar_at' => NULL,'updated_at' => NULL]);
                            $datasExistingRecord = $datasModelClass::where('parent_id',$value)->where('chart_status','Auto_Close')->first();
                            if ($datasExistingRecord) {
                                $datasExistingRecord->forceDelete();
                            }
                        }
                    }
                    
                }
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    // public function getClaimHistory(Request $request,$clientName,$subProjectName) {
    public function getClaimHistory(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
           try {
                $uniqueColumnData = $request->input('uniqueColumnData');
                if (!$uniqueColumnData) {
                    return response()->json(['error' => 'Invalid Data Received'], 400);
                }
                $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                $clientName=$uniqueColumnData['clientName'];
                $subProjectName=$uniqueColumnData['subProjectName'];
                $decodedProjectName = Helpers::encodeAndDecodeID($uniqueColumnData['clientName'], 'decode');
                $decodedPracticeName = $uniqueColumnData['subProjectName'] == '--' ? '--' :Helpers::encodeAndDecodeID($uniqueColumnData['subProjectName'], 'decode');
                //$decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $columnsHeader=[];
               if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at','created_at', 'deleted_at','parent_id','ar_manager_rebuttal_status','ar_manager_rebuttal_comments','qa_manager_rebuttal_status','qa_manager_rebuttal_comments','QA_comments_count'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
               }
               $subProjectId = $uniqueColumnData['subProjectName'] == '--' ?  NULL : $decodedPracticeName;
               $excludeColumns = Helpers::getPopupNonVisiblePatientColumns($decodedProjectName, $subProjectId);
               $columnsHeader = array_values(array_diff($columnsHeader, $excludeColumns));
               $modelName = Str::studly($table_name);
               $modelClass = "App\\Models\\" . $modelName.'Datas';
               $query = $modelClass::query();
               $searchData = []; $filteredData = Arr::except($uniqueColumnData, ['clientName', 'subProjectName']);
           
                    foreach ($filteredData as $key => $value) {
                       $searchData[$key] = $value; 
                        if (is_array($value)) {
                            $value = implode('_el_', $value);
                        }

                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  
                        } elseif ($this->isDate($value)) { 
                            $query->whereDate($key, '=', $value);  
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); 
                        } else {
                            if($value != null) {
                              $query->where($key,$value); 
                            }
                        }
                    }
              
               $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString(); $yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
               $claimHistoryDetails = collect();$duplicateCount = 0;$assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$subProjectId = $uniqueColumnData['subProjectName'] == '--' ?  NULL : $decodedPracticeName;
              if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                   if (class_exists($modelClass)) {
                       $claimHistoryDetails =  $query->orderBy('id','ASC')->get();
                       //->paginate(50);
                   }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                      $claimHistoryDetails = $query->where('CE_emp_id',$loginEmpId)->orderBy('id','ASC')->get();
                      //->paginate(50);                  
                   }
                 }
                 $claimHistoryDetails = Helpers::hidePopupNonVisiblePatientFromRecords($claimHistoryDetails, $excludeColumns);
                 $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                 $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                 ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                 ->select('project_id', 'sub_project_id')
                 ->first();
                 $popupNonEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','non_editable')->where('field_type_3','popup_visible')->get();
                 $popupEditableFields = formConfiguration::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type','editable')->where('field_type_3','popup_visible')->get();
                 $projectColSearchFields = Helpers::excludePopupNonVisibleSearchFields(ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get(), $decodedProjectName, $subProjectId);
                 $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();  
             
                 return view('productions/claimHistory',compact('claimHistoryDetails','columnsHeader','clientName','subProjectName','modelClass','duplicateCount','popUpHeader','popupNonEditableFields','popupEditableFields','projectColSearchFields','projectColSearchFieldsType','searchData'));

           } catch (\Exception $e) {
               log::debug($e->getMessage());
           }
       } else {
           return redirect('/');
       }
    }
    public function pendingTabStatusUpdate(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {

            try {
                $decodedProjectName = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                $decodedPracticeName = $request['subProjectName'] == '--' ? '--' : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                $paProject = Helpers::projectName($decodedProjectName);
                $decodedClientName = $paProject ? $paProject->project_name : null;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $datasModelClass =  "App\\Models\\" . $modelName.'Datas';
                $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                $checkedValues = json_decode($request->input('checkedRowValues'), true);
                $columns = Schema::getColumnListing($table_name);
                $possibleColumns = ['ar_notes', 'notes', 'remarks', 'comments'];
                $columnToUpdate = null;
                foreach ($possibleColumns as $column) {
                    if (in_array($column, $columns)) {
                        $columnToUpdate = $column;
                        break; // Stop at the first found column
                    }
                }
                if($request['selectedRecords'] == "none") {
                   if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                        foreach($checkedValues as $data) {
                            $existingRecord = $modelClass::where('id',$data['value'])->where('chart_status','CE_Pending')->first();
                            $updateData = ['chart_status' => $request['dropdownValue']];                            
                            if ($columnToUpdate) {
                                $updateData[$columnToUpdate] = NULL;
                            } 
                            $existingRecord->update($updateData);
                            $datasExistingRecord = $datasModelClass::where('parent_id',$data['value'])->where('chart_status','CE_Pending')->first();
                            if ($datasExistingRecord) {
                                $datasExistingRecord->forceDelete();
                            }
                        }
                    } else {
                        foreach($checkedValues as $data) {
                            $existingRecord = $modelClass::where('id',$data['value'])->where('CE_emp_id',$loginEmpId)->where('chart_status','CE_Pending')->first();
                            $updateData = ['chart_status' =>  $request['dropdownValue']];                            
                            if ($columnToUpdate) {
                                $updateData[$columnToUpdate] = NULL;
                            }  
                            $existingRecord->update($updateData);
                            $datasExistingRecord = $datasModelClass::where('parent_id',$data['value'])->where('CE_emp_id',$loginEmpId)->where('chart_status','CE_Pending')->first();
                            if ($datasExistingRecord) {
                                $datasExistingRecord->forceDelete();
                            }
                        }
                    }   
                } else {
                    $query = $modelClass::query();
                    $dataQuery = $datasModelClass::query();
                    $searchData = []; 
                    foreach ($request->except('_token', 'checkedRowValues', 'clientName','subProjectName','selectedRecords') as $key => $value) {
                        $searchData[$key] = $value;
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  
                            $dataQuery->where($key, $value);  
                        } elseif ($this->isDate($value)) {  
                            $query->whereDate($key, '=', $value);  
                            $dataQuery->whereDate($key, '=', $value);  
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); 
                            $dataQuery->where($key, $value);  
                        } else {
                            if($value != null) {
                            $query->where($key, 'like', '%' . $value . '%'); 
                            $dataQuery->where($key, 'like', '%' . $value . '%'); 
                            }
                        }
                    }
                   if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)) {
                            $updateData = ['chart_status' =>  $request['dropdownValue']];                            
                            if ($columnToUpdate) {
                                $updateData[$columnToUpdate] = NULL;
                            }  
                            $query->where('chart_status', 'CE_Pending')->update($updateData);
                            $datasExistingRecord = $dataQuery->where('chart_status','CE_Pending')->first();
                            if ($datasExistingRecord) {
                                $datasExistingRecord->forceDelete();
                            }
                    } else {
                        $updateData = ['chart_status' => $request['dropdownValue']];                            
                        if ($columnToUpdate) {
                            $updateData[$columnToUpdate] = NULL;
                        }  
                        $query->where('CE_emp_id',$loginEmpId)->where('chart_status','CE_Pending')->update($updateData);
                        $datasExistingRecord = $dataQuery->where('CE_emp_id',$loginEmpId)->where('chart_status','CE_Pending')->first();
                        if ($datasExistingRecord) {
                            $datasExistingRecord->forceDelete();
                        }
                    }
                }
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    // public function getQuestion(Request $request){
    //     $statusCode = $request->status_code;
    //       $questions = [];
    //     // return multiple questions as array
    //     $questions = \App\Http\Helper\Admin\Helpers::getQuestion($statusCode); 
    //     //return $questions;
    //     return response()->json([
    //         'questions' => $questions ?? []
    //     ]);
    // }
    
    // public function getQuestion(Request $request) {
    //     $statusCode = $request->status_code;
    //     $decodedProjectId = Helpers::encodeAndDecodeID($request->project_id ?? '', 'decode');
    //     $decodedSubPrjId = $request->sub_project_id == '--' ? '--' : Helpers::encodeAndDecodeID($request->sub_project_id ?? '', 'decode');

    //     // 🔹 Check if already answered
    //     // $existingAnswers = QuestionAndAnswer::where('project_id', $decodedProjectId)
    //     //     ->where('sub_project_id', $decodedSubPrjId)
    //     //     ->where('ar_status_id', $statusCode)
    //     //     ->get();
    //     $existingAnswersExists = QuestionAndAnswer::where('project_id', $decodedProjectId)
    //         ->where('sub_project_id', $decodedSubPrjId)
    //         ->where('ar_status_id', $statusCode)
    //         ->get();

    //     if ($existingAnswersExists->isNotEmpty()) {
    //         $existingAnswers = \DB::table('questions')
    //             ->leftJoin('question_and_answers', function($join) use ($decodedProjectId, $decodedSubPrjId, $statusCode) {
    //                 $join->on('questions.id', '=', 'question_and_answers.question_id')
    //                     ->where('question_and_answers.project_id', $decodedProjectId)
    //                     ->where('question_and_answers.sub_project_id', $decodedSubPrjId)
    //                     ->where('question_and_answers.ar_status_id', $statusCode);
    //             })
    //             ->where('questions.ar_status_id', $statusCode)
    //             ->select(
    //                 'questions.id as question_id',
    //                 'questions.question_text',
    //                 'question_and_answers.answer_text',
    //                 'question_and_answers.project_id',
    //                 'question_and_answers.sub_project_id',
    //                 'question_and_answers.record_id'
    //             )
    //             ->get();
    //         return response()->json([
    //             'already_answered' => true,
    //             'status_code' => $statusCode,
    //             'saved' => $existingAnswers->map(function($item,$statusCode) {
    //                 $questionText = $item->question_id != NULL ?  Helpers::getQuestionById($item->question_id)->question_text : '--';   
    //                 return [
    //                     'question_id' => $item->question_id,
    //                     'question' => $questionText, // if relation exists
    //                     'answer'   => $item->answer_text,
    //                 ];
    //             }),
    //         ]);
    //     }

    //     // 🔹 If no answers, get fresh questions
    //     $questions = \App\Http\Helper\Admin\Helpers::getQuestionWithSubQeustion($statusCode);

    //     return response()->json([
    //         'already_answered' => false,
    //         'questions' => $questions ?? []
    //     ]);
    // }
    // public function saveQuestionAnswer(Request $request) {
    //     $statusCode = $request->status_code;
    //     $questions = $request->questions; // array of [id, text, answer]
    //     $decodedProjectId = Helpers::encodeAndDecodeID($request->project_id, 'decode');
    //     $decodedSubPrjId = $request->sub_project_id == '--' ? '--' :Helpers::encodeAndDecodeID($request->sub_project_id, 'decode');
    //     $saved = [];

    //     foreach ($questions as $q) {
    //         $saved[] = [
    //             'question_id' => $q['id'],
    //             'question' => $q['text'],
    //             'answer'   => $q['answer'],
    //             'question_id' => $q['id'],
    //         ];
    //         $answer = [
    //             'project_id' => $decodedProjectId,
    //             'sub_project_id' => $decodedSubPrjId,
    //             'record_id' => $request->record_id,
    //             'ar_status_id' => $statusCode,
    //             'question_id' => $q['id'],
    //             'answer_text'   => $q['answer'],           
    //         ];
    //         $existing = QuestionAndAnswer::where('project_id', $decodedProjectId)->where('sub_project_id', $decodedSubPrjId)->where('ar_status_id', $statusCode)->where('question_id', $q['id'])->first();
    //         if ($existing) {
    //             $existing->update($answer);
    //         } else {
    //             QuestionAndAnswer::create($answer); 
    //         }
        

        
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'status_code' => $statusCode,
    //         'saved' => $saved
    //     ]);
    // }
    // public function fetchSubQuestion(Request $request)
    // {
    //     $questionId = $request->input('question_id');

    //     // Example: fetch sub question from DB (adjust according to your schema)
    //     $subQuestion = \DB::table('sub_questions')
    //         ->where('question_id', $questionId)
    //         ->value('sub_question_text');

    //     if ($subQuestion) {
    //         return response()->json([
    //             'success' => true,
    //             'sub_question_text' => $subQuestion
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => 'No sub question found.'
    //     ], 404);
    // }
       public function downloadProjects(Request $request)
    {
        try {
            $project_shortname = project::where('project_id', $request->project_id)
                ->value('project_name');

            $sub_project_shortname = Subproject::where('sub_project_id', $request->sub_project_id)
                ->value('sub_project_name');

            $current_date = date('mdY');

            $file_name = Str::slug(($project_shortname . '_' . $sub_project_shortname), '_') . '-' . $current_date;

            $data_columns = DB::table('inventory_upload_configuration')
                ->where('project_id', $request->project_id)
                ->where('sub_project_id', $request->sub_project_id)
                ->value('data_columns');

            if (!$data_columns) {
                return redirect()->back()->with('error', 'Download failed: data columns not configured.');
            }

            $labels = array_map('trim', explode(',', $data_columns));

            $export = new class($labels) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithHeadings {

                protected $headers;

                public function __construct($headers)
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

            return Excel::download(
                $export,
                $file_name . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Download failed: ' . $e->getMessage());
        }
    }

    public function clientExportAssigned(Request $request)
    {
        if (
            !Session::get('loginDetails') ||
            empty(Session::get('loginDetails')['userDetail']) ||
            empty(Session::get('loginDetails')['userDetail']['emp_id'])
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Session expired. Please log in again.',
            ], 401);
        }

        try {
            $loginDetails = Session::get('loginDetails');

            $loginEmpId = $loginDetails['userDetail']['emp_id'] ?? '';
            $empDesignation = $loginDetails['userDetail']['user_hrdetails']['current_designation'] ?? '';

            $decodedProjectId = Helpers::encodeAndDecodeID(
                $request->clientName,
                'decode'
            );

            $decodedSubProjectId = $request->subProjectName === '--'
                ? '--'
                : Helpers::encodeAndDecodeID(
                    $request->subProjectName,
                    'decode'
                );

            $project = Helpers::projectName($decodedProjectId);

            if (!$project) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid project selected.',
                ], 422);
            }

            $decodedClientName = $project->project_name;
            $exportProjectName = $project->aims_project_name ?? null;

            $subProject = $decodedSubProjectId === '--'
                ? null
                : Helpers::subProjectName(
                    $decodedProjectId,
                    $decodedSubProjectId
                );

            $decodedSubProjectName = $decodedSubProjectId === '--'
                ? 'project'
                : ($subProject->sub_project_name ?? null);

            if (!$decodedSubProjectName) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid sub-project selected.',
                ], 422);
            }

            $exportSubProjectName = $subProject
                ? ($subProject->sub_project_name ?: $subProject->sub_project_name)
                : 'project';

            $exportFileName = $exportProjectName
                ? $exportProjectName . ' _ ' . $exportSubProjectName
                : 'Resolv';

            $tableName = Str::slug(
                Str::lower($decodedClientName) . '_' .
                Str::lower($decodedSubProjectName),
                '_'
            );

            $searchFilters = $request->except([
                '_token',
                'parent',
                'child',
                'clientName',
                'subProjectName',
                'recordStatusVal',
                'resourceName',
                'chart_status',
                'page',
            ]);

            $jobId = Str::uuid()->toString();

            $payload = [
                'job_id' => $jobId,
                'report_type' => 'client',
                'table_name' => $tableName,
                'project_id' => $decodedProjectId,
                'sub_project_id' => $decodedSubProjectId === '--' ? null : $decodedSubProjectId,
                'login_emp_id' => $loginEmpId,
                'emp_designation' => $empDesignation,
                'chart_status' => $request->chart_status,
                'record_status_val' => $request->recordStatusVal,
                'resource_name' => $request->resourceName,
                'search_filters' => $searchFilters,
                'export_file_name' => $exportFileName,
                'reports_directory' => storage_path('app/reports'),
                'requested_at' => now()->toDateTimeString(),
            ];

            Cache::put(
                'quality_report_' . $jobId,
                [
                    'status' => 'processing',
                    'file' => null,
                    'message' => null,
                ],
                now()->addHours(3)
            );

            RunQualityExportJob::dispatch($payload)
                ->onQueue('assignedTabExport')
                ->delay(now()->addSeconds(2));

            return response()->json([
                'status' => true,
                'job_id' => $jobId,
            ]);
        } catch (\Throwable $e) {
            Log::error('Unable to start client export.', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Unable to start the client export.',
            ], 500);
        }
    }
    public function checkclientExportReport($jobId)
    {
        try {
            $report = Cache::get('quality_report_' . $jobId);

            if (!$report || !is_array($report)) {
                return response()->json([
                    'ready'   => false,
                    'failed'  => false,
                    'status'  => 'processing',
                    'message' => 'Report is still being generated.',
                ]);
            }

            if (($report['status'] ?? null) === 'failed') {
                return response()->json([
                    'ready'   => false,
                    'failed'  => true,
                    'status'  => 'failed',
                    'message' => $report['message']
                        ?? 'Report generation failed.',
                ]);
            }

            $filePath = $report['file'] ?? null;

            if (
                ($report['status'] ?? null) === 'completed' &&
                is_string($filePath) &&
                file_exists($filePath)
            ) {
                return response()->json([
                    'ready'  => true,
                    'failed' => false,
                    'status' => 'completed',
                    'file'   => basename($filePath),
                ]);
            }

            return response()->json([
                'ready'   => false,
                'failed'  => false,
                'status'  => 'processing',
                'message' => 'Report is still being generated.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Quality export status check failed.', [
                'job_id'  => $jobId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'ready'   => false,
                'failed'  => true,
                'status'  => 'failed',
                'message' => 'Unable to check report status.',
            ], 500);
        }
    }
    public function downloadClientExportReport($filename)
    {
        $filename = basename($filename);

        $path = storage_path(
            'app/reports/' . $filename
        );

        Log::info('Quality report download request.', [
            'filename' => $filename,
            'path'     => $path,
            'exists'   => file_exists($path),
        ]);

        if (!file_exists($path)) {
            abort(404, 'Report file not found.');
        }

        return response()
            ->download(
                $path,
                $filename,
                [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                ]
            )
            ->deleteFileAfterSend(true);
    }



}
