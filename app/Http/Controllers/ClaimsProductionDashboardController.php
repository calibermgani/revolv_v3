<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Http\Helper\Admin\Helpers as Helpers;

class ClaimsProductionDashboardController extends Controller
{
    public function index()
    {
        $user = $this->loginUser();
        if ($user === null) {
            return redirect('/');
        }

        $role = $this->roleFromUser($user);
        $titles = [
            'user' => 'My Production Dashboard',
            'tl' => 'Team Production Dashboard',
            'manager' => 'Operations Dashboard',
        ];

        return view('claims-dashboard.index', [
            'role' => $role,
            'pageTitle' => $titles[$role],
            // 'employeeName' => $user['user_name'] ?? $user['emp_id'],
            'startDate' => $this->previousWorkingDay()->toDateString(),
            'endDate' => $this->previousWorkingDay()->toDateString(),
        ]);
    }

    public function data(Request $request)
    {
        $user = $this->loginUser();
        if ($user === null) {
            return response()->json(['message' => 'Please sign in again.'], 401);
        }

        try {
            $range = $this->dateRange($request);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        try {
            $role = $this->roleFromUser($user);
            $empFilter = $role === 'user' ? (string) $user['emp_id'] : null;
            $scopes = $this->scopesForUser();
            $periods = $this->periodsForRange($range['start'], $range['end']);
            $targets = $this->targetByScope($scopes, $periods);

            $cards = [
                'uploaded' => 0,
                'allocated' => 0,
                'worked' => 0,
                'unworked' => 0,
                'unallocated' => 0,
                'actual' => 0,
                'target' => 0,
            ];
            $scopeRows = [];
            $employeeRows = [];
            $hours = array_fill(0, 24, 0);
            $hourProjects = [];
            $empIds = [];

            foreach ($scopes as $scope) {
                $counts = $this->countScope($scope['table'], $scope['columns'], $range, $empFilter);
                $hourly = $this->hourlyForScope($scope['table'], $scope['columns'], $range, $empFilter);
                foreach ($hourly as $hour => $count) {
                    $hours[$hour] += $count;
                    if ($count > 0) {
                        $projectName = $scope['label'];
                        if (!isset($hourProjects[$hour][$projectName])) {
                            $hourProjects[$hour][$projectName] = 0;
                        }
                        $hourProjects[$hour][$projectName] += $count;
                    }
                }

                $targetKey = $scope['project_id'] . '|' . $scope['sub_project_id'];
                $targetInfo = $targets[$targetKey] ?? ['project' => 0.0, 'employee' => 0.0];
                $target = (int) round($targetInfo['project']);
                $employeeTarget = (int) round($targetInfo['employee']);
                $scopeName = $scope['label'];

                $hasActivity = $counts['uploaded'] + $counts['allocated'] + $counts['worked'] + $counts['unworked'] + $counts['unallocated'] + $counts['actual'] + $target;
                if ($hasActivity === 0) {
                    continue;
                }

                $cards['uploaded'] += $counts['uploaded'];
                $cards['allocated'] += $counts['allocated'];
                $cards['worked'] += $counts['worked'];
                $cards['unworked'] += $counts['unworked'];
                $cards['unallocated'] += $counts['unallocated'];
                $cards['actual'] += $counts['actual'];
                $cards['target'] += $target;

                $scopeRows[] = [
                    'name' => $scopeName,
                    'uploaded' => $counts['uploaded'],
                    'allocated' => $counts['allocated'],
                    'worked' => $counts['worked'],
                    'unworked' => $counts['unworked'],
                    'unallocated' => $counts['unallocated'],
                    'actual' => $counts['actual'],
                    'target' => $target,
                ];

                foreach ($counts['employees'] as $empId => $empCounts) {
                    if ($empId === '' || ($empCounts['allocated'] === 0 && $empCounts['actual'] === 0)) {
                        continue;
                    }
                    $empIds[] = $empId;
                    $employeeRows[] = [
                        'emp_id' => $empId,
                        'scope' => $scopeName,
                        'allocated' => $empCounts['allocated'],
                        'actual' => $empCounts['actual'],
                        'target' => $employeeTarget,
                    ];
                }
            }

            $names = $this->employeeNames(array_values(array_unique($empIds)));
            foreach ($employeeRows as &$row) {
                $row['name'] = $names[$row['emp_id']] ?? $row['emp_id'];
                $row['achieved'] = $row['target'] > 0
                    ? (int) round(($row['actual'] / $row['target']) * 100)
                    : 0;
            }
            unset($row);

            usort($scopeRows, function ($a, $b) {
                return $b['uploaded'] <=> $a['uploaded'];
            });
            usort($employeeRows, function ($a, $b) {
                return [$b['actual'], $a['name']] <=> [$a['actual'], $b['name']];
            });

            $cards['achievement'] = $cards['target'] > 0
                ? (int) round(($cards['actual'] / $cards['target']) * 100)
                : 0;

            $hourRows = $this->hourRows($hours, $hourProjects);
            $peakHour = null;
            $peakCount = 0;
            foreach ($hours as $hour => $count) {
                if ($count > $peakCount) {
                    $peakCount = $count;
                    $peakHour = $hour;
                }
            }

            return response()->json([
                'role' => $role,
                'generated_at' => Carbon::now()->timezone('Asia/Kolkata')->format('d M Y, h:i A') . ' IST',
                'start' => $range['start']->toDateString(),
                'end' => $range['end']->toDateString(),
                'cards' => $cards,
                'scopes' => $scopeRows,
                'employees' => $employeeRows,
                'hours' => $hourRows,
                'peak' => $peakHour === null ? null : [
                    'label' => sprintf('%02d:00', $peakHour),
                    'count' => $peakCount,
                ],
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        } catch (\Throwable $e) {
            Log::error('Claims dashboard data failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Unable to load dashboard counts. Please try again.',
            ], 500);
        }
    }

    private function loginUser(): ?array
    {
        $details = Session::get('loginDetails');
        $user = $details['userDetail'] ?? null;
        if (!is_array($user) || empty($user['emp_id'])) {
            return null;
        }

        return $user;
    }

    private function roleFromUser(array $user): string
    {
        $empId = (string) ($user['emp_id'] ?? '');
        $designation = (string) ($user['user_hrdetails']['current_designation'] ?? '');

        if (!$this->isElevated($empId, $designation)) {
            return 'user';
        }

        $managerNeedles = ['Manager', 'VP', 'CEO', 'Vice', 'Subject Matter Expert', 'Group Coordinator', 'Group Co-ordinator'];
        foreach ($managerNeedles as $needle) {
            if ($empId === 'Admin' || ($designation !== '' && strpos($designation, $needle) !== false)) {
                return 'manager';
            }
        }

        return 'tl';
    }

    private function isElevated(string $empId, string $designation): bool
    {
        if ($empId === 'Admin') {
            return true;
        }

        $needles = [
            'Manager',
            'VP',
            'Leader',
            'Team Lead',
            'CEO',
            'Vice',
            'Subject Matter Expert',
            'Group Coordinator',
            'Group Co-ordinator - Quality',
            'Group Co-ordinator - AR',
        ];
        foreach ($needles as $needle) {
            if ($designation !== '' && strpos($designation, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function previousWorkingDay(): Carbon
    {
        $day = Carbon::yesterday();
        while ($day->isWeekend()) {
            $day->subDay();
        }

        return $day;
    }

    private function dateRange(Request $request): array
    {
        try {
            $workingDay = $this->previousWorkingDay()->toDateString();
            $start = Carbon::parse($request->input('start', $workingDay))->startOfDay();
            $end = Carbon::parse($request->input('end', $workingDay))->startOfDay();
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('Select a valid date range.');
        }

        if ($end->lt($start)) {
            throw new \InvalidArgumentException('The end date must be on or after the start date.');
        }

        if ($start->diffInDays($end) > 366) {
            throw new \InvalidArgumentException('Choose a date range of one year or less.');
        }

        return [
            'start' => $start,
            'end' => $end,
            'window_start' => $start->copy()->setTime(8, 0, 0),
            'window_end' => $end->copy()->addDay()->setTime(7, 59, 59),
        ];
    }

    private function periodsForRange(Carbon $start, Carbon $end): array
    {
        $periods = [];
        $cursor = $start->copy()->startOfMonth();
        $last = $end->copy()->startOfMonth();

        while ($cursor->lte($last)) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth()->startOfDay();
            $overlapStart = $start->copy()->startOfDay()->greaterThan($monthStart) ? $start->copy()->startOfDay() : $monthStart;
            $overlapEnd = $end->copy()->startOfDay()->lessThan($monthEnd) ? $end->copy()->startOfDay() : $monthEnd;
            $days = $overlapStart->diffInDays($overlapEnd) + 1;
            $periods[] = [
                'year' => (int) $cursor->format('Y'),
                'month' => $cursor->format('F'),
                'days' => $days,
            ];
            $cursor->addMonth();
        }

        return $periods;
    }

    private function scopesForUser(): array
    {
        $projects = $this->clientProjects();
        if ($projects === []) {
            return [];
        }

        $scopes = [];
        $projectNames = [];
        $aimsNames = [];

        foreach ($projects as $project) {
            $projectId = isset($project['id']) ? (string) $project['id'] : '';
            if ($projectId === '') {
                continue;
            }
            if (!array_key_exists($projectId, $projectNames)) {
                $record = Helpers::projectName($projectId);
                $projectNames[$projectId] = $record ? $record->project_name : null;
                $aimsName = $record ? trim((string) $record->aims_project_name) : '';
                $aimsNames[$projectId] = $aimsName !== '' ? $aimsName : $projectNames[$projectId];
            }
            $projectName = $projectNames[$projectId];
            if (!$projectName) {
                continue;
            }

            foreach ($project['subprject_name'] ?? [] as $subId => $subName) {
                if (!is_string($subName) || trim($subName) === '') {
                    continue;
                }
                $table = Str::slug(Str::lower($projectName . '_' . $subName), '_');
                if (!preg_match('/^[a-z0-9_]+$/', $table) || !Schema::hasTable($table)) {
                    continue;
                }
                $columns = Schema::getColumnListing($table);
                if (!$this->hasColumn($columns, 'invoke_date') || !$this->hasColumn($columns, 'CE_emp_id') || !$this->hasColumn($columns, 'chart_status')) {
                    continue;
                }

                $scopes[] = [
                    'project_id' => $projectId,
                    'sub_project_id' => (string) $subId,
                    'label' => $aimsNames[$projectId] . ' - ' . $subName,
                    'table' => $table,
                    'columns' => $columns,
                ];
            }
        }

        return $scopes;
    }

    private function clientProjects(): array
    {
        $user = $this->loginUser();
        $userId = $user['id'] ?? '';
        $client = new Client(['verify' => false, 'timeout' => 30]);
        $response = $client->request('POST', config('constants.PRO_CODE_URL') . '/api/v1_users/get_clients_on_user', [
            'json' => [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'user_id' => $userId,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $body = json_decode($response->getBody(), true);
        $clientList = $body['clientList'] ?? [];
        if (!is_array($clientList) || $clientList === []) {
            return [];
        }

        $configQuery = DB::table('form_configurations')
            ->select('project_id', 'sub_project_id')
            ->whereNotNull('project_id')
            ->whereNotNull('sub_project_id')
            ->distinct();
        if (Schema::hasColumn('form_configurations', 'deleted_at')) {
            $configQuery->whereNull('deleted_at');
        }

        $map = [];
        foreach ($configQuery->get() as $row) {
            $map[(string) $row->project_id][(string) $row->sub_project_id] = true;
        }

        $filtered = [];
        foreach ($clientList as $project) {
            if (!is_array($project)) {
                continue;
            }
            $projectId = isset($project['id']) ? (string) $project['id'] : '';
            $subs = [];
            foreach ($project['subprject_name'] ?? [] as $subKey => $subName) {
                if ($projectId !== '' && isset($map[$projectId][(string) $subKey])) {
                    $subs[$subKey] = $subName;
                }
            }
            if ($subs !== []) {
                $project['subprject_name'] = $subs;
                $filtered[] = $project;
            }
        }

        return $filtered;
    }

    private function countScope(string $table, array $columns, array $range, ?string $empFilter): array
    {
        $hasArAt = $this->hasColumn($columns, 'ar_at');
        $startDate = $range['start']->toDateString();
        $endDate = $range['end']->toDateString();
        $startDt = $range['window_start']->format('Y-m-d H:i:s');
        $endDt = $range['window_end']->format('Y-m-d H:i:s');
        $bindings = [];

        $select = [
            'CE_emp_id as emp_id',
            $this->sumWhen('invoke_date BETWEEN ? AND ?', [$startDate, $endDate], $bindings, 'uploaded'),
            $this->sumWhen("invoke_date BETWEEN ? AND ? AND CE_emp_id IS NOT NULL AND CE_emp_id <> ''", [$startDate, $endDate], $bindings, 'allocated'),
            $this->sumWhen("invoke_date BETWEEN ? AND ? AND (CE_emp_id IS NULL OR CE_emp_id = '')", [$startDate, $endDate], $bindings, 'unallocated'),
        ];

        if ($hasArAt) {
            $select[] = $this->sumWhen(
                "invoke_date BETWEEN ? AND ? AND CE_emp_id IS NOT NULL AND CE_emp_id <> '' AND ar_at BETWEEN ? AND ? AND chart_status NOT IN ('Auto_Close','AR_non_workable')",
                [$startDate, $endDate, $startDt, $endDt],
                $bindings,
                'worked'
            );
            $select[] = $this->sumWhen(
                "invoke_date BETWEEN ? AND ? AND CE_emp_id IS NOT NULL AND CE_emp_id <> '' AND ar_at IS NULL AND chart_status IN ('CE_Assigned','CE_Inprocess')",
                [$startDate, $endDate],
                $bindings,
                'unworked'
            );
            $select[] = $this->sumWhen(
                "ar_at BETWEEN ? AND ? AND CE_emp_id IS NOT NULL AND CE_emp_id <> '' AND chart_status NOT IN ('Auto_Close','AR_non_workable')",
                [$startDt, $endDt],
                $bindings,
                'actual'
            );
        } else {
            $select[] = 'SUM(0) as worked';
            $select[] = $this->sumWhen(
                "invoke_date BETWEEN ? AND ? AND CE_emp_id IS NOT NULL AND CE_emp_id <> '' AND chart_status IN ('CE_Assigned','CE_Inprocess')",
                [$startDate, $endDate],
                $bindings,
                'unworked'
            );
            $select[] = 'SUM(0) as actual';
        }

        $query = DB::table($table);
        if ($this->hasColumn($columns, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }
        if ($empFilter !== null) {
            $query->where('CE_emp_id', $empFilter);
        }
        $query->where(function ($inner) use ($startDate, $endDate, $startDt, $endDt, $hasArAt) {
            $inner->whereBetween('invoke_date', [$startDate, $endDate]);
            if ($hasArAt) {
                $inner->orWhereBetween('ar_at', [$startDt, $endDt]);
            }
        });

        $rows = $query
            ->selectRaw(implode(', ', $select), $bindings)
            ->groupBy('CE_emp_id')
            ->get();

        $totals = [
            'uploaded' => 0,
            'allocated' => 0,
            'worked' => 0,
            'unworked' => 0,
            'unallocated' => 0,
            'actual' => 0,
            'employees' => [],
        ];

        foreach ($rows as $row) {
            $totals['uploaded'] += (int) $row->uploaded;
            $totals['allocated'] += (int) $row->allocated;
            $totals['worked'] += (int) $row->worked;
            $totals['unworked'] += (int) $row->unworked;
            $totals['unallocated'] += (int) $row->unallocated;
            $totals['actual'] += (int) $row->actual;
            $empId = trim((string) $row->emp_id);
            if ($empId !== '') {
                if (!isset($totals['employees'][$empId])) {
                    $totals['employees'][$empId] = [
                        'allocated' => 0,
                        'actual' => 0,
                    ];
                }
                $totals['employees'][$empId]['allocated'] += (int) $row->allocated;
                $totals['employees'][$empId]['actual'] += (int) $row->actual;
            }
        }

        return $totals;
    }

    private function hourlyForScope(string $table, array $columns, array $range, ?string $empFilter): array
    {
        if (!$this->hasColumn($columns, 'ar_at')) {
            return [];
        }

        $query = DB::table($table)
            ->selectRaw('HOUR(ar_at) as hr')
            ->whereBetween('ar_at', [
                $range['window_start']->format('Y-m-d H:i:s'),
                $range['window_end']->format('Y-m-d H:i:s'),
            ])
            ->whereNotNull('CE_emp_id')
            ->where('CE_emp_id', '<>', '')
            ->whereNotIn('chart_status', ['Auto_Close', 'AR_non_workable']);

        if ($this->hasColumn($columns, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }
        if ($empFilter !== null) {
            $query->where('CE_emp_id', $empFilter);
        }

        $rows = DB::table(DB::raw('(' . $query->toSql() . ') as hourly_rows'))
            ->mergeBindings($query)
            ->selectRaw('hr, COUNT(*) as cnt')
            ->groupBy('hr')
            ->get();

        $hours = [];
        foreach ($rows as $row) {
            if ($row->hr === null) {
                continue;
            }
            $hours[(int) $row->hr] = (int) $row->cnt;
        }

        return $hours;
    }

    private function hasColumn(array $columns, string $name): bool
    {
        foreach ($columns as $column) {
            if (strcasecmp((string) $column, $name) === 0) {
                return true;
            }
        }

        return false;
    }

    private function sumWhen(string $condition, array $params, array &$bindings, string $alias): string
    {
        foreach ($params as $param) {
            $bindings[] = $param;
        }

        return 'SUM(CASE WHEN ' . $condition . ' THEN 1 ELSE 0 END) as ' . $alias;
    }

    private function targetByScope(array $scopes, array $periods): array
    {
        if ($scopes === [] || $periods === [] || !Schema::hasTable('aims_project_du_targets')) {
            return [];
        }

        $projectIds = array_values(array_unique(array_column($scopes, 'project_id')));
        $subIds = array_values(array_unique(array_column($scopes, 'sub_project_id')));
        $query = DB::table('aims_project_du_targets')
            ->whereIn('client_id', $projectIds)
            ->whereIn('subproject_id', $subIds);

        if (Schema::hasColumn('aims_project_du_targets', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $query->where(function ($outer) use ($periods) {
            foreach ($periods as $period) {
                $outer->orWhere(function ($inner) use ($period) {
                    $inner->where('year', $period['year'])->where('month', $period['month']);
                });
            }
        });

        $daysByKey = [];
        foreach ($periods as $period) {
            $daysByKey[$period['year'] . '|' . $period['month']] = $period['days'];
        }

        $map = [];
        foreach ($query->get(['client_id', 'subproject_id', 'actual_target', 'billable_fte', 'year', 'month']) as $row) {
            $key = (string) $row->client_id . '|' . (string) $row->subproject_id;
            $days = $daysByKey[(string) $row->year . '|' . trim((string) $row->month)] ?? 0;
            if (!isset($map[$key])) {
                $map[$key] = ['project' => 0.0, 'employee' => 0.0];
            }
            $daily = $this->numericTarget($row->actual_target);
            $fte = $this->numericTarget($row->billable_fte);
            if ($fte <= 0) {
                $fte = 1;
            }
            $map[$key]['employee'] += $daily * $days;
            $map[$key]['project'] += $daily * $days * $fte;
        }

        return $map;
    }

    private function numericTarget($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        $clean = str_replace([',', '%', ' '], '', (string) $value);

        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    private function employeeNames(array $empIds): array
    {
        if ($empIds === [] || !Schema::hasTable('aims_users')) {
            return [];
        }

        $query = DB::table('aims_users')->whereIn('emp_id', $empIds)->whereNotNull('user_name')->where('user_name', '<>', '');
        if (Schema::hasColumn('aims_users', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $names = [];
        foreach ($query->get(['emp_id', 'user_name']) as $row) {
            $names[(string) $row->emp_id] = $row->emp_id . ' - ' . $row->user_name;
        }

        return $names;
    }

    private function hourRows(array $hours, array $hourProjects): array
    {
        $order = [];
        for ($hour = 8; $hour <= 23; $hour++) {
            $order[] = $hour;
        }
        for ($hour = 0; $hour <= 7; $hour++) {
            $order[] = $hour;
        }

        $rows = [];
        foreach ($order as $hour) {
            $projects = [];
            foreach ($hourProjects[$hour] ?? [] as $name => $count) {
                $projects[] = [
                    'name' => $name,
                    'count' => (int) $count,
                ];
            }
            usort($projects, function ($a, $b) {
                return $b['count'] <=> $a['count'];
            });

            $rows[] = [
                'hour' => $hour,
                'label' => sprintf('%02d:00', $hour),
                'count' => (int) ($hours[$hour] ?? 0),
                'projects' => $projects,
            ];
        }

        return $rows;
    }
}
