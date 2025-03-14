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
use Illuminate\Support\Facades\Cache;
use App\Jobs\GetTotalARCountJob;
use App\Jobs\GetTotalQACountJob;
use App\Jobs\getProjectSubProjectManager;
use App\Jobs\getProjectSubProjectBillableFTE;
use App\Models\CallerChartsWorkLogs;
use App\Jobs\GetProjJob;
use Illuminate\Support\Facades\Schema;
use App\Models\ManualProjectDuplicate;
use App\Jobs\GetProjSubPrjJob;
use App\Models\QualitySampling;
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
    public function projectWorkMail1()
    {
        try {
            Log::info('Executing ProjectWorkMail logic.');
            $loginEmpId = Session::get('loginDetails')['userDetail']['emp_id'] ?? "";
            $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'resolv work to email')->first();
            $toMailId = explode(",", $toMail->cc_emails);
            $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'resolv work cc email')->first();
            $ccMailId = explode(",", $ccMail->cc_emails);     

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
                            $productionARCount = $modelClass::where(function ($query) use ($yesterDayStartDate, $yesterDayEndDate, $yesterday, $today) {
                                $query->where(function ($subQuery) use ($yesterDayStartDate, $yesterDayEndDate) {
                                    $subQuery->whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                                ->whereIn('chart_status', [
                                                    'CE_Inprocess',
                                                    'CE_Pending',
                                                    'CE_Completed',
                                                    'CE_Clarification',
                                                    'CE_Hold',
                                                    'AR_non_workable',
                                                    'Revoke'
                                                ]);
                                })
                                ->orWhere(function ($subQuery) use ($yesterday, $today) {
                                    $subQuery->where('chart_status', 'QA_Completed')
                                                ->where(function ($nestedQuery) use ($yesterday, $today) {
                                                    $nestedQuery->whereDate('coder_work_date', $yesterday)
                                                                ->orWhereDate('coder_work_date', $today);
                                                });
                                });
                            })
                            ->groupBy('CE_emp_id')
                            ->havingRaw('MAX(updated_at) BETWEEN ? AND ?', [$yesterDayStartDate, $yesterDayEndDate])
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
    
            Log::info('ProjectWorkMail1 executed successfully.');
        } catch (\Exception $e) {
            Log::error('Error in ProjectWorkMail1: ' . $e->getMessage());
            Log::debug($e->getMessage());
        }
    }
  
    // public function getProjects()
    // {
    //     try {
    //         $payload = [
    //             'token' => '1a32e71a46317b9cc6feb7388238c95d',
    //         ];
    //         $client = new Client(['verify' => false]);
    //         $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_all_clients', [
    //             'json' => $payload,
    //         ]);
    //         if ($response->getStatusCode() == 200) {
    //             $data = json_decode($response->getBody(), true);
    //         } else {
    //             return response()->json(['error' => 'API request failed'], $response->getStatusCode());
    //         }
    //         return $data['clientList'];
    //     } catch (\Exception $e) {
    //         Log::debug($e->getMessage());
    //     }
    // }

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
            // $client = new Client(['verify' => false]);
            // $payload = [
            //     'token' => '1a32e71a46317b9cc6feb7388238c95d',
            //     'client_id' => $project_information['project_id']
            // ];
            // $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_emails_above_tl_level', [
            //     'json' => $payload
            // ]);
            // if ($response->getStatusCode() == 200) {
            //     $apiData = json_decode($response->getBody(), true);
            // } else {
            //     return response()->json(['error' => 'API request failed'], $response->getStatusCode());
            // }
            // $toMailId = $apiData['people_email'];
            // $reportingPerson = $apiData['reprting_person'];            
            $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'project file not there to mail')->first();
            $toMailId = explode(",", $toMail->cc_emails);
            $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'project file not there')->first();
            $ccMailId = explode(",", $ccMail->cc_emails);
            // $toMailId = ["mgani@caliberfocus.com"];
            if (isset($toMailId) && !empty($toMailId)) {
                // Mail::to($toMailId)->cc($ccMailId)->send(new ProcodeProjectFile($mailHeader, $fileStatus));//stopped file not there mail for exceeding mails check
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
                    //Mail::to($toMailId)->cc($ccMailId)->send(new ProcodeProjectError($mailHeader, $fileStatus, $error_description));//stopped error description mail for exceeding mails check
                }
            
            Log::info('Project Error Mail Send Successfully.');
            Log::info('Project Error Details: ' . print_r($project_information, true));
            }
        }
     
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
                    sleep($retryAfter);
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
                    $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                    sleep($retryAfter);
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
   
    public function projectHourlyMail()
    {
        try {
            Log::info('Executing Project Hourly Mail logic.');

            // $toMailId = ["anukishan@annexmed.com","elanchezhian@annexmed.net", "fabian@annexmed.com", "prabu@annexmed.com","serdeen@annexmed.com","Neel@annexmed.com","Manoj.Achuthan@annexmed.com","Gavin@annexmed.com","hemanathan@annexmed.net","vani@annexmed.com"];
            // $ccMailId = ['anbalagan@annexmed.net','dominic@annexmed.net','durga@annexmed.net','francis@annexmed.net','jaiganesh@annexmed.net','mohan@annexmed.com',
            // 'nicson@annexmed.net','krajkumar@annexmed.net','athamim@annexmed.net','tikkaram@annexmed.net','vinodh@annexmed.net','sbishop@annexmed.net','karthikeyan@annexmed.net','vijaychandran@annexmed.net',
            // 'hemanathan@annexmed.net','vigneshwaran@annexmed.net','mgani@caliberfocus.com','margaretmary@annexmed.net','vijayalaxmi@caliberfocus.com'];
            $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'resolv hourly to email')->first();
            $toMailId = explode(",", $toMail->cc_emails);
            $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'resolv hourly cc email')->first();
            $ccMailId = explode(",", $ccMail->cc_emails);                  
            // $toMailId = ["vijayalaxmi@caliberfocus.com"];
            // $ccMailId = ["mgani@caliberfocus.com"];
         
            $mailHeader = "Resolv Project Hourly Report";
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

          

            // Initialize headers and mail body
            $headers = collect($timeSlots)->pluck('header')->toArray(); // Extract headers
            $mailBody = [];
          //  $toMailId=[];
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
                        // $hourlyCount = $modelClass::whereBetween('updated_at', [$slotStart, $slotEnd])
                        //     ->where('chart_status', 'CE_Completed')
                        //     ->count();
                        $tableName = (new $modelClass)->getTable();
                        $columnExists = Schema::hasColumn($tableName, 'ar_at');
                        $hasNonNullArAt = $columnExists && $modelClass::whereNotNull('ar_at')->exists();
                        $columnToUse = $hasNonNullArAt ? 'ar_at' : 'updated_at';
                        $hourlyCount = $modelClass::whereBetween($columnToUse, [$slotStart, $slotEnd])
                       // ->where('chart_status', 'CE_Completed')
                       ->whereIn('chart_status', ['CE_Completed','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])
                        ->count();
                     

                        $hourlyCounts[] = $hourlyCount; // Add to the array for this project
                    }

                    // Add project data to the mail body
                    $mailBody[] = [
                        'project' => $project['client_name'] . '-' . $subProject,
                        'hourlyCount' => $hourlyCounts, // Full array of counts for all slots                        
                        'project_id' => $project['id'],
                        'subproject_id' => $subKey,
                    ];
                    //$toMailId[] = $project['scope_manager_email'][$subKey];
                }
            }

       

            $today = Carbon::now();

            // Send mail
            Mail::to($toMailId)->cc($ccMailId)->send(new ProjectHourlyMail($mailHeader, $mailBody, $headers, $today,$startTime,$endTime));
            Log::info('ProjectHourlyMail executed successfully.');
        } catch (\Exception $e) {
            Log::error('Error in ProjectHourlyMail: ' . $e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }

    
    public function projectWorkWeb1(Request $request)
    {
        try {
          
            $yesterday = $request['request_date'] ? Carbon::createFromFormat('Y-m-d', $request->input('request_date')) : Carbon::yesterday(); //Carbon::yesterday();
            if ($yesterday->isSaturday()) {
                $yesterday = $yesterday->subDay(1); // Friday
            } elseif ($yesterday->isSunday()) {
                $yesterday = $yesterday->subDay(2); // Friday
            }
    
            $today = $request['request_date'] ? Carbon::createFromFormat('Y-m-d', $request->input('request_date'))->copy()->addDay() : Carbon::today();
            $mailHeader = "Resolv Utilization Report for " . $yesterday->format('m/d/Y');
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
            Log::info('ProjectWorkWeb1 executed successfully.');
        } catch (\Exception $e) {
            Log::error('Error in ProjectWorkWeb1: ' . $e->getMessage());
            Log::debug($e->getMessage());
        }
    }
    public function projectHourlyWeb(Request $request)
    {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $error = "Maintenance mode";
                return view('errors.error_page',compact('error'));
            } catch (\Exception $e) {
                Log::error('Error in ProjectHourlyWeb: ' . $e->getMessage());
                Log::debug($e->getTraceAsString());
            }
        } else {
            return redirect('/');
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
    public function projectWorkWeb(Request $request) {
        try {
            $error = "Maintenance mode";
            return view('errors.error_page',compact('error'));
        } catch (\Exception $e) {
            Log::error('Error in ProjectWorkWeb: ' . $e->getMessage());
            Log::debug($e->getMessage());
        }
    }
   
    public function getProjectCounts($projectId,$yesterDayStartDate,$yesterDayEndDate,$rowProjectId)
    {
    
        try {
            $arCacheKey = 'project_' . str_replace(',', '_', $projectId) . '_ar_count';
            $qaCacheKey = 'project_' . str_replace(',', '_', $projectId) . '_qa_count';      
            $totalAR = Cache::get($arCacheKey, 0);
            $totalQA = Cache::get($qaCacheKey, 0);
        
            $loggedResolvAR = 0;$totalARCount = 0;
            foreach($totalAR['totalArList'] as $key => $arList){          
                if($arList['client_id'] == $rowProjectId && $arList['assigned_people'] != null){
                    $totalARCount += 1;
                $loggedResolvAR +=  EmployeeLogin::where('user_id', $arList['assigned_people'])
                                    ->whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                    ->distinct('user_id')
                                    ->count();
                }
            }
            $loggedResolvQA = 0;
            foreach($totalQA['totalQAList'] as $key => $qaList){    
                if($qaList['client_id'] == $rowProjectId && $qaList['assigned_people'] != null){
                $loggedResolvQA +=  EmployeeLogin::where('user_id', $qaList['assigned_people'])
                                    ->whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                    ->distinct('user_id')
                                    ->count();
                }
            }
            return response()->json([
                'total_ar' => $totalARCount,
                'logged_resolv_ar' => $loggedResolvAR,
                'logged_resolv_qa' => $loggedResolvQA,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getProjectTotalARCount1($project_id)
    {
        try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $project_id,
            ];
            $data = retry(3, function () use ($payload) {
                $client = new Client(['verify' => false]);
                $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_total_ar_total_list', [
                    'json' => $payload,
                ]);
                if ($response->getStatusCode() == 200) {
                    $responseData = json_decode($response->getBody(), true);

                    if (isset($responseData['totalArList'])) {
                        return $responseData;
                    } else {
                        throw new \Exception('totalArList not found in the API response');
                    }
                } elseif ($response->getStatusCode() == 429) {
                    $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                    sleep($retryAfter);
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
    public function getProjectTotalQACount1($project_id)
    {
        try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $project_id,
            ];    
            // Retry 3 times, with a 2-second delay between each attempt
            $data = retry(3, function () use ($payload) {
                $client = new Client(['verify' => false]);
                $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_total_qa_total_list', [
                    'json' => $payload,
                ]);
                
                if ($response->getStatusCode() == 200) {
                    $responseData = json_decode($response->getBody(), true);
    
                    if (isset($responseData['totalQAList'])) {
                        return $responseData;
                    } else {
                        throw new \Exception('totalQAList not found in the API response');
                    }
                } elseif ($response->getStatusCode() == 429) {
                    $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                    sleep($retryAfter);
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
    public function getProjectTotalSlaTarget($project_id,$practice_id) {
            try {
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'client_id' => $project_id,
                    'practice_id' => $practice_id
                ];         
                // Retry 3 times, with a 2-second delay between each attempt
                $data = retry(3, function () use ($payload) {
                    $client = new Client(['verify' => false]);
                    $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_actuval_target', [
                        'json' => $payload,
                    ]);
                    
                    if ($response->getStatusCode() == 200) {
                        $responseData = json_decode($response->getBody(), true);
        
                        if (isset($responseData['projectSLATarget'])) {
                            return $responseData;
                        } else {
                            throw new \Exception('projectSLATarget not found in the API response');
                        }
                    } elseif ($response->getStatusCode() == 429) {
                        $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                        sleep($retryAfter);
                        throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                    } else {
                        throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                    }
                }, 4000);
                
                return $data;
            } catch (\Exception $e) {
                Log::error('Error in getProjectTotalQACount: ' . $e->getMessage());
                return null;
            }
    }
    public function getProjectSubPrjManager($project_id,$sub_project_id)
    {
        try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $project_id,
                'sub_project_id' => $sub_project_id,
            ];         
            // Retry 3 times, with a 2-second delay between each attempt
            $data = retry(3, function () use ($payload) {
                $client = new Client(['verify' => false]);
                $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_manager', [
                    'json' => $payload,
                ]);
                
                if ($response->getStatusCode() == 200) {
                    $responseData = json_decode($response->getBody(), true);
    
                    if (isset($responseData)) {
                        return $responseData['prjMgrName'];
                    } else {
                        throw new \Exception('prjMgrName not found in the API response');
                    }
                } elseif ($response->getStatusCode() == 429) {
                    $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                    sleep($retryAfter);
                    throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                } else {
                    throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                }
            }, 4000);            
            return $data;
        } catch (\Exception $e) {
            Log::error('Error in getPrjMgrName: ' . $e->getMessage());
            return null;
        }
    }
    public function getProjectSubPrjBillableFTE($project_id, $sub_project_id)
    {
        $cacheKey = 'project_' . $project_id . '_' . $sub_project_id . '_billable_fte';
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($project_id, $sub_project_id) {
            try {
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'client_id' => $project_id,
                    'sub_project_id' => $sub_project_id,
                ];
    
                return retry(3, function () use ($payload) {
                    $client = new Client(['verify' => false]);
                    $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_billable_fte', [
                        'json' => $payload,
                    ]);
    
                    if ($response->getStatusCode() == 200) {
                        $responseData = json_decode($response->getBody(), true);
                        return $responseData['prjBillableCount'] ?? null;
                    } elseif ($response->getStatusCode() == 429) {
                        $retryAfter = $response->getHeader('Retry-After')[0] ?? 60;
                        sleep($retryAfter);
                        throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                    } else {
                        throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                    }
                }, 4000);
            } catch (\Exception $e) {
                Log::error('Error in getprjBillableCount: ' . $e->getMessage());
                return null;
            }
        });
    }
    public function getProjectTotalSlaTargetWeb($project_id, $sub_project_id)
    {
        $cacheKey = 'project_' . $project_id . '_' . $sub_project_id . '_sla_target';
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($project_id, $sub_project_id) {
            try {
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'client_id' => $project_id,
                    'practice_id' => $sub_project_id
                ];
    
                return retry(3, function () use ($payload) {
                    $client = new Client(['verify' => false]);
                    $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_actuval_target', [
                        'json' => $payload,
                    ]);
    
                    if ($response->getStatusCode() == 200) {
                        $responseData = json_decode($response->getBody(), true);
                        return $responseData['projectSLATarget'] ?? null;
                    } elseif ($response->getStatusCode() == 429) {
                        $retryAfter = $response->getHeader('Retry-After')[0] ?? 60;
                        sleep($retryAfter);
                        throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                    } else {
                        throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                    }
                }, 4000);
            } catch (\Exception $e) {
                Log::error('Error in getprjSLATarget: ' . $e->getMessage());
                return null;
            }
        });
    }

    public function getProjects()
    {
           $cacheKey = 'project_list';
        return Cache::remember($cacheKey, now()->addMinutes(30), function ()  {
            try {
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                ];
                return retry(3, function () use ($payload) {
                    $client = new Client(['verify' => false]);
                    $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_all_clients', [
                        'json' => $payload,
                    ]);
                    if ($response->getStatusCode() == 200) {
                        $data = json_decode($response->getBody(), true);
                        return $data['clientList'] ?? null;
                    } elseif ($response->getStatusCode() == 429) {
                        $retryAfter = $response->getHeader('Retry-After')[0] ?? 60;
                        sleep($retryAfter);
                        throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                    } else {
                        throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                    }
                }, 4000);
            } catch (\Exception $e) {
                Log::error('Error in getAllPrjList: ' . $e->getMessage());
                Log::debug($e->getMessage());
            }
        });
    }
    public function getProjectTotalDetailedInformation($project_id, $sub_project_id)
    {
        $cacheKey = 'project_' . $project_id . '_' . $sub_project_id . '_detailed_info';
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($project_id, $sub_project_id) {
            try {
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'client_id' => $project_id,
                    'sub_project_id' => $sub_project_id
                ];
    
                return retry(3, function () use ($payload) {
                    $client = new Client(['verify' => false]);
                    $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_detailied_information', [
                        'json' => $payload,
                    ]);
    
                    if ($response->getStatusCode() == 200) {
                        $responseData = json_decode($response->getBody(), true);
                        return $responseData ?? null;
                    } elseif ($response->getStatusCode() == 429) {
                        $retryAfter = $response->getHeader('Retry-After')[0] ?? 60;
                        sleep($retryAfter);
                        throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                    } else {
                        throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                    }
                }, 4000);
            } catch (\Exception $e) {
                Log::error('Error in getprjDetailedInf: ' . $e->getMessage());
                return null;
            }
        });
    }
    public function getProjectTotalDetailedInformationForHourlyWeb($project_id, $sub_project_id)
    {
        $cacheKey = 'project_' . $project_id . '_' . $sub_project_id . '_detailed_info';
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($project_id, $sub_project_id) {
            try {
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'client_id' => $project_id,
                    'sub_project_id' => $sub_project_id
                ];
    
                return retry(3, function () use ($payload) {
                    $client = new Client(['verify' => false]);
                    $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_detailed_details', [
                        'json' => $payload,
                    ]);
    
                    if ($response->getStatusCode() == 200) {
                        $responseData = json_decode($response->getBody(), true);
                        return $responseData ?? null;
                    } elseif ($response->getStatusCode() == 429) {
                        $retryAfter = $response->getHeader('Retry-After')[0] ?? 60;
                        sleep($retryAfter);
                        throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                    } else {
                        throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                    }
                }, 4000);
            } catch (\Exception $e) {
                Log::error('Error in getprjDetailedInfHoulryWeb: ' . $e->getMessage());
                return null;
            }
        });
    }
    public function projectCallChartWorkLogs() {
        try {
           
            $endTimeCallerChartsWorkLogs = CallerChartsWorkLogs::whereNull('end_time')->get();
            foreach($endTimeCallerChartsWorkLogs as $data) {             
                $startTime = Carbon::parse($data->start_time);
                $endTime = $startTime->addMinute();    
                $workTime = "00:01:00";  
                $data->update([
                    'end_time' => $endTime,
                    'work_time' => $workTime,
                ]);
            }                                      
            Log::info('projectcallChartWorkLogs executed successfully.');
        } catch (\Exception $e) {
            Log::error('Error in projectcallChartWorkLogs: ' . $e->getMessage());
            Log::debug($e->getMessage());
        }
        
    }
    public function getClientProjects()
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'user_id' => $userId,
                ];
                $data = retry(3, function () use ($payload) {
                    $client = new Client(['verify' => false]);
                    $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_clients_on_user', [
                        'json' => $payload,
                    ]);
                    if ($response->getStatusCode() == 200) {
                        // $data = json_decode($response->getBody(), true);
                        $responseData = json_decode($response->getBody(), true);
                        if (isset($responseData)) {
                            return $responseData['clientList'];
                        } else {
                            throw new \Exception('clientList not found in the API response');
                        }
                    } elseif ($response->getStatusCode() == 429) {
                        $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                        sleep($retryAfter);
                        throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                    } else {
                        throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                    }
                }, 4000);
                return $data;
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    
    // public function productionAutoClose(Request $request)
    // {
    //          try {
               
    //                $decodedClientName = Helpers::projectName($request->project_id)->project_name;
    //             $decodedsubProjectName = $request->sub_project_id == NULL ? 'project':Helpers::subProjectName($request->project_id,$request->sub_project_id)->sub_project_name;
    //             $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
    //             $modelName = Str::studly($table_name);
    //              $originalModelClass = "App\\Models\\" . $modelName;
    //              if (class_exists($originalModelClass)) {
    //                 $query = $originalModelClass::query();
    //                     foreach ($request->except('token', 'project_id', 'sub_project_id') as $key => $value) {
                         
    //                         if (is_array($value)) {
    //                             $value = implode('_el_', $value); 
    //                         }
    //                         $d = \DateTime::createFromFormat('Y-m-d', $value);
    //                         $isValid = $d && $d->format('Y-m-d') === $value;
                  
    //                         if (is_numeric($value) || is_bool($value)) {
    //                             $query->where($key, $value,"if");  // Exact match for numeric/boolean
    //                         } elseif ($isValid) {  // Check if it's a date
    //                              $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
    //                         } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
    //                             $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
    //                         } else {
    //                              if($value != null) {  
    //                             $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
    //                             }
    //                         }
                       
    //                 }
    //                  $parentRecords = $query->where('chart_status','CE_Assigned')->get(); dd($parentRecords);
    //              }
                  
               
    //         } catch (\Exception $e) {
    //             $e->getMessage();
    //         }
      
    // }

    // public function alterTableChartStatusColumn(Request $request)
    // {
    //          try {
               
    //              $decodedClientName = Helpers::projectName($request->project_id)->project_name;
    //             $decodedsubProjectName = $request->sub_project_id == NULL ? 'project':Helpers::subProjectName($request->project_id,$request->sub_project_id)->sub_project_name;
    //             $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
    //             $tableDataName = Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName). '_datas'),'_');
    //             $duplicateTableName = Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName) . '_duplicates'),'_');
    //             $tableHistoryName =Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName). '_history'),'_');
    //             $tableRevokeHistoryName =Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName). '_revoke_history'),'_');
    //                $newEnumValues = [
    //                 'CE_Assigned',
    //                 'CE_Inprocess',
    //                 'CE_Pending',
    //                 'CE_Completed',
    //                 'CE_Clarification',
    //                 'CE_Hold',
    //                 'AR_non_workable',
    //                 'QA_Assigned',
    //                 'QA_Inprocess',
    //                 'QA_Pending',
    //                 'QA_Completed',
    //                 'QA_Clarification',
    //                 'QA_Hold',
    //                 'Revoke',
    //                 'Rebuttal',
    //                 'Auto_Close' 
    //             ];
                
    //             $newEnumValuesString = implode("','", $newEnumValues);
                
    //             DB::statement("ALTER TABLE {$table_name} MODIFY COLUMN `chart_status` ENUM('{$newEnumValuesString}') NOT NULL DEFAULT 'CE_Assigned'");
    //             DB::statement("ALTER TABLE {$tableDataName} MODIFY COLUMN `chart_status` ENUM('{$newEnumValuesString}') NOT NULL DEFAULT 'CE_Assigned'");
    //             DB::statement("ALTER TABLE {$duplicateTableName} MODIFY COLUMN `chart_status` ENUM('{$newEnumValuesString}') NOT NULL DEFAULT 'CE_Assigned'");
    //             DB::statement("ALTER TABLE {$tableHistoryName} MODIFY COLUMN `chart_status` ENUM('{$newEnumValuesString}') NOT NULL DEFAULT 'CE_Assigned'");
    //             DB::statement("ALTER TABLE {$tableRevokeHistoryName} MODIFY COLUMN `chart_status` ENUM('{$newEnumValuesString}') NOT NULL DEFAULT 'CE_Assigned'");
    //             return $decodedClientName."project table Chart status column altered successfully";                       
               
    //         } catch (\Exception $e) {
    //             $e->getMessage();
    //         }
      
    // }
    public function alterTableChartStatusColumn(Request $request) {
        try {
            // Get client and sub-project names
            $decodedClientName = Helpers::projectName($request->project_id)->project_name;
            $decodedSubProjectName = $request->sub_project_id
                ? Helpers::subProjectName($request->project_id, $request->sub_project_id)->sub_project_name
                : 'project';

            // Create a base name and generate all table names
            $baseName = Str::slug(Str::lower($decodedClientName . '_' . $decodedSubProjectName), '_');
            $tables = [
                $baseName,
                "{$baseName}_datas",
                "{$baseName}_duplicates",
                "{$baseName}_history",
                "{$baseName}_revoke_history",
            ];

            // Ensure each table name is slugified properly (if needed)
            $tables = array_map(function ($table) {
                return Str::slug($table, '_');
            }, $tables);

            // Define new ENUM values including the new option 'Auto_Close'
            $newEnumValues = [
                'CE_Assigned',
                'CE_Inprocess',
                'CE_Pending',
                'CE_Completed',
                'CE_Clarification',
                'CE_Hold',
                'AR_non_workable',
                'QA_Assigned',
                'QA_Inprocess',
                'QA_Pending',
                'QA_Completed',
                'QA_Clarification',
                'QA_Hold',
                'Revoke',
                'Rebuttal',
                'Auto_Close'
            ];
            $enumString = implode("','", $newEnumValues);

            // Loop over each table and alter the chart_status column
            foreach ($tables as $table) {
                DB::statement("ALTER TABLE {$table} MODIFY COLUMN `chart_status` ENUM('{$enumString}') NOT NULL DEFAULT 'CE_Assigned'");
            }

            return "{$decodedClientName} project table Chart status column altered successfully";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function productionAutoClose(Request $request) {
        try {
            $decodedClientName = Helpers::projectName($request->project_id)->project_name;
            $decodedSubProjectName = $request->sub_project_id == NULL
                ? 'project'
                : Helpers::subProjectName($request->project_id, $request->sub_project_id)->sub_project_name;
            $table_name = Str::slug(Str::lower($decodedClientName.'_'.$decodedSubProjectName), '_');
            $modelName = Str::studly($table_name);
            $originalModelClass = "App\\Models\\" . $modelName;
            $modelClass = "App\\Models\\" . $modelName.'Datas';

            if (class_exists($originalModelClass)) {
                $query = $originalModelClass::query();
                $data = [];
                // Build query based on request parameters (except token, project_id, sub_project_id)
                foreach ($request->except('token', 'project_id', 'sub_project_id') as $key => $value) {
                   // $data[$key] = $value;
                    if (is_array($value)) {
                        $value = implode('_el_', $value);
                    }
                    $d = \DateTime::createFromFormat('Y-m-d', $value);
                    $isValid = $d && $d->format('Y-m-d') === $value;

                    if (is_numeric($value) || is_bool($value)) {
                        $query->where($key, $value);
                    } elseif ($isValid) {
                        $query->whereDate($key, '=', $value);
                    } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                        $query->where($key, $value);
                    } else {
                        if ($value != null) {
                            $query->where($key, 'like', '%' . $value . '%');
                        }
                    }
                }

                $assignedRows = $query->where('chart_status', 'CE_Assigned')->get();
                    if(count($assignedRows) > 0) {
                      //  $updatedRows = $query->where('chart_status', 'CE_Assigned')->update(['chart_status' => 'Auto_Close']);
                            foreach($assignedRows as $dataAssignedRows) {                                
                                $data = $dataAssignedRows->toArray();
                                unset($data['id']);
                                unset($data['created_at']);
                                unset($data['updated_at']);
                             //    dd($data,$dataAssignedRows);                    
                                $autoCloseRecords = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$dataAssignedRows->CE_emp_id)->get();
                                $arEmpId = $dataAssignedRows->CE_emp_id;
                                $autoCloseRecordsCount = count($autoCloseRecords);
                                $data['invoke_date'] = date('Y-m-d',strtotime($dataAssignedRows->invoke_date));
                                $data['parent_id'] = $dataAssignedRows->id;
                                $record = $originalModelClass::where('id', $data['parent_id'])->first();
                                        $data['coder_work_date'] = Carbon::now()->format('Y-m-d');
                                        $qasamplingDetailsList = QualitySampling::where('project_id', $request->project_id)
                                                                ->where('sub_project_id', $request->sub_project_id)
                                                                ->where(function($query) use ($arEmpId) {
                                                                    $query->where('coder_emp_id', $arEmpId)
                                                                        ->orWhereNull('coder_emp_id');
                                                                })->orderBy('id', 'desc')->get();
                                            $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL;
                                        foreach ($qasamplingDetailsList as $qasamplingDetails) {
                                            if($qasamplingDetails != null) {
                                                $qaPercentage = $qasamplingDetails["qa_percentage"];
                                                $qarecords = $autoCloseRecordsCount*$qaPercentage/100;
                                                $samplingRecord = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$arEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                                                $samplingRecordCount =  count($samplingRecord);
                                                if($qarecords >= $samplingRecordCount ) {
                                                    $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                                    $data['qa_work_status'] = "Sampling";
                                                    $data['chart_status'] = "Auto_Close";
                                                    break;
                                                } else {
                                                    $data['qa_work_status'] = "Auto_Close";
                                                     $data['chart_status'] = "Auto_Close";
                
                                                }
                                            }
                                        }
                                        $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status'],'coder_work_date' => $data['coder_work_date']]);
                                        $modelClass::create($data);
                            }
                            return response()->json([
                                'success' => true,
                                'message' => "Successfully updated record."
                            ]);
                    } else {
                        return response()->json([
                            'success' => true,
                            'message' => "These record are already worked."
                        ]);
                    }               
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Model class {$originalModelClass} not found."
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
