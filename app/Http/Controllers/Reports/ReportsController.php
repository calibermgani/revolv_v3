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

class ReportsController extends Controller
{
    public function reporstIndex(){
        return view('reports.index');
    }
    public function getSubProjects(Request $request){
        try {
            $subProject = Helpers::subProjectList($request->project_id);
            $user = Helpers::getprojectResourceList($request->project_id);
            return response()->json(['success' => true, 'subProject' => $subProject, 'resource' => $user]);
        } catch (Exception $e) {
            log::debug($e->getMessage());
        }
    }
    public function reportClientAssignedTab(Request $request) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            $client = new Client();
            try {
                $subProject = Helpers::subProjectList($request->project_id);
                $decodedClientName = Helpers::projectName($request->project_id)->project_name;
                $decodedsubProjectName = $request->sub_project_id == null ? 'project' :Helpers::subProjectName($request->project_id, $request->sub_project_id)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $columnsHeader=[];
                if (Schema::hasTable($table_name)) {
                    if ($decodedsubProjectName == 'project' && count($subProject) == 1) {
                        $column_names = DB::select("DESCRIBE $table_name");
                        $columns = array_column($column_names, 'Field');
                        $columnsToExclude = ['QA_required_sampling', 'QA_followup_date', 'annex_coder_trends', 'annex_qa_trends', 'qa_cpt_trends', 'qa_icd_trends', 'qa_modifiers', 'CE_status_code', 'CE_sub_status_code', 'CE_followup_date', 'updated_at', 'created_at', 'deleted_at'];
                        $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                            return !in_array($column, $columnsToExclude);
                        });
                    } else if ($decodedsubProjectName !== 'project') {
                        $column_names = DB::select("DESCRIBE $table_name");
                        $columns = array_column($column_names, 'Field');
                        $columnsToExclude = ['QA_required_sampling','QA_followup_date', 'annex_coder_trends', 'annex_qa_trends','qa_cpt_trends', 'qa_icd_trends', 'qa_modifiers', 'CE_status_code','CE_sub_status_code','CE_followup_date','updated_at','created_at', 'deleted_at'];
                        $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                            return !in_array($column, $columnsToExclude);
                        });
                    }
                }
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
            $client = new Client();
            try {
                $decodedClientName = Helpers::projectName($request->project_id)->project_name;
                $decodedsubProjectName = $request->sub_project_id == null ? 'project' :Helpers::subProjectName($request->project_id, $request->sub_project_id)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)).'_datas','_');
                if (isset($request->work_date) && !empty($request->work_date)) {
                    $work_date = explode(' - ', $request->work_date);
                    $start_date = date('Y-m-d 00:00:00', strtotime($work_date[0]));
                    $end_date = date('Y-m-d 23:59:59', strtotime($work_date[1]));
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
                                $query->where('chart_status',$request->client_status);
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
                $checkedValues[] = 'work_hours';
                foreach ($checkedValues as $key => $header) {
                    if ($header == 'chart_status') {
                        $body_info .= '<th>Charge Status </th>';
                    } else if ($header == 'coder_cpt_trends') {
                        $body_info .= '<th>CPT Trends </th>';
                    } else if ($header == 'coder_icd_trends') {
                        $body_info .= '<th>ICD Trends </th>';
                    } else if ($header == 'coder_modifiers') {
                        $body_info .= '<th>Modifiers </th>';
                    } else {
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

                        if ($header === 'chart_status') {
                            // $data = str_replace('_', ' ', $data);
                            $data = str_replace('_', ' ', $row->{'record_status'});//here fetching chart status from call charts table
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

                        if ($header === 'coder_icd_trends' && ($row->{'qa_icd_trends'} == NULL)) {
                            $data = $data ;
                        } else if ($header === 'coder_icd_trends' && ($row->{'qa_icd_trends'} != NULL)) {
                            $data = isset($row->{'qa_icd_trends'}) && !empty($row->{'qa_icd_trends'}) ? $row->{'qa_icd_trends'} : "--";
                            if (strpos($data, '_el_') !== false) {
                                $data = str_replace('_el_', ' , ', $data);
                            } else {
                                $data = $data;
                            }
                        }

                        if ($header === 'coder_modifiers' && ($row->{'qa_modifiers'} == NULL)) {
                            $data = $data ;
                        } else if ($header === 'coder_modifiers' && ($row->{'qa_modifiers'} != NULL)) {
                            $data = isset($row->{'qa_modifiers'}) && !empty($row->{'qa_modifiers'}) ? $row->{'qa_modifiers'} : "--";
                            if (strpos($data, '_el_') !== false) {
                                $data = str_replace('_el_', ' , ', $data);
                            } else {
                                $data = $data;
                            }
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
                    $decodedsubProjectName = $data->sub_project_id == NULL ? '--' : Helpers::subProjectName($data->project_id, $data->sub_project_id)->sub_project_name;
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
}
