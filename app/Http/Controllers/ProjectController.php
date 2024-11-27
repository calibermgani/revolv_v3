<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\project;
use GuzzleHttp\Client;
use App\Models\subproject;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProjectWorkMail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\ProcodeProjectOnHoldMail;
use App\Models\CCEmailIds;
use App\Mail\ProcodeProjectFile;
use App\Mail\ProcodeProjectInventory;
use App\Mail\ProcodeProjectError;
use App\Models\InventoryErrorLogs;
use App\Http\Helper\Admin\Helpers as Helpers;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeLogin;
use App\Mail\ProjectHourlyMail;
class ProjectController extends Controller
{
    public function clientTableUpdate()
    {
        try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
            ];
            $client = new Client(['verify' => false]);
            $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_project_list', [
                'json' => $payload
            ]);
            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
            } else {
                return response()->json(['error' => 'API request failed'], $response->getStatusCode());
            }
            $projects = $data['project_details'];
            $subProjects = $data['practice_info'];
            $prjData = [];
            $subPrjData = [];
            foreach ($projects as $data) {
                $shortcut = $this->getProjectShortcut($data['client_name']);
                $prjData['project_id'] = $data['id'];
                $prjData['aims_project_name'] = $data['client_name'];
                $prjData['project_name'] = $shortcut;
                $prjData['added_by'] = 1;
                $prjData['status'] = $data['status'];
                $prjDetails = project::where('project_id', $data['id'])->first();
                if ($prjDetails) {
                    $prjData['project_name'] = $prjDetails['project_name']; //not updating project name shortcut
                    $prjDetails->update($prjData);
                } else {
                    project::create($prjData);
                }
            }
            subproject::truncate();
            foreach ($subProjects as $data) {
                $subPrjData['project_id'] = $data['project_id'];
                $subPrjData['sub_project_id'] = $data['sub_project_id'];
                $subPrjData['sub_project_name'] = $data['sub_project_name'];
                $subPrjData['added_by'] = 1;
                $subPrjDetails = subproject::where('project_id', $subPrjData['project_id'])->where('sub_project_id', $subPrjData['sub_project_id'])->first();
                if ($subPrjDetails) {
                    $subPrjDetails->update($subPrjData);
                } else {
                    subproject::create($subPrjData);
                }
            }
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }
    public function getProjectShortcut($projectName)
    {
        // Remove special characters and text within parentheses
        $projectName = preg_replace('/\s+/', ' ', $projectName); // Replace multiple spaces with a single space
        $projectName = preg_replace('/\s*[\(\)]\s*/', ' ', $projectName); // Remove parentheses and text within them
        $projectName = preg_replace('/[^\w\s]/', '', $projectName); // Remove non-alphanumeric characters except whitespace

        // Split the project name into words
        $words = explode(' ', $projectName);

        // Get the first character of each word
        $shortcut = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                if (count($words) > 1) {
                    $shortcut .= strtoupper($word[0]);
                } else {
                    $shortcut = $word;
                }
            }
        }

        return $shortcut;
    }
    // public function projectWorkMail()
    // {
    //     try {
    //         Log::info('Executing ProjectWorkMail logic.');
    //         $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
    //         // $toMailId = ["elanchezhian@annexmed.net", "fabian@annexmed.com", "ushashree@annexmed.com"];
    //         // $ccMailId = ["mgani@caliberfocus.com"];
    //         // $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'project work mail to mail id')->first();
    //         // $toMailId = $toMail != null ? explode(",", $toMail->cc_emails) : null;
    //         // $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'project work mail cc mail id')->first();
    //         // $ccMailId = $ccMail != null ? explode(",", $ccMail->cc_emails) : null;
    //         $toMailId = ["vijayalaxmi@caliberfocus.com"];
    //         $ccMailId = ["vijayalaxmi@caliberfocus.com"];
    //         $yesterday = Carbon::yesterday();
    //         $today = Carbon::today();
    //         if ($yesterday->isSaturday()) {
    //             $yesterday = $yesterday->subDay(1); // Friday
    //         } elseif ($yesterday->isSunday()) {
    //             $yesterday = $yesterday->subDay(2); // Friday
    //         }
    //         $mailHeader = "Resolv Utilization Report for " . $yesterday->format('m/d/Y');
    //         // $yesterDayStartDate = $yesterday->startOfDay()->toDateTimeString();
    //         // $yesterDayEndDate = $yesterday->endOfDay()->toDateTimeString();
    //         $yesterDayStartDate = $yesterday->setTime(11, 0, 0)->toDateTimeString();
    //         $yesterDayEndDate = $today->setTime(8, 0, 0)->toDateTimeString(); // 8 AM

    //         // $mailHeader = "Resolv Utilization Report for 06/07/2024";
    //         // $yesterDayStartDate = "2024-06-07 00:00:00";
    //         // $yesterDayEndDate = "2024-06-07 23:59:59";
    //         $projects = $this->getProjects();
    //         foreach ($projects as $project) {
    //             $prjName =  Helpers::projectName($project["id"]) != null ? Helpers::projectName($project["id"])->project_name : null;//dd($prjName);
    //             if ($prjName !== null) {
                  
    //                 if (count($project["subprject_name"]) > 0) {
    //                     foreach ($project["subprject_name"] as $key => $subProject) {
    //                         // $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProject)), '_');
    //                         $table_name = Str::slug((Str::lower($prjName) . '_' . Str::lower($subProject)), '_');
    //                         $modelName = Str::studly($table_name);
    //                         $modelClass = "App\\Models\\" . $modelName;
    //                         $models[] = $modelClass;
    //                         $prjoectName[] = $project["client_name"] . '-' . $subProject;
    //                         $clientIds[] = $project["id"];
    //                     }
    //                 } else {
    //                     $subProjectText = "project";
    //                     $table_name = Str::slug((Str::lower($prjName) . '_' . Str::lower($subProjectText)), '_');
    //                     $modelName = Str::studly($table_name);
    //                     $modelClass = "App\\Models\\" . $modelName;
    //                     $models[] = $modelClass;
    //                     $prjoectName[] = $project["client_name"];
    //                     $clientIds[] = $project["id"];
    //                 }
    //             }  
    //             $prjoectsPending = [];
    //             foreach ($models as $key => $model) {
    //                  if (class_exists($model)) { 
    //                     $aCount = $model::whereBetween('created_at', [$yesterDayStartDate, $yesterDayEndDate])->where('chart_status', 'CE_Assigned')->count();
    //                     $cCount = $model::whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])->where('chart_status', 'CE_Completed')->count();
    //                     $qCount = $model::whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])->where('chart_status', 'QA_Completed')->count();
    //                     // $pCount = $aCount - $cCount;
    //                     $prjoectsPending[$key]['project'] = $prjoectName[$key];
    //                     $prjoectsPending[$key]['Chats'] = $aCount;
    //                     $prjoectsPending[$key]['Coder'] = $cCount;
    //                     $prjoectsPending[$key]['QA'] = $qCount;
    //                     // $prjoectsPending[$key]['Balance'] = $pCount;
    //                     // $productionARCount = $model::whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])->where('chart_status', 'CE_Completed')->count();
    //                     $prjoectsPending[$key]['total_ar'] = $this->getProjectTotalARCount($clientIds[$key]);
    //                     $prjoectsPending[$key]['total_qa'] =$this->getProjectTotalQACount($clientIds[$key]);;
    //                 }  
    //             }    
    //        }
    //         $mailBody = $prjoectsPending;
    //         Mail::to($toMailId)->cc($ccMailId)->send(new ProjectWorkMail($mailHeader, $mailBody, $yesterday));
    //         Log::info('ProjectWorkMail executed successfully.');
    //     } catch (\Exception $e) {
    //         Log::error('Error in ProjectWorkMail: ' . $e->getMessage());
    //         Log::debug($e->getMessage());
    //     }
    // }
    public function projectWorkMail()
    {
        try {
            Log::info('Executing ProjectWorkMail logic.');
            $loginEmpId = Session::get('loginDetails')['userDetail']['emp_id'] ?? "";    
            //$toMailId = ["mgani@caliberfocus.com"];
            $toMailId = ["elanchezhian@annexmed.net", "fabian@annexmed.com", "prabu@annexmed.com","serdeen@annexmed.com","Neel@annexmed.com","Manoj.Achuthan@annexmed.com","radhika@annexmed.com","Gavin@annexmed.com","hemanathan@annexmed.net","vani@annexmed.com","devanathan@annexmed.net"];
            $ccMailId = ["mgani@caliberfocus.com","margaretmary@annexmed.net"];
            // $toMailId = ["vijayalaxmi@caliberfocus.com"];
            // $ccMailId = ["vijayalaxmi@caliberfocus.com"];
    
            // Set date ranges based on yesterday's date, skipping weekends.
            $yesterday = Carbon::yesterday();
            if ($yesterday->isSaturday()) {
                $yesterday = $yesterday->subDay(1); // Friday
            } elseif ($yesterday->isSunday()) {
                $yesterday = $yesterday->subDay(2); // Friday
            }
    
            $today = Carbon::today();
            $mailHeader = "Resolv Utilization Report for " . $yesterday->format('m/d/Y')." - Trail";
            $yesterDayStartDate = $yesterday->setTime(17, 0, 0)->toDateTimeString();
            $yesterDayEndDate = $today->setTime(8, 0, 0)->toDateTimeString();

            $yesterday5PM = Carbon::yesterday()->setTime(17, 0); // Yesterday at 5:00 PM
            $tomorrow9AM = Carbon::tomorrow()->setTime(9, 0); 
    
            $projects = collect($this->getProjects());
    
            // Prepare batch data collection.
            $prjoectsPending = $projects->flatMap(function ($project) use ($yesterDayStartDate, $yesterDayEndDate,$today,$yesterday) {
                $projectData = [];
                $prjName = Helpers::projectName($project['id'])->project_name ?? null;
    
                if ($prjName !== null) {
                    $subProjects = count($project['subprject_name']) > 0 ? $project['subprject_name'] : ['project'];
    
                    foreach ($subProjects as $subProject) {
                        $tableName = Str::slug(Str::lower($prjName . '_' . $subProject), '_');
                        $modelClass = "App\\Models\\" . Str::studly($tableName);
    
                        if (class_exists($modelClass)) {
                            $aCount = $modelClass::whereBetween('created_at', [$yesterDayStartDate, $yesterDayEndDate])
                                        ->where('chart_status', 'CE_Assigned')->count();
                            $cCount = $modelClass::whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                        ->where('chart_status', 'CE_Completed')->count();
                            $qCount = $modelClass::whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                        ->where('chart_status', 'QA_Completed')->count();
                            $productionARCount =  $modelClass::where(function ($query) use ($yesterDayStartDate, $yesterDayEndDate, $yesterday, $today) {
                                $query->whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                      ->whereIn('chart_status', [
                                          'CE_Inprocess', 
                                          'CE_Pending', 
                                          'CE_Completed', 
                                          'CE_Clarification', 
                                          'CE_Hold', 
                                          'AR_non_workable', 
                                          'Revoke'
                                      ]);
                                 $query->orWhere(function ($subQuery) use ($yesterday, $today) {
                                    $subQuery->where('chart_status', 'CE_Completed')
                                             ->whereDate('coder_work_date', $yesterday)
                                             ->orWhereDate('coder_work_date', $today);
                                });
                            })
                            ->groupBy('CE_emp_id')
                            ->havingRaw('MAX(updated_at) BETWEEN ? AND ?', [$yesterDayStartDate, $yesterDayEndDate])  // Adding the HAVING clause for the updated_at condition
                            ->select('CE_emp_id') 
                            ->get() 
                            ->count(); 
                            // $productionQACount = $modelClass::whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                            // ->whereIn('chart_status', ['QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])->whereNotNull('QA_emp_id')
                            // ->groupBy('QA_emp_id')->count();
                            $productionQACount = $modelClass::whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                ->whereIn('chart_status', ['QA_Assigned', 'QA_Inprocess', 'QA_Pending', 'QA_Completed', 'QA_Clarification', 'QA_Hold'])
                                ->whereNotNull('QA_emp_id')
                                ->distinct('QA_emp_id')
                                ->count('QA_emp_id'); 

                            $totalARDetails = $this->getProjectTotalARCount($project['id']);
                             $totalQADetails = $this->getProjectTotalQACount($project['id']);
                            $loggedResolvAR = 0;$loggedResolvQA=0;

                           // Log::error('Total Users: ' . print_r($totalARDetails['totalArList'], true));

                            foreach($totalARDetails['totalArList'] as $key => $arList){
                               // $yesterday5PM = "2024-11-07 17:00:00"; //Carbon::yesterday()->setTime(17, 0); // Yesterday at 5:00 PM
                                //$tomorrow9AM = "2024-11-08 09:00:00"; //Carbon::tomorrow()->setTime(9, 0); 
                                $yesterday5PM = Carbon::yesterday()->setTime(17, 0); 
                                $tomorrow9AM =  Carbon::tomorrow()->setTime(9, 0);
                                //$loggedResolvAR += EmployeeLogin::where('user_id',$arList['assigned_people'])->whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])->count();
                                $loggedResolvAR +=  EmployeeLogin::where('user_id', $arList['assigned_people'])
                                                    ->whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                                    ->distinct('user_id')
                                                    ->count();
                                //Log::error('Total Users Time'.$tomorrow9AM);
                           


                            }
                            foreach($totalQADetails['totalQAList'] as $key => $qaList){
                                $loggedResolvQA += EmployeeLogin::where('user_id',$qaList['assigned_people'])
                                ->whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                ->distinct('user_id')
                                ->count();
                            }
                            $projectData[] = [
                                'project' => $project['client_name'] . '-' . $subProject,
                                'Chats' => $aCount,
                                'Coder' => $cCount,
                                'QA' => $qCount,
                                'total_ar' => $totalARDetails['totalArCount'],
                                'total_qa' => $totalQADetails['totalQACount'],
                                'prodcution_ar' => $productionARCount,
                                'prodcution_qa' => $productionQACount,
                                'logged_resolv_ar' => $loggedResolvAR,
                                'logged_resolv_qa' => $loggedResolvQA,
                            ];
                        }
                    }
                }
    
                return $projectData;
            });
            $mailBody = $prjoectsPending->toArray();
            Mail::to($toMailId)->cc($ccMailId)->send(new ProjectWorkMail($mailHeader, $mailBody, $yesterday));
    
            Log::info('ProjectWorkMail executed successfully.');
        } catch (\Exception $e) {
            Log::error('Error in ProjectWorkMail: ' . $e->getMessage());
            Log::debug($e->getMessage());
        }
    }
    
    public function getProjects()
    {
        try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
            ];
            $client = new Client(['verify' => false]);
            $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_all_clients', [
                'json' => $payload,
            ]);
            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
            } else {
                return response()->json(['error' => 'API request failed'], $response->getStatusCode());
            }
            return $data['clientList'];
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    public function procodeProjectOnHoldMail()
    {
        try {
            Log::info('Executing resolvProjectOnHoldMail logic.');
            $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
            $client = new Client(['verify' => false]);
            // $toMailId = ["vijayalaxmi@caliberfocus.com"];
            // $ccMailId = ["mgani@caliberfocus.com"];
            $mailHeader = "Resolv - Project Hold Charges reminder";
            $projects = $this->getProjects();
            foreach ($projects as $project) {
                $prjName =  Helpers::projectName($project["id"]) != null ? Helpers::projectName($project["id"])->project_name : null;//dd($prjName);
                    if ($prjName !== null) {
                        if (count($project["subprject_name"]) > 0) {
                            foreach ($project["subprject_name"] as $key => $subProject) {
                                // $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProject)), '_');
                                $table_name = Str::slug((Str::lower($prjName) . '_' . Str::lower($subProject)), '_');
                                $modelName = Str::studly($table_name);
                                $modelClass = "App\\Models\\" . $modelName;
                                $models[] = $modelClass;
                                $prjoectName[] = $project["client_name"] . '-' . $subProject;
                                $projectId[] = $project["id"];
                            }
                        } else {
                            $subProjectText = "project";
                            $table_name = Str::slug((Str::lower($prjName) . '_' . Str::lower($subProjectText)), '_');
                            $modelName = Str::studly($table_name);
                            $modelClass = "App\\Models\\" . $modelName;
                            $models[] = $modelClass;
                            $prjoectName[] = $project["client_name"];
                            $projectId[] = $project["id"];
                        }
                    }
            }
            $procodeProjectsHolding = $projectsIds = [];
            foreach ($models as $key => $model) {
                if (class_exists($model)) {
                    $hCount = $model::where('chart_status', 'CE_Hold')->count();
                    if ($hCount > 0) {
                        $procodeProjectsHolding[$projectId[$key]]['project'] = $prjoectName[$key];
                        $procodeProjectsHolding[$projectId[$key]]['Hold'] = $hCount;
                        // $procodeProjectsHolding[$key]['project_id'] = $projectId[$key];
                        $projectsIds[] = $projectId[$key];
                    }
                }
            }
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $projectsIds
            ];
            if (!empty($procodeProjectsHolding)) {
                //  $response = $client->request('POST',  config("constants.PRO_CODE_URL") . '/api/v1_users/get_details_above_tl_level', [
                $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_details_above_tl_level', [
                    'json' => $payload
                ]);
                if ($response->getStatusCode() == 200) {
                    $apiData = json_decode($response->getBody(), true);
                } else {
                    return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                }
                $projectsHolding = $apiData['people_details'];
                foreach ($projectsHolding as $data) {
                    $clientIds = $data['client_ids'];
                    $mailBody = $procodeProjectsHolding;
                    if ($data["email_id"] != null) {
                        $toMailId = $data["email_id"];
                        $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'project hold records')->first();
                        $ccMailId = explode(",", $ccMail->cc_emails);
                        Mail::to($toMailId)->cc($ccMailId)->send(new ProcodeProjectOnHoldMail($mailHeader, $clientIds, $mailBody));
                        Log::info('Resolv Project On Hold Mail executed successfully.');
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in ProjectOnHoldMail: ' . $e->getMessage());
            Log::debug($e->getMessage());
        }
    }

    public function projectFileNotInFolder(Request $request)
    {
        $project_information = $request->all();
        $current_time = Carbon::now();
        $today = Carbon::today();
        if (($current_time->hour >= 17 || $current_time->hour < 8) && $today->isSaturday() ==  false  && $today->isSunday() ==  false ) {
            $fileStatus = "The " . $project_information['project_name'] . " inventory is not in the specified location. Could you please check and place the inventory files for today as soon as possible. This will help avoid delays in production.";
            $mailHeader = $project_information['project_name'] . " File not in Specific folder";
            $client = new Client(['verify' => false]);
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $project_information['project_id']
            ];
            $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_emails_above_tl_level', [
                'json' => $payload
            ]);
            if ($response->getStatusCode() == 200) {
                $apiData = json_decode($response->getBody(), true);
            } else {
                return response()->json(['error' => 'API request failed'], $response->getStatusCode());
            }
            // $toMailId = $apiData['people_email'];
            $toMailId = "resolvsupport@annexmed.net";
            $reportingPerson = $apiData['reprting_person'];
            $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'project file not there')->first();
            $ccMailId = explode(",", $ccMail->cc_emails);
            // $toMailId = ["mgani@caliberfocus.com"];
            if (isset($toMailId) && !empty($toMailId)) {
                Mail::to($toMailId)->cc($ccMailId)->send(new ProcodeProjectFile($mailHeader, $fileStatus));
            }
            Log::info('ProjectFileNotThere executed successfully.');
            return response()->json([
                "message" => "file is not there"
            ]);
        }
    }

    public function procodeProjectInventoryRecords()
    {
        try {
            Log::info('Execute the Resolv project current date records check and send mail after 12 PM');
            $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
            $client = new Client(['verify' => false]);
            $currentDate = Carbon::now()->format('Y-m-d');
            // $toMailId = ["elanchezhian@annexmed.net", "fabian@annexmed.com", "ushashree@annexmed.com"];
            $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'procode project inventory to mail')->first();
            $toMailId = explode(",", $toMail->cc_emails);
            $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'procode project inventory cc mail')->first();
            $ccMailId = explode(",", $ccMail->cc_emails);
            $mailDate =  Carbon::now()->format('m/d/Y');
            $mailHeader = "Resolv - Inventory Upload Successful - " . $mailDate;
            $projects = $this->getProjects();
            foreach ($projects as $project) {
                if (count($project["subprject_name"]) > 0) {
                    foreach ($project["subprject_name"] as $key => $subProject) {
                        $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProject)), '_');
                        $modelName = Str::studly($table_name);
                        $modelClass = "App\\Models\\" . $modelName;
                        $models[] = $modelClass;
                        $prjoectName[] = $project["client_name"] . '-' . $subProject;
                        $projectId[] = $project["id"];
                    }
                } else {
                    $subProjectText = "project";
                    $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProjectText)), '_');
                    $modelName = Str::studly($table_name);
                    $modelClass = "App\\Models\\" . $modelName;
                    $models[] = $modelClass;
                    $prjoectName[] = $project["client_name"];
                    $projectId[] = $project["id"];
                }
            }
            $procodeProjectsCurrent = $projectsIds = [];
            foreach ($models as $key => $model) {
                if (class_exists($model)) {
                    $currentCount = $model::where('invoke_date', $currentDate)->where('chart_status', 'CE_Assigned')->count();
                    if ($currentCount > 0) {
                        $procodeProjectsCurrent[$projectId[$key]]['project'] = $prjoectName[$key];
                        $procodeProjectsCurrent[$projectId[$key]]['currentCount'] = $currentCount;
                        $projectsIds[] = $projectId[$key];
                    }
                }
            }
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $projectsIds
            ];
            if (!empty($procodeProjectsCurrent)) {
                $mailBody = $procodeProjectsCurrent;
                $current_time = Carbon::now();
                if ($current_time->hour >= 12) {
                    Mail::to($toMailId)->cc($ccMailId)->send(new ProcodeProjectInventory($mailHeader, $mailBody));
                    Log::info('Resolv Project Inventory Mail executed successfully.');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in Project Inventory Mail: ' . $e->getMessage());
            Log::debug($e->getMessage());
        }
    }

    public function projectErrorMail(Request $request)
    {
        $project_information = $request->all();
        if ($project_information['project_id']) {
            $fileStatus = "The " . $project_information['project_name'] . " Containing below errors";
            $mailHeader = $project_information['project_name'] . " Error Description";
            $error_description = $project_information['error_description'];
            $project_information["error_date"] = now()->format('Y-m-d H:i:s');
            $current_time = Carbon::now();
            $today = Carbon::today();
            if (($current_time->hour >= 17 || $current_time->hour < 8) && $today->isSaturday() ==  false  && $today->isSunday() ==  false ) {
                InventoryErrorLogs::create($project_information);
                // $toMailId = ["vijayalaxmi@caliberfocus.com"];
                // $ccMailId = ["mgani@caliberfocus.com"];
                $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'project error mail to mail id')->first();
                $toMailId = explode(",", $toMail->cc_emails);
                $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'project error mail cc mail id')->first();
                $ccMailId = explode(",", $ccMail->cc_emails);
                if (isset($toMailId) && !empty($toMailId)) {
                    Mail::to($toMailId)->cc($ccMailId)->send(new ProcodeProjectError($mailHeader, $fileStatus, $error_description));
                }
            
            Log::info('Project Error Mail Send Successfully.');
            }
        }
        Log::info('Project Error Details: ' . print_r($project_information, true));
        return response()->json(["message" => "Error Mail Sent by Resolv"]);
    }
    public function getProjectTotalARCount($project_id)
    {
        try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $project_id,
            ]; 
            $data = retry(3, function () use ($payload) {
                $client = new Client(['verify' => false]);
                $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_total_ar_list', [
                    'json' => $payload,
                ]);
                
                if ($response->getStatusCode() == 200) {
                    $responseData = json_decode($response->getBody(), true);
    
                    if (isset($responseData['totalArCount'])) {
                        return $responseData;
                    } else {
                        throw new \Exception('totalArCount not found in the API response');
                    }
                } elseif ($response->getStatusCode() == 429) {
                    $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                    sleep((int)$retryAfter);
                    throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                } else {
                    throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                }
            }, 4000);
            return $data;
            // if (isset($data['totalArCount'])) {
            //     return $data;
            // } else {
            //     Log::error('totalArCount not found in API response.');
            //     return null;
            // }
        } catch (\Exception $e) {
            Log::error('Error in getProjectTotalARCount: ' . $e->getMessage());
            return null;
        }
    }

    public function getProjectTotalQACount($project_id)
    {
        try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $project_id,
            ];            
            // Retry 3 times, with a 2-second delay between each attempt
            $data = retry(3, function () use ($payload) {
                $client = new Client(['verify' => false]);
                $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_total_qa_list', [
                    'json' => $payload,
                ]);
                
                if ($response->getStatusCode() == 200) {
                    $responseData = json_decode($response->getBody(), true);
    
                    if (isset($responseData['totalQACount'])) {
                        return $responseData;
                    } else {
                        throw new \Exception('totalQACount not found in the API response');
                    }
                } elseif ($response->getStatusCode() == 429) {
                    $retryAfter = $response->getHeader('Retry-After')[0] ?? 2; // Default wait time 2 seconds
                    sleep((int)$retryAfter);
                    throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                } else {
                    throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                }
            }, 4000);
            
            return $data;
            // if (isset($data['totalQACount'])) {
            //     return $data['totalQACount'];
            // } else {
            //     Log::error('totalQACount not found in API response.');
            //     return null;
            // }
        } catch (\Exception $e) {
            Log::error('Error in getProjectTotalQACount: ' . $e->getMessage());
            return null;
        }
    }
    // public function projectHourlyMail()
    // {
    //     try {
    //         Log::info('Executing Project Hourly Mail logic.'); 
    //         $toMailId = ["vijayalaxmi@caliberfocus.com"];
    //         $ccMailId = ["vijayalaxmi@caliberfocus.com"];
    //         $mailHeader = "Resolv Project Hourly Report";        
    //         $projects = collect($this->getProjects());
    //         $startHour = 9; // 9 AM
    //         $endHour = 5;   // 5 AM (next day)
            
    //         // Generate time slots array
    //         $timeSlots = [];
    //         $startDate = Carbon::today(); // Get today's date
            
    //         for ($hour = $startHour; $hour <= $endHour + 24; $hour++) {
    //             $currentHour = $hour % 24; // Wrap around after 23
    //             $currentDate = $startDate->copy()->addDays(intval($hour / 24)); // Adjust date for next day
    //             $start = Carbon::createFromTime($currentHour, 0, 0, $currentDate->timezone)
    //                           ->setDate($currentDate->year, $currentDate->month, $currentDate->day);
    //             $end = $start->copy()->addHour(); // End is 1 hour after start
            
    //             $timeSlots[] = [
    //                 'start' => $start,
    //                 'end' => $end,
    //                 'header' => $start->format('m/d/Y h:i A') . ' to ' . $end->format('m/d/Y h:i A'),
    //             ];
    //         }
            
    //         // Determine current and previous slots
    //         $currentTime = Carbon::now();
            
    //         // Find current slot
    //         $currentSlotIndex = collect($timeSlots)->search(function ($slot) use ($currentTime) {
    //             return $currentTime->between($slot['start'], $slot['end']);
    //         });
            
    //         // Get current slot and previous slot (if available)
    //         $currentSlot = $timeSlots[$currentSlotIndex] ?? null;
    //         $previousSlot = $timeSlots[$currentSlotIndex - 1] ?? null;
            
    //         // Collect data for both slots
    //         $slotsToProcess = collect([$previousSlot, $currentSlot])->filter();
            
    //         // Initialize headers and bodies
    //         $headers = [];
    //         $mailBody = [];
            
    //         // Fetch project data for each slot
    //         foreach ($slotsToProcess as $slot) {
    //             $headers[] = $slot['header'];
    //             $startDate = $slot['start'];
    //             $endDate = $slot['end'];
            
    //             $slotData = $projects->flatMap(function ($project) use ($startDate, $endDate) {
    //                 $projectData = [];
    //                 $prjName = Helpers::projectName($project['id'])->project_name ?? null;
            
    //                 if ($prjName !== null) {
    //                     $subProjects = count($project['subprject_name']) > 0 ? $project['subprject_name'] : ['project'];
            
    //                     foreach ($subProjects as $subProject) {
    //                         $tableName = Str::slug(Str::lower($prjName . '_' . $subProject), '_');
    //                         $modelClass = "App\\Models\\" . Str::studly($tableName);
            
    //                         if (class_exists($modelClass)) {
    //                             $hourlyCount = $modelClass::whereBetween('updated_at', [$startDate, $endDate])
    //                                         ->where('chart_status', 'CE_Completed')->count();
            
    //                             $projectData[] = [
    //                                 'project' => $project['client_name'] . '-' . $subProject,
    //                                 'hourlyCount' => $hourlyCount
    //                             ];
    //                         }
    //                     }
    //                 }            
    //                 return $projectData;
    //             });            
    //             $mailBody = $slotData->toArray();
    //         }
    //         $today=carbon::now();
    //         Mail::to($toMailId)->cc($ccMailId)->send(new ProjectHourlyMail($mailHeader, $mailBody, $headers, $today));

    //         Log::info('ProjectHourlyMail executed successfully.');
    //     } catch (\Exception $e) {
    //         Log::error('Error in ProjectHourlyMail: ' . $e->getMessage());
    //         Log::debug($e->getMessage());
    //     }
    // }
    // public function projectHourlyMail()
    // {
    //     try {
    //         Log::info('Executing Project Hourly Mail logic.'); 
    //         $toMailId = ["vijayalaxmi@caliberfocus.com"];
    //         $ccMailId = ["mgani@caliberfocus.com"];
    //         $mailHeader = "Resolv Project Hourly Report";        
    //         $projects = collect($this->getProjects());
    //         $startHour = 17; // 17 PM
    //         $endHour = 5;   // 5 AM (next day)
            
    //         // Generate time slots array
    //         $timeSlots = [];
    //         $startDate = Carbon::today(); // Get today's date
            
    //         for ($hour = $startHour; $hour <= $endHour + 24; $hour++) {
    //             $currentHour = $hour % 24; // Wrap around after 23
    //             $currentDate = $startDate->copy()->addDays(intval($hour / 24)); // Adjust date for next day
    //             $start = Carbon::createFromTime($currentHour, 0, 0, $currentDate->timezone)
    //                           ->setDate($currentDate->year, $currentDate->month, $currentDate->day);
    //             $end = $start->copy()->addHour(); // End is 1 hour after start
            
    //             $timeSlots[] = [
    //                 'start' => $start,
    //                 'end' => $end,
    //                 'header' => $start->format('m/d/Y h:i A') . ' to ' . $end->format('m/d/Y h:i A'),
    //             ];
    //         }
            
    //         // Determine the current time and filter relevant slots
    //         $currentTime = Carbon::now();
    
    //         // Filter slots from 9 AM to the current time
    //         $slotsToProcess = collect($timeSlots)->filter(function ($slot) use ($currentTime) {
    //             return $slot['start']->greaterThanOrEqualTo(Carbon::today()->setHour(9))
    //                 && $slot['end']->lessThanOrEqualTo($currentTime);
    //         });
    
    //         // Initialize headers and bodies
    //         $headers = [];
    //         $mailBody = [];
    
    //         // Fetch project data for each slot
    //         foreach ($slotsToProcess as $slot) {
    //             $headers[] = $slot['header'];
    //             $startDate = $slot['start'];
    //             $endDate = $slot['end'];
            
    //             $slotData = $projects->flatMap(function ($project) use ($startDate, $endDate) {
    //                 $projectData = [];
    //                 $prjName = Helpers::projectName($project['id'])->project_name ?? null;
            
    //                 if ($prjName !== null) {
    //                     $subProjects = count($project['subprject_name']) > 0 ? $project['subprject_name'] : ['project'];
            
    //                     foreach ($subProjects as $subProject) {
    //                         $tableName = Str::slug(Str::lower($prjName . '_' . $subProject), '_');
    //                         $modelClass = "App\\Models\\" . Str::studly($tableName);
            
    //                         if (class_exists($modelClass)) {
    //                             $hourlyCount = $modelClass::whereBetween('updated_at', [$startDate, $endDate])
    //                                         ->where('chart_status', 'CE_Completed')->count();
            
    //                             $projectData[] = [
    //                                 'project' => $project['client_name'] . '-' . $subProject,
    //                                 'hourlyCount' => $hourlyCount
    //                             ];
    //                         }
    //                     }
    //                 }            
    //                 return $projectData;
    //             });
            
    //             $mailBody = array_merge($mailBody, $slotData->toArray());
    //         }
    //         $today=carbon::now();
    //         // Send mail
    //         Mail::to($toMailId)->cc($ccMailId)->send(new ProjectHourlyMail($mailHeader, $mailBody, $headers,$today));
    //         Log::info('ProjectHourlyMail executed successfully.');
    //     } catch (\Exception $e) {
    //         Log::error('Error in ProjectHourlyMail: ' . $e->getMessage());
    //         Log::debug($e->getMessage());
    //     }
    // }
       
