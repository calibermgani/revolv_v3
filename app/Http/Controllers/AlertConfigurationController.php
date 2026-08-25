<?php

namespace App\Http\Controllers;

use App\Models\FormConfiguration;
use App\Http\Helper\Admin\Helpers;
use App\Models\AimsProjectResourceAllocation;
use App\Models\AlertConfiguration;
use App\Models\Project;
use App\Models\SubProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AlertConfigurationController extends Controller
{
    /**
     * Main page.
     */
    public function index()
    {
        $projectList = Project::where('status', 'Active')
            ->whereIn('project_id', function ($query) {
                $query->select('project_id')
                    ->from('form_configurations')
                    ->whereNull('deleted_at');
            })
            ->pluck('aims_project_name', 'project_id')
            ->map(function ($name) {
                return ucwords(strtolower($name));
            })
            ->prepend(trans('Select Project'), '')
            ->toArray();

        return view(
            'alert-configurations.index',
            compact('projectList')
        );
    }

    /**
     * AJAX grid data.
     */
    public function list(): JsonResponse
    {
        $configurations = AlertConfiguration::with([
                'project',
                'subProject',
            ])
            ->orderByDesc('id')
            ->get();

        /*
         * Get user names for grid display.
         * User IDs are still stored in alert_configurations.emp_id.
         */
        $employeeIds = $configurations
            ->flatMap(function ($configuration) {
                return array_filter(
                    array_map(
                        'trim',
                        explode(',', $configuration->emp_id)
                    )
                );
            })
            ->unique()
            ->values();

        $employeeNames = AimsProjectResourceAllocation::query()
            ->whereIn('emp_id', $employeeIds)
            ->whereNotNull('emp_id')
            ->select([
                'emp_id',
                'user_name',
            ])
            ->get()
            ->unique('emp_id')
            ->keyBy('emp_id');

        $data = $configurations
            ->map(function ($configuration) use ($employeeNames) {
                $projectName =
                    optional($configuration->project)->aims_project_name
                    ?: optional($configuration->project)->project_name
                    ?: $configuration->project_id;

                $subProjectName =
                    optional($configuration->subProject)
                        ->new_sub_project_name
                    ?: optional($configuration->subProject)
                        ->sub_project_name
                    ?: $configuration->sub_project_id;

                $users = collect(
                    explode(',', $configuration->emp_id)
                )
                    ->map(function ($empId) use ($employeeNames) {
                        $empId = trim($empId);

                        if ($empId === '') {
                            return null;
                        }

                        $employee = $employeeNames->get($empId);

                        if (
                            $employee &&
                            !empty($employee->user_name)
                        ) {
                            return $employee->user_name .
                                ' (' . $empId . ')';
                        }

                        return $empId;
                    })
                    ->filter()
                    ->values();

                return [
                    'id' => $configuration->id,

                    'project_id' =>
                        $configuration->project_id,

                    'sub_project_id' =>
                        $configuration->sub_project_id,

                    'project_name' =>
                        $projectName,

                    'sub_project_name' =>
                        $subProjectName,

                    'column_name' =>
                        $configuration->project_column,

                    'operator' =>
                        $configuration->condition,

                    'value' =>
                        $configuration->value,

                    'users' =>
                        $users,                  
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * Load active subprojects.
     */
    public function getSubProjects(
        Request $request
    ): JsonResponse {
        $request->validate([
            'project_id' => [
                'required',
            ],
        ]);

        // $subProjectList = Helpers::subProjectList(
        //     $request->project_id
        // );
    $subProjectList = subproject::join('form_configurations', function ($join) {
        $join->on('subprojects.project_id', '=', 'form_configurations.project_id')
             ->on('subprojects.sub_project_id', '=', 'form_configurations.sub_project_id');
    })
    ->where('subprojects.project_id', $request->project_id)
    ->whereNull('form_configurations.deleted_at')
    ->select('subprojects.sub_project_id', 'subprojects.sub_project_name')
    ->distinct()
    ->pluck('subprojects.sub_project_name', 'subprojects.sub_project_id')
    ->prepend(trans('Select Sub Project'), '')
    ->toArray();
        $subProjects = collect($subProjectList)
            ->map(function ($name, $id) {
                return [
                    'id' => (string) $id,
                    'name' => $name,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'sub_projects' => $subProjects,
        ]);
    }

    /**
     * Load project columns and users.
     */
    public function getOptions(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'project_id' => [
                'required',
                'integer',
            ],

            'sub_project_id' => [
                'required',
                'integer',
            ],
        ]);

        $projectId = $validated['project_id'];
        $subProjectId = $validated['sub_project_id'];

        $columns = FormConfiguration::query()
            ->where(
                'project_id',
                (string) $projectId
            )
            ->where(
                'sub_project_id',
                (string) $subProjectId
            )
            ->whereNull('deleted_at')
            ->whereNotNull('label_name')
            ->whereNotIn('label_name',['Production Type','Scenario','Question Json','AR At','QA At','AR Notes','AR Denial Codes','AR SubStatus Codes',''])
            ->where('label_name', '!=', '')
            ->where(function ($query) {
                $query->whereNull('field_type_3')
                    ->orWhere('field_type_3', '!=', 'popup_non_visible');
            })
            ->selectRaw(
                'MIN(id) AS id, label_name, input_type'
            )
            ->groupBy('label_name', 'input_type')
            ->orderBy('label_name')
            ->get()
            ->map(function ($column) {
                return [
                    'id' => (int) $column->id,
                    'name' =>
                        (string) $column->label_name,
                    'type' => $column->input_type,
                ];
            })
            ->values();

        $users = AimsProjectResourceAllocation::query()
            ->where('client_id', $projectId)
            ->where('user_status', 'Active')
            ->where('status', 'Active')
            ->whereNotNull('emp_id')
            ->where('emp_id', '!=', '')
            ->where(function ($query) {
                $query
                    ->where(
                        'current_designation',
                        'LIKE',
                        '%Executive AR%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%Senior Executive AR%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%Trainee AR%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%Trainee AR Caller%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%AR Caller Trainee%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%AR Caller%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%Senior AR Caller%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%Trainee - AR%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%Senior Executive - AR%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%AR Executive%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%Senior AR Executive%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%Executive - AR%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%Executive Analyst - AR%'
                    )
                    ->orWhere(
                        'current_designation',
                        'LIKE',
                        '%Senior Analyst - AR%'
                    )
                    ->orWhereNull(
                        'current_designation'
                    );
            })
            ->where(function ($query) {
                $query
                    ->whereNull('current_designation')
                    ->orWhere(
                        'current_designation',
                        'NOT LIKE',
                        '%Senior Process Executive - AR%'
                    );
            })
            ->select([
                'emp_id',
                'user_name',
            ])
            ->orderBy('user_name')
            ->get()
            ->map(function ($user) {
                $empId = trim(
                    (string) $user->emp_id
                );

                $userName = trim(
                    (string) $user->user_name
                );

                if ($userName === '') {
                    $userName = $empId;
                }

                return [
                    'id' => $empId,

                    'name' => $userName === $empId
                        ? $empId
                        : $userName .
                            ' (' . $empId . ')',

                    'emp_id' => $empId,
                ];
            })
            ->unique('id')
            ->values();

        return response()->json([
            'status' => true,
            'columns' => $columns,
            'users' => $users,
        ]);
    }

    /**
     * Save conditions.
     *
     * Each condition is inserted as one row.
     */
    public function store(
        Request $request
    ): JsonResponse {
        $validated = $this->validateRequest(
            $request
        );

        $currentUser =
            $this->getCurrentUserKey();

        $insertedIds = DB::transaction(
            function () use (
                $validated,
                $currentUser
            ) {
                $ids = [];

                foreach (
                    $validated['conditions']
                    as $index => $conditionData
                ) {
                    $formConfiguration =
                        $this->getFormConfiguration(
                            $validated['project_id'],
                            $validated['sub_project_id'],
                            $conditionData['column_id'],
                            $index
                        );

                    $employeeIds = collect(
                        $conditionData['users']
                    )
                        ->map(function ($empId) {
                            return trim(
                                (string) $empId
                            );
                        })
                        ->filter()
                        ->unique()
                        ->values()
                        ->implode(',');

                    $configuration =
                        AlertConfiguration::create([
                            'project_id' =>
                                $validated['project_id'],

                            'sub_project_id' =>
                                $validated['sub_project_id'],

                            'project_column' =>
                                $formConfiguration
                                    ->label_name,

                            'condition' =>
                                $conditionData['operator'],

                            'value' =>
                                $conditionData['value'],

                            'emp_id' =>
                                $employeeIds,

                            'created_by' =>
                                $currentUser,

                            'updated_by' =>
                                $currentUser,
                        ]);

                    $ids[] = $configuration->id;
                }

                return $ids;
            }
        );

        return response()->json([
            'status' => true,
            'message' =>
                'Alert configuration saved successfully.',
            'ids' => $insertedIds,
        ]);
    }

    /**
     * Load one row for editing.
     */
    public function show(
        int $id
    ): JsonResponse {
        $configuration =
            AlertConfiguration::findOrFail($id);

        $formConfigurationId =
            FormConfiguration::query()
                ->where(
                    'project_id',
                    (string) $configuration->project_id
                )
                ->where(
                    'sub_project_id',
                    (string) $configuration
                        ->sub_project_id
                )
                ->where(
                    'label_name',
                    $configuration->project_column
                )
                ->whereNull('deleted_at')
                ->min('id');

        return response()->json([
            'status' => true,

            'data' => [
                'id' =>
                    $configuration->id,

                'project_id' =>
                    $configuration->project_id,

                'sub_project_id' =>
                    $configuration->sub_project_id,

                /*
                 * Existing JavaScript expects an array
                 * called conditions.
                 */
                'conditions' => [
                    [
                        'column_id' =>
                            $formConfigurationId,

                        'column_name' =>
                            $configuration
                                ->project_column,

                        'operator' =>
                            $configuration->condition,

                        'value' =>
                            $configuration->value,

                        'users' => collect(
                            explode(
                                ',',
                                $configuration->emp_id
                            )
                        )
                            ->map(function ($empId) {
                                return trim($empId);
                            })
                            ->filter()
                            ->values(),
                    ],
                ],
            ],
        ]);
    }

    /**
     * Update one row.
     *
     * When additional conditions are added during editing,
     * the first condition updates the selected record and the
     * remaining conditions are inserted as new rows.
     */
    public function update(
        Request $request,
        int $id
    ): JsonResponse {
        $validated = $this->validateRequest(
            $request
        );

        $configuration =
            AlertConfiguration::findOrFail($id);

        $currentUser =
            $this->getCurrentUserKey();

        DB::transaction(function () use (
            $validated,
            $configuration,
            $currentUser
        ) {
            foreach (
                $validated['conditions']
                as $index => $conditionData
            ) {
                $formConfiguration =
                    $this->getFormConfiguration(
                        $validated['project_id'],
                        $validated['sub_project_id'],
                        $conditionData['column_id'],
                        $index
                    );

                $employeeIds = collect(
                    $conditionData['users']
                )
                    ->map(function ($empId) {
                        return trim(
                            (string) $empId
                        );
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->implode(',');

                $data = [
                    'project_id' =>
                        $validated['project_id'],

                    'sub_project_id' =>
                        $validated['sub_project_id'],

                    'project_column' =>
                        $formConfiguration->label_name,

                    'condition' =>
                        $conditionData['operator'],

                    'value' =>
                        $conditionData['value'],

                    'emp_id' =>
                        $employeeIds,

                    'updated_by' =>
                        $currentUser,
                ];

                if ($index === 0) {
                    $configuration->update($data);
                } else {
                    $data['created_by'] =
                        $currentUser;

                    AlertConfiguration::create($data);
                }
            }
        });

        return response()->json([
            'status' => true,
            'message' =>
                'Alert configuration updated successfully.',
        ]);
    }

    /**
     * Delete one configuration row.
     */
    public function destroy(
        int $id
    ): JsonResponse {
        $configuration =
            AlertConfiguration::findOrFail($id);

        $configuration->delete();

        return response()->json([
            'status' => true,
            'message' =>
                'Alert configuration deleted successfully.',
        ]);
    }

    /**
     * Validation.
     */
    private function validateRequest(
        Request $request
    ): array {
        return $request->validate([
            'project_id' => [
                'required',
                'integer',
            ],

            'sub_project_id' => [
                'required',
                'integer',
            ],

            'conditions' => [
                'required',
                'array',
                'min:1',
            ],

            'conditions.*.column_id' => [
                'required',
                'integer',
            ],

            'conditions.*.operator' => [
                'required',
                Rule::in([
                    '>',
                    '>=',
                    '<',
                    '<=',
                    '=',
                    '!=',
                    'between',
                    'not between',
                    'like',
                    'not like',
                    'in',
                    'not in',
                ]),
            ],

            'conditions.*.value' => [
                'required',
                'string',
                'max:1000',
            ],

            'conditions.*.users' => [
                'required',
                'array',
                'min:1',
            ],

            'conditions.*.users.*' => [
                'required',
                'string',
                'max:100',
            ],
        ]);
    }

    /**
     * Validate and return selected form column.
     */
    private function getFormConfiguration(
        int $projectId,
        int $subProjectId,
        int $columnId,
        int $index
    ): FormConfiguration {
        $formConfiguration =
            FormConfiguration::query()
                ->where('id', $columnId)
                ->where(
                    'project_id',
                    (string) $projectId
                )
                ->where(
                    'sub_project_id',
                    (string) $subProjectId
                )
                ->whereNull('deleted_at')
                ->first();

        if (!$formConfiguration) {
            throw ValidationException::withMessages([
                "conditions.{$index}.column_id" =>
                    'The selected project column is invalid.',
            ]);
        }

        return $formConfiguration;
    }

    /**
     * Logged-in user key.
     */
    private function getCurrentUserKey(): string
    {
        if (Auth::check()) {
            return (string) (
                Auth::user()->emp_id
                ?? Auth::id()
            );
        }

        $sessionEmpId = data_get(
            Session::get('loginDetails'),
            'userDetail.emp_id'
        );

        return $sessionEmpId
            ? (string) $sessionEmpId
            : 'System';
    }
}