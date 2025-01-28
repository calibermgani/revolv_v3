<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AopsPreAuthVerification;
use App\Models\AopsPreAuthVerificationDuplicates;
use App\Models\NmNcgVob;
use App\Models\NmNcgVobDuplicates;

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
}
