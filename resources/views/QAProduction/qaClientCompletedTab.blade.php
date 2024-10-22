@extends('layouts.app3')
@php
use Carbon\Carbon;
@endphp
@section('content')

    <div class="card card-custom custom-card">
        <div class="card-body p-0">
            @php
                $empDesignation =
                    Session::get('loginDetails') &&
                    Session::get('loginDetails')['userDetail']['user_hrdetails'] &&
                    Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null
                        ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']
                        : '';
                $loginEmpId =
                    Session::get('loginDetails') &&
                    Session::get('loginDetails')['userDetail'] &&
                    Session::get('loginDetails')['userDetail']['emp_id'] != null
                        ? Session::get('loginDetails')['userDetail']['emp_id']
                        : '';
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
                                        $sopDetails = App\Models\SopDoc::where('project_id', $popUpHeader->project_id)
                                            ->where('sub_project_id', $popUpHeader->sub_project_id)
                                            ->latest()
                                            ->first('sop_path');
                                        // $pdfName =  preg_replace('/[^A-Za-z0-9]/', '_',$clientNameDetails->project_name);
                                    @endphp
                                @else
                                    @php
                                        $sopDetails = '';
                                        // $pdfName = '';
                                    @endphp
                                @endif
                                <a href={{ isset($sopDetails) && isset($sopDetails->sop_path) ? asset($sopDetails->sop_path) : '#' }}
                                    target="_blank">
                                    <button type="button" class="btn text-white mr-3"
                                        style="background-color:#139AB3">SOP</button>
                                </a>
                            </div>
                            <div class="outside float-right" href="javascript:void(0);"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wizard wizard-4 custom-wizard" id="kt_wizard_v4" data-wizard-state="step-first"
                data-wizard-clickable="true" style="margin-top:-2rem !important">
                <div class="wizard-nav">
                    <div class="wizard-steps">
                        <div class="wizard-step mb-0 one" data-wizard-type="done">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Assigned</h6>
                                        @include('CountVar.countRectangle', ['count' => $assignedCount])
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (
                            $loginEmpId == 'Admin' ||
                                strpos($empDesignation, 'Manager') !== false ||
                                strpos($empDesignation, 'VP') !== false ||
                                strpos($empDesignation, 'Leader') !== false ||
                                strpos($empDesignation, 'Team Lead') !== false ||
                                strpos($empDesignation, 'CEO') !== false ||
                                strpos($empDesignation, 'Vice') !== false)
                            <div class="wizard-step mb-0 five" data-wizard-type="done">
                                <div class="wizard-wrapper py-2">
                                    <div class="wizard-label p-2 mt-2">
                                        <div class="wizard-title" style="display: flex; align-items: center;">
                                            <h6 style="margin-right: 5px;">UnAssigned</h6>
                                            @include('CountVar.countRectangle', [
                                                'count' => $unAssignedCount,
                                            ])
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
                                        @include('CountVar.countRectangle', ['count' => $pendingCount])
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wizard-step mb-0 three" data-wizard-type="done">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Hold</h6>
                                        @include('CountVar.countRectangle', ['count' => $holdCount])
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wizard-step mb-0 four" data-wizard-type="step">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Completed</h6>
                                        @include('CountVar.countRectangle', ['count' => $completedCount])
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wizard-step mb-0 seven" data-wizard-type="done">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Auto Close</h6>
                                        @include('CountVar.countRectangle', ['count' => $autoCloseCount])
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (
                            $loginEmpId == 'Admin' ||
                            strpos($empDesignation, 'Manager') !== false ||
                            strpos($empDesignation, 'VP') !== false ||
                            strpos($empDesignation, 'Leader') !== false ||
                            strpos($empDesignation, 'Team Lead') !== false ||
                            strpos($empDesignation, 'CEO') !== false ||
                            strpos($empDesignation, 'Vice') !== false)
                            <div class="wizard-step mb-0 eight" data-wizard-type="step">
                                <div class="wizard-wrapper py-2">
                                    <div class="wizard-label p-2 mt-2">
                                        <div class="wizard-title" style="display: flex; align-items: center;">
                                            <h6 style="margin-right: 5px;">Rebuttal</h6>
                                            @include('CountVar.countRectangle', [
                                                'count' => $rebuttalCount,
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                       
                        {{-- <div class="wizard-step mb-0 five" data-wizard-type="done">
                                        <div class="wizard-wrapper py-2">
                                            <div class="wizard-label p-2 mt-2">
                                                <div class="wizard-title" style="display: flex; align-items: center;">
                                                    <h6 style="margin-right: 5px;">Rework</h6>
                                                        @include('CountVar.countRectangle', ['count' => $reworkCount])
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}
                        {{-- @if ($loginEmpId == 'Admin' || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)
                                        <div class="wizard-step mb-0 six" data-wizard-type="done">
                                            <div class="wizard-wrapper py-2">
                                                <div class="wizard-label p-2 mt-2">
                                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                                        <h6 style="margin-right: 5px;">Duplicate</h6>
                                                           @include('CountVar.countRectangle', ['count' => $duplicateCount])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif --}}
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
                                $decodedClientName = App\Http\Helper\Admin\Helpers::projectName($data->project_id)->project_name;
                                $decodedsubProjectName = $data->sub_project_i == NULL ? 'project' :App\Http\Helper\Admin\Helpers::subProjectName($data->project_id,$data->sub_project_id);
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
                                    url('qa_production/qa_projects_completed/' . $clientName . '/' . $subProjectName) .
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
                                                {{ $labelName }}
                                            </label>
                                            <div class="col-md-10">
                                                @if ($options == null)
                                                    @if ($inputType != 'date_range')
                                                        {!! Form::$inputType($columnName,isset($searchData) && !empty($searchData) ? $searchData[$columnName] : null, [
                                                            'class' => 'form-control ' . $columnName . ' white-smoke pop-non-edt-val',
                                                            'autocomplete' => 'none',
                                                            'style' => 'cursor:pointer',
                                                            'rows' => 3,
                                                            'id' => $columnName,
                                                        ]) !!}
                                                    @else
                                                        {!! Form::text($columnName, null, [
                                                            'class' => 'form-control date_range daterange_' . $columnName . ' white-smoke pop-non-edt-val',
                                                            'autocomplete' => 'none',
                                                            'style' => 'cursor:pointer',
                                                            'id' => 'date_range',                
                                                        ]) !!}
                                                    @endif
                                                @else
                                                    @if ($inputType == 'select')
                                                        {!! Form::$inputType($columnName, ['' => '-- Select --'] + $associativeOptions, isset($searchData) && !empty($searchData) ? $searchData[$columnName] : null, [
                                                            'class' => 'form-control ' . $columnName . ' white-smoke pop-non-edt-val select2',
                                                            'autocomplete' => 'none',
                                                        
                                                            'id' => $columnName,                                                        
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
                <div class="card-body py-0 px-7">
                    <input type="hidden" value={{ $clientName }} id="clientName">
                    <input type="hidden" value={{ $subProjectName }} id="subProjectName">
                    <div class="table-responsive pt-5 pb-5 clietnts_table">
                        <table class="table table-separate table-head-custom no-footer dtr-column "
                            id="client_completed_list" data-order='[[ 0, "desc" ]]'>
                            <thead>
                                @if (!empty($columnsHeader))
                                    <tr>
                                        <th class='notexport' style="color:white !important">Action</th>
                                        @foreach ($columnsHeader as $columnName => $columnValue)
                                            @if ($columnValue != 'id')
                                                <th><input type="hidden" value={{ $columnValue }}>
                                                    @if ($columnValue == 'chart_status')
                                                        Charge Status
                                                    @else
                                                        {{ ucwords(str_replace(['_else_', '_'], ['/', ' '], $columnValue)) }}
                                                    @endif
                                                </th>
                                            @else
                                                <th style="display:none" class='notexport'><input type="hidden"
                                                        value={{ $columnValue }}>
                                                        @if ($columnValue == 'chart_status')
                                                            Charge Status
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
                                @if (isset($completedProjectDetails))
                                    @foreach ($completedProjectDetails as $data)
                                        @php
                                        $arrayAttrributes = $data->getAttributes();
                                        $arrayAttrributes['aging']= null; 
                                        $arrayAttrributes['aging_range']= null;                                       
                                        @endphp
                                        <tr>
                                            <td> <button class="task-start clickable-view" title="View"><i
                                                        class="fa far fa-eye text-eye icon-circle1 mt-0"></i></button></td>
                                            @foreach ($arrayAttrributes as $columnName => $columnValue)
                                                @php
                                                    $columnsToExclude = [
                                                        'ce_hold_reason',
                                                        'qa_hold_reason',
                                                        'qa_work_status',
                                                        'QA_required_sampling',
                                                        'QA_rework_comments',
                                                        'coder_rework_reason',
                                                        'coder_error_count',
                                                        'qa_error_count',
                                                        'tl_error_count',
                                                        'tl_comments',
                                                        'QA_followup_date',
                                                        'CE_status_code',
                                                        'CE_sub_status_code',
                                                        'CE_followup_date',
                                                        'coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                                                        'created_at',
                                                        'updated_at',
                                                        'deleted_at',
                                                    ];
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
                                                        <td
                                                            style="max-width: 300px;
                                                                white-space: normal;">
                                                           @if (str_contains($columnValue, '-') && strtotime($columnValue))
                                                           {{ date('m/d/Y', strtotime($columnValue)) }}
                                                       @elseif ($columnName == 'QA_status_code')
                                                           @php
                                                               if($columnValue != null) {
                                                                   $statusCode = App\Http\Helper\Admin\Helpers::qaStatusById($columnValue);
                                                               } else {
                                                                   $statusCode = '';
                                                               }
                                                           @endphp
                                                           {{ $columnValue == null ? $columnValue : $statusCode['status_code'] }}
                                                       @elseif ($columnName == 'QA_sub_status_code')
                                                           @php
                                                               if($columnValue != null) {
                                                               $subStatusCode = App\Http\Helper\Admin\Helpers::qaSubStatusById($columnValue);
                                                           } else {
                                                               $subStatusCode = '';
                                                           }
                                                           @endphp
                                                           {{ $columnValue == null ? $columnValue :  $subStatusCode['sub_status_code'] }}
                                                           @elseif ($columnName == 'qa_classification')
                                                                @php
                                                                    if($columnValue != null) {
                                                                    $qaClassification = App\Http\Helper\Admin\Helpers::qaClassificationById($columnValue);
                                                                } else {
                                                                    $qaClassification = '';
                                                                }
                                                                @endphp
                                                                {{ $columnValue == null ? $columnValue :  $qaClassification['qa_classification'] }} 
                                                           @elseif ($columnName == 'qa_category')
                                                            @php
                                                                if($columnValue != null) {
                                                                $qaCategory = App\Http\Helper\Admin\Helpers::qaCategoryById($columnValue);
                                                            } else {
                                                                $qaCategory = '';
                                                            }
                                                            @endphp
                                                            {{ $columnValue == null ? $columnValue :  $qaCategory['qa_category'] }}
                                                           @elseif ($columnName == 'qa_scope')
                                                            @php
                                                                if($columnValue != null) {
                                                                $qaScope = App\Http\Helper\Admin\Helpers::qaScopeById($columnValue);
                                                            } else {
                                                                $qaScope = '';
                                                            }
                                                            @endphp
                                                            {{ $columnValue == null ? $columnValue :  $qaScope['qa_scope'] }}
                                                        @elseif ($columnName == 'aging')                                                                                  
                                                           {{ $agingCount }}
                                                       @elseif ($columnName == 'aging_range')
                                                           {{ $agingRange }}
                                                       @else
                                                           @if ($columnName == 'chart_status' && str_contains($columnValue, 'QA_'))
                                                               {{ str_replace('QA_', '', $columnValue) }}
                                                           @else
                                                               {{ $columnValue }}
                                                           @endif
                                                       @endif
                                                        </td>
                                                    @else
                                                        <td style="display:none;max-width: 300px;
                                                                white-space: normal;"
                                                            id="table_id">
                                                            @if (str_contains($columnValue, '-') && strtotime($columnValue))
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
                        <div class="ml-3">
                            Showing {{ $completedProjectDetails->firstItem() != null ? $completedProjectDetails->firstItem() : 0 }} to {{ $completedProjectDetails->lastItem() != null ? $completedProjectDetails->lastItem() : 0 }} of {{ $completedProjectDetails->total() }} entries
                        </div>
                         <div>
                            {{ $completedProjectDetails->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="modal fade modal-first" id="myModal_view" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
            data-backdrop="static" aria-hidden="true">
            @if ($popUpHeader != null)
                <div class="modal-dialog">
                    @php
                        $clientName = App\Http\Helper\Admin\Helpers::projectName($popUpHeader->project_id);
                        $projectName = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(
                            $popUpHeader->project_id,
                            'encode',
                        );
                        if ($popUpHeader->sub_project_id != null) {
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
                                    <div class="rounded-circle bg-white text-black mr-2"
                                        style="width: 50px; height: 50px; display: flex; justify-content: center; align-items: center;font-weight;bold">
                                        <span>{{ strtoupper(substr($clientName->project_name, 0, 1)) }}</span>
                                    </div>&nbsp;&nbsp;
                                    <div>
                                        <h4 class="modal-title mb-0" id="myModalLabel" style="color: #ffffff;">
                                            {{ ucfirst($clientName->aims_project_name) }}
                                        </h4>
                                        @if ($practiceName != '')
                                            <h6 style="color: #ffffff;font-size:1rem;">
                                                {{ ucfirst($practiceName->sub_project_name) }}</h6>
                                        @endif
                                    </div>&nbsp;&nbsp;
                                    <div class="bg-white rounded-pill px-2 text-black"
                                        style="margin-bottom: 2rem;margin-left:2.2px;font-size:10px;font-weight:500;background-color:#E9F3FF;color:#139AB3;">
                                        <span id="title_status_view"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="col-md-8 d-flex justify-content-end" style="display: -webkit-box !important;">
                                             <button type="button" class="btn btn-black-white mr-3" id="sop_click" style="padding: 0.35rem 1rem;">SOP</button>
                                        </div> --}}

                            <button type="button" class="close comment_close" data-dismiss="modal" aria-hidden="true"
                                style="color:#ffffff !important">&times;</button>

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
                                                    str_replace([' ', '/'], ['_', '_else_'], $data->label_name),
                                                );
                                            @endphp

                                            <label class="col-md-12">{{ $data->label_name }}
                                            </label>
                                            <input type="hidden" name="{{ $columnName }}">

                                            <label class="col-md-12 pop-non-edt-val" id={{ $columnName }}>
                                            </label>
                                            <hr style="margin-left:1rem">
                                        @endforeach
                                    @endif
                                </div>
                                <div class="col-md-9" style="border-left: 1px solid #ccc;" data-scroll="true"
                                    data-height="400">
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
                                                    <label class="col-md-12">
                                                        {{ $labelName }}
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id={{ $columnName }}>
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
            <hr>
            <h6 class="title-h6">QA</h6>&nbsp;&nbsp;
            @if (count($popupQAEditableFields) > 0)
                @php $count = 0; @endphp
                @foreach ($popupQAEditableFields as $key => $data)
                    @php
                        $labelName = $data->label_name;
                        $columnName = Str::lower(str_replace([' ', '/'], ['_', '_else_'], $data->label_name));

                    @endphp
                    @if ($count % 2 == 0)
                        <div class="row" id={{ $columnName }}>
                    @endif
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-md-12">
                                {{ $labelName }}
                            </label>
                            <label class="col-md-12 pop-non-edt-val" id={{ $columnName }}>
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
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-12">
                        Charge Status
                    </label>
                    <label class="col-md-12 pop-non-edt-val" id="chart_status">
                    </label>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-12">
                        Error Category
                    </label>
                    <label class="col-md-12 pop-non-edt-val" id="qa_status_view"></label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-12">
                        Sub Category
                    </label>
                    <label class="col-md-12 pop-non-edt-val" id="qa_sub_status_view"></label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group row">
                    <label class="col-md-12" id="qa_classification_label">
                        Classification
                    </label>
                    <label class="col-md-12 pop-non-edt-val" id="qa_classification_view"></label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group row">
                    <label class="col-md-12" id="qa_category_label">
                        Category
                    </label>
                    <label class="col-md-12 pop-non-edt-val" id="qa_category_view"></label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group row">
                    <label class="col-md-12" id="qa_scope_label">
                        Scope
                    </label>
                    <label class="col-md-12 pop-non-edt-val" id="qa_scope_view"></label>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="modal-footer">

        <button class="btn btn-light-danger float-right" id="close_assign" tabindex="10" type="button"
            data-dismiss="modal">
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
    <div class="modal fade modal-second modal-left" id="myModal_sop" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                @if ($popUpHeader != null)
                    @php
                        $clientName = App\Http\Helper\Admin\Helpers::projectName($popUpHeader->project_id);
                        $sopDetails = App\Models\SopDoc::where('project_id', $popUpHeader->project_id)
                            ->where('sub_project_id', $popUpHeader->sub_project_id)
                            ->latest()
                            ->first('sop_path');
                        // $pdfName =  preg_replace('/[^A-Za-z0-9]/', '_',$clientName->project_name);
                    @endphp
                @endif
                <div class="modal-header" style="background-color: #139AB3;height: 84px">
                    <h5 class="modal-title" id="exampleModalLabel" style="color: #ffffff;">SOP</h5>
                    <a href={{ isset($sopDetails) && isset($sopDetails->sop_path) ? asset($sopDetails->sop_path) : '#' }}
                        target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                            class="bi bi-arrow-up-right-square" viewBox="0 0 16 16"
                            style="color: #ffffff; margin-left: 365px;">
                            <path fill-rule="evenodd"
                                d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.854 8.803a.5.5 0 1 1-.708-.707L9.243 6H6.475a.5.5 0 1 1 0-1h3.975a.5.5 0 0 1 .5.5v3.975a.5.5 0 1 1-1 0V6.707z" />
                        </svg>
                    </a>
                    <button type="button" class="close comment_close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <iframe
                        src={{ isset($sopDetails) && isset($sopDetails->sop_path) ? asset($sopDetails->sop_path) : '#' }}
                        style="width: 100%; height: 418px;" frameborder="0" type="application/pdf"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-danger" data-dismiss="modal">Close</button>
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
            var indvidualSearchFieldsCount = Object.keys(@json($projectColSearchFields)).length;
            var qaSubStatusList = @json($qaSubStatusListVal);
            var qaStatusList = @json($qaStatusList);
            var arStatusList = @json( $arStatusList);
            var arActionList = @json($arActionListVal);
            var qaClassification = @json($qaClassificationVal);
            var qaCategory = @json($qaCategoryVal);
            var qaScope = @json($qaScopeVal);
            const url = window.location.href;
            const startIndex = url.indexOf('projects_') + 'projects_'.length;
            const endIndex = url.indexOf('/', startIndex);
            const urlDynamicValue = url.substring(startIndex, endIndex);
            var d = new Date();
            var month = d.getMonth() + 1;
            var day = d.getDate();
            var date = (month < 10 ? '0' : '') + month + '-' +
                (day < 10 ? '0' : '') + day + '-' + d.getFullYear();
            var table = $("#client_completed_list").DataTable({
                processing: true,
                ordering: true,
                clientSide: true,
                lengthChange: false,
                searching: indvidualSearchFieldsCount > 0 ? false : true,
                paging: false,
                info: false,
                scrollCollapse: true,
                scrollX: true,
                "initComplete": function(settings, json) {
                    $('body').find('.dataTables_scrollBody').addClass("scrollbar");
                },
                language: {
                    "search": '',
                    "searchPlaceholder": "   Search",
                },
                buttons: [{
                    "extend": 'excel',
                    "text": `<span data-dismiss="modal" data-toggle="tooltip" data-placement="left" data-original-title="Export" style="font-size:13px"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" fill="currentColor" class="bi bi-box-arrow-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1z"/><path fill-rule="evenodd" d="M7.646.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 1.707V10.5a.5.5 0 0 1-1 0V1.707L5.354 3.854a.5.5 0 1 1-.708-.708z"/>
                             </svg>&nbsp;&nbsp;&nbsp;<span>Export</span></span>`,
                    "className": 'btn btn-primary-export text-white',
                    "title": 'PROCODE',
                    "filename": 'procode_qa_completed_' + date,
                    "exportOptions": {
                        "columns": ':not(.notexport)' // Exclude first two columns
                    }
                }],
                dom: "B<'row'<'col-md-12'f><'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>"
            })
            table.buttons().container()
                .appendTo('.outside');
            $('.dataTables_filter').addClass('pull-left');
            $(document).on('click', '#filter_clear', function(e) {
                window.location.href = baseUrl + 'qa_production/qa_projects_completed/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] +
                    "&child=" + getUrlVars()["child"];
            })
            var clientName = $('#clientName').val();
            var subProjectName = $('#subProjectName').val();
            $(document).on('click', '.clickable-view', function(e) {
                var record_id = $(this).closest('tr').find('#table_id').text();
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
                    url: "{{ url('qa_production/qa_client_view_details') }}",
                    method: 'POST',
                    data: {
                        record_id: record_id,
                        clientName: clientName,
                        subProjectName: subProjectName,
                    },
                    success: function(response) {
                        if (response.success == true) {

                            $('#myModal_view').modal('show');
                            console.log(response.clientData, headers, 'header');
                            handleClientData(response.clientData, headers);
                        } else {
                            $('#myModal_view').modal('hide');
                            js_notification('error', 'Something went wrong');
                        }
                    },
                });

                function handleClientData(clientData, headers) {
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
                            });
                        } else {console.log(header,'headers',value);
                            if (header === 'chart_status' && value.includes('QA_')) {
                                value = value.replace('QA_', '');
                                $('#title_status_view').text(value);
                            }
                            if (header == 'QA_status_code') {
                                var statusName = '';
                                $.each(qaStatusList, function(key, val) {
                                    if (value == key) {
                                        statusName = val;
                                    }
                                });
                                $('label[id="qa_status_view"]').text(statusName);
                            }
                            if (header == 'QA_sub_status_code') {
                                var subStatusName = '';
                                $.each(qaSubStatusList, function(key, val) {
                                    if (value == key) {
                                        subStatusName = val;
                                    }
                                });
                                $('label[id="qa_sub_status_view"]').text(subStatusName);
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
                            if (header == 'qa_classification') {
                                var qa_classification = '';
                                $.each(qaClassification, function(key, val) {      
                                    if (value == key) {
                                        qa_classification = val;
                                    }
                                });
                                if(qa_classification == '') {
                                    $('label[id="qa_classification_label"]').css('display','none');
                                } else {
                                    $('label[id="qa_classification_label"]').css('display','block');
                                }
                                $('label[id="qa_classification_view"]').text(qa_classification);
                            }
                            if (header == 'qa_category') {
                                var qa_category = '';
                                $.each(qaCategory, function(key, val) {      
                                    if (value == key) {
                                        qa_category = val;
                                    }
                                });
                                if(qa_category == '') {
                                    $('label[id="qa_category_label"]').css('display','none');
                                } else {
                                    $('label[id="qa_category_label"]').css('display','block');
                                }
                                $('label[id="qa_category_view"]').text(qa_category);
                            }
                            if (header == 'qa_scope') {
                                var qa_scope = '';
                                $.each(qaScope, function(key, val) {      
                                    if (value == key) {
                                        qa_scope = val;
                                    }
                                });
                                if(qa_scope == '') {
                                    $('label[id="qa_scope_label"]').css('display','none');
                                } else {
                                    $('label[id="qa_scope_label"]').css('display','block');
                                }
                                $('label[id="qa_scope_view"]').text(qa_scope);
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
                window.location.href = baseUrl + 'qa_production/qa_projects_assigned/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] +
                    "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.two', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_pending/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.three', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_hold/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.four', function() {
                window.location.href = "{{ url('#') }}";
            })
            $(document).on('click', '.five', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_unAssigned/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.six', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_duplicate/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.seven', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_auto_close/' + clientName +
                    '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.eight', function() {
                window.location.href = baseUrl + 'qa_production/qa_rebuttal/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })

            $(document).on('click', '#sop_click', function(e) {
                $('#myModal_sop').modal('show');
            });
            $('#myModal_sop').on('shown.bs.modal', function() {
                $('#myModal_view').addClass('modal-right');
            });

            $('#myModal_sop').on('hidden.bs.modal', function() {
                $('#myModal_view').removeClass('modal-right');
            });
        })
    </script>
@endpush
