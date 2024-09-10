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
class ProjectController extends Controller
{
    public function clientTableUpdate()
    {
        try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
            ];
            $client = new Client();
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
    public function projectWorkMail()
    {
        try {
            Log::info('Executing ProjectWorkMail logic.');
            $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
            // $toMailId = ["elanchezhian@annexmed.net", "fabian@annexmed.com", "ushashree@annexmed.com"];
            // $ccMailId = ["mgani@caliberfocus.com"];
            $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'project work mail to mail id')->first();
            $toMailId = explode(",", $toMail->cc_emails);
            $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'project work mail cc mail id')->first();
            $ccMailId = explode(",", $ccMail->cc_emails);
            // $toMailId = ["vijayalaxmi@caliberfocus.com"];
            // $ccMailId = ["vijayalaxmi@caliberfocus.com"];
            $yesterday = Carbon::yesterday();
            if ($yesterday->isSaturday()) {
                $yesterday = $yesterday->subDay(1); // Friday
            } elseif ($yesterday->isSunday()) {
                $yesterday = $yesterday->subDay(2); // Friday
            }
            $mailHeader = "Procode Utilization Report for " . $yesterday->format('m/d/Y');
            $yesterDayStartDate = $yesterday->startOfDay()->toDateTimeString();
            $yesterDayEndDate = $yesterday->endOfDay()->toDateTimeString();
            // $mailHeader = "Procode Utilization Report for 06/07/2024";
            // $yesterDayStartDate = "2024-06-07 00:00:00";
            // $yesterDayEndDate = "2024-06-07 23:59:59";
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
                        }
                    } else {
                        $subProjectText = "project";
                        $table_name = Str::slug((Str::lower($prjName) . '_' . Str::lower($subProjectText)), '_');
                        $modelName = Str::studly($table_name);
                        $modelClass = "App\\Models\\" . $modelName;
                        $models[] = $modelClass;
                        $prjoectName[] = $project["client_name"];
                    }
                }
                $assignedCounts = $coderCompleteCounts = $pendingCounts = $QACounts  = $prjoectsPending = [];
                foreach ($models as $key => $model) {
                    if (class_exists($model)) {
                        $aCount = $model::whereBetween('created_at', [$yesterDayStartDate, $yesterDayEndDate])->where('chart_status', 'CE_Assigned')->count();
                        $cCount = $model::whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])->where('chart_status', 'CE_Completed')->count();
                        $qCount = $model::whereBetween('updated_at', [$yesterDayStartDate, $yesterDayEndDate])->where('chart_status', 'QA_Completed')->count();
                        // $pCount = $aCount - $cCount;
                        $prjoectsPending[$key]['project'] = $prjoectName[$key];
                        $prjoectsPending[$key]['Chats'] = $aCount;
                        $prjoectsPending[$key]['Coder'] = $cCount;
                        $prjoectsPending[$key]['QA'] = $qCount;
                        // $prjoectsPending[$key]['Balance'] = $pCount;
                    }
                }           
           }
            $mailBody = $prjoectsPending;
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
            $client = new Client();
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
            Log::info('Executing procodeProjectOnHoldMail logic.');
            $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
            $client = new Client();
            // $toMailId = ["vijayalaxmi@caliberfocus.com"];
            // $ccMailId = ["mgani@caliberfocus.com"];
            $mailHeader = "Procode - Project Hold Charges reminder";
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
                        Log::info('Procode Project On Hold Mail executed successfully.');
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
        if ($current_time->hour >= 11 && $today->isSaturday() ==  false  && $today->isSunday() ==  false ) {
            $fileStatus = "The " . $project_information['project_name'] . " inventory is not in the specified location. Could you please check and place the inventory files for today as soon as possible. This will help avoid delays in production.";
            $mailHeader = $project_information['project_name'] . " File not in Specific folder";
            $client = new Client();
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
            $toMailId = $apiData['people_email'];
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
            Log::info('Execute the Procode project current date records check and send mail after 12 PM');
            $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
            $client = new Client();
            $currentDate = Carbon::now()->format('Y-m-d');
            // $toMailId = ["elanchezhian@annexmed.net", "fabian@annexmed.com", "ushashree@annexmed.com"];
            $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'procode project inventory to mail')->first();
            $toMailId = explode(",", $toMail->cc_emails);
            $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'procode project inventory cc mail')->first();
            $ccMailId = explode(",", $ccMail->cc_emails);
            $mailDate =  Carbon::now()->format('m/d/Y');
            $mailHeader = "ProCode - Inventory Upload Successful - " . $mailDate;
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
                    Log::info('Procode Project Inventory Mail executed successfully.');
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
            if ($current_time->hour >= 11 && $today->isSaturday() ==  false  && $today->isSunday() ==  false ) {
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
        return response()->json(["message" => "Error Mail Sent by ProCode"]);
    }
}
