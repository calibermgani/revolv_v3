<?php

namespace App\Http\Controllers;

use App\Models\Aging;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                // $client = new Client();
                // $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                //     'form_params' => [
                //         'secret' => env('NOCAPTCHA_SECRET'),
                //         'response' => $request->input('g-recaptcha-response'),
                //     ],
                // ]);
                // $body = json_decode((string) $response->getBody());
                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                     return $this->procodeManagerDashboard();
                    return $this->procodeManagerChartDashboard();
                } else {
                    return $this->procodeUserDashboard();
                }
                // return view('Dashboard/dashboard');
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function procodeTesting()
    {
        return view('Dashboard/procodeTesting');
    }
    public function procodeUserDashboard()
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $agingHeader = Aging::select('days','days_range')->get()->toArray();
                $projects = $this->getProjects();
                $startDate = Carbon::now()->startOfDay()->toDateTimeString();
                $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                $models = [];
                $projectIds = [];
                foreach ($projects as $project) {
                    if (count($project["subprject_name"]) > 0) {
                        foreach ($project["subprject_name"] as $key => $subProject) {
                            $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProject)), '_');
                            $modelName = Str::studly($table_name);
                            $modelClass = "App\\Models\\" . $modelName;
                            $models[] = $modelClass;
                            $projectIds[] = $project["client_name"];
                        }
                    } else {
                        $subProjectText = "project";
                        $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProjectText)), '_');
                        $modelName = Str::studly($table_name);
                        $modelClass = "App\\Models\\" . $modelName;
                        $models[] = $modelClass;
                        $projectIds[] = $project["client_name"];
                    }
                }
                $assignedCounts = $completeCounts = $pendingCounts = $holdCounts = $reworkCounts = $totalCounts = $agingArr1 = $agingArr2 = $agingCount = [];
                foreach ($models as $modelKey => $model) {
                    if (class_exists($model)) {
                        $aCount = $model::where('chart_status', 'CE_Assigned')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $cCount = $model::where('chart_status', 'CE_Completed')->where('qa_work_status', 'Sampling')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $pCount = $model::where('chart_status', 'CE_Pending')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $hCount = $model::where('chart_status', 'CE_Hold')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $rCount = $model::where('chart_status', 'Revoke')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $assignedCounts[] = $aCount;
                        $completeCounts[] = $cCount;
                        $pendingCounts[] = $pCount;
                        $holdCounts[] = $hCount;
                        $reworkCounts[] = $rCount;
                        foreach ($agingHeader as $key => $data) {
                            // $startDay = $data["days"] - 1;
                            // $endDumDay = isset($agingHeader[$key - 1]) &&  isset($agingHeader[$key - 1]["days"]) ? $agingHeader[$key - 1]["days"]  : "0";
                            if(str_contains($data["days_range"],'-')) {
                                $splitRange = explode('-', $data["days_range"]);
                                $startDay = $splitRange[1]-1;
                                $endDumDay =  $splitRange[0]-1;
                                $startDate = Carbon::now()->subDays($startDay)->startOfDay()->toDateTimeString();
                                $endDate = Carbon::now()->subDays($endDumDay)->endOfDay()->toDateTimeString();
                                $dataCount = $model::where('chart_status', 'CE_Assigned')->where('CE_emp_id', $loginEmpId)->whereBetween('created_at', [$startDate, $endDate])->count();
                            } else {
                                $splitRange = explode('+', $data["days_range"]);
                                $endDumDay =  $splitRange[0]-1;
                                $startDay =  $splitRange[1] != "" ? $splitRange[1]-1 : $endDumDay +1;
                                $endDate = Carbon::now()->subDays($endDumDay)->endOfDay()->toDateTimeString();
                                $dataCount = $model::where('chart_status', 'CE_Assigned')->where('CE_emp_id', $loginEmpId)->where('created_at', '<=', $endDate)->count();
                                }
                            $agingArr1[$modelKey][$data["days_range"]] = $dataCount;
                            $agingArr2[$modelKey] = $projectIds[$modelKey];
                        }
                    }
                } //dd( $startArray,$endArray,$startDArray,$endDArray);

                foreach ($agingArr2 as $key => $value) {
                    if (!isset($agingCount[$value])) {
                        $agingCount[$value] = [];
                    }
                    foreach ($agingArr1[$key] as $innerKey => $innerValue) {
                        if (!isset($agingCount[$value][$innerKey])) {
                            $agingCount[$value][$innerKey] = 0;
                        }
                        $agingCount[$value][$innerKey] += $innerValue;
                    }
                }
                $totalAssignedCount = array_sum($assignedCounts);
                $totalCompleteCount = array_sum($completeCounts);
                $totalPendingCount = array_sum($pendingCounts);
                $totalHoldCount = array_sum($holdCounts);
                $totalReworkCount = array_sum($reworkCounts);
                $totalCount = $totalAssignedCount + $totalCompleteCount + $totalPendingCount + $totalHoldCount + $totalReworkCount;
                function allValuesAreZero($array)
                {
                    foreach ($array as $value) {
                        if ($value !== 0) {
                            return false;
                        }
                    }
                    return true;
                }

                foreach ($agingCount as $key => $subArray) {
                    if (allValuesAreZero($subArray)) {
                        unset($agingCount[$key]);
                    }
                }
                return view('Dashboard/userDashboard', compact('projects', 'totalAssignedCount', 'totalCompleteCount', 'totalPendingCount', 'totalHoldCount', 'totalReworkCount', 'totalCount', 'agingHeader', 'agingCount'));
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
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
            $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_practice_on_client', [
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
            $calendarId = $request->CalendarId;
            foreach ($subprojects as $key => $data) {
                $subProjectsWithCount[$key]['client_id'] = $clientDetails['id'];
                $subProjectsWithCount[$key]['client_name'] = $clientDetails['client_name'];
                $subProjectsWithCount[$key]['sub_project_id'] = $data['id'];
                $subProjectsWithCount[$key]['sub_project_name'] = $data['name'];
                $projectName = $subProjectsWithCount[$key]['client_name'];
                $table_name = Str::slug((Str::lower($projectName) . '_' . Str::lower($subProjectsWithCount[$key]['sub_project_name'])), '_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                if ($calendarId == "year") {
                    $days = Carbon::now()->daysInYear;
                    $startDate = Carbon::now()->startOfYear()->toDateTimeString();
                    $endDate = Carbon::now()->endOfYear()->toDateTimeString();
                } else if ($calendarId == "month") {
                    $days =  Carbon::now()->daysInMonth;
                    $startDate = Carbon::now()->startOfMonth()->toDateTimeString();
                    $endDate = Carbon::now()->endOfMonth()->toDateTimeString();
                } else {
                    $days = 0;
                    $startDate = Carbon::now()->startOfDay()->toDateTimeString();
                    $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                }
                // $startDate = Carbon::now()->subDays($days)->startOfDay()->toDateTimeString();
                // $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                if (class_exists($modelClass)) {
                    $subProjectsWithCount[$key]['assignedCount'] = $modelClass::where('chart_status', 'CE_Assigned')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                    $subProjectsWithCount[$key]['CompletedCount'] = $modelClass::where('chart_status', 'CE_Completed')->where('qa_work_status', 'Sampling')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                    $subProjectsWithCount[$key]['PendingCount'] = $modelClass::where('chart_status', 'CE_Pending')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                    $subProjectsWithCount[$key]['holdCount'] = $modelClass::where('chart_status', 'CE_Hold')->where('CE_emp_id', $loginEmpId)
                        ->where(function ($query) use ($startDate, $endDate, $days) {
                            if ($days == 0) {
                                $query;
                            } else {
                                $query->whereBetween('updated_at', [$startDate, $endDate]);
                            }
                        })->count();
                } else {
                    $subProjectsWithCount[$key]['assignedCount'] = '--';
                    $subProjectsWithCount[$key]['CompletedCount'] = '--';
                    $subProjectsWithCount[$key]['PendingCount'] = '--';
                    $subProjectsWithCount[$key]['holdCount'] = '--';
                }
            }

            return response()->json(['subprojects' => $subProjectsWithCount]);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    public function procodeManagerDashboard()
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $agingHeader = Aging::select('days', 'days_range')->get()->toArray();
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $projects = $this->getProjects();
                $startDate = Carbon::now()->startOfDay()->toDateTimeString();
                $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                $models = $projectIds = [];
                foreach ($projects as $project) {
                    if (count($project["subprject_name"]) > 0) {
                        foreach ($project["subprject_name"] as $key => $subProject) {
                            $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProject)), '_');
                            $modelName = Str::studly($table_name);
                            $modelClass = "App\\Models\\" . $modelName;
                            $models[] = $modelClass;
                            $projectIds[] = $project["client_name"];
                        }
                    } else {
                        $subProjectText = "project";
                        $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProjectText)), '_');
                        $modelName = Str::studly($table_name);
                        $modelClass = "App\\Models\\" . $modelName;
                        $models[] = $modelClass;
                        $projectIds[] = $project["client_name"];
                    }
                }
                $assignedCounts = $completeCounts = $pendingCounts = $holdCounts = $reworkCounts = $totalCounts = $agingArr1 = $agingArr2 = $agingCount = [];
                foreach ($models as $modelKey => $model) {
                    if (class_exists($model)) {
                        $aCount = $model::where('chart_status', 'CE_Assigned')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $cCount = $model::where('chart_status', 'CE_Completed')->where('qa_work_status', 'Sampling')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $pCount = $model::where('chart_status', 'CE_Pending')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $hCount = $model::where('chart_status', 'CE_Hold')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $rCount = $model::where('chart_status', 'Revoke')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $assignedCounts[] = $aCount;
                        $completeCounts[] = $cCount;
                        $pendingCounts[] = $pCount;
                        $holdCounts[] = $hCount;
                        $reworkCounts[] = $rCount;
                        foreach ($agingHeader as $key => $data) {
                            // $startDay = $data["days"] - 1;
                            // $endDumDay = isset($agingHeader[$key - 1]) &&  isset($agingHeader[$key - 1]["days"]) ? $agingHeader[$key - 1]["days"]  : "0";
                           if(str_contains($data["days_range"],'-')) {
                                $splitRange = explode('-', $data["days_range"]);
                                $startDay = $splitRange[1]-1;
                                $endDumDay =  $splitRange[0]-1;
                                $startDate = Carbon::now()->subDays($startDay)->startOfDay()->toDateTimeString();
                                $endDate = Carbon::now()->subDays($endDumDay)->endOfDay()->toDateTimeString();
                                $dataCount = $model::where('chart_status', 'CE_Assigned')->whereBetween('created_at', [$startDate, $endDate])->count();
                            } else {
                                $splitRange = explode('+', $data["days_range"]);
                                $endDumDay =  $splitRange[0]-1;
                                $startDay =  $splitRange[1] != "" ? $splitRange[1]-1 : $endDumDay +1;
                                $endDate = Carbon::now()->subDays($endDumDay)->endOfDay()->toDateTimeString();
                                $dataCount = $model::where('chart_status', 'CE_Assigned')->where('created_at', '<=', $endDate)->count();
                            }
                            $agingArr1[$modelKey][$data["days_range"]] = $dataCount;
                            $agingArr2[$modelKey] = $projectIds[$modelKey];
                        }
                    }
                }

                foreach ($agingArr2 as $key => $value) {
                    if (!isset($agingCount[$value])) {
                        $agingCount[$value] = [];
                    }
                    foreach ($agingArr1[$key] as $innerKey => $innerValue) {
                        if (!isset($agingCount[$value][$innerKey])) {
                            $agingCount[$value][$innerKey] = 0;
                        }
                        $agingCount[$value][$innerKey] += $innerValue;
                    }
                }

                $totalAssignedCount = array_sum($assignedCounts);
                $totalCompleteCount = array_sum($completeCounts);
                $totalPendingCount = array_sum($pendingCounts);
                $totalHoldCount = array_sum($holdCounts);
                $totalReworkCount = array_sum($reworkCounts);
                $totalCount = $totalAssignedCount + $totalCompleteCount + $totalPendingCount + $totalHoldCount + $totalReworkCount;

                $agingData = [
                    'AMBC' => [50, 0, 0, 0, 0, 100, 0, 153, 0, 45, 45],
                    'Cancer Care Specialists' => [50, 0, 0, 0, 0, 0, 0, 11, 0, 45, 45],
                    "Saco River Medical Group" => [50, 0, 0, 0, 0, 0, 0, 12, 0, 45, 45],
                    // "AIG" => [250, 0, 0, 0, 0, 70, 0, 12, 0, 45, 45],
                    // "Ash Meomorial Hospital" => [250, 0, 0, 0, 0, 0, 0, 12, 0, 45, 45],
                    // "MDCSp" => [230, 0, 0, 0, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro" => [140, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro1" => [100, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro2" => [200, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro3" => [50, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro4" => [40, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro5" => [30, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro6" => [10, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro7" => [1, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro8" => [2, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro9" => [3, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro10" => [4, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro11" => [5, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro12" => [6, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                ];
                function allValuesAreZero($array)
                {
                    foreach ($array as $value) {
                        if ($value !== 0) {
                            return false;
                        }
                    }
                    return true;
                }

                foreach ($agingCount as $key => $subArray) {
                    if (allValuesAreZero($subArray)) {
                        unset($agingCount[$key]);
                    }
                }
                // dd($agingCount);
                return view('Dashboard/managerDashboard', compact('projects', 'totalAssignedCount', 'totalCompleteCount', 'totalPendingCount', 'totalHoldCount', 'totalReworkCount', 'totalCount', 'agingHeader', 'agingCount', 'agingData'));
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function getUsersWithSubProjects(Request $request)
    {
        try {
            $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
            $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $request->project_id,
            ];
            $client = new Client();
            $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_practices_users_on_client', [
                'json' => $payload,
            ]);
            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
            } else {
                return response()->json(['error' => 'API request failed'], $response->getStatusCode());
            }
            $subprojects = $data['practiceList'];
            $resourceList = $data['resourceList'];
            $clientDetails = $data['clientInfo'];
            $subProjectsWithCount = [];
            if (count($subprojects) > 0) {
                foreach ($subprojects as $key => $data) {
                    $projectName = $clientDetails['client_name'];
                    $table_name = Str::slug((Str::lower($projectName) . '_' . Str::lower($data['name'])), '_');
                    $modelName = Str::studly($table_name);
                    $modelClass = "App\\Models\\" . $modelName;
                    $calendarId = $request->CalendarId;
                    if ($calendarId == "year") {
                        $startDate = Carbon::now()->startOfYear()->toDateTimeString();
                        $endDate = Carbon::now()->endOfYear()->toDateTimeString();
                        $days = Carbon::now()->daysInYear;
                    } else if ($calendarId == "month") {
                        $days =  Carbon::now()->daysInMonth;
                        $startDate = Carbon::now()->startOfMonth()->toDateTimeString();
                        $endDate = Carbon::now()->endOfMonth()->toDateTimeString();
                    } else {
                        $startDate = Carbon::now()->startOfDay()->toDateTimeString();
                        $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                        $days = 0;
                    }
                    // $startDate = Carbon::now()->subDays($days)->startOfDay()->toDateTimeString();
                    // $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                    if (class_exists($modelClass)) {
                        $resourceData = $modelClass::whereIn('CE_emp_id', $resourceList)->select('CE_emp_id')->groupBy('CE_emp_id')->get()->toArray();
                        foreach ($resourceData as $resourceKey => $resourceDataVal) {
                            $subProjectsWithCount[$key][$resourceKey]['client_id'] = $clientDetails['id'];
                            $subProjectsWithCount[$key][$resourceKey]['client_name'] = $clientDetails['client_name'];
                            $subProjectsWithCount[$key][$resourceKey]['sub_project_id'] = $data['id'];
                            $subProjectsWithCount[$key][$resourceKey]['sub_project_name'] = $data['name'];
                            $subProjectsWithCount[$key][$resourceKey]['resource_emp_id'] = $resourceDataVal["CE_emp_id"];
                            $subProjectsWithCount[$key][$resourceKey]['assignedCount'] = $modelClass::where('chart_status', 'CE_Assigned')->whereNotNull('CE_emp_id')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])->whereBetween('updated_at', [$startDate, $endDate])->count();
                            $subProjectsWithCount[$key][$resourceKey]['CompletedCount'] = $modelClass::where('chart_status', 'CE_Completed')->where('qa_work_status', 'Sampling')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])->whereBetween('updated_at', [$startDate, $endDate])->count();
                            $subProjectsWithCount[$key][$resourceKey]['PendingCount'] = $modelClass::where('chart_status', 'CE_Pending')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])->whereBetween('updated_at', [$startDate, $endDate])->count();
                            $subProjectsWithCount[$key][$resourceKey]['holdCount'] = $modelClass::where('chart_status', 'CE_Hold')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])
                                ->where(function ($query) use ($startDate, $endDate, $days) {
                                    if ($days == 0) {
                                        $query;
                                    } else {
                                        $query->whereBetween('updated_at', [$startDate, $endDate]);
                                    }
                                })->count();
                        }
                    } else {
                        $subProjectsWithCount[$key][0]['client_id'] = $clientDetails['id'];
                        $subProjectsWithCount[$key][0]['client_name'] = $clientDetails['client_name'];
                        $subProjectsWithCount[$key][0]['sub_project_id'] = $data['id'];
                        $subProjectsWithCount[$key][0]['sub_project_name'] = $data['name'];
                        $subProjectsWithCount[$key][0]['assignedCount'] = '--';
                        $subProjectsWithCount[$key][0]['CompletedCount'] = '--';
                        $subProjectsWithCount[$key][0]['PendingCount'] = '--';
                        $subProjectsWithCount[$key][0]['holdCount'] = '--';
                        $subProjectsWithCount[$key][0]['resource_emp_id'] = '--';
                    }
                }
            } else {
                $projectName = $clientDetails['client_name'];
                $table_name = Str::slug((Str::lower($projectName) . '_' . 'project'), '_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $calendarId = $request->CalendarId;
                if ($calendarId == "year") {
                    $startDate = Carbon::now()->startOfYear()->toDateTimeString();
                    $endDate = Carbon::now()->endOfYear()->toDateTimeString();
                    $days = Carbon::now()->daysInYear;
                } else if ($calendarId == "month") {
                    $days =  Carbon::now()->daysInMonth;
                    $startDate = Carbon::now()->startOfMonth()->toDateTimeString();
                    $endDate = Carbon::now()->endOfMonth()->toDateTimeString();
                } else {
                    $days = 0;
                    $startDate = Carbon::now()->startOfDay()->toDateTimeString();
                    $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                }
                // $startDate = Carbon::now()->subDays($days)->startOfDay()->toDateTimeString();
                // $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                if (class_exists($modelClass)) {
                    $key = 0;
                    $resourceData = $modelClass::whereIn('CE_emp_id', $resourceList)->select('CE_emp_id')->groupBy('CE_emp_id')->get()->toArray();
                    foreach ($resourceData as $resourceKey => $resourceDataVal) {
                        $subProjectsWithCount[$key][$resourceKey]['client_id'] = $clientDetails['id'];
                        $subProjectsWithCount[$key][$resourceKey]['client_name'] = $clientDetails['client_name'];
                        $subProjectsWithCount[$key][$resourceKey]['sub_project_id'] = '--';
                        $subProjectsWithCount[$key][$resourceKey]['sub_project_name'] = '--';
                        $subProjectsWithCount[$key][$resourceKey]['resource_emp_id'] = $resourceDataVal["CE_emp_id"];
                        $subProjectsWithCount[$key][$resourceKey]['assignedCount'] = $modelClass::where('chart_status', 'CE_Assigned')->whereNotNull('CE_emp_id')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $subProjectsWithCount[$key][$resourceKey]['CompletedCount'] = $modelClass::where('chart_status', 'CE_Completed')->where('qa_work_status', 'Sampling')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $subProjectsWithCount[$key][$resourceKey]['PendingCount'] = $modelClass::where('chart_status', 'CE_Pending')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $subProjectsWithCount[$key][$resourceKey]['holdCount'] = $modelClass::where('chart_status', 'CE_Hold')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])
                            ->where(function ($query) use ($startDate, $endDate, $days) {
                                if ($days == 0) {
                                    $query;
                                } else {
                                    $query->whereBetween('updated_at', [$startDate, $endDate]);
                                }
                            })->count();
                    }
                } else {
                    $key = 0;
                    $subProjectsWithCount[$key][0]['client_id'] = $clientDetails['id'];
                    $subProjectsWithCount[$key][0]['client_name'] = $clientDetails['client_name'];
                    $subProjectsWithCount[$key][0]['sub_project_id'] = '--';
                    $subProjectsWithCount[$key][0]['sub_project_name'] = '--';
                    $subProjectsWithCount[$key][0]['assignedCount'] = '--';
                    $subProjectsWithCount[$key][0]['CompletedCount'] = '--';
                    $subProjectsWithCount[$key][0]['PendingCount'] = '--';
                    $subProjectsWithCount[$key][0]['holdCount'] = '--';
                    $subProjectsWithCount[$key][0]['resource_emp_id'] = '--';
                }
            }

            return response()->json(['subprojects' => $subProjectsWithCount]);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    public function getCalendarFilter(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $calendarId = $request->CalendarId;
                $userType = $request->type;
                if ($calendarId == "week") {
                    $days = 7;
                    $startDate = Carbon::now()->startOfWeek()->startOfDay()->toDateTimeString();
                    $endDate = Carbon::now()->endOfWeek()->endOfDay()->toDateTimeString();
                } else if ($calendarId == "month") {
                    $days =  Carbon::now()->daysInMonth;
                    $startDate = Carbon::now()->startOfMonth()->toDateTimeString();
                    $endDate = Carbon::now()->endOfMonth()->toDateTimeString();
                } else {
                    $days = $calendarId;
                    $startDate = Carbon::now()->startOfDay()->toDateTimeString();
                    $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                }
                // $startDate = Carbon::now()->subDays($days)->startOfDay()->toDateTimeString();
                // $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                $models = [];
                $projects = $this->getProjects();
                foreach ($projects as $project) {
                    if (count($project["subprject_name"]) > 0) {
                        foreach ($project["subprject_name"] as $key => $subProject) {
                            $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProject)), '_');
                            $modelName = Str::studly($table_name);
                            $modelClass = "App\\Models\\" . $modelName;
                            $models[] = $modelClass;
                        }
                    } else {
                        $subProjectText = "project";
                        $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProjectText)), '_');
                        $modelName = Str::studly($table_name);
                        $modelClass = "App\\Models\\" . $modelName;
                        $models[] = $modelClass;
                    }
                }
                $assignedCounts = $completeCounts = $pendingCounts = $holdCounts = $reworkCounts = $totalCounts = [];
                foreach ($models as $model) {
                    if (class_exists($model) && $userType == "user") {
                        $aCount = $model::where('chart_status', 'CE_Assigned')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $cCount = $model::where('chart_status', 'CE_Completed')->where('qa_work_status', 'Sampling')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $pCount = $model::where('chart_status', 'CE_Pending')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $hCount = $model::where('chart_status', 'CE_Hold')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $rCount = $model::where('chart_status', 'Revoke')->where('CE_emp_id', $loginEmpId)->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $assignedCounts[] = $aCount;
                        $completeCounts[] = $cCount;
                        $pendingCounts[] = $pCount;
                        $holdCounts[] = $hCount;
                        $reworkCounts[] = $rCount;
                    } else if (class_exists($model) && $userType == "manager") {
                        $aCount = $model::where('chart_status', 'CE_Assigned')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $cCount = $model::where('chart_status', 'CE_Completed')->where('qa_work_status', 'Sampling')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $pCount = $model::where('chart_status', 'CE_Pending')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $hCount = $model::where('chart_status', 'CE_Hold')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $rCount = $model::where('chart_status', 'Revoke')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $assignedCounts[] = $aCount;
                        $completeCounts[] = $cCount;
                        $pendingCounts[] = $pCount;
                        $holdCounts[] = $hCount;
                        $reworkCounts[] = $rCount;
                    }
                }
                $totalAssignedCount = array_sum($assignedCounts);
                $totalCompleteCount = array_sum($completeCounts);
                $totalPendingCount = array_sum($pendingCounts);
                $totalHoldCount = array_sum($holdCounts);
                $totalReworkCount = array_sum($reworkCounts);
                $totalCount = $totalAssignedCount + $totalCompleteCount + $totalPendingCount + $totalHoldCount + $totalReworkCount;
                return response()->json(['totalCount' => $totalCount, 'totalAssignedCount' => $totalAssignedCount, 'totalCompleteCount' => $totalCompleteCount, 'totalPendingCount' => $totalPendingCount, 'totalHoldCount' => $totalHoldCount, 'totalReworkCount' => $totalReworkCount]);
                return $totalCount;
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function getProjects()
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'user_id' => $userId,
                ];
                $client = new Client();
                $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_clients_on_user', [
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
        } else {
            return redirect('/');
        }
    }

    public function prjCalendarFilter(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $calendarId = $request->CalendarId;
                $projects = $this->getProjects();
                if ($calendarId == "year") {
                    // $days = Carbon::now()->daysInYear;
                    $startDate = Carbon::now()->startOfYear()->toDateTimeString();
                    $endDate = Carbon::now()->endOfYear()->toDateTimeString();
                } else if ($calendarId == "month") {
                    // $days =  Carbon::now()->daysInMonth;
                    $startDate = Carbon::now()->startOfMonth()->toDateTimeString();
                    $endDate = Carbon::now()->endOfMonth()->toDateTimeString();
                }
                // $startDate = Carbon::now()->subDays($days)->startOfDay()->toDateTimeString();
                // $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                $body_info = '<table class="table table-separate table-head-custom no-footer" id="uDashboard_clients_list">
                <thead>
                    <tr>
                        <th width="15px"></th>
                        <th>Client Name</th>
                        <th>Assigned</th>
                        <th>Completed</th>
                        <th>Pending</th>
                        <th>On Hold</th>
                    </tr>
                </thead>
                <tbody>';
                if (isset($projects) && count($projects) > 0) {
                    foreach ($projects as $data) {
                        $loginEmpId =
                            Session::get('loginDetails') &&
                            Session::get('loginDetails')['userDetail'] &&
                            Session::get('loginDetails')['userDetail']['emp_id'] != null
                            ? Session::get('loginDetails')['userDetail']['emp_id']
                            : '';
                        $projectName = $data['client_name'];
                        if (isset($data['subprject_name']) && !empty($data['subprject_name'])) {
                            $subproject_name = $data['subprject_name'];
                            $model_name = collect($subproject_name)
                                ->map(function ($item) use ($projectName) {
                                    return Str::studly(
                                        Str::slug(
                                            Str::lower($projectName) . '_' . Str::lower($item),
                                            '_',
                                        ),
                                    );
                                })
                                ->all();
                        } else {
                            $model_name = collect(
                                Str::studly(
                                    Str::slug(Str::lower($projectName) . '_project', '_'),
                                ),
                            );
                        }

                        $assignedTotalCount = 0;
                        $completedTotalCount = 0;
                        $pendingTotalCount = 0;
                        $holdTotalCount = 0;
                        $modelTFlag = 0;
                        foreach ($model_name as $model) {
                            $modelClass = 'App\\Models\\' . $model;
                            $assignedCount = 0;
                            $completedCount = 0;
                            $pendingCount = 0;
                            $holdCount = 0;
                            $modelFlag = 0;
                            if (class_exists($modelClass)) {
                                $assignedCount = $modelClass
                                    ::where(
                                        'chart_status',
                                        'CE_Assigned'
                                    )
                                    ->where('CE_emp_id', $loginEmpId)
                                    ->whereBetween('updated_at', [$startDate, $endDate])
                                    ->count();
                                $completedCount = $modelClass
                                    ::where('chart_status', 'CE_Completed')
                                    ->where('qa_work_status', 'Sampling')
                                    ->where('CE_emp_id', $loginEmpId)
                                    ->whereBetween('updated_at', [$startDate, $endDate])
                                    ->count();
                                $pendingCount = $modelClass
                                    ::where('chart_status', 'CE_Pending')
                                    ->where('CE_emp_id', $loginEmpId)
                                    ->whereBetween('updated_at', [$startDate, $endDate])
                                    ->count();
                                $holdCount = $modelClass
                                    ::where('chart_status', 'CE_Hold')
                                    ->where('CE_emp_id', $loginEmpId)
                                    ->whereBetween('updated_at', [$startDate, $endDate])
                                    ->count();
                                $modelFlag = 1;
                            } else {
                                $assignedCount = 0;
                                $completedCount = 0;
                                $pendingCount = 0;
                                $holdCount = 0;
                                $modelFlag = 0;
                            }
                            $assignedTotalCount += $assignedCount;
                            $completedTotalCount += $completedCount;
                            $pendingTotalCount += $pendingCount;
                            $holdTotalCount += $holdCount;
                            $modelTFlag += $modelFlag;
                        }
                        if ($modelTFlag > 0) {
                            $body_info .= '<tr class="clickable-client cursor_hand"><td class="details-control"></td>';
                            $body_info .= '<td>' . $data['client_name'] . '<input type="hidden" value=' . $data['id'] . '></td>';
                            $body_info .= '<td>' . $assignedTotalCount . '</td>';
                            $body_info .= '<td>' . $completedTotalCount . '</td>';
                            $body_info .= '<td>' . $pendingTotalCount . '</td>';
                            $body_info .= '<td>' . $holdTotalCount . '</td>';
                            $body_info .= '</tr>';
                        }
                    }
                }

                $body_info .= '</tbody></table>';
                return response()->json([
                    'success' => true,
                    'body_info' => $body_info,
                ]);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function mgrPrjCalendarFilter(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $calendarId = $request->CalendarId;
                $projects = $this->getProjects();
                if ($calendarId == "year") {
                    // $days = Carbon::now()->daysInYear;
                    $startDate = Carbon::now()->startOfYear()->toDateTimeString();
                    $endDate = Carbon::now()->endOfYear()->toDateTimeString();
                } else if ($calendarId == "month") {
                    // $days =  Carbon::now()->daysInMonth;
                    $startDate = Carbon::now()->startOfMonth()->toDateTimeString();
                    $endDate = Carbon::now()->endOfMonth()->toDateTimeString();
                }
                // $startDate = Carbon::now()->subDays($days)->startOfDay()->toDateTimeString();
                // $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                $body_info = '<table class="table table-separate table-head-custom no-footer" id="mDashboard_clients_list">
                <thead>
                    <tr>
                        <th width="15px"></th>
                        <th>Client Name</th>
                        <th>Assigned</th>
                        <th>Completed</th>
                        <th>Pending</th>
                        <th>On Hold</th>
                    </tr>
                </thead>
                <tbody>';
                if (isset($projects) && count($projects) > 0) {
                    foreach ($projects as $data) {
                        $projectName = $data['client_name'];
                        if (isset($data['subprject_name']) && !empty($data['subprject_name'])) {
                            $subproject_name = $data['subprject_name'];
                            $model_name = collect($subproject_name)
                                ->map(function ($item) use ($projectName) {
                                    return Str::studly(
                                        Str::slug(
                                            Str::lower($projectName) . '_' . Str::lower($item),
                                            '_',
                                        ),
                                    );
                                })
                                ->all();
                        } else {
                            $model_name = collect(
                                Str::studly(
                                    Str::slug(Str::lower($projectName) . '_project', '_'),
                                ),
                            );
                        }

                        $assignedTotalCount = 0;
                        $completedTotalCount = 0;
                        $pendingTotalCount = 0;
                        $holdTotalCount = 0;
                        $modelTFlag = 0;
                        foreach ($model_name as $model) {
                            $modelClass = 'App\\Models\\' . $model;
                            $assignedCount = 0;
                            $completedCount = 0;
                            $pendingCount = 0;
                            $holdCount = 0;
                            $modelFlag = 0;

                            if (class_exists($modelClass)) {
                                $assignedCount = $modelClass
                                    ::where('chart_status', 'CE_Assigned')
                                    ->whereNotNull('CE_emp_id')
                                    ->whereBetween('updated_at', [$startDate, $endDate])
                                    ->count();
                                $completedCount = $modelClass
                                    ::where('chart_status', 'CE_Completed')
                                    ->where('qa_work_status', 'Sampling')
                                    ->whereBetween('updated_at', [$startDate, $endDate])
                                    ->count();
                                $pendingCount = $modelClass
                                    ::where('chart_status', 'CE_Pending')
                                    ->whereBetween('updated_at', [$startDate, $endDate])
                                    ->count();
                                $holdCount = $modelClass
                                    ::where('chart_status', 'CE_Hold')
                                    ->whereBetween('updated_at', [$startDate, $endDate])
                                    ->count();
                                $modelFlag = 1;
                            } else {
                                $assignedCount = 0;
                                $completedCount = 0;
                                $pendingCount = 0;
                                $holdCount = 0;
                                $modelFlag = 0;
                            }

                            $assignedTotalCount += $assignedCount;
                            $completedTotalCount += $completedCount;
                            $pendingTotalCount += $pendingCount;
                            $holdTotalCount += $holdCount;
                            $modelTFlag += $modelFlag;
                        }
                        if ($modelTFlag > 0) {
                            $body_info .= '<tr class="clickable-client cursor_hand"><td class="details-control"></td>';
                            $body_info .= '<td>' . $data['client_name'] . '<input type="hidden" value=' . $data['id'] . '></td>';
                            $body_info .= '<td>' . $assignedTotalCount . '</td>';
                            $body_info .= '<td>' . $completedTotalCount . '</td>';
                            $body_info .= '<td>' . $pendingTotalCount . '</td>';
                            $body_info .= '<td>' . $holdTotalCount . '</td>';
                            $body_info .= '</tr>';
                        }
                    }
                }

                $body_info .= '</tbody></table>';
                return response()->json([
                    'success' => true,
                    'body_info' => $body_info,
                ]);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function procodeManagerChartDashboard()
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $agingHeader = Aging::select('days', 'days_range')->get()->toArray();
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $projects = $this->getProjects();
                $startDate = Carbon::now()->startOfDay()->toDateTimeString();
                $endDate = Carbon::now()->endOfDay()->toDateTimeString();
                $models = $projectIds = [];
                foreach ($projects as $project) {
                    if (count($project["subprject_name"]) > 0) {
                        foreach ($project["subprject_name"] as $key => $subProject) {
                            $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProject)), '_');
                            $modelName = Str::studly($table_name);
                            $modelClass = "App\\Models\\" . $modelName;
                            $models[] = $modelClass;
                            $projectIds[] = $project["client_name"];
                        }
                    } else {
                        $subProjectText = "project";
                        $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProjectText)), '_');
                        $modelName = Str::studly($table_name);
                        $modelClass = "App\\Models\\" . $modelName;
                        $models[] = $modelClass;
                        $projectIds[] = $project["client_name"];
                    }
                }
                $assignedCounts = $completeCounts = $pendingCounts = $holdCounts = $reworkCounts = $totalCounts = $agingArr1 = $agingArr2 = $agingCount = [];
                foreach ($models as $modelKey => $model) {
                    if (class_exists($model)) {
                        $aCount = $model::where('chart_status', 'CE_Assigned')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $cCount = $model::where('chart_status', 'CE_Completed')->where('qa_work_status', 'Sampling')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $pCount = $model::where('chart_status', 'CE_Pending')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $hCount = $model::where('chart_status', 'CE_Hold')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $rCount = $model::where('chart_status', 'Revoke')->whereBetween('updated_at', [$startDate, $endDate])->count();
                        $assignedCounts[] = $aCount;
                        $completeCounts[] = $cCount;
                        $pendingCounts[] = $pCount;
                        $holdCounts[] = $hCount;
                        $reworkCounts[] = $rCount;
                        foreach ($agingHeader as $key => $data) {
                            // $startDay = $data["days"] - 1;
                            // $endDumDay = isset($agingHeader[$key - 1]) &&  isset($agingHeader[$key - 1]["days"]) ? $agingHeader[$key - 1]["days"]  : "0";
                           if(str_contains($data["days_range"],'-')) {
                                $splitRange = explode('-', $data["days_range"]);
                                $startDay = $splitRange[1]-1;
                                $endDumDay =  $splitRange[0]-1;
                                $startDate = Carbon::now()->subDays($startDay)->startOfDay()->toDateTimeString();
                                $endDate = Carbon::now()->subDays($endDumDay)->endOfDay()->toDateTimeString();
                                $dataCount = $model::where('chart_status', 'CE_Assigned')->whereBetween('created_at', [$startDate, $endDate])->count();
                            } else {
                                $splitRange = explode('+', $data["days_range"]);
                                $endDumDay =  $splitRange[0]-1;
                                $startDay =  $splitRange[1] != "" ? $splitRange[1]-1 : $endDumDay +1;
                                $endDate = Carbon::now()->subDays($endDumDay)->endOfDay()->toDateTimeString();
                                $dataCount = $model::where('chart_status', 'CE_Assigned')->where('created_at', '<=', $endDate)->count();
                            }
                            $agingArr1[$modelKey][$data["days_range"]] = $dataCount;
                            $agingArr2[$modelKey] = $projectIds[$modelKey];
                        }
                    }
                }

                foreach ($agingArr2 as $key => $value) {
                    if (!isset($agingCount[$value])) {
                        $agingCount[$value] = [];
                    }
                    foreach ($agingArr1[$key] as $innerKey => $innerValue) {
                        if (!isset($agingCount[$value][$innerKey])) {
                            $agingCount[$value][$innerKey] = 0;
                        }
                        $agingCount[$value][$innerKey] += $innerValue;
                    }
                }

                $totalAssignedCount = array_sum($assignedCounts);
                $totalCompleteCount = array_sum($completeCounts);
                $totalPendingCount = array_sum($pendingCounts);
                $totalHoldCount = array_sum($holdCounts);
                $totalReworkCount = array_sum($reworkCounts);
                $totalCount = $totalAssignedCount + $totalCompleteCount + $totalPendingCount + $totalHoldCount + $totalReworkCount;

                $agingData = [
                    'AMBC' => [20, 10, 30, 100],
                    //  'Cancer Care Specialists' => [30, 10, 0, 0, 0, 0, 0, 11, 0, 45, 45],
                    // "Saco River Medical Group" => [50, 0, 0, 0, 0, 0, 0, 12, 0, 45, 45],
                    // "AIG" => [250, 0, 0, 0, 0, 70, 0, 12, 0, 45, 45],
                    // "Ash Meomorial Hospital" => [250, 0, 0, 0, 0, 0, 0, 12, 0, 45, 45],
                    // "MDCSp" => [230, 0, 0, 0, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro" => [140, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro1" => [100, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro2" => [200, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro3" => [50, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro4" => [40, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro5" => [30, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro6" => [10, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro7" => [1, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro8" => [2, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro9" => [3, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro10" => [4, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro11" => [5, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro12" => [6, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                ];
                function allValuesAreZero($array)
                {
                    foreach ($array as $value) {
                        if ($value !== 0) {
                            return false;
                        }
                    }
                    return true;
                }

                foreach ($agingCount as $key => $subArray) {
                    if (allValuesAreZero($subArray)) {
                        unset($agingCount[$key]);
                    }
                }
                // dd($agingCount);
                return view('Dashboard/managerChartDashboard', compact('projects', 'totalAssignedCount', 'totalCompleteCount', 'totalPendingCount', 'totalHoldCount', 'totalReworkCount', 'totalCount', 'agingHeader', 'agingCount', 'agingData'));
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
}
