<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\AopsPreAuthVerification;
use App\Models\AopsPreAuthVerificationDuplicates;
use App\Models\NmNcgVob;
use App\Models\NmNcgVobDuplicates;
use App\Models\RhEligibilityVerification;
use App\Models\RhEligibilityVerificationDuplicates;
use App\Models\AggPreAuthVerification;
use App\Models\AggPreAuthVerificationDuplicates;
use App\Models\RcmEvVob;
use App\Models\RcmEvVobDuplicates;
use App\Models\PmgAr;
use App\Models\PmgArDuplicates;
use App\Models\IhAr;
use App\Models\IhArDuplicates;
use App\Models\MsChargeEntry;
use App\Models\MsChargeEntryDuplicates;
use App\Models\DkmgChargeEntry;
use App\Models\DkmgChargeEntryDuplicates;
use App\Models\SmhcEvVob;
use App\Models\SmhcEvVobDuplicates;
use App\Models\LastsChargeEntry;
use App\Models\LastsChargeEntryDuplicates;
use App\Models\MosiAr;
use App\Models\MosiArDuplicates;
use App\Models\RlmgAr;
use App\Models\RlmgArDuplicates;
use App\Models\OscotrAr;
use App\Models\OscotrArDuplicates;
use App\Models\MpmsAr;
use App\Models\MpmsArDuplicates;
use App\Models\GaAr;
use App\Models\GaArDuplicates;
use App\Models\CrmcAr;
use App\Models\CrmcArDuplicates;
use App\Models\SmmiArDenver;
use App\Models\SmmiArDenverDuplicates;
use App\Models\TqhsArDenials;
use App\Models\TqhsArDenialsDuplicates;
use App\Models\TqhsArNoResponseTfl;
use App\Models\TqhsArNoResponseTflDuplicates;
use App\Models\TqhsArRhcNoResponse;
use App\Models\TqhsArRhcNoResponseDuplicates;
use App\Models\TqhsArRhcDenials;
use App\Models\TqhsArRhcDenialsDuplicates;
use App\Models\DkmgArBehavioralMentalHealth;
use App\Models\DkmgArBehavioralMentalHealthDuplicates;
class ProjectAuthAutomationController extends Controller
{
    public function aopsPreAuthVerification(Request $request)
    {
        try {
            $attributes = [
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'names' => isset($request->names) && $request->names != "NULL" ? $request->names : NULL,                 
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];         

            $duplicateRecordExisting  =  AopsPreAuthVerification::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                AopsPreAuthVerification::insert([
                    'mode_of_request' => isset($request->mode_of_request) && $request->mode_of_request != "NULL" ? $request->mode_of_request : NULL,
                    'request_date' => isset($request->request_date) && $request->request_date != "NULL" ? $request->request_date : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'services' => isset($request->services) && $request->services != "NULL" ? $request->services : NULL,
                    'names' => isset($request->names) && $request->names != "NULL" ? $request->names : NULL,
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                    'insurance_1' => isset($request->insurance_1) && $request->insurance_1 != "NULL" ? $request->insurance_1 : NULL,
                    'insurance_2' => isset($request->insurance_2) && $request->insurance_2 != "NULL" ? $request->insurance_2 : NULL,
                    'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                    'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                    'initial_worked_date' => isset($request->initial_worked_date) && $request->initial_worked_date != "NULL" ? $request->initial_worked_date : NULL,
                    'last_follow_up_status' => isset($request->last_follow_up_status) && $request->last_follow_up_status != "NULL" ? $request->last_follow_up_status : NULL,
                    'last_worked_date' => isset($request->last_worked_date) && $request->last_worked_date != "NULL" ? $request->last_worked_date : NULL,
                    'next_f_u_date' => isset($request->next_f_u_date) && $request->next_f_u_date != "NULL" ? $request->next_f_u_date : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  AopsPreAuthVerification::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'mode_of_request' => isset($request->mode_of_request) && $request->mode_of_request != "NULL" ? $request->mode_of_request : NULL,
                        'request_date' => isset($request->request_date) && $request->request_date != "NULL" ? $request->request_date : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'services' => isset($request->services) && $request->services != "NULL" ? $request->services : NULL,
                        'names' => isset($request->names) && $request->names != "NULL" ? $request->names : NULL,
                        'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                        'insurance_1' => isset($request->insurance_1) && $request->insurance_1 != "NULL" ? $request->insurance_1 : NULL,
                        'insurance_2' => isset($request->insurance_2) && $request->insurance_2 != "NULL" ? $request->insurance_2 : NULL,
                        'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                        'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                        'initial_worked_date' => isset($request->initial_worked_date) && $request->initial_worked_date != "NULL" ? $request->initial_worked_date : NULL,
                        'last_follow_up_status' => isset($request->last_follow_up_status) && $request->last_follow_up_status != "NULL" ? $request->last_follow_up_status : NULL,
                        'last_worked_date' => isset($request->last_worked_date) && $request->last_worked_date != "NULL" ? $request->last_worked_date : NULL,
                        'next_f_u_date' => isset($request->next_f_u_date) && $request->next_f_u_date != "NULL" ? $request->next_f_u_date : NULL,
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
    public function aopsPreAuthVerificationDuplicates(Request $request)
    {
        try {
            AopsPreAuthVerificationDuplicates::insert([
                'mode_of_request' => isset($request->mode_of_request) && $request->mode_of_request != "NULL" ? $request->mode_of_request : NULL,
                'request_date' => isset($request->request_date) && $request->request_date != "NULL" ? $request->request_date : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'services' => isset($request->services) && $request->services != "NULL" ? $request->services : NULL,
                'names' => isset($request->names) && $request->names != "NULL" ? $request->names : NULL,
                'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                'insurance_1' => isset($request->insurance_1) && $request->insurance_1 != "NULL" ? $request->insurance_1 : NULL,
                'insurance_2' => isset($request->insurance_2) && $request->insurance_2 != "NULL" ? $request->insurance_2 : NULL,
                'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                'initial_worked_date' => isset($request->initial_worked_date) && $request->initial_worked_date != "NULL" ? $request->initial_worked_date : NULL,
                'last_follow_up_status' => isset($request->last_follow_up_status) && $request->last_follow_up_status != "NULL" ? $request->last_follow_up_status : NULL,
                'last_worked_date' => isset($request->last_worked_date) && $request->last_worked_date != "NULL" ? $request->last_worked_date : NULL,
                'next_f_u_date' => isset($request->next_f_u_date) && $request->next_f_u_date != "NULL" ? $request->next_f_u_date : NULL,
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
    public function NcgMedicalNcgVob(Request $request)
    {
        try {
            
            $attributes = [
                'urgency' => isset($request->urgency) && $request->urgency != "NULL" ? $request->urgency : NULL,
                'queue_time' => isset($request->queue_time) && $request->queue_time != "NULL" ? $request->queue_time : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                'st' => isset($request->st) && $request->st != "NULL" ? $request->st : NULL,
                'policy' => isset($request->policy) && $request->policy != "NULL" ? $request->policy : NULL,
                'cgroup' => isset($request->cgroup) && $request->cgroup != "NULL" ? $request->cgroup : NULL,
                'source' => isset($request->source) && $request->source != "NULL" ? $request->source : NULL,   
                'comm' => isset($request->comm) && $request->comm != "NULL" ? $request->comm : NULL,  
                'at' => isset($request->at) && $request->at != "NULL" ? $request->at : NULL,  
                'benefits' => isset($request->benefits) && $request->benefits != "NULL" ? $request->benefits : NULL,  
                'invoke_date' => carbon::now()->format('Y-m-d')
            ];

            $duplicateRecordExisting  =  NmNcgVob::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                NmNcgVob::insert([
                    'urgency' => isset($request->urgency) && $request->urgency != "NULL" ? $request->urgency : NULL,
                    'queue_time' => isset($request->queue_time) && $request->queue_time != "NULL" ? $request->queue_time : NULL,
                    'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                    'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                    'st' => isset($request->st) && $request->st != "NULL" ? $request->st : NULL,
                    'policy' => isset($request->policy) && $request->policy != "NULL" ? $request->policy : NULL,
                    'cgroup' => isset($request->cgroup) && $request->cgroup != "NULL" ? $request->cgroup : NULL,
                    'source' => isset($request->source) && $request->source != "NULL" ? $request->source : NULL,   
                    'comm' => isset($request->comm) && $request->comm != "NULL" ? $request->comm : NULL,  
                    'at' => isset($request->at) && $request->at != "NULL" ? $request->at : NULL,  
                    'benefits' => isset($request->benefits) && $request->benefits != "NULL" ? $request->benefits : NULL,    
                   'invoke_date' => date('Y-m-d'),
                   'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                   'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                   'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  NmNcgVob::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'urgency' => isset($request->urgency) && $request->urgency != "NULL" ? $request->urgency : NULL,
                        'queue_time' => isset($request->queue_time) && $request->queue_time != "NULL" ? $request->queue_time : NULL,
                        'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                        'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                        'st' => isset($request->st) && $request->st != "NULL" ? $request->st : NULL,
                        'policy' => isset($request->policy) && $request->policy != "NULL" ? $request->policy : NULL,
                        'cgroup' => isset($request->cgroup) && $request->cgroup != "NULL" ? $request->cgroup : NULL,
                        'source' => isset($request->source) && $request->source != "NULL" ? $request->source : NULL,   
                        'comm' => isset($request->comm) && $request->comm != "NULL" ? $request->comm : NULL,  
                        'at' => isset($request->at) && $request->at != "NULL" ? $request->at : NULL,  
                        'benefits' => isset($request->benefits) && $request->benefits != "NULL" ? $request->benefits : NULL,  
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
    public function NcgMedicalNcgVobDuplicates(Request $request)
    {
        try {
            NmNcgVobDuplicates::insert([
                'urgency' => isset($request->urgency) && $request->urgency != "NULL" ? $request->urgency : NULL,
                'queue_time' => isset($request->queue_time) && $request->queue_time != "NULL" ? $request->queue_time : NULL,
                'status' => isset($request->status) && $request->status != "NULL" ? $request->status : NULL,
                'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                'st' => isset($request->st) && $request->st != "NULL" ? $request->st : NULL,
                'policy' => isset($request->policy) && $request->policy != "NULL" ? $request->policy : NULL,
                'cgroup' => isset($request->cgroup) && $request->cgroup != "NULL" ? $request->cgroup : NULL,
                'source' => isset($request->source) && $request->source != "NULL" ? $request->source : NULL,   
                'comm' => isset($request->comm) && $request->comm != "NULL" ? $request->comm : NULL,  
                'at' => isset($request->at) && $request->at != "NULL" ? $request->at : NULL,  
                'benefits' => isset($request->benefits) && $request->benefits != "NULL" ? $request->benefits : NULL,  
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

    public function rhEligibilityVerification(Request $request)
    {
        try {
            
            $attributes = [
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'examroom' => isset($request->examroom) && $request->examroom != "NULL" ? $request->examroom : NULL,
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                'primary_insurer_name' => isset($request->primary_insurer_name) && $request->primary_insurer_name != "NULL" ? $request->primary_insurer_name : NULL,
                'secondary_insurer_name' => isset($request->secondary_insurer_name) && $request->secondary_insurer_name != "NULL" ? $request->secondary_insurer_name : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
            ];

            $duplicateRecordExisting  =  RhEligibilityVerification::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                RhEligibilityVerification::insert([
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'examroom' => isset($request->examroom) && $request->examroom != "NULL" ? $request->examroom : NULL,
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                    'primary_insurer_name' => isset($request->primary_insurer_name) && $request->primary_insurer_name != "NULL" ? $request->primary_insurer_name : NULL,
                    'secondary_insurer_name' => isset($request->secondary_insurer_name) && $request->secondary_insurer_name != "NULL" ? $request->secondary_insurer_name : NULL,
                    'teritary_insurer_name' => isset($request->teritary_insurer_name) && $request->teritary_insurer_name != "NULL" ? $request->teritary_insurer_name : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  RhEligibilityVerification::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'examroom' => isset($request->examroom) && $request->examroom != "NULL" ? $request->examroom : NULL,
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                        'primary_insurer_name' => isset($request->primary_insurer_name) && $request->primary_insurer_name != "NULL" ? $request->primary_insurer_name : NULL,
                        'secondary_insurer_name' => isset($request->secondary_insurer_name) && $request->secondary_insurer_name != "NULL" ? $request->secondary_insurer_name : NULL,
                        'teritary_insurer_name' => isset($request->teritary_insurer_name) && $request->teritary_insurer_name != "NULL" ? $request->teritary_insurer_name : NULL,
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
    public function rhEligibilityVerificationDuplicates(Request $request)
    {
        try {
            RhEligibilityVerificationDuplicates::insert([
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'examroom' => isset($request->examroom) && $request->examroom != "NULL" ? $request->examroom : NULL,
                'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                'primary_insurer_name' => isset($request->primary_insurer_name) && $request->primary_insurer_name != "NULL" ? $request->primary_insurer_name : NULL,
                'secondary_insurer_name' => isset($request->secondary_insurer_name) && $request->secondary_insurer_name != "NULL" ? $request->secondary_insurer_name : NULL,
                'teritary_insurer_name' => isset($request->teritary_insurer_name) && $request->teritary_insurer_name != "NULL" ? $request->teritary_insurer_name : NULL,
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

    public function aggPreAuthVerification(Request $request)
    {
        try {
            
            $attributes = [
                'first_name' => isset($request->first_name) && $request->first_name != "NULL" ? $request->first_name : NULL,
                'last_name' => isset($request->last_name) && $request->last_name != "NULL" ? $request->last_name : NULL,
                'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'appt_type' => isset($request->appt_type) && $request->appt_type != "NULL" ? $request->appt_type : NULL,
                'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                'primary_insurance_name' => isset($request->primary_insurance_name) && $request->primary_insurance_name != "NULL" ? $request->primary_insurance_name : NULL,
                'primary_ins_subscriber_no' => isset($request->primary_ins_subscriber_no) && $request->primary_ins_subscriber_no != "NULL" ? $request->primary_ins_subscriber_no : NULL,
                'plan_name' => isset($request->plan_name) && $request->plan_name != "NULL" ? $request->plan_name : NULL,
                'network_status' => isset($request->network_status) && $request->network_status != "NULL" ? $request->network_status : NULL,
                'secondary_insurance' => isset($request->secondary_insurance) && $request->secondary_insurance != "NULL" ? $request->secondary_insurance : NULL,
                'secondary_member_id' => isset($request->secondary_member_id) && $request->secondary_member_id != "NULL" ? $request->secondary_member_id : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
            ];

            $duplicateRecordExisting  =  AggPreAuthVerification::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                AggPreAuthVerification::insert([
                    'first_name' => isset($request->first_name) && $request->first_name != "NULL" ? $request->first_name : NULL,
                    'last_name' => isset($request->last_name) && $request->last_name != "NULL" ? $request->last_name : NULL,
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'appt_type' => isset($request->appt_type) && $request->appt_type != "NULL" ? $request->appt_type : NULL,
                    'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                    'primary_insurance_name' => isset($request->primary_insurance_name) && $request->primary_insurance_name != "NULL" ? $request->primary_insurance_name : NULL,
                    'primary_ins_subscriber_no' => isset($request->primary_ins_subscriber_no) && $request->primary_ins_subscriber_no != "NULL" ? $request->primary_ins_subscriber_no : NULL,
                    'plan_name' => isset($request->plan_name) && $request->plan_name != "NULL" ? $request->plan_name : NULL,
                    'network_status' => isset($request->network_status) && $request->network_status != "NULL" ? $request->network_status : NULL,
                    'secondary_insurance' => isset($request->secondary_insurance) && $request->secondary_insurance != "NULL" ? $request->secondary_insurance : NULL,
                    'secondary_member_id' => isset($request->secondary_member_id) && $request->secondary_member_id != "NULL" ? $request->secondary_member_id : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  AggPreAuthVerification::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'first_name' => isset($request->first_name) && $request->first_name != "NULL" ? $request->first_name : NULL,
                        'last_name' => isset($request->last_name) && $request->last_name != "NULL" ? $request->last_name : NULL,
                        'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'appt_type' => isset($request->appt_type) && $request->appt_type != "NULL" ? $request->appt_type : NULL,
                        'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                        'primary_insurance_name' => isset($request->primary_insurance_name) && $request->primary_insurance_name != "NULL" ? $request->primary_insurance_name : NULL,
                        'primary_ins_subscriber_no' => isset($request->primary_ins_subscriber_no) && $request->primary_ins_subscriber_no != "NULL" ? $request->primary_ins_subscriber_no : NULL,
                        'plan_name' => isset($request->plan_name) && $request->plan_name != "NULL" ? $request->plan_name : NULL,
                        'network_status' => isset($request->network_status) && $request->network_status != "NULL" ? $request->network_status : NULL,
                        'secondary_insurance' => isset($request->secondary_insurance) && $request->secondary_insurance != "NULL" ? $request->secondary_insurance : NULL,
                        'secondary_member_id' => isset($request->secondary_member_id) && $request->secondary_member_id != "NULL" ? $request->secondary_member_id : NULL,
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
    public function aggPreAuthVerificationDuplicates(Request $request)
    {
        try {
            AggPreAuthVerificationDuplicates::insert([
                'first_name' => isset($request->first_name) && $request->first_name != "NULL" ? $request->first_name : NULL,
                'last_name' => isset($request->last_name) && $request->last_name != "NULL" ? $request->last_name : NULL,
                'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'appt_type' => isset($request->appt_type) && $request->appt_type != "NULL" ? $request->appt_type : NULL,
                'provider_name' => isset($request->provider_name) && $request->provider_name != "NULL" ? $request->provider_name : NULL,
                'primary_insurance_name' => isset($request->primary_insurance_name) && $request->primary_insurance_name != "NULL" ? $request->primary_insurance_name : NULL,
                'primary_ins_subscriber_no' => isset($request->primary_ins_subscriber_no) && $request->primary_ins_subscriber_no != "NULL" ? $request->primary_ins_subscriber_no : NULL,
                'plan_name' => isset($request->plan_name) && $request->plan_name != "NULL" ? $request->plan_name : NULL,
                'network_status' => isset($request->network_status) && $request->network_status != "NULL" ? $request->network_status : NULL,
                'secondary_insurance' => isset($request->secondary_insurance) && $request->secondary_insurance != "NULL" ? $request->secondary_insurance : NULL,
                'secondary_member_id' => isset($request->secondary_member_id) && $request->secondary_member_id != "NULL" ? $request->secondary_member_id : NULL,
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
    public function rcmEvVob(Request $request)
    {
        try {
            
            $attributes = [
                'order_date' => isset($request->order_date) && $request->order_date != "NULL" ? $request->order_date : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'labs_imaging_procedures' => isset($request->labs_imaging_procedures) && $request->labs_imaging_procedures != "NULL" ? $request->labs_imaging_procedures : NULL,
                'reason' => isset($request->reason) && $request->reason != "NULL" ? $request->reason : NULL,
                'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,
                'received_date' => isset($request->received_date) && $request->received_date != "NULL" ? $request->received_date : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
            ];

            $duplicateRecordExisting  =  RcmEvVob::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                RcmEvVob::insert([
                    'order_date' => isset($request->order_date) && $request->order_date != "NULL" ? $request->order_date : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'labs_imaging_procedures' => isset($request->labs_imaging_procedures) && $request->labs_imaging_procedures != "NULL" ? $request->labs_imaging_procedures : NULL,
                    'reason' => isset($request->reason) && $request->reason != "NULL" ? $request->reason : NULL,
                    'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,
                    'received_date' => isset($request->received_date) && $request->received_date != "NULL" ? $request->received_date : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  RcmEvVob::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'order_date' => isset($request->order_date) && $request->order_date != "NULL" ? $request->order_date : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'labs_imaging_procedures' => isset($request->labs_imaging_procedures) && $request->labs_imaging_procedures != "NULL" ? $request->labs_imaging_procedures : NULL,
                        'reason' => isset($request->reason) && $request->reason != "NULL" ? $request->reason : NULL,
                        'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,
                        'received_date' => isset($request->received_date) && $request->received_date != "NULL" ? $request->received_date : NULL,
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
    public function rcmEvVobDuplicates(Request $request)
    {
        try {
            RcmEvVobDuplicates::insert([
                'order_date' => isset($request->order_date) && $request->order_date != "NULL" ? $request->order_date : NULL,
                'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                'labs_imaging_procedures' => isset($request->labs_imaging_procedures) && $request->labs_imaging_procedures != "NULL" ? $request->labs_imaging_procedures : NULL,
                'reason' => isset($request->reason) && $request->reason != "NULL" ? $request->reason : NULL,
                'schedule_date' => isset($request->schedule_date) && $request->schedule_date != "NULL" ? $request->schedule_date : NULL,
                'received_date' => isset($request->received_date) && $request->received_date != "NULL" ? $request->received_date : NULL,
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

    public function premierMedicalGroupAr(Request $request)
    {
        try {
            
            $attributes = [
                'c_type' => isset($request->c_type) && $request->c_type != "NULL" ? $request->c_type : NULL,
                'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                'mrn' => isset($request->mrn) && $request->mrn != "NULL" ? $request->mrn : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
                'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                'perf_prov' => isset($request->perf_prov) && $request->perf_prov != "NULL" ? $request->perf_prov : NULL,
                'billing_prov' => isset($request->billing_prov) && $request->billing_prov != "NULL" ? $request->billing_prov : NULL,
                'bill_number' => isset($request->bill_number) && $request->bill_number != "NULL" ? $request->bill_number : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
            ];

            $duplicateRecordExisting  =  PmgAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                PmgAr::insert([
                    'c_type' => isset($request->c_type) && $request->c_type != "NULL" ? $request->c_type : NULL,
                    'c_status' => isset($request->c_status) && $request->c_status != "NULL" ? $request->c_status : NULL,
                    'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                    'mrn' => isset($request->mrn) && $request->mrn != "NULL" ? $request->mrn : NULL,
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                    'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
                    'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                    'perf_prov' => isset($request->perf_prov) && $request->perf_prov != "NULL" ? $request->perf_prov : NULL,
                    'billing_prov' => isset($request->billing_prov) && $request->billing_prov != "NULL" ? $request->billing_prov : NULL,
                    'bill_number' => isset($request->bill_number) && $request->bill_number != "NULL" ? $request->bill_number : NULL,
                    'invoke_date' => date('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  PmgAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'c_type' => isset($request->c_type) && $request->c_type != "NULL" ? $request->c_type : NULL,
                        'c_status' => isset($request->c_status) && $request->c_status != "NULL" ? $request->c_status : NULL,
                        'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                        'mrn' => isset($request->mrn) && $request->mrn != "NULL" ? $request->mrn : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
                        'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                        'perf_prov' => isset($request->perf_prov) && $request->perf_prov != "NULL" ? $request->perf_prov : NULL,
                        'billing_prov' => isset($request->billing_prov) && $request->billing_prov != "NULL" ? $request->billing_prov : NULL,
                        'bill_number' => isset($request->bill_number) && $request->bill_number != "NULL" ? $request->bill_number : NULL,
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
    public function premierMedicalGroupArDuplicates(Request $request)
    {
        try {
            PmgArDuplicates::insert([
                'c_type' => isset($request->c_type) && $request->c_type != "NULL" ? $request->c_type : NULL,
                'c_status' => isset($request->c_status) && $request->c_status != "NULL" ? $request->c_status : NULL,
                'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                'mrn' => isset($request->mrn) && $request->mrn != "NULL" ? $request->mrn : NULL,
                'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,
                'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                'bucket' => isset($request->bucket) && $request->bucket != "NULL" ? $request->bucket : NULL,
                'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                'perf_prov' => isset($request->perf_prov) && $request->perf_prov != "NULL" ? $request->perf_prov : NULL,
                'billing_prov' => isset($request->billing_prov) && $request->billing_prov != "NULL" ? $request->billing_prov : NULL,
                'bill_number' => isset($request->bill_number) && $request->bill_number != "NULL" ? $request->bill_number : NULL,
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

    public function integrisHealthAr(Request $request)
    {
        try {
            $attributes = [
                 'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                 'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,
                 'cstm_ins_grpng' => isset($request->cstm_ins_grpng) && $request->cstm_ins_grpng != "NULL" ? $request->cstm_ins_grpng : NULL,
                 'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                 'ins_report_cat' => isset($request->ins_report_cat) && $request->ins_report_cat != "NULL" ? $request->ins_report_cat : NULL,
                 'ins_pkg_type' => isset($request->ins_pkg_type) && $request->ins_pkg_type != "NULL" ? $request->ins_pkg_type : NULL,
                 'rndrng_prvdr' => isset($request->rndrng_prvdr) && $request->rndrng_prvdr != "NULL" ? $request->rndrng_prvdr : NULL,
                 'svc_dprtmnt' => isset($request->svc_dprtmnt) && $request->svc_dprtmnt != "NULL" ? $request->svc_dprtmnt : NULL,
                 'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,
                 'srv_bucket_total' => isset($request->srv_bucket_total) && $request->srv_bucket_total != "NULL" ? $request->srv_bucket_total : NULL,
                 'istaction_date' => isset($request->istaction_date) && $request->istaction_date != "NULL" ? $request->istaction_date : NULL,
                 'trnsfr_type' => isset($request->trnsfr_type) && $request->trnsfr_type != "NULL" ? $request->trnsfr_type : NULL,
                 'c_status' => isset($request->c_status) && $request->c_status != "NULL" ? $request->c_status : NULL,
                 'invoke_date' => carbon::now()->format('Y-m-d')
             ];         

            $duplicateRecordExisting  =  IhAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                   IhAr::insert([
                        'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                        'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,
                        'cstm_ins_grpng' => isset($request->cstm_ins_grpng) && $request->cstm_ins_grpng != "NULL" ? $request->cstm_ins_grpng : NULL,
                        'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                        'ins_report_cat' => isset($request->ins_report_cat) && $request->ins_report_cat != "NULL" ? $request->ins_report_cat : NULL,
                        'ins_pkg_type' => isset($request->ins_pkg_type) && $request->ins_pkg_type != "NULL" ? $request->ins_pkg_type : NULL,
                        'rndrng_prvdr' => isset($request->rndrng_prvdr) && $request->rndrng_prvdr != "NULL" ? $request->rndrng_prvdr : NULL,
                        'svc_dprtmnt' => isset($request->svc_dprtmnt) && $request->svc_dprtmnt != "NULL" ? $request->svc_dprtmnt : NULL,
                        'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,
                        'srv_bucket_total' => isset($request->srv_bucket_total) && $request->srv_bucket_total != "NULL" ? $request->srv_bucket_total : NULL,
                        'istaction_date' => isset($request->istaction_date) && $request->istaction_date != "NULL" ? $request->istaction_date : NULL,
                        'trnsfr_type' => isset($request->trnsfr_type) && $request->trnsfr_type != "NULL" ? $request->trnsfr_type : NULL,
                        'c_status' => isset($request->c_status) && $request->c_status != "NULL" ? $request->c_status : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  IhAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                        'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,
                        'cstm_ins_grpng' => isset($request->cstm_ins_grpng) && $request->cstm_ins_grpng != "NULL" ? $request->cstm_ins_grpng : NULL,
                        'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                        'ins_report_cat' => isset($request->ins_report_cat) && $request->ins_report_cat != "NULL" ? $request->ins_report_cat : NULL,
                        'ins_pkg_type' => isset($request->ins_pkg_type) && $request->ins_pkg_type != "NULL" ? $request->ins_pkg_type : NULL,
                        'rndrng_prvdr' => isset($request->rndrng_prvdr) && $request->rndrng_prvdr != "NULL" ? $request->rndrng_prvdr : NULL,
                        'svc_dprtmnt' => isset($request->svc_dprtmnt) && $request->svc_dprtmnt != "NULL" ? $request->svc_dprtmnt : NULL,
                        'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,
                        'srv_bucket_total' => isset($request->srv_bucket_total) && $request->srv_bucket_total != "NULL" ? $request->srv_bucket_total : NULL,
                        'istaction_date' => isset($request->istaction_date) && $request->istaction_date != "NULL" ? $request->istaction_date : NULL,
                        'trnsfr_type' => isset($request->trnsfr_type) && $request->trnsfr_type != "NULL" ? $request->trnsfr_type : NULL,
                        'c_status' => isset($request->c_status) && $request->c_status != "NULL" ? $request->c_status : NULL,
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
    public function integrisHealthArDuplicates(Request $request)
    {
        try {
               IhArDuplicates::insert([
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                    'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,
                    'cstm_ins_grpng' => isset($request->cstm_ins_grpng) && $request->cstm_ins_grpng != "NULL" ? $request->cstm_ins_grpng : NULL,
                    'ins_pkg_name' => isset($request->ins_pkg_name) && $request->ins_pkg_name != "NULL" ? $request->ins_pkg_name : NULL,
                    'ins_report_cat' => isset($request->ins_report_cat) && $request->ins_report_cat != "NULL" ? $request->ins_report_cat : NULL,
                    'ins_pkg_type' => isset($request->ins_pkg_type) && $request->ins_pkg_type != "NULL" ? $request->ins_pkg_type : NULL,
                    'rndrng_prvdr' => isset($request->rndrng_prvdr) && $request->rndrng_prvdr != "NULL" ? $request->rndrng_prvdr : NULL,
                    'svc_dprtmnt' => isset($request->svc_dprtmnt) && $request->svc_dprtmnt != "NULL" ? $request->svc_dprtmnt : NULL,
                    'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,
                    'srv_bucket_total' => isset($request->srv_bucket_total) && $request->srv_bucket_total != "NULL" ? $request->srv_bucket_total : NULL,
                    'istaction_date' => isset($request->istaction_date) && $request->istaction_date != "NULL" ? $request->istaction_date : NULL,
                    'trnsfr_type' => isset($request->trnsfr_type) && $request->trnsfr_type != "NULL" ? $request->trnsfr_type : NULL,
                    'c_status' => isset($request->c_status) && $request->c_status != "NULL" ? $request->c_status : NULL,
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
    public function msChargeEntry(Request $request)
    {
        try {
            $attributes = [
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                 'primary_insurer_name' => isset($request->primary_insurer_name) && $request->primary_insurer_name != "NULL" ? $request->primary_insurer_name : NULL,
                 'secondary_insurer_name' => isset($request->secondary_insurer_name) && $request->secondary_insurer_name != "NULL" ? $request->secondary_insurer_name : NULL,
                 'rate' => isset($request->rate) && $request->rate != "NULL" ? $request->rate : NULL,
                 'invoke_date' => carbon::now()->format('Y-m-d')
             ];         

            $duplicateRecordExisting  =  MsChargeEntry::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                MsChargeEntry::insert([
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                        'primary_insurer_name' => isset($request->primary_insurer_name) && $request->primary_insurer_name != "NULL" ? $request->primary_insurer_name : NULL,
                        'secondary_insurer_name' => isset($request->secondary_insurer_name) && $request->secondary_insurer_name != "NULL" ? $request->secondary_insurer_name : NULL,
                        'rate' => isset($request->rate) && $request->rate != "NULL" ? $request->rate : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  MsChargeEntry::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                        'primary_insurer_name' => isset($request->primary_insurer_name) && $request->primary_insurer_name != "NULL" ? $request->primary_insurer_name : NULL,
                        'secondary_insurer_name' => isset($request->secondary_insurer_name) && $request->secondary_insurer_name != "NULL" ? $request->secondary_insurer_name : NULL,
                        'rate' => isset($request->rate) && $request->rate != "NULL" ? $request->rate : NULL,
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
    public function msChargeEntryDuplicates(Request $request)
    {
        try {
                MsChargeEntryDuplicates::insert([
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                    'primary_insurer_name' => isset($request->primary_insurer_name) && $request->primary_insurer_name != "NULL" ? $request->primary_insurer_name : NULL,
                    'secondary_insurer_name' => isset($request->secondary_insurer_name) && $request->secondary_insurer_name != "NULL" ? $request->secondary_insurer_name : NULL,
                    'rate' => isset($request->rate) && $request->rate != "NULL" ? $request->rate : NULL,
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

    public function dkmgChargeEntry(Request $request)
    {
        try {
            $attributes = [
                 'slip' => isset($request->slip) && $request->slip != "NULL" ? $request->slip : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                 'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                 'department' => isset($request->department) && $request->department != "NULL" ? $request->department : NULL,
                 'appointment_type' => isset($request->appointment_type) && $request->appointment_type != "NULL" ? $request->appointment_type : NULL,
                 'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                 'invoke_date' => carbon::now()->format('Y-m-d')
             ];         

            $duplicateRecordExisting  =  DkmgChargeEntry::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                DkmgChargeEntry::insert([
                        'slip' => isset($request->slip) && $request->slip != "NULL" ? $request->slip : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                        'department' => isset($request->department) && $request->department != "NULL" ? $request->department : NULL,
                        'appointment_type' => isset($request->appointment_type) && $request->appointment_type != "NULL" ? $request->appointment_type : NULL,
                        'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  DkmgChargeEntry::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'slip' => isset($request->slip) && $request->slip != "NULL" ? $request->slip : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                        'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                        'department' => isset($request->department) && $request->department != "NULL" ? $request->department : NULL,
                        'appointment_type' => isset($request->appointment_type) && $request->appointment_type != "NULL" ? $request->appointment_type : NULL,
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
    public function dkmgChargeEntryDuplicates(Request $request)
    {
        try {
                DkmgChargeEntryDuplicates::insert([
                    'slip' => isset($request->slip) && $request->slip != "NULL" ? $request->slip : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                    'provider' => isset($request->provider) && $request->provider != "NULL" ? $request->provider : NULL,
                    'department' => isset($request->department) && $request->department != "NULL" ? $request->department : NULL,
                    'appointment_type' => isset($request->appointment_type) && $request->appointment_type != "NULL" ? $request->appointment_type : NULL,
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

    public function smhcEvVob(Request $request)
    {
        try {
            $attributes = [
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                 'mrn' => isset($request->mrn) && $request->mrn != "NULL" ? $request->mrn : NULL,
                 'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                 'subscriber' => isset($request->subscriber) && $request->subscriber != "NULL" ? $request->subscriber : NULL,
                 'policy_id' => isset($request->policy_id) && $request->policy_id != "NULL" ? $request->policy_id : NULL,
                 'policy_start_date' => isset($request->policy_start_date) && $request->policy_start_date != "NULL" ? $request->policy_start_date : NULL,
                 'policy_end_date' => isset($request->policy_end_date) && $request->policy_end_date != "NULL" ? $request->policy_end_date : NULL,
                 'visit_date' => isset($request->visit_date) && $request->visit_date != "NULL" ? $request->visit_date : NULL,
                 'visit_time' => isset($request->visit_time) && $request->visit_time != "NULL" ? $request->visit_time : NULL,
                 'last_verification_date' => isset($request->last_verification_date) && $request->last_verification_date != "NULL" ? $request->last_verification_date : NULL,
                 'last_verification_time' => isset($request->last_verification_time) && $request->last_verification_time != "NULL" ? $request->last_verification_time : NULL,
                 'eligibility_status' => isset($request->eligibility_status) && $request->eligibility_status != "NULL" ? $request->eligibility_status : NULL,
                 'eligibility_response' => isset($request->eligibility_response) && $request->eligibility_response != "NULL" ? $request->eligibility_response : NULL,
                 'submitter_name' => isset($request->submitter_name) && $request->submitter_name != "NULL" ? $request->submitter_name : NULL,
                 'submitter_npi' => isset($request->submitter_npi) && $request->submitter_npi != "NULL" ? $request->submitter_npi : NULL,
                 'invoke_date' => carbon::now()->format('Y-m-d')
             ];         

            $duplicateRecordExisting  =  SmhcEvVob::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                SmhcEvVob::insert([
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                        'mrn' => isset($request->mrn) && $request->mrn != "NULL" ? $request->mrn : NULL,
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                        'subscriber' => isset($request->subscriber) && $request->subscriber != "NULL" ? $request->subscriber : NULL,
                        'policy_id' => isset($request->policy_id) && $request->policy_id != "NULL" ? $request->policy_id : NULL,
                        'policy_start_date' => isset($request->policy_start_date) && $request->policy_start_date != "NULL" ? $request->policy_start_date : NULL,
                        'policy_end_date' => isset($request->policy_end_date) && $request->policy_end_date != "NULL" ? $request->policy_end_date : NULL,
                        'visit_date' => isset($request->visit_date) && $request->visit_date != "NULL" ? $request->visit_date : NULL,
                        'visit_time' => isset($request->visit_time) && $request->visit_time != "NULL" ? $request->visit_time : NULL,
                        'last_verification_date' => isset($request->last_verification_date) && $request->last_verification_date != "NULL" ? $request->last_verification_date : NULL,
                        'last_verification_time' => isset($request->last_verification_time) && $request->last_verification_time != "NULL" ? $request->last_verification_time : NULL,
                        'eligibility_status' => isset($request->eligibility_status) && $request->eligibility_status != "NULL" ? $request->eligibility_status : NULL,
                        'eligibility_response' => isset($request->eligibility_response) && $request->eligibility_response != "NULL" ? $request->eligibility_response : NULL,
                        'submitter_name' => isset($request->submitter_name) && $request->submitter_name != "NULL" ? $request->submitter_name : NULL,
                        'submitter_npi' => isset($request->submitter_npi) && $request->submitter_npi != "NULL" ? $request->submitter_npi : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  SmhcEvVob::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                        'mrn' => isset($request->mrn) && $request->mrn != "NULL" ? $request->mrn : NULL,
                        'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                        'subscriber' => isset($request->subscriber) && $request->subscriber != "NULL" ? $request->subscriber : NULL,
                        'policy_id' => isset($request->policy_id) && $request->policy_id != "NULL" ? $request->policy_id : NULL,
                        'policy_start_date' => isset($request->policy_start_date) && $request->policy_start_date != "NULL" ? $request->policy_start_date : NULL,
                        'policy_end_date' => isset($request->policy_end_date) && $request->policy_end_date != "NULL" ? $request->policy_end_date : NULL,
                        'visit_date' => isset($request->visit_date) && $request->visit_date != "NULL" ? $request->visit_date : NULL,
                        'visit_time' => isset($request->visit_time) && $request->visit_time != "NULL" ? $request->visit_time : NULL,
                        'last_verification_date' => isset($request->last_verification_date) && $request->last_verification_date != "NULL" ? $request->last_verification_date : NULL,
                        'last_verification_time' => isset($request->last_verification_time) && $request->last_verification_time != "NULL" ? $request->last_verification_time : NULL,
                        'eligibility_status' => isset($request->eligibility_status) && $request->eligibility_status != "NULL" ? $request->eligibility_status : NULL,
                        'eligibility_response' => isset($request->eligibility_response) && $request->eligibility_response != "NULL" ? $request->eligibility_response : NULL,
                        'submitter_name' => isset($request->submitter_name) && $request->submitter_name != "NULL" ? $request->submitter_name : NULL,
                        'submitter_npi' => isset($request->submitter_npi) && $request->submitter_npi != "NULL" ? $request->submitter_npi : NULL,
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
    public function smhcEvVobDuplicates(Request $request)
    {
        try {
               SmhcEvVobDuplicates::insert([
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'dob' => isset($request->dob) && $request->dob != "NULL" ? $request->dob : NULL,
                    'mrn' => isset($request->mrn) && $request->mrn != "NULL" ? $request->mrn : NULL,
                    'payer' => isset($request->payer) && $request->payer != "NULL" ? $request->payer : NULL,
                    'subscriber' => isset($request->subscriber) && $request->subscriber != "NULL" ? $request->subscriber : NULL,
                    'policy_id' => isset($request->policy_id) && $request->policy_id != "NULL" ? $request->policy_id : NULL,
                    'policy_start_date' => isset($request->policy_start_date) && $request->policy_start_date != "NULL" ? $request->policy_start_date : NULL,
                    'policy_end_date' => isset($request->policy_end_date) && $request->policy_end_date != "NULL" ? $request->policy_end_date : NULL,
                    'visit_date' => isset($request->visit_date) && $request->visit_date != "NULL" ? $request->visit_date : NULL,
                    'visit_time' => isset($request->visit_time) && $request->visit_time != "NULL" ? $request->visit_time : NULL,
                    'last_verification_date' => isset($request->last_verification_date) && $request->last_verification_date != "NULL" ? $request->last_verification_date : NULL,
                    'last_verification_time' => isset($request->last_verification_time) && $request->last_verification_time != "NULL" ? $request->last_verification_time : NULL,
                    'eligibility_status' => isset($request->eligibility_status) && $request->eligibility_status != "NULL" ? $request->eligibility_status : NULL,
                    'eligibility_response' => isset($request->eligibility_response) && $request->eligibility_response != "NULL" ? $request->eligibility_response : NULL,
                    'submitter_name' => isset($request->submitter_name) && $request->submitter_name != "NULL" ? $request->submitter_name : NULL,
                    'submitter_npi' => isset($request->submitter_npi) && $request->submitter_npi != "NULL" ? $request->submitter_npi : NULL,
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

    public function lastsChargeEntry(Request $request)
    {
        try {
            $attributes = [
                 'c_id' => isset($request->c_id) && $request->c_id != "NULL" ? $request->c_id : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                 'therapist' => isset($request->therapist) && $request->therapist != "NULL" ? $request->therapist : NULL,
                 'pri' => isset($request->pri) && $request->pri != "NULL" ? $request->pri : NULL,
                 'proc' => isset($request->proc) && $request->proc != "NULL" ? $request->proc : NULL,
                 'units' => isset($request->units) && $request->units != "NULL" ? $request->units : NULL,
                 'loc' => isset($request->loc) && $request->loc != "NULL" ? $request->loc : NULL,
                 'rate' => isset($request->rate) && $request->rate != "NULL" ? $request->rate : NULL,
             ];         

            $duplicateRecordExisting  =  LastsChargeEntry::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                LastsChargeEntry::insert([
                        'c_id' => isset($request->c_id) && $request->c_id != "NULL" ? $request->c_id : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'therapist' => isset($request->therapist) && $request->therapist != "NULL" ? $request->therapist : NULL,
                        'pri' => isset($request->pri) && $request->pri != "NULL" ? $request->pri : NULL,
                        'proc' => isset($request->proc) && $request->proc != "NULL" ? $request->proc : NULL,
                        'units' => isset($request->units) && $request->units != "NULL" ? $request->units : NULL,
                        'loc' => isset($request->loc) && $request->loc != "NULL" ? $request->loc : NULL,
                        'rate' => isset($request->rate) && $request->rate != "NULL" ? $request->rate : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecords  =  LastsChargeEntry::where($attributes)->where('chart_status',"CE_Assigned")->get();            
                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                            'c_id' => isset($request->c_id) && $request->c_id != "NULL" ? $request->c_id : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                            'therapist' => isset($request->therapist) && $request->therapist != "NULL" ? $request->therapist : NULL,
                            'pri' => isset($request->pri) && $request->pri != "NULL" ? $request->pri : NULL,
                            'proc' => isset($request->proc) && $request->proc != "NULL" ? $request->proc : NULL,
                            'units' => isset($request->units) && $request->units != "NULL" ? $request->units : NULL,
                            'loc' => isset($request->loc) && $request->loc != "NULL" ? $request->loc : NULL,
                            'rate' => isset($request->rate) && $request->rate != "NULL" ? $request->rate : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    }
                     return response()->json(['message' => 'Existing Record Updated Successfully']);
                } else {
                        LastsChargeEntry::insert([
                            'c_id' => isset($request->c_id) && $request->c_id != "NULL" ? $request->c_id : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                            'therapist' => isset($request->therapist) && $request->therapist != "NULL" ? $request->therapist : NULL,
                            'pri' => isset($request->pri) && $request->pri != "NULL" ? $request->pri : NULL,
                            'proc' => isset($request->proc) && $request->proc != "NULL" ? $request->proc : NULL,
                            'units' => isset($request->units) && $request->units != "NULL" ? $request->units : NULL,
                            'loc' => isset($request->loc) && $request->loc != "NULL" ? $request->loc : NULL,
                            'rate' => isset($request->rate) && $request->rate != "NULL" ? $request->rate : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'chart_status' => "CE_Assigned",
                        ]);
                        return response()->json(['message' => 'Record Reinserted Successfully']);
                }
               
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function lastsChargeEntryDuplicates(Request $request)
    {
        try {
               LastsChargeEntryDuplicates::insert([
                    'c_id' => isset($request->c_id) && $request->c_id != "NULL" ? $request->c_id : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                    'therapist' => isset($request->therapist) && $request->therapist != "NULL" ? $request->therapist : NULL,
                    'pri' => isset($request->pri) && $request->pri != "NULL" ? $request->pri : NULL,
                    'proc' => isset($request->proc) && $request->proc != "NULL" ? $request->proc : NULL,
                    'units' => isset($request->units) && $request->units != "NULL" ? $request->units : NULL,
                    'loc' => isset($request->loc) && $request->loc != "NULL" ? $request->loc : NULL,
                    'rate' => isset($request->rate) && $request->rate != "NULL" ? $request->rate : NULL,
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

    public function medValueOffshoreSolutionsIncAr(Request $request)
    {
        try {
            $attributes = [
                 'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                 'responsible_payer' => isset($request->responsible_payer) && $request->responsible_payer != "NULL" ? $request->responsible_payer : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL,
                 'charge_amount' => isset($request->charge_amount) && $request->charge_amount != "NULL" ? $request->charge_amount : NULL,
                 'primary_claim_id' => isset($request->primary_claim_id) && $request->primary_claim_id != "NULL" ? $request->primary_claim_id : NULL,
                 'secondary_claim_id' => isset($request->secondary_claim_id) && $request->secondary_claim_id != "NULL" ? $request->secondary_claim_id : NULL,
             ];         

            $duplicateRecordExisting  =  MosiAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                    MosiAr::insert([
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'responsible_payer' => isset($request->responsible_payer) && $request->responsible_payer != "NULL" ? $request->responsible_payer : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL,
                        'charge_amount' => isset($request->charge_amount) && $request->charge_amount != "NULL" ? $request->charge_amount : NULL,
                        'primary_claim_id' => isset($request->primary_claim_id) && $request->primary_claim_id != "NULL" ? $request->primary_claim_id : NULL,
                        'secondary_claim_id' => isset($request->secondary_claim_id) && $request->secondary_claim_id != "NULL" ? $request->secondary_claim_id : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecord  =  MosiAr::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
                    $duplicateRecord->update([
                        'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                        'responsible_payer' => isset($request->responsible_payer) && $request->responsible_payer != "NULL" ? $request->responsible_payer : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL,
                        'charge_amount' => isset($request->charge_amount) && $request->charge_amount != "NULL" ? $request->charge_amount : NULL,
                        'primary_claim_id' => isset($request->primary_claim_id) && $request->primary_claim_id != "NULL" ? $request->primary_claim_id : NULL,
                        'secondary_claim_id' => isset($request->secondary_claim_id) && $request->secondary_claim_id != "NULL" ? $request->secondary_claim_id : NULL,
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
    public function medValueOffshoreSolutionsIncArDuplicates(Request $request)
    {
        try {
                MosiArDuplicates::insert([
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'responsible_payer' => isset($request->responsible_payer) && $request->responsible_payer != "NULL" ? $request->responsible_payer : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'total_balance' => isset($request->total_balance) && $request->total_balance != "NULL" ? $request->total_balance : NULL,
                    'charge_amount' => isset($request->charge_amount) && $request->charge_amount != "NULL" ? $request->charge_amount : NULL,
                    'primary_claim_id' => isset($request->primary_claim_id) && $request->primary_claim_id != "NULL" ? $request->primary_claim_id : NULL,
                    'secondary_claim_id' => isset($request->secondary_claim_id) && $request->secondary_claim_id != "NULL" ? $request->secondary_claim_id : NULL,
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

    public function rossLegacyMedicalGroupAr(Request $request)
    {
        try {
            $attributes = [
                 'visitid' => isset($request->visitid) && $request->visitid != "NULL" ? $request->visitid : NULL
             ];         

            $duplicateRecordExisting  =  RlmgAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                    RlmgAr::insert([
                        'visitid' => isset($request->visitid) && $request->visitid != "NULL" ? $request->visitid : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                        'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                        'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                        '1_to_30' => isset($request->onetothirty) && $request->onetothirty != "NULL" ? $request->onetothirty : NULL,
                        '31_to_60' => isset($request->thirtytosixty) && $request->thirtytosixty != "NULL" ? $request->thirtytosixty : NULL,
                        '61_to_90' => isset($request->sixtytoninty) && $request->sixtytoninty != "NULL" ? $request->sixtytoninty : NULL,
                        'greater_than_90' => isset($request->greaterthanninty) && $request->greaterthanninty != "NULL" ? $request->greaterthanninty : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,
                        'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecords  =  RlmgAr::where($attributes)->where('chart_status',"CE_Assigned")->get();
                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                            'visitid' => isset($request->visitid) && $request->visitid != "NULL" ? $request->visitid : NULL,
                            'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                            'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                            'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                            '1_to_30' => isset($request->onetothirty) && $request->onetothirty != "NULL" ? $request->onetothirty : NULL,
                            '31_to_60' => isset($request->thirtytosixty) && $request->thirtytosixty != "NULL" ? $request->thirtytosixty : NULL,
                            '61_to_90' => isset($request->sixtytoninty) && $request->sixtytoninty != "NULL" ? $request->sixtytoninty : NULL,
                            'greater_than_90' => isset($request->greaterthanninty) && $request->greaterthanninty != "NULL" ? $request->greaterthanninty : NULL,
                            'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                            'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,
                            'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    }
                    return response()->json(['message' => 'Existing Record Updated Successfully']);
                } else {
                     RlmgAr::insert([
                        'visitid' => isset($request->visitid) && $request->visitid != "NULL" ? $request->visitid : NULL,
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                        'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                        'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                        '1_to_30' => isset($request->onetothirty) && $request->onetothirty != "NULL" ? $request->onetothirty : NULL,
                        '31_to_60' => isset($request->thirtytosixty) && $request->thirtytosixty != "NULL" ? $request->thirtytosixty : NULL,
                        '61_to_90' => isset($request->sixtytoninty) && $request->sixtytoninty != "NULL" ? $request->sixtytoninty : NULL,
                        'greater_than_90' => isset($request->greaterthanninty) && $request->greaterthanninty != "NULL" ? $request->greaterthanninty : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,
                        'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                    return response()->json(['message' => 'Record reinserted Successfully']);
                }
                
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function rossLegacyMedicalGroupArArDuplicates(Request $request)
    {
        try {
                RlmgArDuplicates::insert([
                    'visitid' => isset($request->visitid) && $request->visitid != "NULL" ? $request->visitid : NULL,
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                    'insurance_name' => isset($request->insurance_name) && $request->insurance_name != "NULL" ? $request->insurance_name : NULL,
                    'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                    '1_to_30' => isset($request->onetothirty) && $request->onetothirty != "NULL" ? $request->onetothirty : NULL,
                    '31_to_60' => isset($request->thirtytosixty) && $request->thirtytosixty != "NULL" ? $request->thirtytosixty : NULL,
                    '61_to_90' => isset($request->sixtytoninty) && $request->sixtytoninty != "NULL" ? $request->sixtytoninty : NULL,
                    'greater_than_90' => isset($request->greaterthanninty) && $request->greaterthanninty != "NULL" ? $request->greaterthanninty : NULL,
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                    'value_bucket' => isset($request->value_bucket) && $request->value_bucket != "NULL" ? $request->value_bucket : NULL,
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

    public function orthopaedicSpineCenterOfTheRockiesAr(Request $request)
    {
        try {
            $attributes = [
                 'tk' => isset($request->tk) && $request->tk != "NULL" ? $request->tk : NULL,
                 'tk_balance' => isset($request->tk_balance) && $request->tk_balance != "NULL" ? $request->tk_balance : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
             ];         

            $duplicateRecordExisting  =  OscotrAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                   OscotrAr::insert([
                        'mrn' => isset($request->mrn) && $request->mrn != "NULL" ? $request->mrn : NULL,
                        'tk' => isset($request->tk) && $request->tk != "NULL" ? $request->tk : NULL,
                        'tk_balance' => isset($request->tk_balance) && $request->tk_balance != "NULL" ? $request->tk_balance : NULL,
                        'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'visit_owner' => isset($request->visit_owner) && $request->visit_owner != "NULL" ? $request->visit_owner : NULL,
                        'visit_description' => isset($request->visit_description) && $request->visit_description != "NULL" ? $request->visit_description : NULL,
                        'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                        'doctor' => isset($request->doctor) && $request->doctor != "NULL" ? $request->doctor : NULL,
                        'financial_class' => isset($request->financial_class) && $request->financial_class != "NULL" ? $request->financial_class : NULL,
                        'primary_insur_carrier' => isset($request->primary_insur_carrier) && $request->primary_insur_carrier != "NULL" ? $request->primary_insur_carrier : NULL,
                        'current_insur_carrier' => isset($request->current_insur_carrier) && $request->current_insur_carrier != "NULL" ? $request->current_insur_carrier : NULL,
                        'division' => isset($request->division) && $request->division != "NULL" ? $request->division : NULL,
                        'company' => isset($request->company) && $request->company != "NULL" ? $request->company : NULL,
                        'cus_payer_group' => isset($request->cus_payer_group) && $request->cus_payer_group != "NULL" ? $request->cus_payer_group : NULL,
                        'primary_insur_group' => isset($request->primary_insur_group) && $request->primary_insur_group != "NULL" ? $request->primary_insur_group : NULL,
                        'current_insur_group' => isset($request->current_insur_group) && $request->current_insur_group != "NULL" ? $request->current_insur_group : NULL,
                        'total_chrg' => isset($request->total_chrg) && $request->total_chrg != "NULL" ? $request->total_chrg : NULL,
                        'total_pmnt' => isset($request->total_pmnt) && $request->total_pmnt != "NULL" ? $request->total_pmnt : NULL,
                        'insur_pmnt' => isset($request->insur_pmnt) && $request->insur_pmnt != "NULL" ? $request->insur_pmnt : NULL,
                        'insur_pv_bal' => isset($request->insur_pv_bal) && $request->insur_pv_bal != "NULL" ? $request->insur_pv_bal : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecords  =  OscotrAr::where($attributes)->where('chart_status',"CE_Assigned")->get();
                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                            'mrn' => isset($request->mrn) && $request->mrn != "NULL" ? $request->mrn : NULL,
                            'tk' => isset($request->tk) && $request->tk != "NULL" ? $request->tk : NULL,
                            'tk_balance' => isset($request->tk_balance) && $request->tk_balance != "NULL" ? $request->tk_balance : NULL,
                            'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'visit_owner' => isset($request->visit_owner) && $request->visit_owner != "NULL" ? $request->visit_owner : NULL,
                            'visit_description' => isset($request->visit_description) && $request->visit_description != "NULL" ? $request->visit_description : NULL,
                            'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                            'doctor' => isset($request->doctor) && $request->doctor != "NULL" ? $request->doctor : NULL,
                            'financial_class' => isset($request->financial_class) && $request->financial_class != "NULL" ? $request->financial_class : NULL,
                            'primary_insur_carrier' => isset($request->primary_insur_carrier) && $request->primary_insur_carrier != "NULL" ? $request->primary_insur_carrier : NULL,
                            'current_insur_carrier' => isset($request->current_insur_carrier) && $request->current_insur_carrier != "NULL" ? $request->current_insur_carrier : NULL,
                            'division' => isset($request->division) && $request->division != "NULL" ? $request->division : NULL,
                            'company' => isset($request->company) && $request->company != "NULL" ? $request->company : NULL,
                            'cus_payer_group' => isset($request->cus_payer_group) && $request->cus_payer_group != "NULL" ? $request->cus_payer_group : NULL,
                            'primary_insur_group' => isset($request->primary_insur_group) && $request->primary_insur_group != "NULL" ? $request->primary_insur_group : NULL,
                            'current_insur_group' => isset($request->current_insur_group) && $request->current_insur_group != "NULL" ? $request->current_insur_group : NULL,
                            'total_chrg' => isset($request->total_chrg) && $request->total_chrg != "NULL" ? $request->total_chrg : NULL,
                            'total_pmnt' => isset($request->total_pmnt) && $request->total_pmnt != "NULL" ? $request->total_pmnt : NULL,
                            'insur_pmnt' => isset($request->insur_pmnt) && $request->insur_pmnt != "NULL" ? $request->insur_pmnt : NULL,
                            'insur_pv_bal' => isset($request->insur_pv_bal) && $request->insur_pv_bal != "NULL" ? $request->insur_pv_bal : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    }
                    return response()->json(['message' => 'Existing Record Updated Successfully']);
                } else {
                     OscotrAr::insert([
                        'mrn' => isset($request->mrn) && $request->mrn != "NULL" ? $request->mrn : NULL,
                        'tk' => isset($request->tk) && $request->tk != "NULL" ? $request->tk : NULL,
                        'tk_balance' => isset($request->tk_balance) && $request->tk_balance != "NULL" ? $request->tk_balance : NULL,
                        'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'visit_owner' => isset($request->visit_owner) && $request->visit_owner != "NULL" ? $request->visit_owner : NULL,
                        'visit_description' => isset($request->visit_description) && $request->visit_description != "NULL" ? $request->visit_description : NULL,
                        'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                        'doctor' => isset($request->doctor) && $request->doctor != "NULL" ? $request->doctor : NULL,
                        'financial_class' => isset($request->financial_class) && $request->financial_class != "NULL" ? $request->financial_class : NULL,
                        'primary_insur_carrier' => isset($request->primary_insur_carrier) && $request->primary_insur_carrier != "NULL" ? $request->primary_insur_carrier : NULL,
                        'current_insur_carrier' => isset($request->current_insur_carrier) && $request->current_insur_carrier != "NULL" ? $request->current_insur_carrier : NULL,
                        'division' => isset($request->division) && $request->division != "NULL" ? $request->division : NULL,
                        'company' => isset($request->company) && $request->company != "NULL" ? $request->company : NULL,
                        'cus_payer_group' => isset($request->cus_payer_group) && $request->cus_payer_group != "NULL" ? $request->cus_payer_group : NULL,
                        'primary_insur_group' => isset($request->primary_insur_group) && $request->primary_insur_group != "NULL" ? $request->primary_insur_group : NULL,
                        'current_insur_group' => isset($request->current_insur_group) && $request->current_insur_group != "NULL" ? $request->current_insur_group : NULL,
                        'total_chrg' => isset($request->total_chrg) && $request->total_chrg != "NULL" ? $request->total_chrg : NULL,
                        'total_pmnt' => isset($request->total_pmnt) && $request->total_pmnt != "NULL" ? $request->total_pmnt : NULL,
                        'insur_pmnt' => isset($request->insur_pmnt) && $request->insur_pmnt != "NULL" ? $request->insur_pmnt : NULL,
                        'insur_pv_bal' => isset($request->insur_pv_bal) && $request->insur_pv_bal != "NULL" ? $request->insur_pv_bal : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                    return response()->json(['message' => 'Record reinserted Successfully']);
                }
                
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function orthopaedicSpineCenterOfTheRockiesArDuplicates(Request $request)
    {
        try {
                OscotrArDuplicates::insert([
                    'mrn' => isset($request->mrn) && $request->mrn != "NULL" ? $request->mrn : NULL,
                    'tk' => isset($request->tk) && $request->tk != "NULL" ? $request->tk : NULL,
                    'tk_balance' => isset($request->tk_balance) && $request->tk_balance != "NULL" ? $request->tk_balance : NULL,
                    'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'visit_owner' => isset($request->visit_owner) && $request->visit_owner != "NULL" ? $request->visit_owner : NULL,
                    'visit_description' => isset($request->visit_description) && $request->visit_description != "NULL" ? $request->visit_description : NULL,
                    'facility' => isset($request->facility) && $request->facility != "NULL" ? $request->facility : NULL,
                    'doctor' => isset($request->doctor) && $request->doctor != "NULL" ? $request->doctor : NULL,
                    'financial_class' => isset($request->financial_class) && $request->financial_class != "NULL" ? $request->financial_class : NULL,
                    'primary_insur_carrier' => isset($request->primary_insur_carrier) && $request->primary_insur_carrier != "NULL" ? $request->primary_insur_carrier : NULL,
                    'current_insur_carrier' => isset($request->current_insur_carrier) && $request->current_insur_carrier != "NULL" ? $request->current_insur_carrier : NULL,
                    'division' => isset($request->division) && $request->division != "NULL" ? $request->division : NULL,
                    'company' => isset($request->company) && $request->company != "NULL" ? $request->company : NULL,
                    'cus_payer_group' => isset($request->cus_payer_group) && $request->cus_payer_group != "NULL" ? $request->cus_payer_group : NULL,
                    'primary_insur_group' => isset($request->primary_insur_group) && $request->primary_insur_group != "NULL" ? $request->primary_insur_group : NULL,
                    'current_insur_group' => isset($request->current_insur_group) && $request->current_insur_group != "NULL" ? $request->current_insur_group : NULL,
                    'total_chrg' => isset($request->total_chrg) && $request->total_chrg != "NULL" ? $request->total_chrg : NULL,
                    'total_pmnt' => isset($request->total_pmnt) && $request->total_pmnt != "NULL" ? $request->total_pmnt : NULL,
                    'insur_pmnt' => isset($request->insur_pmnt) && $request->insur_pmnt != "NULL" ? $request->insur_pmnt : NULL,
                    'insur_pv_bal' => isset($request->insur_pv_bal) && $request->insur_pv_bal != "NULL" ? $request->insur_pv_bal : NULL,
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

    public function medicalPracticeManagementServicesAr(Request $request)
    {
        try {
            $attributes = [
                 'encounter' => isset($request->encounter) && $request->encounter != "NULL" ? $request->encounter : NULL
             ];         

            $duplicateRecordExisting  =  MpmsAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                MpmsAr::insert([
                        'encounter' => isset($request->encounter) && $request->encounter != "NULL" ? $request->encounter : NULL,
                        'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'loc_name' => isset($request->loc_name) && $request->loc_name != "NULL" ? $request->loc_name : NULL,
                        'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                        'fin_class' => isset($request->fin_class) && $request->fin_class != "NULL" ? $request->fin_class : NULL,
                        'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,
                        'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                        'outstanding' => isset($request->outstanding) && $request->outstanding != "NULL" ? $request->outstanding : NULL,
                        'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecords  =  MpmsAr::where($attributes)->where('chart_status',"CE_Assigned")->get();
                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                            'encounter' => isset($request->encounter) && $request->encounter != "NULL" ? $request->encounter : NULL,
                            'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'loc_name' => isset($request->loc_name) && $request->loc_name != "NULL" ? $request->loc_name : NULL,
                            'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                            'fin_class' => isset($request->fin_class) && $request->fin_class != "NULL" ? $request->fin_class : NULL,
                            'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,
                            'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                            'outstanding' => isset($request->outstanding) && $request->outstanding != "NULL" ? $request->outstanding : NULL,
                            'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    }
                    return response()->json(['message' => 'Existing Record Updated Successfully']);
                } else {
                    MpmsAr::insert([
                        'encounter' => isset($request->encounter) && $request->encounter != "NULL" ? $request->encounter : NULL,
                        'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'loc_name' => isset($request->loc_name) && $request->loc_name != "NULL" ? $request->loc_name : NULL,
                        'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                        'fin_class' => isset($request->fin_class) && $request->fin_class != "NULL" ? $request->fin_class : NULL,
                        'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,
                        'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                        'outstanding' => isset($request->outstanding) && $request->outstanding != "NULL" ? $request->outstanding : NULL,
                        'date_touched' => isset($request->date_touched) && $request->date_touched != "NULL" ? $request->date_touched : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                    return response()->json(['message' => 'Record reinserted Successfully']);
                }
                
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function medicalPracticeManagementServicesArDuplicates(Request $request)
    {
        try {
            MpmsArDuplicates::insert([
                    'encounter' => isset($request->encounter) && $request->encounter != "NULL" ? $request->encounter : NULL,
                    'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'loc_name' => isset($request->loc_name) && $request->loc_name != "NULL" ? $request->loc_name : NULL,
                    'cpt' => isset($request->cpt) && $request->cpt != "NULL" ? $request->cpt : NULL,
                    'fin_class' => isset($request->fin_class) && $request->fin_class != "NULL" ? $request->fin_class : NULL,
                    'payer_name' => isset($request->payer_name) && $request->payer_name != "NULL" ? $request->payer_name : NULL,
                    'charge' => isset($request->charge) && $request->charge != "NULL" ? $request->charge : NULL,
                    'outstanding' => isset($request->outstanding) && $request->outstanding != "NULL" ? $request->outstanding : NULL,
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

    public function gastrointestinalAssociateAr(Request $request)
    {
        try {
            $attributes = [
                 'bill_number' => isset($request->bill_number) && $request->bill_number != "NULL" ? $request->bill_number : NULL
             ];         

            $duplicateRecordExisting  =  GaAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                GaAr::insert([
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                        'perf_prov' => isset($request->perf_prov) && $request->perf_prov != "NULL" ? $request->perf_prov : NULL,
                        'billing_prov' => isset($request->billing_prov) && $request->billing_prov != "NULL" ? $request->billing_prov : NULL,
                        'contact_date' => isset($request->contact_date) && $request->contact_date != "NULL" ? $request->contact_date : NULL,
                        'bill_number' => isset($request->bill_number) && $request->bill_number != "NULL" ? $request->bill_number : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecords  =  GaAr::where($attributes)->where('chart_status',"CE_Assigned")->get();
                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,
                            'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                            'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                            'perf_prov' => isset($request->perf_prov) && $request->perf_prov != "NULL" ? $request->perf_prov : NULL,
                            'billing_prov' => isset($request->billing_prov) && $request->billing_prov != "NULL" ? $request->billing_prov : NULL,
                            'contact_date' => isset($request->contact_date) && $request->contact_date != "NULL" ? $request->contact_date : NULL,
                            'bill_number' => isset($request->bill_number) && $request->bill_number != "NULL" ? $request->bill_number : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    }
                    return response()->json(['message' => 'Existing Record Updated Successfully']);
                } else {
                    GaAr::insert([
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                        'perf_prov' => isset($request->perf_prov) && $request->perf_prov != "NULL" ? $request->perf_prov : NULL,
                        'billing_prov' => isset($request->billing_prov) && $request->billing_prov != "NULL" ? $request->billing_prov : NULL,
                        'contact_date' => isset($request->contact_date) && $request->contact_date != "NULL" ? $request->contact_date : NULL,
                        'bill_number' => isset($request->bill_number) && $request->bill_number != "NULL" ? $request->bill_number : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                    return response()->json(['message' => 'Record reinserted Successfully']);
                }
                
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function gastrointestinalAssociateArDuplicates(Request $request)
    {
        try {
              GaArDuplicates::insert([
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                    'location' => isset($request->location) && $request->location != "NULL" ? $request->location : NULL,
                    'perf_prov' => isset($request->perf_prov) && $request->perf_prov != "NULL" ? $request->perf_prov : NULL,
                    'billing_prov' => isset($request->billing_prov) && $request->billing_prov != "NULL" ? $request->billing_prov : NULL,
                    'contact_date' => isset($request->contact_date) && $request->contact_date != "NULL" ? $request->contact_date : NULL,
                    'bill_number' => isset($request->bill_number) && $request->bill_number != "NULL" ? $request->bill_number : NULL,
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
    public function colquittRegionalMedicalCenterAr(Request $request)
    {
        try {
            $attributes = [
                 'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                 'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL
             ];         

            $duplicateRecordExisting  =  CrmcAr::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                CrmcAr::insert([
                        'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                        'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL,
                        'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,                     
                        'responsible_provider' => isset($request->responsible_provider) && $request->responsible_provider != "NULL" ? $request->responsible_provider : NULL,
                        'specialty' => isset($request->specialty) && $request->specialty != "NULL" ? $request->specialty : NULL,
                        'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecords  =  CrmcAr::where($attributes)->where('chart_status',"CE_Assigned")->get();
                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                            'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                            'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL,
                            'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,                     
                            'responsible_provider' => isset($request->responsible_provider) && $request->responsible_provider != "NULL" ? $request->responsible_provider : NULL,
                            'specialty' => isset($request->specialty) && $request->specialty != "NULL" ? $request->specialty : NULL,
                            'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    }
                    return response()->json(['message' => 'Existing Record Updated Successfully']);
                } else {
                    CrmcAr::insert([
                        'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                        'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL,
                        'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,                     
                        'responsible_provider' => isset($request->responsible_provider) && $request->responsible_provider != "NULL" ? $request->responsible_provider : NULL,
                        'specialty' => isset($request->specialty) && $request->specialty != "NULL" ? $request->specialty : NULL,
                        'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                    return response()->json(['message' => 'Record reinserted Successfully']);
                }
                
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function colquittRegionalMedicalCenterArDuplicates(Request $request)
    {
        try {
                CrmcArDuplicates::insert([
                    'account_number' => isset($request->account_number) && $request->account_number != "NULL" ? $request->account_number : NULL,
                    'name' => isset($request->name) && $request->name != "NULL" ? $request->name : NULL,
                    'insurance' => isset($request->insurance) && $request->insurance != "NULL" ? $request->insurance : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,                     
                    'responsible_provider' => isset($request->responsible_provider) && $request->responsible_provider != "NULL" ? $request->responsible_provider : NULL,
                    'specialty' => isset($request->specialty) && $request->specialty != "NULL" ? $request->specialty : NULL,
                    'amount' => isset($request->amount) && $request->amount != "NULL" ? $request->amount : NULL,
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
     public function SmmiArDenver(Request $request)
    {
        try {
            $attributes = [
                 'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                 'lineref' => isset($request->lineref) && $request->lineref != "NULL" ? $request->lineref : NULL,
                 'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,
                 'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                 'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                 'file_order' => isset($request->file_order) && $request->file_order != "NULL" ? $request->file_order : NULL
             ];         

            $duplicateRecordExisting  =  SmmiArDenver::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                SmmiArDenver::insert([
                        'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                        'lineref' => isset($request->lineref) && $request->lineref != "NULL" ? $request->lineref : NULL,
                        'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,
                        'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'file_order' => isset($request->file_order) && $request->file_order != "NULL" ? $request->file_order : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecords  =  SmmiArDenver::where($attributes)->where('chart_status',"CE_Assigned")->get();
                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                            'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                            'lineref' => isset($request->lineref) && $request->lineref != "NULL" ? $request->lineref : NULL,
                            'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,
                            'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                            'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                            'file_order' => isset($request->file_order) && $request->file_order != "NULL" ? $request->file_order : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    }
                    return response()->json(['message' => 'Existing Record Updated Successfully']);
                } else {
                    SmmiArDenver::insert([
                        'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                        'lineref' => isset($request->lineref) && $request->lineref != "NULL" ? $request->lineref : NULL,
                        'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,
                        'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'file_order' => isset($request->file_order) && $request->file_order != "NULL" ? $request->file_order : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                    return response()->json(['message' => 'Record reinserted Successfully']);
                }
                
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function SmmiArDenverDuplicates(Request $request)
    {
        try {
                 SmmiArDenverDuplicates::insert([
                    'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                    'lineref' => isset($request->lineref) && $request->lineref != "NULL" ? $request->lineref : NULL,
                    'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,
                    'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                    'file_order' => isset($request->file_order) && $request->file_order != "NULL" ? $request->file_order : NULL,
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
       public function SmmiArDallas(Request $request)
    {
        try {
            $attributes = [
                 'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                 'lineref' => isset($request->lineref) && $request->lineref != "NULL" ? $request->lineref : NULL,
                 'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,
                 'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                 'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                 'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                 'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                 'file_order' => isset($request->file_order) && $request->file_order != "NULL" ? $request->file_order : NULL
             ];         

            $duplicateRecordExisting  =  SmmiArDallas::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                SmmiArDallas::insert([
                        'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                        'lineref' => isset($request->lineref) && $request->lineref != "NULL" ? $request->lineref : NULL,
                        'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,
                        'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'file_order' => isset($request->file_order) && $request->file_order != "NULL" ? $request->file_order : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                        return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecords  =  SmmiArDallas::where($attributes)->where('chart_status',"CE_Assigned")->get();
                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                            'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                            'lineref' => isset($request->lineref) && $request->lineref != "NULL" ? $request->lineref : NULL,
                            'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,
                            'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                            'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                            'file_order' => isset($request->file_order) && $request->file_order != "NULL" ? $request->file_order : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at'=> carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    }
                    return response()->json(['message' => 'Existing Record Updated Successfully']);
                } else {
                    SmmiArDallas::insert([
                        'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                        'lineref' => isset($request->lineref) && $request->lineref != "NULL" ? $request->lineref : NULL,
                        'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,
                        'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                        'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                        'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                        'file_order' => isset($request->file_order) && $request->file_order != "NULL" ? $request->file_order : NULL,
                        'invoke_date' => date('Y-m-d'),
                        'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                        'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                        'chart_status' => "CE_Assigned",
                    ]);
                    return response()->json(['message' => 'Record reinserted Successfully']);
                }
                
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function SmmiArDallasDuplicates(Request $request)
    {
        try {
                 SmmiArDallasDuplicates::insert([
                    'practice' => isset($request->practice) && $request->practice != "NULL" ? $request->practice : NULL,
                    'lineref' => isset($request->lineref) && $request->lineref != "NULL" ? $request->lineref : NULL,
                    'acct_no' => isset($request->acct_no) && $request->acct_no != "NULL" ? $request->acct_no : NULL,
                    'pt_name' => isset($request->pt_name) && $request->pt_name != "NULL" ? $request->pt_name : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'carrier' => isset($request->carrier) && $request->carrier != "NULL" ? $request->carrier : NULL,
                    'balance' => isset($request->balance) && $request->balance != "NULL" ? $request->balance : NULL,
                    'file_order' => isset($request->file_order) && $request->file_order != "NULL" ? $request->file_order : NULL,
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

    public function tqhsArDenials(Request $request) {
        try {
            $attributes = [
                'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];          

            $duplicateRecordExisting  =  TqhsArDenials::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                TqhsArDenials::insert([
                    'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                    'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                    'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
                $duplicateRecords = TqhsArDenials::where($attributes)->where('chart_status',"CE_Assigned")->get();
                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                        'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                        'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                        'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                        'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                        'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
                } else {
                     TqhsArDenials::insert([
                        'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                        'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                        'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                        'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                        'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
                    return response()->json(['message' => 'Record reinserted Successfully']);
                }
                
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function tqhsArDenialsDuplicates(Request $request)
    {
        try {
            TqhsArDenialsDuplicates::insert([
                    'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                    'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                    'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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

     public function tqhsARNoResponseTfi(Request $request) {
        try {
            $attributes = [
                'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];          

            $duplicateRecordExisting  =  TqhsArNoResponseTfl::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                TqhsArNoResponseTfl::insert([
                    'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                    'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                    'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
                $duplicateRecords = TqhsArNoResponseTfl::where($attributes)->where('chart_status',"CE_Assigned")->get();
                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                        'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                        'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                        'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                        'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                        'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
                } else {
                     TqhsArNoResponseTfl::insert([
                        'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                        'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                        'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                        'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                        'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
                    return response()->json(['message' => 'Record reinserted Successfully']);
                }
                
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function tqhsARNoResponseTfiDuplicates(Request $request)
    {
        try {
            TqhsArNoResponseTflDuplicates::insert([
                    'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                    'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                    'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
     public function tqhsArRhcNoResponse(Request $request) {
        try {
            $attributes = [
                'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];          

            $duplicateRecordExisting  =  TqhsArRhcNoResponse::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                TqhsArRhcNoResponse::insert([
                    'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                    'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                    'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
                $duplicateRecords = TqhsArRhcNoResponse::where($attributes)->where('chart_status',"CE_Assigned")->get();
                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                        'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                        'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                        'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                        'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                        'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
                } else {
                     TqhsArRhcNoResponse::insert([
                        'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                        'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                        'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                        'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                        'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
                    return response()->json(['message' => 'Record reinserted Successfully']);
                }
                
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
     }
    public function tqhsArRhcNoResponseDuplicates(Request $request)
    {
        try {
              TqhsArRhcNoResponseDuplicates::insert([
                    'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                    'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                    'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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

     public function tqhsArRhcDenials(Request $request) {
        try {
            $attributes = [
                'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,
                'invoke_date' => carbon::now()->format('Y-m-d')
             ];          

            $duplicateRecordExisting  =  TqhsArRhcDenials::where($attributes)->exists();
            if (!$duplicateRecordExisting) {
                TqhsArRhcDenials::insert([
                    'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                    'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                    'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
                $duplicateRecords = TqhsArRhcDenials::where($attributes)->where('chart_status',"CE_Assigned")->get();
                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                        'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                        'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                        'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                        'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                        'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
                } else {
                     TqhsArRhcDenials::insert([
                        'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                        'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                        'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                        'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                        'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                        'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                        'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
                    return response()->json(['message' => 'Record reinserted Successfully']);
                }
                
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
     }
    public function tqhsArRhcDenialsDuplicates(Request $request)
    {
        try {
               TqhsArRhcDenialsDuplicates::insert([
                    'priority' => isset($request->priority) && $request->priority != "NULL" ? $request->priority : NULL,  
                    'fup_score' => isset($request->fup_score) && $request->fup_score != "NULL" ? $request->fup_score : NULL,  
                    'invoice_number' => isset($request->invoice_number) && $request->invoice_number != "NULL" ? $request->invoice_number : NULL,  
                    'patient' => isset($request->patient) && $request->patient != "NULL" ? $request->patient : NULL, 
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL, 
                    'over_90' => isset($request->over_90) && $request->over_90 != "NULL" ? $request->over_90 : NULL, 
                    'created' => isset($request->created) && $request->created != "NULL" ? $request->created : NULL, 
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
    public function dkmgArBehavioralMentalHealth(Request $request)
    {
        try {
            $attributes = [
                'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,
                'invoke_date' => Carbon::now()->format('Y-m-d'),
            ];

            $duplicateRecordExisting = DkmgArBehavioralMentalHealth::where($attributes)->exists();

            if (!$duplicateRecordExisting) {
                // FIRST TIME INSERT
                DkmgArBehavioralMentalHealth::insert([
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                    'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,
                    'cstm_ins_grpng' => isset($request->cstm_ins_grpng) && $request->cstm_ins_grpng != "NULL" ? $request->cstm_ins_grpng : NULL,
                    'ins_pkg_id' => isset($request->ins_pkg_id) && $request->ins_pkg_id != "NULL" ? $request->ins_pkg_id : NULL,
                    'ins_report_cat' => isset($request->ins_report_cat) && $request->ins_report_cat != "NULL" ? $request->ins_report_cat : NULL,
                    'fc' => isset($request->fc) && $request->fc != "NULL" ? $request->fc : NULL,
                    'proccode' => isset($request->proccode) && $request->proccode != "NULL" ? $request->proccode : NULL,
                    'proccode_grp' => isset($request->proccode_grp) && $request->proccode_grp != "NULL" ? $request->proccode_grp : NULL,
                    'rndrng_prvdr' => isset($request->rndrng_prvdr) && $request->rndrng_prvdr != "NULL" ? $request->rndrng_prvdr : NULL,
                    'sup_prvdr' => isset($request->sup_prvdr) && $request->sup_prvdr != "NULL" ? $request->sup_prvdr : NULL,
                    'svc_dprtmnt' => isset($request->svc_dprtmnt) && $request->svc_dprtmnt != "NULL" ? $request->svc_dprtmnt : NULL,
                    'rndrng_prvdr_mdcl_grp' => isset($request->rndrng_prvdr_mdcl_grp) && $request->rndrng_prvdr_mdcl_grp != "NULL" ? $request->rndrng_prvdr_mdcl_grp : NULL,
                    'sup_prvdr_mdcl_grp' => isset($request->sup_prvdr_mdcl_grp) && $request->sup_prvdr_mdcl_grp != "NULL" ? $request->sup_prvdr_mdcl_grp : NULL,
                    'curr_athena_kick_code' => isset($request->curr_athena_kick_code) && $request->curr_athena_kick_code != "NULL" ? $request->curr_athena_kick_code : NULL,
                    'curr_athena_kick_code_rej_rsn' => isset($request->curr_athena_kick_code_rej_rsn) && $request->curr_athena_kick_code_rej_rsn != "NULL" ? $request->curr_athena_kick_code_rej_rsn : NULL,
                    'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,
                    'curr_glbl_rule_rej_rsn' => isset($request->curr_glbl_rule_rej_rsn) && $request->curr_glbl_rule_rej_rsn != "NULL" ? $request->curr_glbl_rule_rej_rsn : NULL,
                    'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,
                    'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,
                    'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,
                    'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,
                    'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,
                    'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,
                    'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,
                    'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,
                    'cstatus' => isset($request->cstatus) && $request->cstatus != "NULL" ? $request->cstatus : NULL,
                    'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,
                    'trnsfr_type' => isset($request->trnsfr_type) && $request->trnsfr_type != "NULL" ? $request->trnsfr_type : NULL,
                    'invoke_date' => Carbon::now()->format('Y-m-d'),
                    'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                    'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                    'chart_status' => 'CE_Assigned',
                ]);
                return response()->json(['message' => 'Record Inserted Successfully']);
            } else {
                $duplicateRecords = DkmgArBehavioralMentalHealth::where($attributes)->where('chart_status', 'CE_Assigned')->get();

                if ($duplicateRecords->isNotEmpty()) {
                    foreach ($duplicateRecords as $duplicateRecord) {
                        $duplicateRecord->update([
                            'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                            'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                            'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,
                            'cstm_ins_grpng' => isset($request->cstm_ins_grpng) && $request->cstm_ins_grpng != "NULL" ? $request->cstm_ins_grpng : NULL,
                            'ins_pkg_id' => isset($request->ins_pkg_id) && $request->ins_pkg_id != "NULL" ? $request->ins_pkg_id : NULL,
                            'ins_report_cat' => isset($request->ins_report_cat) && $request->ins_report_cat != "NULL" ? $request->ins_report_cat : NULL,
                            'fc' => isset($request->fc) && $request->fc != "NULL" ? $request->fc : NULL,
                            'proccode' => isset($request->proccode) && $request->proccode != "NULL" ? $request->proccode : NULL,
                            'proccode_grp' => isset($request->proccode_grp) && $request->proccode_grp != "NULL" ? $request->proccode_grp : NULL,
                            'rndrng_prvdr' => isset($request->rndrng_prvdr) && $request->rndrng_prvdr != "NULL" ? $request->rndrng_prvdr : NULL,
                            'sup_prvdr' => isset($request->sup_prvdr) && $request->sup_prvdr != "NULL" ? $request->sup_prvdr : NULL,
                            'svc_dprtmnt' => isset($request->svc_dprtmnt) && $request->svc_dprtmnt != "NULL" ? $request->svc_dprtmnt : NULL,
                            'rndrng_prvdr_mdcl_grp' => isset($request->rndrng_prvdr_mdcl_grp) && $request->rndrng_prvdr_mdcl_grp != "NULL" ? $request->rndrng_prvdr_mdcl_grp : NULL,
                            'sup_prvdr_mdcl_grp' => isset($request->sup_prvdr_mdcl_grp) && $request->sup_prvdr_mdcl_grp != "NULL" ? $request->sup_prvdr_mdcl_grp : NULL,
                            'curr_athena_kick_code' => isset($request->curr_athena_kick_code) && $request->curr_athena_kick_code != "NULL" ? $request->curr_athena_kick_code : NULL,
                            'curr_athena_kick_code_rej_rsn' => isset($request->curr_athena_kick_code_rej_rsn) && $request->curr_athena_kick_code_rej_rsn != "NULL" ? $request->curr_athena_kick_code_rej_rsn : NULL,
                            'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,
                            'curr_glbl_rule_rej_rsn' => isset($request->curr_glbl_rule_rej_rsn) && $request->curr_glbl_rule_rej_rsn != "NULL" ? $request->curr_glbl_rule_rej_rsn : NULL,
                            'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,
                            'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,
                            'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,
                            'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,
                            'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,
                            'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,
                            'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,
                            'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,
                            'cstatus' => isset($request->cstatus) && $request->cstatus != "NULL" ? $request->cstatus : NULL,
                            'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,
                            'trnsfr_type' => isset($request->trnsfr_type) && $request->trnsfr_type != "NULL" ? $request->trnsfr_type : NULL,
                            'invoke_date' => Carbon::now()->format('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                    return response()->json(['message' => 'Existing Record Updated Successfully']);
                } else {
                    DkmgArBehavioralMentalHealth::insert([
                           'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                            'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                            'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                            'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                            'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,
                            'cstm_ins_grpng' => isset($request->cstm_ins_grpng) && $request->cstm_ins_grpng != "NULL" ? $request->cstm_ins_grpng : NULL,
                            'ins_pkg_id' => isset($request->ins_pkg_id) && $request->ins_pkg_id != "NULL" ? $request->ins_pkg_id : NULL,
                            'ins_report_cat' => isset($request->ins_report_cat) && $request->ins_report_cat != "NULL" ? $request->ins_report_cat : NULL,
                            'fc' => isset($request->fc) && $request->fc != "NULL" ? $request->fc : NULL,
                            'proccode' => isset($request->proccode) && $request->proccode != "NULL" ? $request->proccode : NULL,
                            'proccode_grp' => isset($request->proccode_grp) && $request->proccode_grp != "NULL" ? $request->proccode_grp : NULL,
                            'rndrng_prvdr' => isset($request->rndrng_prvdr) && $request->rndrng_prvdr != "NULL" ? $request->rndrng_prvdr : NULL,
                            'sup_prvdr' => isset($request->sup_prvdr) && $request->sup_prvdr != "NULL" ? $request->sup_prvdr : NULL,
                            'svc_dprtmnt' => isset($request->svc_dprtmnt) && $request->svc_dprtmnt != "NULL" ? $request->svc_dprtmnt : NULL,
                            'rndrng_prvdr_mdcl_grp' => isset($request->rndrng_prvdr_mdcl_grp) && $request->rndrng_prvdr_mdcl_grp != "NULL" ? $request->rndrng_prvdr_mdcl_grp : NULL,
                            'sup_prvdr_mdcl_grp' => isset($request->sup_prvdr_mdcl_grp) && $request->sup_prvdr_mdcl_grp != "NULL" ? $request->sup_prvdr_mdcl_grp : NULL,
                            'curr_athena_kick_code' => isset($request->curr_athena_kick_code) && $request->curr_athena_kick_code != "NULL" ? $request->curr_athena_kick_code : NULL,
                            'curr_athena_kick_code_rej_rsn' => isset($request->curr_athena_kick_code_rej_rsn) && $request->curr_athena_kick_code_rej_rsn != "NULL" ? $request->curr_athena_kick_code_rej_rsn : NULL,
                            'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,
                            'curr_glbl_rule_rej_rsn' => isset($request->curr_glbl_rule_rej_rsn) && $request->curr_glbl_rule_rej_rsn != "NULL" ? $request->curr_glbl_rule_rej_rsn : NULL,
                            'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,
                            'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,
                            'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,
                            'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,
                            'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,
                            'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,
                            'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,
                            'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,
                            'cstatus' => isset($request->cstatus) && $request->cstatus != "NULL" ? $request->cstatus : NULL,
                            'lstactiondate' => isset($request->lstactiondate) && $request->lstactiondate != "NULL" ? $request->lstactiondate : NULL,
                            'trnsfr_type' => isset($request->trnsfr_type) && $request->trnsfr_type != "NULL" ? $request->trnsfr_type : NULL,
                            'invoke_date' => date('Y-m-d'),
                            'CE_emp_id' => isset($request->CE_emp_id) && $request->CE_emp_id != '-' && $request->CE_emp_id != "NULL" ? $request->CE_emp_id : NULL,
                            'QA_emp_id' => isset($request->QA_emp_id) && $request->QA_emp_id != '-' && $request->QA_emp_id != "NULL" ? $request->QA_emp_id : NULL,
                            'chart_status' => "CE_Assigned",
                    ]);
                    return response()->json(['message' => 'Record Reinserted Successfully']);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

 public function dkmgArBehavioralMentalHealthDuplicates(Request $request)
    {
        try {
                DkmgArBehavioralMentalHealthDuplicates::insert([
                    'claim_id' => isset($request->claim_id) && $request->claim_id != "NULL" ? $request->claim_id : NULL,
                    'patient_id' => isset($request->patient_id) && $request->patient_id != "NULL" ? $request->patient_id : NULL,
                    'patient_name' => isset($request->patient_name) && $request->patient_name != "NULL" ? $request->patient_name : NULL,
                    'dos' => isset($request->dos) && $request->dos != "NULL" ? $request->dos : NULL,
                    'patient_dob' => isset($request->patient_dob) && $request->patient_dob != "NULL" ? $request->patient_dob : NULL,
                    'cstm_ins_grpng' => isset($request->cstm_ins_grpng) && $request->cstm_ins_grpng != "NULL" ? $request->cstm_ins_grpng : NULL,
                    'ins_pkg_id' => isset($request->ins_pkg_id) && $request->ins_pkg_id != "NULL" ? $request->ins_pkg_id : NULL,
                    'ins_report_cat' => isset($request->ins_report_cat) && $request->ins_report_cat != "NULL" ? $request->ins_report_cat : NULL,
                    'fc' => isset($request->fc) && $request->fc != "NULL" ? $request->fc : NULL,
                    'proccode' => isset($request->proccode) && $request->proccode != "NULL" ? $request->proccode : NULL,
                    'proccode_grp' => isset($request->proccode_grp) && $request->proccode_grp != "NULL" ? $request->proccode_grp : NULL,
                    'rndrng_prvdr' => isset($request->rndrng_prvdr) && $request->rndrng_prvdr != "NULL" ? $request->rndrng_prvdr : NULL,
                    'sup_prvdr' => isset($request->sup_prvdr) && $request->sup_prvdr != "NULL" ? $request->sup_prvdr : NULL,
                    'svc_dprtmnt' => isset($request->svc_dprtmnt) && $request->svc_dprtmnt != "NULL" ? $request->svc_dprtmnt : NULL,
                    'rndrng_prvdr_mdcl_grp' => isset($request->rndrng_prvdr_mdcl_grp) && $request->rndrng_prvdr_mdcl_grp != "NULL" ? $request->rndrng_prvdr_mdcl_grp : NULL,
                    'sup_prvdr_mdcl_grp' => isset($request->sup_prvdr_mdcl_grp) && $request->sup_prvdr_mdcl_grp != "NULL" ? $request->sup_prvdr_mdcl_grp : NULL,
                    'curr_athena_kick_code' => isset($request->curr_athena_kick_code) && $request->curr_athena_kick_code != "NULL" ? $request->curr_athena_kick_code : NULL,
                    'curr_athena_kick_code_rej_rsn' => isset($request->curr_athena_kick_code_rej_rsn) && $request->curr_athena_kick_code_rej_rsn != "NULL" ? $request->curr_athena_kick_code_rej_rsn : NULL,
                    'currenterrorfull' => isset($request->currenterrorfull) && $request->currenterrorfull != "NULL" ? $request->currenterrorfull : NULL,
                    'curr_glbl_rule_rej_rsn' => isset($request->curr_glbl_rule_rej_rsn) && $request->curr_glbl_rule_rej_rsn != "NULL" ? $request->curr_glbl_rule_rej_rsn : NULL,
                    'curr_glbl_rule' => isset($request->curr_glbl_rule) && $request->curr_glbl_rule != "NULL" ? $request->curr_glbl_rule : NULL,
                    'srvbucket_0_to_30' => isset($request->srvbucket_0_to_30) && $request->srvbucket_0_to_30 != "NULL" ? $request->srvbucket_0_to_30 : NULL,
                    'srvbucket_31_to_60' => isset($request->srvbucket_31_to_60) && $request->srvbucket_31_to_60 != "NULL" ? $request->srvbucket_31_to_60 : NULL,
                    'srvbucket_91_to_120' => isset($request->srvbucket_91_to_120) && $request->srvbucket_91_to_120 != "NULL" ? $request->srvbucket_91_to_120 : NULL,
                    'srvbucket_121_to_150' => isset($request->srvbucket_121_to_150) && $request->srvbucket_121_to_150 != "NULL" ? $request->srvbucket_121_to_150 : NULL,
                    'srvbucket_151_to_180' => isset($request->srvbucket_151_to_180) && $request->srvbucket_151_to_180 != "NULL" ? $request->srvbucket_151_to_180 : NULL,
                    'srvbucket_greater_than_180' => isset($request->srvbucket_greater_than_180) && $request->srvbucket_greater_than_180 != "NULL" ? $request->srvbucket_greater_than_180 : NULL,
                    'srvbucket_total' => isset($request->srvbucket_total) && $request->srvbucket_total != "NULL" ? $request->srvbucket_total : NULL,
                    'cstatus' => isset($request->cstatus) && $request->cstatus != "NULL" ? $request->cstatus : NULL,
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
    
}
