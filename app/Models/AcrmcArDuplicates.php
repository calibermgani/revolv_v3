<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcrmcArDuplicates extends Model
{
    use SoftDeletes;
     protected $table = 'acrmc_ar_duplicates';
    protected $fillable = [
        'id', 'f_else_c', 'provider_no', 'unique_id', 'patient_no', 'patient_name', 'dos', 'ins_co_else_plan', 'derived_01', 'a_else_r_last_payment_amount', 'original_balance', 'inhouse', 'current_balance', 'invoke_date', 'CE_emp_id', 'QA_emp_id', 'chart_status', 'duplicate_status', 'ce_hold_reason', 'qa_hold_reason', 'qa_work_status', 'QA_required_sampling', 'QA_rework_comments', 'QA_status_code', 'QA_sub_status_code', 'qa_classification', 'qa_category', 'qa_scope', 'QA_followup_date', 'CE_status_code', 'CE_sub_status_code', 'CE_followup_date', 'annex_coder_trends', 'annex_qa_trends', 'cpt_trends', 'icd_trends', 'modifiers', 'QA_comments_count', 'coder_work_date', 'qa_work_date', 'coder_rework_status', 'coder_rework_reason', 'coder_error_count', 'qa_error_count', 'tl_error_count', 'tl_comments', 'ar_status_code', 'ar_action_code', 'ar_manager_rebuttal_status', 'ar_manager_rebuttal_comments', 'qa_manager_rebuttal_status', 'qa_manager_rebuttal_comments', 'created_at', 'production_type', 'ar_substatus_codes', 'ar_denial_codes', 'qa_at', 'ar_at', 'corrective_action', 'rca', 'follow_up_date', 'notes', 'sub_activity', 'activity', 'updated_at', 'deleted_at'
    ];


}
