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
                'chart_status' => "CE_Assigned",
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
                $duplicateRecord  =  OmsiProject::where($attributes)->first();
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
                'chart_status' => "CE_Assigned",
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
                $duplicateRecord  =  NuAr::where($attributes)->first();
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
                'service_date' => isset($request->service_date) && $request->service_date != "NULL" ? $request->service_date : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'aging' => isset($request->aging) && $request->aging != "NULL" ? $request->aging : NULL,
                'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                'notes' => isset($request->notes) && $request->notes != "NULL" ? $request->notes : NULL,
                'chart_status' => "CE_Assigned",
            ];

            $duplicateRecordExisting  =  ChsiAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                ChsiAr::insert([
                        'claims_no' => isset($request->claims_no) && $request->claims_no != "NULL" ? $request->claims_no : NULL,
                        'service_date' => isset($request->service_date) && $request->service_date != "NULL" ? $request->service_date : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'aging' => isset($request->aging) && $request->aging != "NULL" ? $request->aging : NULL,
                        'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                        'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                        'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                        'notes' => isset($request->notes) && $request->notes != "NULL" ? $request->notes : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  ChsiAr::where($attributes)->first();
                $duplicateRecord->update([
                        'claims_no' => isset($request->claims_no) && $request->claims_no != "NULL" ? $request->claims_no : NULL,
                        'service_date' => isset($request->service_date) && $request->service_date != "NULL" ? $request->service_date : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'aging' => isset($request->aging) && $request->aging != "NULL" ? $request->aging : NULL,
                        'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                        'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                        'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                        'notes' => isset($request->notes) && $request->notes != "NULL" ? $request->notes : NULL,
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
                'service_date' => isset($request->service_date) && $request->service_date != "NULL" ? $request->service_date : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'aging' => isset($request->aging) && $request->aging != "NULL" ? $request->aging : NULL,
                'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'visit_type' => isset($request->visit_type) && $request->visit_type != "NULL" ? $request->visit_type : NULL,
                'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                'notes' => isset($request->notes) && $request->notes != "NULL" ? $request->notes : NULL,
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
    public function MillenniumHealthAr(Request $request)
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
                'fc' => isset($request->fc) && $request->fc != "NULL" ? $request->fc : NULL,
                'chart_status' => "CE_Assigned",
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
                $duplicateRecord  =  MhawAr::where($attributes)->first();
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
    public function MillenniumHealthArDuplicates(Request $request)
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
}