//     public function projectHourlyMail()
// {
//     try {
//         Log::info('Executing Project Hourly Mail logic.');

//         $toMailId = ["vijayalaxmi@caliberfocus.com"];
//         $ccMailId = ["mgani@caliberfocus.com"];
//         $mailHeader = "Resolv Project Hourly Report";
//         $projects = collect($this->getProjects());
//         $startHour = 17; // Start at 5 PM
//         $endHour = 5;   // End at 5AM next day

//         // Current time
//         $currentTime = Carbon::now();
//         Log::info("Current time: {$currentTime}");

//         // Determine start time dynamically
//         $startTime = $currentTime->hour < $startHour
//             ? Carbon::yesterday()->setHour($startHour)->setMinute(0)->setSecond(0)
//             : Carbon::today()->setHour($startHour)->setMinute(0)->setSecond(0);
//         Log::info("Calculated start time: {$startTime}");

//         // Generate time slots
//         $timeSlots = [];
//         $startDate = $startTime->copy(); // Use the calculated start time
//         $endDate = Carbon::today()->addDay()->setHour($endHour)->setMinute(0)->setSecond(0); // End at 12 PM next day

//         Log::info("Today's date: {$startDate}");
//         Log::info("End date: {$endDate}");

//         while ($startDate->lessThan($endDate)) {
//             $nextHour = $startDate->copy()->addHour();
//             $timeSlots[] = [
//                 'start' => $startDate->copy(),
//                 'end' => $nextHour,
//                 'header' => $startDate->format('m/d/Y h:i A') . ' to ' . $nextHour->format('m/d/Y h:i A'),
//             ];
//             Log::info("Time slot added: {$startDate} to {$nextHour}");
//             $startDate = $nextHour;
//         }

