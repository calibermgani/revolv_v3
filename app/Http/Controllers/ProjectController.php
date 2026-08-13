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
use App\Models\formConfiguration;
use App\Models\InventoryExeFile;
use App\Mail\ResolvBackEndTemplateUploadFile;
use App\Models\BackEndUploadTemplateExeFile;
use Illuminate\Support\Facades\Response;
use App\Jobs\ProcessDayWiseAimsProduction;
use App\Jobs\DateRangeWiseAimsProductionUpdate;
use App\Jobs\DateRangeWiseAimsProduction;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Throwable;
use App\Jobs\ProcessDayWiseAimsProductionNonArProjects;
use App\Jobs\NonArDateRangeWiseAimsProduction;

ini_set('max_execution_time', 180);
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
            //subproject::truncate();
            foreach ($subProjects as $data) {
                $subPrjData['project_id'] = $data['project_id'];
                $subPrjData['sub_project_id'] = $data['sub_project_id'];
                $subPrjData['sub_project_name'] = $data['sub_project_name'];
                $subPrjData['new_sub_project_name'] = $data['new_sub_project_name'];
                $subPrjData['added_by'] = 1;
                $subPrjDetails = subproject::where('project_id', $subPrjData['project_id'])->where('sub_project_id', $subPrjData['sub_project_id'])->first();
                if ($subPrjDetails) {
                    $subPrjDetails->update($subPrjData);
                } else {
                    $subPrjData['sub_project_name'] = $data['new_sub_project_name'];
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
        $projectName = preg_replace('/\s+/', ' ', $projectName);
        $projectName = preg_replace('/\s*[\(\)]\s*/', ' ', $projectName);
        $projectName = preg_replace('/[^\w\s]/', '', $projectName);

        // Split the project name into words
         $words = array_values(array_filter(explode(' ', $projectName)));

        // Get the first character of each word
        $shortcut = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                if (count($words) > 1) {
                    $shortcut .= strtoupper($word[0]);
                } else {
                    $shortcut = strtoupper($word);
                }
            }
        }

        // Step 2: Get existing shortcuts from DB
        $existing = DB::table('projects')
            ->where('project_name', 'like', $shortcut . '%')
            ->pluck('project_name')
            ->toArray();

        // Step 3: If unique → return
        if (!in_array($shortcut, $existing)) {
            return $shortcut;
        }

        // Step 4: Apply letter-based expansion (NO NUMBERS)
        $maxLength = 5;

        for ($i = 1; $i < $maxLength; $i++) {
            $newShortcut = '';

            foreach ($words as $word) {
                $newShortcut .= strtoupper(substr($word, 0, min(strlen($word), $i + 1)));
            }

            if (!in_array($newShortcut, $existing)) {
                return $newShortcut;
            }
        }

        // Step 5: Final fallback (still letters only)
        return strtoupper(substr(str_replace(' ', '', $projectName), 0, 6));
    }
    // public function getProjectShortcutOld($projectName)
    // {
    //     // Remove special characters and text within parentheses
    //     $projectName = preg_replace('/\s+/', ' ', $projectName); // Replace multiple spaces with a single space
    //     $projectName = preg_replace('/\s*[\(\)]\s*/', ' ', $projectName); // Remove parentheses and text within them
    //     $projectName = preg_replace('/[^\w\s]/', '', $projectName); // Remove non-alphanumeric characters except whitespace

    //     // Split the project name into words
    //     $words = explode(' ', $projectName);

    //     // Get the first character of each word
    //     $shortcut = '';
    //     foreach ($words as $word) {
    //         if (!empty($word)) {
    //             if (count($words) > 1) {
    //                 $shortcut .= strtoupper($word[0]);
    //             } else {
    //                 $shortcut = $word;
    //             }
    //         }
    //     }

    //     return $shortcut;
    // }
  
  
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
               // $prjName =  Helpers::projectName($project["id"]) != null ? Helpers::projectName($project["id"])->project_name : null;//dd($prjName);
                $prjDetails = $project["id"] != null ? Helpers::projectName($project["id"]) : null;
                $prjName = $prjDetails && $prjDetails != null ? $prjDetails->project_name : null;
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
           // Log::info('ProjectFileNotThere executed successfully.');
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
            
            //Log::info('Project Error Mail Send Successfully.');
           //Log::info('Project Error Details: ' . print_r($project_information, true));
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
            $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'resolv hourly to email')->first();
            $toMailId = $toMail != null ? explode(",", $toMail->cc_emails) : null;
            $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'resolv hourly cc email')->first();
            $ccMailId = $ccMail != null ? explode(",", $ccMail->cc_emails) : null;
            // $toMailId = ["vijayalaxmi@caliberfocus.com"];
            // $ccMailId = ["vijayalaxmi@caliberfocus.com"];
         
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
                $index = 0;

                while ($slotStart->lessThan($endTime)) {
                    $slotEnd = $slotStart->copy()->addHour()->subSecond(); // 59:59
                    
                    $timeSlots[$index]['start'] = $slotStart->copy();
                    $timeSlots[$index]['end'] = $slotEnd->copy();

                    $slotStart = $slotStart->copy()->addHour(); // next hour
                    $index++;
                }

                // Second loop: fill header
                $headerSlotStart = $startTime->copy();
                $index = 0;

                while ($headerSlotStart->lessThan($endTime)) {
                    $headerSlotEnd = $headerSlotStart->copy()->addHour();

                    $timeSlots[$index]['header'] = $headerSlotStart->format('m/d/Y h:i A') . ' to ' . $headerSlotEnd->format('m/d/Y h:i A');

                    $headerSlotStart = $headerSlotEnd;
                    $index++;
                }


          

            // Initialize headers and mail body
            $headers = collect($timeSlots)->pluck('header')->toArray(); // Extract headers
            $mailBody = $projectIds = $subProjectIds = [];
          //  $toMailId=[];
            // Process each project
            foreach ($projects as $project) {
               // $prjName = Helpers::projectName($project['id'])->project_name ?? null;
                $prjDetails = $project["id"] != null ? Helpers::projectName($project["id"]) : null;
                $prjName = $prjDetails && $prjDetails != null ? $prjDetails->project_name : null;
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
                       // $columnToUse = $hasNonNullArAt ? 'ar_at' : 'updated_at';
                         $columnToUse = 'ar_at';$hourlyCount = 0;
                    if($columnExists) {
                        $hourlyCount = $modelClass::whereBetween($columnToUse, [$slotStart, $slotEnd])
                       // ->where('chart_status', 'CE_Completed')
                      //    ->whereIn('chart_status', ['CE_Completed','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])
                       // ->whereIn('chart_status', ['CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])
                      ->whereNotIn('chart_status',['Auto_Close','AR_non_workable'])
                       ->count();
                    }
                     

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
                    $projectIds[] = $project['id'];
                    $subProjectIds[] = $subKey;
                }
            }

       

            $today = Carbon::now();

            // Send mail
            if($toMailId != null && $ccMailId != null) {    
                Mail::to($toMailId)->cc($ccMailId)->send(new ProjectHourlyMail($mailHeader, $mailBody, $headers, $today,$startTime,$endTime,$projectIds,$subProjectIds));
            }
            Log::info('ProjectHourlyMail executed successfully.');
        } catch (\Exception $e) {
            Log::error('Error in ProjectHourlyMail: ' . $e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }

    
   
    // public function projectHourlyWeb(Request $request)
    // {
    //     if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
    //         try {
    //             $error = "Maintenance mode";
    //             return view('errors.error_page',compact('error'));
    //         } catch (\Exception $e) {
    //             Log::error('Error in ProjectHourlyWeb: ' . $e->getMessage());
    //             Log::debug($e->getTraceAsString());
    //         }
    //     } else {
    //         return redirect('/');
    //     }
    // }
    
    public function projectUtilizationDashboard(Request $request) {
        try {
            return view('projects.ProjectUtilizationDashboard');

    } catch (\Exception $e) {
            Log::error('Error in ProjectUtilizationDashboard: ' . $e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }
    // public function projectWorkWeb(Request $request) {
    //     try {
    //         $error = "Maintenance mode";
    //         return view('errors.error_page',compact('error'));
    //     } catch (\Exception $e) {
    //         Log::error('Error in ProjectWorkWeb: ' . $e->getMessage());
    //         Log::debug($e->getMessage());
    //     }
    // }
   
    public function getProjectCounts($projectId,$yesterDayStartDate,$yesterDayEndDate,$rowProjectId)
    {
    
        try {
            $arCacheKey = 'project_' . str_replace(',', '_', $projectId) . '_ar_count';
            $qaCacheKey = 'project_' . str_replace(',', '_', $projectId) . '_qa_count';      
            $totalAR = Cache::get($arCacheKey, ['totalArList' => []]);
            $totalQA = Cache::get($qaCacheKey, ['totalQAList' => []]);
        
            $loggedResolvAR = 0;$totalARCount = 0;
            if (!empty($totalAR['totalArList'])) {
                foreach($totalAR['totalArList'] as $key => $arList){          
                    if($arList['client_id'] == $rowProjectId && $arList['assigned_people'] != null){
                        $totalARCount += 1;
                    $loggedResolvAR +=  EmployeeLogin::where('user_id', $arList['assigned_people'])
                                        ->whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                        ->distinct('user_id')
                                        ->count();
                    }
                }
            }
            $loggedResolvQA = 0;

            if (!empty($totalQA['totalQAList'])) {
                foreach($totalQA['totalQAList'] as $key => $qaList){    
                    if($qaList['client_id'] == $rowProjectId && $qaList['assigned_people'] != null){
                    $loggedResolvQA +=  EmployeeLogin::where('user_id', $qaList['assigned_people'])
                                        ->whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])
                                        ->distinct('user_id')
                                        ->count();
                    }
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

    public function getProjects1()
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
                        $clientList = $responseData['clientList'] ?? [];
                        if (empty($clientList)) {
                           return [];
                        }

                        $filteredProjects = Helpers::getFilteredClientProjects($clientList);

                        if (empty($filteredProjects)) {
                           return [];
                        }
                        return $filteredProjects;
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
           // $decodedClientName = Helpers::projectName($request->project_id)->project_name;
           $prjDetails = $request->project_id != null ? Helpers::projectName($request->project_id) : null;
           $decodedClientName = $prjDetails && $prjDetails != null ? $prjDetails->project_name : null;
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
           // $decodedClientName = Helpers::projectName($request->project_id)->project_name;
            $prjDetails = $request->project_id != null ? Helpers::projectName($request->project_id) : null;
            $decodedClientName = $prjDetails && $prjDetails != null ? $prjDetails->project_name : null;
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
                               // $autoCloseRecords = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$dataAssignedRows->CE_emp_id)->get();
                                // $arEmpId = $dataAssignedRows->CE_emp_id;
                                // $autoCloseRecordsCount = count($autoCloseRecords);
                                // $data['invoke_date'] = date('Y-m-d',strtotime($dataAssignedRows->invoke_date));
                                $data['parent_id'] = $dataAssignedRows->id;
                                $record = $originalModelClass::where('id', $data['parent_id'])->first();
                                        // $data['coder_work_date'] = $data['ar_at'] = Carbon::now()->format('Y-m-d');
                                         $data['coder_work_date'] =  Carbon::now()->format('Y-m-d');
                                         $data['ar_at'] = Carbon::now()->format('Y-m-d H:i:s');
                                        // $qasamplingDetailsList = QualitySampling::where('project_id', $request->project_id)
                                        //                         ->where('sub_project_id', $request->sub_project_id)
                                        //                         ->where(function($query) use ($arEmpId) {
                                        //                             $query->where('coder_emp_id', $arEmpId)
                                        //                                 ->orWhereNull('coder_emp_id');
                                        //                         })->orderBy('id', 'desc')->get();
                                            $data['QA_emp_id'] = NULL; $data['qa_work_status'] = NULL;$data['chart_status'] = "Auto_Close";
                                            // if(count($qasamplingDetailsList) > 0) {                                          
                                            //     foreach ($qasamplingDetailsList as $qasamplingDetails) {
                                            //         if($qasamplingDetails != null) {
                                            //             $qaPercentage = $qasamplingDetails["qa_percentage"];
                                            //             $qarecords = $autoCloseRecordsCount*$qaPercentage/100;
                                            //             $samplingRecord = $originalModelClass::where('chart_status','Auto_Close')->where('CE_emp_id',$arEmpId)->where('QA_emp_id',$qasamplingDetails["qa_emp_id"])->where('qa_work_status','Sampling')->get();
                                            //             $samplingRecordCount =  count($samplingRecord);
                                            //             if($qarecords >= $samplingRecordCount ) {
                                            //                 $data['QA_emp_id'] =  $qasamplingDetails["qa_emp_id"];
                                            //                 $data['qa_work_status'] = "Sampling";
                                            //                 $data['chart_status'] = "Auto_Close";
                                            //                 break;
                                            //             } else {
                                            //                 $data['qa_work_status'] = "Auto_Close";
                                            //                 $data['chart_status'] = "Auto_Close";
                        
                                            //             }
                                            //         }
                                            //     }
                                            // } else {
                                            //     $data['QA_emp_id'] =  NULL;
                                            //     $data['qa_work_status'] = NULL;
                                            //     $data['chart_status'] = "Auto_Close";
                                            // }
                                        $record->update( ['chart_status' => $data['chart_status'],'QA_emp_id' => $data['QA_emp_id'],'qa_work_status' => $data['qa_work_status'],'coder_work_date' => $data['coder_work_date'],'ar_at' => $data['ar_at']]);
                                        $modelClass::create($data);
                                        if (class_exists($modelClass)) {
                                            $callChartData['emp_id'] =  $data['CE_emp_id'];
                                            $callChartData['project_id'] = $request->project_id;
                                            $callChartData['sub_project_id'] = $request->sub_project_id;
                                            $callChartData['record_id'] = $data['parent_id'];
                                            $callChartData['start_time'] = $data['ar_at'] ?? Carbon::now()->format('Y-m-d H:i:s');
                                            $callChartData['end_time']   = $data['ar_at'] ?? Carbon::now()->format('Y-m-d H:i:s');
                                            $callChartData['work_time'] = "00:00:00";
                                            $callChartData['record_status'] = $data['chart_status'];
                                            CallerChartsWorkLogs::create($callChartData);
                                        }
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

    public function projectHourlyWeb(Request $request)
    {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
             
                if($loginEmpId == "AM4122" || $loginEmpId == "AM4049" || $loginEmpId == "AM4058" || $loginEmpId == "AM4293") {
                   $projects = collect($this->getProjects());
                } else {
                    $projects = collect($this->getClientProjects());
                }
                if($request['startDateTime'] && $request['endDateTime']) {
                    $startTime =  Carbon::parse($request['startDateTime']);
                    $endTime = Carbon::parse($request['endDateTime']);
                } else {
                    $currentTime = Carbon::now(); 
                    Log::info("Current time: {$currentTime}");
                    // if ($currentTime->hour < 17) {
                    //     if ($currentTime->hour < 5) {
                    //         // Before 5 PM: Yesterday 5 PM to Current Time
                    //         $startTime = Carbon::yesterday()->setHour(17)->setMinute(0)->setSecond(0);
                    //         $endTime = $currentTime;
                    //     } else if($currentTime->hour > 5 && $currentTime->hour < 17){
                    //         // Before 5 PM: Today 5 PM to Current Time
                    //         $startTime = Carbon::yesterday()->setHour(17)->setMinute(0)->setSecond(0);
                    //         $endTime = Carbon::today()->setHour(5)->setMinute(0)->setSecond(0);
                    //     }
                    // } else {
                    //     // After 5 PM: Today 5 PM to Current Time
                    //     $startTime = Carbon::today()->setHour(17)->setMinute(0)->setSecond(0);
                    //     $endTime = $currentTime;
                    // }
                      $startTime = Carbon::yesterday()->setHour(8)->setMinute(0)->setSecond(0);
                      $endTime = Carbon::today()->setHour(8)->setMinute(0)->setSecond(0);
                }

                // Generate time slots dynamically
                $timeSlots = [];
                $slotStart = $startTime->copy();
                $index = 0;

                while ($slotStart->lessThan($endTime)) {
                    $slotEnd = $slotStart->copy()->addHour()->subSecond(); // 59:59
                    
                    $timeSlots[$index]['start'] = $slotStart->copy();
                    $timeSlots[$index]['end'] = $slotEnd->copy();

                    $slotStart = $slotStart->copy()->addHour(); // next hour
                    $index++;
                }

                // Second loop: fill header
                $headerSlotStart = $startTime->copy();
                $index = 0;

                while ($headerSlotStart->lessThan($endTime)) {
                    $headerSlotEnd = $headerSlotStart->copy()->addHour();

                    $timeSlots[$index]['header'] = $headerSlotStart->format('m/d/Y h:i A') . ' to ' . $headerSlotEnd->format('m/d/Y h:i A');

                    $headerSlotStart = $headerSlotEnd;
                    $index++;
                }

                $headers = collect($timeSlots)->pluck('header')->toArray(); // Extract headers
                $mailBody = $projectIds = $subProjectIds = [];

                // Process each project
                foreach ($projects as $project) {
                    //$prjName = Helpers::projectName($project['id'])->project_name ?? null;
                    $prjDetails = $project['id'] != null ? Helpers::projectName($project['id']) : null;
                    $prjName = $prjDetails && $prjDetails != null ? $prjDetails->project_name : null;
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
                            // $hourlyCount = $modelClass::whereBetween('updated_at', [$slotStart, $slotEnd])
                            //     ->where('chart_status', 'CE_Completed')
                            //     ->count();
                            $tableName = (new $modelClass)->getTable();
                            $columnExists = Schema::hasColumn($tableName, 'ar_at');
                            $hasNonNullArAt = $columnExists && $modelClass::whereNotNull('ar_at')->exists();
                            // $columnToUse = $hasNonNullArAt ? 'ar_at' : 'updated_at';
                            $columnToUse = 'ar_at';$hourlyCount = 0;
                            if($columnExists) {
                                $hourlyCount = $modelClass::whereBetween($columnToUse, [$slotStart, $slotEnd])
                                //->whereIn('chart_status', ['CE_Completed','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])
                            // ->whereIn('chart_status', ['CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])
                                 ->whereNotIn('chart_status',['Auto_Close','AR_non_workable'])->count();
                            }
                            $hourlyCounts[] = $hourlyCount; 
                        }
                        $mailBody[] = [
                            'project' => $project['client_name'] . '-' . $subProject,
                            'hourlyCount' => $hourlyCounts, // Full array of counts for all slots                        
                            'project_id' => $project['id'],
                            'subproject_id' => $subKey,
                            // 'prjMgrName'=>$prjMgrName,
                            // 'prjBillableFTE'=>$prjBillableFTE,
                            // 'prjSLATarget'=>$prjSLATarget
                        ];
                        //$projectIds[$project['id']] = $subKey;
                        $projectIds[] = $project['id'];
                        $subProjectIds[] = $subKey;
                    }
                }

            

                $today = Carbon::now();$targetType = $request && $request['target_type'] ? $request['target_type'] : "sla_target";
                return view('projects.projectHourlyWeb', compact( 'mailBody','headers', 'startTime', 'endTime', 'today','projectIds','subProjectIds','targetType'));
            } catch (\Exception $e) {
                Log::error('Error in ProjectHourlyWeb: ' . $e->getMessage());
                Log::debug($e->getTraceAsString());
            }
        } else {
            return redirect('/');
        }
    }
    public function projectDetailedInformationWeb(Request $request){
        try {
            $prjDetails = $request->input('project_id')!= null ? Helpers::projectName(Helpers::encodeAndDecodeID($request->input('project_id'),'decode')) : null;
            $prjName = $prjDetails && $prjDetails != null ? $prjDetails->project_name : null;
            $aimsPrjName = $prjDetails && $prjDetails != null ? $prjDetails->aims_project_name : null;
             $subPrjName = Helpers::subProjectName(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'),Helpers::encodeAndDecodeID($request->input('subproject_id'),'decode'))->sub_project_name ?? null;
            //$prjSLATarget = (int)$this->getProjectTotalSlaTarget(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'),Helpers::encodeAndDecodeID($request->input('subproject_id'),'decode'))['projectSLATarget'];
            $title = $aimsPrjName . '-' . $subPrjName;
            $tableName = Str::slug(Str::lower($prjName . '_' . $subPrjName), '_');
            $modelClass = "App\\Models\\" . Str::studly($tableName);
            $currentTime = Carbon::now();
            Log::info("Current time: {$currentTime}");
            if($request['startTime'] && $request['endTime']) {
                $startTime =  Carbon::parse($request['startTime']);
                $endTime = Carbon::parse($request['endTime']);
            } else {
                // if ($currentTime->hour < 17) {
                //     if ($currentTime->hour < 5) {
                //         // Before 5 PM: Yesterday 5 PM to Current Time
                //         $startTime = Carbon::yesterday()->setHour(17)->setMinute(0)->setSecond(0);
                //         $endTime = $currentTime;
                //     } else if($currentTime->hour > 5 && $currentTime->hour < 17){
                //         // Before 5 PM: Today 5 PM to Current Time
                //         $startTime = Carbon::yesterday()->setHour(17)->setMinute(0)->setSecond(0);
                //         $endTime = Carbon::today()->setHour(5)->setMinute(0)->setSecond(0);
                //     }
                // } else {
                //     // After 5 PM: Today 5 PM to Current Time
                //     $startTime = Carbon::today()->setHour(17)->setMinute(0)->setSecond(0);
                //     $endTime = $currentTime;
                // }
                $startTime = Carbon::yesterday()->setHour(8)->setMinute(0)->setSecond(0);
                $endTime = Carbon::today()->setHour(8)->setMinute(0)->setSecond(0);
            }
               $timeSlots = [];
               $slotStart = $startTime->copy();
                $index = 0;

                while ($slotStart->lessThan($endTime)) {
                    $slotEnd = $slotStart->copy()->addHour()->subSecond(); // 59:59
                    
                    $timeSlots[$index]['start'] = $slotStart->copy();
                    $timeSlots[$index]['end'] = $slotEnd->copy();

                    $slotStart = $slotStart->copy()->addHour(); // next hour
                    $index++;
                }

                // Second loop: fill header
                $headerSlotStart = $startTime->copy();
                $index = 0;

                while ($headerSlotStart->lessThan($endTime)) {
                    $headerSlotEnd = $headerSlotStart->copy()->addHour();

                    $timeSlots[$index]['header'] = $headerSlotStart->format('m/d/Y h:i A') . ' to ' . $headerSlotEnd->format('m/d/Y h:i A');

                    $headerSlotStart = $headerSlotEnd;
                    $index++;
                }

            $headers = collect($timeSlots)->pluck('header')->toArray(); // Extract headers
            $BodyDetails = [];
          
            if(class_exists($modelClass)){
                $existingPrjUsers = $modelClass::where('CE_emp_id', '!=','0')->whereNotNull('CE_emp_id')->where('CE_emp_id','like','AM%')
                ->groupBy('CE_emp_id')->pluck('CE_emp_id')->toArray(); 
                  GetProjSubPrjJob::dispatch(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'),Helpers::encodeAndDecodeID($request->input('subproject_id'),'decode'))->delay(now()->addSeconds(5));
                    $prjTotalDetailsCacheKey = 'project_'.Helpers::encodeAndDecodeID($request->input('project_id'),'decode').Helpers::encodeAndDecodeID($request->input('subproject_id'),'decode').'totalDetails' ;
                    $prjBillableFTE = Cache::get($prjTotalDetailsCacheKey, 0);   
                    if (!is_array($prjBillableFTE)) {
                          $prjBillableFTE = ['prjMgrName' => '--', 'prjBillableCount' => '--', 'projectSLATarget' => '--'];
                      }     
                            $targetPerDay = (float)$prjBillableFTE['projectSLATarget'] ;   
                              $userName =Helpers::getUserNameByAllEmpId($existingPrjUsers); 
                // foreach ($existingPrjUsers as $user) {
                //     $hourlyCounts = [];
                //     $reachedTarget = 0;
                //     foreach ($timeSlots as $slot) {
                //         $slotStart = $slot['start'];
                //         $slotEnd = $slot['end'];                   
                //         $tableName = (new $modelClass)->getTable();
                //         $columnExists = Schema::hasColumn($tableName, 'ar_at');
                //         $hasNonNullArAt = $columnExists && $modelClass::whereNotNull('ar_at')->exists();
                //         $columnToUse = $hasNonNullArAt ? 'ar_at' : 'updated_at';
                //         $hourlyCount = $modelClass::whereBetween($columnToUse, [$slotStart, $slotEnd])
                //          ->whereIn('chart_status', ['CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])
                //         ->where('CE_emp_id', $user)
                //         ->count();

                //         $hourlyCounts[] = $hourlyCount; 
                //         $reachedTarget += $hourlyCount;
                //     }
              
                //     if (is_numeric($reachedTarget) && is_numeric($targetPerDay) && $targetPerDay != 0 && $targetPerDay != "") {
                //         $achievedPercentage = ($reachedTarget / $targetPerDay) * 100;
                //     } else {
                //         // Handle errors or set a default value
                //         $achievedPercentage = 0;
                //     }
                    
                //     $BodyDetails[] = [
                //         'user' => $user,
                //        'hourlyCount' => $hourlyCounts, 
                //        'reachedTarget' => $reachedTarget,
                //        'slaTarget' => $targetPerDay,
                //        'achievedPercentage' => $achievedPercentage
                //    ];
               
                // } 
                $tableName = (new $modelClass)->getTable();
                $columnExists = Schema::hasColumn($tableName, 'ar_at');
                // $columnToUse = $columnExists ? 'ar_at' : 'updated_at';
                $columnToUse = 'ar_at';

                // Get overall min/max time range
                $minStart = min(array_column($timeSlots, 'start'));
                $maxEnd   = max(array_column($timeSlots, 'end'));
                $results = collect();
             if($columnExists) {
                // Run one aggregated query
                $results = $modelClass::selectRaw("
                        CE_emp_id,
                        HOUR($columnToUse) as hr,
                        COUNT(*) as cnt
                    ")
                    ->whereIn('CE_emp_id', $existingPrjUsers)
                    // ->whereIn('chart_status', [
                    //     'CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold',
                    //     'QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'
                    // ])
                     ->whereNotIn('chart_status',['Auto_Close','AR_non_workable'])
                    ->whereBetween($columnToUse, [$minStart, $maxEnd])
                    ->groupBy('CE_emp_id', 'hr')
                    ->get();
             }

                // Reshape results: user → hour → count
                $userCounts = [];
                foreach ($results as $row) {
                    $userCounts[$row->CE_emp_id][$row->hr] = $row->cnt;
                }

                $BodyDetails = [];
                foreach ($existingPrjUsers as $user) {
                    $hourlyCounts = [];
                    $reachedTarget = 0;

                    foreach ($timeSlots as $slot) {
                        $slotHour = (int) date('H', strtotime($slot['start'])); // take start hour
                        $count = $userCounts[$user][$slotHour] ?? 0;
                        $hourlyCounts[] = $count;
                        $reachedTarget += $count;
                    }

                    $achievedPercentage = 0;
                    if (is_numeric($reachedTarget) && is_numeric($targetPerDay) && $targetPerDay > 0) {
                        $achievedPercentage = ($reachedTarget / $targetPerDay) * 100;
                    }

                    $BodyDetails[] = [
                        'user'              => $userName[$user] ?? $user,
                        'hourlyCount'       => $hourlyCounts,
                        'reachedTarget'     => $reachedTarget,
                        'slaTarget'         => $targetPerDay,
                        'achievedPercentage'=> $achievedPercentage
                    ];
                }
            
            }
            usort($BodyDetails, function ($a, $b) {
                return $a['achievedPercentage'] <=> $b['achievedPercentage'];
            });
          return view('projects.projectHourlyDetailedWeb', compact('headers', 'BodyDetails','title'));
        } catch (\Exception $e) {
            Log::error('Error in projectHourlyDetailedWeb: ' . $e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }
        public function getProjects()
        {
            try {
                $clientList = formConfiguration::groupBy(
                        'project_id',
                        'sub_project_id'
                    )
                    ->selectRaw('project_id, sub_project_id')
                    ->whereNotIn('sub_project_id',[55])
                    ->get();

                $clientDetails = [];

                foreach ($clientList as $clientData) {
                    $project = Helpers::projectName($clientData->project_id);

                    $clientDetails[] = [
                        'id' => $clientData->project_id,

                        'client_name' => $project
                            ? $project->aims_project_name
                            : null,

                        'subprject_name' => $clientData->project_id
                            ? subproject::where('project_id', $clientData->project_id)
                                ->where('sub_project_id', $clientData->sub_project_id)
                                ->pluck('sub_project_name', 'sub_project_id')
                                ->toArray()
                            : [],
                    ];
                }

                // Sort ascending based on client_name
                $clientDetails = collect($clientDetails)
                    ->sortBy(
                        fn ($item) => strtolower($item['client_name'] ?? '')
                    )
                    ->values()
                    ->toArray();

                // dd($clientDetails);

                return $clientDetails;

            } catch (\Exception $e) {
                Log::error('Unable to fetch project details', [
                    'error' => $e->getMessage(),
                ]);

                return [];
            }
        }
        public function projectWorkWeb(Request $request) {
            try {
                $yesterday = $request['request_date'] ? Carbon::createFromFormat('Y-m-d', $request->input('request_date')) : Carbon::yesterday();
                $today = $request['request_date'] ? Carbon::createFromFormat('Y-m-d', $request->input('request_date'))->copy()->addDay() : Carbon::today();
        
                if ($yesterday->isSaturday()) {
                    $yesterday = $yesterday->subDay(1); // Friday
                    $today = $today->subDay(1);
                } elseif ($yesterday->isSunday()) {
                    $yesterday = $yesterday->subDay(2); // Friday
                    $today = $today->subDay(2);
                }
                $yesterDayStartDate = $yesterday->setTime(8, 0, 0)->toDateTimeString();
                $yesterDayEndDate = $today->setTime(7, 59, 59)->toDateTimeString();
                $projects = collect($this->getProjects());
                $projectsPending = []; 
                $projectIds = $subProjectIds = [];
                $projects->each(function ($project) use ($yesterDayStartDate, $yesterDayEndDate,$today,$yesterday, &$projectsPending, &$projectIds, &$subProjectIds) {
                   // $prjName = Helpers::projectName($project['id'])->project_name ?? null;
                    $prjDetails = Helpers::projectName($project['id']);
                    $prjName = $prjDetails ? $prjDetails->project_name : null;
                    if ($prjName !== null) {
                        $subProjects = count($project['subprject_name']) > 0 ? $project['subprject_name'] : ['project'];
    
                        foreach ($subProjects as $subKey => $subProject) {
                            $tableName = Str::slug(Str::lower($prjName . '_' . $subProject), '_');
                            $modelClass = "App\\Models\\" . Str::studly($tableName);
                            
                            if (class_exists($modelClass)) {
                                $tableName = (new $modelClass)->getTable();
    
                                $arColumnExists = Schema::hasColumn($tableName, 'ar_at');
                                $hasNonNullArAt = $arColumnExists && $modelClass::whereNotNull('ar_at')->exists();
                                // $arColumnToUse = $hasNonNullArAt ? 'ar_at' : 'updated_at';
                                $arColumnToUse ='ar_at';
    
                                $qaColumnExists = Schema::hasColumn($tableName, 'qa_at');
                                $hasNonNullQaAt = $qaColumnExists && $modelClass::whereNotNull('qa_at')->exists();
                                // $qaColumnToUse = $hasNonNullQaAt ? 'qa_at' : 'updated_at';
                                 $qaColumnToUse = 'qa_at';
                                
                                // $aCount = $modelClass::whereBetween('created_at', [$yesterDayStartDate, $yesterDayEndDate])
                                // ->where('chart_status', 'CE_Assigned')->count();
                               $aCount = $cCount = $qCount = $productionARCount = $productionQACount = 0;
                                  $aCount = InventoryExeFile::whereBetween('exe_date', [$yesterDayStartDate, $yesterDayEndDate])
                                            ->where('project_id', $project['id'])
                                             ->where('sub_project_id', $subKey)
                                             ->count();
                            if($arColumnExists) {
                                $cCount = $modelClass::whereBetween($arColumnToUse, [$yesterDayStartDate, $yesterDayEndDate])
                                            // ->where('chart_status', 'CE_Completed')
                                            // ->whereIn('chart_status', ['CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])//before logic
                                             ->whereNotIn('chart_status',['Auto_Close','AR_non_workable'])
                                            ->count();
                            }
                            if($qaColumnExists) {
                                $qCount = $modelClass::whereBetween($qaColumnToUse, [$yesterDayStartDate, $yesterDayEndDate])
                                            ->where('chart_status', 'QA_Completed')->count();
                            }
                            if($arColumnExists) {               
                                $productionARCount = $modelClass::where(function ($query) use ($yesterDayStartDate, $yesterDayEndDate, $yesterday, $today,$arColumnToUse) {
                                    $query->where(function ($subQuery) use ($yesterDayStartDate, $yesterDayEndDate, $arColumnToUse) {
                                        $subQuery->whereBetween($arColumnToUse, [$yesterDayStartDate, $yesterDayEndDate])
                                                    ->whereIn('chart_status', [
                                                        'CE_Inprocess',
                                                        'CE_Pending',
                                                        'CE_Completed',
                                                        'CE_Clarification',
                                                        'CE_Hold',
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
                                // ->havingRaw('MAX(updated_at) BETWEEN ? AND ?', [$yesterDayStartDate, $yesterDayEndDate])
                                ->havingRaw('MAX(ar_at) BETWEEN ? AND ?', [$yesterDayStartDate, $yesterDayEndDate])
                                ->select('CE_emp_id')
                                ->get()
                                ->count();
                            }
                        if($qaColumnExists) {                                        
                            $productionQACount = $modelClass::whereBetween($qaColumnToUse, [$yesterDayStartDate, $yesterDayEndDate])
                                ->whereIn('chart_status', ['QA_Assigned', 'QA_Inprocess', 'QA_Pending', 'QA_Completed', 'QA_Clarification', 'QA_Hold'])
                                ->whereNotNull('QA_emp_id')
                                ->distinct('QA_emp_id')
                                ->count('QA_emp_id'); 
                        }
    
                                $projectsPending[] = [
                                    'project' => $project['client_name'] . '-' . $subProject,
                                    'Chats' => $aCount,
                                    'Coder' => $cCount,
                                    'QA' => $qCount,
                                    'prodcution_ar' => $productionARCount,//production ar login
                                    'prodcution_qa' => $productionQACount,
                                    'project_id' => $project['id'], // Store project ID
                                    'sub_project_id' => $subKey
                                ];
                                $projectIds[] = $project['id'];
                                $subProjectIds[] = $subKey;
                            }
                        }
                    }
                    // return ['data' => $projectData, 'ids' => $project_id];
                });
                GetTotalARCountJob::dispatch($projectIds)->delay(now()->addSeconds(5));
                GetTotalQACountJob::dispatch($projectIds)->delay(now()->addSeconds(5));//dd($projectsPending);
                return view('projects.projectUtilizationWeb', compact('projectsPending', 'yesterday','yesterDayStartDate','yesterDayEndDate','projectIds','subProjectIds'));
            } catch (\Exception $e) {
                Log::error('Error in ProjectWorkWeb: ' . $e->getMessage());
                Log::debug($e->getMessage());
            }
        }    
        public function projectWorkMail() {
            try {
                Log::info('Executing ProjectWorkMail logic.');
                $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'resolv work to email')->first();
                $toMailId = $toMail != null ? explode(",", $toMail->cc_emails) : null;
                $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'resolv work cc email')->first();
                $ccMailId = $ccMail != null ? explode(",", $ccMail->cc_emails) : null;    
                // $toMailId = ["vijayalaxmi@caliberfocus.com"];
                // $ccMailId = ["vijayalaxmi@caliberfocus.com"];
                $yesterday = Carbon::yesterday();
                 $today = Carbon::today();
                if ($yesterday->isSaturday()) {
                    $yesterday = $yesterday->subDay(1); // Friday
                    $today = $today->subDay(1);
                } elseif ($yesterday->isSunday()) {
                    $yesterday = $yesterday->subDay(2); // Friday
                    $today = $today->subDay(2);
                }               
               
                $mailHeader = "Resolv Utilization Report for " . $yesterday->format('m/d/Y');
                $yesterDayStartDate = $yesterday->setTime(8, 0, 0)->toDateTimeString();
                $yesterDayEndDate = $today->setTime(7, 59, 59)->toDateTimeString();
                $projects = collect($this->getProjects());
                $projectsPending = []; 
                $projectIds = $subProjectIds = [];
                $projects->each(function ($project) use ($yesterDayStartDate, $yesterDayEndDate,$today,$yesterday, &$projectsPending, &$projectIds, &$subProjectIds) {
                    //$prjName = Helpers::projectName($project['id'])->project_name ?? null;
                    $prjDetails = Helpers::projectName($project['id']);
                    $prjName = $prjDetails ? $prjDetails->project_name : null;
                    if ($prjName !== null) {
                        $subProjects = count($project['subprject_name']) > 0 ? $project['subprject_name'] : ['project'];
    
                        foreach ($subProjects as $subKey => $subProject) {
                            $tableName = Str::slug(Str::lower($prjName . '_' . $subProject), '_');
                            $modelClass = "App\\Models\\" . Str::studly($tableName);
                            
                            if (class_exists($modelClass)) {
                                $tableName = (new $modelClass)->getTable();
                                
                                $arColumnExists = Schema::hasColumn($tableName, 'ar_at');
                                $hasNonNullArAt = $arColumnExists && $modelClass::whereNotNull('ar_at')->exists();
                                // $arColumnToUse = $hasNonNullArAt ? 'ar_at' : 'updated_at';
                                 $arColumnToUse ='ar_at';
    
                                $qaColumnExists = Schema::hasColumn($tableName, 'qa_at');
                                $hasNonNullQaAt = $qaColumnExists && $modelClass::whereNotNull('qa_at')->exists();
                                // $qaColumnToUse = $hasNonNullQaAt ? 'qa_at' : 'updated_at';
                                $qaColumnToUse = 'qa_at';
                                
                                // $aCount = $modelClass::whereBetween('created_at', [$yesterDayStartDate, $yesterDayEndDate])
                                // ->where('chart_status', 'CE_Assigned')->count();
                                  $aCount =   $cCount = $qCount = $productionARCount = $productionQACount = 0;
                                  $aCount = InventoryExeFile::whereBetween('exe_date', [$yesterDayStartDate, $yesterDayEndDate])
                                            ->where('project_id', $project['id'])
                                             ->where('sub_project_id', $subKey)
                                             ->count();                                           
                            if($arColumnExists) {
                                $cCount = $modelClass::whereBetween($arColumnToUse, [$yesterDayStartDate, $yesterDayEndDate])
                                            // ->where('chart_status', 'CE_Completed')
                                            //->whereIn('chart_status', ['CE_Completed','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])
                                            //->whereIn('chart_status', ['CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])
                                             ->whereNotIn('chart_status',['Auto_Close','AR_non_workable'])
                                            ->count();
                             }
                            if($qaColumnExists) {
                                $qCount = $modelClass::whereBetween($qaColumnToUse, [$yesterDayStartDate, $yesterDayEndDate])
                                            ->where('chart_status', 'QA_Completed')->count();
                             }
                            if($arColumnExists) {
                                $productionARCount = $modelClass::where(function ($query) use ($yesterDayStartDate, $yesterDayEndDate, $yesterday, $today, $arColumnToUse) {
                                    $query->where(function ($subQuery) use ($yesterDayStartDate, $yesterDayEndDate, $arColumnToUse) {
                                        $subQuery->whereBetween($arColumnToUse, [$yesterDayStartDate, $yesterDayEndDate])
                                                    ->whereIn('chart_status', [
                                                        'CE_Inprocess',
                                                        'CE_Pending',
                                                        'CE_Completed',
                                                        'CE_Clarification',
                                                        'CE_Hold'
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
                                // ->havingRaw('MAX(ar_at) BETWEEN ? AND ?', [$yesterDayStartDate, $yesterDayEndDate])
                                ->havingRaw('MAX(ar_at) BETWEEN ? AND ?', [$yesterDayStartDate, $yesterDayEndDate])
                                ->select('CE_emp_id')
                                ->get()
                                ->count();
                            }
                        if($qaColumnExists) {        
                            $productionQACount = $modelClass::whereBetween($qaColumnToUse, [$yesterDayStartDate, $yesterDayEndDate])
                                ->whereIn('chart_status', ['QA_Assigned', 'QA_Inprocess', 'QA_Pending', 'QA_Completed', 'QA_Clarification', 'QA_Hold'])
                                ->whereNotNull('QA_emp_id')
                                ->distinct('QA_emp_id')
                                ->count('QA_emp_id'); 
                        }
    
                                $projectsPending[] = [
                                    // 'project' => $project['client_name'] . '-' . $subProject,
                                    'project' => $project['client_name'],
                                    'subProject' => $subProject,
                                    'Chats' => $aCount,
                                    'Coder' => $cCount,
                                    'QA' => $qCount,
                                    'prodcution_ar' => $productionARCount,
                                    'prodcution_qa' => $productionQACount,
                                    'project_id' => $project['id'], // Store project ID
                                    'sub_project_id' => $subKey,
                                    'yesterDayStartDate' => $yesterDayStartDate,
                                    'yesterDayEndDate' => $yesterDayEndDate
                                ];
                                $projectIds[] = $project['id'];
                                $subProjectIds[] = $subKey;
                            }
                        }
                    }
                    // return ['data' => $projectData, 'ids' => $project_id];
                });
                // GetTotalARCountJob::dispatch($projectIds)->delay(now()->addSeconds(5));
                // GetTotalQACountJob::dispatch($projectIds)->delay(now()->addSeconds(5));
                $mailBody = $projectsPending;
                if($toMailId != null && $ccMailId != null) {                   
                    Mail::to($toMailId)->cc($ccMailId)->send(new ProjectWorkMail($mailHeader, $mailBody, $yesterday,$projectIds,$subProjectIds));
                }
        
                Log::info('ProjectWorkMail executed successfully.');
            } catch (\Exception $e) {
                Log::error('Error in ProjectWorkMail: ' . $e->getMessage());
                Log::debug($e->getMessage());
            }
        } 
        public function projectDetailedInformation(Request $request){
            try {
                $prjDetails = Helpers::projectName(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'));
                $prjName = $prjDetails ? $prjDetails->project_name : null;
                $aimsPrjName = $prjDetails ? $prjDetails->aims_project_name : null;
                $subPrjName = Helpers::subProjectName(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'),Helpers::encodeAndDecodeID($request->input('subproject_id'),'decode'))->sub_project_name ?? null;
                //$prjSLATarget = (int)$this->getProjectTotalSlaTarget(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'),Helpers::encodeAndDecodeID($request->input('subproject_id'),'decode'))['projectSLATarget'];
          
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
                $index = 0;

                while ($slotStart->lessThan($endTime)) {
                    $slotEnd = $slotStart->copy()->addHour()->subSecond(); // 59:59
                    
                    $timeSlots[$index]['start'] = $slotStart->copy();
                    $timeSlots[$index]['end'] = $slotEnd->copy();

                    $slotStart = $slotStart->copy()->addHour(); // next hour
                    $index++;
                }

                // Second loop: fill header
                $headerSlotStart = $startTime->copy();
                $index = 0;

                while ($headerSlotStart->lessThan($endTime)) {
                    $headerSlotEnd = $headerSlotStart->copy()->addHour();

                    $timeSlots[$index]['header'] = $headerSlotStart->format('m/d/Y h:i A') . ' to ' . $headerSlotEnd->format('m/d/Y h:i A');

                    $headerSlotStart = $headerSlotEnd;
                    $index++;
                }

                $headers = collect($timeSlots)->pluck('header')->toArray(); // Extract headers
                $BodyDetails = [];
              
                if(class_exists($modelClass)){
                    $existingPrjUsers = $modelClass::where('CE_emp_id', '!=','0')->whereNotNull('CE_emp_id')->where('CE_emp_id','like','%AM%')
                    ->groupBy('CE_emp_id')->pluck('CE_emp_id')->toArray(); 
                    GetProjSubPrjJob::dispatch(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'),Helpers::encodeAndDecodeID($request->input('subproject_id'),'decode'))->delay(now()->addSeconds(5));
                    $prjTotalDetailsCacheKey = 'project_'.Helpers::encodeAndDecodeID($request->input('project_id'),'decode').Helpers::encodeAndDecodeID($request->input('subproject_id'),'decode').'totalDetails' ;
                    $prjBillableFTE = Cache::get($prjTotalDetailsCacheKey, 0);   
                    if (!is_array($prjBillableFTE)) {
                          $prjBillableFTE = ['prjMgrName' => '--', 'prjBillableCount' => '--', 'projectSLATarget' => '--'];
                      }     
                    $targetPerDay = (float)$prjBillableFTE['projectSLATarget'] ;   
                    $userName =Helpers::getUserNameByAllEmpId($existingPrjUsers); 
                    // foreach ($existingPrjUsers as $user) {
                    //     $hourlyCounts = [];
                    //     $reachedTarget = 0;
                    //     foreach ($timeSlots as $slot) {
                    //         $slotStart = $slot['start'];
                    //         $slotEnd = $slot['end'];
                    //         //    $hourlyCount = $modelClass::whereBetween('updated_at', [$slotStart, $slotEnd])
                    //         //     ->where('chart_status', 'CE_Completed')->where('CE_emp_id', $user)
                    //         //     ->count();
                    //         $tableName = (new $modelClass)->getTable();
                    //         $columnExists = Schema::hasColumn($tableName, 'ar_at');
                    //         $hasNonNullArAt = $columnExists && $modelClass::whereNotNull('ar_at')->exists();
                    //         $columnToUse = $hasNonNullArAt ? 'ar_at' : 'updated_at';
                    //         $hourlyCount = $modelClass::whereBetween($columnToUse, [$slotStart, $slotEnd])
                    //         // ->where('chart_status', 'CE_Completed')
                    //         //->whereIn('chart_status', ['CE_Completed','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])
                    //         ->whereIn('chart_status', ['CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])
                    //         ->where('CE_emp_id', $user)
                    //         ->count();
    
                    //         //Log::info("Hourly count for {$tableName} from {$slotStart} to {$slotEnd}: {$hourlyCount}");
    
                    //         $hourlyCounts[] = $hourlyCount; 
                    //         $reachedTarget += $hourlyCount;
                    //     }
                    //     GetProjSubPrjJob::dispatch(Helpers::encodeAndDecodeID($request->input('project_id'),'decode'),Helpers::encodeAndDecodeID($request->input('subproject_id'),'decode'))->delay(now()->addSeconds(5));
                    //     $prjTotalDetailsCacheKey = 'project_'.Helpers::encodeAndDecodeID($request->input('project_id'),'decode').Helpers::encodeAndDecodeID($request->input('subproject_id'),'decode').'totalDetails' ;
                    //     $prjBillableFTE = Cache::get($prjTotalDetailsCacheKey, 0);    
                    //     if (!is_array($prjBillableFTE)) {
                    //           $prjBillableFTE = ['prjMgrName' => '--', 'prjBillableCount' => '--', 'projectSLATarget' => '--'];
                    //       }                     
                        
                    //     //   if(is_array($prjBillableFTE) && isset($prjBillableFTE['prjBillableCount'], $prjBillableFTE['projectSLATarget'])) {
                    //     //           $targetPerDay = ((float)$prjBillableFTE['prjBillableCount'] * (float)$prjBillableFTE['projectSLATarget']) ;
                    //     //    } else {
                    //     //       $targetPerDay =  is_array($prjBillableFTE) && ($prjBillableFTE['prjBillableCount'] == null  || $prjBillableFTE['projectSLATarget'] == null) ? '--'  : $prjBillableFTE ;
                    //     //    }
                    //     $targetPerDay = (float)$prjBillableFTE['projectSLATarget'] ;
                    //     if (is_numeric($reachedTarget) && is_numeric($targetPerDay) && $targetPerDay != 0 && $targetPerDay != "") {
                    //         $achievedPercentage = ($reachedTarget / $targetPerDay) * 100;
                    //     } else {
                    //         // Handle errors or set a default value
                    //         $achievedPercentage = 0;
                    //     }
                    //     // if (is_numeric($reachedTarget) && is_numeric($prjSLATarget) && $prjSLATarget != 0 && $prjSLATarget != "") {
                    //     //     $achievedPercentage = ($reachedTarget / $prjSLATarget) * 100;
                    //     // } else {
                    //     //     // Handle errors or set a default value
                    //     //     $achievedPercentage = 0;
                    //     // }
                    //     $BodyDetails[] = [
                    //         'user' => $user,
                    //        'hourlyCount' => $hourlyCounts, 
                    //        'reachedTarget' => $reachedTarget,
                    //        'slaTarget' => $targetPerDay,
                    //        'achievedPercentage' => $achievedPercentage
                    //    ];
                   
                    // }   
                    $tableName = (new $modelClass)->getTable();
                    $columnExists = Schema::hasColumn($tableName, 'ar_at');
                    // $columnToUse = $columnExists ? 'ar_at' : 'updated_at';
                    $columnToUse = 'ar_at';

                    // Get overall min/max time range
                    $minStart = min(array_column($timeSlots, 'start'));
                    $maxEnd   = max(array_column($timeSlots, 'end'));
                    $results = collect();
                   if($columnExists) {
                    // Run one aggregated query
                    $results = $modelClass::selectRaw("
                            CE_emp_id,
                            HOUR($columnToUse) as hr,
                            COUNT(*) as cnt
                        ")
                        ->whereIn('CE_emp_id', $existingPrjUsers)
                        // ->whereIn('chart_status', [
                        //     'CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold',
                        //     'QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'
                        // ])
                        ->whereNotIn('chart_status',['Auto_Close','AR_non_workable'])
                        ->whereBetween($columnToUse, [$minStart, $maxEnd])
                        ->groupBy('CE_emp_id', 'hr')
                        ->get();
                   }

                    // Reshape results: user → hour → count
                    $userCounts = [];
                    foreach ($results as $row) {
                        $userCounts[$row->CE_emp_id][$row->hr] = $row->cnt;
                    }

                    $BodyDetails = [];
                    foreach ($existingPrjUsers as $user) {
                        $hourlyCounts = [];
                        $reachedTarget = 0;

                        foreach ($timeSlots as $slot) {
                            $slotHour = (int) date('H', strtotime($slot['start'])); // take start hour
                            $count = $userCounts[$user][$slotHour] ?? 0;
                            $hourlyCounts[] = $count;
                            $reachedTarget += $count;
                        }

                        $achievedPercentage = 0;
                        if (is_numeric($reachedTarget) && is_numeric($targetPerDay) && $targetPerDay > 0) {
                            $achievedPercentage = ($reachedTarget / $targetPerDay) * 100;
                        }

                        $BodyDetails[] = [
                            'user'              => $userName[$user] ?? $user,
                            'hourlyCount'       => $hourlyCounts,
                            'reachedTarget'     => $reachedTarget,
                            'slaTarget'         => $targetPerDay,
                            'achievedPercentage'=> $achievedPercentage
                        ];
                    }
          
                }  
                usort($BodyDetails, function ($a, $b) {
                    return $a['achievedPercentage'] <=> $b['achievedPercentage'];
                });
              return view('emails.projectDetailedInformationWeb', compact('headers', 'BodyDetails','title'));
            } catch (\Exception $e) {
                Log::error('Error in projectDetailedInformationWeb: ' . $e->getMessage());
                Log::debug($e->getTraceAsString());
            }
        }
    public function productionInsert1(Request $request) {
        try {
            $prjDetails = $request->project_id != null ? Helpers::projectName($request->project_id) : null;
            $decodedClientName = $prjDetails && $prjDetails != null ? $prjDetails->project_name : null;
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
                foreach ($request->except('token', 'project_id', 'sub_project_id') as $key => $value) {
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

               dd($query->toSql(), $query->getBindings());
                     $modelClass::create($data);  
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

    // public function productionInsert(Request $request){
    //     try {
    //         $prjDetails = $request->project_id ? Helpers::projectName($request->project_id) : null;
    //         $decodedClientName = $prjDetails?->project_name;
    //         $decodedSubProjectName = $request->sub_project_id
    //             ? Helpers::subProjectName($request->project_id, $request->sub_project_id)?->sub_project_name
    //             : 'project';

    //         $table_name = Str::slug(Str::lower($decodedClientName . '_' . $decodedSubProjectName), '_');
    //         $modelName = Str::studly($table_name);
    //         $originalModelClass = "App\\Models\\" . $modelName;
    //         $modelClass = $originalModelClass . 'Datas';

    //         if (!class_exists($originalModelClass)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "Model class {$originalModelClass} not found."
    //             ], 404);
    //         }

    //         $data = [];
    //         foreach ($request->except('token', 'project_id', 'sub_project_id') as $key => $value) {
    //             $data[$key] = is_array($value) ? implode('_el_', $value) : $value;
    //         }

    //         $data['chart_status'] = "CE_Completed";
    //         $data['qa_work_status'] = "Auto_Close";
    //         $data['CE_emp_id'] = ($request->filled('CE_emp_id') || $request->filled('AR_emp_id')) ? $request->input('CE_emp_id') : null;         
    //         $data['coder_work_date'] = ($request->filled('coder_work_date') || $request->filled('ar_work_date'))
    //             ? Carbon::createFromFormat('Y-m-d', $request->input('coder_work_date'))->toDateString()
    //             : ($data['invoke_date'] ?? null);

    //         $originData = $data;
    //         $originData['ar_notes'] = NULL;
    //         $originData['ar_status_code'] = NULL;
    //         $originData['ar_action_code'] = NULL;
    //         $originData['ar_denial_codes'] = NULL;
    //         $originData['ar_substatus_codes'] = NULL;

    //         // 1️⃣ Save in original model
    //         $parentRecord = $originalModelClass::create($originData);

    //         // 2️⃣ Insert into modelClass with parent_id
    //         if (class_exists($modelClass)) {
    //             $data['parent_id'] = $parentRecord->id;
    //             $modelClass::create($data);
    //         }

    //         return response()->json(['success' => true, 'message' => 'Record inserted.']);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function productionInsert(Request $request) {
        try {
            $prjDetails = $request->project_id ? Helpers::projectName($request->project_id) : null;
            $decodedClientName = $prjDetails != null ? $prjDetails->project_name : null;
            $decodedSubProjectName = $request->sub_project_id && $request->project_id 
                ? Helpers::subProjectName($request->project_id, $request->sub_project_id)->sub_project_name
                : null;
            if($decodedClientName == null || $decodedSubProjectName == null) {
                return response()->json([
                    'success' => false,
                    'message' => "Project name not found."  
                ], 404);
            }

            $table_name = Str::slug(Str::lower($decodedClientName . '_' . $decodedSubProjectName), '_');
            $modelName = Str::studly($table_name);
            $originalModelClass = "App\\Models\\" . $modelName;
            $modelClass = $originalModelClass . 'Datas';

            if (!class_exists($originalModelClass)) {
                return response()->json([
                    'success' => false,
                    'message' => "Model class {$originalModelClass} not found."
                ], 404);
            }

            // Collect validation errors
            $errors = [];$data = [];
            foreach ($request->except('token', 'project_id', 'sub_project_id') as $key => $value) {
                $data[$key] = is_array($value) ? implode('_el_', $value) : $value;
            }
            $possibleColumns = ['ar_notes', 'notes', 'remarks', 'comments'];
            $tableColumns = \Schema::getColumnListing((new $originalModelClass)->getTable());
            // Validate AR Status Code
            if (!empty($request->ar_status_code)) {
                $statCodes = $request->ar_status_code == '--' ? "None" : $request->ar_status_code;
                $status = \App\Models\ARStatusCodes::where('status_code', $statCodes)->first();
                if (!$status) {
                    $errors['ar_status_code'] = "Invalid status code: {$request->ar_status_code}";
                } else {
                    $data['ar_status_code'] = $status->id; // Use ID instead of code
                }
            }

            // Validate AR Action Code
            if (!empty($request->ar_action_code)) {
                $actionCodes = $request->ar_action_code == '--' ? "None" : $request->ar_action_code;
                $action = \App\Models\ARActionCodes::where('action_code', $actionCodes)->first();
                if (!$action) {
                    $errors['ar_action_code'] = "Invalid action code: {$request->ar_action_code}";
                } else {
                    $data['ar_action_code'] = $action->id;
                }
            }

            // Validate AR Denial Code
            // if (!empty($request->ar_denial_codes)) {
            //     $denialCodes = $request->ar_denial_codes == '--' ? "N" : $request->ar_denial_codes;
            //     $denial = \App\Models\ARDenialCode::where('denial_code', $denialCodes)->first();
            //     if (!$denial) {
            //         $errors['ar_denial_codes'] = "Invalid denial code: {$request->ar_denial_codes}";
            //     } else {
            //         $data['ar_denial_codes'] = $denial->id;
            //     }
            // }
            if (!empty($request->ar_denial_codes)) {
                // Handle "--" as default
                if ($request->ar_denial_codes == '--') {
                    $denialCode = "N";
                    $denialDesc = "None";
                } else {
                    // Split into "code" and "description"
                    // Example: "CO-1 - Claim/Service Denied;"
                    $parts = preg_split("/\s+-\s+/", $request->ar_denial_codes, 2);

                    $denialCode = trim($parts[0] ?? '');
                    $denialDesc = trim($parts[1] ?? '');
                }
                     $denial = \App\Models\ARDenialCode::where('denial_code', $denialCode)
                                ->where('code_description', $denialDesc)
                                ->first();      
            
                

                if (empty($denial)) {
                    $errors['ar_denial_codes'] = "Invalid denial code or description: {$request->ar_denial_codes}";
                } else {
                    $data['ar_denial_codes'] = $denial->id;
                }
            }
             if (!empty($request->ar_substatus_codes)) {
                // Handle "--" as default
                if ($request->ar_substatus_codes == '--') {
                    $subStatusCode = "N";
                    $subStatusCodeDesc = "None";
                } else {
                    // Split into "code" and "description"
                    // Example: "N - None;"
                    $parts = preg_split("/\s+-\s+/", $request->ar_substatus_codes, 2);

                    $subStatusCode = trim($parts[0] ?? '');
                    $subStatusCodeDesc = trim($parts[1] ?? '');
                }

                    $substatus = \App\Models\ARSubStatusCode::where('sub_status_code', $subStatusCode)
                                ->where('sub_status_code_description', $subStatusCodeDesc)
                                ->first();
                

                if (empty($substatus)) {
                    $errors['ar_substatus_codes'] = "Invalid substatus code or description: {$request->ar_substatus_codes}";
                } else {
                    $data['ar_substatus_codes'] = $substatus->id;
                }
            }



            // Validate AR Substatus Code
            // if (!empty($request->ar_substatus_codes)) {
            //     $subStatusCodes = $request->ar_substatus_codes == '--' ? "None" : $request->ar_substatus_codes;
            //     $substatus = \App\Models\ARSubStatusCode::where('sub_status_code_description', $subStatusCodes)->first();
            //     if (!$substatus) {
            //         $errors['ar_substatus_codes'] = "Invalid substatus code: { $request->ar_substatus_codes}";
            //     } else {
            //         $data['ar_substatus_codes'] = $substatus->id;
            //     }
            // }

            // If any validation fails, return to Python
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'errors' => $errors,
                    'row_data' => $request->all()
                ], 422);
            }

            // Build common data
        

            $data['chart_status'] = "CE_Completed";
            $data['qa_work_status'] = "Auto_Close";
            $data['CE_emp_id'] = ($request->filled('CE_emp_id') || $request->filled('AR_emp_id')) ? $request->input('AR_emp_id') : null;
            $data['coder_work_date'] = ($request->filled('coder_work_date') || $request->filled('ar_work_date'))
                ? Carbon::createFromFormat('Y-m-d', $request->input('ar_work_date'))->toDateString()
                : ($data['ar_at'] ?? null);
        $dateValue = $request->input('ar_at') ?? $request->input('ar_work_date') ?? $request->input('coder_work_date');
        $data['ar_at'] = $dateValue ? Carbon::parse($dateValue)->setTime(23, 0, 0)->toDateTimeString(): null;
     
            $originData = $data;
            $originData['ar_notes'] = NULL;
            $originData['notes'] = NULL;
            $originData['comments'] = NULL;
            $originData['remarks'] = NULL;
            $originData['ar_status_code'] = NULL;
            $originData['ar_action_code'] = NULL;
            $originData['ar_denial_codes'] = NULL;
            $originData['ar_substatus_codes'] = NULL;
            $noteValue = null;
            foreach ($possibleColumns as $col) {
                if ($request->filled($col)) {
                    $noteValue = $request->input($col);
                    break;
                }
            }

            // assign to all matching columns in the table
            foreach ($possibleColumns as $col) {
                if (in_array($col, $tableColumns)) {
                    $data[$col] = $request->filled($col) ? $request->input($col) : $noteValue;
                }
       }

            
                // Check for duplicate record
                // $duplicate = $originalModelClass::where($originData)->first();
                // if ($duplicate) {
                //     return response()->json([
                //         'success' => false,
                //         'duplicate' => true,
                //         'message' => 'Record already exists in database',
                //         'row_data' => $request->all()
                //     ], 409); // 409 Conflict
                // }

                $existinOriginData = $originData;   
                unset($existinOriginData['notes'], $existinOriginData['ar_notes'], $existinOriginData['remarks'], $existinOriginData['comments'],$existinOriginData['AR_emp_id'], $existinOriginData['ar_work_date'], $existinOriginData['ar_at']);
                

                $existingData = $data;   
                unset($existingData['notes'], $existingData['ar_notes'], $existingData['remarks'], $existingData['comments'],$existingData['AR_emp_id'], $existingData['ar_work_date'],$existingData['ar_at']);

                // Check if original record exists
                $existinOriginDataCheck = $originalModelClass::where($existinOriginData)->exists();

                if (!$existinOriginDataCheck) {
                    // Insert into original model
                    $parentRecord = $originalModelClass::create($originData);
                } else {
                    // Update existing original records
                    $duplicateRecords = $originalModelClass::where($existinOriginData)->get();
                    if ($duplicateRecords->isNotEmpty()) {
                        foreach ($duplicateRecords as $duplicateRecord) {
                            $originData['updated_at'] = carbon::now()->format('Y-m-d H:i:s');
                            $duplicateRecord->update($originData);  // ✅ fixed
                             $parentRecord = $duplicateRecord;
                        }
                    }
                }

            if (!class_exists($modelClass)) {
                return response()->json([
                    'success' => false,
                    'message' => "Model class {$modelClass} not found."
                ], 404);
            }

            // required change
            // child record is identified by original parent id
            $data['parent_id'] = $parentRecord->id;

            $existingChildRecord = $modelClass::where(
                'parent_id',
                $parentRecord->id
            )->first();

            $callChartData = [];

            $callChartData['emp_id'] = $data['CE_emp_id'];
            $callChartData['project_id'] = $request->project_id;
            $callChartData['sub_project_id'] = $request->sub_project_id;
            $callChartData['record_id'] = $parentRecord->id;

            $callChartData['start_time'] =
                $data['ar_at']
                ?? Carbon::now()->format('Y-m-d H:i:s');

            $callChartData['end_time'] =
                $data['ar_at']
                ?? Carbon::now()->format('Y-m-d H:i:s');

            $callChartData['work_time'] = "00:00:00";
            $callChartData['record_status'] = "CE_Completed";

            if (!$existingChildRecord) {

                // insert child record
                $modelClass::create(
                    $data
                );

                // required change
                // prevent duplicate work log for same original record
                CallerChartsWorkLogs::updateOrCreate(
                    [
                        'project_id' => $request->project_id,
                        'sub_project_id' => $request->sub_project_id,
                        'record_id' => $parentRecord->id
                    ],
                    $callChartData
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Record inserted.'
                ]);

            } else {

                // update existing child record
                $data['updated_at'] = Carbon::now()
                    ->format('Y-m-d H:i:s');

                $existingChildRecord->update(
                    $data
                );

                // required change
                // update same work log instead of inserting duplicate
                CallerChartsWorkLogs::updateOrCreate(
                    [
                        'project_id' => $request->project_id,
                        'sub_project_id' => $request->sub_project_id,
                        'record_id' => $parentRecord->id
                    ],
                    $callChartData
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Record updated.'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function backendUploadTemplateExeFile(Request $request){
        try {
            $attributes = [
                'project_id' => isset($request->project_id) ? $request->project_id : NULL,
                'sub_project_id' => isset($request->sub_project_id) && $request->sub_project_id != "NULL" ? $request->sub_project_id : NULL,
                'file_name' => isset($request->file_name) ? $request->file_name : NULL,
                'exe_date' => now()->format('Y-m-d H:i:s'),
                'upload_status'=>  'yes'
            ];
            $prjwhereAttributes = [
                'project_id' => isset($request->project_id) ? $request->project_id : NULL,
                'sub_project_id' => isset($request->sub_project_id) && $request->sub_project_id != "NULL" ? $request->sub_project_id : NULL
            ];

            $formExists = formConfiguration::where($prjwhereAttributes)->exists();
            $prjExists = project::where('project_id', $request->project_id)->exists();
            if ($prjExists && $formExists) {
                $currentDate = Carbon::now()->format('Y-m-d');
                if (isset($request->project_id)) {
                    $projectId = $request->project_id;
                    $prjDetails = Helpers::projectName($projectId);
                    $clientName = $prjDetails ? $prjDetails->project_name : null;
                    $aimsClientName = $prjDetails ? $prjDetails->aims_project_name : null;
                    if (isset($request->sub_project_id) && $request->sub_project_id != "NULL" && $request->sub_project_id != NULL) {
                        if($clientName != NULL && $aimsClientName != NULL) {
                            $subProjectId = $request->sub_project_id;
                            $subProjectName = Helpers::subProjectName($projectId, $subProjectId)->sub_project_name;
                            $table_name = Str::slug((Str::lower($clientName) . '_' . Str::lower($subProjectName)), '_');
                            $prjoectName = $aimsClientName . ' - ' . $subProjectName;
                        } else {
                            $projectId = $table_name = NULL;
                        }
                    } else {
                        $subProjectId = NULL;
                        $subProjectText = "project";
                        $table_name = Str::slug((Str::lower($clientName) . '_' . Str::lower($subProjectText)), '_');
                        $prjoectName = $aimsClientName;
                    }
                } else {
                    $projectId = $table_name = NULL;
                }               
                    $projectsCurrent = [];
                    $projectsCurrent['project'] = $prjoectName;
                    $projectsCurrent['file_name'] = $attributes['file_name'];
                    $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'backend upload template to email id')->first();
                    $toMailId = explode(",", $toMail->cc_emails);                   
                    $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'backend upload template cc email id')->first();
                    $ccMailId = explode(",", $ccMail->cc_emails);

                    $mailDate = Carbon::now()->format('m/d/Y');
                    $mailHeader = $prjoectName . " - Backend Upload Template Successfully Executed - " . $mailDate;
                
                    BackEndUploadTemplateExeFile::create($attributes);
                    if (isset($toMailId) && !empty($toMailId)) {
                        try {
                            Mail::to($toMailId)->cc($ccMailId)->send(new ResolvBackEndTemplateUploadFile($mailHeader, $projectsCurrent));
                            Log::info($prjoectName . "mail sent ");
                        } catch (\Exception $e) {
                            Log::error('Mail sending failed: ' . $e->getMessage());
                        }
                    }
                    return response()->json(['message' => 'BackEnd Upload Template File Inserted Successfully']);                        
              }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

//   public function projectDayWiseAimsProduction(){
//     try {
//         $yesterday = Carbon::yesterday();
//         $today = Carbon::today();

//         if ($yesterday->isSaturday()) {
//             $yesterday = $yesterday->subDay(1); // Friday
//             $today = $today->subDay(1);
//         } elseif ($yesterday->isSunday()) {
//             $yesterday = $yesterday->subDay(2); // Friday
//             $today = $today->subDay(2);
//         }

//         $yesterDayStartDate = $yesterday->copy()->setTime(8, 0, 0)->toDateTimeString();
//         $yesterDayEndDate   = $today->copy()->setTime(7, 59, 0)->toDateTimeString();

//         $projects = collect($this->getProjects());
//         $BodyDetails = [];

//         $projects->each(function ($project) use ($yesterDayStartDate, $yesterDayEndDate, $today, $yesterday, &$BodyDetails) {
//             try {
//                 $prjDetails = Helpers::projectName($project['id']);
//                 $prjName = $prjDetails->project_name;

//                 if ($prjName) {
//                     $subProjects = count($project['subprject_name']) > 0
//                         ? $project['subprject_name']
//                         : ['project'];

//                     foreach ($subProjects as $subKey => $subProject) {
//                         $tableName = Str::slug(Str::lower($prjName . '_' . $subProject), '_');
//                         $modelClass = "App\\Models\\" . Str::studly($tableName);

//                         if (class_exists($modelClass)) {
//                             $existingPrjUsers = $modelClass::where('CE_emp_id', '!=', '0')
//                                 ->whereNotNull('CE_emp_id')
//                                 ->where('CE_emp_id', 'like', '%AM%')
//                                 ->groupBy('CE_emp_id')
//                                 ->pluck('CE_emp_id')
//                                 ->toArray();

//                             $arColumnExists = Schema::hasColumn($tableName, 'ar_at');
//                             $hasNonNullArAt = $arColumnExists && $modelClass::whereNotNull('ar_at')->exists();
//                             $arColumnToUse = $hasNonNullArAt ? 'ar_at' : 'updated_at';

//                             // Dispatch background job
//                             GetProjSubPrjJob::dispatch($project['id'], $subKey)->delay(now()->addSeconds(5));

//                             $prjTotalDetailsCacheKey = 'project_' . $project['id'] . $subKey . 'totalDetails';
//                             $prjBillableFTE = Cache::get($prjTotalDetailsCacheKey, 0);

//                             if (!is_array($prjBillableFTE)) {
//                                 $prjBillableFTE = [
//                                     'prjMgrName' => '--',
//                                     'prjBillableCount' => '--',
//                                     'projectSLATarget' => '--'
//                                 ];
//                             }

//                             $results = $modelClass::selectRaw("CE_emp_id, COUNT(*) as cnt")
//                                 ->whereIn('CE_emp_id', $existingPrjUsers)
//                                 ->whereBetween($arColumnToUse, [$yesterDayStartDate, $yesterDayEndDate])
//                                 ->groupBy('CE_emp_id')
//                                 ->get()
//                                 ->toArray();
//                            $callChartResults = CallerChartsWorkLogs::selectRaw("
//                                     emp_id, COUNT(*) as call_cnt,
//                                     DATE_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(work_time))), '%H:%i:%s') as work_hours
//                                 ")
//                                 ->whereIn('emp_id', $existingPrjUsers)
//                                 ->where('project_id', $project['id'])
//                                 ->where('sub_project_id', $subKey)
//                                 ->whereBetween('start_time', [$yesterDayStartDate, $yesterDayEndDate])
//                                 ->groupBy('emp_id')
//                                 ->get()
//                                 ->toArray();

//                             $BodyDetails[] = [
//                                 'project_id' => $project['id'],
//                                 'sub_project_id' => $subKey,
//                                 'results' => $results,
//                                 'callChartResults' => $callChartResults,
//                                 'workDate' => $yesterday->format('Y-m-d'),
//                             ];
//                         }
//                     }
//                 }
//             } catch (\Exception $innerEx) {
//                 Log::error("Inner loop error for project {$project['id']}: " . $innerEx->getMessage());
//             }
//         });

//         return response()->json([
//             'code' => 200,
//             'message' => 'success',
//             'prjDetailsList' => $BodyDetails,
//         ]);

//     } catch (\Exception $e) {
//         Log::error('Error in projectDayWiseAimsProduction: ' . $e->getMessage());
//         return response()->json([
//             'code' => 500,
//             'message' => 'Server error: ' . $e->getMessage(),
//         ]);
//     }
//   }

 
    public function projectDayWiseAimsProduction()
    {
        try {
            $yesterday = Carbon::yesterday();
            $today = Carbon::today();

            // Adjust for weekends (Saturday/Sunday → Friday)
            if ($yesterday->isSaturday()) {
                $yesterday = $yesterday->subDay();
                $today = $today->subDay();
            } elseif ($yesterday->isSunday()) {
                $yesterday = $yesterday->subDays(2);
                $today = $today->subDays(2);
            }

            $yesterDayStartDate = $yesterday->copy()->setTime(8, 0, 0)->toDateTimeString();
            $yesterDayEndDate   = $today->copy()->setTime(7, 59, 59)->toDateTimeString();
            $workDate = $yesterday->format('Y-m-d');

            // Dispatch the heavy processing to a queued job
            ProcessDayWiseAimsProduction::dispatch(
                $yesterDayStartDate,
                $yesterDayEndDate,
                $workDate
            )
            ->onQueue('aimsCron')
            ->delay(now()->addSeconds(5));

            return response()->json([
                'code' => 202,
                'message' => 'Processing started in background. Check logs or cache for results.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in projectDayWiseAimsProduction: ' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'message' => 'Server error: ' . $e->getMessage(),
            ]);
        }
    }

    public function getAimsProductionResults($date){
        $cacheKey = "aims_production_{$date}";
        $data = Cache::get($cacheKey);

        if (!$data) {
            return response()->json([
                'code' => 404,
                'message' => "No cached results found for date {$date}.",
            ]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $data,
        ]);
    }


// public function projectDateRangeWiseAimsProduction(Request $request)//Batch + Bus
// {
//     try {
//         $request_values = $request->all();

//         if (empty($request_values['resolvProductionDateRange'])) {
//             return response()->json([
//                 'code' => 400,
//                 'message' => 'Date range required'
//             ]);
//         }

//         $date = \Carbon\Carbon::parse(trim(explode("-", $request_values['resolvProductionDateRange'])[0]));

//         if ($date->isSaturday() || $date->isSunday()) {
//             return response()->json([
//                 'code' => 400,
//                 'message' => 'Weekend not allowed'
//             ]);
//         }

//         $startDate = $date->copy()->setTime(8, 0, 0)->toDateTimeString();
//         $endDate   = $date->copy()->addDay()->setTime(7, 59, 0)->toDateTimeString();
//         $workDate  = $date->format('Y-m-d');

//         $projects = app('App\Http\Controllers\ProjectController')->getProjects();dd($projects[0]);

//         $jobs = [];

//         foreach ($projects as $project) {
//             $jobs[] = new DateRangeWiseAimsProduction(
//                 $startDate,
//                 $endDate,
//                 $workDate,
//                 $project
//             );
//         }

//         Bus::batch($jobs)
//             ->then(function (Batch $batch) use ($workDate) {

//                 // ✅ Combine all project results
//                 $finalData = [];

//                 foreach (Cache::get("partial_{$workDate}", []) as $data) {
//                     $finalData[] = $data;
//                 }

//                 Cache::put("date-range-aims-production_{$workDate}", $finalData, now()->addHours(6));

//                 // cleanup
//                 Cache::forget("partial_{$workDate}");
//             })
//             ->catch(function (Batch $batch, Throwable $e) {
//                 Log::error("Batch failed: " . $e->getMessage());
//             })
//             ->dispatch();

//         return response()->json([
//             'code' => 202,
//             'message' => 'Batch started',
//         ]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'code' => 500,
//             'message' => $e->getMessage()
//         ]);
//     }
// }
    public function projectDateRangeWiseAimsProduction(Request $request) {
        try {
            $request_values = $request->all();

            if (empty($request_values['resolvProductionDateRange'])) {
                return response()->json([
                    'code' => 400,
                    'message' => 'Date range required'
                ]);
            }

            $between_dates = explode("-", $request_values['resolvProductionDateRange']);
            $start_date = Carbon::parse(trim($between_dates[0]));
            $end_date   = Carbon::parse(trim($between_dates[1]));

            $currentDate = $start_date->copy();

            while ($currentDate->lte($end_date)) {

                // ❌ Skip Saturday & Sunday
                // if ($currentDate->isSaturday() || $currentDate->isSunday()) {
                //     $currentDate->addDay();
                //     continue;
                // }

                // ✅ 8AM to next day 7:59AM
                $startDate = $currentDate->copy()->setTime(8, 0, 0)->toDateTimeString();
                $endDate   = $currentDate->copy()->addDay()->setTime(7, 59, 59)->toDateTimeString();

                $workDate = $currentDate->format('Y-m-d');

                // ✅ Dispatch job per date
                DateRangeWiseAimsProduction::dispatch($startDate, $endDate, $workDate)->onQueue('aims')
                    ->delay(now()->addSeconds(2));

                $currentDate->addDay();
            }

            return response()->json([
                'code' => 202,
                'message' => 'Jobs dispatched for all working dates'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function getDateWiseRangeResults(Request $request)
    {
        try {
            $request_values = $request->all();

            if (empty($request_values['resolvProductionDateRange'])) {
                return response()->json([
                    'code' => 400,
                    'message' => 'Date range required'
                ]);
            }

            $between_dates = explode("-", $request_values['resolvProductionDateRange']);
            $start_date = Carbon::parse(trim($between_dates[0]));
            $end_date   = Carbon::parse(trim($between_dates[1]));

            $currentDate = $start_date->copy();
            $finalData = [];

            while ($currentDate->lte($end_date)) {

                // if ($currentDate->isSaturday() || $currentDate->isSunday()) {
                //     $currentDate->addDay();
                //     continue;
                // }

                $date = $currentDate->format('Y-m-d');
                $cacheKey = "date-range-aims-production_{$date}";

                $finalData[$date] = Cache::has($cacheKey)
                    ? Cache::get($cacheKey)
                    : 'processing';

                $currentDate->addDay();
            }

            return response()->json([
                'code' => 200,
                'data' => $finalData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ]);
        }
    }

    //non Ar Projects Production
    public function getNonArProjects()
    {
        try {
            $clientList = DB::table(
                'non_ar_inventory_upload_configuration as config'
            )
                ->leftJoin(
                    'subprojects as sub',
                    function ($join) {
                        $join->on(
                            'sub.project_id',
                            '=',
                            'config.project_id'
                        )->on(
                            'sub.sub_project_id',
                            '=',
                            'config.sub_project_id'
                        );
                    }
                )
                ->select([
                    'config.project_id',
                    'config.sub_project_id',
                    'sub.sub_project_name',
                ])
                ->distinct()
                ->get();

            $projectNames = [];

            $clientDetails = $clientList
                ->map(function ($clientData) use (&$projectNames) {
                    if (
                        !array_key_exists(
                            $clientData->project_id,
                            $projectNames
                        )
                    ) {
                        $project = Helpers::projectName(
                            $clientData->project_id
                        );

                        $projectNames[$clientData->project_id] =
                            $project->aims_project_name
                            ?? $project->project_name
                            ?? null;
                    }

                    return [
                        'id' => $clientData->project_id,
                        'client_name' =>
                            $projectNames[$clientData->project_id],
                        'subprject_name' =>
                            $clientData->sub_project_name
                                ? [
                                    $clientData->sub_project_id =>
                                        $clientData->sub_project_name,
                                ]
                                : [],
                    ];
                })
                ->sortBy(
                    fn ($item) =>
                        strtolower($item['client_name'] ?? '')
                )
                ->values()
                ->toArray();

            return $clientDetails;
        } catch (\Throwable $e) {
            Log::error(
                'Unable to fetch non-AR project details',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );

            return [];
        }
    }
    public function nonARProjectsDayWiseAimsProduction()
    {
        try {
            $workDate = Carbon::yesterday();

            /*
            * Saturday -> Friday
            * Sunday   -> Friday
            */
            if ($workDate->isSaturday()) {
                $workDate->subDay();
            } elseif ($workDate->isSunday()) {
                $workDate->subDays(2);
            }

            $formattedWorkDate = $workDate->format('Y-m-d');

            ProcessDayWiseAimsProductionNonArProjects::dispatch(
                $formattedWorkDate,
                $formattedWorkDate,
                $formattedWorkDate
            )
                ->onQueue('nonArAimsCron')
                ->delay(now()->addSeconds(5));

            return response()->json([
                'code' => 202,
                'message' => 'Processing job dispatched successfully.',
                'work_date' => $formattedWorkDate,
            ], 202);
        } catch (\Throwable $e) {
            Log::error(
                'Unable to dispatch non-AR production job',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );

            return response()->json([
                'code' => 500,
                'message' => 'Unable to start processing.',
            ], 500);
        }
    }
    public function getNonArAimsProductionResults($date){
        $cacheKey = "non_ar_aims_production_{$date}";
        $data = Cache::get($cacheKey);

        if (!$data) {
            return response()->json([
                'code' => 404,
                'message' => "No cached results found for date {$date}.",
            ]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $data,
        ]);
    }
    public function projectDateRangeWiseNonArAimsProduction(Request $request) {
        try {
            $request_values = $request->all();

            if (empty($request_values['resolvNonArProductionDateRange'])) {
                return response()->json([
                    'code' => 400,
                    'message' => 'Date range required'
                ]);
            }

            $between_dates = explode("-", $request_values['resolvNonArProductionDateRange']);
            $start_date = Carbon::parse(trim($between_dates[0]));
            $end_date   = Carbon::parse(trim($between_dates[1]));

            $currentDate = $start_date->copy();

            while ($currentDate->lte($end_date)) {             
                $startDate = Carbon::parse(trim($between_dates[0]))->format('Y-m-d');
                $endDate   = Carbon::parse(trim($between_dates[1]))->format('Y-m-d');
                 $workDate = $currentDate->format('Y-m-d');

                // ✅ Dispatch job per date
                NonArDateRangeWiseAimsProduction::dispatch($startDate, $endDate, $workDate)->onQueue('nonArAimsDateRange')
                    ->delay(now()->addSeconds(2));

                $currentDate->addDay();
            }

            return response()->json([
                'code' => 202,
                'message' => 'Jobs dispatched for all working dates'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function getNonArDateWiseRangeResults(Request $request)
    {
        try {
            $request_values = $request->all();

            if (empty($request_values['resolvNonArProductionDateRange'])) {
                return response()->json([
                    'code' => 400,
                    'message' => 'Date range required'
                ]);
            }

            $between_dates = explode("-", $request_values['resolvNonArProductionDateRange']);
            $start_date = Carbon::parse(trim($between_dates[0]));
            $end_date   = Carbon::parse(trim($between_dates[1]));

            $currentDate = $start_date->copy();
            $finalData = [];

            while ($currentDate->lte($end_date)) {
                $date = $currentDate->format('Y-m-d');
                $cacheKey = "date-range-non-ar-aims-production_{$date}";

                $finalData[$date] = Cache::has($cacheKey)
                    ? Cache::get($cacheKey)
                    : 'processing';

                $currentDate->addDay();
            }

            return response()->json([
                'code' => 200,
                'data' => $finalData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ]);
        }
    }
    
}
