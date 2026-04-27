<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BmrhcArHistory extends Model
{
    use SoftDeletes;
     protected $table = 'bmrhc_ar_history';
    protected $fillable = [
        'id', 'claim_status_category', 'claim_status', 'claim_no', 'claim_date', 'atb', 'dos', 'latest_transfer_date', 'claim_status_change_date', 'modified_date', 'notes', 'staff_member', 'patient_name', 'patient_acct_no', 'payer_name', 'payer_group_name', 'payer_class', 'facility_name', 'facility_group_name', 'facility_place_of_service', 'department_name', 'rendering_provider_name', 'appointment_provider_name', 'additional_provider_1_name', 'additional_provider_2_name', 'pay_to_else_billing_provider', 'resource_provider_name', 'supervising_provider_name', 'claim_amount', 'collected', 'total_balance', 'parent_id', 'invoke_date', 'CE_emp_id', 'QA_emp_id', 'chart_status', 'ce_hold_reason', 'qa_hold_reason', 'qa_work_status', 'QA_required_sampling', 'QA_rework_comments', 'QA_status_code', 'QA_sub_status_code', 'qa_classification', 'qa_category', 'qa_scope', 'QA_followup_date', 'CE_status_code', 'CE_sub_status_code', 'CE_followup_date', 'annex_coder_trends', 'annex_qa_trends', 'cpt_trends', 'icd_trends', 'modifiers', 'QA_comments_count', 'coder_work_date', 'qa_work_date', 'coder_rework_status', 'coder_rework_reason', 'coder_error_count', 'qa_error_count', 'tl_error_count', 'tl_comments', 'ar_status_code', 'ar_action_code', 'ar_manager_rebuttal_status', 'ar_manager_rebuttal_comments', 'qa_manager_rebuttal_status', 'qa_manager_rebuttal_comments', 'created_at', 'production_type', 'ar_substatus_codes', 'ar_denial_codes', 'qa_at', 'ar_at', 'capa', 'rca', 'updated_at', 'deleted_at'
    ];


}
