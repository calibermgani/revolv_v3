<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsEligibilityVerificationHistory extends Model
{
    use SoftDeletes;
     protected $table = 'ms_eligibility_verification_history';
    protected $fillable = [
        'id', 'account', 'ssn', 'last_name', 'first_name', 'middle_name', 'suffix', 'preferred_name', 'pronouns', 'dob', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country', 'email', 'preferred_phone', 'home_number', 'home_messages', 'mobile_number', 'mobile_messages', 'work_number', 'work_messages', 'other_number', 'other_messages', 'administrative_sex', 'marital_status', 'employment_status', 'last_appt', 'next_appt', 'next_appt_with', 'relationship_to_insured', 'payer', 'insured_id', 'secondary_payer', 'secondary_insured_id', 'tertiary_payer', 'tertiary_insured_id', 'quaternary_payer', 'quaternary_insured_id', 'clinicians', 'gender_identity', 'sexual_orientation', 'race', 'ethnicity', 'languages', 'religious_affiliation', 'text_messages', 'ar_notes', 'parent_id', 'invoke_date', 'CE_emp_id', 'QA_emp_id', 'chart_status', 'ce_hold_reason', 'qa_hold_reason', 'qa_work_status', 'QA_required_sampling', 'QA_rework_comments', 'QA_status_code', 'QA_sub_status_code', 'qa_classification', 'qa_category', 'qa_scope', 'QA_followup_date', 'CE_status_code', 'CE_sub_status_code', 'CE_followup_date', 'annex_coder_trends', 'annex_qa_trends', 'cpt_trends', 'icd_trends', 'modifiers', 'QA_comments_count', 'coder_work_date', 'qa_work_date', 'coder_rework_status', 'coder_rework_reason', 'coder_error_count', 'qa_error_count', 'tl_error_count', 'tl_comments', 'ar_status_code', 'ar_action_code', 'ar_manager_rebuttal_status', 'ar_manager_rebuttal_comments', 'qa_manager_rebuttal_status', 'qa_manager_rebuttal_comments', 'created_at', 'production_type', 'ar_substatus_codes', 'ar_denial_codes', 'qa_at', 'ar_at', 'sub_activity', 'activity', 'updated_at', 'deleted_at'
    ];


}
