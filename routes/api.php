<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1_projects'], function() {
    Route::post('prjoect_details', 'App\Http\Controllers\AIGController@projectDetails');
    Route::any('file_not_in_folder', 'App\Http\Controllers\AIGController@fileNotInFolder')->name('fileNotInFolder');
    Route::any('empty_reocrd_mail', 'App\Http\Controllers\AIGController@emptyRecordMail')->name('emptyRecordMail');
    Route::any('duplicate_entry_mail', 'App\Http\Controllers\AIGController@duplicateEntryMail')->name('duplicateEntryMail');
    Route::any('file_format_not_match', 'App\Http\Controllers\AIGController@fileFormatNotMatch')->name('fileFormatNotMatch');
});
Route::group(['prefix' => 'projects'], function() {
    Route::any('project_file_not_in_folder', 'App\Http\Controllers\ProjectController@projectFileNotInFolder');
    Route::any('sioux_land_mental_health', 'App\Http\Controllers\ProjectAutomationController@siouxlandMentalHealth');
    Route::any('saco_river_medical_group', 'App\Http\Controllers\ProjectAutomationController@sacoRiverMedicalGroup');
    Route::any('cancer_care_specialist_ip', 'App\Http\Controllers\ProjectAutomationController@cancerCareSpecialistIP');
    Route::any('inventory_exe_file', 'App\Http\Controllers\ProjectAutomationController@inventoryExeFile');
    Route::any('saco_river_medical_group_duplicate', 'App\Http\Controllers\ProjectAutomationController@sacoRiverMedicalGroupDuplicates');
    Route::any('project_error_mail', 'App\Http\Controllers\ProjectController@projectErrorMail');
    // Resolve Route Files
    Route::any('onpoint', 'App\Http\Controllers\ProjectAutomationController@onpoint');
    Route::any('onpoint_duplicate', 'App\Http\Controllers\ProjectAutomationController@onpointDuplicates');
    Route::any('nau_urology', 'App\Http\Controllers\ProjectAutomationController@nauUrology');
    Route::any('nau_urology_duplicate', 'App\Http\Controllers\ProjectAutomationController@nauUrologyDuplicates');
    Route::any('chestnut_ar', 'App\Http\Controllers\ProjectAutomationController@chestnutAr');
    Route::any('chestnut_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@chestnutArDuplicates');    
    Route::any('mhaw_ar', 'App\Http\Controllers\ProjectAutomationController@millenniumHealthAr');
    Route::any('mhaw_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@millenniumHealthArDuplicates');   
    Route::any('lsc_ar', 'App\Http\Controllers\ProjectAutomationController@lowerShoreClinicAr');
    Route::any('lsc_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@lowerShoreClinicArDuplicates');
    Route::any('matc_ar', 'App\Http\Controllers\ProjectAutomationController@maryvilleAddictionTreatmentCenterAr');
    Route::any('matc_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@maryvilleAddictionTreatmentCenterArDuplicates');    
    Route::any('gchs_ar', 'App\Http\Controllers\ProjectAutomationController@greenClinicHealthSystemAr');
    Route::any('gchs_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@greenClinicHealthSystemArDuplicates');
    Route::any('arthritis_sports_orthopeadics_pc_ar', 'App\Http\Controllers\ProjectAutomationController@arthritisSportsOrthopeadicsPCAr');
    Route::any('arthritis_sports_orthopeadics_pc_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@arthritisSportsOrthopeadicsPCArDuplicates');
    Route::any('rapid_city_medical_center_ar', 'App\Http\Controllers\ProjectAutomationController@rapidCityMedicalCenterAr');
    Route::any('rapid_city_medical_center_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@rapidCityMedicalCenterArDuplicates');
    Route::any('rhea_medical_center_ar', 'App\Http\Controllers\ProjectAutomationController@rheaMedicalCentre');
    Route::any('rhea_medical_center_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@rheaMedicalCentreARDuplicates');
    Route::any('associates_of_plastic_surgery_ar', 'App\Http\Controllers\ProjectAutomationController@AssociatesofPlasticSurgeryAR');
    Route::any('associates_of_plastic_surgery_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@AssociatesofPlasticSurgeryARDuplicates');

    Route::any('neurology_associates_ar', 'App\Http\Controllers\ProjectAutomationController@NeurologyAssociatesAR');
    Route::any('neurology_associates_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@NeurologyAssociatesARDuplicates');
    Route::any('leak_urology_ar', 'App\Http\Controllers\ProjectAutomationController@leakUrologyAR');
    Route::any('leak_urology_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@leakUrologyARDuplicates');
    Route::any('boston_mountain_rural_health_center_ar', 'App\Http\Controllers\ProjectAutomationController@BostonMountainRuralHealthCenterAR');
    Route::any('boston_mountain_rural_health_center_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@BostonMountainRuralHealthCenterARDuplicates');
    Route::any('colon_and_rectal_surgery_ar', 'App\Http\Controllers\ProjectAutomationController@ColonAndRectalSurgeryAR');
    Route::any('colon_and_rectal_surgery_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@ColonAndRectalSurgeryARDuplicates');
    Route::any('ncg_medical_ncg_gottenger_ar', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgGottengerAR');
    Route::any('ncg_medical_ncg_gottenger_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgGottengerARDuplicates');
    Route::any('ncg_medical_ncg_hsc_ar', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgHscAR');
    Route::any('ncg_medical_ncg_hsc_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgHscARDuplicates');
    Route::any('ncg_medical_ncg_hudson_ar', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgHudsonAR');
    Route::any('ncg_medical_ncg_hudson_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgHudsonARDuplicates');
    Route::any('ncg_medical_ncg_psssf_ar', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgPsssfAR');
    Route::any('ncg_medical_ncg_psssf_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgPsssfARDuplicates');
    Route::any('srmg_ar', 'App\Http\Controllers\ProjectAutomationController@srmgAR');
    Route::any('srmg_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@srmgARDuplicates');
    Route::any('valley_urogynecology_associates_ar', 'App\Http\Controllers\ProjectAutomationController@ValleyUrogynecologyAssociatesAR');
    Route::any('valley_urogynecology_associates_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@ValleyUrogynecologyAssociatesARDuplicates');
    Route::any('advanced_medical_billing_collections_prn_ar', 'App\Http\Controllers\ProjectAutomationController@advancedMedicalBillingCollectionsPrnAr');
    Route::any('advanced_medical_billing_collections_prn_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@advancedMedicalBillingCollectionsPrnArDuplicates');
    Route::any('colorado_facial_plastic_surgery_ar', 'App\Http\Controllers\ProjectAutomationController@coloradoFacialPlasticSurgeryAr');
    Route::any('colorado_facial_plastic_surgery_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@coloradoFacialPlasticSurgeryArDuplicates');
    Route::any('day_kimball_medical_group_ar', 'App\Http\Controllers\ProjectAutomationController@dayKimballMedicalGroupAr');
    Route::any('day_kimball_medical_group_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@dayKimballMedicalGroupArDuplicates');
    Route::any('bert_nash_community_mental_health_center_ar', 'App\Http\Controllers\ProjectAutomationController@bertNashCommunityMentalHealthCenterAR');
    Route::any('bert_nash_community_mental_health_center_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@bertNashCommunityMentalHealthCenterARDuplicates');

    Route::any('retina_northwest_ar', 'App\Http\Controllers\ProjectAutomationController@RetinaNorthwestAR');
    Route::any('retina_northwest_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@RetinaNorthwestARDuplicates');
    Route::any('mayers_memorial_hospital_ar', 'App\Http\Controllers\ProjectAutomationController@mayersMemorialHospitalAR');
    Route::any('mayers_memorial_hospital_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@mayersMemorialHospitalARDuplicates');
    Route::any('restoration_healthcare_ar', 'App\Http\Controllers\ProjectAutomationController@restorationHealthcareAr');
    Route::any('restoration_healthcare_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@restorationHealthcareArDuplicates');
    Route::any('advanced_medical_billing_collections_ambc_ar', 'App\Http\Controllers\ProjectAutomationController@advancedMedicalBillingCollectionsAmbcAr');
    Route::any('advanced_medical_billing_collections_ambc_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@advancedMedicalBillingCollectionsAmbcArDuplicates');
    Route::any('hockanum_valley_community_council_ar', 'App\Http\Controllers\ProjectAutomationController@hockanumValleyCommunityCouncilAr');
    Route::any('hockanum_valley_community_council_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@hockanumValleyCommunityCouncilArDuplicates');
    Route::any('adams_county_regional_medical_center_ar', 'App\Http\Controllers\ProjectAutomationController@adamsCountyRegionalMedicalCenterAr');
    Route::any('adams_county_regional_medical_center_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@adamsCountyRegionalMedicalCenterArDuplicates');
    Route::any('lynne_alba_speech_therapy_solutions_ar', 'App\Http\Controllers\ProjectAutomationController@lynneAlbaSpeechTherapySolutionsAr');
    Route::any('lynne_alba_speech_therapy_solutions_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@lynneAlbaSpeechTherapySolutionsArDuplicates');
    Route::any('marion_eye_center_optical_ar', 'App\Http\Controllers\ProjectAutomationController@MarionEyeCenterOpticalAR');
    Route::any('marion_eye_center_optical_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@MarionEyeCenterOpticalARArDuplicates');
    Route::any('medValue_offshore_solutions_inc_ar', 'App\Http\Controllers\ProjectAutomationController@MedValueOffshoreSolutionsIncAR');
    Route::any('medValue_offshore_solutions_inc_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@MedValueOffshoreSolutionsIncArDuplicates');
    Route::any('imaging_for_women_ar', 'App\Http\Controllers\ProjectAutomationController@ImagingforWomenAR');
    Route::any('imaging_for_women_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@ImagingforWomenArDuplicates');
    Route::any('missoula_bone_and_joint_surgery_center_llc_ar', 'App\Http\Controllers\ProjectAutomationController@MissoulaBoneANDJointSurgeryCenterLLCAR');
    Route::any('missoula_bone_and_joint_surgery_center_llc_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@MissoulaBoneANDJointSurgeryCenterLLCArDuplicates');
    Route::any('missoula_bone_and_joint_surgery_center_llc_modmed_ar', 'App\Http\Controllers\ProjectAutomationController@MissoulaBoneANDJointSurgeryCenterLLCModmedAR');
    Route::any('missoula_bone_and_joint_surgery_center_llc_modmed_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@MissoulaBoneANDJointSurgeryCenterLLCModmedArDuplicates');
    Route::any('omni_healthcare_ar', 'App\Http\Controllers\ProjectAutomationController@omniHealthcareAr');
    Route::any('omni_healthcare_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@omniHealthcareArDuplicates');
    Route::any('nationwide_medical_billing_ar', 'App\Http\Controllers\ProjectAutomationController@NationwideMedicalBillingAR');
    Route::any('nationwide_medical_billing_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@NationwideMedicalBillingArDuplicates');
    Route::any('nex_trust_billing_ar', 'App\Http\Controllers\ProjectAutomationController@NexTrustBillingAR');
    Route::any('nex_trust_billing_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@NexTrustBillingArDuplicates');
    Route::any('william_beeRirie_ar', 'App\Http\Controllers\ProjectAutomationController@williamBeeRirieAR');
    Route::any('william_beeRirie_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@williamBeeRirieARDuplicates');    
    Route::any('prine_health_ar', 'App\Http\Controllers\ProjectAutomationController@prineHealthAr');
    Route::any('prine_health_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@prineHealthArDuplicates');
    Route::any('preferred_behavioral_health_group_ar', 'App\Http\Controllers\ProjectAutomationController@preferredBehavioralHealthGroupAr');
    Route::any('preferred_behavioral_health_group_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@preferredBehavioralHealthGroupArDuplicates');
    Route::any('vein_institute_ar', 'App\Http\Controllers\ProjectAutomationController@veinInstituteAr');
    Route::any('vein_institute_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@veinInstituteArDuplicates');
    Route::any('sewickley_eye_group_ar', 'App\Http\Controllers\ProjectAutomationController@sewickleyEyeGroupAr');
    Route::any('sewickley_eye_group_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@sewickleyEyeGroupArDuplicates');
    Route::any('precision_billing_and_consulting_services_ar', 'App\Http\Controllers\ProjectAutomationController@precisionBillingAndConsultingServicesAr');
    Route::any('precision_billing_and_consulting_services_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@precisionBillingAndConsultingServicesArDuplicates');
    Route::any('siouxland_mental_health_center_ar', 'App\Http\Controllers\ProjectAutomationController@SiouxlandMentalHealthCenterAR');
    Route::any('siouxland_mental_health_center_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@SiouxlandMentalHealthCenterARArDuplicates');
    Route::any('the_queens_health_system_ar', 'App\Http\Controllers\ProjectAutomationController@TheQueensHealthSystemAR');
    Route::any('the_queens_health_system_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@TheQueensHealthSystemArDuplicates');
    Route::any('boozman_hoff_eye_center_ar', 'App\Http\Controllers\ProjectAutomationController@boozmanHoffEyeCenterAr');
    Route::any('boozman_hoff_eye_center_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@boozmanHoffEyeCenterArDuplicates');
    Route::any('reno_orthopedic_center_ar', 'App\Http\Controllers\ProjectAutomationController@renoOrthopedicCenterAr');
    Route::any('reno_orthopedic_center_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@renoOrthopedicCenterArDuplicates');
    Route::any('sbgmg_eligiblity_verification', 'App\Http\Controllers\ProjectAutomationController@sbgmgEligiblityVerification');
    Route::any('sbgmg_eligiblity_verification_duplicate', 'App\Http\Controllers\ProjectAutomationController@sbgmgEligiblityVerificationDuplicates');
    Route::any('pbhg_eligibility_verification', 'App\Http\Controllers\ProjectAutomationController@pbhgEligibilityVerification');
    Route::any('pbhg_eligibility_verification_duplicate', 'App\Http\Controllers\ProjectAutomationController@pbhgEligibilityVerificationDuplicates');
    Route::any('ms_eligiblity_verification', 'App\Http\Controllers\ProjectAutomationController@msEligiblityVerification');
    Route::any('ms_eligiblity_verification_duplicate', 'App\Http\Controllers\ProjectAutomationController@msEligiblityVerificationDuplicates');
    Route::any('smb_ar_evolution', 'App\Http\Controllers\ProjectAutomationController@smbArEvolution');
    Route::any('smb_ar_evolution_duplicate', 'App\Http\Controllers\ProjectAutomationController@smbArEvolutionDuplicates');
    Route::any('smb_ar_proactive', 'App\Http\Controllers\ProjectAutomationController@smbArProactive');
    Route::any('smb_ar_proactive_duplicate', 'App\Http\Controllers\ProjectAutomationController@smbArProactiveDuplicates');
    Route::any('aops_pre_auth_verification', 'App\Http\Controllers\ProjectAuthAutomationController@aopsPreAuthVerification');
    Route::any('aops_pre_auth_verification_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@aopsPreAuthVerificationDuplicates');
    Route::any('ncg_medical_ncg_vob', 'App\Http\Controllers\ProjectAuthAutomationController@NcgMedicalNcgVob');
    Route::any('ncg_medical_ncg_vob_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@NcgMedicalNcgVobDuplicates');
    Route::any('rh_eligibility_verification', 'App\Http\Controllers\ProjectAuthAutomationController@rhEligibilityVerification');
    Route::any('rh_eligibility_verification_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@rhEligibilityVerificationDuplicates');
    Route::any('agg_pre_auth_verification', 'App\Http\Controllers\ProjectAuthAutomationController@aggPreAuthVerification');
    Route::any('agg_pre_auth_verification_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@aggPreAuthVerificationDuplicates');
    Route::any('rcm_ev_vob', 'App\Http\Controllers\ProjectAuthAutomationController@rcmEvVob');
    Route::any('rcm_ev_vob_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@rcmEvVobDuplicates');
    Route::any('pmg_ar', 'App\Http\Controllers\ProjectAuthAutomationController@premierMedicalGroupAr');
    Route::any('pmg_ar_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@premierMedicalGroupArDuplicates');
    Route::any('integris_health_ar', 'App\Http\Controllers\ProjectAuthAutomationController@integrisHealthAr');
    Route::any('integris_health_ar_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@integrisHealthArDuplicates');
    Route::any('ms_charge_entry', 'App\Http\Controllers\ProjectAuthAutomationController@msChargeEntry');
    Route::any('ms_charge_entry_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@msChargeEntryDuplicates');
    Route::any('dkmg_charge_entry', 'App\Http\Controllers\ProjectAuthAutomationController@dkmgChargeEntry');
    Route::any('dkmg_charge_entry_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@dkmgChargeEntryDuplicates');
    Route::any('smhc_ev_vob', 'App\Http\Controllers\ProjectAuthAutomationController@smhcEvVob');
    Route::any('smhc_ev_vob_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@smhcEvVobDuplicates');
    Route::any('lasts_charge_entry', 'App\Http\Controllers\ProjectAuthAutomationController@lastsChargeEntry');
    Route::any('lasts_charge_entry_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@lastsChargeEntryDuplicates');
    Route::any('mosi_ar', 'App\Http\Controllers\ProjectAuthAutomationController@medValueOffshoreSolutionsIncAr');
    Route::any('mosi_ar_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@medValueOffshoreSolutionsIncArDuplicates');
    Route::any('rlmg_ar', 'App\Http\Controllers\ProjectAuthAutomationController@rossLegacyMedicalGroupAr');
    Route::any('rlmg_ar_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@rossLegacyMedicalGroupArArDuplicates');
    Route::any('oscotr_ar', 'App\Http\Controllers\ProjectAuthAutomationController@orthopaedicSpineCenterOfTheRockiesAr');
    Route::any('oscotr_ar_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@orthopaedicSpineCenterOfTheRockiesArDuplicates');
    Route::any('mpms_ar', 'App\Http\Controllers\ProjectAuthAutomationController@medicalPracticeManagementServicesAr');
    Route::any('mpms_ar_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@medicalPracticeManagementServicesArDuplicates');
    Route::any('ga_ar', 'App\Http\Controllers\ProjectAuthAutomationController@gastrointestinalAssociateAr');
    Route::any('ga_ar_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@gastrointestinalAssociateArDuplicates');
    Route::any('crmc_ar', 'App\Http\Controllers\ProjectAuthAutomationController@colquittRegionalMedicalCenterAr');
    Route::any('crmc_ar_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@colquittRegionalMedicalCenterArDuplicates');
    Route::any('smmi_ar_denver', 'App\Http\Controllers\ProjectAuthAutomationController@SmmiArDenver');
    Route::any('smmi_ar_denver_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@SmmiArDenverDuplicates');
    Route::any('smmi_ar_dallas', 'App\Http\Controllers\ProjectAuthAutomationController@SmmiArDallas');
    Route::any('smmi_ar_dallas_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@SmmiArDallasDuplicates');
    Route::any('tqhs_ar_denials', 'App\Http\Controllers\ProjectAuthAutomationController@tqhsArDenials');
    Route::any('tqhs_ar_denials_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@tqhsArDenialsDuplicates');
    Route::any('tqhs_ar_no_response_tfi', 'App\Http\Controllers\ProjectAuthAutomationController@tqhsARNoResponseTfi');
    Route::any('tqhs_ar_no_response_tfi_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@tqhsARNoResponseTfiDuplicates');
    Route::any('tqhs_ar_rhc_no_response', 'App\Http\Controllers\ProjectAuthAutomationController@tqhsArRhcNoResponse');
    Route::any('tqhs_ar_rhc_no_response_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@tqhsArRhcNoResponseDuplicates');
    Route::any('tqhs_ar_rhc_denials', 'App\Http\Controllers\ProjectAuthAutomationController@tqhsArRhcDenials');
    Route::any('tqhs_ar_rhc_denials_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@tqhsArRhcDenialsDuplicates');
    Route::any('dkmg_ar_behavioral_mental_health', 'App\Http\Controllers\ProjectAuthAutomationController@dkmgArBehavioralMentalHealth');
    Route::any('dkmg_ar_behavioral_mental_health_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@dkmgArBehavioralMentalHealthDuplicates');
    Route::any('pbhg_pre_auth_verification', 'App\Http\Controllers\ProjectAuthAutomationController@pbhgPreAuthVerification');
    Route::any('pbhg_pre_auth_verification_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@pbhgPreAuthVerificationDuplicates');
    Route::any('ivc_ev_vob', 'App\Http\Controllers\ProjectAuthAutomationController@insightVascularConsultantsEVandVOB');
    Route::any('ivc_ev_vob_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@insightVascularConsultantsEVandVOBDuplicates');
    Route::any('chc_ev', 'App\Http\Controllers\ProjectAuthAutomationController@chcEligibilityVerification');
    Route::any('chc_ev_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@chcEligibilityVerificationDuplicates');
    Route::any('thc_ar', 'App\Http\Controllers\ProjectAuthAutomationController@theHeritageClinicAR');
    Route::any('thc_ar_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@theHeritageClinicARDuplicates');
    Route::any('crmhs_ar', 'App\Http\Controllers\ProjectAuthAutomationController@columbiaRiverMentalHealthServiceAR');
    Route::any('crmhs_ar_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@columbiaRiverMentalHealthServiceARDuplicates');
    Route::any('rcsa_ar', 'App\Http\Controllers\ProjectAuthAutomationController@riverCitySkinAndAestheticsAR');
    Route::any('rcsa_ar_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@riverCitySkinAndAestheticsArARDuplicates');
    Route::any('tqhs_adjudication', 'App\Http\Controllers\ProjectAuthAutomationController@tqhsAdjudication');
    Route::any('tqhs_adjudication_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@tqhsAdjudicationDuplicates');
    Route::any('tqhs_coding_to_billing', 'App\Http\Controllers\ProjectAuthAutomationController@tqhsCodingToBilling');
    Route::any('tqhs_coding_to_billing_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@tqhsCodingToBillingDuplicates');
    Route::any('helixona_ar', 'App\Http\Controllers\ProjectAuthAutomationController@helixonaAR');
    Route::any('helixona_ar_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@helixonaARDuplicates');
    Route::any('smp_seleverstov_follow_up', 'App\Http\Controllers\ProjectAuthAutomationController@seleverstovMedicalPC');
    Route::any('smp_seleverstov_follow_up_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@seleverstovMedicalPCDuplicates');
    Route::any('phhe_phoenix_heart_follow_up', 'App\Http\Controllers\ProjectAuthAutomationController@phhePhoenixHeartFollowUp');
    Route::any('phhe_phoenix_heart_follow_up_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@phhePhoenixHeartFollowUpDuplicates');
    Route::any('seci_my_spectrum_follow_up', 'App\Http\Controllers\ProjectAuthAutomationController@seciMySpectrumFollowUp');
    Route::any('seci_my_spectrum_follow_up_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@seciMySpectrumFollowUpDuplicates');
    Route::any('esh_excelsior_follow_up', 'App\Http\Controllers\ProjectAuthAutomationController@eshExcelsiorFollowUp');
    Route::any('esh_excelsior_follow_up_duplicate', 'App\Http\Controllers\ProjectAuthAutomationController@eshExcelsiorFollowUpDuplicates');
    Route::any('get_project_columns', 'App\Http\Controllers\FormController@getProjectColumns');




    Route::any('production_auto_close', 'App\Http\Controllers\ProjectController@productionAutoClose');
    Route::any('alter_table_chart_status_column', 'App\Http\Controllers\ProjectController@alterTableChartStatusColumn');
    Route::any('production_insert', 'App\Http\Controllers\ProjectController@productionInsert');
    Route::any('backend_upload_template_exe_file', 'App\Http\Controllers\ProjectController@backendUploadTemplateExeFile');
    Route::any('bulk_excel_export', 'App\Http\Controllers\Reports\ReportsController@bulkExportApi');
    Route::any('project_day_wise_aims_production', 'App\Http\Controllers\ProjectController@projectDayWiseAimsProduction');
    Route::get('aims-production-results/{date}',  'App\Http\Controllers\ProjectController@getAimsProductionResults');
    Route::any('project_daterange_wise_aims_production','App\Http\Controllers\ProjectController@projectDateRangeWiseAimsProduction');
    Route::get('date-range-aims-production-results',  'App\Http\Controllers\ProjectController@getDateWiseRangeResults');



});