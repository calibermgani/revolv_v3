<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\InventoryExeFile;
use App\Models\CCEmailIds;
use App\Mail\ProcodeInventoryExeFile;
use App\Http\Helper\Admin\Helpers as Helpers;
use App\Models\InventoryErrorLogs;
use App\Models\OmsiAr;
use App\Models\OmsiArDuplicates;
use App\Models\NuAr;
use App\Models\NuArDuplicates;
use App\Models\ChsiAr;
use App\Models\ChsiArDuplicates;
use App\Models\MhawAr;
use App\Models\MhawArDuplicates;
use App\Models\LscAr;
use App\Models\LscArDuplicates;
use App\Models\MatcAr;
use App\Models\MatcArDuplicates;
use App\Models\GchsAr;
use App\Models\GchsArDuplicates;
use App\Models\AsopAr;
use App\Models\AsopArDuplicates;
use App\Models\RcmAr;
use App\Models\RcmArDuplicates;
use App\Models\RmcAr;
use App\Models\RmcArDuplicates;
use App\Models\AopsAr;
use App\Models\AopsArDuplicates;
use App\Models\NaAr;
use App\Models\NaArDuplicates;
use App\Models\LuAr;
use App\Models\LuArDuplicates;
use App\Models\BmrhcAr;
use App\Models\BmrhcArDuplicates;
use App\Models\CarsAr;
use App\Models\CarsArDuplicates;
use App\Models\NmNcgGottengerAr;
use App\Models\NmNcgGottengerArDuplicates;
use App\Models\NmNcgHudsonAr;
use App\Models\NmNcgHudsonArDuplicates;
use App\Models\NmNcgHscAr;
use App\Models\NmNcgHscArDuplicates;
use App\Models\NmNcgPsssf;
use App\Models\NmNcgPsssfDuplicates;
use App\Models\SrmgAr;
use App\Models\SrmgArDuplicates;
use App\Models\VuaAr;
use App\Models\VuaArDuplicates;
use App\Models\AmbcPrnAr;
use App\Models\AmbcPrnArDuplicates;
use App\Models\CfpsAr;
use App\Models\CfpsArDuplicates;
use App\Models\DkmgAr;
use App\Models\DkmgArDuplicates;
use App\Models\BncmhcAr;
use App\Models\BncmhcArDuplicates;
use App\Models\RnAr;
use App\Models\RnArDuplicates;
use App\Models\MmhAr;
use App\Models\MmhArDuplicates;
use App\Models\RhAr;
use App\Models\RhArDuplicates;
use App\Models\AmbcAmbcAr;
use App\Models\AmbcAmbcArDuplicates;
use App\Models\HvccAr;
use App\Models\HvccArDuplicates;


class ProjectAutomationController extends Controller
{

    public function inventoryExeFile(Request $request)
    {
        try {
            $attributes = [
                'project_id' => isset($request->project_id) ? $request->project_id : NULL,
                'sub_project_id' => isset($request->sub_project_id) && $request->sub_project_id != "NULL" ? $request->sub_project_id : NULL,
                'file_name' => isset($request->file_name) ? $request->file_name : NULL,
                'exe_date' => now()->format('Y-m-d H:i:s'),
                'upload_status'=> isset($request->upload_status) ? $request->upload_status : 'auto'
            ];
            $whereAttributes = [
                'project_id' => isset($request->project_id) ? $request->project_id : NULL,
                'sub_project_id' => isset($request->sub_project_id) && $request->sub_project_id != "NULL" ? $request->sub_project_id : NULL,
                'file_name' => isset($request->file_name) ? $request->file_name : NULL
            ];
            $exists = InventoryExeFile::where($whereAttributes)->whereDate('exe_date', now()->format('Y-m-d'))->exists();
            // if (!$exists) {
                $currentDate = Carbon::now()->format('Y-m-d');
                if (isset($request->project_id)) {
                    $projectId = $request->project_id;
                    $clientName = Helpers::projectName($projectId)->project_name;
                    $aimsClientName = Helpers::projectName($projectId)->aims_project_name;
                    if (isset($request->sub_project_id) && $request->sub_project_id != "NULL" && $request->sub_project_id != NULL) {
                        $subProjectId = $request->sub_project_id;
                        $subProjectName = Helpers::subProjectName($projectId, $subProjectId)->sub_project_name;
                        $table_name = Str::slug((Str::lower($clientName) . '_' . Str::lower($subProjectName)), '_');
                        $prjoectName = $aimsClientName . ' - ' . $subProjectName;
                    } else {
                        $subProjectId = NULL;
                        $subProjectText = "project";
                        $table_name = Str::slug((Str::lower($clientName) . '_' . Str::lower($subProjectText)), '_');
                        $prjoectName = $aimsClientName;
                    }
                } else {
                    $projectId = NULL;
                }

                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDuplicate = "App\\Models\\" . $modelName . 'Duplicates';
                $currentCount = 0;
                if (class_exists($modelClass)) {
                    $currentCount =  isset($request->inventory_count) ? $request->inventory_count : $modelClass::where('invoke_date', $currentDate)->where('chart_status', 'CE_Assigned')->count();
                    $duplicateCount = $modelClassDuplicate::where('invoke_date', $currentDate)->where('chart_status', 'CE_Assigned')->count();
                    // $assignedCount = $modelClass::where('invoke_date', $currentDate)->where('chart_status', 'CE_Assigned')->whereNotNull('CE_emp_id')->count();
                    // $unAssignedCount = $modelClass::where('invoke_date', $currentDate)->where('chart_status', 'CE_Assigned')->whereNull('CE_emp_id')->count();
                    $assignedCount = isset($request->assign_count) ? $request->assign_count : $modelClass::where('invoke_date', $currentDate)->where('chart_status', 'CE_Assigned')->whereNotNull('CE_emp_id')->count();
                    $unAssignedCount = isset($request->unassign_count) ? $request->unassign_count : $modelClass::where('invoke_date', $currentDate)->where('chart_status', 'CE_Assigned')->whereNull('CE_emp_id')->count();
                }
                $procodeProjectsCurrent = [];
                Log::info($prjoectName . " count is " . $currentCount);
                if ($currentCount> 0) {
                    $procodeProjectsCurrent['project'] = $prjoectName;
                    $procodeProjectsCurrent['currentCount'] = $currentCount;
                    $procodeProjectsCurrent['duplicateCount'] = $duplicateCount;
                    $procodeProjectsCurrent['assignedCount'] = $assignedCount;
                    $procodeProjectsCurrent['unAssignedCount'] = $unAssignedCount;
                    $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'inventory exe file to mail id')->first();
                    $toMailId = explode(",", $toMail->cc_emails);
                    // $toMailId = "mgani@caliberfocus.com";
                    // $ccMailId = "vijayalaxmi@caliberfocus.com";
                    $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'inventory exe file')->first();
                    $ccMailId = explode(",", $ccMail->cc_emails);

                    $mailDate = Carbon::now()->format('m/d/Y');
                    $mailHeader = $prjoectName . " - Inventory Upload Successful - " . $mailDate;
                    $project_information["project_id"] = $attributes["project_id"];
                    $project_information["sub_project_id"] = $attributes["sub_project_id"];
                    $project_information["error_description"] = "Default Assigned Count: " . $procodeProjectsCurrent['assignedCount'] . PHP_EOL . " Inventory Uploaded Time: " . now()->format('m/d/Y g:i A');
                    $project_information["error_status_code"] = 200;
                    $project_information["error_date"] = now()->format('Y-m-d H:i:s');
                    $attributes["inventory_count"] = $currentCount;
                    InventoryExeFile::create($attributes);
                    InventoryErrorLogs::create($project_information);
                    if (isset($toMailId) && !empty($toMailId)) {
                        try {
                            Mail::to($toMailId)->cc($ccMailId)->send(new ProcodeInventoryExeFile($mailHeader, $procodeProjectsCurrent));
                            Log::info($prjoectName . "mail sent ");
                        } catch (\Exception $e) {
                            Log::error('Mail sending failed: ' . $e->getMessage());
                        }
                    }
                    return response()->json(['message' => 'Inventory File Inserted Successfully']);
                }
                return response()->json(['message' => 'Inventory mail was not sent because the count is zero']);
            // } else {
            //     return response()->json(['message' => 'Inventory File already exists']);
            // }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    // Resolv Details

