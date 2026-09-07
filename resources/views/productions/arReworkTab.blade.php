@extends('layouts.app3')
@php
use Carbon\Carbon;
@endphp
@section('content')

                <div class="card card-custom custom-card" id="production_ar_rework_tab">
                    <div class="card-body p-0">
                        @php
                             $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                             $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                             $canGrantUserEditable = $loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false;
                             $userEditableRecordIds = isset($userEditableRecordIds) ? $userEditableRecordIds : [];
                        @endphp
                        <div class="card-header border-0 px-4">
                            <div class="row">
                                <div class="col-md-6">
                                <span class="project_header" style="margin-left: 4px !important;">Practice List</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="row" style="justify-content: flex-end;margin-right:1.4rem">
                                        <div>
                                            @if ($popUpHeader != null)
                                                    @php
                                                            $clientNameDetails = App\Http\Helper\Admin\Helpers::projectName(
                                                                $popUpHeader->project_id,
                                                            );
                                                            $sopDetails = App\Models\SopDoc::where('project_id',$popUpHeader->project_id)->where('sub_project_id',$popUpHeader->sub_project_id)->latest()->first('sop_path');
                                                   @endphp
                                                    @else
                                                    @php
                                                        $sopDetails = '';
                                                    @endphp
                                                @endif
                                                   @if (isset($sopDetails) && !empty($sopDetails->sop_path))
                                                        <a href="{{ asset($sopDetails->sop_path) }}" target="_blank">
                                                            <button
                                                                type="button"
                                                                class="btn text-white mr-3"
                                                                style="background-color:#139AB3">SOP
                                                            </button>                                                               
                                                        </a>
                                                    @else
                                                        <button
                                                            type="button"
                                                            class="btn text-white mr-3"
                                                            style="background-color:#139AB3"
                                                            disabled>
                                                            SOP
                                                        </button>
                                                    @endif
                                         </div>
                                         <div class="d-flex align-items-center" id="export_div">
                                            <a class="btn btn-primary-export text-white ml-2" href="javascript:void(0);" id='assign_export'  style="font-size:13px"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" fill="currentColor" class="bi bi-box-arrow-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1z"/><path fill-rule="evenodd" d="M7.646.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 1.707V10.5a.5.5 0 0 1-1 0V1.707L5.354 3.854a.5.5 0 1 1-.708-.708z"/>
                                            </svg>&nbsp;&nbsp;&nbsp;<span>Export</span></a>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                        <div class="wizard wizard-4 custom-wizard" id="kt_wizard_v4" data-wizard-state="step-first"
                            data-wizard-clickable="true" style="margin-top:-2rem !important">
                            <div class="wizard-nav">
                                <div class="wizard-steps">
                                    <!--begin:: Tab Menu View -->
                                    <div class="wizard-step mb-0 one" data-wizard-type="done">
                                        <div class="wizard-wrapper py-2">
                                            <div class="wizard-label p-2 mt-2">
                                                <div class="wizard-title" style="display: flex; align-items: center;">
                                                    <h6 style="margin-right: 5px;">Assigned</h6>
                                                        <span id="assigned_count">
                                                            @include('CountVar.countRectangle', ['count' => $assignedCount])
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($loginEmpId  == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)
                                        <div class="wizard-step mb-0 seven" data-wizard-type="done">
                                            <div class="wizard-wrapper py-2">
                                                <div class="wizard-label p-2 mt-2">
                                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                                        <h6 style="margin-right: 5px;">UnAssigned</h6>
                                                        <span id="unasigned_count">
                                                            @include('CountVar.countRectangle', ['count' => $unAssignedCount])
                                                         </span>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="wizard-step mb-0 two" data-wizard-type="done">
                                        <div class="wizard-wrapper py-2">
                                            <div class="wizard-label p-2 mt-2">
                                                <div class="wizard-title" style="display: flex; align-items: center;">
                                                    <h6 style="margin-right: 5px;">Pending</h6>
                                                  <span id="pending_count">
                                                    @include('CountVar.countRectangle', ['count' => $pendingCount])
                                                  </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wizard-step mb-0 three" data-wizard-type="done">
                                        <div class="wizard-wrapper py-2">
                                            <div class="wizard-label p-2 mt-2">
                                                <div class="wizard-title" style="display: flex; align-items: center;">
                                                    <h6 style="margin-right: 5px;">Hold</h6>
                                                    <span id="hold_count">
                                                        @include('CountVar.countRectangle', ['count' => $holdCount])
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wizard-step mb-0 four" data-wizard-type="done">
                                        <div class="wizard-wrapper py-2">
                                            <div class="wizard-label p-2 mt-2">
                                                <div class="wizard-title" style="display: flex; align-items: center;">
                                                    <h6 style="margin-right: 5px;">Completed</h6>
                                                    <span id="completed_count">
                                                        @include('CountVar.countRectangle', ['count' => $completedCount])
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wizard-step mb-0 five" data-wizard-type="done">
                                        <div class="wizard-wrapper py-2">
                                            <div class="wizard-label p-2 mt-2">
                                                <div class="wizard-title" style="display: flex; align-items: center;">
                                                    <h6 style="margin-right: 5px;">Audit Rework</h6>
                                                    <span id="rework_count">
                                                        @include('CountVar.countRectangle', ['count' => $reworkCount])
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($loginEmpId  == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Co-ordinator - Quality') !== false || strpos($empDesignation, 'Group Co-ordinator - AR') !== false)
                                        <div class="wizard-step mb-0 six" data-wizard-type="done">
                                            <div class="wizard-wrapper py-2">
                                                <div class="wizard-label p-2 mt-2">
                                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                                        <h6 style="margin-right: 5px;">Duplicate</h6>
                                                        <span id="duplicate_count">
                                                            @include('CountVar.countRectangle', ['count' => $duplicateCount])
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="wizard-step mb-0 eight" data-wizard-type="done">
                                        <div class="wizard-wrapper py-2">
                                            <div class="wizard-label p-2 mt-2">
                                                <div class="wizard-title"
                                                style="display: flex; align-items: center;">
                                                    <h6 style="margin-right: 5px;">Non Workable</h6>
                                                    <span id="non_workable_count">
                                                        @include('CountVar.countRectangle', ['count' => $arNonWorkableCount])
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wizard-step mb-0 nine" data-wizard-type="done">
                                        <div class="wizard-wrapper py-2">
                                            <div class="wizard-label p-2 mt-2">
                                                <div class="wizard-title" style="display: flex; align-items: center;">
                                                    <h6 style="margin-right: 5px;">Rebuttal</h6>
                                                    <span id="rebuttal_count">
                                                        @include('CountVar.countRectangle', ['count' => $rebuttalCount])
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wizard-step mb-0 ten" data-wizard-type="done">
                                        <div class="wizard-wrapper py-2">
                                            <div class="wizard-label p-2 mt-2">
                                                <div class="wizard-title" style="display: flex; align-items: center;">
                                                    <h6 style="margin-right: 5px;">Auto Close</h6>
                                                    <span id="auto_close_count">
                                                        @include('CountVar.countRectangle', ['count' => $arAutoCloseCount])
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wizard-step mb-0 eleven" data-wizard-type="step">
                                        <div class="wizard-wrapper py-2">
                                            <div class="wizard-label p-2 mt-2">
                                                <div class="wizard-title"
                                                    style="display:flex;align-items:center;">

                                                    <h6 style="margin-right:5px;">
                                                        AR Rework
                                                    </h6>

                                                    <span id="ar_rework_count">
                                                        @include('CountVar.countRectangle',
                                                        [
                                                            'count'=>$arReworkCount
                                                        ])
                                                    </span>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-custom custom-top-border">
                            <div><span type="button" id="filterExpandButton" class="float-right mr-8 mt-5">
                                <i class="ki ki-arrow-down icon-nm"></i></span></div>
                               
                                <div class="card-body py-0 px-7" id="filter_section" style="display:none">
                                   
                                    @if (count($projectColSearchFields) > 0)
                                        @php $count = 0; @endphp
                                        @foreach ($projectColSearchFields as $key => $data)
                                            @php
                                            $paProject =App\Http\Helper\Admin\Helpers::projectName($data->project_id);
                                            $decodedClientName = $paProject ? $paProject->project_name : null;
                                            $decodedsubProjectName = $data->sub_project_id == NULL ? 'project' :App\Http\Helper\Admin\Helpers::subProjectName($data->project_id,$data->sub_project_id);
                                                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                                                $modelName = Str::studly($table_name);
                                                $modelClass = "App\\Models\\" .  $modelName;
                                                $labelName = ucwords(str_replace(['_else_', '_'], ['/', ' '], $data->column_name));
                                                $columnName = Str::lower(str_replace([' ', '/'], ['_', '_else_'], $data->column_name));
                                                $inputType = $data->column_type; $options = null;
                                            if($inputType == 'select') {
                                                $options = $modelClass::select($columnName)
                                                            ->distinct()
                                                            ->get()
                                                            ->pluck($columnName)
                                                            ->toArray();
                                                            $associativeOptions = [];
                                                            if ($options !== null) {
                                                                foreach ($options as $option) {
                                                                    $option=trim($option);
                                                                    $associativeOptions[$option] = $option;
                                                                }
                                                            }
                                            }
                                         $clientName = App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data->project_id, 'encode');
                                         $subProjectName = $data->sub_project_id != null ? App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data->sub_project_id, 'encode') : '--';
                                            @endphp
                                             {!! Form::open([
                                                'url' =>
                                                url('projects_ar_rework/' . $clientName . '/' . $subProjectName) .
                                                                '?parent=' .
                                                                request()->parent .
                                                                '&child=' .
                                                                request()->child,
                                                'class' => 'form',
                                                'id' => 'formSearch',
                                                'enctype' => 'multipart/form-data',
                                            ]) !!}
                                            @csrf
                                          
                                        @if ($count % 4 == 0)
                                                <div class="row mr-0 ml-0 mt-5">
                                                    @endif
                                                <div class="col-md-3">
                                                    <div class="form-group row row_mar_bm">
                                                        <label
                                                            class="col-md-12">
                                                            @if(str_contains($labelName, 'Coder '))
                                                              {{ str_replace('Coder ', 'AR ', $labelName) }}
                                                            @elseif ($data->column_name == 'CE_emp_id')
                                                              AR Emp Id
                                                            @else
                                                              {{ $labelName }}
                                                            @endif
                                                        </label>
                                                        <div class="col-md-10">
                                                            @if ($options == null)
                                                                @if ($inputType != 'date_range')
                                                                    {!! Form::$inputType($columnName,isset($searchData) && !empty($searchData) && isset($searchData[$columnName]) && $searchData[$columnName]  ? $searchData[$columnName] : null, [
                                                                        'class' => 'form-control  white-smoke pop-non-edt-val',
                                                                        'autocomplete' => 'none',
                                                                        'style' => 'cursor:pointer',
                                                                        'rows' => 3
                                                                    ]) !!}
                                                                @else
                                                                    {!! Form::text($columnName, null, [
                                                                        'class' => 'form-control date_range white-smoke pop-non-edt-val',
                                                                        'autocomplete' => 'none',
                                                                        'style' => 'cursor:pointer'     
                                                                    ]) !!}
                                                                @endif
                                                            @else
                                                                @if ($inputType == 'select')
                                                                    {!! Form::$inputType($columnName, ['' => '-- Select --'] + $associativeOptions, isset($searchData) && !empty($searchData) && isset($searchData[$columnName]) && $searchData[$columnName] ? $searchData[$columnName] : null, [
                                                                        'class' => 'form-control white-smoke pop-non-edt-val select2',
                                                                        'autocomplete' => 'none'                                                
                                                                    ]) !!}
                                                            @endif
                                                            @endif
                                                        </div>
                                                    
                                                    
                                                    </div>
                                                </div>
                                                @php $count++; @endphp
                                                @if ($count % 4 == 0 || $loop->last)
                                                </div>
                                            @endif
                                        
                                        @endforeach
                                        <div class="form-footer" style="justify-content: center !important">                                      
                                            <button type="submit" class="btn  btn-white-black font-weight-bold"
                                                id="filter_search">Search</button> &nbsp;&nbsp; <button class="btn btn-light-danger" id="filter_clear" tabindex="10" type="button">
                                                    <span>
                                                        <span>Clear</span>
                                                    </span>
                                                </button>                        
                                        </div>
                                    @endif
                                </div>
                          
                                {!! Form::close() !!}
                                @if ($canGrantUserEditable)
                                @php
                                $pageSelectedRecord = ($arReworkDetails->count() > 0) ? (($arReworkDetails->lastItem() - $arReworkDetails->firstItem()) + 1) : 0;
                                @endphp
                                <p id="select_p1" style="text-align:center;display:none">All {{$pageSelectedRecord}} {{$pageSelectedRecord == 1 ? 'record on this page is selected' : 'records on this page are selected'}} . <a  id="select_all_status" style="color:#6993FF !important;cursor:pointer !important">Select all {{$arReworkDetails->total()}} records</a></p>
                                <p id="clear_p1" style="text-align:center;display:none">All {{$arReworkDetails->total()}} records are selected.<a style="color:#6993FF !important;cursor:pointer !important" id="clear_all_status">Clear Selection.</a></p>
                                @endif
                            <div class="card-body py-0 px-7">
                                <input type="hidden" value={{ $clientName }} id="clientName">
                                <input type="hidden" value={{ $subProjectName }} id="subProjectName">
                                <div class="table-responsive pt-5 pb-5 clietnts_table">
                                    <table class="table table-separate table-head-custom no-footer dtr-column "
                                        id="ar_rework_list" data-order='[[ 0, "desc" ]]'>
                                        <thead>
                                            @if (!empty($columnsHeader))
                                                <tr>
                                                    <th class='notexport' style="color:white !important">Action</th>
                                                    @foreach ($columnsHeader as $columnName => $columnValue)
                                                        @if ($columnValue != 'id')
                                                            <th><input type="hidden"
                                                                    value={{ $columnValue }}>
                                                                    @if ($columnValue == 'chart_status')
                                                                    Charge Status
                                                                    @elseif ($columnValue == 'CE_emp_id')
                                                                    AR Emp Id
                                                                    @elseif ($columnValue == 'coder_work_date')
                                                                    AR Work Date
                                                                    @elseif ($columnValue == 'coder_rework_status')
                                                                    AR Rework Status
                                                                  @else
                                                                   {{ ucwords(str_replace(['_else_', '_'], ['/', ' '], $columnValue)) }}
                                                                  @endif
                                                            </th>
                                                        @else
                                                            <th style="display:none" class='notexport'><input type="hidden"
                                                                    value={{ $columnValue }}>
                                                                    @if ($columnValue == 'chart_status')
                                                                    Charge Status
                                                                    @elseif ($columnValue == 'CE_emp_id')
                                                                    AR Emp Id
                                                                    @elseif ($columnValue == 'coder_work_date')
                                                                    AR Work Date
                                                                    @elseif ($columnValue == 'coder_rework_status')
                                                                    AR Rework Status
                                                                  @else
                                                                   {{ ucwords(str_replace(['_else_', '_'], ['/', ' '], $columnValue)) }}
                                                                  @endif
                                                            </th>
                                                        @endif
                                                    @endforeach
                                                </tr>
                                            @endif

                                        </thead>
                                        <tbody>
                                            @if (isset($arReworkDetails))
                                                @php
                                                    $currentArDate = Carbon::today();
                                                    $currentArAtStart = $currentArDate->copy()->setTime(8, 0, 0)->toDateTimeString();
                                                    $currentArAtEnd = $currentArDate->copy()->addDay()->setTime(7, 59, 59)->toDateTimeString();
                                                @endphp
                                                @foreach ($arReworkDetails as $data)
                                                    @php
                                                    $arrayAttrributes = $data->getAttributes();
                                                    $arrayAttrributes['aging']= null; 
                                                    $arrayAttrributes['aging_range']= null;
                                                    $arAtValue = $arrayAttrributes['ar_at'] ?? $data->getRawOriginal('ar_at') ?? $data->ar_at ?? null;
                                                    $isCurrentArAt = !empty($arAtValue)
                                                        && strtotime($arAtValue) >= strtotime($currentArAtStart)
                                                        && strtotime($arAtValue) <= strtotime($currentArAtEnd);
                                                    $isUserEditable = true;                                                                          
                                                    @endphp
                                                    <tr>
                                                    <td>
                                                         @if (($loginEmpId !== "Admin" || strpos($empDesignation, 'Manager') !== true || strpos($empDesignation, 'VP') !== true || strpos($empDesignation, 'Leader') !== true || strpos($empDesignation, 'Team Lead') !== true || strpos($empDesignation, 'CEO') !== true || strpos($empDesignation, 'Vice') !== true || strpos($empDesignation, 'Group Coordinator - AR') !== true || strpos($empDesignation, 'Subject Matter Expert') !== true) && $loginEmpId != $data->CE_emp_id)
                                                         @else  
                                                                @if ($isCurrentArAt || $isUserEditable)
                                                                    <button class="task-start clickable-row start"
                                                                            title="Edit">
                                                                        <i class="fa fa-edit icon-circle1 mt-0" aria-hidden="true" style="color:#ffffff"></i>
                                                                    </button>
                                                                @endif
                                                        @endif
                                                            <button class="task-start clickable-view"
                                                                    title="View">
                                                                <i class="fa fa-eye text-eye icon-circle1 mt-0"></i>
                                                            </button>
                                                    </td>
                                                        @foreach ($arrayAttrributes as $columnName => $columnValue)
                                                            @php
                                                                $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date', 
                                                                'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                                                                'created_at', 'updated_at', 'deleted_at'];
                                                                if(isset($arrayAttrributes['dos'])) {          
                                                                    $dosDate = Carbon::parse($arrayAttrributes['dos']);
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
                                                                } else {
                                                                        $agingCount = '--';
                                                                        $agingRange = '--';
                                                             }
                                                            @endphp
                                                            @if (!in_array($columnName, $columnsToExclude))
                                                                @if ($columnName != 'id')
                                                                <td style="max-width: 300px;
                                                                white-space: normal;">
                                                                    {{-- @if (str_contains($columnValue, '-') && strtotime($columnValue)) --}}
                                                                    @if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $columnValue))
                                                                        {{ date('m/d/Y', strtotime($columnValue)) }}
                                                                    @else
                                                                        @if ($columnName == 'chart_status' && str_contains($columnValue, 'CE_'))
                                                                            {{ str_replace('CE_', '', $columnValue) }}
                                                                        @elseif ($columnName == 'aging')                                                                                  
                                                                            {{ $agingCount }}
                                                                        @elseif ($columnName == 'aging_range')
                                                                            {{ $agingRange }}
                                                                        @else
                                                                            {{ $columnValue }}
                                                                        @endif
                                                                    @endif
                                                                </td>
                                                            @else
                                                                <td style="display:none;max-width: 300px;
                                                                white-space: normal;" id="table_id">
                                                                    {{-- @if (str_contains($columnValue, '-') && strtotime($columnValue)) --}}
                                                                    @if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $columnValue))
                                                                        {{ date('m/d/Y', strtotime($columnValue)) }}
                                                                    @elseif ($columnName == 'aging')                                                                                  
                                                                        {{ $agingCount }}
                                                                    @elseif ($columnName == 'aging_range')
                                                                        {{ $agingRange }}
                                                                    @else
                                                                        {{ $columnValue }}
                                                                    @endif
                                                                </td>
                                                            @endif
                                                            @endif
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="ml-3" 
                                           id="ar_rework_showing_text"
                                            data-first="{{ $arReworkDetails->firstItem() != null ? $arReworkDetails->firstItem() : 0 }}"
                                            data-total="{{ $arReworkDetails->total() }}"
                                            data-per-page="{{ $arReworkDetails->perPage() }}">

                                            Showing {{ $arReworkDetails->firstItem() != null ? $arReworkDetails->firstItem() : 0 }} to {{ $arReworkDetails->lastItem() != null ? $arReworkDetails->lastItem() : 0 }} of {{ $arReworkDetails->total() }} entries
                                    </div>
                                     <div id="ar_rework_pagination">
                                        {{ $arReworkDetails->appends(request()->except([ 'page']))->links() }}
                                    </div>
                                </div>          
                            </div>

                        </div>
                    </div>
                    <div class="modal fade modal-first" id="myModal_status" tabindex="-1" role="dialog"
                       aria-labelledby="myModalLabel" data-backdrop="static" aria-hidden="true">
                        @if ($popUpHeader != null)
                            <div class="modal-dialog">
                                @php
                                    $clientName = App\Http\Helper\Admin\Helpers::projectName(
                                        $popUpHeader->project_id,
                                    );
                                 
                                    $projectName = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(
                                        $popUpHeader->project_id,
                                        'encode',
                                    );
                                    if($popUpHeader->sub_project_id != NULL) {
                                        $practiceName = App\Http\Helper\Admin\Helpers::subProjectName(
                                            $popUpHeader->project_id,
                                            $popUpHeader->sub_project_id,
                                        );
                                        $subProjectName = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(
                                        $popUpHeader->sub_project_id,
                                        'encode',
                                        );
                                    } else {
                                        $practiceName = '';
                                        $subProjectName = '--';
                                    }
                                @endphp


                                    <div class="modal-content" style="margin-top: 7rem">
                                        <div class="modal-header" style="background-color: #139AB3;height: 84px">
                                            {{-- <div class="row" style="height: auto;width:100%"> --}}
                                                <div class="col-md-4">
                                                    <div class="align-items-center" style="display: -webkit-box !important;">
                                                        <div class="rounded-circle bg-white text-black mr-2" style="width: 50px; height: 50px; display: flex; justify-content: center; align-items: center;font-weight;bold">
                                                            <span>{{ strtoupper(substr($clientName->project_name, 0, 1)) }}</span>
                                                        </div>&nbsp;&nbsp;
                                                        <div>
                                                            <h6 class="modal-title mb-0" id="myModalLabel" style="color: #ffffff;">
                                                                {{ ucfirst($clientName->aims_project_name) }}
                                                            </h6>
                                                            @if($practiceName != '')
                                                            <h6 style="color: #ffffff;font-size:1rem;">{{ ucfirst($practiceName->sub_project_name) }}</h6>
                                                            @endif
                                                        </div>&nbsp;&nbsp;
                                                    <div class="bg-white rounded-pill px-2 text-black" style="margin-bottom: 2rem;margin-left:2.2px;font-size:10px;font-weight:500;background-color:#E9F3FF;color:#139AB3;">
                                                            <span id="title_status"></span>
                                                        </div>
                                                    </div>                                                         
                                                </div>
                                                <button type="button" class="close comment_close" data-dismiss="modal"
                                                       aria-hidden="true" style="color:#ffffff !important">&times;</button>
                                            {{-- </div> --}}
                                            
                                        </div>
                                        {!! Form::open([
                                            'url' =>
                                                url('project_update/' . $projectName . '/' . $subProjectName) .
                                                '?parent=' .
                                                request()->parent .
                                                '&child=' .
                                                request()->child,
                                            'class' => 'form',
                                            'id' => 'reworkTabFormConfiguration',
                                            'enctype' => 'multipart/form-data',
                                        ]) !!}
                                        @csrf
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-3" data-scroll="true" data-height="400">
                                                    <h6 class="title-h6">Basic Information</h6>&nbsp;&nbsp;
                                                    <input type="hidden" name="idValue">
                                                    <input type="hidden" name="parentId">
                                                    <input type="hidden" name="record_old_status">
                                                     <input type="hidden" name="ar_rework_val" value="user_rework">
                                                    @if (count($popupNonEditableFields) > 0)
                                                        @php $count = 0; @endphp
                                                        @foreach ($popupNonEditableFields as $data)
                                                        @php
                                                         $columnName = Str::lower(
                                                            str_replace(
                                                                [' ', '/'],
                                                                ['_', '_else_'],
                                                                $data->label_name,
                                                            ),
                                                        );
                                                    @endphp

                                                        <label
                                                            class="col-md-12">{{ $data->label_name }}
                                                        </label>
                                                        <input type="hidden" name="{{ $columnName }}">

                                                        <label class="col-md-12 pop-non-edt-val"
                                                            id={{ $columnName }}>
                                                        </label>
                                                        <hr style="margin-left:1rem">
                                                        @endforeach
                                                    @endif
                                                </div>
                                                <div class="col-md-9" style="border-left: 1px solid #ccc;" data-scroll="true" data-height="400">
                                                    <h6 class="title-h6">AR</h6>&nbsp;&nbsp;
                                                    @if (count($popupEditableFields) > 0)
                                                        @php $count = 0; @endphp
                                                        @foreach ($popupEditableFields as $key => $data)
                                                        @php
                                                        $labelName = $data->label_name;
                                                        $columnName = Str::lower(
                                                            str_replace([' ', '/'], ['_', '_else_'], $data->label_name),
                                                        );
                                                        $inputType = $data->input_type;
                                                        $options =
                                                            $data->options_name != null ? explode(',', $data->options_name) : null;
                                                        $associativeOptions = [];
                                                        if ($options !== null) {
                                                            foreach ($options as $option) {
                                                                $associativeOptions[$option] = $option;
                                                            }
                                                        }
                                                    @endphp
                                                    @if ($count % 2 == 0)
                                                        <div class="row">
                                                    @endif
                                                        <div class="col-md-6 dynamic-field">
                                                            <div class="form-group row row_mar_bm">
                                                                <label
                                                                    class="col-md-12 {{ $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 1 || $data->input_type_editable == 3) ? 'required' : '' }}">
                                                                    {{ $labelName }}
                                                                </label>
                                                                <div class="col-md-10">
                                                                    @if ($options == null)
                                                                        @if ($inputType != 'date_range')
                                                                            {!! Form::$inputType($columnName . '[]', null, [
                                                                                'class' => 'form-control ' . $columnName . ' white-smoke pop-non-edt-val',
                                                                                'autocomplete' => 'none',
                                                                                'style' => 'cursor:pointer',
                                                                                'rows' => 3,
                                                                                'id' => $columnName,
                                                                                $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 1 || $data->input_type_editable == 3) ? 'required' : '',
                                                                                ($data->input_type_editable == 1 || $data->input_type_editable == 3) ? '' : 'readonly'
                                                                            ]) !!}
                                                                        @else
                                                                            {!! Form::text($columnName . '[]', null, [
                                                                                'class' => 'form-control date_range ' . $columnName . ' white-smoke pop-non-edt-val',
                                                                                'autocomplete' => 'none',
                                                                                'style' => 'cursor:pointer',
                                                                                'id' => $columnName,
                                                                                $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 1 || $data->input_type_editable == 3) ? 'required' : '',
                                                                                ($data->input_type_editable == 1 || $data->input_type_editable == 3) ? '' : 'readonly'
                                                                            ]) !!}
                                                                        @endif
                                                                    @else
                                                                        @if ($inputType == 'select')
                                                                            {!! Form::$inputType($columnName . '[]', ['' => '-- Select --'] + $associativeOptions, null, [
                                                                                'class' => 'form-control ' . $columnName . ' white-smoke pop-non-edt-val',
                                                                                'autocomplete' => 'none',
                                                                                'style' => 'cursor:pointer;' . (($data->input_type_editable == 1 || $data->input_type_editable == 3) ? '' : 'pointer-events: none;'),
                                                                                'id' => $columnName,
                                                                                $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 1 || $data->input_type_editable == 3) ? 'required' : '',
                                                                            ]) !!}
                                                                        @elseif ($inputType == 'checkbox')
                                                                            <p id="check_p1"
                                                                                style="display:none;color:red; margin-left: 3px;">Checkbox
                                                                                is not checked</p>
                                                                            <div class="form-group row">
                                                                                @for ($i = 0; $i < count($options); $i++)
                                                                                    <div class="col-md-6">
                                                                                        <div class="checkbox-inline mt-2">
                                                                                            <label class="checkbox pop-non-edt-val"
                                                                                                style="word-break: break-all;">
                                                                                                {!! Form::$inputType($columnName . '[]', $options[$i], false, [
                                                                                                    'class' => $columnName,
                                                                                                    'id' => $columnName,
                                                                                                    $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 1 || $data->input_type_editable == 3) ? 'required' : '',
                                                                                                    'onclick' => $data->input_type_editable != 1 && $data->input_type_editable != 3 ? 'return false;' : '',
                                                                                                ]) !!}{{ $options[$i] }}
                                                                                                <span></span>
                                                                                            </label>
                                                                                        </div>
                                                                                    </div>
                                                                                @endfor
                                                                            </div>
                                                                        @elseif ($inputType == 'radio')
                                                                            <p id="radio_p1"
                                                                                style="display: none; color: red; margin-left: 3px;">Radio
                                                                                is not selected</p>
                                                                            <div class="form-group row">
                                                                                @for ($i = 0; $i < count($options); $i++)
                                                                                    <div class="col-md-6">
                                                                                        <div class="radio-inline mt-2">
                                                                                            <label class="radio pop-non-edt-val"
                                                                                                style="word-break: break-all;">
                                                                                                {!! Form::$inputType($columnName, $options[$i], false, [
                                                                                                    'class' => $columnName,
                                                                                                    'id' => $columnName,
                                                                                                    $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 1 || $data->input_type_editable == 3) ? 'required' : '',
                                                                                                    'disabled' => $data->input_type_editable != 1 && $data->input_type_editable != 3,
                                                                                                ]) !!}{{ $options[$i] }}
                                                                                                <span></span>
                                                                                            </label>
                                                                                        </div>

                                                                                    </div>
                                                                                @endfor

                                                                            </div>
                                                                        @endif
                                                                    @endif

                                                                </div>
                                                                <div class="col-md-1 col-form-label pt-0 pb-4" style="margin-left: -1.3rem;">
                                                                    <input type="hidden"
                                                                        value="{{ $associativeOptions != null ? json_encode($associativeOptions) : null }}"
                                                                        class="add_options">

                                                                    @if ($data->field_type_1 == 'multiple')
                                                                    <i class="fa fa-plus add_more"
                                                                            id="add_more_{{ $columnName }}"
                                                                            style="{{ $data->field_type_1 == 'multiple' ? 'visibility: visible;' : 'visibility: hidden;' }}"></i>
                                                                        <input type="hidden"
                                                                            value="{{ $data->field_type_1 == 'multiple' ? $labelName : '' }}"
                                                                            class="add_labelName">
                                                                        <input type="hidden"
                                                                            value="{{ $data->field_type_1 == 'multiple' ? $columnName : '' }}"
                                                                            class="add_columnName">
                                                                        <input type="hidden"
                                                                            value="{{ $data->field_type_1 == 'multiple' ? $inputType : '' }}"
                                                                            class="add_inputtype">
                                                                        <input type="hidden"
                                                                            value="{{ $data->field_type_1 == 'multiple' ? ($data->field_type_2 == 'mandatory' ? 'required' : '') : '' }}"
                                                                            class="add_mandatory">

                                                                    @endif
                                                                </div>
                                                                <div></div>
                                                            </div>
                                                        </div>
                                                    @php $count++; @endphp
                                                    @if ($count % 2 == 0 || $loop->last)
                                                    </div>
                                                    @endif
                                                        @endforeach
                                                    @endif
                                                    @php
                                                    if($popUpHeader->sub_project_id != null && $popUpHeader->sub_project_id != "") {
                                                        $statusActionShow = App\Models\projectInputSetting::where('sub_project_id',$popUpHeader->sub_project_id)->first();                                                                                                                              
                                                    } else {
                                                        $statusActionShow = null;
                                                    }
                                               @endphp
                                                    <div class="row mt-4">
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <label class="col-md-12 required">
                                                                        Status Code
                                                                    </label>
                                                                    @php $arStatusList = $popUpHeader->sub_project_id != null && $popUpHeader->sub_project_id != "" ? App\Http\Helper\Admin\Helpers::arStatusListBySubPrjId( $popUpHeader->sub_project_id) : []; @endphp
                
                                                                    <div class="col-md-10">
                                                                        <input type="hidden" id="ar_status_val">
                                                                        {!! Form::Select(
                                                                            'ar_status_code',
                                                                            $arStatusList,
                                                                            null,
                                                                            [
                                                                                'class' => 'form-control white-smoke  kt_select2_qa_status_modal pop-non-edt-val ',
                                                                                'autocomplete' => 'none',
                                                                                'id' => 'ar_status_code',
                                                                                'style' => 'cursor:pointer'
                                                                            ],
                                                                        ) !!}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <label class="col-md-12 required">
                                                                        Action Code
                                                                    </label>
                                                                    @php $arActionList = []; @endphp
                                                                    <div class="col-md-10">
                                                                        {!! Form::Select(
                                                                            'ar_action_code',
                                                                            $arActionList,
                                                                            null,
                                                                            [
                                                                                'class' => 'form-control white-smoke  kt_select2_ar_action_code_modal pop-non-edt-val ',
                                                                                'autocomplete' => 'none',
                                                                                'id' => 'ar_action_code',
                                                                                'style' => 'cursor:pointer'
                                                                            ],
                                                                        ) !!}
                
                                                                    </div>
                                                                </div>
                                                            </div>
                                                    </div>
                                                <div class="row mt-4">
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <label class="col-md-12 required">
                                                                Denial Code
                                                            </label>
                                                            @php $arDenialList = App\Http\Helper\Admin\Helpers::arDenialList(); @endphp
        
                                                            <div class="col-md-10">
                                                                    <input type="hidden" id="ar_denial_val">
                                                                {!! Form::Select(
                                                                    'ar_denial_codes',
                                                                    $arDenialList,
                                                                    null,
                                                                    [
                                                                        'class' => 'form-control white-smoke  kt_select2_denial_modal pop-non-edt-val ',
                                                                        'autocomplete' => 'none',
                                                                        'id' => 'ar_denial_codes',
                                                                        'style' => 'cursor:pointer',
                                                                    ],
                                                                ) !!}
                                                            </div>
                                                        </div>
                                                    </div> 
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <label class="col-md-12">
                                                                Substatus Code
                                                            </label>
                                                            @php $arSubStatusList = App\Http\Helper\Admin\Helpers::arSubStatusList(); @endphp
        
                                                            <div class="col-md-10">
                                                                    <input type="hidden" id="ar_substatus_val">
                                                                {!! Form::Select(
                                                                    'ar_substatus_codes',
                                                                    $arSubStatusList,
                                                                    null,
                                                                    [
                                                                        'class' => 'form-control white-smoke  kt_select2_substatus_1_modal pop-non-edt-val ',
                                                                        'autocomplete' => 'none',
                                                                        'id' => 'ar_substatus_codes',
                                                                        'style' => 'cursor:pointer',
                                                                    ],
                                                                ) !!}
                                                            </div>
                                                        </div>
                                                    </div>                                                                      
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col-md-6">
                                                        <input type="hidden" name="invoke_date">
                                                        <input type="hidden" name="CE_emp_id">
                                                        <div class="form-group row">
                                                            <label class="col-md-12 required">
                                                                Charge Status
                                                            </label>
                                                            <div class="col-md-10">
                                                                {!! Form::Select(
                                                                    'chart_status',
                                                                    [
                                                                        '' => '--Select--',
                                                                         'CE_Completed' => 'Completed',
                                                                    ],
                                                                    null,
                                                                    [
                                                                        'class' => 'form-control white-smoke  pop-non-edt-val ',
                                                                        'autocomplete' => 'none',
                                                                        'id' => 'chart_status',
                                                                        'style' => 'cursor:pointer',
                                                                    ],
                                                                ) !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <label class="col-md-12 required" id="ce_hold_reason_label" style = 'display:none'>
                                                                Hold Reason
                                                            </label>
                                                            <div class="col-md-10">
                                                                {!! Form::textarea('ce_hold_reason',  null, ['class' => 'text-black form-control','rows' => 3,'id' => 'ce_hold_reason','style' => 'display:none']) !!}

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                           
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                 <button type="submit" class="btn1 float-right" id="project_complete_save" style="margin-right: -2rem">Submit</button>                                       
                                            </div>
                                            
                                        </div>
                                        {!! Form::close() !!}
                                    </div>

                            </div>
                        @endif
                   </div>
                    <div class="modal fade modal-first" id="myModal_view" tabindex="-1" role="dialog"
                        aria-labelledby="myModalLabel" data-backdrop="static" aria-hidden="true">
                            @if ($popUpHeader != null)
                                <div class="modal-dialog">
                                    @php
                                        $clientName = App\Http\Helper\Admin\Helpers::projectName(
                                            $popUpHeader->project_id,
                                        );
                                    
                                        $projectName = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(
                                            $popUpHeader->project_id,
                                            'encode',
                                        );
                                        if($popUpHeader->sub_project_id != NULL) {
                                            $practiceName = App\Http\Helper\Admin\Helpers::subProjectName(
                                                $popUpHeader->project_id,
                                                $popUpHeader->sub_project_id,
                                            );
                                            $subProjectName = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(
                                            $popUpHeader->sub_project_id,
                                            'encode',
                                            );
                                        } else {
                                            $practiceName = '';
                                            $subProjectName = '--';
                                        }

                                    @endphp


                                        <div class="modal-content" style="margin-top: 7rem">
                                            <div class="modal-header" style="background-color: #139AB3;height: 84px">

                                                <div class="col-md-4">
                                                    <div class="align-items-center" style="display: -webkit-box !important;">
                                                        <!-- Round background for the first letter of the project name -->
                                                        <div class="rounded-circle bg-white text-black mr-2" style="width: 50px; height: 50px; display: flex; justify-content: center; align-items: center;font-weight;bold">
                                                            <span>{{ strtoupper(substr($clientName->project_name, 0, 1)) }}</span>
                                                        </div>&nbsp;&nbsp;
                                                        <div>
                                                            <!-- Project name -->
                                                            <h4 class="modal-title mb-0" id="myModalLabel" style="color: #ffffff;">
                                                                {{ ucfirst($clientName->aims_project_name) }}
                                                            </h4>
                                                            @if($practiceName != '')
                                                            <h6 style="color: #ffffff;font-size:1rem;">{{ ucfirst($practiceName->sub_project_name) }}</h6>
                                                            @endif
                                                        </div>&nbsp;&nbsp;
                                                        <!-- Oval background for project status -->
                                                        <div class="bg-white rounded-pill px-2 text-black" style="margin-bottom: 2rem;margin-left:2.2px;font-size:10px;font-weight:500;background-color:#E9F3FF;color:#139AB3;">
                                                            <span id="title_status_view"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" class="close comment_close" data-dismiss="modal"
                                                    aria-hidden="true" style="color:#ffffff !important">&times;</button>

                                            </div>

                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-3" data-scroll="true" data-height="400">
                                                        <h6 class="title-h6">Basic Information</h6>&nbsp;&nbsp;
                                                        <input type="hidden" name="idValue">
                                                        @if (count($popupNonEditableFields) > 0)
                                                            @php $count = 0; @endphp

                                                            @foreach ($popupNonEditableFields as $data)
                                                            @php
                                                            $columnName = Str::lower(
                                                                str_replace(
                                                                    [' ', '/'],
                                                                    ['_', '_else_'],
                                                                    $data->label_name,
                                                                ),
                                                            );
                                                        @endphp

                                                            <label
                                                                class="col-md-12">{{ $data->label_name }}
                                                            </label>
                                                            <input type="hidden" name="{{ $columnName }}">

                                                            <label class="col-md-12 pop-non-edt-val"
                                                                id={{ $columnName }}>
                                                            </label>
                                                            <hr style="margin-left:1rem">
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    <div class="col-md-9" style="border-left: 1px solid #ccc;" data-scroll="true" data-height="400">
                                                        <h6 class="title-h6">AR</h6>&nbsp;&nbsp;
                                                        @if (count($popupEditableFields) > 0)
                                                            @php $count = 0; @endphp
                                                            @foreach ($popupEditableFields as $key => $data)
                                                            @php
                                                            $labelName = $data->label_name;
                                                            $columnName = Str::lower(
                                                                str_replace([' ', '/'], ['_', '_else_'], $data->label_name),
                                                            );

                                                        @endphp
                                                        @if ($count % 2 == 0)
                                                            <div class="row" id={{ $columnName }}>
                                                        @endif
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <label
                                                                        class="col-md-12">
                                                                        {{ $labelName }}
                                                                    </label>
                                                                    <label class="col-md-12 pop-non-edt-val"
                                                                    id={{ $columnName }}>
                                                                </label>

                                                                    <div></div>
                                                                </div>
                                                            </div>
                                                        @php $count++; @endphp
                                                        @if ($count % 2 == 0 || $loop->last)
                                                        </div>
                                                        @endif
                                                            @endforeach
                                                        @endif
                                                        @php
                                                        if($popUpHeader->sub_project_id != null && $popUpHeader->sub_project_id != "") {
                                                            $statusActionShow = App\Models\projectInputSetting::where('sub_project_id',$popUpHeader->sub_project_id)->first();                                                                                                                              
                                                        } else {
                                                            $statusActionShow = null;
                                                        }
                                                    @endphp
                                                   
                                                        <div class="row mt-4">
                                                                <div class="col-md-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-md-12" id="ar_status_label">
                                                                            Status Code
                                                                        </label>
                                                                        <label class="col-md-12 pop-non-edt-val" id="ar_status_view">
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-md-12" id="ar_action_label">
                                                                            Action Code
                                                                        </label>
                                                                        <label class="col-md-12 pop-non-edt-val" id="ar_action_view">
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                        </div>
                                                    <div class="row mt-4">             
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <label class="col-md-12" id="ar_denial_label">
                                                                        Denial Code
                                                                    </label>
                                                                    <label class="col-md-12 pop-non-edt-val" id="ar_denial_view">
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <label class="col-md-12" id="ar_substatus_code_label">
                                                                        Substatus Code
                                                                    </label>
                                                                    <label class="col-md-12 pop-non-edt-val" id="ar_substatus_view">
                                                                    </label>
                                                                </div>
                                                            </div>
                                                    </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group row" style="margin-left: -2rem">
                                                                <label class="col-md-12">
                                                                    Charge Status
                                                                </label>
                                                                <label class="col-md-12 pop-non-edt-val"
                                                                id="chart_status">
                                                            </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">

                                                    <button class="btn btn-light-danger float-right" id="close_assign" tabindex="10" type="button" data-dismiss="modal">
                                                        <span>
                                                            <span>Close</span>
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>

                                        </div>

                                </div>
                            @endif
                    </div>  
                    <div class="modal fade modal-second modal-left" id="myModal_sop" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                    @if ($popUpHeader != null)
                                        @php
                                                $clientName = App\Http\Helper\Admin\Helpers::projectName(
                                                    $popUpHeader->project_id,
                                                );
                                                $sopDetails = App\Models\SopDoc::where('project_id',$popUpHeader->project_id)->where('sub_project_id',$popUpHeader->sub_project_id)->latest()->first('sop_path');                     
                                        @endphp
                                    @endif
                                <div class="modal-header" style="background-color: #139AB3;height: 84px">
                                    <h5 class="modal-title" id="exampleModalLabel" style="color: #ffffff;" >SOP</h5>
                                        @if (isset($sopDetails) && !empty($sopDetails->sop_path))
                                            <a href="{{ asset($sopDetails->sop_path) }}" target="_blank">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="18"
                                                    height="18"
                                                    fill="currentColor"
                                                    class="bi bi-arrow-up-right-square"
                                                    viewBox="0 0 16 16"
                                                    style="color: #ffffff; margin-left: 365px;">
                                                    <path fill-rule="evenodd"
                                                        d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.854 8.803a.5.5 0 1 1-.708-.707L9.243 6H6.475a.5.5 0 1 1 0-1h3.975a.5.5 0 0 1 .5.5v3.975a.5.5 0 1 1-1 0V6.707z" />
                                                </svg>
                                            </a>
                                        @endif
                                    <button type="button" class="close comment_close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                </div>
                                <div class="modal-body">
                                        @if (isset($sopDetails) && !empty($sopDetails->sop_path))
                                            <iframe
                                                src="{{ asset($sopDetails->sop_path) }}"
                                                style="width: 100%; height: 418px;"
                                                frameborder="0"
                                                type="application/pdf">
                                            </iframe>
                                        @else
                                            <div
                                                class="d-flex justify-content-center align-items-center"
                                                style="width: 100%; height: 418px;">
                                                <span>No SOP document available.</span>
                                            </div>
                                        @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light-danger" data-dismiss="modal">Close</button>
                                    <!-- Additional buttons can be added here -->
                                </div>
                            </div>
                        </div>
                    </div>

@endsection
<style>
    .dropdown-item.active {
            color: #ffffff;
            text-decoration: none;
            background-color: #888a91;
    }

    .modal-left .modal-dialog {
        margin-top: 90px;
        margin-left: 320px;
        margin-right: auto;
    }

    .modal-left .modal-content {
        border-radius: 5px;
    }

    .modal-right .modal-dialog {
        margin-left: auto;
        margin-right: 220px;
        transition: margin 5s ease-in-out;
    }

    .modal-right .modal-content {
        border-radius: 5px;
    }
</style>
@push('view.scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#filterExpandButton").click(function() {
                var div = document.getElementById('filter_section');
                if (div.style.display !== 'none') {
                    div.style.display = 'none';
                }
                else {
                    div.style.display = 'block';
                }
            });
            var countDigits = {{ strlen($arReworkCount) }};
            var newWidth = 30 + (countDigits - 1) * 6;
            var newHeight = 30 + (countDigits - 1) * 6;
            $('.code-badge-tab-selected').css({
                'width': newWidth + 'px',
                'height': newHeight + 'px'
            });
            $('.code-badge-tab').css({
                'width': newWidth + 'px',
                'height': newHeight + 'px'
            });
            var indvidualSearchFieldsCount = Object.keys(@json($projectColSearchFields)).length;
            var arStatusList = @json( $arStatusList);
            var arActionList = @json($arActionListVal);
            var arDenialList = @json($arDenialList);
            var arSubStatusList = @json($arSubStatusList);
            const url = window.location.href;
            const startIndex = url.indexOf('projects_') + 'projects_'.length;
            const endIndex = url.indexOf('/', startIndex);
            const urlDynamicValue = url.substring(startIndex, endIndex);
                var d = new Date();
                var month = d.getMonth() + 1;
                var day = d.getDate();
                var date = (month < 10 ? '0' : '') + month + '-' +
                    (day < 10 ? '0' : '') + day + '-' + d.getFullYear();
            var table = $("#ar_rework_list").DataTable({
                processing: true,
                ordering: true,
                clientSide: true,
                lengthChange: false,
                searching: indvidualSearchFieldsCount > 0 ? false : true,
                paging: false,
                info: false,
                // pageLength: 20,
                scrollCollapse: true,
                scrollX: true,
                "initComplete": function(settings, json) {
                    $('body').find('.dataTables_scrollBody').addClass("scrollbar");
                },
                language: {
                    "search": '',
                    "searchPlaceholder": "   Search",
                },
                dom: "<'row'<'col-md-12'f><'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>"
            })
            table.buttons().container()
                .appendTo('.outside');
                $('.dataTables_filter').addClass('pull-left');
                var clientName = $('#clientName').val();
                var subProjectName = $('#subProjectName').val();
                $(document).on('click', '#filter_clear', function(e) {
                    window.location.href = baseUrl + 'projects_ar_rework/' + clientName + '/' + subProjectName +
                        "?parent=" +
                        getUrlVars()[
                            "parent"] +
                        "&child=" + getUrlVars()["child"];
                })
            var currentWorkRow = null;
            $(document).on('click', '.clickable-row', function(e) {
                    e.preventDefault();
                    currentWorkRow = $(this).closest('tr');
                    var $row = currentWorkRow;
                    var record_id = $row.find('#table_id').text().trim();

                    $('#reworkTabFormConfiguration')[0].reset();
                    $('#myModal_status').modal('show');
                    if (typeof showGlobalLoader === 'function') {
                        showGlobalLoader('Loading...', false, false);
                    } else {
                        $('#global-loader').css({
                            display: 'flex',
                            opacity: 1
                        });
                    }
                    var classes = $(this).attr('class');
                    var lastClass = '';
                    if (classes) {
                        var classArray = classes.split(' ');
                        var lastClass = classArray[classArray.length - 1];
                    }
                        var record_id =  $(this).closest('tr').find('#table_id').text();
                        var $row = $(this).closest('tr');
                        var tdCount = $row.find('td').length;
                        var thCount = tdCount - 1;

                    var headers = [];
                    $row.closest('table').find('thead th input').each(function() {
                        if ($(this).val() != undefined) {
                            headers.push($(this).val());
                        }
                    });
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                'content')
                        }
                    });

                    $.ajax({
                        url: "{{ url('client_completed_datas_details') }}",
                        method: 'POST',
                        global: false,
                        data: {
                            record_id: record_id,
                            clientName: clientName,
                            subProjectName: subProjectName,
                            urlDynamicValue: urlDynamicValue
                        },
                        success: function(response) {
                            if(lastClass == 'start'){
                                if (response.success == true) {
                                    handleClientCompletedData(response.clientData,headers);
                                } else {
                                    $('#myModal_status').modal('hide');
                                    js_notification('error', 'Something went wrong');
                                }
                            }
                        },
                            error: function() {
                                $('#myModal_status').modal('hide');
                                js_notification('error', 'Work log start failed');
                            },
                            complete: function() {
                                if (typeof hideGlobalLoader === 'function') {
                                    hideGlobalLoader();
                                } else {
                                    $('#global-loader').hide();
                                }
                            }
                    });
                    function handleClientCompletedData(clientData,headers) {
                        $.each(headers, function(index, header) {
                            value = clientData[header];
                            $('label[id="' + header + '"]').html("");
                            $('input[name="' + header + '[]"]').html("");
                                if (/_el_/.test(value)) {
                                    elementToRemove = 'add_more_'+header;
                                    $('#'+elementToRemove).remove();
                                    var values = value.split('_el_');
                                    var optionsJson =  $('.'+header).closest('.dynamic-field').find('.add_options').val();
                                    var optionsObject = optionsJson ? JSON.parse(optionsJson) : null;
                                    var optionsArray = optionsObject ? Object.values(optionsObject) : null;
                                    var addMandatory =  $('.'+header).closest('.dynamic-field').find('.add_mandatory').val();
                                    var inputType;
                                    $('select[name="' + header + '[]"]').val(values[0]).trigger('change');
                                        $('textarea[name="' + header + '[]"]').val(values[0]);
                                        if ($('input[name="' + header + '[]"][type="checkbox"]').length > 0) {
                                            var checkboxValues = values[0].split(','); 
                                            $('input[name="' + header + '[]"]').each(function() {
                                                var checkboxValue = $(this).val(); 
                                                var isChecked = checkboxValues.includes(checkboxValue);
                                                $(this).prop('checked', isChecked);
                                            });
                                        } else if($('input[name="' + header + '"][type="radio"]').length > 0) {

                                            $('input[name="' + header + '"]').filter('[value="' + values[0] + '"]').prop(
                                                'checked', true);
                                        } else {
                                            $('input[name="' + header + '[]"]').val(values[0]);
                                        }
                                        for (var i = 1; i < values.length; i++) {
                                            var selectType;
                                            var isLastValue = i === values.length - 1;
                                            var newElementId =  'dynamicElement_' + header + i;
                                                if ($('select[name="' + header + '[]"]').prop('tagName') != undefined) {
                                                        selectType = $('<select>', {
                                                            name: header + '[]',
                                                            class: 'form-control ' + header + ' white-smoke pop-non-edt-val',
                                                            id: header + i,
                                                            addMandatory
                                                        });
                                                        selectType.append($('<option>', { value: '', text: '-- Select --' }));
                                                        optionsArray.forEach(function(option) {
                                                            selectType.append($('<option>', {
                                                                value: option,
                                                                text: option,
                                                                selected: option == values[i]
                                                            }));
                                                        });

                                                        var selectWrapper = $('<div>', { class: 'col-md-10' }).append(selectType);
                                                            if(i === values.length - 1) {
                                                            var minusButton = $('<i>', { class: 'fa fa-plus add_more', id: 'add_more_'+header });
                                                        } else {
                                                            var minusButton = $('<i>', { class: 'fa fa-minus minus_button remove_more', id: header+ i });
                                                        }
                                                        var colLabel = $('<div>', { class: 'col-md-1 col-form-label text-lg-right pt-0 pb-4', style: 'margin-left: -1.3rem;' }).append(minusButton);
                                                        var rowDiv = $('<div>', { class: 'row mt-4', id: newElementId}).append(selectWrapper, colLabel);
                                                        $('select[name="' + header + '[]"]').closest('.dynamic-field').append(rowDiv);

                                                } else if ($('textarea[name="' + header + '[]"]').prop('nodeName') != undefined) {
                                                        inputType =  '<textarea name="' + header + '[]" '+addMandatory+' class="form-control ' + header + ' white-smoke pop-non-edt-val mt-0" rows="3" id="' + header + i + '">' + values[i] + '</textarea>';
                                                        if(i === values.length - 1) {
                                                            var minusButton = '<i class="fa fa-plus add_more" id="' +'add_more_'+header +'"></i>';
                                                        } else {
                                                            var minusButton = '<i class="fa fa-minus minus_button remove_more" id="'+header+ i +'"></i>';
                                                        }
                                                            var span = '<div class="row mt-4" id="' + newElementId + '">' +
                                                                '<div class="col-md-10">' + inputType + '</div><div class="col-md-1 col-form-label text-lg-right pt-0 pb-4" style="margin-left: -1.3rem;">' +
                                                                    minusButton +'</div><div></div></div>';
                                                            $('textarea[name="' + header + '[]"]').closest('.dynamic-field').append(span);
                                                } else if ($('input[name="' + header + '[]"][type="checkbox"]').length > 0 && Array.isArray(optionsArray)) {
                                                            inputType = '<div class="form-group row">';
                                                            optionsArray.forEach(function(option) {
                                                                var checked = (values[i] && values[i].split(',').includes(option.toString())) ? 'checked' : '';
                                                                inputType +=
                                                                    '<div class="col-md-6">' +
                                                                    '<div class="checkbox-inline mt-2">' +
                                                                    '<label class="checkbox pop-non-edt-val" style="word-break: break-all;" >' +
                                                                    '<input type="checkbox" name="' + header + '[]" value="' + option + '" '+addMandatory+' class="'+header +'" id="' +header + i + '" ' + checked + '>' + option +
                                                                    '<span></span>' +
                                                                    '</label>' +
                                                                    '</div>' +
                                                                    '</div>';
                                                            });

                                                            inputType += '</div>';
                                                            if(i === values.length - 1) {
                                                            var minusButton = '<i class="fa fa-plus add_more" id="' +'add_more_'+header +'"></i>';
                                                            } else {
                                                                var minusButton = '<i class="fa fa-minus minus_button remove_more" id="'+header+ i +'"></i>';
                                                            }
                                                            var span = '<div class="row mt-4" id="' + newElementId + '">' +
                                                                '<div class="col-md-10">' + inputType + '</div><div  class="col-md-1 col-form-label text-lg-right pt-0 pb-4" style="margin-left: -1.3rem;">' +
                                                                    minusButton + '</div><div></div></div>';

                                                            $('input[name="' + header + '[]"]').closest('.dynamic-field').append(span);
                                                } else if ($('input[name="' + header + '"][type="radio"]').length > 0 && Array.isArray(optionsArray)) {
                                                            inputType = '<div class="form-group row">';
                                                            optionsArray.forEach(function(option) {
                                                                var checked = (values[i] && values[i].split(',').includes(option.toString())) ? 'checked' : '';
                                                                inputType +=
                                                                    '<div class="col-md-6">' +
                                                                    '<div class="radio-inline mt-2">' +
                                                                    '<label class="radio pop-non-edt-val" style="word-break: break-all;" >' +
                                                                    '<input type="radio" name="' + header + '_' + i +'" '+addMandatory+'  class="'+header +'" value="' + option + '" id="' +
                                                                        header + i + '" ' + checked + '>' + option +
                                                                    '<span></span>' +
                                                                    '</label>' +
                                                                    '</div>' +
                                                                    '</div>';
                                                            });

                                                            inputType += '</div>';
                                                            if(i === values.length - 1) {
                                                            var minusButton = '<i class="fa fa-plus add_more" id="' +'add_more_'+header +'"></i>';
                                                            } else {
                                                                var minusButton = '<i class="fa fa-minus minus_button remove_more" id="'+header+ i +'"></i>';
                                                            }
                                                            var span = '<div class="row mt-4" id="' + newElementId + '">' +
                                                                '<div class="col-md-10">' + inputType + '</div><div  class="col-md-1 col-form-label text-lg-right pt-0 pb-4" style="margin-left: -1.3rem;">' +
                                                                    minusButton + '</div><div></div></div>';
                                                            $('input[name="' + header + '"]').closest('.dynamic-field').append(span);
                                                } else {
                                                    var fieldType =  $('.'+header).attr('type');
                                                    var classes = $('.'+header).attr('class');
                                                    var classArray = classes.split(' ');
                                                    var dateRangeClass = '';
                                                    for (var j = 0; j < classArray.length; j++) {
                                                        if (classArray[j] === 'date_range') {
                                                            dateRangeClass = classArray[j];
                                                            break;
                                                        }
                                                    }
                                                    if(dateRangeClass == 'date_range') {
                                                        inputType = '<input type="'+fieldType+'" name="' + header +'[]" '+addMandatory+' class="form-control date_range ' + header + ' white-smoke pop-non-edt-val" autocomplete="none" style="cursor:pointer" value="' + values[i] + '" id="' +header + i + '">';
                                                            if(i === values.length - 1) {
                                                                    var minusButton = '<i class="fa fa-plus add_more" id="' +'add_more_'+header +'"></i>';
                                                            } else {
                                                                var minusButton = '<i class="fa fa-minus minus_button remove_more" id="'+ header+ i +'"></i>';
                                                            }
                                                    }
                                                    else {
                                                        inputType = '<input type="'+fieldType+'" name="' + header +'[]" '+addMandatory+' class="form-control ' + header + ' white-smoke pop-non-edt-val"  value="' + values[i] + '" id="' +header + i + '">';
                                                        if(i === values.length - 1) {
                                                                var minusButton = '<i class="fa fa-plus add_more" id="' +'add_more_'+header +'"></i>';
                                                        } else {
                                                            var minusButton = '<i class="fa fa-minus minus_button remove_more" id="'+ header+ i +'"></i>';
                                                        }
                                                    }
                                                    var span = '<div class="row mt-4"  id="' +newElementId+ '">' +
                                                        '<div class="col-md-10">'+ inputType +'</div><div  class="col-md-1 col-form-label text-lg-right pt-0 pb-4" style="margin-left: -1.3rem;">' +
                                                            minusButton +'</div><div></div></div>';
                                                        $('input[name="' + header + '[]"]').closest('.dynamic-field').append(span);
                                                }
                                        }
                                        $('.date_range').daterangepicker({
                                            autoUpdateInput: false,
                                        }).on('apply.daterangepicker', function(ev, picker) {
                                            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                                        }).attr("autocomplete", "off");

                                } else if ($('input[name="' + header + '[]"]').is(':checkbox') && value !== null) {
                                    var checkboxValues = value.split(',');
                                    $('input[name="' + header + '[]"]').each(function() {
                                        $(this).prop('checked', checkboxValues.includes($(this).val()));
                                    });
                                } else if ($('input[name="' + header + '"]').is(':radio') && value !== '' && value !== null) {
                                if(value.length > 0) {
                                        $('input[name="' + header + '"]').filter('[value="' + value + '"]').prop(
                                            'checked', true);
                                }
                                } else if ($('select[name="' + header + '[]"]').length) {
                                $('select[name="' + header + '[]"]').val(value).trigger('change');
                                } else {
                                    $('input[name="parentId"]').val(clientData['parent_id']);
                                    $('input[name="record_old_status"]').val(clientData['chart_status']);
                                    if (header === 'chart_status' && value.includes('CE_')) {
                                            claimStatus = value;
                                            value = value.replace('CE_', '');
                                            $('select[name="chart_status"]').val(claimStatus).trigger('change');
                                        $('#title_status').text(value);
                                    }
                                    if (header == 'id') {
                                        $('input[name="idValue"]').val(value);
                                    }
                                    if (header == 'invoke_date') {
                                        $('input[name="invoke_date"]').val(value);
                                    }
                                    if (header == 'CE_emp_id') {
                                        $('input[name="CE_emp_id"]').val(value);
                                    }
                                    if (header == 'ar_status_code') {
                                        $('select[name="ar_status_code"]').val(value).trigger('change');
                                        $('#ar_status_val').val(value);
                                    }
                                    if (header == 'ar_action_code') {
                                        statusVal = $('#ar_status_val').val();
                                        actionCode(statusVal,value);
                                    }
                                    if (header == 'ar_denial_codes') {
                                        $('select[name="ar_denial_codes"]').val(value).trigger('change');
                                        $('#ar_denial_val').val(value);
                                    }
                                    if (header == 'ar_substatus_codes') {
                                        $('select[name="ar_substatus_codes"]').val(value).trigger('change');
                                        $('#ar_substatus_val').val(value);
                                    }
                                    $('textarea[name="' + header + '[]"]').val(value);
                                    $('label[id="' + header + '"]').text(value);
                                        if(value != null) {
                                            $('input[name="' + header + '[]"]').val(value);
                                            $('input[name="' + header + '"]').val(value);
                                        }
                                }
                        });

                    }
            });
            $(document).on('click', '.clickable-view', function(e) {
                    var record_id =  $(this).closest('tr').find('#table_id').text();
                    var $row = $(this).closest('tr');
                    var tdCount = $row.find('td').length;
                    var thCount = tdCount - 1;

                    var headers = [];
                    $row.closest('table').find('thead th input').each(function() {
                        if ($(this).val() != undefined) {
                            headers.push($(this).val());
                        }
                    });
                    $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content')
                    }
                  });

                    $.ajax({
                        url: "{{ url('client_view_details') }}",
                        method: 'POST',
                        data: {
                            record_id: record_id,
                            clientName: clientName,
                            subProjectName: subProjectName,
                        },
                        success: function(response) {
                            if (response.success == true) {

                                $('#myModal_view').modal('show');
                                handleClientData(response.clientData,headers);
                            } else {
                                $('#myModal_view').modal('hide');
                                js_notification('error', 'Something went wrong');
                            }
                        },
                    });
                    function handleClientData(clientData,headers) {
                    $.each(headers, function(index, header) {
                        value = clientData[header];
                        $('label[id="' + header + '"]').html("");
                    if (/_el_/.test(value)) {
                        var values = value.split('_el_');
                        var formattedDatas = [];
                        values.forEach(function(data, index) {
                            var circle = $('<span>').addClass('circle');
                            var span = $('<span>').addClass('date-label').text(data);
                                span.prepend(circle);
                                   formattedDatas.push(span);
                        });
                        formattedDatas.forEach(function(span, index) {

                            $('label[id="' + header + '"]').append(span);
                            // Add comma after each span except the last one

                        });
                    } else {
                        if (header === 'chart_status' && value.includes('CE_')) {
                                value = value.replace('CE_', '');
                                $('#title_status_view').text(value);
                        }
                        if (header == 'ar_status_code') {
                                var statusName = '';
                                    $.each(arStatusList, function(key, val) {
                                        if (value == key) {
                                            statusName = val;
                                        }
                                    });
                                    if(statusName == '') {
                                        $('label[id="ar_status_label"]').css('display','none');
                                    } else {
                                        $('label[id="ar_status_label"]').css('display','block');
                                    }
                                    $('label[id="ar_status_view"]').text(statusName);
                               }
                            if (header == 'ar_action_code') {
                                var subStatusName = '';
                                $.each(arActionList, function(key, val) {
                                    if (value == key) {
                                        subStatusName = val;
                                    }
                                });
                                  if(subStatusName == '') {
                                    $('label[id="ar_action_label"]').css('display','none');
                                } else {
                                    $('label[id="ar_action_label"]').css('display','block');
                                }
                                $('label[id="ar_action_view"]').text(subStatusName);
                            }
                            if (header == 'ar_denial_codes') {
                                var denialName = '';
                                $.each(arDenialList, function(key, val) {
                                    if (value == key) {
                                        denialName = val;
                                    }
                                });
                                $('label[id="ar_denial_view"]').text(denialName);
                            }
                            if (header == 'ar_substatus_codes') {
                                var subStatusName = '';
                                $.each(arSubStatusList, function(key, val) {
                                    if (value == key) {
                                        subStatusName = val;
                                    }
                                });
                                $('label[id="ar_substatus_view"]').text(subStatusName);
                            }
                       $('label[id="' + header + '"]').text(value);
                    }

                    function formatDate(dateString) {
                        var parts = dateString.split('-');
                        var formattedDatas = parts[1] + '/' + parts[2] + '/' + parts[0];
                        return formattedDatas;
                    }
                        
                  });

               }
            });

            $(document).on('click', '.one', function() {
                window.location.href = baseUrl + 'projects_assigned/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] +
                    "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.two', function() {
                window.location.href = baseUrl + 'projects_pending/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.three', function() {
                window.location.href = baseUrl + 'projects_hold/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.four', function(e) {
                // window.location.href = "{{ url('#') }}";
                window.location.href = baseUrl + 'projects_completed/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.five', function() {
                window.location.href = baseUrl + 'projects_Revoke/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.six', function() {
                window.location.href = baseUrl + 'projects_duplicate/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.seven', function() {
                window.location.href = baseUrl + 'projects_unassigned/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.eight', function() {
                window.location.href = baseUrl + 'projects_non_workable/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.nine', function() {
                window.location.href = baseUrl + 'ar_rebuttal/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.ten', function() {
                window.location.href = baseUrl + 'projects_auto_close/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.eleven', function() {
                window.location.href = baseUrl + 'projects_ar_rework/' + clientName + '/' + subProjectName +
                    "?parent=" + getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            });
                function actionCode(statusVal,value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: "GET",
                        url: "{{ url('production/ar_action_code_list') }}",
                        data: {
                            status_code_id: statusVal
                        },
                        success: function(res) {
                            subStatusCount = Object.keys(res.subStatus).length;
                            var sla_options = '<option value="">-- Select --</option>';
                            $.each(res.subStatus, function(key, value) {
                                sla_options += '<option value="' + key + '" ' + '>' + value +
                                    '</option>';
                            });
                            $('select[name="ar_action_code"]').html(sla_options);
                             if (value) {
                                $('select[name="ar_action_code"]').val(value);
                            }
                        },
                        error: function(jqXHR, exception) {}
                    });
                }
                $(document).on('change', '#ar_status_code', function() {
                    var status_code_id = $(this).val();
                        KTApp.block('#myModal_status', {
                            overlayColor: '#000000',
                            state: 'danger',
                            opacity: 0.1,
                            message: 'Fetching...',
                        });
                        actionCode(status_code_id,'');
                    KTApp.unblock('#myModal_status');
                });
            $(document).on('click', '#sop_click', function(e) {
                $('#myModal_sop').modal('show');
            });
                $('#myModal_sop').on('shown.bs.modal', function () {
                     $('#myModal_view').addClass('modal-right');
                });

                $('#myModal_sop').on('hidden.bs.modal', function () {
                    $('#myModal_view').removeClass('modal-right');
                });
                $(document).on('click', '#project_complete_save', function(e) {
                    e.preventDefault();
            
                    var fieldNames = $('#reworkTabFormConfiguration').serializeArray().map(function(input) {
                        return input.name;
                    });
                    var requiredFields = {};
                    var requiredFieldsType = {};
                    var inputclass = [];
                    var inputTypeValue = 0; var inputTypeRadioValue = 0;
                    var claimStatus =  $('#chart_status').val();
                        if(claimStatus == "CE_Hold") {
                            var ceHoldReason = $('#ce_hold_reason');
                            if(ceHoldReason.val() == '') {
                                ceHoldReason.css('border-color', 'red', 'important');
                                    inputTypeValue = 1;
                            } else {
                                    ceHoldReason.css('border-color', '');
                                    inputTypeValue = 0;
                            }
                        }

                   if (
                       ($('#ar_status_code').val() == '' || $('#ar_status_code').val() == null) ||
                       ($('#ar_action_code').val() == '' || $('#ar_action_code').val() == null) ||
                       ($('#ar_denial_codes').val() == '' || $('#ar_denial_codes').val() == null)
                    ) {
                        e.preventDefault(); // block form submission
                        inputTypeValue = 1; // at least one field is invalid
                    }
                    // Validate status code
                    if ($('#ar_status_code').val() == '' || $('#ar_status_code').val() == null) {
                        $('#ar_status_code').next('.select2-container').find('.select2-selection').css('border', '1px solid red');
                    } else {
                        $('#ar_status_code').next('.select2-container').find('.select2-selection').css('border', '');
                    }

                    // Validate action code
                    if ($('#ar_action_code').val() == '' || $('#ar_action_code').val() == null) {
                        $('#ar_action_code').next('.select2-container').find('.select2-selection').css('border', '1px solid red');
                    } else {
                        $('#ar_action_code').next('.select2-container').find('.select2-selection').css('border', '');
                    }

                    // Validate denial code
                    if ($('#ar_denial_codes').val() == '' || $('#ar_denial_codes').val() == null) {
                        $('#ar_denial_codes').next('.select2-container').find('.select2-selection').css('border', '1px solid red');
                    } else {
                        $('#ar_denial_codes').next('.select2-container').find('.select2-selection').css('border', '');
                    }         
                    $('#reworkTabFormConfiguration').find(':input[required], select[required], textarea[required]',
                        ':input[type="checkbox"][required], input[type="radio"][required]').each(
                        function() {
                            var fieldName = $(this).attr('name');
                            var fieldType = $(this).attr('type') || $(this).prop('tagName').toLowerCase();

                            if (!requiredFields[fieldType]) {
                                requiredFields[fieldType] = [];
                            }

                             requiredFields[fieldType].push(fieldName);
                        });
                    $('input[type="radio"]').each(function() {
                        var groupName = $(this).attr("name");
                        var mandatory = $(this).prop('required');
                         if ($('input[type="radio"][name="' + groupName + '"]:checked').length === 0 && mandatory === true) {
                            $('#radio_p1').css('display', 'block');
                            inputTypeRadioValue = 1;
                        } else {
                            $('#radio_p1').css('display', 'none');
                            inputTypeRadioValue = 0;
                        }
                    });


                    $('input[type="checkbox"]').each(function() {
                        var groupName = $(this).attr("id");
                        var mandatory = $(this).prop('required');
                         if($(this).attr("name") !== 'check[]' && $(this).attr("name") !== undefined) {
                            if ($('input[type="checkbox"][id="' + groupName + '"]:checked').length === 0) {
                                if ($('input[type="checkbox"][id="' + groupName + '"]:checked').length ===
                                    0 && mandatory === true) {
                                    $('#check_p1').css('display', 'block');
                                    inputTypeValue = 1;
                                } else {
                                    $('#check_p1').css('display', 'none');
                                    inputTypeValue = 0;
                                }
                                return false;
                            }
                        }
                    });

                    for (var fieldType in requiredFields) {
                        if (requiredFields.hasOwnProperty(
                                fieldType)) {
                            var fieldNames = requiredFields[fieldType];
                            fieldNames.forEach(function(fieldNameVal) {
                                var label_id = $('' + fieldType + '[name="' + fieldNameVal + '"]').attr(
                                    'class');
                                var classValue = (fieldType == 'text' || fieldType == 'date') ? $(
                                        'input' + '[name="' + fieldNameVal + '"]').attr(
                                        'class') : $('' + fieldType + '[name="' + fieldNameVal + '"]')
                                    .attr(
                                        'class');
                                if (classValue !== undefined) {
                                    var classes = classValue.split(' ');
                                    inputclass.push($('.' + classes[1]));
                                    inclass = $('.' + classes[1]);
                                    inclass.each(function(element) {

                                        var label_id = $(this).attr('id');
                                        if ($(this).val() == '') {
                                            if ($(this).val() == '') {
                                                e.preventDefault();
                                                $(this).css('border-color', 'red', 'important');
                                                inputTypeValue = 1;
                                            } else {
                                                $(this).css('border-color', '');
                                                inputTypeValue = 0;
                                            }
                                            return false;
                                        }
                                    });
                                }
                            });

                        }
                    }

                    var fieldValuesByFieldName = {};

                    $('input[type="radio"]:checked').each(function() {
                        var fieldName = $(this).attr('class');
                        var fieldValue = $(this).val();
                        if (!fieldValuesByFieldName[fieldName]) {
                            fieldValuesByFieldName[fieldName] = [];
                        }

                        fieldValuesByFieldName[fieldName].push(fieldValue);
                    });
                    var groupedData = {};
                    Object.keys(fieldValuesByFieldName).forEach(function(key) {
                        var columnName = key;
                        if (!groupedData[columnName]) {
                            groupedData[columnName] = [];
                        }
                        groupedData[columnName] = groupedData[columnName].concat(fieldValuesByFieldName[
                            key]);
                    });
                    $.each(fieldValuesByFieldName, function(fieldName, fieldValues) {
                        $.each(fieldValues, function(index, value) {
                            $('<input>').attr({
                                type: 'hidden',
                                name: fieldName + '[]',
                                value: value
                            }).appendTo('form#reworkTabFormConfiguration');
                        });
                    });

                    if (inputTypeValue == 0 && inputTypeRadioValue == 0) {
                        swal.fire({
                            text: "Do you want to update?",
                            icon: "success",
                            buttonsStyling: false,
                            showCancelButton: true,
                            confirmButtonText: "Yes",
                            cancelButtonText: "No",
                            reverseButtons: true,
                            customClass: {
                                confirmButton: "btn font-weight-bold btn-white-black",
                                cancelButton: "btn font-weight-bold btn-light-danger",
                            }

                        }).then(function(result) {
                            if (result.value == true) {
                            
                                var statusVal = $('#chart_status').val();
                                var formData = new FormData($('#reworkTabFormConfiguration')[0]);

                                $('#project_complete_save').prop('disabled', true);
                                /* hide popup immediately */
                                $('#myModal_status').modal('hide');
                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open').css('padding-right', '');                           
                                
                            
                                /* stop global loader */
                                window.skipGlobalLoader = true;
                                $('.blockUI, .blockOverlay').remove();
                                KTApp.block('#production_ar_rework_tab', {
                                    overlayColor: '#000000',
                                    state: 'danger',
                                    opacity: 0.1,
                                    message: 'Updating...',
                                });
                                $.ajax({
                                    url: $('#reworkTabFormConfiguration').attr('action'),
                                    method: 'POST',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    cache: false,
                                    global: false,
                                    success: function(response) {
                                        if (response.success != true && response.status !== 'success') {
                                            js_notification('error', response.message || 'Save failed. Please refresh and check.');
                                        }
                                    },
                                        error: function(xhr) {
                                        let msg = 'Something went wrong';
                                        if (xhr.responseJSON && xhr.responseJSON.message) {
                                            msg = xhr.responseJSON.message;
                                        }
                                        js_notification('error', msg);
                                    },
                                    complete: function() {
                                        $('#project_complete_save').prop('disabled', false);
                                        window.skipGlobalLoader = false;
                                        $('#global-loader').hide();
                                            KTApp.unblock('#production_ar_rework_tab');
                                            js_notification('success', 'Updated successfully');                                         
                                        $('.blockUI, .blockOverlay').remove();
                                        
                                    }
                                });
                            } else {
                            }
                        });
                    } else {
                       return false;
                    }
                });
                $(document).on('click', '#assign_export', function(e) {   
                    var resourceName = null; 
                    var formData = $('#formSearch').serialize();
                    var chartStatus = "CE_Completed";
                    var recordStatusVal = "user_rework";
                    formData += '&chart_status=' + chartStatus;
                    formData += '&clientName=' + clientName;
                    formData += '&subProjectName=' + subProjectName;
                    formData += '&resourceName=' + resourceName;                    
                    formData += '&recordStatusVal=' + recordStatusVal;
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                'content')
                        }
                    });
                    KTApp.block('#export_div', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Fetching...',
                    });
                    $.ajax({
                            url: "{{ url('client_export') }}",
                            method: 'POST',
                            data: formData,
                            xhrFields: {
                                responseType: 'blob'  // This is crucial for downloading Excel
                            },
                            success: function(response, status, xhr) {  // Correct order of parameters
                                var filename = "";
                                var disposition = xhr.getResponseHeader('Content-Disposition');
                                if (disposition && disposition.indexOf('attachment') !== -1) {
                                    var matches = /filename[^;=\n]*=([^;\n]*)/.exec(disposition);                            
                                    if (matches != null && matches[1]) {
                                        // Trim any extra spaces or quotes around the filename
                                        filename = matches[1].trim().replace(/^"|"$/g, '');
                                    }
                                }

                                var blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                                var link = document.createElement('a');
                                link.href = window.URL.createObjectURL(blob);
                                link.download = filename || 'export.xlsx';
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                                KTApp.unblock('#export_div');
                            },
                            error: function(response) {
                                console.log('Error generating Excel file', response);
                            }
                      });

                });             
        })
    </script>
@endpush
