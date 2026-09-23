<?php

namespace App\Http\Controllers;

use App\Models\AimsUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AimsUserSyncController extends Controller
{
    public function syncAimsUsers()
    {
        try {
            $sourceUrl = 'https://aims.officeos.in/api/v1_users/get_aims_active_users';

            $response = Http::timeout(120)
                ->acceptJson()
                ->get($sourceUrl, [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                ]);

            if (!$response->successful()) {
                Log::error('AIMS active users API failed.', [
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Unable to fetch active users from AIMS.',
                    'code' => $response->status(),
                ], 500);
            }

            $apiResponse = $response->json();

            if (!isset($apiResponse['status']) || $apiResponse['status'] !== true) {
                return response()->json([
                    'status' => false,
                    'message' => $apiResponse['message'] ?? 'AIMS API returned an unsuccessful response.',
                    'response' => $apiResponse,
                ], 422);
            }

            $users = $apiResponse['data'] ?? [];

            if (empty($users)) {
                return response()->json([
                    'status' => true,
                    'message' => 'No active users received from AIMS.',
                    'count' => 0,
                ], 200);
            }

            $now = now();
            $rows = [];
            $emptyEmpIds = [];
            $duplicateEmpIds = [];

            foreach ($users as $user) {
                $empId = isset($user['emp_id']) ? trim((string) $user['emp_id']) : '';
                if ($empId === '') {
                    $emptyEmpIds[] = [
                        'aims_user_id' => $user['user_id'] ?? null,
                        'user_name' => $user['user_name'] ?? null,
                        'status' => $user['status'] ?? null,
                    ];
                    continue;
                }

                if (isset($rows[$empId])) {
                    $duplicateEmpIds[] = [
                        'emp_id' => $empId,
                        'previous_aims_user_id' => $rows[$empId]['aims_user_id'] ?? null,
                        'current_aims_user_id' => $user['user_id'] ?? null,
                        'user_name' => $user['user_name'] ?? null,
                    ];
                }

                $rows[$empId] = [
                    'aims_user_id' => $user['user_id'] ?? null,
                    'emp_id' => $empId,
                    'user_name' => $user['user_name'] ?? null,
                    'status' => $user['status'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ];
            }

            foreach (array_chunk(array_values($rows), 500) as $chunk) {
                AimsUser::upsert(
                    $chunk,
                    ['emp_id'],
                    ['aims_user_id', 'user_name', 'status', 'updated_at', 'deleted_at']
                );
            }

            Log::info('AIMS users sync counts.', [
                'api_count' => count($users),
                'inserted_or_updated' => count($rows),
                'empty_emp_id_count' => count($emptyEmpIds),
                'duplicate_emp_id_count' => count($duplicateEmpIds),
                'empty_emp_ids' => $emptyEmpIds,
                'duplicate_emp_ids' => $duplicateEmpIds,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'AIMS users synced successfully.',
                'count' => count($rows),
                'api_count' => count($users),
                'empty_emp_id_count' => count($emptyEmpIds),
                'duplicate_emp_id_count' => count($duplicateEmpIds),
            ], 200);
        } catch (\Throwable $e) {
            Log::error('AIMS users sync failed.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'AIMS users synchronization failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