    public function onpoint(Request $request)
    {
        try {
            $attributes = [
                'office_keys' => isset($request->office_keys) && $request->office_keys != "NULL" ? $request->office_keys : NULL,
                'worklist' => isset($request->worklist) && $request->worklist != "NULL" ? $request->worklist : NULL,
                'insurance_balance' => isset($request->insurance_balance) && $request->insurance_balance != "NULL" ? $request->insurance_balance : NULL,
                'past_due_days' => isset($request->past_due_days) && $request->past_due_days != "NULL" ? $request->past_due_days : NULL,
                'visit' => isset($request->visit) && $request->visit != "NULL" ? $request->visit : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                'last_date' => isset($request->last_date) && $request->last_date != "NULL" ? $request->last_date : NULL,
                'last_action' => isset($request->last_action) && $request->last_action != "NULL" ? $request->last_action : NULL,
                'follow_up_date' => isset($request->follow_up_date) && $request->follow_up_date != "NULL" ? $request->follow_up_date : NULL,
                'follow_up_action' => isset($request->follow_up_action) && $request->follow_up_action != "NULL" ? $request->follow_up_action : NULL,
            ];

            $duplicateRecordExisting  =  OmsiAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                OmsiAr::insert([
                        'office_keys' => isset($request->office_keys) && $request->office_keys != "NULL" ? $request->office_keys : NULL,
                        'worklist' => isset($request->worklist) && $request->worklist != "NULL" ? $request->worklist : NULL,
                        'insurance_balance' => isset($request->insurance_balance) && $request->insurance_balance != "NULL" ? $request->insurance_balance : NULL,
                        'past_due_days' => isset($request->past_due_days) && $request->past_due_days != "NULL" ? $request->past_due_days : NULL,
                        'visit' => isset($request->visit) && $request->visit != "NULL" ? $request->visit : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                        'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                        'last_date' => isset($request->last_date) && $request->last_date != "NULL" ? $request->last_date : NULL,
                        'last_action' => isset($request->last_action) && $request->last_action != "NULL" ? $request->last_action : NULL,
                        'follow_up_date' => isset($request->follow_up_date) && $request->follow_up_date != "NULL" ? $request->follow_up_date : NULL,
                        'follow_up_action' => isset($request->follow_up_action) && $request->follow_up_action != "NULL" ? $request->follow_up_action : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  OmsiAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                            'office_keys' => isset($request->office_keys) && $request->office_keys != "NULL" ? $request->office_keys : NULL,
                            'worklist' => isset($request->worklist) && $request->worklist != "NULL" ? $request->worklist : NULL,
                            'insurance_balance' => isset($request->insurance_balance) && $request->insurance_balance != "NULL" ? $request->insurance_balance : NULL,
                            'past_due_days' => isset($request->past_due_days) && $request->past_due_days != "NULL" ? $request->past_due_days : NULL,
                            'visit' => isset($request->visit) && $request->visit != "NULL" ? $request->visit : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                            'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                            'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                            'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                            'last_date' => isset($request->last_date) && $request->last_date != "NULL" ? $request->last_date : NULL,
                            'last_action' => isset($request->last_action) && $request->last_action != "NULL" ? $request->last_action : NULL,
                            'follow_up_date' => isset($request->follow_up_date) && $request->follow_up_date != "NULL" ? $request->follow_up_date : NULL,
                            'follow_up_action' => isset($request->follow_up_action) && $request->follow_up_action != "NULL" ? $request->follow_up_action : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                    ]);
                }
                return response()->json(['message' => 'Existing Record Updated Successfully']);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function onpointDuplicates(Request $request)
    {
        try {
            OmsiArDuplicates::insert([
                'office_keys' => isset($request->office_keys) && $request->slip != "NULL" ? $request->office_keys : NULL,
                'worklist' => isset($request->worklist) && $request->worklist != "NULL" ? $request->worklist : NULL,
                'insurance_balance' => isset($request->insurance_balance) && $request->insurance_balance != "NULL" ? $request->insurance_balance : NULL,
                'past_due_days' => isset($request->past_due_days) && $request->past_due_days != "NULL" ? $request->past_due_days : NULL,
                'visit' => isset($request->visit) && $request->visit != "NULL" ? $request->visit : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                'last_date' => isset($request->last_date) && $request->last_date != "NULL" ? $request->last_date : NULL,
                'last_action' => isset($request->last_action) && $request->last_action != "NULL" ? $request->last_action : NULL,
                'follow_up_date' => isset($request->follow_up_date) && $request->follow_up_date != "NULL" ? $request->follow_up_date : NULL,
                'follow_up_action' => isset($request->follow_up_action) && $request->follow_up_action != "NULL" ? $request->follow_up_action : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
            ]);
            return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    public function nauUrology(Request $request)
    {
        try {
            $attributes = [
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                'srv_day' => isset($request->srv_day) && $request->srv_day != "NULL" ? $request->srv_day : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'sup_prvdr' => isset($request->sup_prvdr) && $request->sup_prvdr != "NULL" ? $request->sup_prvdr : NULL,
                'patient_primary_ins_pkg_name' => isset($request->patient_primary_ins_pkg_name) && $request->patient_primary_ins_pkg_name != "NULL" ? $request->patient_primary_ins_pkg_name : NULL,
                'patient_secondary_ins_pkg_name' => isset($request->patient_secondary_ins_pkg_name) && $request->patient_secondary_ins_pkg_name != "NULL" ? $request->patient_secondary_ins_pkg_name : NULL,
                'primary_status' => isset($request->primary_status) && $request->primary_status != "NULL" ? $request->primary_status : NULL,
                'secondary_status' => isset($request->secondary_status) && $request->secondary_status != "NULL" ? $request->secondary_status : NULL,
                'proccode' => isset($request->proccode) && $request->proccode != "NULL" ? $request->proccode : NULL,
                'all_chgs' => isset($request->all_chgs) && $request->all_chgs != "NULL" ? $request->all_chgs : NULL,
                'primary_bal' => isset($request->primary_bal) && $request->primary_bal != "NULL" ? $request->primary_bal : NULL,
                'secondary_bal' => isset($request->secondary_bal) && $request->secondary_bal != "NULL" ? $request->secondary_bal : NULL,
            ];

            $duplicateRecordExisting  =  NuAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                NuAr::insert([
                        'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                        'srv_day' => isset($request->srv_day) && $request->srv_day != "NULL" ? $request->srv_day : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'sup_prvdr' => isset($request->sup_prvdr) && $request->sup_prvdr != "NULL" ? $request->sup_prvdr : NULL,
                        'patient_primary_ins_pkg_name' => isset($request->patient_primary_ins_pkg_name) && $request->patient_primary_ins_pkg_name != "NULL" ? $request->patient_primary_ins_pkg_name : NULL,
                        'patient_secondary_ins_pkg_name' => isset($request->patient_secondary_ins_pkg_name) && $request->patient_secondary_ins_pkg_name != "NULL" ? $request->patient_secondary_ins_pkg_name : NULL,
                        'primary_status' => isset($request->primary_status) && $request->primary_status != "NULL" ? $request->primary_status : NULL,
                        'secondary_status' => isset($request->secondary_status) && $request->secondary_status != "NULL" ? $request->secondary_status : NULL,
                        'proccode' => isset($request->proccode) && $request->proccode != "NULL" ? $request->proccode : NULL,
                        'all_chgs' => isset($request->all_chgs) && $request->all_chgs != "NULL" ? $request->all_chgs : NULL,
                        'primary_bal' => isset($request->primary_bal) && $request->primary_bal != "NULL" ? $request->primary_bal : NULL,
                        'secondary_bal' => isset($request->secondary_bal) && $request->secondary_bal != "NULL" ? $request->secondary_bal : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  NuAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                            'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                            'srv_day' => isset($request->srv_day) && $request->srv_day != "NULL" ? $request->srv_day : NULL,
                            'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                            'sup_prvdr' => isset($request->sup_prvdr) && $request->sup_prvdr != "NULL" ? $request->sup_prvdr : NULL,
                            'patient_primary_ins_pkg_name' => isset($request->patient_primary_ins_pkg_name) && $request->patient_primary_ins_pkg_name != "NULL" ? $request->patient_primary_ins_pkg_name : NULL,
                            'patient_secondary_ins_pkg_name' => isset($request->patient_secondary_ins_pkg_name) && $request->patient_secondary_ins_pkg_name != "NULL" ? $request->patient_secondary_ins_pkg_name : NULL,
                            'primary_status' => isset($request->primary_status) && $request->primary_status != "NULL" ? $request->primary_status : NULL,
                            'secondary_status' => isset($request->secondary_status) && $request->secondary_status != "NULL" ? $request->secondary_status : NULL,
                            'proccode' => isset($request->proccode) && $request->proccode != "NULL" ? $request->proccode : NULL,
                            'all_chgs' => isset($request->all_chgs) && $request->all_chgs != "NULL" ? $request->all_chgs : NULL,
                            'primary_bal' => isset($request->primary_bal) && $request->primary_bal != "NULL" ? $request->primary_bal : NULL,
                            'secondary_bal' => isset($request->secondary_bal) && $request->secondary_bal != "NULL" ? $request->secondary_bal : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                    ]);
                }
                return response()->json(['message' => 'Existing Record Updated Successfully']);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function nauUrologyDuplicates(Request $request)
    {
        try {
            NuArDuplicates::insert([
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                'srv_day' => isset($request->srv_day) && $request->srv_day != "NULL" ? $request->srv_day : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'sup_prvdr' => isset($request->sup_prvdr) && $request->sup_prvdr != "NULL" ? $request->sup_prvdr : NULL,
                'patient_primary_ins_pkg_name' => isset($request->patient_primary_ins_pkg_name) && $request->patient_primary_ins_pkg_name != "NULL" ? $request->patient_primary_ins_pkg_name : NULL,
                'patient_secondary_ins_pkg_name' => isset($request->patient_secondary_ins_pkg_name) && $request->patient_secondary_ins_pkg_name != "NULL" ? $request->patient_secondary_ins_pkg_name : NULL,
                'primary_status' => isset($request->primary_status) && $request->primary_status != "NULL" ? $request->primary_status : NULL,
                'secondary_status' => isset($request->secondary_status) && $request->secondary_status != "NULL" ? $request->secondary_status : NULL,
                'proccode' => isset($request->proccode) && $request->proccode != "NULL" ? $request->proccode : NULL,
                'all_chgs' => isset($request->all_chgs) && $request->all_chgs != "NULL" ? $request->all_chgs : NULL,
                'primary_bal' => isset($request->primary_bal) && $request->primary_bal != "NULL" ? $request->primary_bal : NULL,
                'secondary_bal' => isset($request->secondary_bal) && $request->secondary_bal != "NULL" ? $request->secondary_bal : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
            ]);
            return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    public function chestnutAr(Request $request)
    {
        try {
            $attributes = [
                'claims_no' => isset($request->claims_no) && $request->claims_no != "NULL" ? $request->claims_no : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'pvdr' => isset($request->pvdr) && $request->pvdr != "NULL" ? $request->pvdr : NULL,
                'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                'guarantor_name'=>isset($request->guarantor_name) && $request->guarantor_name != "NULL" ? $request->guarantor_name : NULL,
                'transfer_days'=>isset($request->transfer_days) && $request->transfer_days != "NULL" ? $request->transfer_days : NULL,
                'with_held'=>isset($request->with_held) && $request->with_held != "NULL" ? $request->with_held : NULL,
                'adjustment'=>isset($request->adjustment) && $request->adjustment != "NULL" ? $request->adjustment : NULL,
                'pmts_else_adjs'=>isset($request->pmts_else_adjs) && $request->pmts_else_adjs != "NULL" ? $request->pmts_else_adjs : NULL,
                'claim_type'=>isset($request->claim_type) && $request->claim_type != "NULL" ? $request->claim_type : NULL,
            ];

            $duplicateRecordExisting  =  ChsiAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                ChsiAr::insert([
                        'claims_no' => isset($request->claims_no) && $request->claims_no != "NULL" ? $request->claims_no : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'pvdr' => isset($request->pvdr) && $request->pvdr != "NULL" ? $request->pvdr : NULL,
                        'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                        'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                        'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                        'guarantor_name'=>isset($request->guarantor_name) && $request->guarantor_name != "NULL" ? $request->guarantor_name : NULL,
                        'transfer_days'=>isset($request->transfer_days) && $request->transfer_days != "NULL" ? $request->transfer_days : NULL,
                        'with_held'=>isset($request->with_held) && $request->with_held != "NULL" ? $request->with_held : NULL,
                        'adjustment'=>isset($request->adjustment) && $request->adjustment != "NULL" ? $request->adjustment : NULL,
                        'pmts_else_adjs'=>isset($request->pmts_else_adjs) && $request->pmts_else_adjs != "NULL" ? $request->pmts_else_adjs : NULL,
                        'claim_type'=>isset($request->claim_type) && $request->claim_type != "NULL" ? $request->claim_type : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  ChsiAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                            'claims_no' => isset($request->claims_no) && $request->claims_no != "NULL" ? $request->claims_no : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                            'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                            'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                            'pvdr' => isset($request->pvdr) && $request->pvdr != "NULL" ? $request->pvdr : NULL,
                            'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                            'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                            'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                            'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                            'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                            'guarantor_name'=>isset($request->guarantor_name) && $request->guarantor_name != "NULL" ? $request->guarantor_name : NULL,
                            'transfer_days'=>isset($request->transfer_days) && $request->transfer_days != "NULL" ? $request->transfer_days : NULL,
                            'with_held'=>isset($request->with_held) && $request->with_held != "NULL" ? $request->with_held : NULL,
                            'adjustment'=>isset($request->adjustment) && $request->adjustment != "NULL" ? $request->adjustment : NULL,
                            'pmts_else_adjs'=>isset($request->pmts_else_adjs) && $request->pmts_else_adjs != "NULL" ? $request->pmts_else_adjs : NULL,
                            'claim_type'=>isset($request->claim_type) && $request->claim_type != "NULL" ? $request->claim_type : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                    ]);
                }
                return response()->json(['message' => 'Existing Record Updated Successfully']);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function chestnutArDuplicates(Request $request)
    {
        try {
            ChsiArDuplicates::insert([
                'claims_no' => isset($request->claims_no) && $request->claims_no != "NULL" ? $request->claims_no : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'pvdr' => isset($request->pvdr) && $request->pvdr != "NULL" ? $request->pvdr : NULL,
                'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                'guarantor_name'=>isset($request->guarantor_name) && $request->guarantor_name != "NULL" ? $request->guarantor_name : NULL,
                'transfer_days'=>isset($request->transfer_days) && $request->transfer_days != "NULL" ? $request->transfer_days : NULL,
                'with_held'=>isset($request->with_held) && $request->with_held != "NULL" ? $request->with_held : NULL,
                'adjustment'=>isset($request->adjustment) && $request->adjustment != "NULL" ? $request->adjustment : NULL,
                'pmts_else_adjs'=>isset($request->pmts_else_adjs) && $request->pmts_else_adjs != "NULL" ? $request->pmts_else_adjs : NULL,
                'claim_type'=>isset($request->claim_type) && $request->claim_type != "NULL" ? $request->claim_type : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
            ]);
            return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function millenniumHealthAr(Request $request)
    {
        try {
            $attributes = [
                'trans_id' => isset($request->trans_id) && $request->trans_id != "NULL" ? $request->trans_id : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'perf_doctor_name' => isset($request->perf_doctor_name) && $request->perf_doctor_name != "NULL" ? $request->perf_doctor_name : NULL,
                'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                'office_name' => isset($request->office_name) && $request->office_name != "NULL" ? $request->office_name : NULL,
                'doctor_name' => isset($request->doctor_name) && $request->doctor_name != "NULL" ? $request->doctor_name : NULL,
                'ins_name' => isset($request->ins_name) && $request->ins_name != "NULL" ? $request->ins_name : NULL,
                'fc' => isset($request->fc) && $request->fc != "NULL" ? $request->fc : NULL
            ];

            $duplicateRecordExisting  =  MhawAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                MhawAr::insert([
                        'trans_id' => isset($request->trans_id) && $request->trans_id != "NULL" ? $request->trans_id : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'perf_doctor_name' => isset($request->perf_doctor_name) && $request->perf_doctor_name != "NULL" ? $request->perf_doctor_name : NULL,
                        'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                        'office_name' => isset($request->office_name) && $request->office_name != "NULL" ? $request->office_name : NULL,
                        'doctor_name' => isset($request->doctor_name) && $request->doctor_name != "NULL" ? $request->doctor_name : NULL,
                        'ins_name' => isset($request->ins_name) && $request->ins_name != "NULL" ? $request->ins_name : NULL,
                        'fc' => isset($request->fc) && $request->fc != "NULL" ? $request->fc : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  MhawAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                            'trans_id' => isset($request->trans_id) && $request->trans_id != "NULL" ? $request->trans_id : NULL,
                            'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                            'perf_doctor_name' => isset($request->perf_doctor_name) && $request->perf_doctor_name != "NULL" ? $request->perf_doctor_name : NULL,
                            'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                            'office_name' => isset($request->office_name) && $request->office_name != "NULL" ? $request->office_name : NULL,
                            'doctor_name' => isset($request->doctor_name) && $request->doctor_name != "NULL" ? $request->doctor_name : NULL,
                            'ins_name' => isset($request->ins_name) && $request->ins_name != "NULL" ? $request->ins_name : NULL,
                            'fc' => isset($request->fc) && $request->fc != "NULL" ? $request->fc : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                    ]);
                }
                return response()->json(['message' => 'Existing Record Updated Successfully']);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function millenniumHealthArDuplicates(Request $request)
    {
        try {
            MhawArDuplicates::insert([
                'trans_id' => isset($request->trans_id) && $request->trans_id != "NULL" ? $request->trans_id : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'perf_doctor_name' => isset($request->perf_doctor_name) && $request->perf_doctor_name != "NULL" ? $request->perf_doctor_name : NULL,
                'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                'office_name' => isset($request->office_name) && $request->office_name != "NULL" ? $request->office_name : NULL,
                'doctor_name' => isset($request->doctor_name) && $request->doctor_name != "NULL" ? $request->doctor_name : NULL,
                'ins_name' => isset($request->ins_name) && $request->ins_name != "NULL" ? $request->ins_name : NULL,
                'fc' => isset($request->fc) && $request->fc != "NULL" ? $request->fc : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
            ]);
            return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function lowerShoreClinicAr(Request $request)
    {
        try {
            $attributes = [
                'service_id' => isset($request->service_id) && $request->service_id != "NULL" ? $request->service_id : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'client_name' => isset($request->client_name) && $request->client_name != "NULL" ? $request->client_name : NULL,
                'service_type' => isset($request->service_type) && $request->service_type != "NULL" ? $request->service_type : NULL,
                'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                'program' => isset($request->program) && $request->program != "NULL" ? $request->program : NULL,
                'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'billing_rate' => isset($request->billing_rate) && $request->billing_rate != "NULL" ? $request->billing_rate : NULL,
                'curpayer_code' => isset($request->curpayer_code) && $request->curpayer_code != "NULL" ? $request->curpayer_code : NULL,
                'curid_insur' => isset($request->curid_insur) && $request->curid_insur != "NULL" ? $request->curid_insur : NULL,
                'auth_id' => isset($request->auth_id) && $request->auth_id != "NULL" ? $request->auth_id : NULL,
                'balance_due' => isset($request->balance_due) && $request->balance_due != "NULL" ? $request->balance_due : NULL,
                'client_due' => isset($request->client_due) && $request->client_due != "NULL" ? $request->client_due : NULL,
                'insur_due' => isset($request->insur_due) && $request->insur_due != "NULL" ? $request->insur_due : NULL,
                'batch_date' => isset($request->batch_date) && $request->batch_date != "NULL" ? $request->batch_date : NULL,
                'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                'comments' => isset($request->comments) && $request->comments != "NULL" ? $request->comments : NULL,
                'credible_notes' => isset($request->credible_notes) && $request->credible_notes != "NULL" ? $request->credible_notes : NULL,
                'balance_range' => isset($request->balance_range) && $request->balance_range != "NULL" ? $request->balance_range : NULL,
                'cpt_modifier' => isset($request->cpt_modifier) && $request->cpt_modifier != "NULL" ? $request->cpt_modifier : NULL
            ];

            $duplicateRecordExisting  =  LscAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                LscAr::insert([
                        'service_id' => isset($request->service_id) && $request->service_id != "NULL" ? $request->service_id : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'client_name' => isset($request->client_name) && $request->client_name != "NULL" ? $request->client_name : NULL,
                        'service_type' => isset($request->service_type) && $request->service_type != "NULL" ? $request->service_type : NULL,
                        'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                        'program' => isset($request->program) && $request->program != "NULL" ? $request->program : NULL,
                        'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'billing_rate' => isset($request->billing_rate) && $request->billing_rate != "NULL" ? $request->billing_rate : NULL,
                        'curpayer_code' => isset($request->curpayer_code) && $request->curpayer_code != "NULL" ? $request->curpayer_code : NULL,
                        'curid_insur' => isset($request->curid_insur) && $request->curid_insur != "NULL" ? $request->curid_insur : NULL,
                        'auth_id' => isset($request->auth_id) && $request->auth_id != "NULL" ? $request->auth_id : NULL,
                        'balance_due' => isset($request->balance_due) && $request->balance_due != "NULL" ? $request->balance_due : NULL,
                        'client_due' => isset($request->client_due) && $request->client_due != "NULL" ? $request->client_due : NULL,
                        'insur_due' => isset($request->insur_due) && $request->insur_due != "NULL" ? $request->insur_due : NULL,
                        'batch_date' => isset($request->batch_date) && $request->batch_date != "NULL" ? $request->batch_date : NULL,
                        'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                        'comments' => isset($request->comments) && $request->comments != "NULL" ? $request->comments : NULL,
                        'credible_notes' => isset($request->credible_notes) && $request->credible_notes != "NULL" ? $request->credible_notes : NULL,
                        'balance_range' => isset($request->balance_range) && $request->balance_range != "NULL" ? $request->balance_range : NULL,
                        'cpt_modifier' => isset($request->cpt_modifier) && $request->cpt_modifier != "NULL" ? $request->cpt_modifier : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  LscAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                            'service_id' => isset($request->service_id) && $request->service_id != "NULL" ? $request->service_id : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'client_name' => isset($request->client_name) && $request->client_name != "NULL" ? $request->client_name : NULL,
                            'service_type' => isset($request->service_type) && $request->service_type != "NULL" ? $request->service_type : NULL,
                            'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                            'program' => isset($request->program) && $request->program != "NULL" ? $request->program : NULL,
                            'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                            'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                            'billing_rate' => isset($request->billing_rate) && $request->billing_rate != "NULL" ? $request->billing_rate : NULL,
                            'curpayer_code' => isset($request->curpayer_code) && $request->curpayer_code != "NULL" ? $request->curpayer_code : NULL,
                            'curid_insur' => isset($request->curid_insur) && $request->curid_insur != "NULL" ? $request->curid_insur : NULL,
                            'auth_id' => isset($request->auth_id) && $request->auth_id != "NULL" ? $request->auth_id : NULL,
                            'balance_due' => isset($request->balance_due) && $request->balance_due != "NULL" ? $request->balance_due : NULL,
                            'client_due' => isset($request->client_due) && $request->client_due != "NULL" ? $request->client_due : NULL,
                            'insur_due' => isset($request->insur_due) && $request->insur_due != "NULL" ? $request->insur_due : NULL,
                            'batch_date' => isset($request->batch_date) && $request->batch_date != "NULL" ? $request->batch_date : NULL,
                            'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                            'comments' => isset($request->comments) && $request->comments != "NULL" ? $request->comments : NULL,
                            'credible_notes' => isset($request->credible_notes) && $request->credible_notes != "NULL" ? $request->credible_notes : NULL,
                            'balance_range' => isset($request->balance_range) && $request->balance_range != "NULL" ? $request->balance_range : NULL,
                            'cpt_modifier' => isset($request->cpt_modifier) && $request->cpt_modifier != "NULL" ? $request->cpt_modifier : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                    ]);
                }
                return response()->json(['message' => 'Existing Record Updated Successfully']);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function lowerShoreClinicArDuplicates(Request $request)
    {
        try {
            LscArDuplicates::insert([
                'service_id' => isset($request->service_id) && $request->service_id != "NULL" ? $request->service_id : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'client_name' => isset($request->client_name) && $request->client_name != "NULL" ? $request->client_name : NULL,
                'service_type' => isset($request->service_type) && $request->service_type != "NULL" ? $request->service_type : NULL,
                'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                'program' => isset($request->program) && $request->program != "NULL" ? $request->program : NULL,
                'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'billing_rate' => isset($request->billing_rate) && $request->billing_rate != "NULL" ? $request->billing_rate : NULL,
                'curpayer_code' => isset($request->curpayer_code) && $request->curpayer_code != "NULL" ? $request->curpayer_code : NULL,
                'curid_insur' => isset($request->curid_insur) && $request->curid_insur != "NULL" ? $request->curid_insur : NULL,
                'auth_id' => isset($request->auth_id) && $request->auth_id != "NULL" ? $request->auth_id : NULL,
                'balance_due' => isset($request->balance_due) && $request->balance_due != "NULL" ? $request->balance_due : NULL,
                'client_due' => isset($request->client_due) && $request->client_due != "NULL" ? $request->client_due : NULL,
                'insur_due' => isset($request->insur_due) && $request->insur_due != "NULL" ? $request->insur_due : NULL,
                'batch_date' => isset($request->batch_date) && $request->batch_date != "NULL" ? $request->batch_date : NULL,
                'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                'comments' => isset($request->comments) && $request->comments != "NULL" ? $request->comments : NULL,
                'credible_notes' => isset($request->credible_notes) && $request->credible_notes != "NULL" ? $request->credible_notes : NULL,
                'balance_range' => isset($request->balance_range) && $request->balance_range != "NULL" ? $request->balance_range : NULL,
                'cpt_modifier' => isset($request->cpt_modifier) && $request->cpt_modifier != "NULL" ? $request->cpt_modifier : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
            ]);
            return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }


    public function maryvilleAddictionTreatmentCenterAr(Request $request)
    {
        try {
            $attributes = [
                'customer_number' => isset($request->customer_number) && $request->customer_number != "NULL" ? $request->customer_number : NULL,
                'customer_name' => isset($request->customer_name) && $request->customer_name != "NULL" ? $request->customer_name : NULL,
                'document_type' => isset($request->document_type) && $request->document_type != "NULL" ? $request->document_type : NULL,
                'document_number' => isset($request->document_number) && $request->document_number != "NULL" ? $request->document_number : NULL,
                'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'coding' => isset($request->coding) && $request->coding != "NULL" ? $request->coding : NULL,
                'doc_date' => isset($request->doc_date) && $request->doc_date != "NULL" ? $request->doc_date : NULL,
                'due_date_or_check_else_recpt_no' => isset($request->due_date_or_check_else_recpt_no) && $request->due_date_or_check_else_recpt_no != "NULL" ? $request->due_date_or_check_else_recpt_no : NULL,
                'current' => isset($request->current) && $request->current != "NULL" ? $request->current : NULL,
                '0_to_30' => isset($request->zerotothirty) && $request->zerotothirty != "NULL" ? $request->zerotothirty : NULL,
                '30_to_60' => isset($request->thirtytosixty) && $request->thirtytosixty != "NULL" ? $request->thirtytosixty : NULL,
                '60_to_90' => isset($request->sixtytoninty) && $request->sixtytoninty != "NULL" ? $request->sixtytoninty : NULL,
                '90_above' => isset($request->nintyabove) && $request->sixtytonighty != "NULL" ? $request->sixtytonighty : NULL,
                'total' => isset($request->total) && $request->total != "NULL" ? $request->total : NULL,
                'previous_payment' => isset($request->previous_payment) && $request->previous_payment != "NULL" ? $request->previous_payment : NULL
            ];

            $duplicateRecordExisting  =  MatcAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                MatcAr::insert([
                        'customer_number' => isset($request->customer_number) && $request->customer_number != "NULL" ? $request->customer_number : NULL,
                        'customer_name' => isset($request->customer_name) && $request->customer_name != "NULL" ? $request->customer_name : NULL,
                        'document_type' => isset($request->document_type) && $request->document_type != "NULL" ? $request->document_type : NULL,
                        'document_number' => isset($request->document_number) && $request->document_number != "NULL" ? $request->document_number : NULL,
                        'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'coding' => isset($request->coding) && $request->coding != "NULL" ? $request->coding : NULL,
                        'doc_date' => isset($request->doc_date) && $request->doc_date != "NULL" ? $request->doc_date : NULL,
                        'due_date_or_check_else_recpt_no' => isset($request->due_date_or_check_else_recpt_no) && $request->due_date_or_check_else_recpt_no != "NULL" ? $request->due_date_or_check_else_recpt_no : NULL,
                        'current' => isset($request->current) && $request->current != "NULL" ? $request->current : NULL,
                        '0_to_30' => isset($request->zerotothirty) && $request->zerotothirty != "NULL" ? $request->zerotothirty : NULL,
                        '30_to_60' => isset($request->thirtytosixty) && $request->thirtytosixty != "NULL" ? $request->thirtytosixty : NULL,
                        '60_to_90' => isset($request->sixtytoninty) && $request->sixtytoninty != "NULL" ? $request->sixtytoninty : NULL,
                        '90_above' => isset($request->nintyabove) && $request->sixtytonighty != "NULL" ? $request->sixtytonighty : NULL,
                        'total' => isset($request->total) && $request->total != "NULL" ? $request->total : NULL,
                        'previous_payment' => isset($request->previous_payment) && $request->previous_payment != "NULL" ? $request->previous_payment : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  MatcAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                            'customer_number' => isset($request->customer_number) && $request->customer_number != "NULL" ? $request->customer_number : NULL,
                            'customer_name' => isset($request->customer_name) && $request->customer_name != "NULL" ? $request->customer_name : NULL,
                            'document_type' => isset($request->document_type) && $request->document_type != "NULL" ? $request->document_type : NULL,
                            'document_number' => isset($request->document_number) && $request->document_number != "NULL" ? $request->document_number : NULL,
                            'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'coding' => isset($request->coding) && $request->coding != "NULL" ? $request->coding : NULL,
                            'doc_date' => isset($request->doc_date) && $request->doc_date != "NULL" ? $request->doc_date : NULL,
                            'due_date_or_check_else_recpt_no' => isset($request->due_date_or_check_else_recpt_no) && $request->due_date_or_check_else_recpt_no != "NULL" ? $request->due_date_or_check_else_recpt_no : NULL,
                            'current' => isset($request->current) && $request->current != "NULL" ? $request->current : NULL,
                            '0_to_30' => isset($request->zerotothirty) && $request->zerotothirty != "NULL" ? $request->zerotothirty : NULL,
                            '30_to_60' => isset($request->thirtytosixty) && $request->thirtytosixty != "NULL" ? $request->thirtytosixty : NULL,
                            '60_to_90' => isset($request->sixtytoninty) && $request->sixtytoninty != "NULL" ? $request->sixtytoninty : NULL,
                            '90_above' => isset($request->nintyabove) && $request->sixtytonighty != "NULL" ? $request->sixtytonighty : NULL,
                            'total' => isset($request->total) && $request->total != "NULL" ? $request->total : NULL,
                            'previous_payment' => isset($request->previous_payment) && $request->previous_payment != "NULL" ? $request->previous_payment : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                    ]);
                }
                return response()->json(['message' => 'Existing Record Updated Successfully']);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function maryvilleAddictionTreatmentCenterArDuplicates(Request $request)
    {
        try {
            MatcArDuplicates::insert([
                'customer_number' => isset($request->customer_number) && $request->customer_number != "NULL" ? $request->customer_number : NULL,
                'customer_name' => isset($request->customer_name) && $request->customer_name != "NULL" ? $request->customer_name : NULL,
                'document_type' => isset($request->document_type) && $request->document_type != "NULL" ? $request->document_type : NULL,
                'document_number' => isset($request->document_number) && $request->document_number != "NULL" ? $request->document_number : NULL,
                'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'coding' => isset($request->coding) && $request->coding != "NULL" ? $request->coding : NULL,
                'doc_date' => isset($request->doc_date) && $request->doc_date != "NULL" ? $request->doc_date : NULL,
                'due_date_or_check_else_recpt_no' => isset($request->due_date_or_check_else_recpt_no) && $request->due_date_or_check_else_recpt_no != "NULL" ? $request->due_date_or_check_else_recpt_no : NULL,
                'current' => isset($request->current) && $request->current != "NULL" ? $request->current : NULL,
                '0_to_30' => isset($request->zerotothirty) && $request->zerotothirty != "NULL" ? $request->zerotothirty : NULL,
                '30_to_60' => isset($request->thirtytosixty) && $request->thirtytosixty != "NULL" ? $request->thirtytosixty : NULL,
                '60_to_90' => isset($request->sixtytoninty) && $request->sixtytoninty != "NULL" ? $request->sixtytoninty : NULL,
                '90_above' => isset($request->nintyabove) && $request->sixtytonighty != "NULL" ? $request->sixtytonighty : NULL,
                'total' => isset($request->total) && $request->total != "NULL" ? $request->total : NULL,
                'previous_payment' => isset($request->previous_payment) && $request->previous_payment != "NULL" ? $request->previous_payment : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
            ]);
            return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    public function greenClinicHealthSystemAr(Request $request)
    {
        try {
            $attributes = [
                'member_id' => isset($request->member_id) && $request->member_id != "NULL" ? $request->member_id : NULL,
                'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,
                'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'procedures' => isset($request->procedures) && $request->procedures != "NULL" ? $request->procedures : NULL,
                'charge_amount' => isset($request->charge_amount) && $request->charge_amount != "NULL" ? $request->charge_amount : NULL,
                'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,
                'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,
                'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL,
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                'diagnosis' => isset($request->diagnosis) && $request->diagnosis != "NULL" ? $request->diagnosis : NULL
            ];

            $duplicateRecordExisting  =  GchsAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                GchsAr::insert([
                        'member_id' => isset($request->member_id) && $request->member_id != "NULL" ? $request->member_id : NULL,
                        'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,
                        'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'procedures' => isset($request->procedures) && $request->procedures != "NULL" ? $request->procedures : NULL,
                        'charge_amount' => isset($request->charge_amount) && $request->charge_amount != "NULL" ? $request->charge_amount : NULL,
                        'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,
                        'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,
                        'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL,
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                        'diagnosis' => isset($request->diagnosis) && $request->diagnosis != "NULL" ? $request->diagnosis : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  GchsAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                            'member_id' => isset($request->member_id) && $request->member_id != "NULL" ? $request->member_id : NULL,
                            'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,
                            'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                            'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'procedures' => isset($request->procedures) && $request->procedures != "NULL" ? $request->procedures : NULL,
                            'charge_amount' => isset($request->charge_amount) && $request->charge_amount != "NULL" ? $request->charge_amount : NULL,
                            'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,
                            'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,
                            'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL,
                            'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                            'diagnosis' => isset($request->diagnosis) && $request->diagnosis != "NULL" ? $request->diagnosis : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                    ]);
                }
                return response()->json(['message' => 'Existing Record Updated Successfully']);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function greenClinicHealthSystemArDuplicates(Request $request)
    {
        try {
            GchsArDuplicates::insert([
                'member_id' => isset($request->member_id) && $request->member_id != "NULL" ? $request->member_id : NULL,
                'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,
                'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'procedures' => isset($request->procedures) && $request->procedures != "NULL" ? $request->procedures : NULL,
                'charge_amount' => isset($request->charge_amount) && $request->charge_amount != "NULL" ? $request->charge_amount : NULL,
                'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,
                'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,
                'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL,
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                'diagnosis' => isset($request->diagnosis) && $request->diagnosis != "NULL" ? $request->diagnosis : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
            ]);
            return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    public function arthritisSportsOrthopeadicsPCAr(Request $request)
    {
        try {
            $attributes = [
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,
                'ins1_amt' => isset($request->ins1_amt) && $request->ins1_amt != "NULL" ? $request->ins1_amt : NULL,
               ];

            $duplicateRecordExisting  =  AsopAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                AsopAr::insert([
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'enc_no' => isset($request->enc_no) && $request->enc_no != "NULL" ? $request->enc_no : NULL,
                        'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,
                        'phone_number' => isset($request->phone_number) && $request->phone_number != "NULL" ? $request->phone_number : NULL,
                        'member_id' => isset($request->member_id) && $request->member_id != "NULL" ? $request->member_id : NULL,
                        'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                        'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                        'ins1_amt' => isset($request->ins1_amt) && $request->ins1_amt != "NULL" ? $request->ins1_amt : NULL,
                        'line_amt' => isset($request->line_amt) && $request->line_amt != "NULL" ? $request->line_amt : NULL,
                        'rendering_provider' => isset($request->rendering_provider) && $request->rendering_provider != "NULL" ? $request->rendering_provider : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  AsopAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                            'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'enc_no' => isset($request->enc_no) && $request->enc_no != "NULL" ? $request->enc_no : NULL,
                            'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,
                            'phone_number' => isset($request->phone_number) && $request->phone_number != "NULL" ? $request->phone_number : NULL,
                            'member_id' => isset($request->member_id) && $request->member_id != "NULL" ? $request->member_id : NULL,
                            'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                            'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                            'ins1_amt' => isset($request->ins1_amt) && $request->ins1_amt != "NULL" ? $request->ins1_amt : NULL,
                            'line_amt' => isset($request->line_amt) && $request->line_amt != "NULL" ? $request->line_amt : NULL,
                            'rendering_provider' => isset($request->rendering_provider) && $request->rendering_provider != "NULL" ? $request->rendering_provider : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                    ]);
                }
                return response()->json(['message' => 'Existing Record Updated Successfully']);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function arthritisSportsOrthopeadicsPCArDuplicates(Request $request)
    {
        try {
            AsopArDuplicates::insert([
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'enc_no' => isset($request->enc_no) && $request->enc_no != "NULL" ? $request->enc_no : NULL,
                'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,
                'phone_number' => isset($request->phone_number) && $request->phone_number != "NULL" ? $request->phone_number : NULL,
                'member_id' => isset($request->member_id) && $request->member_id != "NULL" ? $request->member_id : NULL,
                'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                'ins1_amt' => isset($request->ins1_amt) && $request->ins1_amt != "NULL" ? $request->ins1_amt : NULL,
                'line_amt' => isset($request->line_amt) && $request->line_amt != "NULL" ? $request->line_amt : NULL,
                'rendering_provider' => isset($request->rendering_provider) && $request->rendering_provider != "NULL" ? $request->rendering_provider : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
            ]);
            return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    public function rapidCityMedicalCenterAr(Request $request)
    {
        try {
            $attributes = [
                'claim' => isset($request->claim) && $request->claim != "NULL" ? $request->claim : NULL,
                'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'charge_amount' => isset($request->charge_amount) && $request->charge_amount != "NULL" ? $request->charge_amount : NULL,
                'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,
                'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'last_submission_date' => isset($request->last_submission_date) && $request->last_submission_date != "NULL" ? $request->last_submission_date : NULL,
            ];

            $duplicateRecordExisting  =  RcmAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                RcmAr::insert([
                        'claim' => isset($request->claim) && $request->claim != "NULL" ? $request->claim : NULL,
                        'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'charge_amount' => isset($request->charge_amount) && $request->charge_amount != "NULL" ? $request->charge_amount : NULL,
                        'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,
                        'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,
                        'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'last_submission_date' => isset($request->last_submission_date) && $request->last_submission_date != "NULL" ? $request->last_submission_date : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  RcmAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'claim' => isset($request->claim) && $request->claim != "NULL" ? $request->claim : NULL,
                        'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'charge_amount' => isset($request->charge_amount) && $request->charge_amount != "NULL" ? $request->charge_amount : NULL,
                        'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,
                        'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,
                        'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'last_submission_date' => isset($request->last_submission_date) && $request->last_submission_date != "NULL" ? $request->last_submission_date : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                    ]);
                }
                return response()->json(['message' => 'Existing Record Updated Successfully']);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function rapidCityMedicalCenterArDuplicates(Request $request)
    {
        try {
            RcmArDuplicates::insert([
                'claim' => isset($request->claim) && $request->claim != "NULL" ? $request->claim : NULL,
                'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'charge_amount' => isset($request->charge_amount) && $request->charge_amount != "NULL" ? $request->charge_amount : NULL,
                'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,
                'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'last_submission_date' => isset($request->last_submission_date) && $request->last_submission_date != "NULL" ? $request->last_submission_date : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
            ]);
            return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }


     //Rhea Medical Center

     public function rheaMedicalCentre(Request $request)
     {
         try {
             $attributes = [
                 'claims' => isset($request->claims) && $request->claims != "NULL" ? $request->claims : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'pvdr' => isset($request->pvdr) && $request->pvdr != "NULL" ? $request->pvdr : NULL,
                 'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                 'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                 'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,                
                 'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                 'pmts_adjs' => isset($request->pmts_adjs) && $request->pmts_adjs != "NULL" ? $request->pmts_adjs : NULL,
                 'adjustment' => isset($request->adjustment) && $request->adjustment != "NULL" ? $request->adjustment : NULL,
                 'withheld' => isset($request->withheld) && $request->withheld != "NULL" ? $request->withheld : NULL,                
                 'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                 'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                 'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                 'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                 'guarantor_name' => isset($request->guarantor_name) && $request->guarantor_name != "NULL" ? $request->guarantor_name : NULL,
             ];
 
             $duplicateRecordExisting  =  RmcAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                RmcAr::insert([
                    'claims' => isset($request->claims) && $request->claims != "NULL" ? $request->claims : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'pvdr' => isset($request->pvdr) && $request->pvdr != "NULL" ? $request->pvdr : NULL,
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                    'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,                
                    'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                    'pmts_adjs' => isset($request->pmts_adjs) && $request->pmts_adjs != "NULL" ? $request->pmts_adjs : NULL,
                    'adjustment' => isset($request->adjustment) && $request->adjustment != "NULL" ? $request->adjustment : NULL,
                    'withheld' => isset($request->withheld) && $request->withheld != "NULL" ? $request->withheld : NULL,                
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                    'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                    'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                    'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                    'guarantor_name' => isset($request->guarantor_name) && $request->guarantor_name != "NULL" ? $request->guarantor_name : NULL,
                         'invoke_date' => date('Y-m-d'),
                         'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                         'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                         'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  RmcAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'claims' => isset($request->claims) && $request->claims != "NULL" ? $request->claims : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'pvdr' => isset($request->pvdr) && $request->pvdr != "NULL" ? $request->pvdr : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,                
                        'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                        'pmts_adjs' => isset($request->pmts_adjs) && $request->pmts_adjs != "NULL" ? $request->pmts_adjs : NULL,
                        'adjustment' => isset($request->adjustment) && $request->adjustment != "NULL" ? $request->adjustment : NULL,
                        'withheld' => isset($request->withheld) && $request->withheld != "NULL" ? $request->withheld : NULL,                
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                        'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                        'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                        'guarantor_name' => isset($request->guarantor_name) && $request->guarantor_name != "NULL" ? $request->guarantor_name : NULL,
                             'invoke_date' => date('Y-m-d'),
                             'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                             'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                             'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }


     public function rheaMedicalCentreARDuplicates(Request $request)
     {
         try {
            RmcAr::insert([
                'claims' => isset($request->claims) && $request->claims != "NULL" ? $request->claims : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'pvdr' => isset($request->pvdr) && $request->pvdr != "NULL" ? $request->pvdr : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,                
                'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                'pmts_adjs' => isset($request->pmts_adjs) && $request->pmts_adjs != "NULL" ? $request->pmts_adjs : NULL,
                'adjustment' => isset($request->adjustment) && $request->adjustment != "NULL" ? $request->adjustment : NULL,
                'withheld' => isset($request->withheld) && $request->withheld != "NULL" ? $request->withheld : NULL,                
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                'guarantor_name' => isset($request->guarantor_name) && $request->guarantor_name != "NULL" ? $request->guarantor_name : NULL,
                 'invoke_date' => date('Y-m-d'),
                 'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                 'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                 'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }


     // Associates of Plastic Surgery

     public function AssociatesofPlasticSurgeryAR(Request $request)
     {
         try {
             $attributes = [
                 'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                 'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'total_ba' => isset($request->total_ba) && $request->total_ba != "NULL" ? $request->total_ba : NULL,
                
                 
             ];
 
             $duplicateRecordExisting  =  AopsAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                AopsAr::insert([
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'total_ba' => isset($request->total_ba) && $request->total_ba != "NULL" ? $request->total_ba : NULL,
                    'change_in_ar' => isset($request->change_in_ar) && $request->change_in_ar != "NULL" ? $request->change_in_ar : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  AopsAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'total_ba' => isset($request->total_ba) && $request->total_ba != "NULL" ? $request->total_ba : NULL,
                    'change_in_ar' => isset($request->change_in_ar) && $request->change_in_ar != "NULL" ? $request->change_in_ar : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     } 
     
     public function AssociatesofPlasticSurgeryARDuplicates(Request $request)
     {
         try {
            AopsAr::insert([
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'total_ba' => isset($request->total_ba) && $request->total_ba != "NULL" ? $request->total_ba : NULL,
                'change_in_ar' => isset($request->change_in_ar) && $request->change_in_ar != "NULL" ? $request->change_in_ar : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }


 // Neurology Associates


 public function NeurologyAssociatesAR(Request $request)
 {
     try {
         $attributes = [
             'claims' => isset($request->claims) && $request->claims != "NULL" ? $request->claims : NULL,
             'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
             'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
             'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
             'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
             'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
            
             
         ];

         $duplicateRecordExisting  =  NaAr::where($attributes)->exists();
         if (!$duplicateRecordExisting) {
            NaAr::insert([
                'claims' => isset($request->claims) && $request->claims != "NULL" ? $request->claims : NULL,
                'atb' => isset($request->atb) && $request->atb != "NULL" ? $request->atb : NULL,                
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'pvdr' => isset($request->pvdr) && $request->pvdr != "NULL" ? $request->pvdr : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                'pmts_adjs' => isset($request->pmts_adjs) && $request->pmts_adjs != "NULL" ? $request->pmts_adjs : NULL,
                'adjustment' => isset($request->adjustment) && $request->adjustment != "NULL" ? $request->adjustment : NULL,
                'withheld' => isset($request->withheld) && $request->withheld != "NULL" ? $request->withheld : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                'guarantor_name' => isset($request->guarantor_name) && $request->guarantor_name != "NULL" ? $request->guarantor_name : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
                 ]);
                     return response()->json(['message' => 'Record Inserted Successfully']);
         } else {
             $duplicateRecord  =  NaAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
             if ($duplicateRecord) {
                 $duplicateRecord->update([
                'claims' => isset($request->claims) && $request->claims != "NULL" ? $request->claims : NULL,
                'atb' => isset($request->atb) && $request->atb != "NULL" ? $request->atb : NULL,                
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'pvdr' => isset($request->pvdr) && $request->pvdr != "NULL" ? $request->pvdr : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                'pmts_adjs' => isset($request->pmts_adjs) && $request->pmts_adjs != "NULL" ? $request->pmts_adjs : NULL,
                'adjustment' => isset($request->adjustment) && $request->adjustment != "NULL" ? $request->adjustment : NULL,
                'withheld' => isset($request->withheld) && $request->withheld != "NULL" ? $request->withheld : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                'guarantor_name' => isset($request->guarantor_name) && $request->guarantor_name != "NULL" ? $request->guarantor_name : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
                'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                 ]);
             }
             return response()->json(['message' => 'Existing Record Updated Successfully']);
         }
     } catch (\Exception $e) {
         $e->getMessage();
     }
 } 


 public function NeurologyAssociatesARDuplicates(Request $request)
     {
         try {
            NaArDuplicates::insert([
                'claims' => isset($request->claims) && $request->claims != "NULL" ? $request->claims : NULL,
                'atb' => isset($request->atb) && $request->atb != "NULL" ? $request->atb : NULL,                
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'pvdr' => isset($request->pvdr) && $request->pvdr != "NULL" ? $request->pvdr : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                'pmts_adjs' => isset($request->pmts_adjs) && $request->pmts_adjs != "NULL" ? $request->pmts_adjs : NULL,
                'adjustment' => isset($request->adjustment) && $request->adjustment != "NULL" ? $request->adjustment : NULL,
                'withheld' => isset($request->withheld) && $request->withheld != "NULL" ? $request->withheld : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                'guarantor_name' => isset($request->guarantor_name) && $request->guarantor_name != "NULL" ? $request->guarantor_name : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

/*************  ✨ Codeium Command ⭐  *************/
        /**
         * Store a newly created resource in storage.
/******  55baa038-4444-4675-86d5-c8357e15ea32  *******/
     public function leakUrologyAR(Request $request)
     {
         try {
             $attributes = [
                 'claim_no' => isset($request->claim_no) && $request->claim_no != "NULL" ? $request->claim_no : NULL,
                 'unique_id_no' => isset($request->unique_id_no) && $request->unique_id_no != "NULL" ? $request->unique_id_no : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                 'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                 'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                 'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                 'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                 'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                 'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                 'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL               
             ];
 
             $duplicateRecordExisting  =  LuAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                   LuAr::insert([
                        'claim_no' => isset($request->claim_no) && $request->claim_no != "NULL" ? $request->claim_no : NULL,
                        'unique_id_no' => isset($request->unique_id_no) && $request->unique_id_no != "NULL" ? $request->unique_id_no : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                        'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                        'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  LuAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'claim_no' => isset($request->claim_no) && $request->claim_no != "NULL" ? $request->claim_no : NULL,
                        'unique_id_no' => isset($request->unique_id_no) && $request->unique_id_no != "NULL" ? $request->unique_id_no : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                        'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                        'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function leakUrologyARDuplicates(Request $request)
     {
         try {
            LuArDuplicates::insert([
                'claim_no' => isset($request->claim_no) && $request->claim_no != "NULL" ? $request->claim_no : NULL,
                'unique_id_no' => isset($request->unique_id_no) && $request->unique_id_no != "NULL" ? $request->unique_id_no : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function BostonMountainRuralHealthCenterAR(Request $request)
     {
         try {
             $attributes = [
                 'claim_status_category' => isset($request->claim_status_category) && $request->claim_status_category != "NULL" ? $request->claim_status_category : NULL,
                 'claim_status' => isset($request->claim_status) && $request->claim_status != "NULL" ? $request->claim_status : NULL,
                 'claim_no' => isset($request->claim_no) && $request->claim_no != "NULL" ? $request->claim_no : NULL,
                 'claim_date' => isset($request->claim_date) && $request->claim_date != "NULL" ? $request->claim_date : NULL,
                 'atb' => isset($request->atb) && $request->atb != "NULL" ? $request->atb : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'latest_transfer_date' => isset($request->latest_transfer_date) && $request->latest_transfer_date != "NULL" ? $request->latest_transfer_date : NULL,
                 'claim_status_change_date' => isset($request->claim_status_change_date) && $request->claim_status_change_date != "NULL" ? $request->claim_status_change_date : NULL,
                 'modified_date' => isset($request->modified_date) && $request->modified_date != "NULL" ? $request->modified_date : NULL,
                //  'notes' => isset($request->notes) && $request->notes != "NULL" ? $request->notes : NULL,
                 'staff_member' => isset($request->staff_member) && $request->staff_member != "NULL" ? $request->staff_member : NULL,    
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,              
                 'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,       
                 'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,      
                 'payer_group_name' => isset($request->payer_group_name) && $request->payer_group_name != "NULL" ? $request->payer_group_name : NULL,       
                 'payer_class' => isset($request->payer_class) && $request->payer_class != "NULL" ? $request->payer_class : NULL,       
                 'facility_name' => isset($request->facility_name) && $request->facility_name != "NULL" ? $request->facility_name : NULL,      
                 'facility_group_name' => isset($request->facility_group_name) && $request->facility_group_name != "NULL" ? $request->facility_group_name : NULL,       
                 'facility_place_of_service' => isset($request->facility_place_of_service) && $request->facility_place_of_service != "NULL" ? $request->facility_place_of_service : NULL ,      
                 'department_name' => isset($request->department_name) && $request->department_name != "NULL" ? $request->department_name : NULL ,     
                 'rendering_provider_name' => isset($request->rendering_provider_name) && $request->rendering_provider_name != "NULL" ? $request->rendering_provider_name : NULL,       
                 'appointment_provider_name' => isset($request->appointment_provider_name) && $request->appointment_provider_name != "NULL" ? $request->appointment_provider_name : NULL,       
                 'additional_provider_1_name' => isset($request->additional_provider_1_name) && $request->additional_provider_1_name != "NULL" ? $request->additional_provider_1_name : NULL,       
                 'additional_provider_2_name' => isset($request->additional_provider_2_name) && $request->additional_provider_2_name != "NULL" ? $request->additional_provider_2_name : NULL,       
                 'pay_to_else_billing_provider' => isset($request->pay_to_else_billing_provider) && $request->pay_to_else_billing_provider != "NULL" ? $request->pay_to_else_billing_provider : NULL,       
                 'resource_provider_name' => isset($request->resource_provider_name) && $request->resource_provider_name != "NULL" ? $request->resource_provider_name : NULL,       
                 'supervising_provider_name' => isset($request->supervising_provider_name) && $request->supervising_provider_name != "NULL" ? $request->supervising_provider_name : NULL,       
                 'claim_amount' => isset($request->claim_amount) && $request->claim_amount != "NULL" ? $request->claim_amount : NULL,
                 'collected' => isset($request->collected) && $request->collected != "NULL" ? $request->collected : NULL,
                 'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL
             ];
 
             $duplicateRecordExisting  =  BmrhcAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                   BmrhcAr::insert([
                        'claim_status_category' => isset($request->claim_status_category) && $request->claim_status_category != "NULL" ? $request->claim_status_category : NULL,
                        'claim_status' => isset($request->claim_status) && $request->claim_status != "NULL" ? $request->claim_status : NULL,
                        'claim_no' => isset($request->claim_no) && $request->claim_no != "NULL" ? $request->claim_no : NULL,
                        'claim_date' => isset($request->claim_date) && $request->claim_date != "NULL" ? $request->claim_date : NULL,
                        'atb' => isset($request->atb) && $request->atb != "NULL" ? $request->atb : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'latest_transfer_date' => isset($request->latest_transfer_date) && $request->latest_transfer_date != "NULL" ? $request->latest_transfer_date : NULL,
                        'claim_status_change_date' => isset($request->claim_status_change_date) && $request->claim_status_change_date != "NULL" ? $request->claim_status_change_date : NULL,
                        'modified_date' => isset($request->modified_date) && $request->modified_date != "NULL" ? $request->modified_date : NULL,
                        // 'notes' => isset($request->notes) && $request->notes != "NULL" ? $request->notes : NULL,
                        'staff_member' => isset($request->staff_member) && $request->staff_member != "NULL" ? $request->staff_member : NULL,    
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,              
                        'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,       
                        'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,      
                        'payer_group_name' => isset($request->payer_group_name) && $request->payer_group_name != "NULL" ? $request->payer_group_name : NULL,       
                        'payer_class' => isset($request->payer_class) && $request->payer_class != "NULL" ? $request->payer_class : NULL,       
                        'facility_name' => isset($request->facility_name) && $request->facility_name != "NULL" ? $request->facility_name : NULL,      
                        'facility_group_name' => isset($request->facility_group_name) && $request->facility_group_name != "NULL" ? $request->facility_group_name : NULL,       
                        'facility_place_of_service' => isset($request->facility_place_of_service) && $request->facility_place_of_service != "NULL" ? $request->facility_place_of_service : NULL ,      
                        'department_name' => isset($request->department_name) && $request->department_name != "NULL" ? $request->department_name : NULL ,     
                        'rendering_provider_name' => isset($request->rendering_provider_name) && $request->rendering_provider_name != "NULL" ? $request->rendering_provider_name : NULL,       
                        'appointment_provider_name' => isset($request->appointment_provider_name) && $request->appointment_provider_name != "NULL" ? $request->appointment_provider_name : NULL,       
                        'additional_provider_1_name' => isset($request->additional_provider_1_name) && $request->additional_provider_1_name != "NULL" ? $request->additional_provider_1_name : NULL,       
                        'additional_provider_2_name' => isset($request->additional_provider_2_name) && $request->additional_provider_2_name != "NULL" ? $request->additional_provider_2_name : NULL,       
                        'pay_to_else_billing_provider' => isset($request->pay_to_else_billing_provider) && $request->pay_to_else_billing_provider != "NULL" ? $request->pay_to_else_billing_provider : NULL,       
                        'resource_provider_name' => isset($request->resource_provider_name) && $request->resource_provider_name != "NULL" ? $request->resource_provider_name : NULL,       
                        'supervising_provider_name' => isset($request->supervising_provider_name) && $request->supervising_provider_name != "NULL" ? $request->supervising_provider_name : NULL,       
                        'claim_amount' => isset($request->claim_amount) && $request->claim_amount != "NULL" ? $request->claim_amount : NULL,
                        'collected' => isset($request->collected) && $request->collected != "NULL" ? $request->collected : NULL,
                        'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  BmrhcAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'claim_status_category' => isset($request->claim_status_category) && $request->claim_status_category != "NULL" ? $request->claim_status_category : NULL,
                        'claim_status' => isset($request->claim_status) && $request->claim_status != "NULL" ? $request->claim_status : NULL,
                        'claim_no' => isset($request->claim_no) && $request->claim_no != "NULL" ? $request->claim_no : NULL,
                        'claim_date' => isset($request->claim_date) && $request->claim_date != "NULL" ? $request->claim_date : NULL,
                        'atb' => isset($request->atb) && $request->atb != "NULL" ? $request->atb : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'latest_transfer_date' => isset($request->latest_transfer_date) && $request->latest_transfer_date != "NULL" ? $request->latest_transfer_date : NULL,
                        'claim_status_change_date' => isset($request->claim_status_change_date) && $request->claim_status_change_date != "NULL" ? $request->claim_status_change_date : NULL,
                        'modified_date' => isset($request->modified_date) && $request->modified_date != "NULL" ? $request->modified_date : NULL,
                        // 'notes' => isset($request->notes) && $request->notes != "NULL" ? $request->notes : NULL,
                        'staff_member' => isset($request->staff_member) && $request->staff_member != "NULL" ? $request->staff_member : NULL,    
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,              
                        'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,       
                        'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,      
                        'payer_group_name' => isset($request->payer_group_name) && $request->payer_group_name != "NULL" ? $request->payer_group_name : NULL,       
                        'payer_class' => isset($request->payer_class) && $request->payer_class != "NULL" ? $request->payer_class : NULL,       
                        'facility_name' => isset($request->facility_name) && $request->facility_name != "NULL" ? $request->facility_name : NULL,      
                        'facility_group_name' => isset($request->facility_group_name) && $request->facility_group_name != "NULL" ? $request->facility_group_name : NULL,       
                        'facility_place_of_service' => isset($request->facility_place_of_service) && $request->facility_place_of_service != "NULL" ? $request->facility_place_of_service : NULL ,      
                        'department_name' => isset($request->department_name) && $request->department_name != "NULL" ? $request->department_name : NULL ,     
                        'rendering_provider_name' => isset($request->rendering_provider_name) && $request->rendering_provider_name != "NULL" ? $request->rendering_provider_name : NULL,       
                        'appointment_provider_name' => isset($request->appointment_provider_name) && $request->appointment_provider_name != "NULL" ? $request->appointment_provider_name : NULL,       
                        'additional_provider_1_name' => isset($request->additional_provider_1_name) && $request->additional_provider_1_name != "NULL" ? $request->additional_provider_1_name : NULL,       
                        'additional_provider_2_name' => isset($request->additional_provider_2_name) && $request->additional_provider_2_name != "NULL" ? $request->additional_provider_2_name : NULL,       
                        'pay_to_else_billing_provider' => isset($request->pay_to_else_billing_provider) && $request->pay_to_else_billing_provider != "NULL" ? $request->pay_to_else_billing_provider : NULL,       
                        'resource_provider_name' => isset($request->resource_provider_name) && $request->resource_provider_name != "NULL" ? $request->resource_provider_name : NULL,       
                        'supervising_provider_name' => isset($request->supervising_provider_name) && $request->supervising_provider_name != "NULL" ? $request->supervising_provider_name : NULL,       
                        'claim_amount' => isset($request->claim_amount) && $request->claim_amount != "NULL" ? $request->claim_amount : NULL,
                        'collected' => isset($request->collected) && $request->collected != "NULL" ? $request->collected : NULL,
                        'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function BostonMountainRuralHealthCenterARDuplicates(Request $request)
     {
         try {
              BmrhcArDuplicates::insert([
                 'claim_status_category' => isset($request->claim_status_category) && $request->claim_status_category != "NULL" ? $request->claim_status_category : NULL,
                 'claim_status' => isset($request->claim_status) && $request->claim_status != "NULL" ? $request->claim_status : NULL,
                 'claim_no' => isset($request->claim_no) && $request->claim_no != "NULL" ? $request->claim_no : NULL,
                 'claim_date' => isset($request->claim_date) && $request->claim_date != "NULL" ? $request->claim_date : NULL,
                 'atb' => isset($request->atb) && $request->atb != "NULL" ? $request->atb : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'latest_transfer_date' => isset($request->latest_transfer_date) && $request->latest_transfer_date != "NULL" ? $request->latest_transfer_date : NULL,
                 'claim_status_change_date' => isset($request->claim_status_change_date) && $request->claim_status_change_date != "NULL" ? $request->claim_status_change_date : NULL,
                 'modified_date' => isset($request->modified_date) && $request->modified_date != "NULL" ? $request->modified_date : NULL,
                 'notes' => isset($request->notes) && $request->notes != "NULL" ? $request->notes : NULL,
                 'staff_member' => isset($request->staff_member) && $request->staff_member != "NULL" ? $request->staff_member : NULL,    
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,              
                 'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,       
                 'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,      
                 'payer_group_name' => isset($request->payer_group_name) && $request->payer_group_name != "NULL" ? $request->payer_group_name : NULL,       
                 'payer_class' => isset($request->payer_class) && $request->payer_class != "NULL" ? $request->payer_class : NULL,       
                 'facility_name' => isset($request->facility_name) && $request->facility_name != "NULL" ? $request->facility_name : NULL,      
                 'facility_group_name' => isset($request->facility_group_name) && $request->facility_group_name != "NULL" ? $request->facility_group_name : NULL,       
                 'facility_place_of_service' => isset($request->facility_place_of_service) && $request->facility_place_of_service != "NULL" ? $request->facility_place_of_service : NULL ,      
                 'department_name' => isset($request->department_name) && $request->department_name != "NULL" ? $request->department_name : NULL ,     
                 'rendering_provider_name' => isset($request->rendering_provider_name) && $request->rendering_provider_name != "NULL" ? $request->rendering_provider_name : NULL,       
                 'appointment_provider_name' => isset($request->appointment_provider_name) && $request->appointment_provider_name != "NULL" ? $request->appointment_provider_name : NULL,       
                 'additional_provider_1_name' => isset($request->additional_provider_1_name) && $request->additional_provider_1_name != "NULL" ? $request->additional_provider_1_name : NULL,       
                 'additional_provider_2_name' => isset($request->additional_provider_2_name) && $request->additional_provider_2_name != "NULL" ? $request->additional_provider_2_name : NULL,       
                 'pay_to_else_billing_provider' => isset($request->pay_to_else_billing_provider) && $request->pay_to_else_billing_provider != "NULL" ? $request->pay_to_else_billing_provider : NULL,       
                 'resource_provider_name' => isset($request->resource_provider_name) && $request->resource_provider_name != "NULL" ? $request->resource_provider_name : NULL,       
                 'supervising_provider_name' => isset($request->supervising_provider_name) && $request->supervising_provider_name != "NULL" ? $request->supervising_provider_name : NULL,       
                 'claim_amount' => isset($request->claim_amount) && $request->claim_amount != "NULL" ? $request->claim_amount : NULL,
                 'collected' => isset($request->collected) && $request->collected != "NULL" ? $request->collected : NULL,
                 'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function ColonAndRectalSurgeryAR(Request $request)
     {
         try {
             $attributes = [
                 'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'rendering_provider' => isset($request->rendering_provider) && $request->rendering_provider != "NULL" ? $request->rendering_provider : NULL,
                 'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                 'ins_mem_id' => isset($request->ins_mem_id) && $request->ins_mem_id != "NULL" ? $request->ins_mem_id : NULL,
                 'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                 'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL   
             ];
 
             $duplicateRecordExisting  =  CarsAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                CarsAr::insert([
                    'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'rendering_provider' => isset($request->rendering_provider) && $request->rendering_provider != "NULL" ? $request->rendering_provider : NULL,
                    'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                    'ins_mem_id' => isset($request->ins_mem_id) && $request->ins_mem_id != "NULL" ? $request->ins_mem_id : NULL,
                    'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  CarsAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'rendering_provider' => isset($request->rendering_provider) && $request->rendering_provider != "NULL" ? $request->rendering_provider : NULL,
                        'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                        'ins_mem_id' => isset($request->ins_mem_id) && $request->ins_mem_id != "NULL" ? $request->ins_mem_id : NULL,
                        'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,   
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function ColonAndRectalSurgeryARDuplicates(Request $request)
     {
         try {
            CarsArDuplicates::insert([
                'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'rendering_provider' => isset($request->rendering_provider) && $request->rendering_provider != "NULL" ? $request->rendering_provider : NULL,
                'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                'ins_mem_id' => isset($request->ins_mem_id) && $request->ins_mem_id != "NULL" ? $request->ins_mem_id : NULL,
                'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function NcgMedicalNcgGottengerAR(Request $request)
     {
         try {
             $attributes = [
                 'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                 'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                 'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                 'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                 'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                 'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                 'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                 'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                 'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                 'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                 'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                 'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                 'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                 'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                 'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
             ];
 
             $duplicateRecordExisting  =  NmNcgGottengerAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                NmNcgGottengerAr::insert([
                    'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                    'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                    'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                    'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                    'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                    'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                    'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                    'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                    'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                    'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                    'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                    'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                    'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                    'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                    'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  NmNcgGottengerAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                        'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                        'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                        'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                        'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                        'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                        'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                        'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                        'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                        'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                        'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                        'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                        'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                        'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                        'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function NcgMedicalNcgGottengerARDuplicates(Request $request)
     {
         try {
            NmNcgGottengerArDuplicates::insert([
                'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function NcgMedicalNcgHudsonAR(Request $request)
     {
         try {
             $attributes = [
                 'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                 'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                 'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                 'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                 'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                 'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                 'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                 'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                 'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                 'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                 'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                 'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                 'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                 'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                 'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
             ];
 
             $duplicateRecordExisting  =  NmNcgHudsonAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                NmNcgHudsonAr::insert([
                    'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                    'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                    'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                    'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                    'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                    'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                    'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                    'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                    'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                    'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                    'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                    'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                    'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                    'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                    'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  NmNcgHudsonAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                        'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                        'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                        'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                        'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                        'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                        'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                        'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                        'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                        'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                        'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                        'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                        'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                        'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                        'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function NcgMedicalNcgHudsonARDuplicates(Request $request)
     {
         try {
            NmNcgHudsonArDuplicates::insert([
                'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function NcgMedicalNcgHscAR(Request $request)
     {
         try {
             $attributes = [
                 'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                 'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                 'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                 'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                 'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                 'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                 'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                 'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                 'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                 'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                 'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                 'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                 'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                 'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                 'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
             ];
 
             $duplicateRecordExisting  =  NmNcgHscAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                NmNcgHscAr::insert([
                    'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                    'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                    'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                    'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                    'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                    'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                    'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                    'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                    'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                    'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                    'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                    'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                    'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                    'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                    'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  NmNcgHscAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                        'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                        'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                        'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                        'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                        'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                        'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                        'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                        'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                        'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                        'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                        'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                        'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                        'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                        'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function NcgMedicalNcgHscARDuplicates(Request $request)
     {
         try {
            NmNcgHscArDuplicates::insert([
                'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function NcgMedicalNcgPsssfAR(Request $request)
     {
         try {
             $attributes = [
                 'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                 'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                 'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                 'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                 'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                 'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                 'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                 'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                 'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                 'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                 'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                 'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                 'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                 'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                 'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
             ];
 
             $duplicateRecordExisting  =  NmNcgPsssf::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                NmNcgPsssf::insert([
                    'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                    'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                    'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                    'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                    'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                    'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                    'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                    'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                    'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                    'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                    'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                    'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                    'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                    'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                    'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  NmNcgPsssf::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                        'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                        'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                        'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                        'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                        'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                        'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                        'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                        'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                        'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                        'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                        'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                        'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                        'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                        'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function NcgMedicalNcgPsssfARDuplicates(Request $request)
     {
         try {
            NmNcgPsssfDuplicates::insert([
                'queue' => isset($request->queue) && $request->queue != "NULL" ? $request->queue : NULL,
                'insurance_no' => isset($request->insurance_no) && $request->insurance_no != "NULL" ? $request->insurance_no : NULL,
                'unique_value' => isset($request->unique_value) && $request->unique_value != "NULL" ? $request->unique_value : NULL,
                'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                'unqi_1' => isset($request->unqi_1) && $request->unqi_1 != "NULL" ? $request->unqi_1 : NULL,
                'duplicate' => isset($request->duplicate) && $request->duplicate != "NULL" ? $request->duplicate : NULL,
                'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'cpt_else_mod' => isset($request->cpt_else_mod) && $request->cpt_else_mod != "NULL" ? $request->cpt_else_mod : NULL,   
                'dx_code' => isset($request->dx_code) && $request->dx_code != "NULL" ? $request->dx_code : NULL,  
                'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,  
                'last_datebilled' => isset($request->last_datebilled) && $request->last_datebilled != "NULL" ? $request->last_datebilled : NULL,  
                'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL,  
                'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,  
                'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,  
                'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan : NULL,  
                'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,  
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function srmgAR(Request $request)
     {
         try {
             $attributes = [
                'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                ];
 
             $duplicateRecordExisting  =  SrmgAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                SrmgAr::insert([
                    'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                    'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                    'patient_lastname' => isset($request->patient_lastname) && $request->patient_lastname != "NULL" ? $request->patient_lastname : NULL,
                    'patient_firstname' => isset($request->patient_firstname) && $request->patient_firstname != "NULL" ? $request->patient_firstname : NULL,
                    'patientdob' => isset($request->patientdob) && $request->patientdob != "NULL" ? $request->patientdob : NULL,
                    'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                    'policyidnumber' => isset($request->policyidnumber) && $request->policyidnumber != "NULL" ? $request->policyidnumber : NULL,
                    'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,   
                    'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,  
                    'srvbucket_61_to_90' => isset($request->srvbucket_61_to_90) && $request->srvbucket_61_to_90 != "NULL" ? $request->srvbucket_61_to_90 : NULL,  
                    'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,  
                    'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,  
                    'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,  
                    'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,  
                    'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'currenterror' => isset($request->currenterror) && $request->currenterror != "NULL" ? $request->currenterror : NULL,  
                    'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,  
                    'current_err_rej_reason' => isset($request->current_err_rej_reason) && $request->current_err_rej_reason != "NULL" ? $request->current_err_rej_reason : NULL,  
                    'days_in_status' => isset($request->days_in_status) && $request->days_in_status != "NULL" ? $request->days_in_status : NULL,  
                    'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,  
                    'curr_lcl_rule' => isset($request->curr_lcl_rule) && $request->curr_lcl_rule != "NULL" ? $request->curr_lcl_rule : NULL,  
                    'curr_payor_kick_code' => isset($request->curr_payor_kick_code) && $request->curr_payor_kick_code != "NULL" ? $request->curr_payor_kick_code : NULL,  
                    'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  SrmgAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                        'patient_lastname' => isset($request->patient_lastname) && $request->patient_lastname != "NULL" ? $request->patient_lastname : NULL,
                        'patient_firstname' => isset($request->patient_firstname) && $request->patient_firstname != "NULL" ? $request->patient_firstname : NULL,
                        'patientdob' => isset($request->patientdob) && $request->patientdob != "NULL" ? $request->patientdob : NULL,
                        'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                        'policyidnumber' => isset($request->policyidnumber) && $request->policyidnumber != "NULL" ? $request->policyidnumber : NULL,
                        'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,   
                        'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,  
                        'srvbucket_61_to_90' => isset($request->srvbucket_61_to_90) && $request->srvbucket_61_to_90 != "NULL" ? $request->srvbucket_61_to_90 : NULL,  
                        'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,  
                        'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,  
                        'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,  
                        'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,  
                        'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'currenterror' => isset($request->currenterror) && $request->currenterror != "NULL" ? $request->currenterror : NULL,  
                        'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,  
                        'current_err_rej_reason' => isset($request->current_err_rej_reason) && $request->current_err_rej_reason != "NULL" ? $request->current_err_rej_reason : NULL,  
                        'days_in_status' => isset($request->days_in_status) && $request->days_in_status != "NULL" ? $request->days_in_status : NULL,  
                        'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,  
                        'curr_lcl_rule' => isset($request->curr_lcl_rule) && $request->curr_lcl_rule != "NULL" ? $request->curr_lcl_rule : NULL,  
                        'curr_payor_kick_code' => isset($request->curr_payor_kick_code) && $request->curr_payor_kick_code != "NULL" ? $request->curr_payor_kick_code : NULL,  
                        'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,  
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function srmgARDuplicates(Request $request)
     {
         try {
            SrmgArDuplicates::insert([
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                'patient_lastname' => isset($request->patient_lastname) && $request->patient_lastname != "NULL" ? $request->patient_lastname : NULL,
                'patient_firstname' => isset($request->patient_firstname) && $request->patient_firstname != "NULL" ? $request->patient_firstname : NULL,
                'patientdob' => isset($request->patientdob) && $request->patientdob != "NULL" ? $request->patientdob : NULL,
                'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                'policyidnumber' => isset($request->policyidnumber) && $request->policyidnumber != "NULL" ? $request->policyidnumber : NULL,
                'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,   
                'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,  
                'srvbucket_61_to_90' => isset($request->srvbucket_61_to_90) && $request->srvbucket_61_to_90 != "NULL" ? $request->srvbucket_61_to_90 : NULL,  
                'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,  
                'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,  
                'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,  
                'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,  
                'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'currenterror' => isset($request->currenterror) && $request->currenterror != "NULL" ? $request->currenterror : NULL,  
                'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,  
                'current_err_rej_reason' => isset($request->current_err_rej_reason) && $request->current_err_rej_reason != "NULL" ? $request->current_err_rej_reason : NULL,  
                'days_in_status' => isset($request->days_in_status) && $request->days_in_status != "NULL" ? $request->days_in_status : NULL,  
                'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,  
                'curr_lcl_rule' => isset($request->curr_lcl_rule) && $request->curr_lcl_rule != "NULL" ? $request->curr_lcl_rule : NULL,  
                'curr_payor_kick_code' => isset($request->curr_payor_kick_code) && $request->curr_payor_kick_code != "NULL" ? $request->curr_payor_kick_code : NULL,  
                'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,  
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function ValleyUrogynecologyAssociatesAR(Request $request)
     {
         try {
             $attributes = [
                'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                ];
 
             $duplicateRecordExisting  =  VuaAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                VuaAr::insert([
                    'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                    'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                    'patient_lastname' => isset($request->patient_lastname) && $request->patient_lastname != "NULL" ? $request->patient_lastname : NULL,
                    'patient_firstname' => isset($request->patient_firstname) && $request->patient_firstname != "NULL" ? $request->patient_firstname : NULL,
                    'patientdob' => isset($request->patientdob) && $request->patientdob != "NULL" ? $request->patientdob : NULL,
                    'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                    'policyidnumber' => isset($request->policyidnumber) && $request->policyidnumber != "NULL" ? $request->policyidnumber : NULL,
                    'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,   
                    'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,  
                    'srvbucket_61_to_90' => isset($request->srvbucket_61_to_90) && $request->srvbucket_61_to_90 != "NULL" ? $request->srvbucket_61_to_90 : NULL,  
                    'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,  
                    'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,  
                    'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,  
                    'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,  
                    'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'currenterror' => isset($request->currenterror) && $request->currenterror != "NULL" ? $request->currenterror : NULL,  
                    'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,  
                    'current_err_rej_reason' => isset($request->current_err_rej_reason) && $request->current_err_rej_reason != "NULL" ? $request->current_err_rej_reason : NULL,  
                    'days_in_status' => isset($request->days_in_status) && $request->days_in_status != "NULL" ? $request->days_in_status : NULL,  
                    'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,  
                    'curr_lcl_rule' => isset($request->curr_lcl_rule) && $request->curr_lcl_rule != "NULL" ? $request->curr_lcl_rule : NULL,  
                    'curr_payor_kick_code' => isset($request->curr_payor_kick_code) && $request->curr_payor_kick_code != "NULL" ? $request->curr_payor_kick_code : NULL,  
                    'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  VuaAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                        'patient_lastname' => isset($request->patient_lastname) && $request->patient_lastname != "NULL" ? $request->patient_lastname : NULL,
                        'patient_firstname' => isset($request->patient_firstname) && $request->patient_firstname != "NULL" ? $request->patient_firstname : NULL,
                        'patientdob' => isset($request->patientdob) && $request->patientdob != "NULL" ? $request->patientdob : NULL,
                        'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                        'policyidnumber' => isset($request->policyidnumber) && $request->policyidnumber != "NULL" ? $request->policyidnumber : NULL,
                        'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,   
                        'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,  
                        'srvbucket_61_to_90' => isset($request->srvbucket_61_to_90) && $request->srvbucket_61_to_90 != "NULL" ? $request->srvbucket_61_to_90 : NULL,  
                        'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,  
                        'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,  
                        'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,  
                        'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,  
                        'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'currenterror' => isset($request->currenterror) && $request->currenterror != "NULL" ? $request->currenterror : NULL,  
                        'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,  
                        'current_err_rej_reason' => isset($request->current_err_rej_reason) && $request->current_err_rej_reason != "NULL" ? $request->current_err_rej_reason : NULL,  
                        'days_in_status' => isset($request->days_in_status) && $request->days_in_status != "NULL" ? $request->days_in_status : NULL,  
                        'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,  
                        'curr_lcl_rule' => isset($request->curr_lcl_rule) && $request->curr_lcl_rule != "NULL" ? $request->curr_lcl_rule : NULL,  
                        'curr_payor_kick_code' => isset($request->curr_payor_kick_code) && $request->curr_payor_kick_code != "NULL" ? $request->curr_payor_kick_code : NULL,  
                        'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,  
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function ValleyUrogynecologyAssociatesARDuplicates(Request $request)
     {
         try {
              VuaArDuplicates::insert([
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                'patient_lastname' => isset($request->patient_lastname) && $request->patient_lastname != "NULL" ? $request->patient_lastname : NULL,
                'patient_firstname' => isset($request->patient_firstname) && $request->patient_firstname != "NULL" ? $request->patient_firstname : NULL,
                'patientdob' => isset($request->patientdob) && $request->patientdob != "NULL" ? $request->patientdob : NULL,
                'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                'policyidnumber' => isset($request->policyidnumber) && $request->policyidnumber != "NULL" ? $request->policyidnumber : NULL,
                'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,   
                'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,  
                'srvbucket_61_to_90' => isset($request->srvbucket_61_to_90) && $request->srvbucket_61_to_90 != "NULL" ? $request->srvbucket_61_to_90 : NULL,  
                'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,  
                'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,  
                'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,  
                'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,  
                'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'currenterror' => isset($request->currenterror) && $request->currenterror != "NULL" ? $request->currenterror : NULL,  
                'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,  
                'current_err_rej_reason' => isset($request->current_err_rej_reason) && $request->current_err_rej_reason != "NULL" ? $request->current_err_rej_reason : NULL,  
                'days_in_status' => isset($request->days_in_status) && $request->days_in_status != "NULL" ? $request->days_in_status : NULL,  
                'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,  
                'curr_lcl_rule' => isset($request->curr_lcl_rule) && $request->curr_lcl_rule != "NULL" ? $request->curr_lcl_rule : NULL,  
                'curr_payor_kick_code' => isset($request->curr_payor_kick_code) && $request->curr_payor_kick_code != "NULL" ? $request->curr_payor_kick_code : NULL,  
                'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,  
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function advancedMedicalBillingCollectionsPrnAr(Request $request)
     {
         try {
             $attributes = [
                'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                ];
 
             $duplicateRecordExisting  =  AmbcPrnAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                AmbcPrnAr::insert([
                    'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                    'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                    'patient_lastname' => isset($request->patient_lastname) && $request->patient_lastname != "NULL" ? $request->patient_lastname : NULL,
                    'patient_firstname' => isset($request->patient_firstname) && $request->patient_firstname != "NULL" ? $request->patient_firstname : NULL,
                    'patientdob' => isset($request->patientdob) && $request->patientdob != "NULL" ? $request->patientdob : NULL,
                    'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                    'policyidnumber' => isset($request->policyidnumber) && $request->policyidnumber != "NULL" ? $request->policyidnumber : NULL,
                    'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,   
                    'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,  
                    'srvbucket_61_to_90' => isset($request->srvbucket_61_to_90) && $request->srvbucket_61_to_90 != "NULL" ? $request->srvbucket_61_to_90 : NULL,  
                    'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,  
                    'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,  
                    'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,  
                    'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,  
                    'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'currenterror' => isset($request->currenterror) && $request->currenterror != "NULL" ? $request->currenterror : NULL,  
                    'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,  
                    'current_err_rej_reason' => isset($request->current_err_rej_reason) && $request->current_err_rej_reason != "NULL" ? $request->current_err_rej_reason : NULL,  
                    'days_in_status' => isset($request->days_in_status) && $request->days_in_status != "NULL" ? $request->days_in_status : NULL,  
                    'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,  
                    'curr_lcl_rule' => isset($request->curr_lcl_rule) && $request->curr_lcl_rule != "NULL" ? $request->curr_lcl_rule : NULL,  
                    'curr_payor_kick_code' => isset($request->curr_payor_kick_code) && $request->curr_payor_kick_code != "NULL" ? $request->curr_payor_kick_code : NULL,  
                    'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  AmbcPrnAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                        'patient_lastname' => isset($request->patient_lastname) && $request->patient_lastname != "NULL" ? $request->patient_lastname : NULL,
                        'patient_firstname' => isset($request->patient_firstname) && $request->patient_firstname != "NULL" ? $request->patient_firstname : NULL,
                        'patientdob' => isset($request->patientdob) && $request->patientdob != "NULL" ? $request->patientdob : NULL,
                        'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                        'policyidnumber' => isset($request->policyidnumber) && $request->policyidnumber != "NULL" ? $request->policyidnumber : NULL,
                        'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,   
                        'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,  
                        'srvbucket_61_to_90' => isset($request->srvbucket_61_to_90) && $request->srvbucket_61_to_90 != "NULL" ? $request->srvbucket_61_to_90 : NULL,  
                        'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,  
                        'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,  
                        'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,  
                        'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,  
                        'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'currenterror' => isset($request->currenterror) && $request->currenterror != "NULL" ? $request->currenterror : NULL,  
                        'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,  
                        'current_err_rej_reason' => isset($request->current_err_rej_reason) && $request->current_err_rej_reason != "NULL" ? $request->current_err_rej_reason : NULL,  
                        'days_in_status' => isset($request->days_in_status) && $request->days_in_status != "NULL" ? $request->days_in_status : NULL,  
                        'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,  
                        'curr_lcl_rule' => isset($request->curr_lcl_rule) && $request->curr_lcl_rule != "NULL" ? $request->curr_lcl_rule : NULL,  
                        'curr_payor_kick_code' => isset($request->curr_payor_kick_code) && $request->curr_payor_kick_code != "NULL" ? $request->curr_payor_kick_code : NULL,  
                        'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,  
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function advancedMedicalBillingCollectionsPrnArDuplicates(Request $request)
     {
         try {
            AmbcPrnArDuplicates::insert([
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                'patient_lastname' => isset($request->patient_lastname) && $request->patient_lastname != "NULL" ? $request->patient_lastname : NULL,
                'patient_firstname' => isset($request->patient_firstname) && $request->patient_firstname != "NULL" ? $request->patient_firstname : NULL,
                'patientdob' => isset($request->patientdob) && $request->patientdob != "NULL" ? $request->patientdob : NULL,
                'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                'policyidnumber' => isset($request->policyidnumber) && $request->policyidnumber != "NULL" ? $request->policyidnumber : NULL,
                'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,   
                'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,  
                'srvbucket_61_to_90' => isset($request->srvbucket_61_to_90) && $request->srvbucket_61_to_90 != "NULL" ? $request->srvbucket_61_to_90 : NULL,  
                'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,  
                'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,  
                'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,  
                'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,  
                'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'currenterror' => isset($request->currenterror) && $request->currenterror != "NULL" ? $request->currenterror : NULL,  
                'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,  
                'current_err_rej_reason' => isset($request->current_err_rej_reason) && $request->current_err_rej_reason != "NULL" ? $request->current_err_rej_reason : NULL,  
                'days_in_status' => isset($request->days_in_status) && $request->days_in_status != "NULL" ? $request->days_in_status : NULL,  
                'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,  
                'curr_lcl_rule' => isset($request->curr_lcl_rule) && $request->curr_lcl_rule != "NULL" ? $request->curr_lcl_rule : NULL,  
                'curr_payor_kick_code' => isset($request->curr_payor_kick_code) && $request->curr_payor_kick_code != "NULL" ? $request->curr_payor_kick_code : NULL,  
                'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,  
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function coloradoFacialPlasticSurgeryAr(Request $request)
     {
         try {
             $attributes = [
                'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
                'insured_id' => isset($request->insured_id) && $request->insured_id != "NULL" ? $request->insured_id : NULL,  
                'proc_code' => isset($request->proc_code) && $request->proc_code != "NULL" ? $request->proc_code     : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'phone_number' => isset($request->phone_number) && $request->phone_number != "NULL" ? $request->phone_number : NULL,  
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,  
                ];
 
             $duplicateRecordExisting  =  CfpsAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                CfpsAr::insert([
                    'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
                    'insured_id' => isset($request->insured_id) && $request->insured_id != "NULL" ? $request->insured_id : NULL,  
                    'proc_code' => isset($request->proc_code) && $request->proc_code != "NULL" ? $request->proc_code     : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'phone_number' => isset($request->phone_number) && $request->phone_number != "NULL" ? $request->phone_number : NULL,  
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  CfpsAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                        'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
                        'insured_id' => isset($request->insured_id) && $request->insured_id != "NULL" ? $request->insured_id : NULL,  
                        'proc_code' => isset($request->proc_code) && $request->proc_code != "NULL" ? $request->proc_code     : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'phone_number' => isset($request->phone_number) && $request->phone_number != "NULL" ? $request->phone_number : NULL,  
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,  
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function coloradoFacialPlasticSurgeryArDuplicates(Request $request)
     {
         try {
            
            CfpsArDuplicates::insert([
                'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
                'insured_id' => isset($request->insured_id) && $request->insured_id != "NULL" ? $request->insured_id : NULL,  
                'proc_code' => isset($request->proc_code) && $request->proc_code != "NULL" ? $request->proc_code     : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'phone_number' => isset($request->phone_number) && $request->phone_number != "NULL" ? $request->phone_number : NULL,  
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,  
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function dayKimballMedicalGroupAr(Request $request)
     {
         try {
             $attributes = [
                'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name: NULL
                ];
 
             $duplicateRecordExisting  =  DkmgAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                DkmgAr::insert([
                    'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                    'patientid' => isset($request->patientid) && $request->patientid != "NULL" ? $request->patientid : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'patientdob' => isset($request->patientdob) && $request->patientdob != "NULL" ? $request->patientdob : NULL,  
                    'cstm_ins_grpng' => isset($request->cstm_ins_grpng) && $request->cstm_ins_grpng != "NULL" ? $request->cstm_ins_grpng : NULL,  
                    'ins_pkg_id' => isset($request->ins_pkg_id) && $request->ins_pkg_id != "NULL" ? $request->ins_pkg_id : NULL,  
                    'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,  
                    'ins_report_cat' => isset($request->ins_report_cat) && $request->ins_report_cat != "NULL" ? $request->ins_report_cat : NULL,  
                    'proccode' => isset($request->proccode) && $request->proccode != "NULL" ? $request->proccode : NULL,  
                    'proccode_grp' => isset($request->proccode_grp) && $request->proccode_grp != "NULL" ? $request->proccode_grp : NULL,  
                    'rndrng_prvdr' => isset($request->rndrng_prvdr) && $request->rndrng_prvdr != "NULL" ? $request->rndrng_prvdr : NULL,  
                    'sup_prvdr' => isset($request->sup_prvdr) && $request->sup_prvdr != "NULL" ? $request->sup_prvdr : NULL,  
                    'svc_dprtmnt' => isset($request->svc_dprtmnt) && $request->svc_dprtmnt != "NULL" ? $request->svc_dprtmnt : NULL,  
                    'rndrng_prvdr_mdcl_grp' => isset($request->rndrng_prvdr_mdcl_grp) && $request->rndrng_prvdr_mdcl_grp != "NULL" ? $request->rndrng_prvdr_mdcl_grp : NULL,  
                    'sprsvng_prvdr_prvdr_grp' => isset($request->sprsvng_prvdr_prvdr_grp) && $request->sprsvng_prvdr_prvdr_grp != "NULL" ? $request->sprsvng_prvdr_prvdr_grp : NULL,  
                    'curr_athena_kick_code' => isset($request->curr_athena_kick_code) && $request->curr_athena_kick_code != "NULL" ? $request->curr_athena_kick_code : NULL,  
                    'curr_athena_kick_code_rej_rsn' => isset($request->curr_athena_kick_code_rej_rsn) && $request->curr_athena_kick_code_rej_rsn != "NULL" ? $request->curr_athena_kick_code_rej_rsn : NULL,  
                    'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,  
                    'curr_glbl_rule_rej_rsn' => isset($request->curr_glbl_rule_rej_rsn) && $request->curr_glbl_rule_rej_rsn != "NULL" ? $request->curr_glbl_rule_rej_rsn : NULL,  
                    'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,  
                    'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                    'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,  
                    'trnsfr_type' => isset($request->trnsfr_type) && $request->trnsfr_type != "NULL" ? $request->trnsfr_type : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  DkmgAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                        'patientid' => isset($request->patientid) && $request->patientid != "NULL" ? $request->patientid : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'patientdob' => isset($request->patientdob) && $request->patientdob != "NULL" ? $request->patientdob : NULL,  
                        'cstm_ins_grpng' => isset($request->cstm_ins_grpng) && $request->cstm_ins_grpng != "NULL" ? $request->cstm_ins_grpng : NULL,  
                        'ins_pkg_id' => isset($request->ins_pkg_id) && $request->ins_pkg_id != "NULL" ? $request->ins_pkg_id : NULL,  
                        'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,  
                        'ins_report_cat' => isset($request->ins_report_cat) && $request->ins_report_cat != "NULL" ? $request->ins_report_cat : NULL,  
                        'proccode' => isset($request->proccode) && $request->proccode != "NULL" ? $request->proccode : NULL,  
                        'proccode_grp' => isset($request->proccode_grp) && $request->proccode_grp != "NULL" ? $request->proccode_grp : NULL,  
                        'rndrng_prvdr' => isset($request->rndrng_prvdr) && $request->rndrng_prvdr != "NULL" ? $request->rndrng_prvdr : NULL,  
                        'sup_prvdr' => isset($request->sup_prvdr) && $request->sup_prvdr != "NULL" ? $request->sup_prvdr : NULL,  
                        'svc_dprtmnt' => isset($request->svc_dprtmnt) && $request->svc_dprtmnt != "NULL" ? $request->svc_dprtmnt : NULL,  
                        'rndrng_prvdr_mdcl_grp' => isset($request->rndrng_prvdr_mdcl_grp) && $request->rndrng_prvdr_mdcl_grp != "NULL" ? $request->rndrng_prvdr_mdcl_grp : NULL,  
                        'sprsvng_prvdr_prvdr_grp' => isset($request->sprsvng_prvdr_prvdr_grp) && $request->sprsvng_prvdr_prvdr_grp != "NULL" ? $request->sprsvng_prvdr_prvdr_grp : NULL,  
                        'curr_athena_kick_code' => isset($request->curr_athena_kick_code) && $request->curr_athena_kick_code != "NULL" ? $request->curr_athena_kick_code : NULL,  
                        'curr_athena_kick_code_rej_rsn' => isset($request->curr_athena_kick_code_rej_rsn) && $request->curr_athena_kick_code_rej_rsn != "NULL" ? $request->curr_athena_kick_code_rej_rsn : NULL,  
                        'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,  
                        'curr_glbl_rule_rej_rsn' => isset($request->curr_glbl_rule_rej_rsn) && $request->curr_glbl_rule_rej_rsn != "NULL" ? $request->curr_glbl_rule_rej_rsn : NULL,  
                        'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,  
                        'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                        'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,  
                        'trnsfr_type' => isset($request->trnsfr_type) && $request->trnsfr_type != "NULL" ? $request->trnsfr_type : NULL,  
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function dayKimballMedicalGroupArDuplicates(Request $request)
     {
         try {
            
            DkmgArDuplicates::insert([
                'claimid' => isset($request->claimid) && $request->claimid != "NULL" ? $request->claimid : NULL,
                'patientid' => isset($request->patientid) && $request->patientid != "NULL" ? $request->patientid : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'patientdob' => isset($request->patientdob) && $request->patientdob != "NULL" ? $request->patientdob : NULL,  
                'cstm_ins_grpng' => isset($request->cstm_ins_grpng) && $request->cstm_ins_grpng != "NULL" ? $request->cstm_ins_grpng : NULL,  
                'ins_pkg_id' => isset($request->ins_pkg_id) && $request->ins_pkg_id != "NULL" ? $request->ins_pkg_id : NULL,  
                'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,  
                'ins_report_cat' => isset($request->ins_report_cat) && $request->ins_report_cat != "NULL" ? $request->ins_report_cat : NULL,  
                'proccode' => isset($request->proccode) && $request->proccode != "NULL" ? $request->proccode : NULL,  
                'proccode_grp' => isset($request->proccode_grp) && $request->proccode_grp != "NULL" ? $request->proccode_grp : NULL,  
                'rndrng_prvdr' => isset($request->rndrng_prvdr) && $request->rndrng_prvdr != "NULL" ? $request->rndrng_prvdr : NULL,  
                'sup_prvdr' => isset($request->sup_prvdr) && $request->sup_prvdr != "NULL" ? $request->sup_prvdr : NULL,  
                'svc_dprtmnt' => isset($request->svc_dprtmnt) && $request->svc_dprtmnt != "NULL" ? $request->svc_dprtmnt : NULL,  
                'rndrng_prvdr_mdcl_grp' => isset($request->rndrng_prvdr_mdcl_grp) && $request->rndrng_prvdr_mdcl_grp != "NULL" ? $request->rndrng_prvdr_mdcl_grp : NULL,  
                'sprsvng_prvdr_prvdr_grp' => isset($request->sprsvng_prvdr_prvdr_grp) && $request->sprsvng_prvdr_prvdr_grp != "NULL" ? $request->sprsvng_prvdr_prvdr_grp : NULL,  
                'curr_athena_kick_code' => isset($request->curr_athena_kick_code) && $request->curr_athena_kick_code != "NULL" ? $request->curr_athena_kick_code : NULL,  
                'curr_athena_kick_code_rej_rsn' => isset($request->curr_athena_kick_code_rej_rsn) && $request->curr_athena_kick_code_rej_rsn != "NULL" ? $request->curr_athena_kick_code_rej_rsn : NULL,  
                'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,  
                'curr_glbl_rule_rej_rsn' => isset($request->curr_glbl_rule_rej_rsn) && $request->curr_glbl_rule_rej_rsn != "NULL" ? $request->curr_glbl_rule_rej_rsn : NULL,  
                'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,  
                'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,  
                'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,  
                'trnsfr_type' => isset($request->trnsfr_type) && $request->trnsfr_type != "NULL" ? $request->trnsfr_type : NULL,  
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function bertNashCommunityMentalHealthCenterAR(Request $request)
     {
         try {
             $attributes = [
                'charge_id' => isset($request->charge_id) && $request->charge_id != "NULL" ? $request->charge_id : NULL,
                'client_name' => isset($request->client_name) && $request->client_name != "NULL" ? $request->client_name : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance: NULL
                ];
 
             $duplicateRecordExisting  =  BncmhcAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                BncmhcAr::insert([
                    'charge_id' => isset($request->charge_id) && $request->charge_id != "NULL" ? $request->charge_id : NULL,
                    'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL,
                    'client_name' => isset($request->client_name) && $request->client_name != "NULL" ? $request->client_name : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'clinician' => isset($request->clinician) && $request->clinician != "NULL" ? $request->clinician : NULL,  
                    'procedure_name' => isset($request->procedure_name) && $request->procedure_name != "NULL" ? $request->procedure_name : NULL,  
                    'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,  
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,  
                    'claim_line_item_id' => isset($request->claim_line_item_id) && $request->claim_line_item_id != "NULL" ? $request->claim_line_item_id : NULL,  
                    'payer_name_as_per_smartcare' => isset($request->payer_name_as_per_smartcare) && $request->payer_name_as_per_smartcare != "NULL" ? $request->payer_name_as_per_smartcare : NULL,  
                    'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  BncmhcAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'charge_id' => isset($request->charge_id) && $request->charge_id != "NULL" ? $request->charge_id : NULL,
                        'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL,
                        'client_name' => isset($request->client_name) && $request->client_name != "NULL" ? $request->client_name : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'clinician' => isset($request->clinician) && $request->clinician != "NULL" ? $request->clinician : NULL,  
                        'procedure_name' => isset($request->procedure_name) && $request->procedure_name != "NULL" ? $request->procedure_name : NULL,  
                        'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,  
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,  
                        'claim_line_item_id' => isset($request->claim_line_item_id) && $request->claim_line_item_id != "NULL" ? $request->claim_line_item_id : NULL,  
                        'payer_name_as_per_smartcare' => isset($request->payer_name_as_per_smartcare) && $request->payer_name_as_per_smartcare != "NULL" ? $request->payer_name_as_per_smartcare : NULL,  
                        'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,  
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function bertNashCommunityMentalHealthCenterARDuplicates(Request $request)
     {
         try {
            
            BncmhcArDuplicates::insert([
                'charge_id' => isset($request->charge_id) && $request->charge_id != "NULL" ? $request->charge_id : NULL,
                'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL,
                'client_name' => isset($request->client_name) && $request->client_name != "NULL" ? $request->client_name : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'clinician' => isset($request->clinician) && $request->clinician != "NULL" ? $request->clinician : NULL,  
                'procedure_name' => isset($request->procedure_name) && $request->procedure_name != "NULL" ? $request->procedure_name : NULL,  
                'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,  
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,  
                'claim_line_item_id' => isset($request->claim_line_item_id) && $request->claim_line_item_id != "NULL" ? $request->claim_line_item_id : NULL,  
                'payer_name_as_per_smartcare' => isset($request->payer_name_as_per_smartcare) && $request->payer_name_as_per_smartcare != "NULL" ? $request->payer_name_as_per_smartcare : NULL,  
                'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,  
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

  // Retina Northwest

  public function RetinaNorthwestAR(Request $request)
     {
         try {
             $attributes = [
                'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,                
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance: NULL
                ];
 
             $duplicateRecordExisting  =  RnAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                RnAr::insert([
                    'patientname' => isset($request->patientname) && $request->patientname != "NULL" ? $request->patientname : NULL,
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
                    'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,  
                    'uid' => isset($request->uid) && $request->uid != "NULL" ? $request->uid : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'year' => isset($request->year) && $request->year != "NULL" ? $request->year : NULL,  
                    'month' => isset($request->month) && $request->month != "NULL" ? $request->month : NULL,  
                    'responsibility' => isset($request->responsibility) && $request->responsibility != "NULL" ? $request->responsibility : NULL,  
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                    'first_billed' => isset($request->first_billed) && $request->first_billed != "NULL" ? $request->first_billed : NULL,  
                    'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,  
                    'last_billed' => isset($request->last_billed) && $request->last_billed != "NULL" ? $request->last_billed : NULL, 
                    'last_payment' => isset($request->last_payment) && $request->last_payment != "NULL" ? $request->last_payment : NULL, 
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL, 
                    'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL, 
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL, 
                    'policyid' => isset($request->policyid) && $request->policyid != "NULL" ? $request->policyid : NULL,
                    'group_number' => isset($request->group_number) && $request->group_number != "NULL" ? $request->group_number : NULL,
                    'on_hold' => isset($request->on_hold) && $request->on_hold != "NULL" ? $request->on_hold : NULL,
                    'aging_current' => isset($request->aging_current) && $request->aging_current != "NULL" ? $request->aging_current : NULL,
                    'aging_30_to_60' => isset($request->aging_30_to_60) && $request->aging_30_to_60 != "NULL" ? $request->aging_30_to_60 : NULL,
                    'aging_60_to_90' => isset($request->aging_60_to_90) && $request->aging_60_to_90 != "NULL" ? $request->aging_60_to_90 : NULL,
                    'aging_90_to_120' => isset($request->aging_90_to_120) && $request->aging_90_to_120 != "NULL" ? $request->aging_90_to_120 : NULL,
                    'aging_120_to_150' => isset($request->aging_120_to_150) && $request->aging_120_to_150 != "NULL" ? $request->aging_120_to_150 : NULL,
                    'aging_older' => isset($request->aging_older) && $request->aging_older != "NULL" ? $request->aging_older : NULL,
                    'last_worklist_status_name' => isset($request->last_worklist_status_name) && $request->last_worklist_status_name != "NULL" ? $request->last_worklist_status_name : NULL,
                    'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                    'last_worklist_status_note' => isset($request->last_worklist_status_note) && $request->last_worklist_status_note != "NULL" ? $request->last_worklist_status_note : NULL,
                    'last_worklist_status_date' => isset($request->last_worklist_status_date) && $request->last_worklist_status_date != "NULL" ? $request->last_worklist_status_date : NULL,
                    'last_worklist_status_username' => isset($request->last_worklist_status_username) && $request->last_worklist_status_username != "NULL" ? $request->last_worklist_status_username : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  RnAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'patientname' => isset($request->patientname) && $request->patientname != "NULL" ? $request->patientname : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
                        'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,  
                        'uid' => isset($request->uid) && $request->uid != "NULL" ? $request->uid : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'year' => isset($request->year) && $request->year != "NULL" ? $request->year : NULL,  
                        'month' => isset($request->month) && $request->month != "NULL" ? $request->month : NULL,  
                        'responsibility' => isset($request->responsibility) && $request->responsibility != "NULL" ? $request->responsibility : NULL,  
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                        'first_billed' => isset($request->first_billed) && $request->first_billed != "NULL" ? $request->first_billed : NULL,  
                        'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,  
                        'last_billed' => isset($request->last_billed) && $request->last_billed != "NULL" ? $request->last_billed : NULL, 
                        'last_payment' => isset($request->last_payment) && $request->last_payment != "NULL" ? $request->last_payment : NULL, 
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL, 
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL, 
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL, 
                        'policyid' => isset($request->policyid) && $request->policyid != "NULL" ? $request->policyid : NULL,
                        'group_number' => isset($request->group_number) && $request->group_number != "NULL" ? $request->group_number : NULL,
                        'on_hold' => isset($request->on_hold) && $request->on_hold != "NULL" ? $request->on_hold : NULL,
                        'aging_current' => isset($request->aging_current) && $request->aging_current != "NULL" ? $request->aging_current : NULL,
                        'aging_30_to_60' => isset($request->aging_30_to_60) && $request->aging_30_to_60 != "NULL" ? $request->aging_30_to_60 : NULL,
                        'aging_60_to_90' => isset($request->aging_60_to_90) && $request->aging_60_to_90 != "NULL" ? $request->aging_60_to_90 : NULL,
                        'aging_90_to_120' => isset($request->aging_90_to_120) && $request->aging_90_to_120 != "NULL" ? $request->aging_90_to_120 : NULL,
                        'aging_120_to_150' => isset($request->aging_120_to_150) && $request->aging_120_to_150 != "NULL" ? $request->aging_120_to_150 : NULL,
                        'aging_older' => isset($request->aging_older) && $request->aging_older != "NULL" ? $request->aging_older : NULL,
                        'last_worklist_status_name' => isset($request->last_worklist_status_name) && $request->last_worklist_status_name != "NULL" ? $request->last_worklist_status_name : NULL,
                        'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                        'last_worklist_status_note' => isset($request->last_worklist_status_note) && $request->last_worklist_status_note != "NULL" ? $request->last_worklist_status_note : NULL,
                        'last_worklist_status_date' => isset($request->last_worklist_status_date) && $request->last_worklist_status_date != "NULL" ? $request->last_worklist_status_date : NULL,
                        'last_worklist_status_username' => isset($request->last_worklist_status_username) && $request->last_worklist_status_username != "NULL" ? $request->last_worklist_status_username : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function RetinaNorthwestARDuplicates(Request $request)
     {
         try {
            
            RnArDuplicates::insert([
                'patientname' => isset($request->patientname) && $request->patientname != "NULL" ? $request->patientname : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
                'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,  
                'uid' => isset($request->uid) && $request->uid != "NULL" ? $request->uid : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'year' => isset($request->year) && $request->year != "NULL" ? $request->year : NULL,  
                'month' => isset($request->month) && $request->month != "NULL" ? $request->month : NULL,  
                'responsibility' => isset($request->responsibility) && $request->responsibility != "NULL" ? $request->responsibility : NULL,  
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                'first_billed' => isset($request->first_billed) && $request->first_billed != "NULL" ? $request->first_billed : NULL,  
                'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,  
                'last_billed' => isset($request->last_billed) && $request->last_billed != "NULL" ? $request->last_billed : NULL, 
                'last_payment' => isset($request->last_payment) && $request->last_payment != "NULL" ? $request->last_payment : NULL, 
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL, 
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL, 
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL, 
                'policyid' => isset($request->policyid) && $request->policyid != "NULL" ? $request->policyid : NULL,
                'group_number' => isset($request->group_number) && $request->group_number != "NULL" ? $request->group_number : NULL,
                'on_hold' => isset($request->on_hold) && $request->on_hold != "NULL" ? $request->on_hold : NULL,
                'aging_current' => isset($request->aging_current) && $request->aging_current != "NULL" ? $request->aging_current : NULL,
                'aging_30_to_60' => isset($request->aging_30_to_60) && $request->aging_30_to_60 != "NULL" ? $request->aging_30_to_60 : NULL,
                'aging_60_to_90' => isset($request->aging_60_to_90) && $request->aging_60_to_90 != "NULL" ? $request->aging_60_to_90 : NULL,
                'aging_90_to_120' => isset($request->aging_90_to_120) && $request->aging_90_to_120 != "NULL" ? $request->aging_90_to_120 : NULL,
                'aging_120_to_150' => isset($request->aging_120_to_150) && $request->aging_120_to_150 != "NULL" ? $request->aging_120_to_150 : NULL,
                'aging_older' => isset($request->aging_older) && $request->aging_older != "NULL" ? $request->aging_older : NULL,
                'last_worklist_status_name' => isset($request->last_worklist_status_name) && $request->last_worklist_status_name != "NULL" ? $request->last_worklist_status_name : NULL,
                'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                'last_worklist_status_note' => isset($request->last_worklist_status_note) && $request->last_worklist_status_note != "NULL" ? $request->last_worklist_status_note : NULL,
                'last_worklist_status_date' => isset($request->last_worklist_status_date) && $request->last_worklist_status_date != "NULL" ? $request->last_worklist_status_date : NULL,
                'last_worklist_status_username' => isset($request->last_worklist_status_username) && $request->last_worklist_status_username != "NULL" ? $request->last_worklist_status_username : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function mayersMemorialHospitalAR(Request $request)
     {
         try {
             $attributes = [
                'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,                
                'encounter_id' => isset($request->encounter_id) && $request->encounter_id != "NULL" ? $request->encounter_id : NULL,  
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name: NULL,
                'dos_from' => isset($request->dos_from) && $request->dos_from != "NULL" ? $request->dos_from: NULL,
                'dos_to' => isset($request->dos_to) && $request->dos_to != "NULL" ? $request->dos_to: NULL,
                'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt: NULL,
                'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt: NULL,
                'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan: NULL,
                'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility: NULL
                ];
 
             $duplicateRecordExisting  =  MmhAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                MmhAr::insert([
                    'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,                
                    'encounter_id' => isset($request->encounter_id) && $request->encounter_id != "NULL" ? $request->encounter_id : NULL,  
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name: NULL,
                    'dos_from' => isset($request->dos_from) && $request->dos_from != "NULL" ? $request->dos_from: NULL,
                    'dos_to' => isset($request->dos_to) && $request->dos_to != "NULL" ? $request->dos_to: NULL,
                    'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt: NULL,
                    'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt: NULL,
                    'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan: NULL,
                    'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility: NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  MmhAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,                
                        'encounter_id' => isset($request->encounter_id) && $request->encounter_id != "NULL" ? $request->encounter_id : NULL,  
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name: NULL,
                        'dos_from' => isset($request->dos_from) && $request->dos_from != "NULL" ? $request->dos_from: NULL,
                        'dos_to' => isset($request->dos_to) && $request->dos_to != "NULL" ? $request->dos_to: NULL,
                        'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt: NULL,
                        'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt: NULL,
                        'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan: NULL,
                        'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility: NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function mayersMemorialHospitalARDuplicates(Request $request)
     {
         try {
            
            MmhArDuplicates::insert([
                'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,                
                'encounter_id' => isset($request->encounter_id) && $request->encounter_id != "NULL" ? $request->encounter_id : NULL,  
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name: NULL,
                'dos_from' => isset($request->dos_from) && $request->dos_from != "NULL" ? $request->dos_from: NULL,
                'dos_to' => isset($request->dos_to) && $request->dos_to != "NULL" ? $request->dos_to: NULL,
                'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt: NULL,
                'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt: NULL,
                'insurance_plan' => isset($request->insurance_plan) && $request->insurance_plan != "NULL" ? $request->insurance_plan: NULL,
                'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility: NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function restorationHealthcareAr(Request $request)
     {
         try {
             $attributes = [
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,                
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id: NULL,
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance: NULL,
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider: NULL,
                'office' => isset($request->office) && $request->office != "NULL" ? $request->office: NULL,
                'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed: NULL,
                'first_edi_date' => isset($request->first_edi_date) && $request->first_edi_date != "NULL" ? $request->first_edi_date: NULL,
                'last_edi_date' => isset($request->last_edi_date) && $request->last_edi_date != "NULL" ? $request->last_edi_date: NULL,
                'billing_status' => isset($request->billing_status) && $request->billing_status != "NULL" ? $request->billing_status: NULL,
                'total_insurance_balance' => isset($request->total_insurance_balance) && $request->total_insurance_balance != "NULL" ? $request->total_insurance_balance: NULL,
                ];
 
             $duplicateRecordExisting  =  RhAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                RhAr::insert([
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,                
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id: NULL,
                    'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance: NULL,
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider: NULL,
                    'office' => isset($request->office) && $request->office != "NULL" ? $request->office: NULL,
                    'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed: NULL,
                    'first_edi_date' => isset($request->first_edi_date) && $request->first_edi_date != "NULL" ? $request->first_edi_date: NULL,
                    'last_edi_date' => isset($request->last_edi_date) && $request->last_edi_date != "NULL" ? $request->last_edi_date: NULL,
                    'billing_status' => isset($request->billing_status) && $request->billing_status != "NULL" ? $request->billing_status: NULL,
                    'total_insurance_balance' => isset($request->total_insurance_balance) && $request->total_insurance_balance != "NULL" ? $request->total_insurance_balance: NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  RhAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,                
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id: NULL,
                        'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance: NULL,
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider: NULL,
                        'office' => isset($request->office) && $request->office != "NULL" ? $request->office: NULL,
                        'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed: NULL,
                        'first_edi_date' => isset($request->first_edi_date) && $request->first_edi_date != "NULL" ? $request->first_edi_date: NULL,
                        'last_edi_date' => isset($request->last_edi_date) && $request->last_edi_date != "NULL" ? $request->last_edi_date: NULL,
                        'billing_status' => isset($request->billing_status) && $request->billing_status != "NULL" ? $request->billing_status: NULL,
                        'total_insurance_balance' => isset($request->total_insurance_balance) && $request->total_insurance_balance != "NULL" ? $request->total_insurance_balance: NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function restorationHealthcareArDuplicates(Request $request)
     {
         try {
            
            RhArDuplicates::insert([
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,                
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id: NULL,
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance: NULL,
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider: NULL,
                'office' => isset($request->office) && $request->office != "NULL" ? $request->office: NULL,
                'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed: NULL,
                'first_edi_date' => isset($request->first_edi_date) && $request->first_edi_date != "NULL" ? $request->first_edi_date: NULL,
                'last_edi_date' => isset($request->last_edi_date) && $request->last_edi_date != "NULL" ? $request->last_edi_date: NULL,
                'billing_status' => isset($request->billing_status) && $request->billing_status != "NULL" ? $request->billing_status: NULL,
                'total_insurance_balance' => isset($request->total_insurance_balance) && $request->total_insurance_balance != "NULL" ? $request->total_insurance_balance: NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function advancedMedicalBillingCollectionsAmbcAr(Request $request)
     {
         try {
             $attributes = [
                'encounter_id' => isset($request->encounter_id) && $request->encounter_id != "NULL" ? $request->encounter_id : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'patient_date_of_birth' => isset($request->patient_date_of_birth) && $request->patient_date_of_birth != "NULL" ? $request->patient_date_of_birth : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'billing_provider' => isset($request->billing_provider) && $request->billing_provider != "NULL" ? $request->billing_provider : NULL,  
                'payer_id' => isset($request->payer_id) && $request->payer_id != "NULL" ? $request->payer_id : NULL,  
                'policy_id' => isset($request->policy_id) && $request->policy_id != "NULL" ? $request->policy_id : NULL,  
                ];
 
             $duplicateRecordExisting  =  AmbcAmbcAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                AmbcAmbcAr::insert([
                    'encounter_id' => isset($request->encounter_id) && $request->encounter_id != "NULL" ? $request->encounter_id : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'patient_date_of_birth' => isset($request->patient_date_of_birth) && $request->patient_date_of_birth != "NULL" ? $request->patient_date_of_birth : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'billing_provider' => isset($request->billing_provider) && $request->billing_provider != "NULL" ? $request->billing_provider : NULL,  
                    'payer_id' => isset($request->payer_id) && $request->payer_id != "NULL" ? $request->payer_id : NULL,  
                    'policy_id' => isset($request->policy_id) && $request->policy_id != "NULL" ? $request->policy_id : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  AmbcAmbcAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'encounter_id' => isset($request->encounter_id) && $request->encounter_id != "NULL" ? $request->encounter_id : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'patient_date_of_birth' => isset($request->patient_date_of_birth) && $request->patient_date_of_birth != "NULL" ? $request->patient_date_of_birth : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'billing_provider' => isset($request->billing_provider) && $request->billing_provider != "NULL" ? $request->billing_provider : NULL,  
                        'payer_id' => isset($request->payer_id) && $request->payer_id != "NULL" ? $request->payer_id : NULL,  
                        'policy_id' => isset($request->policy_id) && $request->policy_id != "NULL" ? $request->policy_id : NULL,  
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function advancedMedicalBillingCollectionsAmbcArDuplicates(Request $request)
     {
         try {
            AmbcAmbcArDuplicates::insert([
                'encounter_id' => isset($request->encounter_id) && $request->encounter_id != "NULL" ? $request->encounter_id : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'patient_date_of_birth' => isset($request->patient_date_of_birth) && $request->patient_date_of_birth != "NULL" ? $request->patient_date_of_birth : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'billing_provider' => isset($request->billing_provider) && $request->billing_provider != "NULL" ? $request->billing_provider : NULL,  
                'payer_id' => isset($request->payer_id) && $request->payer_id != "NULL" ? $request->payer_id : NULL,  
                'policy_id' => isset($request->policy_id) && $request->policy_id != "NULL" ? $request->policy_id : NULL,  
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }

     public function hockanumValleyCommunityCouncilAr(Request $request)
     {
         try {
             $attributes = [
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                    'ar_balance' => isset($request->ar_balance) && $request->ar_balance != "NULL" ? $request->ar_balance : NULL,  
                ];
 
             $duplicateRecordExisting  =  HvccAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                HvccAr::insert([
                    'rendering_provider' => isset($request->rendering_provider) && $request->rendering_provider != "NULL" ? $request->rendering_provider : NULL,
                    'enc_id' => isset($request->enc_id) && $request->enc_id != "NULL" ? $request->enc_id : NULL,
                    'post_date' => isset($request->post_date) && $request->post_date != "NULL" ? $request->post_date : NULL,  
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL,  
                    'modifier' => isset($request->modifier) && $request->modifier != "NULL" ? $request->modifier : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                    'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,  
                    'payment' => isset($request->payment) && $request->payment != "NULL" ? $request->payment : NULL,  
                    'ar_balance' => isset($request->ar_balance) && $request->ar_balance != "NULL" ? $request->ar_balance : NULL,  
                    'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  HvccAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'rendering_provider' => isset($request->rendering_provider) && $request->rendering_provider != "NULL" ? $request->rendering_provider : NULL,
                        'enc_id' => isset($request->enc_id) && $request->enc_id != "NULL" ? $request->enc_id : NULL,
                        'post_date' => isset($request->post_date) && $request->post_date != "NULL" ? $request->post_date : NULL,  
                        'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL,  
                        'modifier' => isset($request->modifier) && $request->modifier != "NULL" ? $request->modifier : NULL,  
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                        'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,  
                        'payment' => isset($request->payment) && $request->payment != "NULL" ? $request->payment : NULL,  
                        'ar_balance' => isset($request->ar_balance) && $request->ar_balance != "NULL" ? $request->ar_balance : NULL,  
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,  
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                     ]);
                 }
                 return response()->json(['message' => 'Existing Record Updated Successfully']);
             }
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
     public function hockanumValleyCommunityCouncilArDuplicates(Request $request)
     {
         try {
            HvccArDuplicates::insert([
                'rendering_provider' => isset($request->rendering_provider) && $request->rendering_provider != "NULL" ? $request->rendering_provider : NULL,
                'enc_id' => isset($request->enc_id) && $request->enc_id != "NULL" ? $request->enc_id : NULL,
                'post_date' => isset($request->post_date) && $request->post_date != "NULL" ? $request->post_date : NULL,  
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL,  
                'modifier' => isset($request->modifier) && $request->modifier != "NULL" ? $request->modifier : NULL,  
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,  
                'payment' => isset($request->payment) && $request->payment != "NULL" ? $request->payment : NULL,  
                'ar_balance' => isset($request->ar_balance) && $request->ar_balance != "NULL" ? $request->ar_balance : NULL,  
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,  
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
             ]);
             return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
         } catch (\Exception $e) {
             $e->getMessage();
         }
     }
}
