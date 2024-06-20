<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

/* Authentication Module Start */
 Route::get('/', ['as' => 'login', 'uses' => 'App\Http\Controllers\Auth\LoginController@login']);
//Route::get('login', 'App\Http\Controllers\Auth\LoginController@login')->name('login');
Route::get('logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');
//Route::post('login', 'App\Http\Controllers\Auth\LoginController@postLogin')->name('login');
Route::get('check_user_password', 'App\Http\Controllers\Auth\LoginController@CheckUserPassword');
// Route::post('change_user_password', 'App\Http\Controllers\Auth\LoginController@ChangeUserPassword');
Route::any('/store-in-session', 'App\Http\Controllers\Auth\LoginController@storeInSession');

Route::any('dashboard', 'App\Http\Controllers\DashboardController@dashboard')->name('dashboard');
Route::any('production_dashboard', 'App\Http\Controllers\ProductionController@dashboard')->name('production_dashboard');
Route::any('clients', 'App\Http\Controllers\ProductionController@clients')->name('clients');
Route::any('sub_projects', 'App\Http\Controllers\ProductionController@getSubProjects')->name('subProjects');
Route::any('clients/handle_row', 'App\Http\Controllers\ProductionController@handleRowClick')->name('client.handleRow');
Route::any('projects/{clientName}', 'App\Http\Controllers\ProductionController@clientTabs')->name('client.tabs');
Route::any('projects_assigned/{clientName}/{subProjectName}', 'App\Http\Controllers\ProductionController@clientAssignedTab')->name('clientAssigned');
Route::any('projects_pending/{clientName}/{subProjectName}', 'App\Http\Controllers\ProductionController@clientPendingTab')->name('clientPending');
Route::any('projects_hold/{clientName}/{subProjectName}', 'App\Http\Controllers\ProductionController@clientHoldTab')->name('clientHold');
Route::any('projects_completed/{clientName}/{subProjectName}', 'App\Http\Controllers\ProductionController@clientCompletedTab')->name('clientCompleted');
Route::any('projects_Revoke/{clientName}/{subProjectName}', 'App\Http\Controllers\ProductionController@clientReworkTab')->name('clientRework');
Route::any('projects_duplicate/{clientName}/{subProjectName}', 'App\Http\Controllers\ProductionController@clientDuplicateTab')->name('clientDuplicate');
Route::any('clients_duplicate_status', 'App\Http\Controllers\ProductionController@clientsDuplicateStatus');
Route::any('form_creation', 'App\Http\Controllers\FormController@formCreationIndex')->name('formCreationIndex');
Route::any('form_configuration_store', 'App\Http\Controllers\FormController@formConfigurationStore')->name('formConfigurationStore');
Route::any('sub_project_list', 'App\Http\Controllers\FormController@getSubProjectList')->name('subProjectList');
Route::any('form_configuration_list', 'App\Http\Controllers\FormController@formConfigurationList')->name('formConfigurationList');
Route::any('form_edit/{project_id}/{sub_project_id}', 'App\Http\Controllers\FormController@formEdit')->name('formEdit');
Route::any('form_configuration_update', 'App\Http\Controllers\FormController@formConfigurationUpdate')->name('formConfigurationUpdate');
Route::any('project_store/{projectName}/{subProjectName}', 'App\Http\Controllers\ProductionController@clientsStore');
Route::any('procode_testing','App\Http\Controllers\DashboardController@procodeTesting');
Route::any('assignee_change', 'App\Http\Controllers\ProductionController@assigneeChange');
Route::any('caller_chart_work_logs', 'App\Http\Controllers\ProductionController@callerChartWorkLogs');
Route::any('client_completed_datas_details', 'App\Http\Controllers\ProductionController@clientCompletedDatasDetails');
Route::any('client_table_update', 'App\Http\Controllers\ProjectController@clientTableUpdate');
Route::any('project_update/{projectName}/{subProjectName}', 'App\Http\Controllers\ProductionController@clientsUpdate');
Route::any('client_view_details', 'App\Http\Controllers\ProductionController@clientViewDetails');
Route::any('reports', 'App\Http\Controllers\Reports\ReportsController@reporstIndex');
Route::any('reports/get_sub_projects', 'App\Http\Controllers\Reports\ReportsController@getSubProjects');
Route::any('reports/report_client_assigned_tab', 'App\Http\Controllers\Reports\ReportsController@reportClientAssignedTab');
Route::any('reports/report_client_columns_list', 'App\Http\Controllers\Reports\ReportsController@reportClientColumnsList');
Route::any('project_config_delete', 'App\Http\Controllers\FormController@projectConfigDelete');
Route::any('sampling', 'App\Http\Controllers\SettingController@qualitySampling');
Route::any('qa_sampling_store', 'App\Http\Controllers\SettingController@qualitySamplingStore');
Route::any('qa_sampling_update', 'App\Http\Controllers\SettingController@qualitySamplingUpdate');
Route::any('client_rework_datas_details', 'App\Http\Controllers\ProductionController@clientReworkDatasDetails');
Route::any('project_rework_update/{projectName}/{subProjectName}', 'App\Http\Controllers\ProductionController@clientsReworkUpdate');//
Route::any('projects_unassigned/{clientName}/{subProjectName}', 'App\Http\Controllers\ProductionController@clientUnAssignedTab')->name('clientUnAssigned');