//         Log::info("Generated time slots: ", $timeSlots);

//         // Initialize headers and mail body
//         $headers = collect($timeSlots)->pluck('header')->toArray(); // Extract headers
//         $mailBody = [];

//         // Process each project
//         foreach ($projects as $project) {
//             $prjName = Helpers::projectName($project['id'])->project_name ?? null;
//             if ($prjName === null) {
//                 Log::warning("Project name is null for project ID {$project['id']}");
//                 continue;
//             }

//             $subProjects = count($project['subprject_name']) > 0 ? $project['subprject_name'] : ['project'];
//             foreach ($subProjects as $subProject) {
//                 $tableName = Str::slug(Str::lower($prjName . '_' . $subProject), '_');
//                 $modelClass = "App\\Models\\" . Str::studly($tableName);

//                 if (!class_exists($modelClass)) {
//                     Log::warning("Model class does not exist: {$modelClass}");
//                     continue;
//                 }

//                 $hourlyCounts = [];
//                 foreach ($timeSlots as $slot) {
//                     $startDate = $slot['start'];
//                     $endDate = $slot['end'];

//                     // Query hourly count for the specific time slot
//                     $hourlyCount = $modelClass::whereBetween('updated_at', [$startDate, $endDate])
//                         ->where('chart_status', 'CE_Completed')
//                         ->count();

