<?php

namespace App\Http\Controllers;

use App\Models\Aging;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Helper\Admin\Helpers as Helpers;
use App\Models\InventoryExeFile;
use App\Models\ProjectReason;
use App\Jobs\GetProjJob;
use Illuminate\Support\Facades\Cache;
use App\Jobs\GetSubPrjJob;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Coordinator') !== false)) {
                    return $this->procodeManagerDashboard();
                } else {

                    return $this->procodeUserDashboard();
                }
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
        if (
            Session::get('loginDetails') &&
            Session::get('loginDetails')['userDetail'] &&
            Session::get('loginDetails')['userDetail']['emp_id'] != null
        ) {
            try {
                $loginEmpId = Session::get('loginDetails')['userDetail']['emp_id'] ?? "";
                $userId = Session::get('loginDetails')['userDetail']['id'] ?? "";

                $agingHeader = Aging::select('days', 'days_range')->get()->toArray();
                $projects = $this->getProjects();

                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->endOfMonth()->endOfDay();

                /* ---------------- CACHE FORM CONFIG ---------------- */
                $allowedConfigs = Cache::remember('form_configurations_map', 3600, function () {
                    return DB::table('form_configurations')
                        ->whereNull('deleted_at')
                        ->get()
                        ->groupBy('project_id')
                        ->map(fn($rows) => $rows->pluck('sub_project_id')->toArray())
                        ->toArray();
                });

                /* ---------------- CACHE PROJECT NAMES ---------------- */
                $projectNameCache = [];

                /* ---------------- PREPARE AGING RANGES ---------------- */
                $agingRanges = [];

                foreach ($agingHeader as $data) {
                    if (str_contains($data["days_range"], '-')) {
                        [$end, $start] = explode('-', $data["days_range"]);
                        $startDateCalc = Carbon::now();
                        $endDateCalc = Carbon::now();
                        for ($i = 0; $i < ($start - 1); $i++) {
                            $startDateCalc->subDay();
                            while ($startDateCalc->isWeekend()) $startDateCalc->subDay();
                        }
                        for ($i = 0; $i < ($end - 1); $i++) {
                            $endDateCalc->subDay();
                            while ($endDateCalc->isWeekend()) $endDateCalc->subDay();
                        }
                        $agingRanges[$data["days_range"]] = [
                            'type' => 'between',
                            'start' => $startDateCalc->startOfDay(),
                            'end' => $endDateCalc->endOfDay(),
                        ];

                    } else {
                        [$end] = explode('+', $data["days_range"]);
                        $endDateCalc = Carbon::now();
                        for ($i = 0; $i < ($end - 1); $i++) {
                            $endDateCalc->subDay();
                            while ($endDateCalc->isWeekend()) $endDateCalc->subDay();
                        }
                        $agingRanges[$data["days_range"]] = [
                            'type' => 'lte',
                            'end' => $endDateCalc->endOfDay(),
                        ];
                    }
                }
                /* ---------------- BUILD MODELS ---------------- */
                $models = [];
                $projectIds = [];
                foreach ($projects as $project) {
                    $projectId = $project['id'];
                    if (!isset($projectNameCache[$projectId])) {
                        $daProject = Helpers::projectName($projectId);
                        $projectNameCache[$projectId] = $daProject ? $daProject->project_name : null;
                    }
                    $clientName = $projectNameCache[$projectId];
                    $subProjects = $project['subprject_name'] ?? [];
                    // ✅ fallback fix
                    if (empty($subProjects)) {
                        $subProjects = [0 => 'project'];
                    }
                    foreach ($subProjects as $subId => $subName) {
                        // ✅ config filter (safe)
                        if (isset($allowedConfigs[$projectId]) &&
                            !in_array($subId, $allowedConfigs[$projectId])) {
                            continue;
                        }
                        $table_name = Str::slug(
                            Str::lower($clientName . '_' . $subName),
                            '_'
                        );
                        $modelClass = "App\\Models\\" . Str::studly($table_name);
                        if (class_exists($modelClass)) {
                            $models[] = $modelClass;
                            $projectIds[] = $clientName;
                        }
                    }
                }

                /* ---------------- INIT ---------------- */
                $assignedCounts = $completeCounts = $pendingCounts =
                $holdCounts = $reworkCounts =
                $agingCount = [];

                /* ---------------- MAIN LOOP ---------------- */
                foreach ($models as $index => $model) {

                    /* ===== SINGLE QUERY ===== */
                    $rows = $model::where('CE_emp_id', $loginEmpId)
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->get(['chart_status', 'invoke_date']);

                    $assigned = $completed = $pending = $hold = $rework = 0;

                    $agingLocal = array_fill_keys(array_column($agingHeader, 'days_range'), 0);

                    foreach ($rows as $row) {

                        /* ---- STATUS ---- */
                        if ($row->chart_status === 'CE_Assigned') $assigned++;
                        if ($row->chart_status === 'CE_Completed') $completed++;
                        if ($row->chart_status === 'CE_Pending') $pending++;
                        if ($row->chart_status === 'CE_Hold') $hold++;
                        if ($row->chart_status === 'Revoke') $rework++;

                        /* ---- AGING ---- */
                        if ($row->chart_status === 'CE_Assigned') {

                            foreach ($agingRanges as $rangeKey => $range) {

                                if ($range['type'] === 'between') {
                                    if ($row->invoke_date >= $range['start'] && $row->invoke_date <= $range['end']) {
                                        $agingLocal[$rangeKey]++;
                                    }
                                } else {
                                    if ($row->invoke_date <= $range['end']) {
                                        $agingLocal[$rangeKey]++;
                                    }
                                }
                            }
                        }
                    }

                    $assignedCounts[] = $assigned;
                    $completeCounts[] = $completed;
                    $pendingCounts[] = $pending;
                    $holdCounts[] = $hold;
                    $reworkCounts[] = $rework;

                    /* ---- MERGE AGING ---- */
                    $projectName = $projectIds[$index];

                    if (!isset($agingCount[$projectName])) {
                        $agingCount[$projectName] = [];
                    }

                    foreach ($agingLocal as $k => $v) {
                        if (!isset($agingCount[$projectName][$k])) {
                            $agingCount[$projectName][$k] = 0;
                        }
                        $agingCount[$projectName][$k] += $v;
                    }
                }

                /* ---------------- TOTALS ---------------- */
                $totalAssignedCount = array_sum($assignedCounts);
                $totalCompleteCount = array_sum($completeCounts);
                $totalPendingCount = array_sum($pendingCounts);
                $totalHoldCount = array_sum($holdCounts);
                $totalReworkCount = array_sum($reworkCounts);

                $totalCount =
                    $totalAssignedCount +
                    $totalCompleteCount +
                    $totalPendingCount +
                    $totalHoldCount +
                    $totalReworkCount;

                /* ---------------- CLEAN ZERO AGING ---------------- */
                foreach ($agingCount as $key => $subArray) {
                    if (!array_filter($subArray)) {
                        unset($agingCount[$key]);
                    }
                }

                return view('Dashboard/userDashboard', compact(
                    'projects',
                    'totalAssignedCount',
                    'totalCompleteCount',
                    'totalPendingCount',
                    'totalHoldCount',
                    'totalReworkCount',
                    'totalCount',
                    'agingHeader',
                    'agingCount'
                ));

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

            $loginEmpId = Session::get('loginDetails')['userDetail']['emp_id'] ?? "";
            $empDesignation = Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] ?? "";

            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $request->project_id,
            ];

            $client = new Client(['verify' => false]);
            $response = $client->request(
                'POST',
                config("constants.PRO_CODE_URL") . '/api/v1_users/get_practice_on_client',
                ['json' => $payload]
            );

            if ($response->getStatusCode() != 200) {
                return response()->json(['error' => 'API request failed'], $response->getStatusCode());
            }

            $data = json_decode($response->getBody(), true);
            $data['practiceList'] = Helpers::filterPracticeList(
                $data['practiceList'] ?? [],
                $data['clientInfo']['id'] ?? null
            );
            $subprojects = $data['practiceList'] ?? [];
            $clientDetails = $data['clientInfo'];
            $subProjectsWithCount = [];
            $calendarId = $request->CalendarId;

            /* ---------------- CACHE PROJECT NAME ONCE ---------------- */
            $daProject = Helpers::projectName($clientDetails["id"]);
            $daPrjName = $daProject ? $daProject->project_name : null;

            /* ---------------- PRE-CALCULATE DATE RANGE ONCE ---------------- */
            if ($calendarId == "year") {
                $days = Carbon::now()->daysInYear;
                $startDate = Carbon::now()->startOfYear()->toDateString();
                $endDate = Carbon::now()->endOfYear()->toDateString();
            } elseif ($calendarId == "month") {
                $days = Carbon::now()->daysInMonth;
                $startDate = Carbon::now()->startOfMonth()->toDateString();
                $endDate = Carbon::now()->endOfMonth()->toDateString();
            } else {
                $days = 0;
                $startDate = Carbon::now()->startOfDay()->toDateString();
                $endDate = Carbon::now()->endOfDay()->toDateString();
            }

            /* ---------------- MAIN LOOP ---------------- */
                foreach ($subprojects as $key => $data) {
                    $subProjectsWithCount[$key]['client_id'] = $clientDetails['id'];
                    $subProjectsWithCount[$key]['client_name'] = $daPrjName;
                    $subProjectsWithCount[$key]['sub_project_id'] = $data['id'];
                    $subProjectsWithCount[$key]['sub_project_name'] = $data['name'];
                    $projectName = $subProjectsWithCount[$key]['client_name'];
                    $table_name = Str::slug(
                        Str::lower($projectName . '_' . $subProjectsWithCount[$key]['sub_project_name']),
                        '_'
                    );
                    $modelName = Str::studly($table_name);
                    $modelClass = "App\\Models\\" . $modelName;
                        if (class_exists($modelClass)) {
                            /* ---------------- SINGLE BASE FILTER ---------------- */
                            $baseQuery = $modelClass::where('CE_emp_id', $loginEmpId)
                                ->whereBetween('invoke_date', [$startDate, $endDate]);

                            /* ---------------- KEEP ORIGINAL COUNT BEHAVIOR ---------------- */
                            $subProjectsWithCount[$key]['assignedCount'] =
                                (clone $baseQuery)->where('chart_status', 'CE_Assigned')->count();

                            $subProjectsWithCount[$key]['CompletedCount'] =
                                (clone $baseQuery)->where('chart_status', 'CE_Completed')->count();

                            $subProjectsWithCount[$key]['PendingCount'] =
                                (clone $baseQuery)->where('chart_status', 'CE_Pending')->count();

                            /* ---------------- HOLD (UNCHANGED LOGIC EXACTLY) ---------------- */
                            $subProjectsWithCount[$key]['holdCount'] =
                                $modelClass::where('chart_status', 'CE_Hold')
                                ->where('CE_emp_id', $loginEmpId)
                                ->where(function ($query) use ($startDate, $endDate, $days) {
                                    if ($days == 0) {
                                        $query;
                                    } else {
                                        $query->whereBetween('invoke_date', [$startDate, $endDate]);
                                    }
                                })
                                ->count();
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
        if (Session::get('loginDetails') &&
            Session::get('loginDetails')['userDetail'] &&
            Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails')['userDetail']['emp_id'] ?? "";
                $userId = Session::get('loginDetails')['userDetail']['id'] ?? "";
                $agingHeader = Aging::select('days', 'days_range')->get()->toArray();
                $projects = $this->getProjects();

                $startDate = Carbon::now()->startOfMonth()->startOfDay()->toDateString();
                $endDate = Carbon::now()->endOfMonth()->endOfDay()->toDateString();

                /* ---------------- CACHE FORM CONFIG ---------------- */
                $allowedConfigs = Cache::remember('form_configurations_map', 3600, function () {
                    return DB::table('form_configurations')
                        ->whereNull('deleted_at')
                        ->get()
                        ->groupBy('project_id')
                        ->map(function ($rows) {
                            return $rows->pluck('sub_project_id')->toArray();
                        })
                        ->toArray();
                });

                /* ---------------- CACHE PROJECT NAMES ---------------- */
                $projectNameCache = [];

                /* ---------------- PRE-CALCULATE AGING RANGES ---------------- */
                $agingRanges = [];

                foreach ($agingHeader as $data) {

                    if (str_contains($data["days_range"], '-')) {

                        $splitRange = explode('-', $data["days_range"]);
                        $startDay = $splitRange[1] - 1;
                        $endDay = $splitRange[0] - 1;

                        $start = Carbon::now();
                        $end = Carbon::now();

                        for ($i = 0; $i < $startDay; $i++) {
                            $start->subDay();
                            while ($start->isWeekend()) $start->subDay();
                        }

                        for ($i = 0; $i < $endDay; $i++) {
                            $end->subDay();
                            while ($end->isWeekend()) $end->subDay();
                        }

                        $agingRanges[$data["days_range"]] = [
                            'type' => 'between',
                            'start' => $start->startOfDay()->toDateString(),
                            'end' => $end->endOfDay()->toDateString(),
                        ];

                    } else {

                        $splitRange = explode('+', $data["days_range"]);
                        $endDay = $splitRange[0] - 1;

                        $end = Carbon::now();

                        for ($i = 0; $i < $endDay; $i++) {
                            $end->subDay();
                            while ($end->isWeekend()) $end->subDay();
                        }

                        $agingRanges[$data["days_range"]] = [
                            'type' => 'lte',
                            'end' => $end->endOfDay()->toDateString(),
                        ];
                    }
                }

                /* ---------------- BUILD MODELS ---------------- */
                $models = [];
                $projectIds = [];

                foreach ($projects as $project) {

                    $projectId = $project['id'];

                    if (!isset($allowedConfigs[$projectId])) {
                        continue;
                    }

                    if (!isset($projectNameCache[$projectId])) {
                        $daProject = Helpers::projectName($projectId);
                        $projectNameCache[$projectId] = $daProject ? $daProject->project_name : null;
                    }

                    $clientName = $projectNameCache[$projectId];
                    $subProjects = $project['subprject_name'] ?? [];

                    foreach ($subProjects as $subId => $subName) {

                        if (!in_array($subId, $allowedConfigs[$projectId])) {
                            continue;
                        }

                        $table_name = Str::slug(
                            Str::lower($clientName . '_' . $subName),
                            '_'
                        );

                        $modelClass = "App\\Models\\" . Str::studly($table_name);
                        $models[] = $modelClass;
                        $projectIds[] = $clientName;
                    }
                }

                /* ---------------- INIT ARRAYS ---------------- */
                $assignedCounts = $completeCounts = $pendingCounts =
                $holdCounts = $reworkCounts = $unAssignedCounts =
                $agingArr1 = $agingArr2 = $agingCount = [];

                /* ---------------- MAIN LOOP ---------------- */
                foreach ($models as $modelKey => $model) {
                    if (!class_exists($model)) {
                        continue;
                    }

                    /* ===== OPTIMIZED STATUS COUNTS (1 QUERY) ===== */
                    $baseQuery = $model::whereBetween('invoke_date', [$startDate, $endDate]);

                    $counts = $baseQuery->selectRaw("
                        SUM(CASE WHEN chart_status IN ('CE_Assigned','CE_Inprocess') AND CE_emp_id IS NOT NULL THEN 1 ELSE 0 END) as assigned,
                        SUM(CASE WHEN chart_status = 'CE_Completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN chart_status = 'CE_Pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN chart_status = 'CE_Hold' THEN 1 ELSE 0 END) as hold,
                        SUM(CASE WHEN chart_status = 'Revoke' THEN 1 ELSE 0 END) as rework,
                        SUM(CASE WHEN chart_status = 'CE_Assigned' AND CE_emp_id IS NULL THEN 1 ELSE 0 END) as unassigned
                    ")->first();

                    $assignedCounts[] = $counts->assigned;
                    $completeCounts[] = $counts->completed;
                    $pendingCounts[] = $counts->pending;
                    $holdCounts[] = $counts->hold;
                    $reworkCounts[] = $counts->rework;
                    $unAssignedCounts[] = $counts->unassigned;

                    /* ===== AGING CALCULATION (OPTIMIZED, SAME LOGIC) ===== */
                    foreach ($agingHeader as $data) {

                        $range = $agingRanges[$data["days_range"]];

                        if ($range['type'] === 'between') {

                            $dataCount = $model::where('chart_status', 'CE_Assigned')
                                ->whereNotNull('CE_emp_id')
                                ->whereBetween('invoke_date', [$range['start'], $range['end']])
                                ->count();

                        } else {

                            $dataCount = $model::where('chart_status', 'CE_Assigned')
                                ->whereNotNull('CE_emp_id')
                                ->where('invoke_date', '<=', $range['end'])
                                ->count();
                        }

                        $agingArr1[$modelKey][$data["days_range"]] = $dataCount;
                        $agingArr2[$modelKey] = $projectIds[$modelKey];
                    }
                }

                /* ---------------- FINAL AGGREGATION ---------------- */
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

                /* ---------------- TOTALS ---------------- */
                $totalAssignedCount = array_sum($assignedCounts);
                $totalCompleteCount = array_sum($completeCounts);
                $totalPendingCount = array_sum($pendingCounts);
                $totalHoldCount = array_sum($holdCounts);
                $totalReworkCount = array_sum($reworkCounts);
                $totalUnAssignedCounts = array_sum($unAssignedCounts);

                $totalCount =
                    $totalAssignedCount +
                    $totalCompleteCount +
                    $totalPendingCount +
                    $totalHoldCount +
                    $totalReworkCount +
                    $totalUnAssignedCounts;

                /* ---------------- REMOVE ZERO AGING ---------------- */
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

                $agingData = [
                    'AMBC' => [50, 0, 0, 0, 0, 100, 0, 153, 0, 45, 45],
                    'Cancer Care Specialists' => [50, 0, 0, 0, 0, 0, 0, 11, 0, 45, 45],
                    "Saco River Medical Group" => [50, 0, 0, 0, 0, 0, 0, 12, 0, 45, 45],
                ];

                return view('Dashboard/managerDashboard', compact(
                    'projects',
                    'totalAssignedCount',
                    'totalCompleteCount',
                    'totalPendingCount',
                    'totalHoldCount',
                    'totalReworkCount',
                    'totalCount',
                    'agingHeader',
                    'agingCount',
                    'agingData'
                ));
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

                $loginEmpId = Session::get('loginDetails')['userDetail']['emp_id'] ?? "";
                $empDesignation = Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] ?? "";

                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'client_id' => $request->project_id,
                ];

                $client = new Client(['verify' => false]);
                $response = $client->request(
                    'POST',
                    config("constants.PRO_CODE_URL") . '/api/v1_users/get_practices_users_on_client',
                    ['json' => $payload]
                );

                if ($response->getStatusCode() != 200) {
                    return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                }

                $data = json_decode($response->getBody(), true);
                $data['practiceList'] = Helpers::filterPracticeList(
                        $data['practiceList'] ?? [],
                        $data['clientInfo']['id'] ?? null
                    );
                $subprojects = $data['practiceList'] ?? [];
                $resourceList = $data['resourceList'] ?? [];
                $clientDetails = $data['clientInfo'];

                $subProjectsWithCount = [];

                /* ---------------- CACHE PROJECT NAME ONCE ---------------- */
                $projectData = Helpers::projectName($clientDetails["id"]);
                $projectName = $projectData ? $projectData->project_name : null;

                /* ---------------- DATE LOGIC (UNCHANGED EXACTLY) ---------------- */
                $calendarId = $request->CalendarId;

                if ($calendarId == "year") {
                    $startDate = Carbon::now()->startOfYear()->toDateString();
                    $endDate = Carbon::now()->endOfYear()->toDateString();
                    $days = Carbon::now()->daysInYear;
                } elseif ($calendarId == "month") {
                    $startDate = Carbon::now()->startOfMonth()->toDateString();
                    $endDate = Carbon::now()->endOfMonth()->toDateString();
                    $days = Carbon::now()->daysInMonth;
                } else {
                    $startDate = Carbon::now()->startOfDay()->toDateString();
                    $endDate = Carbon::now()->endOfDay()->toDateString();
                    $days = 0;
                }

                /* =========================================================
                * MAIN LOOP (UNCHANGED STRUCTURE)
                * ========================================================= */
                if (count($subprojects) > 0) {

                    foreach ($subprojects as $key => $subProjectData) {
                        
                        $table_name = Str::slug(
                            Str::lower($projectName . '_' . $subProjectData['name']),
                            '_'
                        );

                        $modelName = Str::studly($table_name);
                        $modelClass = "App\\Models\\" . $modelName;

                        if (class_exists($modelClass)) {

                            $resourceData = $modelClass::whereIn('CE_emp_id', $resourceList)
                                ->select('CE_emp_id')
                                ->groupBy('CE_emp_id')
                                ->get()
                                ->toArray();

                            foreach ($resourceData as $resourceKey => $resourceDataVal) {

                                /* 🔥 DO NOT CALL Helpers AGAIN (SAFE OPTIMIZATION) */
                                $subProjectsWithCount[$key][$resourceKey]['client_id'] = $clientDetails['id'];
                                $subProjectsWithCount[$key][$resourceKey]['client_name'] = $projectName;
                                $subProjectsWithCount[$key][$resourceKey]['sub_project_id'] = $subProjectData['id'];
                                $subProjectsWithCount[$key][$resourceKey]['sub_project_name'] = $subProjectData['name'];
                                                                $subProjectsWithCount[$key][$resourceKey]['resource_emp_id'] = $resourceDataVal["CE_emp_id"];

                                /* ===== ORIGINAL QUERIES (UNCHANGED LOGIC) ===== */
                                $subProjectsWithCount[$key][$resourceKey]['assignedCount'] =
                                    $modelClass::where('chart_status', 'CE_Assigned')
                                    ->whereNotNull('CE_emp_id')
                                    ->where('CE_emp_id', $resourceDataVal["CE_emp_id"])
                                    ->whereBetween('invoke_date', [$startDate, $endDate])
                                    ->count();

                                $subProjectsWithCount[$key][$resourceKey]['CompletedCount'] =
                                    $modelClass::where('chart_status', 'CE_Completed')
                                    ->where('CE_emp_id', $resourceDataVal["CE_emp_id"])
                                    ->whereBetween('invoke_date', [$startDate, $endDate])
                                    ->count();

                                $subProjectsWithCount[$key][$resourceKey]['PendingCount'] =
                                    $modelClass::where('chart_status', 'CE_Pending')
                                    ->where('CE_emp_id', $resourceDataVal["CE_emp_id"])
                                    ->whereBetween('invoke_date', [$startDate, $endDate])
                                    ->count();

                                $subProjectsWithCount[$key][$resourceKey]['holdCount'] =
                                    $modelClass::where('chart_status', 'CE_Hold')
                                    ->where('CE_emp_id', $resourceDataVal["CE_emp_id"])
                                    ->where(function ($query) use ($startDate, $endDate, $days) {
                                        if ($days != 0) {
                                            $query->whereBetween('invoke_date', [$startDate, $endDate]);
                                        }
                                    })
                                    ->count();
                            }
                        } else {

                            $subProjectsWithCount[$key][0] = [
                                'client_id' => $clientDetails['id'],
                                'client_name' => $projectName,
                                'sub_project_id' => $subProjectData['id'],
                                 'sub_project_name' => $subProjectData['name'],
                                'assignedCount' => '--',
                                'CompletedCount' => '--',
                                'PendingCount' => '--',
                                'holdCount' => '--',
                                'resource_emp_id' => '--',
                            ];
                        }
                    }
                } else {

                    $table_name = Str::slug(Str::lower($projectName . '_project'), '_');
                    $modelName = Str::studly($table_name);
                    $modelClass = "App\\Models\\" . $modelName;

                    if (class_exists($modelClass)) {

                        $key = 0;

                        $resourceData = $modelClass::whereIn('CE_emp_id', $resourceList)
                            ->select('CE_emp_id')
                            ->groupBy('CE_emp_id')
                            ->get()
                            ->toArray();

                        foreach ($resourceData as $resourceKey => $resourceDataVal) {

                            $subProjectsWithCount[$key][$resourceKey]['client_id'] = $clientDetails['id'];
                            $subProjectsWithCount[$key][$resourceKey]['client_name'] = $projectName;
                            $subProjectsWithCount[$key][$resourceKey]['sub_project_id'] = '--';
                            $subProjectsWithCount[$key][$resourceKey]['sub_project_name'] = '--';
                            $subProjectsWithCount[$key][$resourceKey]['resource_emp_id'] = $resourceDataVal["CE_emp_id"];

                            $subProjectsWithCount[$key][$resourceKey]['assignedCount'] =
                                $modelClass::where('chart_status', 'CE_Assigned')
                                ->whereNotNull('CE_emp_id')
                                ->where('CE_emp_id', $resourceDataVal["CE_emp_id"])
                                ->whereBetween('invoke_date', [$startDate, $endDate])
                                ->count();

                            $subProjectsWithCount[$key][$resourceKey]['CompletedCount'] =
                                $modelClass::where('chart_status', 'CE_Completed')
                                ->where('CE_emp_id', $resourceDataVal["CE_emp_id"])
                                ->whereBetween('invoke_date', [$startDate, $endDate])
                                ->count();

                            $subProjectsWithCount[$key][$resourceKey]['PendingCount'] =
                                $modelClass::where('chart_status', 'CE_Pending')
                                ->where('CE_emp_id', $resourceDataVal["CE_emp_id"])
                                ->whereBetween('invoke_date', [$startDate, $endDate])
                                ->count();

                            $subProjectsWithCount[$key][$resourceKey]['holdCount'] =
                                $modelClass::where('chart_status', 'CE_Hold')
                                ->where('CE_emp_id', $resourceDataVal["CE_emp_id"])
                                ->where(function ($query) use ($startDate, $endDate, $days) {
                                    if ($days != 0) {
                                        $query->whereBetween('invoke_date', [$startDate, $endDate]);
                                    }
                                })
                                ->count();
                        }
                    }
                }

                return response()->json(['subprojects' => $subProjectsWithCount]);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
                return response()->json(['error' => 'Something went wrong']);
            }
    }

    public function getCalendarFilter(Request $request)
    {
        if (!Session::get('loginDetails') || !isset(Session::get('loginDetails')['userDetail']['emp_id'])) {
            return redirect('/');
        }

        try {
            $loginDetails = Session::get('loginDetails')['userDetail'];

            $loginEmpId = $loginDetails['emp_id'] ?? "";
            $userId     = $loginDetails['id'] ?? "";

            $calendarId = $request->CalendarId;
            $userType   = $request->type;

            // -----------------------------
            // DATE RANGE (optimized switch)
            // -----------------------------
            $now = Carbon::now();

            switch ($calendarId) {
                case "week":
                    $startDate = $now->copy()->startOfWeek()->startOfDay()->toDateString();
                    $endDate   = $now->copy()->endOfWeek()->endOfDay()->toDateString();
                    break;

                case "month":
                    $startDate = $now->copy()->startOfMonth()->toDateString();
                    $endDate   = $now->copy()->endOfMonth()->toDateString();
                    break;

                case "year":
                    $startDate = $now->copy()->startOfYear()->toDateString();
                    $endDate   = $now->copy()->endOfYear()->toDateString();
                    break;

                default:
                    $days      = (int) $calendarId;
                    $startDate = $now->copy()->startOfDay()->toDateString();
                    $endDate   = $now->copy()->endOfDay()->toDateString();
                    break;
            }

            $projects = $this->getProjects();

            $models = [];
            $projectNameCache = []; // ✅ avoid repeated Helpers calls

            foreach ($projects as $project) {

                $projectId = $project["id"];

                // cache project name (IMPORTANT optimization)
                if (!isset($projectNameCache[$projectId])) {
                    $daProject = Helpers::projectName($projectId);
                    $projectNameCache[$projectId] = $daProject ? $daProject->project_name : null;
                }

                $clientName = $projectNameCache[$projectId];

                $subProjects = $project["subprject_name"] ?? [];

                if (count($subProjects) > 0) {
                    foreach ($subProjects as $subProject) {

                        $table_name = Str::slug(
                            Str::lower($clientName . '_' . $subProject),
                            '_'
                        );

                        $models[] = "App\\Models\\" . Str::studly($table_name);
                    }
                } else {
                    $table_name = Str::slug(
                        Str::lower($clientName . '_project'),
                        '_'
                    );

                    $models[] = "App\\Models\\" . Str::studly($table_name);
                }
            }

            // -----------------------------
            // COUNT ARRAYS
            // -----------------------------
            $assignedCounts = $completeCounts = $pendingCounts =
            $holdCounts = $reworkCounts = $unAssignedCounts = [];

            foreach ($models as $model) {

                if (!class_exists($model)) {
                    continue;
                }

                if ($userType === "user") {

                    $assignedCounts[] = $model::where('chart_status', 'CE_Assigned')
                        ->where('CE_emp_id', $loginEmpId)
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $completeCounts[] = $model::where('chart_status', 'CE_Completed')
                        ->where('CE_emp_id', $loginEmpId)
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $pendingCounts[] = $model::where('chart_status', 'CE_Pending')
                        ->where('CE_emp_id', $loginEmpId)
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $holdCounts[] = $model::where('chart_status', 'CE_Hold')
                        ->where('CE_emp_id', $loginEmpId)
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $reworkCounts[] = $model::where('chart_status', 'Revoke')
                        ->where('CE_emp_id', $loginEmpId)
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                } elseif ($userType === "manager") {

                    $assignedCounts[] = $model::where('chart_status', 'CE_Assigned')
                        ->whereNotNull('CE_emp_id')
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $completeCounts[] = $model::where('chart_status', 'CE_Completed')
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $pendingCounts[] = $model::where('chart_status', 'CE_Pending')
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $holdCounts[] = $model::where('chart_status', 'CE_Hold')
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $reworkCounts[] = $model::where('chart_status', 'Revoke')
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $unAssignedCounts[] = $model::where('chart_status', 'CE_Assigned')
                        ->whereNull('CE_emp_id')
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();
                }
            }

            // -----------------------------
            // TOTALS (unchanged logic)
            // -----------------------------
            $totalAssignedCount   = array_sum($assignedCounts);
            $totalCompleteCount   = array_sum($completeCounts);
            $totalPendingCount    = array_sum($pendingCounts);
            $totalHoldCount       = array_sum($holdCounts);
            $totalReworkCount     = array_sum($reworkCounts);
            $totalUnAssignedCounts = array_sum($unAssignedCounts);

            $totalCount =
                $totalAssignedCount +
                $totalCompleteCount +
                $totalPendingCount +
                $totalHoldCount +
                $totalReworkCount +
                $totalUnAssignedCounts;

            return response()->json([
                'totalCount'          => $totalCount,
                'totalAssignedCount'  => $totalAssignedCount,
                'totalCompleteCount'  => $totalCompleteCount,
                'totalPendingCount'   => $totalPendingCount,
                'totalHoldCount'      => $totalHoldCount,
                'totalReworkCount'    => $totalReworkCount
            ]);

        } catch (\Exception $e) {
            Log::debug($e->getMessage());
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
                $data = retry(3, function () use ($payload) {
                    $client = new Client(['verify' => false]);
                    $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_clients_on_user', [
                        'json' => $payload,
                    ]);
                    if ($response->getStatusCode() == 200) {
                        // $data = json_decode($response->getBody(), true);
                        $responseData = json_decode($response->getBody(), true);
                        if (!empty($responseData['clientList'])) {
                            return Helpers::getFilteredClientProjects($responseData['clientList']);
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

    public function prjCalendarFilter(Request $request)
    {
        if (!Session::get('loginDetails') || !isset(Session::get('loginDetails')['userDetail']['emp_id'])) {
            return redirect('/');
        }

        try {
            $user = Session::get('loginDetails')['userDetail'];

            $loginEmpId = $user['emp_id'] ?? "";
            $userId     = $user['id'] ?? "";

            $calendarId = $request->CalendarId;
            $projects   = $this->getProjects();

            $now = Carbon::now();

            // ---------------- DATE FILTER (optimized) ----------------
            if ($calendarId == "year") {
                $startDate = $now->copy()->startOfYear()->toDateString();
                $endDate   = $now->copy()->endOfYear()->toDateString();
            } elseif ($calendarId == "month") {
                $startDate = $now->copy()->startOfMonth()->toDateString();
                $endDate   = $now->copy()->endOfMonth()->toDateString();
            } else {
                $startDate = $now->copy()->startOfDay()->toDateString();
                $endDate   = $now->copy()->endOfDay()->toDateString();
            }

            // ---------------- HTML INIT ----------------
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

            // ---------------- CACHE PROJECT NAME ----------------
            $projectNameCache = [];

            foreach ($projects as $data) {

                $projectId = $data["id"];

                if (!isset($projectNameCache[$projectId])) {
                    $daProject = Helpers::projectName($projectId);
                    $projectNameCache[$projectId] = $daProject ? $daProject->project_name : null;
                }

                $projectName = $projectNameCache[$projectId];

                // ---------------- MODEL BUILD ----------------
                if (!empty($data['subprject_name'])) {

                    $model_name = collect($data['subprject_name'])
                        ->map(function ($item) use ($projectName) {
                            return Str::studly(
                                Str::slug(Str::lower($projectName) . '_' . Str::lower($item), '_')
                            );
                        })
                        ->all();
                } else {
                    $model_name = [
                        Str::studly(
                            Str::slug(Str::lower($projectName) . '_project', '_')
                        )
                    ];
                }

                // ---------------- COUNTERS ----------------
                $assignedTotalCount = 0;
                $completedTotalCount = 0;
                $pendingTotalCount = 0;
                $holdTotalCount = 0;
                $modelTFlag = 0;
                foreach ($model_name as $model) {

                    $modelClass = "App\\Models\\$model";

                    if (!class_exists($modelClass)) {
                        continue;
                    }

                    $assignedTotalCount += $modelClass::where('chart_status', 'CE_Assigned')
                        ->where('CE_emp_id', $loginEmpId)
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $completedTotalCount += $modelClass::where('chart_status', 'CE_Completed')
                        ->where('CE_emp_id', $loginEmpId)
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $pendingTotalCount += $modelClass::where('chart_status', 'CE_Pending')
                        ->where('CE_emp_id', $loginEmpId)
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $holdTotalCount += $modelClass::where('chart_status', 'CE_Hold')
                        ->where('CE_emp_id', $loginEmpId)
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $modelTFlag = 1;
                }

                // ---------------- HTML BUILD (UNCHANGED) ----------------
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

            $body_info .= '</tbody></table>';
            return response()->json([
                'success' => true,
                'body_info' => $body_info,
            ]);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    public function mgrPrjCalendarFilter(Request $request)
    {
        if (!Session::get('loginDetails') || !Session::get('loginDetails')['userDetail']['emp_id']) {
            return redirect('/');
        }

        try {
            $loginEmpId = Session::get('loginDetails')['userDetail']['emp_id'];
            $calendarId = $request->CalendarId;
            $projects = $this->getProjects();

            // Date calculation (unchanged logic, just cleaner)
            if ($calendarId == "year") {
                $startDate = Carbon::now()->startOfYear()->toDateString();
                $endDate = Carbon::now()->endOfYear()->toDateString();
            } elseif ($calendarId == "month") {
                $startDate = Carbon::now()->startOfMonth()->toDateString();
                $endDate = Carbon::now()->endOfMonth()->toDateString();
            } else {
                $startDate = Carbon::now()->startOfDay()->toDateString();
                $endDate = Carbon::now()->endOfDay()->toDateString();
            }

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

            foreach ($projects as $data) {

                // ✅ cache helper call (was repeated inside loop)
                $projectData = Helpers::projectName($data["id"]);
                $projectName = $projectData ? $projectData->project_name : null;

                // build models (same logic, just cleaner)
                $modelNames = [];

                if (!empty($data['subprject_name'])) {
                    foreach ($data['subprject_name'] as $item) {
                        $modelNames[] = Str::studly(
                            Str::slug(strtolower($projectName . '_' . $item), '_')
                        );
                    }
                } else {
                    $modelNames[] = Str::studly(
                        Str::slug(strtolower($projectName . '_project'), '_')
                    );
                }

                $assignedTotalCount = 0;
                $completedTotalCount = 0;
                $pendingTotalCount = 0;
                $holdTotalCount = 0;
                $modelTFlag = 0;

                foreach ($modelNames as $model) {

                    $modelClass = "App\\Models\\$model";

                    if (!class_exists($modelClass)) {
                        continue;
                    }

                    // queries unchanged (ONLY grouped + reused date vars)
                    $assignedTotalCount += $modelClass::whereIn('chart_status', ['CE_Assigned','CE_Inprocess'])
                        ->whereNotNull('CE_emp_id')
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $completedTotalCount += $modelClass::where('chart_status', 'CE_Completed')
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $pendingTotalCount += $modelClass::where('chart_status', 'CE_Pending')
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $holdTotalCount += $modelClass::where('chart_status', 'CE_Hold')
                        ->whereBetween('invoke_date', [$startDate, $endDate])
                        ->count();

                    $modelTFlag = 1;
                }

                if ($modelTFlag > 0) {
                    $body_info .= '<tr class="clickable-client cursor_hand project-clickable-row">
                        <td class="details-control"></td>
                        <td>' . $data['client_name'] . '<input type="hidden" value=' . $data['id'] . '></td>
                        <td>' . $assignedTotalCount . '</td>
                        <td>' . $completedTotalCount . '</td>
                        <td>' . $pendingTotalCount . '</td>
                        <td>' . $holdTotalCount . '</td>
                    </tr>';
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
    }

    public function inventoryUploadList(Request $request)
    {
        if (!Session::get('loginDetails') || !Session::get('loginDetails')['userDetail']['emp_id']) {
            return redirect('/');
        }

        try {
            $work_date = $request->work_date ?? null;

            if (!empty($work_date)) {
                $work_date = explode(' - ', $work_date);
                $start_date = date('Y-m-d 00:00:00', strtotime($work_date[0]));
                $end_date = date('Y-m-d 23:59:59', strtotime($work_date[1]));
            } else {
                $baseDate = Carbon::now();
                $start_date = $baseDate->copy()->subMonths(2)->startOfMonth()->format('Y-m-d 00:00:00');
                $end_date = $baseDate->copy()->addMonths(2)->endOfMonth()->format('Y-m-d 23:59:59');
            }

            $query = InventoryExeFile::query();

            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween('exe_date', [$start_date, $end_date]);
            }

            if (!empty($request->project_id)) {
                $query->where('project_id', $request->project_id);

                if (!empty($request->sub_project_id)) {
                    $query->where('sub_project_id', $request->sub_project_id);
                }
            }

            $client_data = $query->get();

            $body_info = '<table class="table table-separate table-head-custom no-footer" id="report_list">
                            <thead>
                            <tr>
                                <th>Project</th>
                                <th>Sub Project</th>
                                <th>Uploaded Count</th>
                                <th>Uploaded Date</th>
                                <th>Uploaded Status</th>
                            </tr>
                            </thead><tbody>';

            foreach ($client_data as $data) {
                // cache helper calls (major improvement)
                $projectData = Helpers::projectName($data->project_id);
                $projectName = $projectData ? $projectData->aims_project_name : null;
                $subProjectName = '--';
                if (!empty($data->sub_project_id)) {
                    $sub = Helpers::subProjectName($data->project_id, $data->sub_project_id);
                    $subProjectName = $sub ? $sub->sub_project_name : '--';
                }

                $inventoryCount = $data->inventory_count ?? '--';

                $body_info .= '<tr>
                    <td class="wrap-text">' . $projectName . '</td>
                    <td class="wrap-text">' . $subProjectName . '</td>
                    <td class="wrap-text">' . $inventoryCount . '</td>
                    <td class="wrap-text">' . date('m/d/Y H:i:s', strtotime($data->exe_date)) . '</td>
                    <td class="wrap-text">' . ucfirst($data->upload_status) . '</td>
                </tr>';
            }
            $body_info .= '</tbody></table>';
            

            return response()->json([
                'success' => true,
                'body_info' => $body_info,
            ]);

        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }
    public function projectReasonSave(Request $request)
    {
        if (!Session::get('loginDetails') || !Session::get('loginDetails')['userDetail']['emp_id']) {
            return redirect('/');
        }

            try {
                $data = $request->all();
                $data['manager_id'] = Session::get('loginDetails')['userDetail']['id'];
                $projectReason = ProjectReason::create($data);
                if ($projectReason) {
                    return response()->json([
                        'success' => true
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to save project reason'
                        ]);
                }
            } catch (Exception $e) {
                log::debug($e->getMessage());
            }
    }
}
