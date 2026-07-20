<?php

namespace App\Http\Controllers;

use App\Models\project;
use App\Models\subproject;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ProjectDuTargetSyncController extends Controller
{
    public function syncProjectDuTargets(): JsonResponse
    {
        try {
            $sourceUrl = 'https://aims.officeos.in/api/v1_users/project-du-targets';
            $syncToken = 'your-project-sync-token';

            if (empty($sourceUrl)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Project source API URL is missing.',
                ], 500);
            }

            $httpRequest = Http::withHeaders([
                'Accept' => 'application/json',
            ])->withOptions([
                'connect_timeout' => 10,
                'timeout' => 120,
                'verify' => true,
            ])->retry(3, 1000);

            if (!empty($syncToken)) {
                $httpRequest = $httpRequest->withHeaders([
                    'X-Sync-Token' => $syncToken,
                ]);
            }

            $response = $httpRequest->get($sourceUrl);

            if (!$response->successful()) {
                Log::error('Project DU source API returned an error', [
                    'source_url' => $sourceUrl,
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Source application returned an error.',
                    'source_status_code' => $response->status(),
                    'source_response' => config('app.debug')
                        ? $response->body()
                        : null,
                ], 502);
            }

            $payload = $response->json();

            if (!is_array($payload)) {
                Log::error('Project DU source API returned invalid JSON', [
                    'source_url' => $sourceUrl,
                    'response' => $response->body(),
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Source application returned invalid JSON.',
                ], 502);
            }

            $validator = Validator::make($payload, [
                'status' => [
                    'required',
                    'boolean',
                ],

                'data' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'data.*.client_id' => [
                    'nullable',
                    'integer',
                ],

                'data.*.project' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'data.*.subproject_id' => [
                    'nullable',
                    'integer',
                ],

                'data.*.subproject_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'data.*.scope_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'data.*.billable_fte' => [
                    'nullable',
                ],

                'data.*.actual_target' => [
                    'nullable',
                ],

                'data.*.du' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
            ]);

            if ($validator->fails()) {
                Log::error('Invalid project DU source response', [
                    'errors' => $validator->errors()->toArray(),
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Source API response validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            if (
                !isset($payload['status']) ||
                $payload['status'] !== true
            ) {
                return response()->json([
                    'status' => false,
                    'message' => isset($payload['message'])
                        ? $payload['message']
                        : 'Source application returned unsuccessful status.',
                ], 502);
            }

            /*
             * Active unique mappings:
             *
             * API client_id     = form_configurations.project_id
             * API subproject_id = form_configurations.sub_project_id
             */
            $formConfigurationPairs = DB::table('form_configurations')
                ->select(
                    'project_id',
                    'sub_project_id'
                )
                ->whereNull('deleted_at')
                ->whereNotNull('project_id')
                ->whereNotNull('sub_project_id')
                ->groupBy(
                    'project_id',
                    'sub_project_id'
                )
                ->get();

            if ($formConfigurationPairs->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No active project/subproject mappings were found in form_configurations. Existing records were not modified.',
                ], 422);
            }

            $validPairMap = $formConfigurationPairs
                ->mapWithKeys(function ($record) {
                    $pairKey = $this->makePairKey(
                        $record->project_id,
                        $record->sub_project_id
                    );

                    return [
                        $pairKey => true,
                    ];
                });

            $sourceRows = collect($payload['data']);

            $excludedScopes = [
                'coding - ops',
                'coding - qa',
                'dext',
            ];

            /*
             * Remove source records with missing IDs and excluded scopes.
             * Keep only one source record per project/subproject pair.
             */
            $eligibleSourceRows = $sourceRows
                ->filter(function ($row) use ($excludedScopes) {
                    $clientId = trim(
                        (string) ($row['client_id'] ?? '')
                    );

                    $subprojectId = trim(
                        (string) ($row['subproject_id'] ?? '')
                    );

                    $scopeName = strtolower(
                        trim((string) ($row['scope_name'] ?? ''))
                    );

                    return $clientId !== ''
                        && $subprojectId !== ''
                        && !in_array(
                            $scopeName,
                            $excludedScopes,
                            true
                        );
                })
                ->unique(function ($row) {
                    return $this->makePairKey(
                        $row['client_id'] ?? null,
                        $row['subproject_id'] ?? null
                    );
                })
                ->values();

            /*
             * Match source records with form_configurations.
             */
            $matchedSourceRows = $eligibleSourceRows
                ->filter(function ($row) use ($validPairMap) {
                    $pairKey = $this->makePairKey(
                        $row['client_id'] ?? null,
                        $row['subproject_id'] ?? null
                    );

                    return $validPairMap->has($pairKey);
                })
                ->values();

            $matchedPairMap = $matchedSourceRows
                ->mapWithKeys(function ($row) {
                    $pairKey = $this->makePairKey(
                        $row['client_id'] ?? null,
                        $row['subproject_id'] ?? null
                    );

                    return [
                        $pairKey => true,
                    ];
                });

            /*
             * Find configurations not available in the source API.
             */
            $missingMappings = $formConfigurationPairs
                ->filter(function ($record) use ($matchedPairMap) {
                    $pairKey = $this->makePairKey(
                        $record->project_id,
                        $record->sub_project_id
                    );

                    return !$matchedPairMap->has($pairKey);
                })
                ->map(function ($record) {
                    return [
                        'project_id' => (string) $record->project_id,
                        'sub_project_id' => (string) $record->sub_project_id,
                    ];
                })
                ->values();

            if ($matchedSourceRows->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No AIMS records matched form_configurations. Existing records were not modified.',

                    'form_configuration_pairs' =>
                        $formConfigurationPairs->count(),

                    'source_records' =>
                        $sourceRows->count(),

                    'eligible_source_pairs' => 0,

                    'records_saved' => 0,

                    'missing_mapping_count' =>
                        $missingMappings->count(),

                    'missing_mappings' =>
                        $missingMappings,
                ], 422);
            }

            $projectIds = $matchedSourceRows
                ->pluck('client_id')
                ->filter(function ($value) {
                    return $value !== null && $value !== '';
                })
                ->unique()
                ->values()
                ->all();

            $subProjectIds = $matchedSourceRows
                ->pluck('subproject_id')
                ->filter(function ($value) {
                    return $value !== null && $value !== '';
                })
                ->unique()
                ->values()
                ->all();

            /*
             * Project names are taken from the local projects table.
             */
            $projectNameMap = Project::where('status', 'Active')
                ->whereIn('project_id', $projectIds)
                ->whereNotNull('aims_project_name')
                ->pluck(
                    'aims_project_name',
                    'project_id'
                );

            /*
             * Subproject names are taken from the local subprojects table.
             */
            $subprojectNameMap = subproject::whereIn(
                    'project_id',
                    $projectIds
                )
                ->whereIn(
                    'sub_project_id',
                    $subProjectIds
                )
                ->select(
                    'project_id',
                    'sub_project_id',
                    'sub_project_name'
                )
                ->get()
                ->mapWithKeys(function ($record) {
                    $pairKey = $this->makePairKey(
                        $record->project_id,
                        $record->sub_project_id
                    );

                    return [
                        $pairKey => $record->sub_project_name,
                    ];
                });

            /*
             * Keep rows having both project and subproject names.
             */
            $localNameMatchedRows = $matchedSourceRows
                ->filter(function ($row) use (
                    $projectNameMap,
                    $subprojectNameMap
                ) {
                    $clientId = trim(
                        (string) ($row['client_id'] ?? '')
                    );

                    $subprojectId = trim(
                        (string) ($row['subproject_id'] ?? '')
                    );

                    $pairKey = $this->makePairKey(
                        $clientId,
                        $subprojectId
                    );

                    $projectName = trim(
                        (string) $projectNameMap->get(
                            $clientId,
                            ''
                        )
                    );

                    $subprojectName = trim(
                        (string) $subprojectNameMap->get(
                            $pairKey,
                            ''
                        )
                    );

                    return $projectName !== ''
                        && $subprojectName !== '';
                })
                ->values();

            /*
             * Report pairs where local names are missing.
             */
            $missingLocalNames = $matchedSourceRows
                ->filter(function ($row) use (
                    $projectNameMap,
                    $subprojectNameMap
                ) {
                    $clientId = trim(
                        (string) ($row['client_id'] ?? '')
                    );

                    $subprojectId = trim(
                        (string) ($row['subproject_id'] ?? '')
                    );

                    $pairKey = $this->makePairKey(
                        $clientId,
                        $subprojectId
                    );

                    $projectName = trim(
                        (string) $projectNameMap->get(
                            $clientId,
                            ''
                        )
                    );

                    $subprojectName = trim(
                        (string) $subprojectNameMap->get(
                            $pairKey,
                            ''
                        )
                    );

                    return $projectName === ''
                        || $subprojectName === '';
                })
                ->map(function ($row) use (
                    $projectNameMap,
                    $subprojectNameMap
                ) {
                    $clientId = trim(
                        (string) ($row['client_id'] ?? '')
                    );

                    $subprojectId = trim(
                        (string) ($row['subproject_id'] ?? '')
                    );

                    $pairKey = $this->makePairKey(
                        $clientId,
                        $subprojectId
                    );

                    return [
                        'project_id' => $clientId,
                        'sub_project_id' => $subprojectId,

                        'project_name_found' =>
                            trim((string) $projectNameMap->get(
                                $clientId,
                                ''
                            )) !== '',

                        'subproject_name_found' =>
                            trim((string) $subprojectNameMap->get(
                                $pairKey,
                                ''
                            )) !== '',
                    ];
                })
                ->values();

            if ($localNameMatchedRows->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No matched records had valid local project and subproject names. Existing records were not modified.',

                    'missing_local_name_count' =>
                        $missingLocalNames->count(),

                    'missing_local_names' =>
                        $missingLocalNames,
                ], 422);
            }

            $syncedAt = now();

            /*
             * Example:
             *
             * year  = 2026
             * month = July
             */
            $currentYear = (int) $syncedAt->format('Y');
            $currentMonth = $syncedAt->format('F');

            /*
             * Prepare current year/month rows.
             *
             * Do not provide the primary-key id.
             */
            $rows = $localNameMatchedRows
                ->map(function ($row) use (
                    $syncedAt,
                    $currentYear,
                    $currentMonth,
                    $projectNameMap,
                    $subprojectNameMap
                ) {
                    $clientId = (int) ($row['client_id'] ?? 0);
                    $subprojectId = (int) ($row['subproject_id'] ?? 0);

                    $pairKey = $this->makePairKey(
                        $clientId,
                        $subprojectId
                    );

                    return [
                        'client_id' => $clientId,

                        'project' => trim(
                            (string) $projectNameMap->get(
                                $clientId,
                                ''
                            )
                        ),

                        'subproject_id' => $subprojectId,

                        'subproject_name' => trim(
                            (string) $subprojectNameMap->get(
                                $pairKey,
                                ''
                            )
                        ),

                        'scope_name' => $this->nullableString(
                            $row['scope_name'] ?? null
                        ),

                        'billable_fte' => $this->nullableString(
                            $row['billable_fte'] ?? null
                        ),

                        'actual_target' => $this->nullableString(
                            $row['actual_target'] ?? null
                        ),

                        'du' => $this->nullableString(
                            $row['du'] ?? null
                        ),

                        'year' => $currentYear,
                        'month' => $currentMonth,

                        'synced_at' => $syncedAt,
                        'created_at' => $syncedAt,
                        'updated_at' => $syncedAt,
                    ];
                })
                ->values();

            $insertedCount = 0;
            $updatedCount = 0;
            $deletedStaleCount = 0;

            /*
             * Preserve IDs for records already existing in the same
             * year and month.
             */
            DB::transaction(function () use (
                $rows,
                $currentYear,
                $currentMonth,
                $syncedAt,
                &$insertedCount,
                &$updatedCount,
                &$deletedStaleCount
            ) {
                /*
                 * Get current year/month records indexed by
                 * client_id|subproject_id.
                 */
                $existingRows = DB::table('aims_project_du_targets')
                    ->select(
                        'id',
                        'client_id',
                        'subproject_id'
                    )
                    ->where('year', $currentYear)
                    ->where('month', $currentMonth)
                    ->orderBy('id')
                    ->get()
                    ->mapWithKeys(function ($record) {
                        $pairKey = $this->makePairKey(
                            $record->client_id,
                            $record->subproject_id
                        );

                        return [
                            $pairKey => $record,
                        ];
                    });

                $retainedIds = [];

                foreach ($rows as $row) {
                    $pairKey = $this->makePairKey(
                        $row['client_id'],
                        $row['subproject_id']
                    );

                    /*
                     * Existing current-month pair:
                     * update the existing record and preserve its ID.
                     */
                    if ($existingRows->has($pairKey)) {
                        $existingRecord = $existingRows->get($pairKey);

                        $updateData = $row;

                        /*
                         * Preserve the original created_at value.
                         */
                        unset($updateData['created_at']);

                        $updateData['updated_at'] = $syncedAt;

                        DB::table('aims_project_du_targets')
                            ->where('id', $existingRecord->id)
                            ->update($updateData);

                        $retainedIds[] = $existingRecord->id;
                        $updatedCount++;
                    } else {
                        /*
                         * New pair for the current year/month:
                         * MySQL assigns the next auto-increment ID.
                         */
                        $insertedId = DB::table(
                            'aims_project_du_targets'
                        )->insertGetId($row);

                        $retainedIds[] = $insertedId;
                        $insertedCount++;
                    }
                }

                /*
                 * Delete records from the same year/month that are no
                 * longer available in the current synchronization.
                 */
                $staleQuery = DB::table('aims_project_du_targets')
                    ->where('year', $currentYear)
                    ->where('month', $currentMonth);

                if (!empty($retainedIds)) {
                    $staleQuery->whereNotIn(
                        'id',
                        $retainedIds
                    );
                }

                $deletedStaleCount = $staleQuery->delete();
            }, 3);

            return response()->json([
                'status' => true,
                'message' => 'Project DU target details synchronized successfully.',

                'current_year' =>
                    $currentYear,

                'current_month' =>
                    $currentMonth,

                'form_configuration_pairs' =>
                    $formConfigurationPairs->count(),

                'source_records' =>
                    $sourceRows->count(),

                'eligible_source_pairs' =>
                    $matchedPairMap->count(),

                'local_name_matched_pairs' =>
                    $localNameMatchedRows->count(),

                'records_processed' =>
                    $rows->count(),

                'updated_records' =>
                    $updatedCount,

                'inserted_records' =>
                    $insertedCount,

                'deleted_stale_records' =>
                    $deletedStaleCount,

                'missing_mapping_count' =>
                    $missingMappings->count(),

                'missing_mappings' =>
                    $missingMappings,

                'missing_local_name_count' =>
                    $missingLocalNames->count(),

                'missing_local_names' =>
                    $missingLocalNames,

                'synced_at' =>
                    $syncedAt->toDateTimeString(),
            ], 200);

        } catch (Throwable $exception) {
            Log::error('Project DU synchronization failed', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Project DU target synchronization failed.',
                'error' => config('app.debug')
                    ? $exception->getMessage()
                    : null,
            ], 500);
        }
    }

    /**
     * Generate project/subproject matching key.
     */
    private function makePairKey(
        $projectId,
        $subprojectId
    ): string {
        return trim((string) $projectId)
            . '|'
            . trim((string) $subprojectId);
    }

    /**
     * Convert null or empty values into NULL.
     *
     * PHP 7.4 compatible.
     */
    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }
}