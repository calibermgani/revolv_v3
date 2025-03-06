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
use App\Models\AcrmcAr;
use App\Models\AcrmcArDuplicates;
use App\Models\LastsAr;
use App\Models\LastsArDuplicates;
use App\Models\MecoAr;
use App\Models\MecoArDuplicates;
use App\Models\MosiDrElsamad;
use App\Models\MosiDrElsamadDuplicates;
use App\Models\IfwAr;
use App\Models\IfwArDuplicates;
use App\Models\MbjsclMbjHst;
use App\Models\MbjsclMbjHstDuplicates;
use App\Models\MbjsclMbjModmed;
use App\Models\MbjsclMbjModmedDuplicates;
use App\Models\OhAr;
use App\Models\OhArDuplicates;
use App\Models\NmbAr;
use App\Models\NmbArDuplicates;
use App\Models\NbAr;
use App\Models\NbArDuplicates;
use App\Models\WbrAr;
use App\Models\WbrArDuplicates;
use App\Models\PhAr;
use App\Models\PhArDuplicates;
use App\Models\PbhgAr;
use App\Models\PbhgArDuplicates;
use App\Models\ViAr;
use App\Models\ViArDuplicates;
use App\Models\SegAr;
use App\Models\SegArDuplicates;
use App\Models\PbcslAr;
use App\Models\PbcslArDuplicates;
use App\Models\SmhcAr;
use App\Models\SmhcArDuplicates;
use App\Models\TqhsAr;
use App\Models\TqhsArDuplicates;
use App\Models\BecAr;
use App\Models\BecArDuplicates;
use App\Models\RocAr;
use App\Models\RocArDuplicates;
use App\Models\SbgmgEligibilityVerification;
use App\Models\SbgmgEligibilityVerificationDuplicates;
use App\Models\PbhgEligibilityVerification;
use App\Models\PbhgEligibilityVerificationDuplicates;
use App\Models\MsEligibilityVerification;
use App\Models\MsEligibilityVerificationDuplicates;
use App\Models\SmbArEvolution;
use App\Models\SmbArEvolutionDuplicates;
use App\Models\SmbArProactive;
use App\Models\SmbArProactiveDuplicates;
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
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'fc' => isset($request->fc) && $request->fc != "NULL" ? $request->fc : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'cpt_modifier' => isset($request->cpt_modifier) && $request->cpt_modifier != "NULL" ? $request->cpt_modifier : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'previous_payment' => isset($request->previous_payment) && $request->previous_payment != "NULL" ? $request->previous_payment : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'diagnosis' => isset($request->diagnosis) && $request->diagnosis != "NULL" ? $request->diagnosis : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                 'invoke_date' => carbon::now()->format('Y-m-d')
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
                 'invoke_date' => carbon::now()->format('Y-m-d')               
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
             'invoke_date' => carbon::now()->format('Y-m-d')           
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


     public function NeurologyAssociatesARDuplicates(Request $request) {
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
                 'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
                 'invoke_date' => carbon::now()->format('Y-m-d')               
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
                 'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL,
                 'invoke_date' => carbon::now()->format('Y-m-d')
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
                //  'patient_acct_no' => isset($request->patient_acct_no) && $request->patient_acct_no != "NULL" ? $request->patient_acct_no : NULL,
                //  'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
               //  'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 //'rendering_provider' => isset($request->rendering_provider) && $request->rendering_provider != "NULL" ? $request->rendering_provider : NULL,
                 'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
              //   'ins_mem_id' => isset($request->ins_mem_id) && $request->ins_mem_id != "NULL" ? $request->ins_mem_id : NULL,
               //  'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL,
                 'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                 'invoke_date' => carbon::now()->format('Y-m-d')   
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
                 'invoke_date' => carbon::now()->format('Y-m-d')
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
                 'invoke_date' => carbon::now()->format('Y-m-d')
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
                 'invoke_date' => carbon::now()->format('Y-m-d')
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
                 'invoke_date' => carbon::now()->format('Y-m-d')
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
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                    'invoke_date' => carbon::now()->format('Y-m-d')
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
                'invoke_date' => carbon::now()->format('Y-m-d') 
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
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name: NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'invoke_date' => carbon::now()->format('Y-m-d')
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

     public function RetinaNorthwestAR(Request $request) {
         try {
             $attributes = [
                'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,                
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance: NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility: NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                'invoke_date' => carbon::now()->format('Y-m-d')
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
                  //  'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                    'ar_balance' => isset($request->ar_balance) && $request->ar_balance != "NULL" ? $request->ar_balance : NULL, 
                    'invoke_date' => carbon::now()->format('Y-m-d') 
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
     public function adamsCountyRegionalMedicalCenterAr(Request $request)
     {
         try {
             $attributes = [
                    'f_else_c' => isset($request->f_else_c) && $request->f_else_c != "NULL" ? $request->f_else_c : NULL,  
                    'provider_no' => isset($request->provider_no) && $request->provider_no != "NULL" ? $request->provider_no : NULL,  
                    'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                    'patient_no' => isset($request->patient_no) && $request->patient_no != "NULL" ? $request->patient_no : NULL,  
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'ins_co_else_plan' => isset($request->ins_co_else_plan) && $request->ins_co_else_plan != "NULL" ? $request->ins_co_else_plan : NULL,  
                    'derived_01' => isset($request->derived_01) && $request->derived_01 != "NULL" ? $request->derived_01 : NULL,  
                    'a_else_r_last_payment_amount' => isset($request->a_else_r_last_payment_amount) && $request->a_else_r_last_payment_amount != "NULL" ? $request->a_else_r_last_payment_amount : NULL,  
                    'original_balance' => isset($request->original_balance) && $request->original_balance != "NULL" ? $request->original_balance : NULL,  
                    'inhouse' => isset($request->inhouse) && $request->inhouse != "NULL" ? $request->inhouse : NULL,  
                    'current_balance' => isset($request->current_balance) && $request->current_balance != "NULL" ? $request->current_balance : NULL,
                    'invoke_date' => carbon::now()->format('Y-m-d')
                ];
 
             $duplicateRecordExisting  =  AcrmcAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                AcrmcAr::insert([
                    'f_else_c' => isset($request->f_else_c) && $request->f_else_c != "NULL" ? $request->f_else_c : NULL,  
                    'provider_no' => isset($request->provider_no) && $request->provider_no != "NULL" ? $request->provider_no : NULL,  
                    'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                    'patient_no' => isset($request->patient_no) && $request->patient_no != "NULL" ? $request->patient_no : NULL,  
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'ins_co_else_plan' => isset($request->ins_co_else_plan) && $request->ins_co_else_plan != "NULL" ? $request->ins_co_else_plan : NULL,  
                    'derived_01' => isset($request->derived_01) && $request->derived_01 != "NULL" ? $request->derived_01 : NULL,  
                    'a_else_r_last_payment_amount' => isset($request->a_else_r_last_payment_amount) && $request->a_else_r_last_payment_amount != "NULL" ? $request->a_else_r_last_payment_amount : NULL,  
                    'original_balance' => isset($request->original_balance) && $request->original_balance != "NULL" ? $request->original_balance : NULL,  
                    'inhouse' => isset($request->inhouse) && $request->inhouse != "NULL" ? $request->inhouse : NULL,  
                    'current_balance' => isset($request->current_balance) && $request->current_balance != "NULL" ? $request->current_balance : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  AcrmcAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'f_else_c' => isset($request->f_else_c) && $request->f_else_c != "NULL" ? $request->f_else_c : NULL,  
                        'provider_no' => isset($request->provider_no) && $request->provider_no != "NULL" ? $request->provider_no : NULL,  
                        'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                        'patient_no' => isset($request->patient_no) && $request->patient_no != "NULL" ? $request->patient_no : NULL,  
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'ins_co_else_plan' => isset($request->ins_co_else_plan) && $request->ins_co_else_plan != "NULL" ? $request->ins_co_else_plan : NULL,  
                        'derived_01' => isset($request->derived_01) && $request->derived_01 != "NULL" ? $request->derived_01 : NULL,  
                        'a_else_r_last_payment_amount' => isset($request->a_else_r_last_payment_amount) && $request->a_else_r_last_payment_amount != "NULL" ? $request->a_else_r_last_payment_amount : NULL,  
                        'original_balance' => isset($request->original_balance) && $request->original_balance != "NULL" ? $request->original_balance : NULL,  
                        'inhouse' => isset($request->inhouse) && $request->inhouse != "NULL" ? $request->inhouse : NULL,  
                        'current_balance' => isset($request->current_balance) && $request->current_balance != "NULL" ? $request->current_balance : NULL,  
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
     public function adamsCountyRegionalMedicalCenterArDuplicates(Request $request)
     {
         try {
            AcrmcArDuplicates::insert([
               'f_else_c' => isset($request->f_else_c) && $request->f_else_c != "NULL" ? $request->f_else_c : NULL,  
                'provider_no' => isset($request->provider_no) && $request->provider_no != "NULL" ? $request->provider_no : NULL,  
                'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                'patient_no' => isset($request->patient_no) && $request->patient_no != "NULL" ? $request->patient_no : NULL,  
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'ins_co_else_plan' => isset($request->ins_co_else_plan) && $request->ins_co_else_plan != "NULL" ? $request->ins_co_else_plan : NULL,  
                'derived_01' => isset($request->derived_01) && $request->derived_01 != "NULL" ? $request->derived_01 : NULL,  
                'a_else_r_last_payment_amount' => isset($request->a_else_r_last_payment_amount) && $request->a_else_r_last_payment_amount != "NULL" ? $request->a_else_r_last_payment_amount : NULL,  
                'original_balance' => isset($request->original_balance) && $request->original_balance != "NULL" ? $request->original_balance : NULL,  
                'inhouse' => isset($request->inhouse) && $request->inhouse != "NULL" ? $request->inhouse : NULL,  
                'current_balance' => isset($request->current_balance) && $request->current_balance != "NULL" ? $request->current_balance : NULL,  
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

     public function lynneAlbaSpeechTherapySolutionsAr(Request $request)
     {
         try {
             $attributes = [
                    'inv_no' => isset($request->inv_no) && $request->inv_no != "NULL" ? $request->inv_no : NULL,  
                    'inv_date' => isset($request->inv_date) && $request->inv_date != "NULL" ? $request->inv_date : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                    'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,  
                    'inv_total' => isset($request->inv_total) && $request->inv_total != "NULL" ? $request->inv_total : NULL,  
                    'ins_balance' => isset($request->ins_balance) && $request->ins_balance != "NULL" ? $request->ins_balance : NULL,
                    'invoke_date' => carbon::now()->format('Y-m-d')
                ];
 
             $duplicateRecordExisting  =  LastsAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                LastsAr::insert([
                    'inv_no' => isset($request->inv_no) && $request->inv_no != "NULL" ? $request->inv_no : NULL,  
                    'inv_date' => isset($request->inv_date) && $request->inv_date != "NULL" ? $request->inv_date : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                    'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,  
                    'inv_total' => isset($request->inv_total) && $request->inv_total != "NULL" ? $request->inv_total : NULL,  
                    'ins_balance' => isset($request->ins_balance) && $request->ins_balance != "NULL" ? $request->ins_balance : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  LastsAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'inv_no' => isset($request->inv_no) && $request->inv_no != "NULL" ? $request->inv_no : NULL,  
                        'inv_date' => isset($request->inv_date) && $request->inv_date != "NULL" ? $request->inv_date : NULL,  
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                        'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,  
                        'inv_total' => isset($request->inv_total) && $request->inv_total != "NULL" ? $request->inv_total : NULL,  
                        'ins_balance' => isset($request->ins_balance) && $request->ins_balance != "NULL" ? $request->ins_balance : NULL,  
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
     public function lynneAlbaSpeechTherapySolutionsArDuplicates(Request $request)
     {
         try {
            LastsArDuplicates::insert([
                'inv_no' => isset($request->inv_no) && $request->inv_no != "NULL" ? $request->inv_no : NULL,  
                'inv_date' => isset($request->inv_date) && $request->inv_date != "NULL" ? $request->inv_date : NULL,  
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,  
                'inv_total' => isset($request->inv_total) && $request->inv_total != "NULL" ? $request->inv_total : NULL,  
                'ins_balance' => isset($request->ins_balance) && $request->ins_balance != "NULL" ? $request->ins_balance : NULL,  
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


     // MarionEyeCenterOpticalAR

     public function MarionEyeCenterOpticalAR(Request $request)
     {
         try {
             $attributes = [
                    'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,  
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL,  
                    'invoke_date' => carbon::now()->format('Y-m-d')
                    
                ];
 
             $duplicateRecordExisting  =  MecoAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                MecoAr::insert([
                    'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,  
                    'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'total_billed' => isset($request->total_billed) && $request->total_billed != "NULL" ? $request->total_billed : NULL,  
                    'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL,
                    'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,
                    'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  MecoAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,  
                        'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'total_billed' => isset($request->total_billed) && $request->total_billed != "NULL" ? $request->total_billed : NULL,  
                        'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL,
                        'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,
                        'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,                    
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
     public function MarionEyeCenterOpticalARArDuplicates(Request $request)
     {
         try {
            MecoAr::insert([
                'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,  
                'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'total_billed' => isset($request->total_billed) && $request->total_billed != "NULL" ? $request->total_billed : NULL,  
                'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL,
                'payer_mix' => isset($request->payer_mix) && $request->payer_mix != "NULL" ? $request->payer_mix : NULL,
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,   
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


 // MedValue Offshore Solutions Inc.


     public function MedValueOffshoreSolutionsIncAR(Request $request) {
         try {
             $attributes = [
                    'follow_up_visit' => isset($request->follow_up_visit) && $request->follow_up_visit != "NULL" ? $request->follow_up_visit : NULL,  
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL,  
                    'plan_balance' => isset($request->plan_balance) && $request->plan_balance != "NULL" ? $request->plan_balance : NULL,  
                    'invoke_date' => carbon::now()->format('Y-m-d')
                    
                ];
 
             $duplicateRecordExisting  =  MosiDrElsamad::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                MosiDrElsamad::insert([
                    'follow_up_visit' => isset($request->follow_up_visit) && $request->follow_up_visit != "NULL" ? $request->follow_up_visit : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL,  
                    'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL,  
                    'group_name' => isset($request->group_name) && $request->group_name != "NULL" ? $request->group_name : NULL,
                    'denial_category' => isset($request->denial_category) && $request->denial_category != "NULL" ? $request->denial_category : NULL,
                    'reason' => isset($request->reason) && $request->reason != "NULL" ? $request->reason : NULL,
                    'remit_code' => isset($request->remit_code) && $request->remit_code != "NULL" ? $request->remit_code : NULL,
                    'plan_balance' => isset($request->plan_balance) && $request->plan_balance != "NULL" ? $request->plan_balance : NULL,
                    'submit_date' => isset($request->submit_date) && $request->submit_date != "NULL" ? $request->submit_date : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  MosiDrElsamad::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'follow_up_visit' => isset($request->follow_up_visit) && $request->follow_up_visit != "NULL" ? $request->follow_up_visit : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                        'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL,  
                        'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL,  
                        'group_name' => isset($request->group_name) && $request->group_name != "NULL" ? $request->group_name : NULL,
                        'denial_category' => isset($request->denial_category) && $request->denial_category != "NULL" ? $request->denial_category : NULL,
                        'reason' => isset($request->reason) && $request->reason != "NULL" ? $request->reason : NULL,
                        'remit_code' => isset($request->remit_code) && $request->remit_code != "NULL" ? $request->remit_code : NULL,
                        'plan_balance' => isset($request->plan_balance) && $request->plan_balance != "NULL" ? $request->plan_balance : NULL,
                        'submit_date' => isset($request->submit_date) && $request->submit_date != "NULL" ? $request->submit_date : NULL,                   
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
     public function MedValueOffshoreSolutionsIncArDuplicates(Request $request)
     {
         try {
            MosiDrElsamadDuplicates::insert([
                'follow_up_visit' => isset($request->follow_up_visit) && $request->follow_up_visit != "NULL" ? $request->follow_up_visit : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL,  
                'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL,  
                'group_name' => isset($request->group_name) && $request->group_name != "NULL" ? $request->group_name : NULL,
                'denial_category' => isset($request->denial_category) && $request->denial_category != "NULL" ? $request->denial_category : NULL,
                'reason' => isset($request->reason) && $request->reason != "NULL" ? $request->reason : NULL,
                'remit_code' => isset($request->remit_code) && $request->remit_code != "NULL" ? $request->remit_code : NULL,
                'plan_balance' => isset($request->plan_balance) && $request->plan_balance != "NULL" ? $request->plan_balance : NULL,
                'submit_date' => isset($request->submit_date) && $request->submit_date != "NULL" ? $request->submit_date : NULL,                    
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


     // Imaging for Women


     public function ImagingforWomenAR(Request $request)
     {
         try {
             $attributes = [
                    'resp_ins_plan_type' => isset($request->resp_ins_plan_type) && $request->resp_ins_plan_type != "NULL" ? $request->resp_ins_plan_type : NULL,  
                    'resp_ins_name' => isset($request->resp_ins_name) && $request->resp_ins_name != "NULL" ? $request->resp_ins_name : NULL,                      
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'follow_up_status' => isset($request->follow_up_status) && $request->follow_up_status != "NULL" ? $request->follow_up_status : NULL,  
                    'account_id' => isset($request->account_id) && $request->account_id != "NULL" ? $request->account_id : NULL,
                    'current' => isset($request->current) && $request->current != "NULL" ? $request->current : NULL,
                    '30_days' => isset($request->to_30_days) && $request->to_30_days != "NULL" ? $request->to_30_days : NULL,
                    '60_days' => isset($request->to_60_days) && $request->to_60_days != "NULL" ? $request->to_60_days : NULL,
                    '90_days' => isset($request->to_90_days) && $request->to_90_days != "NULL" ? $request->to_90_days : NULL,
                    '120_days' => isset($request->to_120_days) && $request->to_120_days != "NULL" ? $request->to_120_days : NULL,
                    '150_days' => isset($request->to_150_days) && $request->to_150_days != "NULL" ? $request->to_150_days : NULL,
                    'total' => isset($request->total) && $request->total != "NULL" ? $request->total : NULL,
                    '60_greater' => isset($request->greater_60) && $request->greater_60 != "NULL" ? $request->greater_60 : NULL,
                    'invoke_date' => carbon::now()->format('Y-m-d')
                    
                ];
 
             $duplicateRecordExisting  =  IfwAr::where($attributes)->exists();
             if (!$duplicateRecordExisting) {
                IfwAr::insert([
                    'resp_ins_plan_type' => isset($request->resp_ins_plan_type) && $request->resp_ins_plan_type != "NULL" ? $request->resp_ins_plan_type : NULL,  
                    'resp_ins_name' => isset($request->resp_ins_name) && $request->resp_ins_name != "NULL" ? $request->resp_ins_name : NULL,                      
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'follow_up_status' => isset($request->follow_up_status) && $request->follow_up_status != "NULL" ? $request->follow_up_status : NULL,  
                    'account_id' => isset($request->account_id) && $request->account_id != "NULL" ? $request->account_id : NULL,
                    'current' => isset($request->current) && $request->current != "NULL" ? $request->current : NULL,
                    '30_days' => isset($request->to_30_days) && $request->to_30_days != "NULL" ? $request->to_30_days : NULL,
                    '60_days' => isset($request->to_60_days) && $request->to_60_days != "NULL" ? $request->to_60_days : NULL,
                    '90_days' => isset($request->to_90_days) && $request->to_90_days != "NULL" ? $request->to_90_days : NULL,
                    '120_days' => isset($request->to_120_days) && $request->to_120_days != "NULL" ? $request->to_120_days : NULL,
                    '150_days' => isset($request->to_150_days) && $request->to_150_days != "NULL" ? $request->to_150_days : NULL,
                    'total' => isset($request->total) && $request->total != "NULL" ? $request->total : NULL,
                    '60_greater' => isset($request->greater_60) && $request->greater_60 != "NULL" ? $request->greater_60 : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                         return response()->json(['message' => 'Record Inserted Successfully']);
             } else {
                 $duplicateRecord  =  IfwAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                 if ($duplicateRecord) {
                     $duplicateRecord->update([
                        'resp_ins_plan_type' => isset($request->resp_ins_plan_type) && $request->resp_ins_plan_type != "NULL" ? $request->resp_ins_plan_type : NULL,  
                        'resp_ins_name' => isset($request->resp_ins_name) && $request->resp_ins_name != "NULL" ? $request->resp_ins_name : NULL,                         
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                        'follow_up_status' => isset($request->follow_up_status) && $request->follow_up_status != "NULL" ? $request->follow_up_status : NULL,  
                        'account_id' => isset($request->account_id) && $request->account_id != "NULL" ? $request->account_id : NULL,
                        'current' => isset($request->current) && $request->current != "NULL" ? $request->current : NULL,
                        '30_days' => isset($request->to_30_days) && $request->to_30_days != "NULL" ? $request->to_30_days : NULL,
                        '60_days' => isset($request->to_60_days) && $request->to_60_days != "NULL" ? $request->to_60_days : NULL,
                        '90_days' => isset($request->to_90_days) && $request->to_90_days != "NULL" ? $request->to_90_days : NULL,
                        '120_days' => isset($request->to_120_days) && $request->to_120_days != "NULL" ? $request->to_120_days : NULL,
                        '150_days' => isset($request->to_150_days) && $request->to_150_days != "NULL" ? $request->to_150_days : NULL,
                        'total' => isset($request->total) && $request->total != "NULL" ? $request->total : NULL,
                        '60_greater' => isset($request->greater_60) && $request->greater_60 != "NULL" ? $request->greater_60 : NULL,                 
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
     public function ImagingforWomenArDuplicates(Request $request)
     {
         try {
            IfwArDuplicates::insert([
                'resp_ins_plan_type' => isset($request->resp_ins_plan_type) && $request->resp_ins_plan_type != "NULL" ? $request->resp_ins_plan_type : NULL,  
                'resp_ins_name' => isset($request->resp_ins_name) && $request->resp_ins_name != "NULL" ? $request->resp_ins_name : NULL,                  
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                'follow_up_status' => isset($request->follow_up_status) && $request->follow_up_status != "NULL" ? $request->follow_up_status : NULL,  
                'account_id' => isset($request->account_id) && $request->account_id != "NULL" ? $request->account_id : NULL,
                'current' => isset($request->current) && $request->current != "NULL" ? $request->current : NULL,
                '30_days' => isset($request->to_30_days) && $request->to_30_days != "NULL" ? $request->to_30_days : NULL,
                '60_days' => isset($request->to_60_days) && $request->to_60_days != "NULL" ? $request->to_60_days : NULL,
                '90_days' => isset($request->to_90_days) && $request->to_90_days != "NULL" ? $request->to_90_days : NULL,
                '120_days' => isset($request->to_120_days) && $request->to_120_days != "NULL" ? $request->to_120_days : NULL,
                '150_days' => isset($request->to_150_days) && $request->to_150_days != "NULL" ? $request->to_150_days : NULL,
                'total' => isset($request->total) && $request->total != "NULL" ? $request->total : NULL,
                '60_greater' => isset($request->greater_60) && $request->greater_60 != "NULL" ? $request->greater_60 : NULL,                   
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



// Missoula Bone & Joint Surgery Center, LLC


public function MissoulaBoneANDJointSurgeryCenterLLCAR(Request $request)
{
    try {
        $attributes = [
               'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
               'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,                      
               'ptid_vst_no' => isset($request->ptid_vst_no) && $request->ptid_vst_no != "NULL" ? $request->ptid_vst_no : NULL,  
               'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
               'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,
               'balance_due' => isset($request->balance_due) && $request->balance_due != "NULL" ? $request->balance_due : NULL,
              'invoke_date' => carbon::now()->format('Y-m-d')               
           ];           

        $duplicateRecordExisting  =  MbjsclMbjHst::where($attributes)->exists();
        if (!$duplicateRecordExisting) {
            MbjsclMbjHst::insert([
               'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
               'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL, 
               'ptid_vst_no' => isset($request->ptid_vst_no) && $request->ptid_vst_no != "NULL" ? $request->ptid_vst_no : NULL,                      
               'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
               'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
               'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,
               'policy_no' => isset($request->policy_no) && $request->policy_no != "NULL" ? $request->policy_no : NULL,
               'balance_due' => isset($request->balance_due) && $request->balance_due != "NULL" ? $request->balance_due : NULL,               
               'invoke_date' => date('Y-m-d'),
               'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
               'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
               'chart_status' => "CE_Assigned",
                ]);
                    return response()->json(['message' => 'Record Inserted Successfully']);
        } else {
            $duplicateRecord  =  MbjsclMbjHst::where($attributes)->where('chart_status',"CE_Assigned")->first();
            if ($duplicateRecord) {
                $duplicateRecord->update([
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL, 
                    'ptid_vst_no' => isset($request->ptid_vst_no) && $request->ptid_vst_no != "NULL" ? $request->ptid_vst_no : NULL,                      
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
                    'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,
                    'policy_no' => isset($request->policy_no) && $request->policy_no != "NULL" ? $request->policy_no : NULL,
                    'balance_due' => isset($request->balance_due) && $request->balance_due != "NULL" ? $request->balance_due : NULL,                 
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
public function MissoulaBoneANDJointSurgeryCenterLLCArDuplicates(Request $request)
{
    try {
        MbjsclMbjHstDuplicates::insert([
            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
            'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL, 
            'ptid_vst_no' => isset($request->ptid_vst_no) && $request->ptid_vst_no != "NULL" ? $request->ptid_vst_no : NULL,                      
            'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
            'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
            'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,
            'policy_no' => isset($request->policy_no) && $request->policy_no != "NULL" ? $request->policy_no : NULL,
            'balance_due' => isset($request->balance_due) && $request->balance_due != "NULL" ? $request->balance_due : NULL,                     
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
// Missoula Bone & Joint Surgery Center, LLC Modmed
public function MissoulaBoneANDJointSurgeryCenterLLCModmedAR(Request $request)
{
    try {
        $attributes = [
               'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
               'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,                      
               'primary_provider' => isset($request->primary_provider) && $request->primary_provider != "NULL" ? $request->primary_provider : NULL,  
               'total_ar_aging' => isset($request->total_ar_aging) && $request->total_ar_aging != "NULL" ? $request->total_ar_aging : NULL,  
              // 'invoke_date' => carbon::now()->format('Y-m-d')           
           ];
           

        $duplicateRecordExisting  =  MbjsclMbjModmed::where($attributes)->exists();
        if (!$duplicateRecordExisting) {
            MbjsclMbjModmed::insert([
               'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
               'primary_provider' => isset($request->primary_provider) && $request->primary_provider != "NULL" ? $request->primary_provider : NULL, 
               'primary_provider_npi' => isset($request->primary_provider_npi) && $request->primary_provider_npi != "NULL" ? $request->primary_provider_npi : NULL,                      
               'responsible_party' => isset($request->responsible_party) && $request->responsible_party != "NULL" ? $request->responsible_party : NULL,  
               'bill_id' => isset($request->bill_id) && $request->bill_id != "NULL" ? $request->bill_id : NULL,  
               'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
               'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,
               'payer_policy_number' => isset($request->payer_policy_number) && $request->payer_policy_number != "NULL" ? $request->payer_policy_number : NULL,
               'total_ar_aging' => isset($request->total_ar_aging) && $request->total_ar_aging != "NULL" ? $request->total_ar_aging : NULL,               
               'invoke_date' => date('Y-m-d'),
               'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
               'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
               'chart_status' => "CE_Assigned",
                ]);
                    return response()->json(['message' => 'Record Inserted Successfully']);
        } else {
            $duplicateRecord  =  MbjsclMbjModmed::where($attributes)->where('chart_status',"CE_Assigned")->first();
            if ($duplicateRecord) {
                $duplicateRecord->update([
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'primary_provider' => isset($request->primary_provider) && $request->primary_provider != "NULL" ? $request->primary_provider : NULL, 
                    'primary_provider_npi' => isset($request->primary_provider_npi) && $request->primary_provider_npi != "NULL" ? $request->primary_provider_npi : NULL,                      
                    'responsible_party' => isset($request->responsible_party) && $request->responsible_party != "NULL" ? $request->responsible_party : NULL,  
                    'bill_id' => isset($request->bill_id) && $request->bill_id != "NULL" ? $request->bill_id : NULL,  
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,
                    'payer_policy_number' => isset($request->payer_policy_number) && $request->payer_policy_number != "NULL" ? $request->payer_policy_number : NULL,
                    'total_ar_aging' => isset($request->total_ar_aging) && $request->total_ar_aging != "NULL" ? $request->total_ar_aging : NULL,               
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                ]);
                return response()->json(['message' => 'Existing Record Updated Successfully']);
            } else {
                  MbjsclMbjModmed::insert([
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'primary_provider' => isset($request->primary_provider) && $request->primary_provider != "NULL" ? $request->primary_provider : NULL, 
                    'primary_provider_npi' => isset($request->primary_provider_npi) && $request->primary_provider_npi != "NULL" ? $request->primary_provider_npi : NULL,                      
                    'responsible_party' => isset($request->responsible_party) && $request->responsible_party != "NULL" ? $request->responsible_party : NULL,  
                    'bill_id' => isset($request->bill_id) && $request->bill_id != "NULL" ? $request->bill_id : NULL,  
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,
                    'payer_policy_number' => isset($request->payer_policy_number) && $request->payer_policy_number != "NULL" ? $request->payer_policy_number : NULL,
                    'total_ar_aging' => isset($request->total_ar_aging) && $request->total_ar_aging != "NULL" ? $request->total_ar_aging : NULL,               
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                     ]);
                     return response()->json(['message' => 'Existing Record reinserted Successfully']);
            }
           
        }
    } catch (\Exception $e) {
        $e->getMessage();
    }
}
public function MissoulaBoneANDJointSurgeryCenterLLCModmedArDuplicates(Request $request)
{
    try {
        MbjsclMbjModmedDuplicates::insert([
            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
            'primary_provider' => isset($request->primary_provider) && $request->primary_provider != "NULL" ? $request->primary_provider : NULL, 
            'primary_provider_npi' => isset($request->primary_provider_npi) && $request->primary_provider_npi != "NULL" ? $request->primary_provider_npi : NULL,                      
            'responsible_party' => isset($request->responsible_party) && $request->responsible_party != "NULL" ? $request->responsible_party : NULL,  
            'bill_id' => isset($request->bill_id) && $request->bill_id != "NULL" ? $request->bill_id : NULL,  
            'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
            'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,
            'payer_policy_number' => isset($request->payer_policy_number) && $request->payer_policy_number != "NULL" ? $request->payer_policy_number : NULL,
            'total_ar_aging' => isset($request->total_ar_aging) && $request->total_ar_aging != "NULL" ? $request->total_ar_aging : NULL,                      
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

public function omniHealthcareAr(Request $request)
{
    try {
        $attributes = [
               'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,  
               'patient_last_name' => isset($request->patient_last_name) && $request->patient_last_name != "NULL" ? $request->patient_last_name : NULL,                      
               'patient_first_name' => isset($request->patient_first_name) && $request->patient_first_name != "NULL" ? $request->patient_first_name : NULL,  
               'voucher_number' => isset($request->voucher_number) && $request->voucher_number != "NULL" ? $request->voucher_number : NULL,     
               'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,                            
               'vch_fees' => isset($request->vch_fees) && $request->vch_fees != "NULL" ? $request->vch_fees : NULL,     
               'carrier_name' => isset($request->carrier_name) && $request->carrier_name != "NULL" ? $request->carrier_name : NULL,     
               'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,     
               'actual_prov_abbr' => isset($request->actual_prov_abbr) && $request->actual_prov_abbr != "NULL" ? $request->actual_prov_abbr : NULL,     
               'carrier_phone' => isset($request->carrier_phone) && $request->carrier_phone != "NULL" ? $request->carrier_phone : NULL,
               'invoke_date' => carbon::now()->format('Y-m-d')
           ];
           

        $duplicateRecordExisting  =  OhAr::where($attributes)->exists();
        if (!$duplicateRecordExisting) {
            OhAr::insert([
               'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,  
               'patient_last_name' => isset($request->patient_last_name) && $request->patient_last_name != "NULL" ? $request->patient_last_name : NULL,                      
               'patient_first_name' => isset($request->patient_first_name) && $request->patient_first_name != "NULL" ? $request->patient_first_name : NULL,  
               'voucher_number' => isset($request->voucher_number) && $request->voucher_number != "NULL" ? $request->voucher_number : NULL,     
               'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,                            
               'vch_fees' => isset($request->vch_fees) && $request->vch_fees != "NULL" ? $request->vch_fees : NULL,     
               'carrier_name' => isset($request->carrier_name) && $request->carrier_name != "NULL" ? $request->carrier_name : NULL,     
               'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,     
               'actual_prov_abbr' => isset($request->actual_prov_abbr) && $request->actual_prov_abbr != "NULL" ? $request->actual_prov_abbr : NULL,     
               'carrier_phone' => isset($request->carrier_phone) && $request->carrier_phone != "NULL" ? $request->carrier_phone : NULL,         
               'invoke_date' => date('Y-m-d'),
               'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
               'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
               'chart_status' => "CE_Assigned",
                ]);
                    return response()->json(['message' => 'Record Inserted Successfully']);
        } else {
            $duplicateRecord  =  OhAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
            if ($duplicateRecord) {
                $duplicateRecord->update([
                    'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,  
                    'patient_last_name' => isset($request->patient_last_name) && $request->patient_last_name != "NULL" ? $request->patient_last_name : NULL,                      
                    'patient_first_name' => isset($request->patient_first_name) && $request->patient_first_name != "NULL" ? $request->patient_first_name : NULL,  
                    'voucher_number' => isset($request->voucher_number) && $request->voucher_number != "NULL" ? $request->voucher_number : NULL,     
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,                            
                    'vch_fees' => isset($request->vch_fees) && $request->vch_fees != "NULL" ? $request->vch_fees : NULL,     
                    'carrier_name' => isset($request->carrier_name) && $request->carrier_name != "NULL" ? $request->carrier_name : NULL,     
                    'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,     
                    'actual_prov_abbr' => isset($request->actual_prov_abbr) && $request->actual_prov_abbr != "NULL" ? $request->actual_prov_abbr : NULL,     
                    'carrier_phone' => isset($request->carrier_phone) && $request->carrier_phone != "NULL" ? $request->carrier_phone : NULL,               
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
public function omniHealthcareArDuplicates(Request $request)
{
    try {
        OhArDuplicates::insert([
            'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,  
            'patient_last_name' => isset($request->patient_last_name) && $request->patient_last_name != "NULL" ? $request->patient_last_name : NULL,                      
            'patient_first_name' => isset($request->patient_first_name) && $request->patient_first_name != "NULL" ? $request->patient_first_name : NULL,  
            'voucher_number' => isset($request->voucher_number) && $request->voucher_number != "NULL" ? $request->voucher_number : NULL,     
            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,                            
            'vch_fees' => isset($request->vch_fees) && $request->vch_fees != "NULL" ? $request->vch_fees : NULL,     
            'carrier_name' => isset($request->carrier_name) && $request->carrier_name != "NULL" ? $request->carrier_name : NULL,     
            'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,     
            'actual_prov_abbr' => isset($request->actual_prov_abbr) && $request->actual_prov_abbr != "NULL" ? $request->actual_prov_abbr : NULL,     
            'carrier_phone' => isset($request->carrier_phone) && $request->carrier_phone != "NULL" ? $request->carrier_phone : NULL,                      
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
// Nationwide Medical Billing
public function NationwideMedicalBillingAR(Request $request)
{
    try {
        $attributes = [
               'unique_code' => isset($request->unique_code) && $request->unique_code != "NULL" ? $request->unique_code : NULL,  
               'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,                      
               'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
               'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,               
               'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
               'encounter' => isset($request->encounter) && $request->encounter != "NULL" ? $request->encounter : NULL, 
               'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,
               'balance_amount' => isset($request->balance_amount) && $request->balance_amount != "NULL" ? $request->balance_amount : NULL,     
               'invoke_date' => carbon::now()->format('Y-m-d')        
               
           ];
           

        $duplicateRecordExisting  =  NmbAr::where($attributes)->exists();
        if (!$duplicateRecordExisting) {
            NmbAr::insert([
               'unique_code' => isset($request->unique_code) && $request->unique_code != "NULL" ? $request->unique_code : NULL,  
               'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL, 
               'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,                      
               'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,  
               'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
               'encounter' => isset($request->encounter) && $request->encounter != "NULL" ? $request->encounter : NULL,  
               'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,
               'balance_amount' => isset($request->balance_amount) && $request->balance_amount != "NULL" ? $request->balance_amount : NULL,               
               'invoke_date' => date('Y-m-d'),
               'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
               'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
               'chart_status' => "CE_Assigned",
                ]);
                    return response()->json(['message' => 'Record Inserted Successfully']);
        } else {
            $duplicateRecord  =  NmbAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
            if ($duplicateRecord) {
                $duplicateRecord->update([
                    'unique_code' => isset($request->unique_code) && $request->unique_code != "NULL" ? $request->unique_code : NULL,  
                    'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL, 
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,                      
                    'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'encounter' => isset($request->encounter) && $request->encounter != "NULL" ? $request->encounter : NULL,  
                    'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,
                    'balance_amount' => isset($request->balance_amount) && $request->balance_amount != "NULL" ? $request->balance_amount : NULL,               
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                ]);
            } else {
                $duplicateRecordCheckByDate  =  NmbAr::where($attributes)->where('invoke_date',carbon::now()->format('Y-m-d'))->exists();
                if (!$duplicateRecordCheckByDate) {
                    NmbAr::insert([
                       'unique_code' => isset($request->unique_code) && $request->unique_code != "NULL" ? $request->unique_code : NULL,  
                       'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL, 
                       'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,                      
                       'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,  
                       'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                       'encounter' => isset($request->encounter) && $request->encounter != "NULL" ? $request->encounter : NULL,  
                       'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,
                       'balance_amount' => isset($request->balance_amount) && $request->balance_amount != "NULL" ? $request->balance_amount : NULL,               
                       'invoke_date' => date('Y-m-d'),
                       'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                       'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                       'chart_status' => "CE_Assigned",
                        ]);
                            return response()->json(['message' => 'Record Inserted Successfully']);
                }
            }
            return response()->json(['message' => 'Existing Record Updated Successfully']);
        }
    } catch (\Exception $e) {
        $e->getMessage();
    }
}
public function NationwideMedicalBillingArDuplicates(Request $request)
{
    try {
        NmbArDuplicates::insert([
            'unique_code' => isset($request->unique_code) && $request->unique_code != "NULL" ? $request->unique_code : NULL,  
            'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL, 
            'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,                      
            'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,  
            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
            'encounter' => isset($request->encounter) && $request->encounter != "NULL" ? $request->encounter : NULL,  
            'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,
            'balance_amount' => isset($request->balance_amount) && $request->balance_amount != "NULL" ? $request->balance_amount : NULL,                      
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

// NexTrust Billing
public function NexTrustBillingAR(Request $request)
{
    try {
        $attributes = [
               'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
               'patient_no' => isset($request->patient_no) && $request->patient_no != "NULL" ? $request->patient_no : NULL,                                             
               'invoice_no' => isset($request->invoice_no) && $request->invoice_no != "NULL" ? $request->invoice_no : NULL,  
               'invoke_date' => carbon::now()->format('Y-m-d')               
           ];
           

        $duplicateRecordExisting  =  NbAr::where($attributes)->exists();
        if (!$duplicateRecordExisting) {
            NbAr::insert([
               'invoice_no' => isset($request->invoice_no) && $request->invoice_no != "NULL" ? $request->invoice_no : NULL,  
               'invoice_date' => isset($request->invoice_date) && $request->invoice_date != "NULL" ? $request->invoice_date : NULL, 
               'service_date' => isset($request->service_date) && $request->service_date != "NULL" ? $request->service_date : NULL,                      
               'total_amount' => isset($request->total_amount) && $request->total_amount != "NULL" ? $request->total_amount : NULL,  
               'balance_amount' => isset($request->balance_amount) && $request->balance_amount != "NULL" ? $request->balance_amount : NULL,  
               'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,  
               'patient_no' => isset($request->patient_no) && $request->patient_no != "NULL" ? $request->patient_no : NULL,
               'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,               
               'ins_name' => isset($request->ins_name) && $request->ins_name != "NULL" ? $request->ins_name : NULL,
               'ins_priority' => isset($request->ins_priority) && $request->ins_priority != "NULL" ? $request->ins_priority : NULL,
               'invoke_date' => date('Y-m-d'),
               'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
               'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
               'chart_status' => "CE_Assigned",
                ]);
                    return response()->json(['message' => 'Record Inserted Successfully']);
        } else {
            $duplicateRecord  =  NbAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
            if ($duplicateRecord) {
                $duplicateRecord->update([
                    'invoice_no' => isset($request->invoice_no) && $request->invoice_no != "NULL" ? $request->invoice_no : NULL,  
                    'invoice_date' => isset($request->invoice_date) && $request->invoice_date != "NULL" ? $request->invoice_date : NULL, 
                    'service_date' => isset($request->service_date) && $request->service_date != "NULL" ? $request->service_date : NULL,                      
                    'total_amount' => isset($request->total_amount) && $request->total_amount != "NULL" ? $request->total_amount : NULL,  
                    'balance_amount' => isset($request->balance_amount) && $request->balance_amount != "NULL" ? $request->balance_amount : NULL,  
                    'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,  
                    'patient_no' => isset($request->patient_no) && $request->patient_no != "NULL" ? $request->patient_no : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,               
                    'ins_name' => isset($request->ins_name) && $request->ins_name != "NULL" ? $request->ins_name : NULL,
                    'ins_priority' => isset($request->ins_priority) && $request->ins_priority != "NULL" ? $request->ins_priority : NULL,               
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
public function NexTrustBillingArDuplicates(Request $request)
{
    try {
        NbArDuplicates::insert([
            'invoice_no' => isset($request->invoice_no) && $request->invoice_no != "NULL" ? $request->invoice_no : NULL,  
            'invoice_date' => isset($request->invoice_date) && $request->invoice_date != "NULL" ? $request->invoice_date : NULL, 
            'service_date' => isset($request->service_date) && $request->service_date != "NULL" ? $request->service_date : NULL,                      
            'total_amount' => isset($request->total_amount) && $request->total_amount != "NULL" ? $request->total_amount : NULL,  
            'balance_amount' => isset($request->balance_amount) && $request->balance_amount != "NULL" ? $request->balance_amount : NULL,  
            'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,  
            'patient_no' => isset($request->patient_no) && $request->patient_no != "NULL" ? $request->patient_no : NULL,
            'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,               
            'ins_name' => isset($request->ins_name) && $request->ins_name != "NULL" ? $request->ins_name : NULL,
            'ins_priority' => isset($request->ins_priority) && $request->ins_priority != "NULL" ? $request->ins_priority : NULL,                   
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


    public function williamBeeRirieAR(Request $request)
    {
        try {
            $attributes = [
                'account_num' => isset($request->account_num) && $request->account_num != "NULL" ? $request->account_num : NULL,  
                'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL, 
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,  
                'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,  
               // 'responsible_provider' => isset($request->responsible_provider) && $request->responsible_provider != "NULL" ? $request->responsible_provider : NULL,  
                'reg_date' => isset($request->reg_date) && $request->reg_date != "NULL" ? $request->reg_date : NULL,  
            //    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')        
            ];           

            $duplicateRecordExisting  =  WbrAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                WbrAr::insert([
                'account_num' => isset($request->account_num) && $request->account_num != "NULL" ? $request->account_num : NULL,  
                'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL, 
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,  
                    'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,  
                'responsible_provider' => isset($request->responsible_provider) && $request->responsible_provider != "NULL" ? $request->responsible_provider : NULL,  
                'reg_date' => isset($request->reg_date) && $request->reg_date != "NULL" ? $request->reg_date : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,   
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  WbrAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'account_num' => isset($request->account_num) && $request->account_num != "NULL" ? $request->account_num : NULL,  
                        'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL, 
                        'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,  
                        'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,  
                        'responsible_provider' => isset($request->responsible_provider) && $request->responsible_provider != "NULL" ? $request->responsible_provider : NULL,  
                        'reg_date' => isset($request->reg_date) && $request->reg_date != "NULL" ? $request->reg_date : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,              
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
    public function williamBeeRirieARDuplicates(Request $request)
    {
        try {
            WbrArDuplicates::insert([
                'account_num' => isset($request->account_num) && $request->account_num != "NULL" ? $request->account_num : NULL,  
                'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL, 
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,  
                'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,  
                'responsible_provider' => isset($request->responsible_provider) && $request->responsible_provider != "NULL" ? $request->responsible_provider : NULL,  
                'reg_date' => isset($request->reg_date) && $request->reg_date != "NULL" ? $request->reg_date : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
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

    public function prineHealthAr(Request $request)
    {
        try {
            $attributes = [
              //  'organization' => isset($request->organization) && $request->organization != "NULL" ? $request->organization : NULL,  
                'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL, 
             //   'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                'responsible_plan' => isset($request->responsible_plan) && $request->responsible_plan != "NULL" ? $request->responsible_plan : NULL,  
              //  'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,                 
              //  'address' => isset($request->address) && $request->address != "NULL" ? $request->address : NULL ,              
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
             //   'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL,  
             //   'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL, 
              //  'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,  
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')        
               
            ];           

            $duplicateRecordExisting  =  PhAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                PhAr::insert([
                        'organization' => isset($request->organization) && $request->organization != "NULL" ? $request->organization : NULL,  
                        'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL, 
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                        'responsible_plan' => isset($request->responsible_plan) && $request->responsible_plan != "NULL" ? $request->responsible_plan : NULL,  
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,                 
                        'address' => isset($request->address) && $request->address != "NULL" ? $request->address : NULL ,              
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL,  
                        'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL, 
                        'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,  
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL, 
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  PhAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'organization' => isset($request->organization) && $request->organization != "NULL" ? $request->organization : NULL,  
                        'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL, 
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                        'responsible_plan' => isset($request->responsible_plan) && $request->responsible_plan != "NULL" ? $request->responsible_plan : NULL,  
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,                 
                        'address' => isset($request->address) && $request->address != "NULL" ? $request->address : NULL ,              
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL,  
                        'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL, 
                        'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,  
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,           
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                    ]);
                    return response()->json(['message' => 'Existing Record Updated Successfully']);
                } else {
                    PhAr::insert([
                        'organization' => isset($request->organization) && $request->organization != "NULL" ? $request->organization : NULL,  
                        'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL, 
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                        'responsible_plan' => isset($request->responsible_plan) && $request->responsible_plan != "NULL" ? $request->responsible_plan : NULL,  
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,                 
                        'address' => isset($request->address) && $request->address != "NULL" ? $request->address : NULL ,              
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL,  
                        'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL, 
                        'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,  
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL, 
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully Bcz its not Assigned Status']);
                }
               
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function prineHealthArDuplicates(Request $request)
    {
        try {
            PhArDuplicates::insert([
                'organization' => isset($request->organization) && $request->organization != "NULL" ? $request->organization : NULL,  
                'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL, 
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                'responsible_plan' => isset($request->responsible_plan) && $request->responsible_plan != "NULL" ? $request->responsible_plan : NULL,  
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,                 
                'address' => isset($request->address) && $request->address != "NULL" ? $request->address : NULL ,              
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL,  
                'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL, 
                'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,  
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
    public function preferredBehavioralHealthGroupAr(Request $request)
    {
        try {
            $attributes = [
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                // 'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL, 
                // 'staff' => isset($request->staff) && $request->staff != "NULL" ? $request->staff : NULL,  
                // 'client_id' => isset($request->client_id) && $request->client_id != "NULL" ? $request->client_id : NULL,  
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,                 
              //  'item_id' => isset($request->item_id) && $request->item_id != "NULL" ? $request->item_id : NULL ,              
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL,  
             //   'expected' => isset($request->expected) && $request->expected != "NULL" ? $request->expected : NULL, 
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
              //  'prj_approved' => isset($request->prj_approved) && $request->prj_approved != "NULL" ? $request->prj_approved : NULL,  
             //   'prj_errors' => isset($request->prj_errors) && $request->prj_errors != "NULL" ? $request->prj_errors : NULL,  
             //   'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL,  
              //  'client_activity' => isset($request->client_activity) && $request->client_activity != "NULL" ? $request->client_activity : NULL,
              'invoke_date' => carbon::now()->format('Y-m-d')        
              
            ];           

            $duplicateRecordExisting  =  PbhgAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                PbhgAr::insert([
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                        'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL, 
                        'staff' => isset($request->staff) && $request->staff != "NULL" ? $request->staff : NULL,  
                        'client_id' => isset($request->client_id) && $request->client_id != "NULL" ? $request->client_id : NULL,  
                        'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,                 
                        'item_id' => isset($request->item_id) && $request->item_id != "NULL" ? $request->item_id : NULL ,              
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL,  
                        'expected' => isset($request->expected) && $request->expected != "NULL" ? $request->expected : NULL, 
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'prj_approved' => isset($request->prj_approved) && $request->prj_approved != "NULL" ? $request->prj_approved : NULL,  
                        'prj_errors' => isset($request->prj_errors) && $request->prj_errors != "NULL" ? $request->prj_errors : NULL,  
                        'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL,  
                        'client_activity' => isset($request->client_activity) && $request->client_activity != "NULL" ? $request->client_activity : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  PbhgAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                        'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL, 
                        'staff' => isset($request->staff) && $request->staff != "NULL" ? $request->staff : NULL,  
                        'client_id' => isset($request->client_id) && $request->client_id != "NULL" ? $request->client_id : NULL,  
                        'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,                 
                        'item_id' => isset($request->item_id) && $request->item_id != "NULL" ? $request->item_id : NULL ,              
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL,  
                        'expected' => isset($request->expected) && $request->expected != "NULL" ? $request->expected : NULL, 
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'prj_approved' => isset($request->prj_approved) && $request->prj_approved != "NULL" ? $request->prj_approved : NULL,  
                        'prj_errors' => isset($request->prj_errors) && $request->prj_errors != "NULL" ? $request->prj_errors : NULL,  
                        'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL,  
                        'client_activity' => isset($request->client_activity) && $request->client_activity != "NULL" ? $request->client_activity : NULL,         
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                    ]);
                    return response()->json(['message' => 'Existing Record Updated Successfully']);
                } else {
                    PbhgAr::insert([
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                        'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL, 
                        'staff' => isset($request->staff) && $request->staff != "NULL" ? $request->staff : NULL,  
                        'client_id' => isset($request->client_id) && $request->client_id != "NULL" ? $request->client_id : NULL,  
                        'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,                 
                        'item_id' => isset($request->item_id) && $request->item_id != "NULL" ? $request->item_id : NULL ,              
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL,  
                        'expected' => isset($request->expected) && $request->expected != "NULL" ? $request->expected : NULL, 
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'prj_approved' => isset($request->prj_approved) && $request->prj_approved != "NULL" ? $request->prj_approved : NULL,  
                        'prj_errors' => isset($request->prj_errors) && $request->prj_errors != "NULL" ? $request->prj_errors : NULL,  
                        'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL,  
                        'client_activity' => isset($request->client_activity) && $request->client_activity != "NULL" ? $request->client_activity : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully Bcz its not Assigned Status']);
                }
               
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function preferredBehavioralHealthGroupArDuplicates(Request $request)
    {
        try {
            PbhgArDuplicates::insert([
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,  
                'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL, 
                'staff' => isset($request->staff) && $request->staff != "NULL" ? $request->staff : NULL,  
                'client_id' => isset($request->client_id) && $request->client_id != "NULL" ? $request->client_id : NULL,  
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,                 
                'item_id' => isset($request->item_id) && $request->item_id != "NULL" ? $request->item_id : NULL ,              
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL,  
                'expected' => isset($request->expected) && $request->expected != "NULL" ? $request->expected : NULL, 
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'prj_approved' => isset($request->prj_approved) && $request->prj_approved != "NULL" ? $request->prj_approved : NULL,  
                'prj_errors' => isset($request->prj_errors) && $request->prj_errors != "NULL" ? $request->prj_errors : NULL,  
                'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL,  
                'client_activity' => isset($request->client_activity) && $request->client_activity != "NULL" ? $request->client_activity : NULL,                   
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
    public function veinInstituteAr(Request $request)
    {
        try {
            $attributes = [
                'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,  
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL, 
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];           

            $duplicateRecordExisting  =  ViAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                ViAr::insert([
                    'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,  
                    'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL, 
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                    'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,                 
                    'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL ,              
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,  
                    'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,  
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL, 
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  ViAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,  
                        'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL, 
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                        'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,                 
                        'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL ,              
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,  
                        'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,  
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
    public function veinInstituteArDuplicates(Request $request)
    {
        try {
            ViArDuplicates::insert([
                'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,  
                'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL, 
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,                 
                'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL ,              
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,  
                'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,  
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

    public function sewickleyEyeGroupAr(Request $request)
    {
        try {
            $attributes = [
                'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,  
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL, 
                'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];          

            $duplicateRecordExisting  =  SegAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                SegAr::insert([
                    'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,  
                    'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL, 
                    'grand_total' => isset($request->grand_total) && $request->grand_total != "NULL" ? $request->grand_total : NULL, 
                    'billed_date' => isset($request->billed_date) && $request->billed_date != "NULL" ? $request->billed_date : NULL, 
                    'outstanding_balance' => isset($request->outstanding_balance) && $request->outstanding_balance != "NULL" ? $request->outstanding_balance : NULL, 
                    'policy_no' => isset($request->policy_no) && $request->policy_no != "NULL" ? $request->policy_no : NULL, 
                    'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL, 
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL, 
                    'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,  
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  SegAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,  
                        'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                        'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL, 
                        'grand_total' => isset($request->grand_total) && $request->grand_total != "NULL" ? $request->grand_total : NULL, 
                        'billed_date' => isset($request->billed_date) && $request->billed_date != "NULL" ? $request->billed_date : NULL, 
                        'outstanding_balance' => isset($request->outstanding_balance) && $request->outstanding_balance != "NULL" ? $request->outstanding_balance : NULL, 
                        'policy_no' => isset($request->policy_no) && $request->policy_no != "NULL" ? $request->policy_no : NULL, 
                        'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL, 
                        'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL, 
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
    public function sewickleyEyeGroupArDuplicates(Request $request)
    {
        try {
            SegArDuplicates::insert([
                'acc_no' => isset($request->acc_no) && $request->acc_no != "NULL" ? $request->acc_no : NULL,  
                'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL,  
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL, 
                'grand_total' => isset($request->grand_total) && $request->grand_total != "NULL" ? $request->grand_total : NULL, 
                'billed_date' => isset($request->billed_date) && $request->billed_date != "NULL" ? $request->billed_date : NULL, 
                'outstanding_balance' => isset($request->outstanding_balance) && $request->outstanding_balance != "NULL" ? $request->outstanding_balance : NULL, 
                'policy_no' => isset($request->policy_no) && $request->policy_no != "NULL" ? $request->policy_no : NULL, 
                'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL, 
                'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL, 
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
    public function precisionBillingAndConsultingServicesAr(Request $request)
    {
        try {
            $attributes = [
                'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL,  
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];          

            $duplicateRecordExisting  =  PbcslAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                PbcslAr::insert([
                    'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,  
                    'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                    'responsible' => isset($request->responsible) && $request->responsible != "NULL" ? $request->responsible : NULL, 
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL, 
                    'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL, 
                    'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL, 
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL, 
                    'category' => isset($request->category) && $request->category != "NULL" ? $request->category : NULL, 
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL, 
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  PbcslAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,  
                        'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL,  
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                        'responsible' => isset($request->responsible) && $request->responsible != "NULL" ? $request->responsible : NULL, 
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL, 
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                        'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL, 
                        'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL, 
                        'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL, 
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL, 
                        'category' => isset($request->category) && $request->category != "NULL" ? $request->category : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s'),
                        'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL, 
                    ]);
                }
                return response()->json(['message' => 'Existing Record Updated Successfully']);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function precisionBillingAndConsultingServicesArDuplicates(Request $request)
    {
        try {
            PbcslArDuplicates::insert([
                'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,  
                'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL,  
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,  
                'responsible' => isset($request->responsible) && $request->responsible != "NULL" ? $request->responsible : NULL, 
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL, 
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                'billed' => isset($request->billed) && $request->billed != "NULL" ? $request->billed : NULL, 
                'prj_procedure' => isset($request->prj_procedure) && $request->prj_procedure != "NULL" ? $request->prj_procedure : NULL, 
                'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL, 
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL, 
                'category' => isset($request->category) && $request->category != "NULL" ? $request->category : NULL,
                'invoke_date' => date('Y-m-d'),
                'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                'chart_status' => "CE_Assigned",
                'unique_id' => isset($request->unique_id) && $request->unique_id != "NULL" ? $request->unique_id : NULL, 
            ]);
            return response()->json(['message' => 'Duplicate Record Inserted Successfully']);
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
   // Siouxland Mental Health Center
   public function SiouxlandMentalHealthCenterAR(Request $request)
    {
        try {
            $attributes = [
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                'claims_no' => isset($request->claims_no) && $request->claims_no != "NULL" ? $request->claims_no : NULL,  
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];          

            $duplicateRecordExisting  =  SmhcAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                SmhcAr::insert([
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
                    'claims_no' => isset($request->claims_no) && $request->claims_no != "NULL" ? $request->claims_no : NULL,  
                    'line_item' => isset($request->line_item) && $request->line_item != "NULL" ? $request->line_item : NULL, 
                    'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL, 
                    'denial_code' => isset($request->denial_code) && $request->denial_code != "NULL" ? $request->denial_code : NULL, 
                    'denial_reason' => isset($request->denial_reason) && $request->denial_reason != "NULL" ? $request->denial_reason : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL, 
                    'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL, 
                    'responsibility_insurance' => isset($request->responsibility_insurance) && $request->responsibility_insurance != "NULL" ? $request->responsibility_insurance : NULL, 
                    'policy_no' => isset($request->policy_no) && $request->policy_no != "NULL" ? $request->policy_no : NULL, 
                    'last_submission_date' => isset($request->last_submission_date) && $request->last_submission_date != "NULL" ? $request->last_submission_date : NULL, 
                    'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL, 
                    'allowable' => isset($request->allowable) && $request->allowable != "NULL" ? $request->allowable : NULL, 
                    'ins_pay' => isset($request->ins_pay) && $request->ins_pay != "NULL" ? $request->ins_pay : NULL, 
                    'pat_pay' => isset($request->pat_pay) && $request->pat_pay != "NULL" ? $request->pat_pay : NULL, 
                    'credit_adj' => isset($request->credit_adj) && $request->credit_adj != "NULL" ? $request->credit_adj : NULL, 
                    'debit_adj' => isset($request->debit_adj) && $request->debit_adj != "NULL" ? $request->debit_adj : NULL, 
                    'refund' => isset($request->refund) && $request->refund != "NULL" ? $request->refund : NULL,
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  SmhcAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
                    'claims_no' => isset($request->claims_no) && $request->claims_no != "NULL" ? $request->claims_no : NULL,  
                    'line_item' => isset($request->line_item) && $request->line_item != "NULL" ? $request->line_item : NULL, 
                    'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL, 
                    'denial_code' => isset($request->denial_code) && $request->denial_code != "NULL" ? $request->denial_code : NULL, 
                    'denial_reason' => isset($request->denial_reason) && $request->denial_reason != "NULL" ? $request->denial_reason : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL, 
                    'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL, 
                    'responsibility_insurance' => isset($request->responsibility_insurance) && $request->responsibility_insurance != "NULL" ? $request->responsibility_insurance : NULL, 
                    'policy_no' => isset($request->policy_no) && $request->policy_no != "NULL" ? $request->policy_no : NULL, 
                    'last_submission_date' => isset($request->last_submission_date) && $request->last_submission_date != "NULL" ? $request->last_submission_date : NULL, 
                    'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL, 
                    'allowable' => isset($request->allowable) && $request->allowable != "NULL" ? $request->allowable : NULL, 
                    'ins_pay' => isset($request->ins_pay) && $request->ins_pay != "NULL" ? $request->ins_pay : NULL, 
                    'pat_pay' => isset($request->pat_pay) && $request->pat_pay != "NULL" ? $request->pat_pay : NULL, 
                    'credit_adj' => isset($request->credit_adj) && $request->credit_adj != "NULL" ? $request->credit_adj : NULL, 
                    'debit_adj' => isset($request->debit_adj) && $request->debit_adj != "NULL" ? $request->debit_adj : NULL, 
                    'refund' => isset($request->refund) && $request->refund != "NULL" ? $request->refund : NULL,
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
    public function SiouxlandMentalHealthCenterARArDuplicates(Request $request)
    {
        try {
            SmhcArDuplicates::insert([
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,  
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,  
                    'claims_no' => isset($request->claims_no) && $request->claims_no != "NULL" ? $request->claims_no : NULL,  
                    'line_item' => isset($request->line_item) && $request->line_item != "NULL" ? $request->line_item : NULL, 
                    'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL, 
                    'denial_code' => isset($request->denial_code) && $request->denial_code != "NULL" ? $request->denial_code : NULL, 
                    'denial_reason' => isset($request->denial_reason) && $request->denial_reason != "NULL" ? $request->denial_reason : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL, 
                    'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL, 
                    'responsibility_insurance' => isset($request->responsibility_insurance) && $request->responsibility_insurance != "NULL" ? $request->responsibility_insurance : NULL, 
                    'policy_no' => isset($request->policy_no) && $request->policy_no != "NULL" ? $request->policy_no : NULL, 
                    'last_submission_date' => isset($request->last_submission_date) && $request->last_submission_date != "NULL" ? $request->last_submission_date : NULL, 
                    'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL, 
                    'allowable' => isset($request->allowable) && $request->allowable != "NULL" ? $request->allowable : NULL, 
                    'ins_pay' => isset($request->ins_pay) && $request->ins_pay != "NULL" ? $request->ins_pay : NULL, 
                    'pat_pay' => isset($request->pat_pay) && $request->pat_pay != "NULL" ? $request->pat_pay : NULL, 
                    'credit_adj' => isset($request->credit_adj) && $request->credit_adj != "NULL" ? $request->credit_adj : NULL, 
                    'debit_adj' => isset($request->debit_adj) && $request->debit_adj != "NULL" ? $request->debit_adj : NULL, 
                    'refund' => isset($request->refund) && $request->refund != "NULL" ? $request->refund : NULL,
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
    // TheQueensHealthSystem
    public function TheQueensHealthSystemAR(Request $request)
    {
        try {
            $attributes = [
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,  
                'account_id' => isset($request->account_id) && $request->account_id != "NULL" ? $request->account_id : NULL,  
                'service_provider' => isset($request->service_provider) && $request->service_provider != "NULL" ? $request->service_provider : NULL, 
                'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL,
                'procedure_code' => isset($request->procedure_code) && $request->procedure_code != "NULL" ? $request->procedure_code : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];          

            $duplicateRecordExisting  =  TqhsAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                TqhsAr::insert([
                    'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                    'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'over_to_90' => isset($request->over_to_90) && $request->over_to_90 != "NULL" ? $request->over_to_90 : NULL, 
                    'prj_created' => isset($request->prj_created) && $request->prj_created != "NULL" ? $request->prj_created : NULL, 
                    'sts' => isset($request->sts) && $request->sts != "NULL" ? $request->sts : NULL, 
                    'account_id' => isset($request->account_id) && $request->account_id != "NULL" ? $request->account_id : NULL, 
                    'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL, 
                    'service_provider' => isset($request->service_provider) && $request->service_provider != "NULL" ? $request->service_provider : NULL, 
                    'billing_provider' => isset($request->billing_provider) && $request->billing_provider != "NULL" ? $request->billing_provider : NULL, 
                    'tx_list' => isset($request->tx_list) && $request->tx_list != "NULL" ? $request->tx_list : NULL, 
                    'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL, 
                    'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL, 
                    'correspondence' => isset($request->correspondence) && $request->correspondence != "NULL" ? $request->correspondence : NULL, 
                    'wq_entry_date' => isset($request->wq_entry_date) && $request->wq_entry_date != "NULL" ? $request->wq_entry_date : NULL, 
                    'payor' => isset($request->payor) && $request->payor != "NULL" ? $request->payor : NULL, 
                    'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL, 
                    'allowance_discrepancy_amount' => isset($request->allowance_discrepancy_amount) && $request->allowance_discrepancy_amount != "NULL" ? $request->allowance_discrepancy_amount : NULL,
                    'allowance_discrepancy_percentage' => isset($request->allowance_discrepancy_percentage) && $request->allowance_discrepancy_percentage != "NULL" ? $request->allowance_discrepancy_percentage : NULL,
                    'timely_filing_deadline_date' => isset($request->timely_filing_deadline_date) && $request->timely_filing_deadline_date != "NULL" ? $request->timely_filing_deadline_date : NULL,
                    'days_until_timely_filing_deadline' => isset($request->days_until_timely_filing_deadline) && $request->days_until_timely_filing_deadline != "NULL" ? $request->days_until_timely_filing_deadline : NULL,
                    'id_1' => isset($request->id_1) && $request->id_1 != "NULL" ? $request->id_1 : NULL,
                    'bill_area' => isset($request->bill_area) && $request->bill_area != "NULL" ? $request->bill_area : NULL,
                    'department' => isset($request->department) && $request->department != "NULL" ? $request->department : NULL,
                    'pos' => isset($request->pos) && $request->pos != "NULL" ? $request->pos : NULL,
                    'bill_area_name' => isset($request->bill_area_name) && $request->bill_area_name != "NULL" ? $request->bill_area_name : NULL,
                    'cross_over_flag' => isset($request->cross_over_flag) && $request->cross_over_flag != "NULL" ? $request->cross_over_flag : NULL,
                    'id_2' => isset($request->id_2) && $request->id_2 != "NULL" ? $request->id_2 : NULL,
                    'last_activity' => isset($request->last_activity) && $request->last_activity != "NULL" ? $request->last_activity : NULL,
                    'reason_codes' => isset($request->reason_codes) && $request->reason_codes != "NULL" ? $request->reason_codes : NULL,
                    'procedure_code' => isset($request->procedure_code) && $request->procedure_code != "NULL" ? $request->procedure_code : NULL,
                    'open_hb_denials' => isset($request->open_hb_denials) && $request->open_hb_denials != "NULL" ? $request->open_hb_denials : NULL,
                    'icn_no' => isset($request->icn_no) && $request->icn_no != "NULL" ? $request->icn_no : NULL,
                    'subscriber_id' => isset($request->subscriber_id) && $request->subscriber_id != "NULL" ? $request->subscriber_id : NULL,
                    'modifier_list' => isset($request->modifier_list) && $request->modifier_list != "NULL" ? $request->modifier_list : NULL,
                    'expected_amt' => isset($request->expected_amt) && $request->expected_amt != "NULL" ? $request->expected_amt : NULL,
                    'payment' => isset($request->payment) && $request->payment != "NULL" ? $request->payment : NULL,
                    'remit_code_last_pmt' => isset($request->remit_code_last_pmt) && $request->remit_code_last_pmt != "NULL" ? $request->remit_code_last_pmt : NULL,
                    'remit_code_name_last_pmt' => isset($request->remit_code_name_last_pmt) && $request->remit_code_name_last_pmt != "NULL" ? $request->remit_code_name_last_pmt : NULL,
                    'precert_required' => isset($request->precert_required) && $request->precert_required != "NULL" ? $request->precert_required : NULL,
                    'referral_created_user_id' => isset($request->referral_created_user_id) && $request->referral_created_user_id != "NULL" ? $request->referral_created_user_id : NULL,
                    'referral_created_user_name' => isset($request->referral_created_user_name) && $request->referral_created_user_name != "NULL" ? $request->referral_created_user_name : NULL,
                    'billing_provider_pin' => isset($request->billing_provider_pin) && $request->billing_provider_pin != "NULL" ? $request->billing_provider_pin : NULL,
                    'billing_provider_npi' => isset($request->billing_provider_npi) && $request->billing_provider_npi != "NULL" ? $request->billing_provider_npi : NULL,
                    'referring_provider_npi' => isset($request->referring_provider_npi) && $request->referring_provider_npi != "NULL" ? $request->referring_provider_npi : NULL,
                    'claim_form_name' => isset($request->claim_form_name) && $request->claim_form_name != "NULL" ? $request->claim_form_name : NULL,
                    'claim_date' => isset($request->claim_date) && $request->claim_date != "NULL" ? $request->claim_date : NULL,
                    'estimate_status' => isset($request->estimate_status) && $request->estimate_status != "NULL" ? $request->estimate_status : NULL,
                    'no_surprise_act_status' => isset($request->no_surprise_act_status) && $request->no_surprise_act_status != "NULL" ? $request->no_surprise_act_status : NULL,
                    'smartedit_message' => isset($request->smartedit_message) && $request->smartedit_message != "NULL" ? $request->smartedit_message : NULL,
                    'last_transfer_user' => isset($request->last_transfer_user) && $request->last_transfer_user != "NULL" ? $request->last_transfer_user : NULL,
                    'last_transfer_wq_id' => isset($request->last_transfer_wq_id) && $request->last_transfer_wq_id != "NULL" ? $request->last_transfer_wq_id : NULL,
                    'last_transfer_wq_nm' => isset($request->last_transfer_wq_nm) && $request->last_transfer_wq_nm != "NULL" ? $request->last_transfer_wq_nm : NULL,
                    'last_transfer_date' => isset($request->last_transfer_date) && $request->last_transfer_date != "NULL" ? $request->last_transfer_date : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  TqhsAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                    'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                    'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'over_to_90' => isset($request->over_to_90) && $request->over_to_90 != "NULL" ? $request->over_to_90 : NULL, 
                    'prj_created' => isset($request->prj_created) && $request->prj_created != "NULL" ? $request->prj_created : NULL, 
                    'sts' => isset($request->sts) && $request->sts != "NULL" ? $request->sts : NULL, 
                    'account_id' => isset($request->account_id) && $request->account_id != "NULL" ? $request->account_id : NULL, 
                    'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL, 
                    'service_provider' => isset($request->service_provider) && $request->service_provider != "NULL" ? $request->service_provider : NULL, 
                    'billing_provider' => isset($request->billing_provider) && $request->billing_provider != "NULL" ? $request->billing_provider : NULL, 
                    'tx_list' => isset($request->tx_list) && $request->tx_list != "NULL" ? $request->tx_list : NULL, 
                    'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL, 
                    'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL, 
                    'correspondence' => isset($request->correspondence) && $request->correspondence != "NULL" ? $request->correspondence : NULL, 
                    'wq_entry_date' => isset($request->wq_entry_date) && $request->wq_entry_date != "NULL" ? $request->wq_entry_date : NULL, 
                    'payor' => isset($request->payor) && $request->payor != "NULL" ? $request->payor : NULL, 
                    'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL, 
                    'allowance_discrepancy_amount' => isset($request->allowance_discrepancy_amount) && $request->allowance_discrepancy_amount != "NULL" ? $request->allowance_discrepancy_amount : NULL,
                    'allowance_discrepancy_percentage' => isset($request->allowance_discrepancy_percentage) && $request->allowance_discrepancy_percentage != "NULL" ? $request->allowance_discrepancy_percentage : NULL,
                    'timely_filing_deadline_date' => isset($request->timely_filing_deadline_date) && $request->timely_filing_deadline_date != "NULL" ? $request->timely_filing_deadline_date : NULL,
                    'days_until_timely_filing_deadline' => isset($request->days_until_timely_filing_deadline) && $request->days_until_timely_filing_deadline != "NULL" ? $request->days_until_timely_filing_deadline : NULL,
                    'id_1' => isset($request->id_1) && $request->id_1 != "NULL" ? $request->id_1 : NULL,
                    'bill_area' => isset($request->bill_area) && $request->bill_area != "NULL" ? $request->bill_area : NULL,
                    'department' => isset($request->department) && $request->department != "NULL" ? $request->department : NULL,
                    'pos' => isset($request->pos) && $request->pos != "NULL" ? $request->pos : NULL,
                    'bill_area_name' => isset($request->bill_area_name) && $request->bill_area_name != "NULL" ? $request->bill_area_name : NULL,
                    'cross_over_flag' => isset($request->cross_over_flag) && $request->cross_over_flag != "NULL" ? $request->cross_over_flag : NULL,
                    'id_2' => isset($request->id_2) && $request->id_2 != "NULL" ? $request->id_2 : NULL,
                    'last_activity' => isset($request->last_activity) && $request->last_activity != "NULL" ? $request->last_activity : NULL,
                    'reason_codes' => isset($request->reason_codes) && $request->reason_codes != "NULL" ? $request->reason_codes : NULL,
                    'procedure_code' => isset($request->procedure_code) && $request->procedure_code != "NULL" ? $request->procedure_code : NULL,
                    'open_hb_denials' => isset($request->open_hb_denials) && $request->open_hb_denials != "NULL" ? $request->open_hb_denials : NULL,
                    'icn_no' => isset($request->icn_no) && $request->icn_no != "NULL" ? $request->icn_no : NULL,
                    'subscriber_id' => isset($request->subscriber_id) && $request->subscriber_id != "NULL" ? $request->subscriber_id : NULL,
                    'modifier_list' => isset($request->modifier_list) && $request->modifier_list != "NULL" ? $request->modifier_list : NULL,
                    'expected_amt' => isset($request->expected_amt) && $request->expected_amt != "NULL" ? $request->expected_amt : NULL,
                    'payment' => isset($request->payment) && $request->payment != "NULL" ? $request->payment : NULL,
                    'remit_code_last_pmt' => isset($request->remit_code_last_pmt) && $request->remit_code_last_pmt != "NULL" ? $request->remit_code_last_pmt : NULL,
                    'remit_code_name_last_pmt' => isset($request->remit_code_name_last_pmt) && $request->remit_code_name_last_pmt != "NULL" ? $request->remit_code_name_last_pmt : NULL,
                    'precert_required' => isset($request->precert_required) && $request->precert_required != "NULL" ? $request->precert_required : NULL,
                    'referral_created_user_id' => isset($request->referral_created_user_id) && $request->referral_created_user_id != "NULL" ? $request->referral_created_user_id : NULL,
                    'referral_created_user_name' => isset($request->referral_created_user_name) && $request->referral_created_user_name != "NULL" ? $request->referral_created_user_name : NULL,
                    'billing_provider_pin' => isset($request->billing_provider_pin) && $request->billing_provider_pin != "NULL" ? $request->billing_provider_pin : NULL,
                    'billing_provider_npi' => isset($request->billing_provider_npi) && $request->billing_provider_npi != "NULL" ? $request->billing_provider_npi : NULL,
                    'referring_provider_npi' => isset($request->referring_provider_npi) && $request->referring_provider_npi != "NULL" ? $request->referring_provider_npi : NULL,
                    'claim_form_name' => isset($request->claim_form_name) && $request->claim_form_name != "NULL" ? $request->claim_form_name : NULL,
                    'claim_date' => isset($request->claim_date) && $request->claim_date != "NULL" ? $request->claim_date : NULL,
                    'estimate_status' => isset($request->estimate_status) && $request->estimate_status != "NULL" ? $request->estimate_status : NULL,
                    'no_surprise_act_status' => isset($request->no_surprise_act_status) && $request->no_surprise_act_status != "NULL" ? $request->no_surprise_act_status : NULL,
                    'smartedit_message' => isset($request->smartedit_message) && $request->smartedit_message != "NULL" ? $request->smartedit_message : NULL,
                    'last_transfer_user' => isset($request->last_transfer_user) && $request->last_transfer_user != "NULL" ? $request->last_transfer_user : NULL,
                    'last_transfer_wq_id' => isset($request->last_transfer_wq_id) && $request->last_transfer_wq_id != "NULL" ? $request->last_transfer_wq_id : NULL,
                    'last_transfer_wq_nm' => isset($request->last_transfer_wq_nm) && $request->last_transfer_wq_nm != "NULL" ? $request->last_transfer_wq_nm : NULL,
                    'last_transfer_date' => isset($request->last_transfer_date) && $request->last_transfer_date != "NULL" ? $request->last_transfer_date : NULL,
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
    public function TheQueensHealthSystemArDuplicates(Request $request)
    {
        try {
            TqhsArDuplicates::insert([
                    'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                    'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'over_to_90' => isset($request->over_to_90) && $request->over_to_90 != "NULL" ? $request->over_to_90 : NULL, 
                    'prj_created' => isset($request->prj_created) && $request->prj_created != "NULL" ? $request->prj_created : NULL, 
                    'sts' => isset($request->sts) && $request->sts != "NULL" ? $request->sts : NULL, 
                    'account_id' => isset($request->account_id) && $request->account_id != "NULL" ? $request->account_id : NULL, 
                    'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL, 
                    'service_provider' => isset($request->service_provider) && $request->service_provider != "NULL" ? $request->service_provider : NULL, 
                    'billing_provider' => isset($request->billing_provider) && $request->billing_provider != "NULL" ? $request->billing_provider : NULL, 
                    'tx_list' => isset($request->tx_list) && $request->tx_list != "NULL" ? $request->tx_list : NULL, 
                    'billed_amt' => isset($request->billed_amt) && $request->billed_amt != "NULL" ? $request->billed_amt : NULL, 
                    'outstanding_amt' => isset($request->outstanding_amt) && $request->outstanding_amt != "NULL" ? $request->outstanding_amt : NULL, 
                    'correspondence' => isset($request->correspondence) && $request->correspondence != "NULL" ? $request->correspondence : NULL, 
                    'wq_entry_date' => isset($request->wq_entry_date) && $request->wq_entry_date != "NULL" ? $request->wq_entry_date : NULL, 
                    'payor' => isset($request->payor) && $request->payor != "NULL" ? $request->payor : NULL, 
                    'plan' => isset($request->plan) && $request->plan != "NULL" ? $request->plan : NULL, 
                    'allowance_discrepancy_amount' => isset($request->allowance_discrepancy_amount) && $request->allowance_discrepancy_amount != "NULL" ? $request->allowance_discrepancy_amount : NULL,
                    'allowance_discrepancy_percentage' => isset($request->allowance_discrepancy_percentage) && $request->allowance_discrepancy_percentage != "NULL" ? $request->allowance_discrepancy_percentage : NULL,
                    'timely_filing_deadline_date' => isset($request->timely_filing_deadline_date) && $request->timely_filing_deadline_date != "NULL" ? $request->timely_filing_deadline_date : NULL,
                    'days_until_timely_filing_deadline' => isset($request->days_until_timely_filing_deadline) && $request->days_until_timely_filing_deadline != "NULL" ? $request->days_until_timely_filing_deadline : NULL,
                    'id_1' => isset($request->id_1) && $request->id_1 != "NULL" ? $request->id_1 : NULL,
                    'bill_area' => isset($request->bill_area) && $request->bill_area != "NULL" ? $request->bill_area : NULL,
                    'department' => isset($request->department) && $request->department != "NULL" ? $request->department : NULL,
                    'pos' => isset($request->pos) && $request->pos != "NULL" ? $request->pos : NULL,
                    'bill_area_name' => isset($request->bill_area_name) && $request->bill_area_name != "NULL" ? $request->bill_area_name : NULL,
                    'cross_over_flag' => isset($request->cross_over_flag) && $request->cross_over_flag != "NULL" ? $request->cross_over_flag : NULL,
                    'id_2' => isset($request->id_2) && $request->id_2 != "NULL" ? $request->id_2 : NULL,
                    'last_activity' => isset($request->last_activity) && $request->last_activity != "NULL" ? $request->last_activity : NULL,
                    'reason_codes' => isset($request->reason_codes) && $request->reason_codes != "NULL" ? $request->reason_codes : NULL,
                    'procedure_code' => isset($request->procedure_code) && $request->procedure_code != "NULL" ? $request->procedure_code : NULL,
                    'open_hb_denials' => isset($request->open_hb_denials) && $request->open_hb_denials != "NULL" ? $request->open_hb_denials : NULL,
                    'icn_no' => isset($request->icn_no) && $request->icn_no != "NULL" ? $request->icn_no : NULL,
                    'subscriber_id' => isset($request->subscriber_id) && $request->subscriber_id != "NULL" ? $request->subscriber_id : NULL,
                    'modifier_list' => isset($request->modifier_list) && $request->modifier_list != "NULL" ? $request->modifier_list : NULL,
                    'expected_amt' => isset($request->expected_amt) && $request->expected_amt != "NULL" ? $request->expected_amt : NULL,
                    'payment' => isset($request->payment) && $request->payment != "NULL" ? $request->payment : NULL,
                    'remit_code_last_pmt' => isset($request->remit_code_last_pmt) && $request->remit_code_last_pmt != "NULL" ? $request->remit_code_last_pmt : NULL,
                    'remit_code_name_last_pmt' => isset($request->remit_code_name_last_pmt) && $request->remit_code_name_last_pmt != "NULL" ? $request->remit_code_name_last_pmt : NULL,
                    'precert_required' => isset($request->precert_required) && $request->precert_required != "NULL" ? $request->precert_required : NULL,
                    'referral_created_user_id' => isset($request->referral_created_user_id) && $request->referral_created_user_id != "NULL" ? $request->referral_created_user_id : NULL,
                    'referral_created_user_name' => isset($request->referral_created_user_name) && $request->referral_created_user_name != "NULL" ? $request->referral_created_user_name : NULL,
                    'billing_provider_pin' => isset($request->billing_provider_pin) && $request->billing_provider_pin != "NULL" ? $request->billing_provider_pin : NULL,
                    'billing_provider_npi' => isset($request->billing_provider_npi) && $request->billing_provider_npi != "NULL" ? $request->billing_provider_npi : NULL,
                    'referring_provider_npi' => isset($request->referring_provider_npi) && $request->referring_provider_npi != "NULL" ? $request->referring_provider_npi : NULL,
                    'claim_form_name' => isset($request->claim_form_name) && $request->claim_form_name != "NULL" ? $request->claim_form_name : NULL,
                    'claim_date' => isset($request->claim_date) && $request->claim_date != "NULL" ? $request->claim_date : NULL,
                    'estimate_status' => isset($request->estimate_status) && $request->estimate_status != "NULL" ? $request->estimate_status : NULL,
                    'no_surprise_act_status' => isset($request->no_surprise_act_status) && $request->no_surprise_act_status != "NULL" ? $request->no_surprise_act_status : NULL,
                    'smartedit_message' => isset($request->smartedit_message) && $request->smartedit_message != "NULL" ? $request->smartedit_message : NULL,
                    'last_transfer_user' => isset($request->last_transfer_user) && $request->last_transfer_user != "NULL" ? $request->last_transfer_user : NULL,
                    'last_transfer_wq_id' => isset($request->last_transfer_wq_id) && $request->last_transfer_wq_id != "NULL" ? $request->last_transfer_wq_id : NULL,
                    'last_transfer_wq_nm' => isset($request->last_transfer_wq_nm) && $request->last_transfer_wq_nm != "NULL" ? $request->last_transfer_wq_nm : NULL,
                    'last_transfer_date' => isset($request->last_transfer_date) && $request->last_transfer_date != "NULL" ? $request->last_transfer_date : NULL,
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

    public function boozmanHoffEyeCenterAr(Request $request)
    {
        try {
            $attributes = [
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,  
                'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,  
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL, 
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'total' => isset($request->total) && $request->total != "NULL" ? $request->total : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];          

            $duplicateRecordExisting  =  BecAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                BecAr::insert([
                    'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,  
                    'transfertype' => isset($request->transfertype) && $request->transfertype != "NULL" ? $request->transfertype : NULL,  
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,  
                    'ins_reporting_category' => isset($request->ins_reporting_category) && $request->ins_reporting_category != "NULL" ? $request->ins_reporting_category : NULL, 
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL, 
                    'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL, 
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'post_date' => isset($request->post_date) && $request->post_date != "NULL" ? $request->post_date : NULL, 
                    'ins_id' => isset($request->ins_id) && $request->ins_id != "NULL" ? $request->ins_id : NULL, 
                    'appointment_id' => isset($request->appointment_id) && $request->appointment_id != "NULL" ? $request->appointment_id : NULL, 
                    'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL, 
                    'less_than_31' => isset($request->less_than_31) && $request->less_than_31 != "NULL" ? $request->less_than_31 : NULL, 
                    'from_31_to_60' => isset($request->from_31_to_60) && $request->from_31_to_60 != "NULL" ? $request->from_31_to_60 : NULL, 
                    'from_61_to_90' => isset($request->from_61_to_90) && $request->from_61_to_90 != "NULL" ? $request->from_61_to_90 : NULL, 
                    'from_91_to_120' => isset($request->from_91_to_120) && $request->from_91_to_120 != "NULL" ? $request->from_91_to_120 : NULL, 
                    'greater_than_120' => isset($request->greater_than_120) && $request->greater_than_120 != "NULL" ? $request->greater_than_120 : NULL, 
                    'total' => isset($request->total) && $request->total != "NULL" ? $request->total : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  BecAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                   'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,  
                    'transfertype' => isset($request->transfertype) && $request->transfertype != "NULL" ? $request->transfertype : NULL,  
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,  
                    'ins_reporting_category' => isset($request->ins_reporting_category) && $request->ins_reporting_category != "NULL" ? $request->ins_reporting_category : NULL, 
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL, 
                    'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL, 
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'post_date' => isset($request->post_date) && $request->post_date != "NULL" ? $request->post_date : NULL, 
                    'ins_id' => isset($request->ins_id) && $request->ins_id != "NULL" ? $request->ins_id : NULL, 
                    'appointment_id' => isset($request->appointment_id) && $request->appointment_id != "NULL" ? $request->appointment_id : NULL, 
                    'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL, 
                    'less_than_31' => isset($request->less_than_31) && $request->less_than_31 != "NULL" ? $request->less_than_31 : NULL, 
                    'from_31_to_60' => isset($request->from_31_to_60) && $request->from_31_to_60 != "NULL" ? $request->from_31_to_60 : NULL, 
                    'from_61_to_90' => isset($request->from_61_to_90) && $request->from_61_to_90 != "NULL" ? $request->from_61_to_90 : NULL, 
                    'from_91_to_120' => isset($request->from_91_to_120) && $request->from_91_to_120 != "NULL" ? $request->from_91_to_120 : NULL, 
                    'greater_than_120' => isset($request->greater_than_120) && $request->greater_than_120 != "NULL" ? $request->greater_than_120 : NULL, 
                    'total' => isset($request->total) && $request->total != "NULL" ? $request->total : NULL,
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
    public function boozmanHoffEyeCenterArDuplicates(Request $request)
    {
        try {
            BecArDuplicates::insert([
                    'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,  
                    'transfertype' => isset($request->transfertype) && $request->transfertype != "NULL" ? $request->transfertype : NULL,  
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,  
                    'ins_reporting_category' => isset($request->ins_reporting_category) && $request->ins_reporting_category != "NULL" ? $request->ins_reporting_category : NULL, 
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL, 
                    'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL, 
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'post_date' => isset($request->post_date) && $request->post_date != "NULL" ? $request->post_date : NULL, 
                    'ins_id' => isset($request->ins_id) && $request->ins_id != "NULL" ? $request->ins_id : NULL, 
                    'appointment_id' => isset($request->appointment_id) && $request->appointment_id != "NULL" ? $request->appointment_id : NULL, 
                    'charges' => isset($request->charges) && $request->charges != "NULL" ? $request->charges : NULL, 
                    'less_than_31' => isset($request->less_than_31) && $request->less_than_31 != "NULL" ? $request->less_than_31 : NULL, 
                    'from_31_to_60' => isset($request->from_31_to_60) && $request->from_31_to_60 != "NULL" ? $request->from_31_to_60 : NULL, 
                    'from_61_to_90' => isset($request->from_61_to_90) && $request->from_61_to_90 != "NULL" ? $request->from_61_to_90 : NULL, 
                    'from_91_to_120' => isset($request->from_91_to_120) && $request->from_91_to_120 != "NULL" ? $request->from_91_to_120 : NULL, 
                    'greater_than_120' => isset($request->greater_than_120) && $request->greater_than_120 != "NULL" ? $request->greater_than_120 : NULL, 
                    'total' => isset($request->total) && $request->total != "NULL" ? $request->total : NULL,
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
    public function renoOrthopedicCenterAr(Request $request)
    {
        try {
            $attributes = [
                 'c_id' => isset($request->c_id) && $request->c_id != "NULL" ? $request->c_id : NULL,  
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                 'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL
             ];          

            $duplicateRecordExisting  =  RocAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                RocAr::insert([
                    'last_activity_type' => isset($request->last_activity_type) && $request->last_activity_type != "NULL" ? $request->last_activity_type : NULL,  
                    'days_to_appeal' => isset($request->days_to_appeal) && $request->days_to_appeal != "NULL" ? $request->days_to_appeal : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL, 
                    'c_id' => isset($request->c_id) && $request->c_id != "NULL" ? $request->c_id : NULL, 
                    'claim_form_name' => isset($request->claim_form_name) && $request->claim_form_name != "NULL" ? $request->claim_form_name : NULL, 
                    'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'sts' => isset($request->sts) && $request->sts != "NULL" ? $request->sts : NULL, 
                    'account_id' => isset($request->account_id) && $request->account_id != "NULL" ? $request->account_id : NULL, 
                    'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL, 
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'payor' => isset($request->payor) && $request->payor != "NULL" ? $request->payor : NULL, 
                    'payor_name' => isset($request->payor_name) && $request->payor_name != "NULL" ? $request->payor_name : NULL, 
                    'denial_code' => isset($request->denial_code) && $request->denial_code != "NULL" ? $request->denial_code : NULL, 
                    'reason_code_list' => isset($request->reason_code_list) && $request->reason_code_list != "NULL" ? $request->reason_code_list : NULL, 
                    'subscriber_id' => isset($request->subscriber_id) && $request->subscriber_id != "NULL" ? $request->subscriber_id : NULL, 
                    'service_provider' => isset($request->service_provider) && $request->service_provider != "NULL" ? $request->service_provider : NULL,
                    'tx_list' => isset($request->tx_list) && $request->tx_list != "NULL" ? $request->tx_list : NULL,
                    'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,
                    'outstanding_amount' => isset($request->outstanding_amount) && $request->outstanding_amount != "NULL" ? $request->outstanding_amount : NULL,
                    'claim_status' => isset($request->claim_status) && $request->claim_status != "NULL" ? $request->claim_status : NULL,
                    'billing_prov_npi' => isset($request->billing_prov_npi) && $request->billing_prov_npi != "NULL" ? $request->billing_prov_npi : NULL,
                    'svc_prov_npi' => isset($request->svc_prov_npi) && $request->svc_prov_npi != "NULL" ? $request->svc_prov_npi : NULL,
                    'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                    'timely_filing_deadline_date' => isset($request->timely_filing_deadline_date) && $request->timely_filing_deadline_date != "NULL" ? $request->timely_filing_deadline_date : NULL,
                    'wq_entry_date' => isset($request->wq_entry_date) && $request->wq_entry_date != "NULL" ? $request->wq_entry_date : NULL,
                    'department_name' => isset($request->department_name) && $request->department_name != "NULL" ? $request->department_name : NULL,
                    'rev_location_name' => isset($request->rev_location_name) && $request->rev_location_name != "NULL" ? $request->rev_location_name : NULL,
                    'pos_name' => isset($request->pos_name) && $request->pos_name != "NULL" ? $request->pos_name : NULL,
                    'is_nrp_suspended' => isset($request->is_nrp_suspended) && $request->is_nrp_suspended != "NULL" ? $request->is_nrp_suspended : NULL,
                    'nrp_suspended_date' => isset($request->nrp_suspended_date) && $request->nrp_suspended_date != "NULL" ? $request->nrp_suspended_date : NULL,
                    'days_in_wq' => isset($request->days_in_wq) && $request->days_in_wq != "NULL" ? $request->days_in_wq : NULL,
                    'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                    'current_payer_icn' => isset($request->current_payer_icn) && $request->current_payer_icn != "NULL" ? $request->current_payer_icn : NULL,
                    'suggested_intial_follow_up_date' => isset($request->suggested_intial_follow_up_date) && $request->suggested_intial_follow_up_date != "NULL" ? $request->suggested_intial_follow_up_date : NULL,
                    'title' => isset($request->title) && $request->title != "NULL" ? $request->title : NULL,
                    'has_corr_mail' => isset($request->has_corr_mail) && $request->has_corr_mail != "NULL" ? $request->has_corr_mail : NULL,
                    'adj_denied' => isset($request->adj_denied) && $request->adj_denied != "NULL" ? $request->adj_denied : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  RocAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'last_activity_type' => isset($request->last_activity_type) && $request->last_activity_type != "NULL" ? $request->last_activity_type : NULL,  
                        'days_to_appeal' => isset($request->days_to_appeal) && $request->days_to_appeal != "NULL" ? $request->days_to_appeal : NULL,  
                        'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL, 
                        'c_id' => isset($request->c_id) && $request->c_id != "NULL" ? $request->c_id : NULL, 
                        'claim_form_name' => isset($request->claim_form_name) && $request->claim_form_name != "NULL" ? $request->claim_form_name : NULL, 
                        'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                        'sts' => isset($request->sts) && $request->sts != "NULL" ? $request->sts : NULL, 
                        'account_id' => isset($request->account_id) && $request->account_id != "NULL" ? $request->account_id : NULL, 
                        'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL, 
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                        'payor' => isset($request->payor) && $request->payor != "NULL" ? $request->payor : NULL, 
                        'payor_name' => isset($request->payor_name) && $request->payor_name != "NULL" ? $request->payor_name : NULL, 
                        'denial_code' => isset($request->denial_code) && $request->denial_code != "NULL" ? $request->denial_code : NULL, 
                        'reason_code_list' => isset($request->reason_code_list) && $request->reason_code_list != "NULL" ? $request->reason_code_list : NULL, 
                        'subscriber_id' => isset($request->subscriber_id) && $request->subscriber_id != "NULL" ? $request->subscriber_id : NULL, 
                        'service_provider' => isset($request->service_provider) && $request->service_provider != "NULL" ? $request->service_provider : NULL,
                        'tx_list' => isset($request->tx_list) && $request->tx_list != "NULL" ? $request->tx_list : NULL,
                        'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,
                        'outstanding_amount' => isset($request->outstanding_amount) && $request->outstanding_amount != "NULL" ? $request->outstanding_amount : NULL,
                        'claim_status' => isset($request->claim_status) && $request->claim_status != "NULL" ? $request->claim_status : NULL,
                        'billing_prov_npi' => isset($request->billing_prov_npi) && $request->billing_prov_npi != "NULL" ? $request->billing_prov_npi : NULL,
                        'svc_prov_npi' => isset($request->svc_prov_npi) && $request->svc_prov_npi != "NULL" ? $request->svc_prov_npi : NULL,
                        'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                        'timely_filing_deadline_date' => isset($request->timely_filing_deadline_date) && $request->timely_filing_deadline_date != "NULL" ? $request->timely_filing_deadline_date : NULL,
                        'wq_entry_date' => isset($request->wq_entry_date) && $request->wq_entry_date != "NULL" ? $request->wq_entry_date : NULL,
                        'department_name' => isset($request->department_name) && $request->department_name != "NULL" ? $request->department_name : NULL,
                        'rev_location_name' => isset($request->rev_location_name) && $request->rev_location_name != "NULL" ? $request->rev_location_name : NULL,
                        'pos_name' => isset($request->pos_name) && $request->pos_name != "NULL" ? $request->pos_name : NULL,
                        'is_nrp_suspended' => isset($request->is_nrp_suspended) && $request->is_nrp_suspended != "NULL" ? $request->is_nrp_suspended : NULL,
                        'nrp_suspended_date' => isset($request->nrp_suspended_date) && $request->nrp_suspended_date != "NULL" ? $request->nrp_suspended_date : NULL,
                        'days_in_wq' => isset($request->days_in_wq) && $request->days_in_wq != "NULL" ? $request->days_in_wq : NULL,
                        'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                        'current_payer_icn' => isset($request->current_payer_icn) && $request->current_payer_icn != "NULL" ? $request->current_payer_icn : NULL,
                        'suggested_intial_follow_up_date' => isset($request->suggested_intial_follow_up_date) && $request->suggested_intial_follow_up_date != "NULL" ? $request->suggested_intial_follow_up_date : NULL,
                        'title' => isset($request->title) && $request->title != "NULL" ? $request->title : NULL,
                        'has_corr_mail' => isset($request->has_corr_mail) && $request->has_corr_mail != "NULL" ? $request->has_corr_mail : NULL,
                        'adj_denied' => isset($request->adj_denied) && $request->adj_denied != "NULL" ? $request->adj_denied : NULL,
                        //'invoke_date' => date('Y-m-d'),
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
    public function renoOrthopedicCenterArDuplicates(Request $request)
    {
        try {
            RocArDuplicates::insert([
                    'last_activity_type' => isset($request->last_activity_type) && $request->last_activity_type != "NULL" ? $request->last_activity_type : NULL,  
                    'days_to_appeal' => isset($request->days_to_appeal) && $request->days_to_appeal != "NULL" ? $request->days_to_appeal : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL, 
                    'c_id' => isset($request->c_id) && $request->c_id != "NULL" ? $request->c_id : NULL, 
                    'claim_form_name' => isset($request->claim_form_name) && $request->claim_form_name != "NULL" ? $request->claim_form_name : NULL, 
                    'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'sts' => isset($request->sts) && $request->sts != "NULL" ? $request->sts : NULL, 
                    'account_id' => isset($request->account_id) && $request->account_id != "NULL" ? $request->account_id : NULL, 
                    'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL, 
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'payor' => isset($request->payor) && $request->payor != "NULL" ? $request->payor : NULL, 
                    'payor_name' => isset($request->payor_name) && $request->payor_name != "NULL" ? $request->payor_name : NULL, 
                    'denial_code' => isset($request->denial_code) && $request->denial_code != "NULL" ? $request->denial_code : NULL, 
                    'reason_code_list' => isset($request->reason_code_list) && $request->reason_code_list != "NULL" ? $request->reason_code_list : NULL, 
                    'subscriber_id' => isset($request->subscriber_id) && $request->subscriber_id != "NULL" ? $request->subscriber_id : NULL, 
                    'service_provider' => isset($request->service_provider) && $request->service_provider != "NULL" ? $request->service_provider : NULL,
                    'tx_list' => isset($request->tx_list) && $request->tx_list != "NULL" ? $request->tx_list : NULL,
                    'billed_amount' => isset($request->billed_amount) && $request->billed_amount != "NULL" ? $request->billed_amount : NULL,
                    'outstanding_amount' => isset($request->outstanding_amount) && $request->outstanding_amount != "NULL" ? $request->outstanding_amount : NULL,
                    'claim_status' => isset($request->claim_status) && $request->claim_status != "NULL" ? $request->claim_status : NULL,
                    'billing_prov_npi' => isset($request->billing_prov_npi) && $request->billing_prov_npi != "NULL" ? $request->billing_prov_npi : NULL,
                    'svc_prov_npi' => isset($request->svc_prov_npi) && $request->svc_prov_npi != "NULL" ? $request->svc_prov_npi : NULL,
                    'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                    'timely_filing_deadline_date' => isset($request->timely_filing_deadline_date) && $request->timely_filing_deadline_date != "NULL" ? $request->timely_filing_deadline_date : NULL,
                    'wq_entry_date' => isset($request->wq_entry_date) && $request->wq_entry_date != "NULL" ? $request->wq_entry_date : NULL,
                    'department_name' => isset($request->department_name) && $request->department_name != "NULL" ? $request->department_name : NULL,
                    'rev_location_name' => isset($request->rev_location_name) && $request->rev_location_name != "NULL" ? $request->rev_location_name : NULL,
                    'pos_name' => isset($request->pos_name) && $request->pos_name != "NULL" ? $request->pos_name : NULL,
                    'is_nrp_suspended' => isset($request->is_nrp_suspended) && $request->is_nrp_suspended != "NULL" ? $request->is_nrp_suspended : NULL,
                    'nrp_suspended_date' => isset($request->nrp_suspended_date) && $request->nrp_suspended_date != "NULL" ? $request->nrp_suspended_date : NULL,
                    'days_in_wq' => isset($request->days_in_wq) && $request->days_in_wq != "NULL" ? $request->days_in_wq : NULL,
                    'date_of_birth' => isset($request->date_of_birth) && $request->date_of_birth != "NULL" ? $request->date_of_birth : NULL,
                    'current_payer_icn' => isset($request->current_payer_icn) && $request->current_payer_icn != "NULL" ? $request->current_payer_icn : NULL,
                    'suggested_intial_follow_up_date' => isset($request->suggested_intial_follow_up_date) && $request->suggested_intial_follow_up_date != "NULL" ? $request->suggested_intial_follow_up_date : NULL,
                    'title' => isset($request->title) && $request->title != "NULL" ? $request->title : NULL,
                    'has_corr_mail' => isset($request->has_corr_mail) && $request->has_corr_mail != "NULL" ? $request->has_corr_mail : NULL,
                    'adj_denied' => isset($request->adj_denied) && $request->adj_denied != "NULL" ? $request->adj_denied : NULL,
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

    public function sbgmgEligiblityVerification(Request $request)
    {
        try {
            $attributes = [
                'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,  
                 'schedule_time' => isset($request->schedule_time) && $request->schedule_time != "NULL" ? $request->schedule_time : NULL,
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,
                'staff_name' => isset($request->staff_name) && $request->staff_name != "NULL" ? $request->staff_name : NULL,
                'code' => isset($request->code) && $request->code != "NULL" ? $request->code : NULL,
                'claim_duration' => isset($request->claim_duration) && $request->claim_duration != "NULL" ? $request->claim_duration : NULL,
                'claim_procedure' => isset($request->claim_procedure) && $request->claim_procedure != "NULL" ? $request->claim_procedure : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'referring_provider' => isset($request->referring_provider) && $request->referring_provider != "NULL" ? $request->referring_provider : NULL,
                'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                'ar_notes' => isset($request->ar_notes) && $request->ar_notes != "NULL" ? $request->ar_notes : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];          

            $duplicateRecordExisting  =  SbgmgEligibilityVerification::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                SbgmgEligibilityVerification::insert([
                    'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,  
                    'schedule_time' => isset($request->schedule_time) && $request->schedule_time != "NULL" ? $request->schedule_time : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,
                    'staff_name' => isset($request->staff_name) && $request->staff_name != "NULL" ? $request->staff_name : NULL,
                    'code' => isset($request->code) && $request->code != "NULL" ? $request->code : NULL,
                    'claim_duration' => isset($request->claim_duration) && $request->claim_duration != "NULL" ? $request->claim_duration : NULL,
                    'claim_procedure' => isset($request->claim_procedure) && $request->claim_procedure != "NULL" ? $request->claim_procedure : NULL,
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                    'referring_provider' => isset($request->referring_provider) && $request->referring_provider != "NULL" ? $request->referring_provider : NULL,
                    'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                    'ar_notes' => isset($request->ar_notes) && $request->ar_notes != "NULL" ? $request->ar_notes : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  SbgmgEligibilityVerification::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,  
                        'schedule_time' => isset($request->schedule_time) && $request->schedule_time != "NULL" ? $request->schedule_time : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,
                        'staff_name' => isset($request->staff_name) && $request->staff_name != "NULL" ? $request->staff_name : NULL,
                        'code' => isset($request->code) && $request->code != "NULL" ? $request->code : NULL,
                        'claim_duration' => isset($request->claim_duration) && $request->claim_duration != "NULL" ? $request->claim_duration : NULL,
                        'claim_procedure' => isset($request->claim_procedure) && $request->claim_procedure != "NULL" ? $request->claim_procedure : NULL,
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                        'referring_provider' => isset($request->referring_provider) && $request->referring_provider != "NULL" ? $request->referring_provider : NULL,
                        'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                        'ar_notes' => isset($request->ar_notes) && $request->ar_notes != "NULL" ? $request->ar_notes : NULL,
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
    public function sbgmgEligiblityVerificationDuplicates(Request $request)
    {
        try {
            SbgmgEligibilityVerificationDuplicates::insert([
                    'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,  
                    'schedule_time' => isset($request->schedule_time) && $request->schedule_time != "NULL" ? $request->schedule_time : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,
                    'staff_name' => isset($request->staff_name) && $request->staff_name != "NULL" ? $request->staff_name : NULL,
                    'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,
                    'code' => isset($request->code) && $request->code != "NULL" ? $request->code : NULL,
                    'claim_duration' => isset($request->claim_duration) && $request->claim_duration != "NULL" ? $request->claim_duration : NULL,
                    'claim_procedure' => isset($request->claim_procedure) && $request->claim_procedure != "NULL" ? $request->claim_procedure : NULL,
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                    'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                    'ar_notes' => isset($request->ar_notes) && $request->ar_notes != "NULL" ? $request->ar_notes : NULL,
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

    public function pbhgEligibilityVerification(Request $request)
    {
        try {
            $attributes = [
                 'organization' => isset($request->organization) && $request->organization != "NULL" ? $request->organization : NULL,  
                 'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,  
                 'claim_time' => isset($request->claim_time) && $request->claim_time != "NULL" ? $request->claim_time : NULL,
                 'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL,
                 'claim_activity' => isset($request->claim_activity) && $request->claim_activity != "NULL" ? $request->claim_activity : NULL,
                 'program' => isset($request->program) && $request->program != "NULL" ? $request->program : NULL,
                 'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                 'staff' => isset($request->staff) && $request->staff != "NULL" ? $request->staff : NULL,
                 'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                 'client_amt_due' => isset($request->client_amt_due) && $request->client_amt_due != "NULL" ? $request->client_amt_due : NULL,
                 'client_id' => isset($request->client_id) && $request->client_id != "NULL" ? $request->client_id : NULL,
              //   'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
               //  'ar_notes' => isset($request->ar_notes) && $request->ar_notes != "NULL" ? $request->ar_notes : NULL,
                 'invoke_date' => carbon::now()->format('Y-m-d')
             ];          

            $duplicateRecordExisting  =  PbhgEligibilityVerification::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                PbhgEligibilityVerification::insert([
                    'organization' => isset($request->organization) && $request->organization != "NULL" ? $request->organization : NULL,  
                    'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,  
                    'claim_time' => isset($request->claim_time) && $request->claim_time != "NULL" ? $request->claim_time : NULL,
                    'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL,
                    'claim_activity' => isset($request->claim_activity) && $request->claim_activity != "NULL" ? $request->claim_activity : NULL,
                    'program' => isset($request->program) && $request->program != "NULL" ? $request->program : NULL,
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                    'staff' => isset($request->staff) && $request->staff != "NULL" ? $request->staff : NULL,
                    'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                    'client_amt_due' => isset($request->client_amt_due) && $request->client_amt_due != "NULL" ? $request->client_amt_due : NULL,
                    'client_id' => isset($request->client_id) && $request->client_id != "NULL" ? $request->client_id : NULL,
                    'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                 //   'ar_notes' => isset($request->ar_notes) && $request->ar_notes != "NULL" ? $request->ar_notes : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  PbhgEligibilityVerification::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'organization' => isset($request->organization) && $request->organization != "NULL" ? $request->organization : NULL,  
                        'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,  
                        'claim_time' => isset($request->claim_time) && $request->claim_time != "NULL" ? $request->claim_time : NULL,
                        'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL,
                        'claim_activity' => isset($request->claim_activity) && $request->claim_activity != "NULL" ? $request->claim_activity : NULL,
                        'program' => isset($request->program) && $request->program != "NULL" ? $request->program : NULL,
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                        'staff' => isset($request->staff) && $request->staff != "NULL" ? $request->staff : NULL,
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'client_amt_due' => isset($request->client_amt_due) && $request->client_amt_due != "NULL" ? $request->client_amt_due : NULL,
                        'client_id' => isset($request->client_id) && $request->client_id != "NULL" ? $request->client_id : NULL,
                        'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                     //   'ar_notes' => isset($request->ar_notes) && $request->ar_notes != "NULL" ? $request->ar_notes : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                    ]);
                    return response()->json(['message' => 'Existing Record Updated Successfully']);
                } else {
                    PbhgEligibilityVerification::insert([
                        'organization' => isset($request->organization) && $request->organization != "NULL" ? $request->organization : NULL,  
                        'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,  
                        'claim_time' => isset($request->claim_time) && $request->claim_time != "NULL" ? $request->claim_time : NULL,
                        'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL,
                        'claim_activity' => isset($request->claim_activity) && $request->claim_activity != "NULL" ? $request->claim_activity : NULL,
                        'program' => isset($request->program) && $request->program != "NULL" ? $request->program : NULL,
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                        'staff' => isset($request->staff) && $request->staff != "NULL" ? $request->staff : NULL,
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'client_amt_due' => isset($request->client_amt_due) && $request->client_amt_due != "NULL" ? $request->client_amt_due : NULL,
                        'client_id' => isset($request->client_id) && $request->client_id != "NULL" ? $request->client_id : NULL,
                        'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                      //  'ar_notes' => isset($request->ar_notes) && $request->ar_notes != "NULL" ? $request->ar_notes : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                        ]);
                            return response()->json(['message' => 'Record Inserted Successfully Bcz its not Assigned Status']);
                }
              
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function pbhgEligibilityVerificationDuplicates(Request $request)
    {
        try {
            PbhgEligibilityVerificationDuplicates::insert([
                    'organization' => isset($request->organization) && $request->organization != "NULL" ? $request->organization : NULL,  
                    'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,  
                    'claim_time' => isset($request->claim_time) && $request->claim_time != "NULL" ? $request->claim_time : NULL,
                    'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL,
                    'claim_activity' => isset($request->claim_activity) && $request->claim_activity != "NULL" ? $request->claim_activity : NULL,
                    'program' => isset($request->program) && $request->program != "NULL" ? $request->program : NULL,
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                    'staff' => isset($request->staff) && $request->staff != "NULL" ? $request->staff : NULL,
                    'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                    'client_amt_due' => isset($request->client_amt_due) && $request->client_amt_due != "NULL" ? $request->client_amt_due : NULL,
                    'client_id' => isset($request->client_id) && $request->client_id != "NULL" ? $request->client_id : NULL,
                    'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                  //  'ar_notes' => isset($request->ar_notes) && $request->ar_notes != "NULL" ? $request->ar_notes : NULL,
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

    public function msEligiblityVerification(Request $request)
    {
        try {
            $attributes = [
                'last_name' => isset($request->last_name) && $request->last_name != "NULL" ? $request->last_name : NULL,
                'first_name' => isset($request->first_name) && $request->first_name != "NULL" ? $request->first_name : NULL,
                'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                'next_appt_with' => isset($request->next_appt_with) && $request->next_appt_with != "NULL" ? $request->next_appt_with : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'insured_id' => isset($request->insured_id) && $request->insured_id != "NULL" ? $request->insured_id : NULL,
                'clinicians' => isset($request->clinicians) && $request->clinicians != "NULL" ? $request->clinicians : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];          

            $duplicateRecordExisting  =  MsEligibilityVerification::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                MsEligibilityVerification::insert([
                    'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL,  
                    'ssn' => isset($request->ssn) && $request->ssn != "NULL" ? $request->ssn : NULL,
                    'last_name' => isset($request->last_name) && $request->last_name != "NULL" ? $request->last_name : NULL,
                    'first_name' => isset($request->first_name) && $request->first_name != "NULL" ? $request->first_name : NULL,
                    'middle_name' => isset($request->middle_name) && $request->middle_name != "NULL" ? $request->middle_name : NULL,
                    'suffix' => isset($request->suffix) && $request->suffix != "NULL" ? $request->suffix : NULL,
                    'preferred_name' => isset($request->preferred_name) && $request->preferred_name != "NULL" ? $request->preferred_name : NULL,
                    'pronouns' => isset($request->pronouns) && $request->pronouns != "NULL" ? $request->pronouns : NULL,
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                    'address_line_1' => isset($request->address_line_1) && $request->address_line_1 != "NULL" ? $request->address_line_1 : NULL,
                    'address_line_2' => isset($request->address_line_2) && $request->address_line_2 != "NULL" ? $request->address_line_2 : NULL,
                    'city' => isset($request->city) && $request->city != "NULL" ? $request->city : NULL,
                    'state' => isset($request->state) && $request->state != "NULL" ? $request->state : NULL,
                    'postal_code' => isset($request->postal_code) && $request->postal_code != "NULL" ? $request->postal_code : NULL,
                    'country' => isset($request->country) && $request->country != "NULL" ? $request->country : NULL,
                    'email' => isset($request->email) && $request->email != "NULL" ? $request->email : NULL,
                    'preferred_phone' => isset($request->preferred_phone) && $request->preferred_phone != "NULL" ? $request->preferred_phone : NULL,
                    'home_number' => isset($request->home_number) && $request->home_number != "NULL" ? $request->home_number : NULL,
                    'home_messages' => isset($request->home_messages) && $request->home_messages != "NULL" ? $request->home_messages : NULL,
                    'mobile_number' => isset($request->mobile_number) && $request->mobile_number != "NULL" ? $request->mobile_number : NULL,
                    'mobile_messages' => isset($request->mobile_messages) && $request->mobile_messages != "NULL" ? $request->mobile_messages : NULL,
                    'work_number' => isset($request->work_number) && $request->work_number != "NULL" ? $request->work_number : NULL,
                    'work_messages' => isset($request->work_messages) && $request->work_messages != "NULL" ? $request->work_messages : NULL,
                    'other_number' => isset($request->other_number) && $request->other_number != "NULL" ? $request->other_number : NULL,
                    'other_messages' => isset($request->other_messages) && $request->other_messages != "NULL" ? $request->other_messages : NULL,
                    'administrative_sex' => isset($request->administrative_sex) && $request->administrative_sex != "NULL" ? $request->administrative_sex : NULL,
                    'marital_status' => isset($request->marital_status) && $request->marital_status != "NULL" ? $request->marital_status : NULL,
                    'employment_status' => isset($request->employment_status) && $request->employment_status != "NULL" ? $request->employment_status : NULL,
                    'last_appt' => isset($request->last_appt) && $request->last_appt != "NULL" ? $request->last_appt : NULL,
                    'next_appt' => isset($request->next_appt) && $request->next_appt != "NULL" ? $request->next_appt : NULL,
                    'next_appt_with' => isset($request->next_appt_with) && $request->next_appt_with != "NULL" ? $request->next_appt_with : NULL,
                    'relationship_to_insured' => isset($request->relationship_to_insured) && $request->relationship_to_insured != "NULL" ? $request->relationship_to_insured : NULL,
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                    'insured_id' => isset($request->insured_id) && $request->insured_id != "NULL" ? $request->insured_id : NULL,
                    'secondary_payer' => isset($request->secondary_payer) && $request->secondary_payer != "NULL" ? $request->secondary_payer : NULL,
                    'secondary_insured_id' => isset($request->secondary_insured_id) && $request->secondary_insured_id != "NULL" ? $request->secondary_insured_id : NULL,
                    'tertiary_payer' => isset($request->tertiary_payer) && $request->tertiary_payer != "NULL" ? $request->tertiary_payer : NULL,
                    'tertiary_insured_id' => isset($request->tertiary_insured_id) && $request->tertiary_insured_id != "NULL" ? $request->tertiary_insured_id : NULL,
                    'quaternary_payer' => isset($request->quaternary_payer) && $request->quaternary_payer != "NULL" ? $request->quaternary_payer : NULL,
                    'quaternary_insured_id' => isset($request->quaternary_insured_id) && $request->quaternary_insured_id != "NULL" ? $request->quaternary_insured_id : NULL,
                    'clinicians' => isset($request->clinicians) && $request->clinicians != "NULL" ? $request->clinicians : NULL,
                    'gender_identity' => isset($request->gender_identity) && $request->gender_identity != "NULL" ? $request->gender_identity : NULL,
                    'sexual_orientation' => isset($request->sexual_orientation) && $request->sexual_orientation != "NULL" ? $request->sexual_orientation : NULL,
                    'race' => isset($request->race) && $request->race != "NULL" ? $request->race : NULL,
                    'ethnicity' => isset($request->ethnicity) && $request->ethnicity != "NULL" ? $request->ethnicity : NULL,
                    'languages' => isset($request->languages) && $request->languages != "NULL" ? $request->languages : NULL,
                    'religious_affiliation' => isset($request->religious_affiliation) && $request->religious_affiliation != "NULL" ? $request->religious_affiliation : NULL,
                    'text_messages' => isset($request->text_messages) && $request->text_messages != "NULL" ? $request->text_messages : NULL,
                    'ar_notes' => isset($request->ar_notes) && $request->ar_notes != "NULL" ? $request->ar_notes : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  MsEligibilityVerification::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL,  
                        'ssn' => isset($request->ssn) && $request->ssn != "NULL" ? $request->ssn : NULL,
                        'last_name' => isset($request->last_name) && $request->last_name != "NULL" ? $request->last_name : NULL,
                        'first_name' => isset($request->first_name) && $request->first_name != "NULL" ? $request->first_name : NULL,
                        'middle_name' => isset($request->middle_name) && $request->middle_name != "NULL" ? $request->middle_name : NULL,
                        'suffix' => isset($request->suffix) && $request->suffix != "NULL" ? $request->suffix : NULL,
                        'preferred_name' => isset($request->preferred_name) && $request->preferred_name != "NULL" ? $request->preferred_name : NULL,
                        'pronouns' => isset($request->pronouns) && $request->pronouns != "NULL" ? $request->pronouns : NULL,
                        'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                        'address_line_1' => isset($request->address_line_1) && $request->address_line_1 != "NULL" ? $request->address_line_1 : NULL,
                        'address_line_2' => isset($request->address_line_2) && $request->address_line_2 != "NULL" ? $request->address_line_2 : NULL,
                        'city' => isset($request->city) && $request->city != "NULL" ? $request->city : NULL,
                        'state' => isset($request->state) && $request->state != "NULL" ? $request->state : NULL,
                        'postal_code' => isset($request->postal_code) && $request->postal_code != "NULL" ? $request->postal_code : NULL,
                        'country' => isset($request->country) && $request->country != "NULL" ? $request->country : NULL,
                        'email' => isset($request->email) && $request->email != "NULL" ? $request->email : NULL,
                        'preferred_phone' => isset($request->preferred_phone) && $request->preferred_phone != "NULL" ? $request->preferred_phone : NULL,
                        'home_number' => isset($request->home_number) && $request->home_number != "NULL" ? $request->home_number : NULL,
                        'home_messages' => isset($request->home_messages) && $request->home_messages != "NULL" ? $request->home_messages : NULL,
                        'mobile_number' => isset($request->mobile_number) && $request->mobile_number != "NULL" ? $request->mobile_number : NULL,
                        'mobile_messages' => isset($request->mobile_messages) && $request->mobile_messages != "NULL" ? $request->mobile_messages : NULL,
                        'work_number' => isset($request->work_number) && $request->work_number != "NULL" ? $request->work_number : NULL,
                        'work_messages' => isset($request->work_messages) && $request->work_messages != "NULL" ? $request->work_messages : NULL,
                        'other_number' => isset($request->other_number) && $request->other_number != "NULL" ? $request->other_number : NULL,
                        'other_messages' => isset($request->other_messages) && $request->other_messages != "NULL" ? $request->other_messages : NULL,
                        'administrative_sex' => isset($request->administrative_sex) && $request->administrative_sex != "NULL" ? $request->administrative_sex : NULL,
                        'marital_status' => isset($request->marital_status) && $request->marital_status != "NULL" ? $request->marital_status : NULL,
                        'employment_status' => isset($request->employment_status) && $request->employment_status != "NULL" ? $request->employment_status : NULL,
                        'last_appt' => isset($request->last_appt) && $request->last_appt != "NULL" ? $request->last_appt : NULL,
                        'next_appt' => isset($request->next_appt) && $request->next_appt != "NULL" ? $request->next_appt : NULL,
                        'next_appt_with' => isset($request->next_appt_with) && $request->next_appt_with != "NULL" ? $request->next_appt_with : NULL,
                        'relationship_to_insured' => isset($request->relationship_to_insured) && $request->relationship_to_insured != "NULL" ? $request->relationship_to_insured : NULL,
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                        'insured_id' => isset($request->insured_id) && $request->insured_id != "NULL" ? $request->insured_id : NULL,
                        'secondary_payer' => isset($request->secondary_payer) && $request->secondary_payer != "NULL" ? $request->secondary_payer : NULL,
                        'secondary_insured_id' => isset($request->secondary_insured_id) && $request->secondary_insured_id != "NULL" ? $request->secondary_insured_id : NULL,
                        'tertiary_payer' => isset($request->tertiary_payer) && $request->tertiary_payer != "NULL" ? $request->tertiary_payer : NULL,
                        'tertiary_insured_id' => isset($request->tertiary_insured_id) && $request->tertiary_insured_id != "NULL" ? $request->tertiary_insured_id : NULL,
                        'quaternary_payer' => isset($request->quaternary_payer) && $request->quaternary_payer != "NULL" ? $request->quaternary_payer : NULL,
                        'quaternary_insured_id' => isset($request->quaternary_insured_id) && $request->quaternary_insured_id != "NULL" ? $request->quaternary_insured_id : NULL,
                        'clinicians' => isset($request->clinicians) && $request->clinicians != "NULL" ? $request->clinicians : NULL,
                        'gender_identity' => isset($request->gender_identity) && $request->gender_identity != "NULL" ? $request->gender_identity : NULL,
                        'sexual_orientation' => isset($request->sexual_orientation) && $request->sexual_orientation != "NULL" ? $request->sexual_orientation : NULL,
                        'race' => isset($request->race) && $request->race != "NULL" ? $request->race : NULL,
                        'ethnicity' => isset($request->ethnicity) && $request->ethnicity != "NULL" ? $request->ethnicity : NULL,
                        'languages' => isset($request->languages) && $request->languages != "NULL" ? $request->languages : NULL,
                        'religious_affiliation' => isset($request->religious_affiliation) && $request->religious_affiliation != "NULL" ? $request->religious_affiliation : NULL,
                        'text_messages' => isset($request->text_messages) && $request->text_messages != "NULL" ? $request->text_messages : NULL,
                        'ar_notes' => isset($request->ar_notes) && $request->ar_notes != "NULL" ? $request->ar_notes : NULL,
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
    public function msEligiblityVerificationDuplicates(Request $request)
    {
        try {
            MsEligibilityVerificationDuplicates::insert([
                'account' => isset($request->account) && $request->account != "NULL" ? $request->account : NULL,  
                'ssn' => isset($request->ssn) && $request->ssn != "NULL" ? $request->ssn : NULL,
                'last_name' => isset($request->last_name) && $request->last_name != "NULL" ? $request->last_name : NULL,
                'first_name' => isset($request->first_name) && $request->first_name != "NULL" ? $request->first_name : NULL,
                'middle_name' => isset($request->middle_name) && $request->middle_name != "NULL" ? $request->middle_name : NULL,
                'suffix' => isset($request->suffix) && $request->suffix != "NULL" ? $request->suffix : NULL,
                'preferred_name' => isset($request->preferred_name) && $request->preferred_name != "NULL" ? $request->preferred_name : NULL,
                'pronouns' => isset($request->pronouns) && $request->pronouns != "NULL" ? $request->pronouns : NULL,
                'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                'address_line_1' => isset($request->address_line_1) && $request->address_line_1 != "NULL" ? $request->address_line_1 : NULL,
                'address_line_2' => isset($request->address_line_2) && $request->address_line_2 != "NULL" ? $request->address_line_2 : NULL,
                'city' => isset($request->city) && $request->city != "NULL" ? $request->city : NULL,
                'state' => isset($request->state) && $request->state != "NULL" ? $request->state : NULL,
                'postal_code' => isset($request->postal_code) && $request->postal_code != "NULL" ? $request->postal_code : NULL,
                'country' => isset($request->country) && $request->country != "NULL" ? $request->country : NULL,
                'email' => isset($request->email) && $request->email != "NULL" ? $request->email : NULL,
                'preferred_phone' => isset($request->preferred_phone) && $request->preferred_phone != "NULL" ? $request->preferred_phone : NULL,
                'home_number' => isset($request->home_number) && $request->home_number != "NULL" ? $request->home_number : NULL,
                'home_messages' => isset($request->home_messages) && $request->home_messages != "NULL" ? $request->home_messages : NULL,
                'mobile_number' => isset($request->mobile_number) && $request->mobile_number != "NULL" ? $request->mobile_number : NULL,
                'mobile_messages' => isset($request->mobile_messages) && $request->mobile_messages != "NULL" ? $request->mobile_messages : NULL,
                'work_number' => isset($request->work_number) && $request->work_number != "NULL" ? $request->work_number : NULL,
                'work_messages' => isset($request->work_messages) && $request->work_messages != "NULL" ? $request->work_messages : NULL,
                'other_number' => isset($request->other_number) && $request->other_number != "NULL" ? $request->other_number : NULL,
                'other_messages' => isset($request->other_messages) && $request->other_messages != "NULL" ? $request->other_messages : NULL,
                'administrative_sex' => isset($request->administrative_sex) && $request->administrative_sex != "NULL" ? $request->administrative_sex : NULL,
                'marital_status' => isset($request->marital_status) && $request->marital_status != "NULL" ? $request->marital_status : NULL,
                'employment_status' => isset($request->employment_status) && $request->employment_status != "NULL" ? $request->employment_status : NULL,
                'last_appt' => isset($request->last_appt) && $request->last_appt != "NULL" ? $request->last_appt : NULL,
                'next_appt' => isset($request->next_appt) && $request->next_appt != "NULL" ? $request->next_appt : NULL,
                'next_appt_with' => isset($request->next_appt_with) && $request->next_appt_with != "NULL" ? $request->next_appt_with : NULL,
                'relationship_to_insured' => isset($request->relationship_to_insured) && $request->relationship_to_insured != "NULL" ? $request->relationship_to_insured : NULL,
                'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                'insured_id' => isset($request->insured_id) && $request->insured_id != "NULL" ? $request->insured_id : NULL,
                'secondary_payer' => isset($request->secondary_payer) && $request->secondary_payer != "NULL" ? $request->secondary_payer : NULL,
                'secondary_insured_id' => isset($request->secondary_insured_id) && $request->secondary_insured_id != "NULL" ? $request->secondary_insured_id : NULL,
                'tertiary_payer' => isset($request->tertiary_payer) && $request->tertiary_payer != "NULL" ? $request->tertiary_payer : NULL,
                'tertiary_insured_id' => isset($request->tertiary_insured_id) && $request->tertiary_insured_id != "NULL" ? $request->tertiary_insured_id : NULL,
                'quaternary_payer' => isset($request->quaternary_payer) && $request->quaternary_payer != "NULL" ? $request->quaternary_payer : NULL,
                'quaternary_insured_id' => isset($request->quaternary_insured_id) && $request->quaternary_insured_id != "NULL" ? $request->quaternary_insured_id : NULL,
                'clinicians' => isset($request->clinicians) && $request->clinicians != "NULL" ? $request->clinicians : NULL,
                'gender_identity' => isset($request->gender_identity) && $request->gender_identity != "NULL" ? $request->gender_identity : NULL,
                'sexual_orientation' => isset($request->sexual_orientation) && $request->sexual_orientation != "NULL" ? $request->sexual_orientation : NULL,
                'race' => isset($request->race) && $request->race != "NULL" ? $request->race : NULL,
                'ethnicity' => isset($request->ethnicity) && $request->ethnicity != "NULL" ? $request->ethnicity : NULL,
                'languages' => isset($request->languages) && $request->languages != "NULL" ? $request->languages : NULL,
                'religious_affiliation' => isset($request->religious_affiliation) && $request->religious_affiliation != "NULL" ? $request->religious_affiliation : NULL,
                'text_messages' => isset($request->text_messages) && $request->text_messages != "NULL" ? $request->text_messages : NULL,
                'ar_notes' => isset($request->ar_notes) && $request->ar_notes != "NULL" ? $request->ar_notes : NULL,
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

    public function smbArEvolution(Request $request)
    {
        try {
            $attributes = [
                 'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,  
                 'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                 'invoke_date' => carbon::now()->format('Y-m-d')
             ];         

            $duplicateRecordExisting  =  SmbArEvolution::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                SmbArEvolution::insert([
                    'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,  
                    'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,
                    'uid_pt_dos' => isset($request->uid_pt_dos) && $request->uid_pt_dos != "NULL" ? $request->uid_pt_dos : NULL,
                    'dup_uid_pt_dos' => isset($request->dup_uid_pt_dos) && $request->dup_uid_pt_dos != "NULL" ? $request->dup_uid_pt_dos : NULL,
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'year' => isset($request->year) && $request->year != "NULL" ? $request->year : NULL,
                    'month' => isset($request->month) && $request->month != "NULL" ? $request->month : NULL,
                    'days' => isset($request->days) && $request->days != "NULL" ? $request->days : NULL,
                    'tdate' => isset($request->tdate) && $request->tdate != "NULL" ? $request->tdate : NULL,
                    'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                    'disputed' => isset($request->disputed) && $request->disputed != "NULL" ? $request->disputed : NULL,
                    'first_billed' => isset($request->first_billed) && $request->first_billed != "NULL" ? $request->first_billed : NULL,
                    'last_billed' => isset($request->last_billed) && $request->last_billed != "NULL" ? $request->last_billed : NULL,
                    'aged_0_to_30' => isset($request->aged_0_to_30) && $request->aged_0_to_30 != "NULL" ? $request->aged_0_to_30 : NULL,
                    'aged_31_to_60' => isset($request->aged_31_to_60) && $request->aged_31_to_60 != "NULL" ? $request->aged_31_to_60 : NULL,
                    'aged_61_to_90' => isset($request->aged_61_to_90) && $request->aged_61_to_90 != "NULL" ? $request->aged_61_to_90 : NULL,
                    'aged_91_to_120' => isset($request->aged_91_to_120) && $request->aged_91_to_120 != "NULL" ? $request->aged_91_to_120 : NULL,
                    'aged_121' => isset($request->aged_121) && $request->aged_121 != "NULL" ? $request->aged_121 : NULL,
                    'total_owed' => isset($request->total_owed) && $request->total_owed != "NULL" ? $request->total_owed : NULL,
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  SmbArEvolution::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,  
                        'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,
                        'uid_pt_dos' => isset($request->uid_pt_dos) && $request->uid_pt_dos != "NULL" ? $request->uid_pt_dos : NULL,
                        'dup_uid_pt_dos' => isset($request->dup_uid_pt_dos) && $request->dup_uid_pt_dos != "NULL" ? $request->dup_uid_pt_dos : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'year' => isset($request->year) && $request->year != "NULL" ? $request->year : NULL,
                        'month' => isset($request->month) && $request->month != "NULL" ? $request->month : NULL,
                        'days' => isset($request->days) && $request->days != "NULL" ? $request->days : NULL,
                        'tdate' => isset($request->tdate) && $request->tdate != "NULL" ? $request->tdate : NULL,
                        'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                        'disputed' => isset($request->disputed) && $request->disputed != "NULL" ? $request->disputed : NULL,
                        'first_billed' => isset($request->first_billed) && $request->first_billed != "NULL" ? $request->first_billed : NULL,
                        'last_billed' => isset($request->last_billed) && $request->last_billed != "NULL" ? $request->last_billed : NULL,
                        'aged_0_to_30' => isset($request->aged_0_to_30) && $request->aged_0_to_30 != "NULL" ? $request->aged_0_to_30 : NULL,
                        'aged_31_to_60' => isset($request->aged_31_to_60) && $request->aged_31_to_60 != "NULL" ? $request->aged_31_to_60 : NULL,
                        'aged_61_to_90' => isset($request->aged_61_to_90) && $request->aged_61_to_90 != "NULL" ? $request->aged_61_to_90 : NULL,
                        'aged_91_to_120' => isset($request->aged_91_to_120) && $request->aged_91_to_120 != "NULL" ? $request->aged_91_to_120 : NULL,
                        'aged_121' => isset($request->aged_121) && $request->aged_121 != "NULL" ? $request->aged_121 : NULL,
                        'total_owed' => isset($request->total_owed) && $request->total_owed != "NULL" ? $request->total_owed : NULL,
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
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
    public function smbArEvolutionDuplicates(Request $request)
    {
        try {
            SmbArEvolutionDuplicates::insert([
                    'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,  
                    'account_no' => isset($request->account_no) && $request->account_no != "NULL" ? $request->account_no : NULL,
                    'uid_pt_dos' => isset($request->uid_pt_dos) && $request->uid_pt_dos != "NULL" ? $request->uid_pt_dos : NULL,
                    'dup_uid_pt_dos' => isset($request->dup_uid_pt_dos) && $request->dup_uid_pt_dos != "NULL" ? $request->dup_uid_pt_dos : NULL,
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'year' => isset($request->year) && $request->year != "NULL" ? $request->year : NULL,
                    'month' => isset($request->month) && $request->month != "NULL" ? $request->month : NULL,
                    'days' => isset($request->days) && $request->days != "NULL" ? $request->days : NULL,
                    'tdate' => isset($request->tdate) && $request->tdate != "NULL" ? $request->tdate : NULL,
                    'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                    'disputed' => isset($request->disputed) && $request->disputed != "NULL" ? $request->disputed : NULL,
                    'first_billed' => isset($request->first_billed) && $request->first_billed != "NULL" ? $request->first_billed : NULL,
                    'last_billed' => isset($request->last_billed) && $request->last_billed != "NULL" ? $request->last_billed : NULL,
                    'aged_0_to_30' => isset($request->aged_0_to_30) && $request->aged_0_to_30 != "NULL" ? $request->aged_0_to_30 : NULL,
                    'aged_31_to_60' => isset($request->aged_31_to_60) && $request->aged_31_to_60 != "NULL" ? $request->aged_31_to_60 : NULL,
                    'aged_61_to_90' => isset($request->aged_61_to_90) && $request->aged_61_to_90 != "NULL" ? $request->aged_61_to_90 : NULL,
                    'aged_91_to_120' => isset($request->aged_91_to_120) && $request->aged_91_to_120 != "NULL" ? $request->aged_91_to_120 : NULL,
                    'aged_121' => isset($request->aged_121) && $request->aged_121 != "NULL" ? $request->aged_121 : NULL,
                    'total_owed' => isset($request->total_owed) && $request->total_owed != "NULL" ? $request->total_owed : NULL,
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
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
    public function smbArProactive(Request $request)
    {
        try {
            $attributes = [
                  'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                  'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                 'invoke_date' => carbon::now()->format('Y-m-d')
             ];         

            $duplicateRecordExisting  =  SmbArProactive::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                SmbArProactive::insert([
                    'uid_pt_dos' => isset($request->uid_pt_dos) && $request->uid_pt_dos != "NULL" ? $request->uid_pt_dos : NULL,
                    'dup_uid_pt_dos' => isset($request->dup_uid_pt_dos) && $request->dup_uid_pt_dos != "NULL" ? $request->dup_uid_pt_dos : NULL,
                    'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                    'days' => isset($request->days) && $request->days != "NULL" ? $request->days : NULL,
                    'since' => isset($request->since) && $request->since != "NULL" ? $request->since : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'year' => isset($request->year) && $request->year != "NULL" ? $request->year : NULL,
                    'month' => isset($request->month) && $request->month != "NULL" ? $request->month : NULL,
                    'tdate' => isset($request->tdate) && $request->tdate != "NULL" ? $request->tdate : NULL,
                    'service' => isset($request->service) && $request->service != "NULL" ? $request->service : NULL,
                    'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL,
                    'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                    'cdate' => isset($request->cdate) && $request->cdate != "NULL" ? $request->cdate : NULL,
                    'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                    'ins' => isset($request->ins) && $request->ins != "NULL" ? $request->ins : NULL,
                    'client_paid' => isset($request->client_paid) && $request->client_paid != "NULL" ? $request->client_paid : NULL,
                    'adj' => isset($request->adj) && $request->adj != "NULL" ? $request->adj : NULL,
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  SmbArProactive::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'uid_pt_dos' => isset($request->uid_pt_dos) && $request->uid_pt_dos != "NULL" ? $request->uid_pt_dos : NULL,
                        'dup_uid_pt_dos' => isset($request->dup_uid_pt_dos) && $request->dup_uid_pt_dos != "NULL" ? $request->dup_uid_pt_dos : NULL,
                        'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                        'days' => isset($request->days) && $request->days != "NULL" ? $request->days : NULL,
                        'since' => isset($request->since) && $request->since != "NULL" ? $request->since : NULL,  
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'year' => isset($request->year) && $request->year != "NULL" ? $request->year : NULL,
                        'month' => isset($request->month) && $request->month != "NULL" ? $request->month : NULL,
                        'tdate' => isset($request->tdate) && $request->tdate != "NULL" ? $request->tdate : NULL,
                        'service' => isset($request->service) && $request->service != "NULL" ? $request->service : NULL,
                        'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL,
                        'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                        'cdate' => isset($request->cdate) && $request->cdate != "NULL" ? $request->cdate : NULL,
                        'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                        'ins' => isset($request->ins) && $request->ins != "NULL" ? $request->ins : NULL,
                        'client_paid' => isset($request->client_paid) && $request->client_paid != "NULL" ? $request->client_paid : NULL,
                        'adj' => isset($request->adj) && $request->adj != "NULL" ? $request->adj : NULL,
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
    public function smbArProactiveDuplicates(Request $request)
    {
        try {
            SmbArProactiveDuplicates::insert([
                    'uid_pt_dos' => isset($request->uid_pt_dos) && $request->uid_pt_dos != "NULL" ? $request->uid_pt_dos : NULL,
                    'dup_uid_pt_dos' => isset($request->dup_uid_pt_dos) && $request->dup_uid_pt_dos != "NULL" ? $request->dup_uid_pt_dos : NULL,
                    'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                    'days' => isset($request->days) && $request->days != "NULL" ? $request->days : NULL,
                    'since' => isset($request->since) && $request->since != "NULL" ? $request->since : NULL,  
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'year' => isset($request->year) && $request->year != "NULL" ? $request->year : NULL,
                    'month' => isset($request->month) && $request->month != "NULL" ? $request->month : NULL,
                    'tdate' => isset($request->tdate) && $request->tdate != "NULL" ? $request->tdate : NULL,
                    'service' => isset($request->service) && $request->service != "NULL" ? $request->service : NULL,
                    'client' => isset($request->client) && $request->client != "NULL" ? $request->client : NULL,
                    'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                    'cdate' => isset($request->cdate) && $request->cdate != "NULL" ? $request->cdate : NULL,
                    'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                    'ins' => isset($request->ins) && $request->ins != "NULL" ? $request->ins : NULL,
                    'client_paid' => isset($request->client_paid) && $request->client_paid != "NULL" ? $request->client_paid : NULL,
                    'adj' => isset($request->adj) && $request->adj != "NULL" ? $request->adj : NULL,
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


} // Main Close
