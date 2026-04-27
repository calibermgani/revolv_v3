<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatcArHistory extends Model
{
    use SoftDeletes;
     protected $table = 'matc_ar_history';
    protected $fillable = [
        'id', 'customer_number', 'customer_name', 'document_type', 'document_number', 'name', 'dos', 'coding', 'doc_date', 'due_date_or_check_else_recpt_no', 'current', '0_to_30', '30_to_60', '60_to_90', '90_above', 'total', 'previous_payment', 'parent_id', 'invoke_date', 'CE_emp_id', 'QA_emp_id', 'chart_status', 'ce_hold_reason', 'qa_hold_reason', 'qa_work_status', 'QA_required_sampling', 'QA_rework_comments', 'QA_status_code', 'QA_sub_status_code', 'qa_classification', 'qa_category', 'qa_scope', 'QA_followup_date', 'CE_status_code', 'CE_sub_status_code', 'CE_followup_date', 'annex_coder_trends', 'annex_qa_trends', 'cpt_trends', 'icd_trends', 'modifiers', 'QA_comments_count', 'coder_work_date', 'qa_work_date', 'coder_rework_status', 'coder_rework_reason', 'coder_error_count', 'qa_error_count', 'tl_error_count', 'tl_comments', 'ar_status_code', 'ar_action_code', 'ar_manager_rebuttal_status', 'ar_manager_rebuttal_comments', 'qa_manager_rebuttal_status', 'qa_manager_rebuttal_comments', 'created_at', 'production_type', 'ar_substatus_codes', 'ar_denial_codes', 'ar_notes', 'qa_at', 'ar_at', 'sub_activity', 'activity', 'qa_error_comments', 'updated_at', 'deleted_at'
    ];


}
