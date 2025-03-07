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
use App\Models\smhcEvVobDuplicates;
use App\Models\LastsChargeEntry;
use App\Models\LastsChargeEntryDuplicates;
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
                $duplicateRecord  =  LastsChargeEntry::where($attributes)->where('chart_status',"CE_Assigned")->first();
                if ($duplicateRecord) {
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
}
