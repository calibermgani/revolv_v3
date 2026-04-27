<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DkmgArBehavioralMentalHealthRevokeHistory extends Model
{
    use SoftDeletes;
     protected $table = 'dkmg_ar_behavioral_mental_health_revoke_history';
    protected $fillable = [
        'id', 'claim_id', 'patient_id', 'patient_name', 'dos', 'patient_dob', 'cstm_ins_grpng', 'ins_pkg_id', 'ins_report_cat', 'fc', 'proccode', 'proccode_grp', 'rndrng_prvdr', 'sup_prvdr', 'svc_dprtmnt', 'rndrng_prvdr_mdcl_grp', 'sup_prvdr_mdcl_grp', 'curr_athena_kick_code', 'curr_athena_kick_code_rej_rsn', 'currenterrorfull', 'curr_glbl_rule_rej_rsn', 'curr_glbl_rule', 'srvbucket_0_to_30', 'srvbucket_31_to_60', 'srvbucket_91_to_120', 'srvbucket_121_to_150', 'srvbucket_151_to_180', 'srvbucket_greater_than_180', 'srvbucket_total', 'cstatus', 'lstactiondate', 'trnsfr_type', 'ar_notes', 'ar_at', 'qa_at', 'parent_id', 'invoke_date', 'CE_emp_id', 'QA_emp_id', 'chart_status', 'ce_hold_reason', 'qa_hold_reason', 'qa_work_status', 'QA_required_sampling', 'QA_rework_comments', 'QA_status_code', 'QA_sub_status_code', 'qa_classification', 'qa_category', 'qa_scope', 'QA_followup_date', 'CE_status_code', 'CE_sub_status_code', 'CE_followup_date', 'annex_coder_trends', 'annex_qa_trends', 'cpt_trends', 'icd_trends', 'modifiers', 'QA_comments_count', 'coder_work_date', 'qa_work_date', 'coder_rework_status', 'coder_rework_reason', 'coder_error_count', 'qa_error_count', 'tl_error_count', 'tl_comments', 'ar_status_code', 'ar_action_code', 'ar_manager_rebuttal_status', 'ar_manager_rebuttal_comments', 'qa_manager_rebuttal_status', 'qa_manager_rebuttal_comments', 'created_at', 'production_type', 'capa', 'rca', 'sub_status_code', 'status_code', 'ar_substatus_codes', 'ar_denial_codes', 'updated_at', 'deleted_at'
    ];


}
