<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Helper\Admin\Helpers as Helpers;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Models\InventoryErrorLogs;
use Carbon\Carbon;
use App\Jobs\GetUserNameByEmpId;
use Illuminate\Support\Facades\Cache;
use App\Models\projectInputSetting;
use App\Jobs\getProjectResourceListJob;
use App\Models\ProjectReason;
use App\Models\formConfiguration;
class ReportsController extends Controller
{
    public function reporstIndex(){
        return view('reports.index');
    }
    public function getSubProjects(Request $request){
        try {
            $subProject = Helpers::subProjectList($request->project_id);
            // $user = Helpers::getprojectResourceList($request->project_id);
        
            if (!empty($request->project_id) && is_string($request->project_id)) {
                // getProjectResourceListJob::dispatch($request->project_id)->delay(now()->addSeconds(5));
                // $prjResourceCacheKey = 'project_'.$request->project_id.'prjResourceList' ;
                // $user = Cache::get($prjResourceCacheKey, 0); 
                $user=Helpers::getprojectResourceList($request->project_id); 
            }  else {
                $user = [];
            }           
            return response()->json(['success' => true, 'subProject' => $subProject, 'resource' => $user]);
        } catch (Exception $e) {
            log::debug($e->getMessage());
        }
    }
    public function reportClientAssignedTab(Request $request) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            $client = new Client(['verify' => false]);
            try {
                $subProject = Helpers::subProjectList($request->project_id);
                $decodedClientName = Helpers::projectName($request->project_id) != null ? Helpers::projectName($request->project_id)->project_name : null;
                $decodedsubProjectName = $request->sub_project_id == null ? 'project' :($request->project_id != null ? Helpers::subProjectName($request->project_id, $request->sub_project_id)->sub_project_name : null);
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $columnsHeader=[];
                if (Schema::hasTable($table_name)) {
                    if ($decodedsubProjectName == 'project' && count($subProject) == 1) {
                        $column_names = DB::select("DESCRIBE $table_name");
                        $columns = array_column($column_names, 'Field');
                        $columnsToExclude = ['QA_required_sampling', 'QA_followup_date', 'annex_coder_trends', 'annex_qa_trends', 'qa_cpt_trends', 'qa_icd_trends', 'qa_modifiers', 'CE_status_code', 'CE_sub_status_code', 'CE_followup_date', 'updated_at', 'created_at', 'deleted_at','cpt_trends','icd_trends','modifiers'];
                        $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                            return !in_array($column, $columnsToExclude);
                        });
                    } else if ($decodedsubProjectName !== 'project') {
                        $column_names = DB::select("DESCRIBE $table_name");
                        $columns = array_column($column_names, 'Field');
                        $columnsToExclude = ['QA_required_sampling','QA_followup_date', 'annex_coder_trends', 'annex_qa_trends','qa_cpt_trends', 'qa_icd_trends', 'qa_modifiers', 'CE_status_code','CE_sub_status_code','CE_followup_date','updated_at','created_at', 'deleted_at','cpt_trends','icd_trends','modifiers'];
                        $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                            return !in_array($column, $columnsToExclude);
                        });
                    }
                }
                if($request->sub_project_id != null && $request->sub_project_id != "") {
                    $statusActionShow = projectInputSetting::where('sub_project_id',$request->sub_project_id)->first();                                                                                                                              
                } else {
                    $statusActionShow = null;
                } 
                if($statusActionShow != null) {
                    if($statusActionShow->sub_project_id == $request->sub_project_id && $statusActionShow->status_input != 1) {
                        $key = array_search('ar_status_code', $columnsHeader);
                                if ($key !== false) {
                                    unset($columnsHeader[$key]); 
                                }           
                    }
                    if($statusActionShow->sub_project_id == $request->sub_project_id && $statusActionShow->action_input != 1) {
                        $key = array_search('ar_action_code', $columnsHeader);
                                if ($key !== false) {
                                    unset($columnsHeader[$key]); 
                                }           
                    }

                }
                // $hideVal=projectInputSetting::select('sub_project_id')->get();
                //     foreach($hideVal as $innerVal) {
                //         $hideValArray[] = $innerVal->sub_project_id;
                //     }
                //     if(in_array($request->sub_project_id,$hideValArray)) {                      
                //         $key = array_search('ar_action_code', $columnsHeader);
                //         if ($key !== false) {
                //             unset($columnsHeader[$key]); 
                //         }                       
                //     } 
                  
                return response()->json([
                    'success' => true,
                    'columnsHeader' => $columnsHeader,
                ]);
            } catch (Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function reportClientColumnsList(Request $request) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            $client = new Client(['verify' => false]);
            try {
                $decodedClientName = Helpers::projectName($request->project_id)->project_name;
               // $decodedsubProjectName = $request->sub_project_id == null ? 'project' :Helpers::subProjectName($request->project_id, $request->sub_project_id)->sub_project_name;
                $decodedsubProjectName = $request->sub_project_id == null ? 'project' :($request->project_id != null ? (Helpers::subProjectName($request->project_id, $request->sub_project_id) != null ?Helpers::subProjectName($request->project_id, $request->sub_project_id)->sub_project_name : null) : null);
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)).'_datas','_');
                if (isset($request->work_date) && !empty($request->work_date)) {
                    $work_date = explode(' - ', $request->work_date);
                    $start_date = date('Y-m-d 17:00:00', strtotime($work_date[0]));
                    $end_date = date('Y-m-d 09:00:00', strtotime($work_date[1] . ' +1 day'));
                }else{
                    $start_date = "";
                    $end_date = "";
                }
                if (isset($request->checkedValues)) {
                    if ($request->checkedValues[0] === 'all') {
                        $checkedValues = array_diff($request->checkedValues, ['all']);
                    }else{
                        $checkedValues = $request->checkedValues;
                    }
                    $columnsHeader = implode(',', $checkedValues);
                    $columns = [
                        DB::raw($columnsHeader),
                        "caller_charts_work_logs.work_time",
                        "caller_charts_work_logs.record_status"
                    ];
                    
                    // Check if the columns exist in the table
                    if (Schema::hasColumn($table_name, 'qa_cpt_trends')) {
                        $columns[] = 'qa_cpt_trends';
                    }
                    if (Schema::hasColumn($table_name, 'qa_icd_trends')) {
                        $columns[] = 'qa_icd_trends';
                    }
                    if (Schema::hasColumn($table_name, 'qa_modifiers')) {
                        $columns[] = 'qa_modifiers';
                    }
                    $client_data = DB::table($table_name)
                        // ->select([
                        //     DB::raw($columnsHeader),
                        //     "caller_charts_work_logs.work_time","caller_charts_work_logs.record_status",'qa_cpt_trends', 'qa_icd_trends', 'qa_modifiers'
                        //     // DB::raw("TIME_FORMAT(SEC_TO_TIME(TIMESTAMPDIFF(SECOND, caller_charts_work_logs.start_time, caller_charts_work_logs.end_time)), '%H:%i:%s') AS work_hours")
                        // ])
                        ->select($columns)
                        ->where('caller_charts_work_logs.project_id', '=', $request->project_id)
                        ->where('caller_charts_work_logs.sub_project_id', '=', $request->sub_project_id)
                        ->join('caller_charts_work_logs', 'caller_charts_work_logs.record_id', '=', $table_name . '.parent_id')
                        ->where(function ($query) use ($start_date, $end_date) {
                            if (!empty($start_date) && !empty($end_date)) {
                                $query->whereBetween('caller_charts_work_logs.start_time', [$start_date, $end_date]);
                            }else{
                                $query;
                            }
                        })
                        ->where(function ($query) use ($request) {
                            if ($request->user) {
                                $query->where('CE_emp_id',$request->user);
                                $query->orWhere('QA_emp_id',$request->user);
                            }else{
                                $query;
                            }
                        })
                        ->where(function ($query) use ($request) {

                            if ($request->client_status) {
                                // $query->where('chart_status',$request->client_status);
                                $query->where('caller_charts_work_logs.record_status', $request->client_status);
                            }else{
                                $query;
                            }
                        })

                        ->get();
                } else {
                    $client_data = [];
                }//dd($client_data);
                // if (count($client_data) > 0) {
                $body_info = '<table class="table table-separate table-head-custom no-footer dtr-column clients_list_filter" id="report_list"><thead><tr>';
                $additionalValues = ['aging','aging_range'];
                $checkedValues = array_merge($checkedValues, $additionalValues);
                $checkedValues[] = 'work_hours'; $agingCount = $agingRange = null;
                foreach ($checkedValues as $key => $header) {
                    if ($header == 'chart_status') {
                        $body_info .= '<th>Charge Status </th>';
                    } else if ($header == 'CE_emp_id') {
                        $body_info .= '<th>AR Emp Id </th>';
                    } else if ($header == 'ce_hold_reason') {
                        $body_info .= '<th>AR Hold Reason </th>';
                    } else if($header == "coder_work_date") {
                        $body_info .= '<th>AR Work Date</th>';
                    } else if($header == "coder_rework_status") {
                        $body_info .= '<th>AR Rework Status</th>';
                    } else if($header == "coder_rework_reason") {
                        $body_info .= '<th>AR Rework Reason</th>';
                    } else if($header == "coder_error_count") {
                        $body_info .= '<th>AR Error Count</th>';
                    } else if($header == "ar_status_code") {
                        $body_info .= '<th>Status Code</th>';
                    } else if($header == "ar_action_code") {
                        $body_info .= '<th>Action Code</th>';
                    }
                     else {
                        $body_info .= '<th>' . ucwords(str_replace(['_else_', '_'], ['/', ' '], $header)) . '</th>';
                    }
                }
                $body_info .= '</tr></thead><tbody>';

                foreach ($client_data as $row) {
                 
                    $body_info .= '<tr>';
                    foreach ($checkedValues as $header) {
                        $data = isset($row->{$header}) && !empty($row->{$header}) ? $row->{$header} : "--";
                        if ($header == 'QA_status_code') {
                            if ($data != '--') {
                                $data = Helpers::qaStatusById($data)['status_code'];
                            } else {
                                $data;
                            }
                        }
                        if ($header == 'QA_sub_status_code') {
                            if ($data != '--') {
                                $data = Helpers::qaSubStatusById($data)['sub_status_code'];
                            } else {
                                $data;
                            }
                        }
                        if ($header == 'qa_classification') {
                            if ($data != '--') {
                                $data = Helpers::qaClassificationById($data)['qa_classification'];
                            } else {
                                $data;
                            }
                        }
                        if ($header == 'qa_category') {
                            if ($data != '--') {
                                $data = Helpers::qaCategoryById($data)['qa_category'];
                            } else {
                                $data;
                            }
                        }
                        if ($header == 'qa_scope') {
                            if ($data != '--') {
                                $data = Helpers::qaScopeById($data)['qa_scope'];
                            } else {
                                $data;
                            }
                        }
                        if ($header == 'ar_status_code') {
                            if ($data != '--' && $data != null) {
                                $status = Helpers::arStatusById($data);
                                $data = $status != null ? $status['status_code'] : $data;
                            } else {
                                $data;
                            }
                        }
                        if ($header == 'ar_action_code') {
                            if ($data != '--' && $data != null) {
                                $action = Helpers::arActionById($data);
                                $data = $action != null ? $action['action_code'] : $data;
                            } else {
                                $data;
                            }
                        }
                        if ($header === 'chart_status') {
                            //$data = str_replace('_', ' ', $row->{'record_status'});
                            $recordStatus = $row->{'record_status'};
                             if (strpos($recordStatus, 'CE_') === 0) {
                                  $data = str_replace('CE_', 'AR ', $recordStatus);
                            } elseif (strpos($recordStatus, 'QA_') === 0) {
                                $data = str_replace('QA_', 'QA ', $recordStatus);
                            } elseif (strpos($recordStatus, 'QA_') === 0) {
                                $data = str_replace('QA_', 'QA ', $recordStatus);
                            } else {
                                $data =  str_replace('_', ' ',$recordStatus);
                                $data = ucwords($data);
                            }
                        }
                        if ($header === 'qa_work_status') {
                            $data = str_replace('_', ' ', $data);
                        }
                        if ($header === 'work_hours') {
                                $data =isset($row->work_time) && !empty($row->work_time) ? $row->work_time : "--";
                        }
                        if (strpos($data, '_el_') !== false) {
                            $data = str_replace('_el_', ' , ', $data);
                        } else {
                            $data = $data;
                        }
                        if ($header === 'qa_work_date' && ($row->{'record_status'} == "QA_Completed")) {
                            $data = $data != '--' ? date('m/d/y',strtotime($data)) : '--';
                        } else if ($header === 'qa_work_date') {
                            $data =  '--';
                        }
                        if ($header === 'invoke_date') {
                             $data = date('m/d/y',strtotime($data));
                        }
                        if ($header === 'coder_work_date' && ($row->{'record_status'} == "CE_Completed")) {
                            $data = $data != '--' ? date('m/d/y',strtotime($data)) : '--';
                        } else if ($header === 'coder_work_date') {
                            $data =  '--';
                        }
                        if ($header === 'coder_cpt_trends' && ($row->{'qa_cpt_trends'} == NULL)) {
                            $data = $data ;
                        } else if ($header === 'coder_cpt_trends' && ($row->{'qa_cpt_trends'} != NULL)) {
                            $data = isset($row->{'qa_cpt_trends'}) && !empty($row->{'qa_cpt_trends'}) ? $row->{'qa_cpt_trends'} : "--";
                            if (strpos($data, '_el_') !== false) {
                                $data = str_replace('_el_', ' , ', $data);
                            } else {
                                $data = $data;
                            }
                        }

                       
                        if ($header === 'dos') {
                            $data = $data != '--' ? date('m/d/y',strtotime($data)) : '--';
                            $dosDate = Carbon::parse($row->{'dos'});
                            $currentDate = Carbon::now();
                            $agingCount = $dosDate->diffInDays($currentDate);
                            if ($agingCount <= 30) {
                                $agingRange = '0-30';
                            } elseif ($agingCount <= 60) {
                                $agingRange ='31-60';
                            } elseif ($agingCount <= 90) {
                                $agingRange = '61-90';
                            } elseif ($agingCount <= 120) {
                                $agingRange = '91-120';
                            } elseif ($agingCount <= 180) {
                                $agingRange = '121-180';
                            } elseif ($agingCount <= 365) {
                                $agingRange = '181-365';
                            } else {
                            $agingRange = '365+';
                            }
                        } 
                        if ($header === 'aging') {
                            $data = $agingCount;
                        }
                        if ($header === 'aging_range') {
                            $data = $agingRange;
                        }
                        $body_info .= '<td class="wrap-text">' . $data . '</td>';
                    }
                    $body_info .= '</tr>';
                }

                $body_info .= '</tbody></table>';
                // } else {
                //     $body_info = '<p>No data available</p>';
                // }

                return response()->json([
                    'success' => true,
                    'body_info' => $body_info,
                ]);
            } catch (Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function inventoryErrorReportList(Request $request)
    {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {

                return view('reports.inventoryErrorReport');
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function inventoryErrorReport(Request $request)
    {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $searchDate  = explode("-", $request['error_date']);

                if (count($searchDate) > 1) {
                    $start_date  = date('Y-m-d 00:00:00', strtotime($searchDate[0]));
                    $end_date    = date('Y-m-d 23:59:59', strtotime($searchDate[1]));
                } else {
                    $start_date = "";
                    $end_date   = "";
                }
                $error_data = InventoryErrorLogs::where(function ($query) use ($request) {
                    if (isset($request['project_id']) && $request['project_id'] != '') {
                        $query->where('project_id', $request['project_id']);
                    } else {
                        $query;
                    }
                })
                    ->where(function ($query) use ($request) {
                        if (isset($request['sub_project_id']) && $request['sub_project_id'] != '') {
                            $query->where('sub_project_id', $request['sub_project_id']);
                        } else {
                            $query;
                        }
                    })
                    ->where(function ($query) use ($request, $start_date, $end_date) {
                        if (isset($request['error_date'])) {
                            $query->whereBetween('error_date', [$start_date, $end_date]);
                        } else {
                            $query;
                        }
                    })
                    ->orderBy('id', 'desc')
                    ->get();
                    
                $body_info = '<table class="table table-separate table-head-custom no-footer dtr-column clients_list_filter" id="report_list"><thead><tr>';
                $body_info .= '<th>Date</th>';
                $body_info .= '<th>Project Name</th>';
                $body_info .= '<th>Sub Project Name</th>';
                $body_info .= '<th>Description</th>';
                $body_info .= '<th>Status Code</th>';
                $body_info .= '</tr></thead><tbody>';

                foreach ($error_data as $data) {
                    $decodedClientName = Helpers::projectName($data->project_id)->aims_project_name;
                     $decodedsubProjectName = $data->sub_project_id == NULL ? '--' : (Helpers::subProjectName($data->project_id, $data->sub_project_id) != null ?Helpers::subProjectName($data->project_id, $data->sub_project_id)->sub_project_name : null);
                    // $decodedsubProjectName = $request->sub_project_id == null ? 'project' :($request->project_id != null ? (Helpers::subProjectName($request->project_id, $request->sub_project_id) != null ?Helpers::subProjectName($request->project_id, $request->sub_project_id)->sub_project_name : null) : null);
                    $errorStatusCode = $data->error_status_code != NULL ? $data->error_status_code : '--';
                    $errorDate =  $data->error_date != NULL ? date('m/d/Y g:i A', strtotime($data->error_date)) : '--';
                    $errorDescription = $data->error_description != NULL ? nl2br(e( $data->error_description))  : '--';
                    $errorDescription = wordwrap($errorDescription, 120, '<br>');
                    $body_info .= '<tr>';
                    $body_info .= '<td>' . $errorDate . '</td>';
                    $body_info .= '<td>' . $decodedClientName . '</td>';
                    $body_info .= '<td>' . $decodedsubProjectName . '</td>';
                    $body_info .= '<td>' . $errorDescription . '</td>';
                    $body_info .= '<td>' . $errorStatusCode . '</td>';
                    $body_info .= '</tr>';
                }

                $body_info .= '</tbody></table>';


                return response()->json([
                    'success' => true,
                    'body_info' => $body_info,
                ]);
            } catch (Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function teamPerformanceReportList(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                return view("reports.teamPerformanceReport");
            } catch (Exception $e) {
                    log::debug($e->getMessage());
                }
        } else {
            return redirect('/');
        }
    }
    public function teamPerformanceReport(Request $request)
    {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                if (isset($request['month_num']) && $request['month_num'] < date('Y-m')) {
                    $start_month_number = date('Y-m-01', strtotime($request['month_num']));
                    $end_month_number = date('Y-m-t', strtotime($request['month_num']));
                } else {
                    $start_month_number = date('Y-m-01');
                    $end_month_number = date('Y-m-d');
                }
                $decodedClientName = $request->project_id != '' ? Helpers::projectName($request->project_id)->aims_project_name : 'Project';
                $decodedsubProjectName = $request->sub_project_id == '' ? '--' : Helpers::subProjectName($request->project_id, $request->sub_project_id)->sub_project_name;
                $user = $request->user == '' ? '--' : $request->user;
                $datediff = strtotime($end_month_number) - strtotime($start_month_number);
                $datediff = floor($datediff / (60 * 60 * 24));
                $month_numbers = '';
                $month_days = '';
                $userPercentage = '';
                for ($i = 0; $i < $datediff + 1; $i++) {
                    $start_month   = date("Y-m-d", strtotime($start_month_number . ' + ' . $i . 'day'));
                    $month_numbers .= '<th style="text-align: center">' . date('d', strtotime($start_month)) . '</th>';
                    $month_days .= '<th style="text-align: center">' . date('D', strtotime($start_month)) . '</th>';
                    $userPercentage .= '<td  style="text-align: center">' . "100%" . '</td>';
                }
                    
                $body_info = '<table class="table table-separate table-head-custom no-footer dtr-column dataTable" id="report_list">
                <thead>
                      <tr>
                        <th style="text-align: center">Project</th>
                        <th style="text-align: center">Sub Project</th>
                        <th  style="text-align: center">User</th>
                     ' . $month_numbers . '
                    </tr>
                 
                </thead>  <tbody >';          
                    $body_info .= '<tr>';
                    $body_info .= '<td  style="text-align: center">' . $decodedClientName . '</td>';
                    $body_info .= '<td  style="text-align: center">' .  $decodedsubProjectName   . '</td>';
                    $body_info .= '<td  style="text-align: center">' .  $user . '</td>'.  $userPercentage.  '</tr>';
           

                $body_info .= '</tbody></table>';


                return response()->json([
                    'success' => true,
                    'body_info' => $body_info,
                ]);
            } catch (Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

   
    public function productionReports(Request $request)
    {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $error = "Maintenance mode";
                return view('errors.error_page',compact('error'));
                // $payload = [
                //     'token' => '1a32e71a46317b9cc6feb7388238c95d'
                // ];
                // $client = new Client(['verify' => false]);
                // $response = $client->request('POST',  config("constants.PRO_CODE_URL") . '/api/v1_users/get_quality_ar_emp_list', [
                //     'json' => $payload
                // ]);
                // if ($response->getStatusCode() == 200) {
                //     $data = json_decode($response->getBody(), true);
                // } else {
                //     return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                // }
                // $coderList = $data['coderList'];                         
                $qaSamplingList = 0;
                $projectId = $subProjectId = $workDate = 0;
                if($request->project_id) {
                    $projectId = $request->project_id;
                }
                if($request->sub_project_id) {
                    $subProjectId = $request->sub_project_id;
                } 
                if($request->work_date) {
                    $workDate = $request->work_date;
                }
                $work_date = $request->work_date;
                $workingDates = [];
                if (isset($work_date) && !empty($work_date)) {
                    $work_date = explode(' - ', $work_date);
                    $start_date = new \DateTime($work_date[0]);
                    $end_date = new \DateTime($work_date[1]);
                    $end_date->modify('+1 day'); // Include the end date
                
                    while ($start_date < $end_date) {
                        // Exclude Saturdays (6) and Sundays (0)
                        if ($start_date->format('N') < 6) {
                            $workingDates[] = $start_date->format('Y-m-d');
                        }
                        $start_date->modify('+1 day');
                    }
                } else {
                    $workingDates = [];
                }
                $productionReportList = collect(); $productionReportArray =[]; $excel_name = 'Resolv';
                if ($request->project_id && $request->sub_project_id) {
                    $decodedClientName = Helpers::projectName($request->project_id)->project_name;
                    $decodedSubProjectName = $request->sub_project_id == null ? 'project' : Helpers::subProjectName($request->project_id, $request->sub_project_id)->sub_project_name;
                    $excel_name = $decodedClientName.'-'.$decodedSubProjectName;
                    $tableName = Str::slug(Str::lower($decodedClientName) . '_' . Str::lower($decodedSubProjectName), '_');
                    $modelName = Str::studly($tableName);
                    $modelClass = "App\\Models\\" . $modelName . "Datas";            
                    if (class_exists($modelClass)) {
                        // Check if columns exist
                        $columns = Schema::getColumnListing((new $modelClass)->getTable());            
                        // Check if the necessary columns exist in the table
                        $hasActivity = in_array('activity', $columns);
                        $hasSubActivity = in_array('sub_activity', $columns);
                        $hasCoderWorkDate = in_array('coder_work_date', $columns);
            
                        $query = $modelClass::select('CE_emp_id', DB::raw('COUNT(*) as count'))
                            ->where('chart_status', 'CE_Completed');
            
                        if ($hasActivity) {
                            $query->addSelect('activity');
                        }
                        if ($hasSubActivity) {
                            $query->addSelect('sub_activity');
                        }
                        if ($hasCoderWorkDate) {
                            $query->addSelect('coder_work_date');
                        }
            
                        $productionReportList = $query->groupBy('CE_emp_id')
                            ->when($hasActivity, function ($query) {
                                return $query->groupBy('activity')->orderBy('activity');
                            })
                            ->when($hasSubActivity, function ($query) {
                                return $query->groupBy('sub_activity')->orderBy('sub_activity');
                            })
                            ->when($hasCoderWorkDate, function ($query) {
                                return $query->groupBy('coder_work_date')->orderBy('coder_work_date');
                            })
                            ->orderBy('CE_emp_id')
                            ->get();
            
                        // Processing production report list as before
                        $productionReportArray = [];
                        $empIds = $productionReportList->pluck('CE_emp_id')->unique(); // Collect unique emp_ids
            
                        foreach ($empIds as $empId) {
                            GetUserNameByEmpId::dispatch($empId)->delay(now()->addSeconds(5)); // Dispatch the job for each empId
                        }
            
                        foreach ($productionReportList as $key => $data) {
                            $productionReportArray[$key]['emp_id'] = $data['CE_emp_id'];
            
                            // Check if emp name is in cache
                            $cacheKey = "emp_name_{$data['CE_emp_id']}";
                            $empName = null;
                            $attempts = 0;
                            while ($attempts < 10 && !$empName) {
                                if (Cache::has($cacheKey)) {
                                    $empName = Cache::get($cacheKey);
                                    break;
                                }
                                sleep(1); // Delay before rechecking the cache
                                $attempts++;
                            }
            
                            // Set data for the report
                            $productionReportArray[$key]['arName'] = $empName ?? 'Unknown'; // Fallback to 'Unknown' if not found
                            $productionReportArray[$key]['activity'] = $hasActivity ? $data['activity'] : null;
                            $productionReportArray[$key]['sub_activity'] = $hasSubActivity ? $data['sub_activity'] : null;
                            $productionReportArray[$key]['coder_work_date'] = $hasCoderWorkDate ? $data['coder_work_date'] : null;
                            $productionReportArray[$key]['count'] = $data['count'];
            
                            // Get worked records for the report
                            // $productionReportArray[$key]['workedRecords'] = $modelClass::where([
                            //     'activity' => $data['activity'] ?? null,
                            //     'sub_activity' => $data['sub_activity'] ?? null,
                            //     'CE_emp_id' => $data['CE_emp_id'],
                            //     'coder_work_date' => $data['coder_work_date'] ?? null,
                            //     'chart_status' => 'CE_Completed'
                            // ])->pluck('parent_id')->toArray();
                            $productionReportArray[$key]['workedRecords'] = $modelClass::where('CE_emp_id', $data['CE_emp_id'])
                                 ->where('coder_work_date', $data['coder_work_date'] ?? null)
                                ->where('chart_status', 'CE_Completed')
                                ->when(in_array('activity', $columns), function($query) use ($data) {
                                    return $query->where('activity', $data['activity'] ?? null);
                                })
                                ->when(in_array('sub_activity', $columns), function($query) use ($data) {
                                    return $query->where('sub_activity', $data['sub_activity'] ?? null);
                                })
                                ->pluck('parent_id')
                                ->toArray();
                                $productionReportArray[$key]['worked_time'] = $modelClass::where('CE_emp_id', $data['CE_emp_id']) 
                                 ->where('coder_work_date', $data['coder_work_date'] ?? null)
                                //->whereIn('coder_work_date',$workingDates)
                                ->where('chart_status', 'CE_Completed')
                                ->when(in_array('activity', $columns), function($query) use ($data) {
                                    return $query->where('activity', $data['activity'] ?? null);
                                })
                                ->when(in_array('sub_activity', $columns), function($query) use ($data) {
                                    return $query->where('sub_activity', $data['sub_activity'] ?? null);
                                })
                                ->pluck('updated_at')
                                ->map(function ($updatedAt) {
                                    return date('Y-m-d H:i:s', strtotime($updatedAt)); // Convert to desired format
                                })
                                ->toArray();

                        }
            
                      
                    } 
                }
             
                $finalData = [];
            
                // foreach ($workingDates as $date) {
                //     foreach ($productionReportArray as $employee) {
                //         if($date ==  $employee['coder_work_date']) {
                //             $finalData[] = [
                //                 'date' => $date,
                //                 'emp_id' => $employee['emp_id'],
                //                 'emp_name' => $employee['arName'],
                //                 'activity' => $employee['activity'],
                //                 'sub_activity' => $employee['sub_activity'],
                //                 'count' => $employee['count'],
                //                 'workedRecords' => $employee['workedRecords'],
                //                 'worked_time' => $employee['worked_time']
                            
                //             ];
                //         }
                //     }
                // }
                $finalData = [];

                foreach ($workingDates as $date) {
                    $start_date = $date . " 17:00:00"; // Start time for the date
                    $end_date = date('Y-m-d', strtotime($date . ' +1 day')) . " 05:00:00"; // End time for the next day
                
                    foreach ($productionReportArray as $employee) {
                        $final_work_time = [];
                        $date1 = ''; // Reset date1 for each employee
                
                        // Loop through the worked_time for each employee
                        foreach ($employee['worked_time'] as $time) {
                            // Check if the worked time falls within the specified date range
                            if (strtotime($time) >= strtotime($start_date) && strtotime($time) <= strtotime($end_date)) {
                                // Assign the date1 only if it's not already assigned (we want to assign it once)
                                if (!$date1) {
                                    $date1 = $date;
                                }
                                $final_work_time[] = $time; // Add the valid worked time to the final array
                            }
                        }
                
                        // Only add to finalData if we have valid worked_time
                        if (!empty($final_work_time)) {
                            $finalData[] = [
                                'date' => $date1, // The date1 assigned for this employee
                                'emp_id' => $employee['emp_id'],
                                'emp_name' => $employee['arName'],
                                'activity' => $employee['activity'],
                                'sub_activity' => $employee['sub_activity'],
                                'count' => $employee['count'],
                                'workedRecords' => $employee['workedRecords'],
                                'worked_time' => $final_work_time
                            ];
                        }
                    }
                }               
             
                
                return view('reports.productionReport', compact('productionReportArray','projectId','subProjectId','workDate','workingDates','finalData','excel_name'));
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    // public function productionReportSearch(Request $request)
    // {
    //     if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
    //         try {
    //             $data =  $request->all();

    //             return redirect('report/production_reports' . '?parent=' . request()->parent . '&child=' . request()->child);
    //         } catch (\Exception $e) {
    //             Log::debug($e->getMessage());
    //         }
    //     } else {
    //         return redirect('/');
    //     }
    // }

    public function productionMgrUserReport(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {           
                if ($request->work_date != null || $request->project_id!= null || $request->sub_project_id != null || $request->remarks_status != null || $request->reason_type != null || $request->manager_name != null ) {  
                    // $formProjectIds = formConfiguration::groupBy('project_id','sub_project_id')->pluck('sub_project_id','project_id')->toArray();
                    // dd(json_encode($formProjectIds));
                    $formProjectIds = formConfiguration::groupBy('project_id', 'sub_project_id')
                        ->pluck('sub_project_id', 'project_id')
                        ->toArray();
                        $clientIds = array_keys($formProjectIds);
                        $subPrjIds = array_values($formProjectIds);
                        $list = Helpers::getProjectSubPrjManagerList($clientIds,$subPrjIds);
          
            // $formattedData = [];

            // foreach ($formProjectIds as $projectId => $subProjectIds) {
                
            //     $formattedData[] = [
            //         "client_id" => $projectId, // Assuming the client_id is static; modify as needed
            //         "sub_project_id" => array_values((array) $subProjectIds) // Ensure it's an array
            //     ];
            // }
            // $ids= json_encode($formattedData);
          
           
// Output JSON

                    if($request->project_id) {
                        $projectId = $request->project_id;
                    } else {
                        $projectId = null;
                    }
                    if($request->sub_project_id) {
                        $subProjectId = $request->sub_project_id;
                    }  else {
                        $subProjectId = null;
                    }
                    if ($request->work_date) {
                        $workDate = $request->work_date;
                    } else {
                        $workDate = '';
                    }
                    if ($request->remarks_status) {
                        $remarkStatusVal = $request->remarks_status;
                    } else {
                        $remarkStatusVal = '';
                    }
                    if ($request->reason_type) {
                        $reasonTypeVal = $request->reason_type;
                    } else {
                        $reasonTypeVal = '';
                    }
                    if($request->manager_name) {
                        $managerName = $request->manager_name;
                    }  else {
                        $managerName = null;
                    }
                    if (isset($request->work_date) && !empty($request->work_date)) {
                        $work_date = explode(' - ', $request->work_date);
                        $startTime = date('Y-m-d 17:00:00', strtotime($work_date[0]));
                        $endTime = date('Y-m-d 09:00:00', strtotime($work_date[1] . ' +1 day'));
                    }else{
                        $startTime = "";
                        $endTime = "";
                    }
                    $productionReasons = ProjectReason::where(function ($query) use ($startTime, $endTime,$projectId,$subProjectId,$reasonTypeVal,$managerName) {
                        if($projectId) {
                            $query->where('project_id', $projectId);
                        } else {
                            $query;
                        }
                        if($subProjectId) {
                            $query->where('sub_project_id', $subProjectId);
                        } else {
                            $query;
                        }
                        if (!empty($startTime) && !empty($endTime)) {
                            $query->whereBetween('project_reasons.created_at', [$startTime, $endTime]);
                        }else{
                            $query;
                        }
                        if($reasonTypeVal == 'ar_reason') {
                            $query->whereNotNull('ar_reason');
                        } else if($reasonTypeVal == 'qa_reason') {
                            $query->whereNotNull('qa_reason');
                        } {
                            $query;
                        }
                        if($managerName) {
                            $query->where('manager_id', $managerName);
                        } else {
                            $query;
                        }
                    }) ->groupBy('project_id','sub_project_id','manager_id','created_date')
                      ->selectRaw('project_id, sub_project_id,manager_id,DATE(created_at) as created_date');
                      if($remarkStatusVal == "without_remarks") {                       
                        $productionReasons =$list;
                      } else {
                        $productionReasons = $productionReasons->get();
                      }
                      $productionMgrs = ProjectReason::where(function ($query) use ($startTime, $endTime,$projectId,$subProjectId,$reasonTypeVal,$managerName) {
                        if($projectId) {
                            $query->where('project_id', $projectId);
                        } else {
                            $query;
                        }
                        if($subProjectId) {
                            $query->where('sub_project_id', $subProjectId);
                        } else {
                            $query;
                        }
                        if (!empty($startTime) && !empty($endTime)) {
                            $query->whereBetween('project_reasons.created_at', [$startTime, $endTime]);
                        }else{
                            $query;
                        }
                        if($reasonTypeVal == 'ar_reason') {
                            $query->whereNotNull('ar_reason');
                        } else if($reasonTypeVal == 'qa_reason') {
                            $query->whereNotNull('qa_reason');
                        } {
                            $query;
                        }
                        if($managerName) {
                            $query->where('manager_id', $managerName);
                        } else {
                            $query;
                        }
                    }) ->groupBy('manager_id');
                    if($remarkStatusVal == "without_remarks") {
                        $productionMgrs =[];
                    } else {                     
                        $productionMgrs = $productionMgrs->pluck('manager_id')->toArray();
                    }
                    
                    // $productionReasons = ProjectReason::when(!empty($startTime) && !empty($endTime), function ($query) use ($startTime, $endTime) {
                    //     return $query->whereBetween('created_at', [$startTime, $endTime]);
                    // })
                    // ->groupBy('project_id','sub_project_id','manager_id','created_date')
                    // ->selectRaw('project_id, sub_project_id,manager_id,DATE(created_at) as created_date') // Example aggregation
                    // ->get();               
                } else {      
                    $productionReasons = $productionMgrs = [];
                    $workDate = $startTime = $endTime = '';
                    $projectId = null;
                    $subProjectId = null;
                    $remarkStatusVal = $reasonTypeVal = '';
                    $managerName = null;
                }
                return view('reports.productionCommentsReport', compact('productionReasons','projectId','subProjectId','workDate','startTime','endTime','productionMgrs','remarkStatusVal','reasonTypeVal','managerName'));                
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
}
