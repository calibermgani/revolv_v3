<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IfwArHistory extends Model
{
    use SoftDeletes;
     protected $table = 'ifw_ar_history';
    protected $fillable = [
        'id', 'resp_ins_plan_type', 'resp_ins_name', 'patient_name', 'follow_up_status', 'account_id', 'current', '30_days', '60_days', '90_days', '120_days', '150_days', 'total', '60_greater', 'ar_notes', 'activity', 'sub_activity', 'parent_id', 'invoke_date', 'CE_emp_id', 'QA_emp_id', 'chart_status', 'ce_hold_reason', 'qa_hold_reason', 'qa_work_status', 'QA_required_sampling', 'QA_rework_comments', 'QA_status_code', 'QA_sub_status_code', 'qa_classification', 'qa_category', 'qa_scope', 'QA_followup_date', 'CE_status_code', 'CE_sub_status_code', 'CE_followup_date', 'annex_coder_trends', 'annex_qa_trends', 'cpt_trends', 'icd_trends', 'modifiers', 'QA_comments_count', 'coder_work_date', 'qa_work_date', 'coder_rework_status', 'coder_rework_reason', 'coder_error_count', 'qa_error_count', 'tl_error_count', 'tl_comments', 'ar_status_code', 'ar_action_code', 'ar_manager_rebuttal_status', 'ar_manager_rebuttal_comments', 'qa_manager_rebuttal_status', 'qa_manager_rebuttal_comments', 'created_at', 'production_type', 'rca_capa', 'ar_substatus_codes', 'ar_denial_codes', 'qa_at', 'ar_at', 'updated_at', 'deleted_at'
    ];


}
