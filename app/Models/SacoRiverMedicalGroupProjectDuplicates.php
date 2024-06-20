<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SacoRiverMedicalGroupProjectDuplicates extends Model
{
    use SoftDeletes;
     protected $table = 'saco_river_medical_group_project_duplicates';
    protected $fillable = [
        'id', 'account_number', 'claim_no', 'dos', 'dob', 'patient_name', 'responsibility', 'primary_insurance_name', 'primary_policy_id', 'total_charges', 'total_ar', 'invoke_date', 'CE_emp_id', 'QA_emp_id', 'chart_status', 'duplicate_status', 'ce_hold_reason', 'qa_hold_reason', 'qa_work_status', 'QA_required_sampling', 'QA_rework_comments', 'coder_rework_status', 'coder_rework_reason', 'coder_error_count', 'qa_error_count', 'tl_error_count', 'tl_comments', 'QA_status_code', 'QA_sub_status_code', 'QA_followup_date', 'CE_status_code', 'CE_sub_status_code', 'CE_followup_date', 'created_at', 'updated_at', 'deleted_at'
    ];


}
