<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TqhsArRhcNoResponseDatas extends Model
{
    use SoftDeletes;
     protected $table = 'tqhs_ar_rhc_no_response_datas';
    protected $fillable = [
        'id', 'priority', 'fup_score', 'invoice_number', 'patient', 'dos', 'over_90', 'created', 'sts', 'account_id', 'account', 'service_provider', 'billing_provider', 'tx_list', 'billed_amt', 'outstanding_amt', 'correspondence', 'wq_entry_date', 'payor', 'plan', 'allowance_discrepancy_amount', 'allowance_discrepancy_percentage', 'timely_filing_deadline_date', 'days_until_timely_filing_deadline', 'id_1', 'bill_area', 'department', 'pos', 'bill_area_name', 'cross_over_flag', 'id_2', 'last_activity', 'reason_codes', 'procedure_code', 'open_hb_denials', 'icn_no', 'subscriber_id', 'modifier_list', 'expected_amt', 'payment', 'remit_code_last_pmt', 'remit_code_name_last_pmt', 'precert_required', 'referral_created_user_id', 'referral_created_user_name', 'billing_provider_pin', 'billing_provider_npi', 'referring_provider_npi', 'claim_form_name', 'claim_date', 'estimate_status', 'no_surprise_act_status', 'smartedit_message', 'last_transfer_user', 'last_transfer_wq_id', 'last_transfer_wq_nm', 'last_transfer_date', 'ar_notes', 'ar_at', 'qa_at', 'parent_id', 'invoke_date', 'CE_emp_id', 'QA_emp_id', 'chart_status', 'ce_hold_reason', 'qa_hold_reason', 'qa_work_status', 'QA_required_sampling', 'QA_rework_comments', 'QA_status_code', 'QA_sub_status_code', 'qa_classification', 'qa_category', 'qa_scope', 'QA_followup_date', 'CE_status_code', 'CE_sub_status_code', 'CE_followup_date', 'annex_coder_trends', 'annex_qa_trends', 'cpt_trends', 'icd_trends', 'modifiers', 'QA_comments_count', 'coder_work_date', 'qa_work_date', 'coder_rework_status', 'coder_rework_reason', 'coder_error_count', 'qa_error_count', 'tl_error_count', 'tl_comments', 'ar_status_code', 'ar_action_code', 'ar_manager_rebuttal_status', 'ar_manager_rebuttal_comments', 'qa_manager_rebuttal_status', 'qa_manager_rebuttal_comments', 'created_at', 'production_type', 'ar_substatus_codes', 'ar_denial_codes', 'updated_at', 'deleted_at'
    ];


}
