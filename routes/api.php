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
    Route::any('ncg_medical_ncg_hudson_ar', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgHudsonAR');
    Route::any('ncg_medical_ncg_hudson_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgHudsonARDuplicates');
    Route::any('ncg_medical_ncg_hsc_ar', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgHscAR');
    Route::any('ncg_medical_ncg_hsc_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgHscARDuplicates');
    Route::any('ncg_medical_ncg_psss_ar', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgPscAR');
    Route::any('ncg_medical_ncg_psss_ar_duplicate', 'App\Http\Controllers\ProjectAutomationController@NcgMedicalNcgPscARDuplicates');
    
    

});