//                     Log::info("Hourly count for {$tableName} from {$startDate} to {$endDate}: {$hourlyCount}");

//                     $hourlyCounts[] = $hourlyCount; // Add to the array for this project
//                 }

//                 // Add project data to the mail body
//                 $mailBody[] = [
//                     'project' => $project['client_name'] . '-' . $subProject,
//                     'hourlyCount' => $hourlyCounts, // Full array of counts for all slots
//                 ];
//             }
//         }

//         Log::info("Final mail body: ", $mailBody);

//         $today = Carbon::now();
//         // Send mail
//         Mail::to($toMailId)->cc($ccMailId)->send(new ProjectHourlyMail($mailHeader, $mailBody, $headers, $today));
//         Log::info('ProjectHourlyMail executed successfully.');
//     } catch (\Exception $e) {
//         Log::error('Error in ProjectHourlyMail: ' . $e->getMessage());
//         Log::debug($e->getTraceAsString());
//     }
// }
    public function projectHourlyMail()
    {
        try {
            Log::info('Executing Project Hourly Mail logic.');

            $toMailId = ["elanchezhian@annexmed.net", "fabian@annexmed.com", "prabu@annexmed.com","serdeen@annexmed.com","Neel@annexmed.com","Manoj.Achuthan@annexmed.com","radhika@annexmed.com","Gavin@annexmed.com","hemanathan@annexmed.net","vani@annexmed.com","devanathan@annexmed.net"];
            $ccMailId = ["mgani@caliberfocus.com","margaretmary@annexmed.net","vijayalaxmi@caliberfocus.com"];
            // $toMailId = ["vijayalaxmi@caliberfocus.com"];
            // $ccMailId = ["vijayalaxmi@caliberfocus.com"];
          
            $mailHeader = "Resolv Project Hourly Report - Trail";
            $projects = collect($this->getProjects());

            // Current time
            $currentTime = Carbon::now();
            Log::info("Current time: {$currentTime}");

            // Determine start and end times based on current time
            if ($currentTime->hour < 17) {
                if ($currentTime->hour < 5) {
                    // Before 5 PM: Yesterday 5 PM to Current Time
                    $startTime = Carbon::yesterday()->setHour(17)->setMinute(0)->setSecond(0);
                    $endTime = $currentTime;
                } else if($currentTime->hour > 5 && $currentTime->hour < 17){
                    // Before 5 PM: Today 5 PM to Current Time
                    $startTime = Carbon::yesterday()->setHour(17)->setMinute(0)->setSecond(0);
                    $endTime = Carbon::today()->setHour(5)->setMinute(0)->setSecond(0);
                }
            } else {
                // After 5 PM: Today 5 PM to Current Time
                $startTime = Carbon::today()->setHour(17)->setMinute(0)->setSecond(0);
                $endTime = $currentTime;
            }

            Log::info("Calculated start time: {$startTime}");
            Log::info("Calculated end time: {$endTime}");

            // Generate time slots dynamically
            $timeSlots = [];
            $slotStart = $startTime->copy();

            while ($slotStart->lessThan($endTime)) {
                $slotEnd = $slotStart->copy()->addHour();
                $timeSlots[] = [
                    'start' => $slotStart,
                    'end' => $slotEnd,
                    'header' => $slotStart->format('m/d/Y h:i A') . ' to ' . $slotEnd->format('m/d/Y h:i A'),
                ];
                Log::info("Time slot added: {$slotStart} to {$slotEnd}");
                $slotStart = $slotEnd;
            }

            Log::info("Generated time slots: ", $timeSlots);

            // Initialize headers and mail body
            $headers = collect($timeSlots)->pluck('header')->toArray(); // Extract headers
            $mailBody = [];
            $toMailId=[];
            // Process each project
            foreach ($projects as $project) {
                $prjName = Helpers::projectName($project['id'])->project_name ?? null;
                if ($prjName === null) {
                    Log::warning("Project name is null for project ID {$project['id']}");
                    continue;
                }

                $subProjects = count($project['subprject_name']) > 0 ? $project['subprject_name'] : ['project'];
                foreach ($subProjects as $subKey => $subProject) {
                    $tableName = Str::slug(Str::lower($prjName . '_' . $subProject), '_');
                    $modelClass = "App\\Models\\" . Str::studly($tableName);

                    if (!class_exists($modelClass)) {
                        Log::warning("Model class does not exist: {$modelClass}");
                        continue;
                    }

                    $hourlyCounts = [];
                    foreach ($timeSlots as $slot) {
                        $slotStart = $slot['start'];
                        $slotEnd = $slot['end'];

                        // Query hourly count for the specific time slot
                        $hourlyCount = $modelClass::whereBetween('updated_at', [$slotStart, $slotEnd])
                            ->where('chart_status', 'CE_Completed')
                            ->count();

                        Log::info("Hourly count for {$tableName} from {$slotStart} to {$slotEnd}: {$hourlyCount}");

                        $hourlyCounts[] = $hourlyCount; // Add to the array for this project
                    }

                    // Add project data to the mail body
                    $mailBody[] = [
                        'project' => $project['client_name'] . '-' . $subProject,
                        'hourlyCount' => $hourlyCounts, // Full array of counts for all slots                        
                        'project_id' => $project['id'],
                        'subproject_id' => $subKey,
                    ];
                    $toMailId[] = $project['scope_manager_email'][$subKey];
                }
            }

            Log::info("Final mail body: ", $mailBody);

            $today = Carbon::now();

            // Send mail
            Mail::to($toMailId)->cc($ccMailId)->send(new ProjectHourlyMail($mailHeader, $mailBody, $headers, $today));
            Log::info('ProjectHourlyMail executed successfully.');
        } catch (\Exception $e) {
            Log::error('Error in ProjectHourlyMail: ' . $e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }

    public function projectDetailedInformation(Request $request){
        try {
            $prjName = Helpers::projectName(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'))->project_name ?? null;
            $aimsPrjName = Helpers::projectName(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'))->aims_project_name ?? null;
          
                $subPrjName = Helpers::subProjectName(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'),Helpers::encodeAndDecodeID($request->input('subproject_id'),'decode'))->sub_project_name ?? null;
       
            $title = $aimsPrjName . '-' . $subPrjName;
            $tableName = Str::slug(Str::lower($prjName . '_' . $subPrjName), '_');
            $modelClass = "App\\Models\\" . Str::studly($tableName);
            $currentTime = Carbon::now();
            Log::info("Current time: {$currentTime}");
            if ($request->input('requested_date')) {
                $requestedDate = Carbon::createFromFormat('m/d/Y h:i A', $request->input('requested_date'));
                $currentDate = $currentTime->format('Y-m-d');
                $inputDate = $requestedDate->format('Y-m-d');     
                if ($inputDate !== $currentDate) {
                    if ($requestedDate->hour < 5) {
                       $startTime = $requestedDate->copy()->subDay()->setHour(17)->setMinute(0)->setSecond(0);
                        $endTime = $requestedDate->copy()->setHour(5)->setMinute(0)->setSecond(0);
                    } else {
                        $startTime = $requestedDate->copy()->setHour(17)->setMinute(0)->setSecond(0);
                        $endTime = $requestedDate->copy()->addDay()->setHour(5)->setMinute(0)->setSecond(0);
                    }
                } else {
                    if ($currentTime->hour < 17) {
                        $startTime = Carbon::yesterday()->setHour(17)->setMinute(0)->setSecond(0);
                            $endTime = Carbon::today()->setHour(5)->setMinute(0)->setSecond(0);
                    } else {
                        $startTime = Carbon::today()->setHour(17)->setMinute(0)->setSecond(0);
                        $endTime = $currentTime;
                    }
                }
            } else {
                if ($currentTime->hour < 17) {
                    $startTime = Carbon::yesterday()->setHour(17)->setMinute(0)->setSecond(0);
                        $endTime = Carbon::today()->setHour(5)->setMinute(0)->setSecond(0);
                } else {
                    $startTime = Carbon::today()->setHour(17)->setMinute(0)->setSecond(0);
                    $endTime = $currentTime;
                }
            }
            $timeSlots = [];
            $slotStart = $startTime->copy();
            while ($slotStart->lessThan($endTime)) {
                $slotEnd = $slotStart->copy()->addHour();
                $timeSlots[] = [
                    'start' => $slotStart,
                    'end' => $slotEnd,
                    'header' => $slotStart->format('m/d/Y h:i A') . ' to ' . $slotEnd->format('m/d/Y h:i A'),
                ];
                Log::info("Time slot added: {$slotStart} to {$slotEnd}");
                $slotStart = $slotEnd;
            }
            $headers = collect($timeSlots)->pluck('header')->toArray(); // Extract headers
            $BodyDetails = [];
          
            if(class_exists($modelClass)){
                $existingPrjUsers = $modelClass::where('CE_emp_id', '!=','0')->whereNotNull('CE_emp_id')->where('CE_emp_id','like','%AM%')
                ->groupBy('CE_emp_id')->pluck('CE_emp_id')->toArray(); 
                foreach ($existingPrjUsers as $user) {
                    $hourlyCounts = [];
                    $reachedTarget = 0;
                    foreach ($timeSlots as $slot) {
                        $slotStart = $slot['start'];
                        $slotEnd = $slot['end'];
                           $hourlyCount = $modelClass::whereBetween('updated_at', [$slotStart, $slotEnd])
                            ->where('chart_status', 'CE_Completed')->where('CE_emp_id', $user)
                            ->count();

                        Log::info("Hourly count for {$tableName} from {$slotStart} to {$slotEnd}: {$hourlyCount}");

                        $hourlyCounts[] = $hourlyCount; 
                        $reachedTarget += $hourlyCount;
                    }
                    $BodyDetails[] = [
                        'user' => $user,
                       'hourlyCount' => $hourlyCounts, 
                       'reachedTarget' => $reachedTarget,
                   ];
               
                }             
            }  
          return view('emails.projectDetailedInformationWeb', compact('headers', 'BodyDetails','title'));
        } catch (\Exception $e) {
            Log::error('Error in ProjectHourlyMail: ' . $e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }
    public function projectWorkWeb(Request $request)
    {
        try {
          
            $yesterday = $request['request_date'] ? Carbon::createFromFormat('Y-m-d', $request->input('request_date')) : Carbon::yesterday(); //Carbon::yesterday();
            if ($yesterday->isSaturday()) {
                $yesterday = $yesterday->subDay(1); // Friday
            } elseif ($yesterday->isSunday()) {
                $yesterday = $yesterday->subDay(2); // Friday
            }
    
            $today = $request['request_date'] ? Carbon::createFromFormat('Y-m-d', $request->input('request_date'))->copy()->addDay() : Carbon::today();
            $mailHeader = "Resolv Utilization Report for " . $yesterday->format('m/d/Y')." - Trail";
            $yesterDayStartDate = $yesterday->setTime(17, 0, 0)->toDateTimeString();
            $yesterDayEndDate = $today->setTime(8, 0, 0)->toDateTimeString();

            $yesterday5PM = Carbon::yesterday()->setTime(17, 0); 
            $tomorrow9AM = Carbon::tomorrow()->setTime(9, 0); 
    
            $projects = collect($this->getProjects());
            $prjoectsPending = $projects->flatMap(function ($project) use ($yesterDayStartDate, $yesterDayEndDate,$today,$yesterday) {
                $projectData = [];
                $prjName = Helpers::projectName($project['id'])->project_name ?? null;
    
                if ($prjName !== null) {
                    $subProjects = count($project['subprject_name']) > 0 ? $project['subprject_name'] : ['project'];
    
                    foreach ($subProjects as $subProject) {
                        $tableName = Str::slug(Str::lower($prjName . '_' . $subProject), '_');
                        $modelClass = "App\\Models\\" . Str::studly($tableName);
    
                        if (class_exists($modelClass)) {
                            $aCount = $modelClass::whereBetween('created_at', [$yesterDayStartDate, $yesterDayEndDate])
                                        ->where('chart_status', 'CE_Assigned')->count();
                            $cCount = $modelClass::whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                        ->where('chart_status', 'CE_Completed')->count();
                            $qCount = $modelClass::whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                        ->where('chart_status', 'QA_Completed')->count();
                            $productionARCount =  $modelClass::where(function ($query) use ($yesterDayStartDate, $yesterDayEndDate, $yesterday, $today) {
                                $query->whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                      ->whereIn('chart_status', [
                                          'CE_Inprocess', 
                                          'CE_Pending', 
                                          'CE_Completed', 
                                          'CE_Clarification', 
                                          'CE_Hold', 
                                          'AR_non_workable', 
                                          'Revoke'
                                      ]);
                                 $query->orWhere(function ($subQuery) use ($yesterday, $today) {
                                    $subQuery->where('chart_status', 'CE_Completed')
                                             ->whereDate('coder_work_date', $yesterday)
                                             ->orWhereDate('coder_work_date', $today);
                                });
                            })
                            ->groupBy('CE_emp_id')
                            ->havingRaw('MAX(updated_at) BETWEEN ? AND ?', [$yesterDayStartDate, $yesterDayEndDate]) 
                            ->select('CE_emp_id') 
                            ->get() 
                            ->count(); 
                            $productionQACount = $modelClass::whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                ->whereIn('chart_status', ['QA_Assigned', 'QA_Inprocess', 'QA_Pending', 'QA_Completed', 'QA_Clarification', 'QA_Hold'])
                                ->whereNotNull('QA_emp_id')
                                ->distinct('QA_emp_id')
                                ->count('QA_emp_id'); 

                            $totalARDetails = $this->getProjectTotalARCount($project['id']);
                             $totalQADetails = $this->getProjectTotalQACount($project['id']);
                            $loggedResolvAR = 0;$loggedResolvQA=0;
                            foreach($totalARDetails['totalArList'] as $key => $arList){
                                $yesterday5PM = Carbon::yesterday()->setTime(17, 0); 
                                $tomorrow9AM =  Carbon::tomorrow()->setTime(9, 0);
                                $loggedResolvAR +=  EmployeeLogin::where('user_id', $arList['assigned_people'])
                                                    ->whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                                    ->distinct('user_id')
                                                    ->count();
                            }
                            foreach($totalQADetails['totalQAList'] as $key => $qaList){
                                $loggedResolvQA += EmployeeLogin::where('user_id',$qaList['assigned_people'])
                                ->whereBetween('updated_at',[$yesterDayStartDate, $yesterDayEndDate])
                                ->distinct('user_id')
                                ->count();
                            }
                            $projectData[] = [
                                'project' => $project['client_name'] . '-' . $subProject,
                                'Chats' => $aCount,
                                'Coder' => $cCount,
                                'QA' => $qCount,
                                'total_ar' => $totalARDetails['totalArCount'],
                                'total_qa' => $totalQADetails['totalQACount'],
                                'prodcution_ar' => $productionARCount,
                                'prodcution_qa' => $productionQACount,
                                'logged_resolv_ar' => $loggedResolvAR,
                                'logged_resolv_qa' => $loggedResolvQA,
                            ];
                        }
                    }
                }
    
                return $projectData;
            });
            $mailBody = $prjoectsPending->toArray();
            return view('projects.projectUtilizationWeb', compact('mailHeader', 'mailBody', 'yesterday'));
            Log::info('ProjectWorkWeb executed successfully.');
        } catch (\Exception $e) {
            Log::error('Error in ProjectWorkWeb: ' . $e->getMessage());
            Log::debug($e->getMessage());
        }
    }
    public function projectHourlyWeb(Request $request)
    {
        try {
            $projects = collect($this->getProjects());
          
            if($request['startDateTime'] && $request['endDateTime']) {
                $startTime =  Carbon::parse($request['startDateTime']);
                $endTime = Carbon::parse($request['endDateTime']);
            } else {
                  $currentTime = Carbon::now(); 
                 Log::info("Current time: {$currentTime}");
                if ($currentTime->hour < 17) {
                    if ($currentTime->hour < 5) {
                        // Before 5 PM: Yesterday 5 PM to Current Time
                        $startTime = Carbon::yesterday()->setHour(17)->setMinute(0)->setSecond(0);
                        $endTime = $currentTime;
                    } else if($currentTime->hour > 5 && $currentTime->hour < 17){
                        // Before 5 PM: Today 5 PM to Current Time
                        $startTime = Carbon::yesterday()->setHour(17)->setMinute(0)->setSecond(0);
                        $endTime = Carbon::today()->setHour(5)->setMinute(0)->setSecond(0);
                    }
                } else {
                    // After 5 PM: Today 5 PM to Current Time
                    $startTime = Carbon::today()->setHour(17)->setMinute(0)->setSecond(0);
                    $endTime = $currentTime;
                }
            }
            Log::info("Calculated start time: {$startTime}");
            Log::info("Calculated end time: {$endTime}");

            // Generate time slots dynamically
            $timeSlots = [];
            $slotStart = $startTime->copy();

            while ($slotStart->lessThan($endTime)) {
                $slotEnd = $slotStart->copy()->addHour();
                $timeSlots[] = [
                    'start' => $slotStart,
                    'end' => $slotEnd,
                    'header' => $slotStart->format('m/d/Y h:i A') . ' to ' . $slotEnd->format('m/d/Y h:i A'),
                ];
                Log::info("Time slot added: {$slotStart} to {$slotEnd}");
                $slotStart = $slotEnd;
            }

            Log::info("Generated time slots: ", $timeSlots);

            // Initialize headers and mail body
            $headers = collect($timeSlots)->pluck('header')->toArray(); // Extract headers
            $mailBody = [];

            // Process each project
            foreach ($projects as $project) {
                $prjName = Helpers::projectName($project['id'])->project_name ?? null;
                if ($prjName === null) {
                    Log::warning("Project name is null for project ID {$project['id']}");
                    continue;
                }

                $subProjects = count($project['subprject_name']) > 0 ? $project['subprject_name'] : ['project'];
                foreach ($subProjects as $subKey => $subProject) {
                    $tableName = Str::slug(Str::lower($prjName . '_' . $subProject), '_');
                    $modelClass = "App\\Models\\" . Str::studly($tableName);

                    if (!class_exists($modelClass)) {
                        Log::warning("Model class does not exist: {$modelClass}");
                        continue;
                    }

                    $hourlyCounts = [];
                    foreach ($timeSlots as $slot) {
                        $slotStart = $slot['start'];
                        $slotEnd = $slot['end'];
                        $hourlyCount = $modelClass::whereBetween('updated_at', [$slotStart, $slotEnd])
                            ->where('chart_status', 'CE_Completed')
                            ->count();

                        $hourlyCounts[] = $hourlyCount; 
                    }

                    $mailBody[] = [
                        'project' => $project['client_name'] . '-' . $subProject,
                        'hourlyCount' => $hourlyCounts, // Full array of counts for all slots                        
                        'project_id' => $project['id'],
                        'subproject_id' => $subKey,
                    ];
                }
            }

            Log::info("Final mail body: ", $mailBody);

            $today = Carbon::now();
            return view('projects.projectHourlyWeb', compact( 'mailBody','headers', 'startTime', 'endTime', 'today'));
        } catch (\Exception $e) {
            Log::error('Error in ProjectHourlyMail: ' . $e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }
    public function projectDetailedInformationWeb(Request $request){
        try {
            $prjName = Helpers::projectName(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'))->project_name ?? null;
            $aimsPrjName = Helpers::projectName(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'))->aims_project_name ?? null;
          
                $subPrjName = Helpers::subProjectName(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'),Helpers::encodeAndDecodeID($request->input('subproject_id'),'decode'))->sub_project_name ?? null;
       
            $title = $aimsPrjName . '-' . $subPrjName;
            $tableName = Str::slug(Str::lower($prjName . '_' . $subPrjName), '_');
            $modelClass = "App\\Models\\" . Str::studly($tableName);
            $currentTime = Carbon::now();
            Log::info("Current time: {$currentTime}");
            if($request['startTime'] && $request['endTime']) {
                $startTime =  Carbon::parse($request['startTime']);
                $endTime = Carbon::parse($request['endTime']);
            } else {
                if ($currentTime->hour < 17) {
                    $startTime = Carbon::yesterday()->setHour(17)->setMinute(0)->setSecond(0);
                        $endTime = Carbon::today()->setHour(5)->setMinute(0)->setSecond(0);
                } else {
                    $startTime = Carbon::today()->setHour(17)->setMinute(0)->setSecond(0);
                    $endTime = $currentTime;
                }
            }
            $timeSlots = [];
            $slotStart = $startTime->copy();
            while ($slotStart->lessThan($endTime)) {
                $slotEnd = $slotStart->copy()->addHour();
                $timeSlots[] = [
                    'start' => $slotStart,
                    'end' => $slotEnd,
                    'header' => $slotStart->format('m/d/Y h:i A') . ' to ' . $slotEnd->format('m/d/Y h:i A'),
                ];
                Log::info("Time slot added: {$slotStart} to {$slotEnd}");
                $slotStart = $slotEnd;
            }
            $headers = collect($timeSlots)->pluck('header')->toArray(); // Extract headers
            $BodyDetails = [];
          
            if(class_exists($modelClass)){
                $existingPrjUsers = $modelClass::where('CE_emp_id', '!=','0')->whereNotNull('CE_emp_id')->where('CE_emp_id','like','%AM%')
                ->groupBy('CE_emp_id')->pluck('CE_emp_id')->toArray(); 
                foreach ($existingPrjUsers as $user) {
                    $hourlyCounts = [];
                    $reachedTarget = 0;
                    foreach ($timeSlots as $slot) {
                        $slotStart = $slot['start'];
                        $slotEnd = $slot['end'];
                           $hourlyCount = $modelClass::whereBetween('updated_at', [$slotStart, $slotEnd])
                            ->where('chart_status', 'CE_Completed')->where('CE_emp_id', $user)
                            ->count();

                        Log::info("Hourly count for {$tableName} from {$slotStart} to {$slotEnd}: {$hourlyCount}");

                        $hourlyCounts[] = $hourlyCount; 
                        $reachedTarget += $hourlyCount;
                    }
                    $BodyDetails[] = [
                        'user' => $user,
                       'hourlyCount' => $hourlyCounts, 
                       'reachedTarget' => $reachedTarget,
                   ];
               
                }             
            }  
          return view('projects.projectHourlyDetailedWeb', compact('headers', 'BodyDetails','title'));
        } catch (\Exception $e) {
            Log::error('Error in ProjectHourlyMail: ' . $e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }
    public function projectUtilizationDashboard(Request $request) {
        try {
            return view('projects.ProjectUtilizationDashboard');

    } catch (\Exception $e) {
            Log::error('Error in ProjectUtilizationDashboard: ' . $e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }
}
