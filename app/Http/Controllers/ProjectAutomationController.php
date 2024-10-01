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
use App\Models\OmsiProject;
use App\Models\OmsiProjectDuplicates;
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
            ];
            $whereAttributes = [
                'project_id' => isset($request->project_id) ? $request->project_id : NULL,
                'sub_project_id' => isset($request->sub_project_id) && $request->sub_project_id != "NULL" ? $request->sub_project_id : NULL,
                'file_name' => isset($request->file_name) ? $request->file_name : NULL
            ];
            $exists = InventoryExeFile::where($whereAttributes)->whereDate('exe_date', now()->format('Y-m-d'))->exists();
            if (!$exists) {
                InventoryExeFile::create($attributes);
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
                    $currentCount = $modelClass::where('invoke_date', $currentDate)->where('chart_status', 'CE_Assigned')->count();
                    $duplicateCount = $modelClassDuplicate::where('invoke_date', $currentDate)->where('chart_status', 'CE_Assigned')->count();
                    $assignedCount = $modelClass::where('invoke_date', $currentDate)->where('chart_status', 'CE_Assigned')->whereNotNull('CE_emp_id')->count();
                    $unAssignedCount = $modelClass::where('invoke_date', $currentDate)->where('chart_status', 'CE_Assigned')->whereNull('CE_emp_id')->count();
                }
                $procodeProjectsCurrent = [];
                Log::info($prjoectName . " count is " . $currentCount);
                if ($currentCount > 0) {
                    $procodeProjectsCurrent['project'] = $prjoectName;
                    $procodeProjectsCurrent['currentCount'] = $currentCount;
                    $procodeProjectsCurrent['duplicateCount'] = $duplicateCount;
                    $procodeProjectsCurrent['assignedCount'] = $assignedCount;
                    $procodeProjectsCurrent['unAssignedCount'] = $unAssignedCount;
                    // $toMail = CCEmailIds::select('cc_emails')->where('cc_module', 'inventory exe file to mail id')->first();
                    // $toMailId = explode(",", $toMail->cc_emails);
                    $toMailId = "mgani@caliberfocus.com";
                    $ccMailId = "vijayalaxmi@caliberfocus.com";
                    // $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'inventory exe file')->first();
                    // $ccMailId = explode(",", $ccMail->cc_emails);

                    $mailDate = Carbon::now()->format('m/d/Y');
                    $mailHeader = $prjoectName . " - Inventory Upload Successful - " . $mailDate;
                    $project_information["project_id"] = $attributes["project_id"];
                    $project_information["sub_project_id"] = $attributes["sub_project_id"];
                    $project_information["error_description"] = "Default Assigned Count: " . $procodeProjectsCurrent['assignedCount'] . PHP_EOL . " Inventory Uploaded Time: " . now()->format('m/d/Y g:i A');
                    $project_information["error_status_code"] = 200;
                    $project_information["error_date"] = now()->format('Y-m-d H:i:s');
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
            } else {
                return response()->json(['message' => 'Inventory File already exists']);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    // Resolv Details

    public function onpoint(Request $request)
    {
        try {
            $attributes = [
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
            ];

            $duplicateRecordExisting  =  OmsiProject::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                    OmsiProject::insert([
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
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  OmsiProject::where($attributes)->where('chart_status',"CE_Assigned")->first();
                $duplicateRecord->update([
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
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                ]);
                return response()->json(['message' => 'Yesterday Record Updated Successfully']);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function onpointDuplicates(Request $request)
    {
        try {
            OmsiProjectDuplicates::insert([
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
                return response()->json(['message' => 'Yesterday Record Updated Successfully']);
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
                return response()->json(['message' => 'Yesterday Record Updated Successfully']);
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
                'aging' => isset($request->aging) && $request->aging != "NULL" ? $request->aging : NULL,
                'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
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
                        'aging' => isset($request->aging) && $request->aging != "NULL" ? $request->aging : NULL,
                        'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
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
                $duplicateRecord->update([
                        'trans_id' => isset($request->trans_id) && $request->trans_id != "NULL" ? $request->trans_id : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'aging' => isset($request->aging) && $request->aging != "NULL" ? $request->aging : NULL,
                        'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
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
                return response()->json(['message' => 'Yesterday Record Updated Successfully']);
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
                'aging' => isset($request->aging) && $request->aging != "NULL" ? $request->aging : NULL,
                'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
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
                return response()->json(['message' => 'Yesterday Record Updated Successfully']);
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
                return response()->json(['message' => 'Yesterday Record Updated Successfully']);
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




}
