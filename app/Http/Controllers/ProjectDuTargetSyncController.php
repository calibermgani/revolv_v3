<?php

namespace App\Http\Controllers;

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
            /*
             * Enter the complete source application API URL directly.
             */
            $sourceUrl = 'https://aims.officeos.in/api/v1_users/project-du-targets';
           

            /*
             * Enter the same token configured in the source application.
             * Keep this empty when the source API does not require a token.
             */
            $syncToken = 'your-project-sync-token';

            if (empty($sourceUrl)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Project source API URL is missing.',
                ], 500);
            }

            /*
             * Compatible with older Laravel HTTP client versions.
             */
            $httpRequest = Http::withHeaders([
                'Accept' => 'application/json',
            ])->withOptions([
                'connect_timeout' => 10,
                'timeout' => 120,
                'verify' => true,
            ])->retry(3, 1000);

            /*
             * Send the synchronization token only when it is provided.
             */
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
                'data.*.project' => [
                    'required',
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
                    'payload' => $payload,
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Source API response validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            if (($payload['status'] ?? false) !== true) {
                return response()->json([
                    'status' => false,
                    'message' => 'Source application returned unsuccessful status.',
                ], 502);
            }

            $syncedAt = now();

            $rows = collect($payload['data'])
                ->map(function ($row) use ($syncedAt) {
                    return [
                        'project' => trim(
                            (string) ($row['project'] ?? '')
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

                        'synced_at' => $syncedAt,
                        'created_at' => $syncedAt,
                        'updated_at' => $syncedAt,
                    ];
                })
                // ->filter(function ($row) {
                //     return $row['project'] !== '';
                // })
                ->filter(function ($row) {
                    $project = trim((string) ($row['project'] ?? ''));
                    $scopeName = trim((string) ($row['scope_name'] ?? ''));

                    $excludedScopes = [
                        'Coding - Ops',
                        'Coding - QA',
                        'DEXT',
                    ];

                    return $project !== ''
                        && !in_array($scopeName, $excludedScopes, true);
                })
                ->values();

            /*
             * Do not delete current records if the API returns no valid data.
             */
            if ($rows->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Source API returned no valid records. Existing records were not modified.',
                ], 422);
            }

            DB::transaction(function () use ($rows) {
                /*
                 * Remove the previous complete snapshot.
                 */
                DB::table('aims_project_du_targets')->delete();

                /*
                 * Insert the new snapshot in batches.
                 */
                $rows->chunk(500)->each(function ($chunk) {
                    DB::table('aims_project_du_targets')
                        ->insert($chunk->all());
                });
            }, 3);

            return response()->json([
                'status' => true,
                'message' => 'Project DU target details synchronized successfully.',
                'records_saved' => $rows->count(),
                'synced_at' => $syncedAt->toDateTimeString(),
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

    /*
     * Compatible with PHP 7.4.
     */
    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}