Route::group(['prefix' => 'qa_production'], function () {
    Route::any('qa_clients', 'App\Http\Controllers\QA\QAProductionController@clients')->name('qaClients');
    Route::any('qa_sub_projects', 'App\Http\Controllers\QA\QAProductionController@getSubProjects')->name('qaSubProjects');
    Route::any('qa_projects_assigned/{clientName}/{subProjectName}', 'App\Http\Controllers\QA\QAProductionController@clientAssignedTab')->name('qaClientAssigned');
    Route::any('qa_projects_pending/{clientName}/{subProjectName}', 'App\Http\Controllers\QA\QAProductionController@clientPendingTab')->name('qaClientPending');
    Route::any('qa_projects_hold/{clientName}/{subProjectName}', 'App\Http\Controllers\QA\QAProductionController@clientHoldTab')->name('qaClientHold');
    Route::any('qa_projects_completed/{clientName}/{subProjectName}', 'App\Http\Controllers\QA\QAProductionController@clientCompletedTab')->name('cqaCientCompleted');
    Route::any('qa_projects_Revoke/{clientName}/{subProjectName}', 'App\Http\Controllers\QA\QAProductionController@clientReworkTab')->name('qaClientRework');
    Route::any('qa_projects_duplicate/{clientName}/{subProjectName}', 'App\Http\Controllers\QA\QAProductionController@clientDuplicateTab')->name('qaClientDuplicate');
    Route::any('qa_client_completed_datas_details', 'App\Http\Controllers\QA\QAProductionController@qaClientCompletedDatasDetails');
    Route::any('qa_client_view_details', 'App\Http\Controllers\QA\QAProductionController@qaClientViewDetails');
    Route::any('qa_project_store/{projectName}/{subProjectName}', 'App\Http\Controllers\QA\QAProductionController@clientsStore');
    Route::any('qa_project_update/{projectName}/{subProjectName}', 'App\Http\Controllers\QA\QAProductionController@clientsUpdate');
    Route::any('qa_sub_status_list', 'App\Http\Controllers\QA\QAProductionController@qaSubStatusList');
    Route::any('qa_projects_auto_close/{clientName}/{subProjectName}', 'App\Http\Controllers\QA\QAProductionController@clientAutoClose');
    Route::any('sampling_assignee', 'App\Http\Controllers\QA\QAProductionController@samplingAssignee');
});


    Route::any('procode_user_dashboard','App\Http\Controllers\DashboardController@procodeUserDashboard');
    Route::group(['prefix' => 'mom'], function () {
        // Route::post('full_calender/action', 'App\Http\Controllers\MOM\MomController@action');
        Route::any('mom_dashboard', 'App\Http\Controllers\MOM\MomController@index');
        Route::any('mom_add/{date}', 'App\Http\Controllers\MOM\MomController@momAdd');
        Route::any('mom_store', 'App\Http\Controllers\MOM\MomController@momStore');
        Route::any('mom_edit/{id}', 'App\Http\Controllers\MOM\MomController@momEdit');
        Route::any('mom_update', 'App\Http\Controllers\MOM\MomController@momUpdate');
        Route::any('mom_delete', 'App\Http\Controllers\MOM\MomController@momDelete');
    });

    Route::group(['prefix' => 'dashboard'], function () {
        Route::any('sub_projects', 'App\Http\Controllers\DashboardController@getSubProjects')->name('subProjects');
        Route::any('procode_manager_dashboard','App\Http\Controllers\DashboardController@procodeManagerDashboard');
        Route::any('users_sub_projects', 'App\Http\Controllers\DashboardController@getUsersWithSubProjects');
        Route::any('calendar_filter','App\Http\Controllers\DashboardController@getCalendarFilter');
        Route::any('projects_calendar_filter','App\Http\Controllers\DashboardController@prjCalendarFilter');
        Route::any('mgr_projects_calendar_filter','App\Http\Controllers\DashboardController@mgrPrjCalendarFilter');

    });
    Route::any('project_work_mail', 'App\Http\Controllers\ProjectController@projectWorkMail');

    Route::group(['prefix' => 'sop'], function () {
        Route::any('sop_upload', 'App\Http\Controllers\SettingController@sopImportData');
        Route::any('sub_project_list', 'App\Http\Controllers\SettingController@getSubProjectList');
        Route::any('sop_doc_store', 'App\Http\Controllers\SettingController@sopDocStore');
        Route::any('sop_list', 'App\Http\Controllers\SettingController@sopList');
    });

    Route::group(['prefix' => 'permission'], function () {
        Route::any('', 'App\Http\Controllers\MenuPermissionController@index');
        Route::any('grand_permission', 'App\Http\Controllers\MenuPermissionController@store');
        Route::any('table_permission/{table}', 'MenuPermissionController@getColumnListPermission');
        Route::any('user_page_permission/{user_id}/{page}', 'MenuPermissionController@userPermissionSettings');
        Route::any('user_grand_page_permission', 'MenuPermissionController@page_permission_store');
        Route::any('side_menu_list', 'MenuPermissionController@SideMenuList');
    });
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
