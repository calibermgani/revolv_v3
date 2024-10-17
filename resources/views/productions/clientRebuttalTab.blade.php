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
                        <span class="project_header" style="margin-left: 4px !important;">Client Information</span>
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
                                    @endphp
                                @else
                                    @php
                                        $sopDetails = '';
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
                            <div class="wizard-step mb-0 seven" data-wizard-type="done">
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
                        <div class="wizard-step mb-0 four" data-wizard-type="done">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Completed</h6>
                                        @include('CountVar.countRectangle', ['count' => $completedCount])
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wizard-step mb-0 five" data-wizard-type="done">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Audit Rework</h6>
                                        @include('CountVar.countRectangle', ['count' => $reworkCount])
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
                            <div class="wizard-step mb-0 six" data-wizard-type="done">
                                <div class="wizard-wrapper py-2">
                                    <div class="wizard-label p-2 mt-2">
                                        <div class="wizard-title" style="display: flex; align-items: center;">
                                            <h6 style="margin-right: 5px;">Duplicate</h6>
                                            @include('CountVar.countRectangle', [
                                                'count' => $duplicateCount,
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="wizard-step mb-0 eight" data-wizard-type="done">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Non Workable</h6>
                                        @include('CountVar.countRectangle', [
                                            'count' => $arNonWorkableCount,
                                        ])
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wizard-step mb-0 nine" data-wizard-type="step">
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
                    </div>
                </div>
            </div>

            <div class="card card-custom custom-top-border">
                <div class="card-body py-0 px-7">
                    <input type="hidden" value={{ $clientName }} id="clientName">
                    <input type="hidden" value={{ $subProjectName }} id="subProjectName">
                    <div class="table-responsive pt-5 pb-5 clietnts_table">
                        <table class="table table-separate table-head-custom no-footer dtr-column "
                            id="client_rebuttal_list" data-order='[[ 0, "desc" ]]'>
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
                                @if (isset($rebuttalProjectDetails))
                                    @foreach ($rebuttalProjectDetails as $data)
                                    @php
                                            $arrayAttrributes = $data->getAttributes();
                                            $arrayAttrributes['aging']= null; 
                                            $arrayAttrributes['aging_range']= null;                                       
                                        @endphp
                                        <tr>
                                            <td>
                                                <button class="task-start clickable-view" title="View"><i
                                                        class="fa far fa-eye text-eye icon-circle1 mt-0"></i></button>
                                            </td>
                                            @foreach ($arrayAttrributes as $columnName => $columnValue)
                                                @php
                                                    $columnsToExclude = [
                                                        'QA_emp_id',
                                                        'ce_hold_reason',
                                                        'qa_hold_reason',
                                                        'qa_work_status',
                                                        'QA_required_sampling',
                                                        'QA_rework_comments',
                                                        'coder_rework_status',
                                                        'coder_rework_reason',
                                                        'coder_error_count',
                                                        'qa_error_count',
                                                        'tl_error_count',
                                                        'tl_comments',
                                                        'QA_followup_date',
                                                        'CE_status_code',
                                                        'CE_sub_status_code',
                                                        'CE_followup_date',
                                                        'coder_cpt_trends',
                                                        'coder_icd_trends',
                                                        'coder_modifiers',
                                                        'qa_cpt_trends',
                                                        'qa_icd_trends',
                                                        'qa_modifiers',
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
                                                                    if ($columnValue != null) {
                                                                        $statusCode = App\Http\Helper\Admin\Helpers::qaStatusById(
                                                                            $columnValue,
                                                                        );
                                                                    } else {
                                                                        $statusCode = '';
                                                                    }
                                                                @endphp
                                                                {{ $columnValue == null ? $columnValue : $statusCode['status_code'] }}
                                                            @elseif ($columnName == 'QA_sub_status_code')
                                                                @php
                                                                    if ($columnValue != null) {
                                                                        $subStatusCode = App\Http\Helper\Admin\Helpers::qaSubStatusById(
                                                                            $columnValue,
                                                                        );
                                                                    } else {
                                                                        $subStatusCode = '';
                                                                    }
                                                                @endphp
                                                                {{ $columnValue == null ? $columnValue : $subStatusCode['sub_status_code'] }}
                                                            @elseif ($columnName == 'aging')                                                                                  
                                                                {{ $agingCount }}
                                                            @elseif ($columnName == 'aging_range')
                                                                  {{ $agingRange }}  
                                                            @else
                                                                @if ($columnName == 'chart_status' && str_contains($columnValue, 'CE_'))
                                                                    {{ str_replace('CE_', '', $columnValue) }}
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
                </div>

            </div>
        </div>
        <div class="modal fade modal-first" id="myModal_view" tabindex="-1" role="dialog"
            aria-labelledby="myModalLabel" data-backdrop="static" aria-hidden="true">
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
                            <div class="row" style="height: auto;width:100%">
                                <div class="col-md-4">
                                    <div class="align-items-center" style="display: -webkit-box !important;">
                                        <div class="rounded-circle bg-white text-black mr-2"
                                            style="width: 50px; height: 50px; display: flex; justify-content: center; align-items: center;font-weight;bold">
                                            <span>{{ strtoupper(substr($clientName->project_name, 0, 1)) }}</span>
                                        </div>&nbsp;&nbsp;
                                        <div>
                                            <h6 class="modal-title mb-0" id="myModalLabel" style="color: #ffffff;">
                                                {{ ucfirst($clientName->aims_project_name) }}
                                            </h6>
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
                            </div>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-3" data-scroll="true" data-height="400">
                                    <h6 class="title-h6">Basic Information</h6>&nbsp;&nbsp;
                                    <input type="hidden" name="idValue">
                                    <input type="hidden" name="parentId">
                                    <input type="hidden" name="record_old_status">
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
                                                <div class="row">
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
                                        <hr>
                                        <h6 class="title-h6">QA</h6>&nbsp;&nbsp;
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="qa_status_label">
                                                        Error Category
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id="qa_status_view">
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="qa_sub_status_label">
                                                        Sub Category
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id="qa_sub_status_view">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                      
                                        <div class="row mt-4" id="reworkNotesDiv">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="qa_rework_comments_label">
                                                        Rework Notes
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id="qa_rework_comments_view">
                                                    </label>
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
                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-md-12 required" id="rework_status_label">
                                                            Rebuttal Status
                                                        </label>
                                                        <div class="col-md-10">
                                                            {!! Form::Select(
                                                                'ar_manager_rebuttal_status',
                                                                [
                                                                    '' => 'Select',
                                                                    'agree' => 'Agree',
                                                                    'dis_agree' => 'Disagree',
                                                                ],
                                                                null,
                                                                [
                                                                    'class' => 'form-control white-smoke  pop-non-edt-val ',
                                                                    'autocomplete' => 'none',
                                                                    'id' => 'ar_manager_rebuttal_status',
                                                                    'style' => 'cursor:pointer',
                                                                    'required',
                                                                ],
                                                            ) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-md-12 required" id="ar_manager_rebuttal_comments_label" style = 'display:none'>
                                                            Comments
                                                        </label>
                                                        <div class="col-md-10">
                                                            {!! Form::textarea('ar_manager_rebuttal_comments', null, [
                                                                'class' => 'text-black form-control',
                                                                'rows' => 3,
                                                                'id' => 'ar_manager_rebuttal_comments',
                                                                'style' => 'display:none',
                                                                'required',
                                                            ]) !!}

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-danger" id="close" data-dismiss="modal">Close</button>
                                @if (
                                    $loginEmpId == 'Admin' ||
                                    strpos($empDesignation, 'Manager') !== false ||
                                    strpos($empDesignation, 'VP') !== false ||
                                    strpos($empDesignation, 'Leader') !== false ||
                                    strpos($empDesignation, 'Team Lead') !== false ||
                                    strpos($empDesignation, 'CEO') !== false ||
                                    strpos($empDesignation, 'Vice') !== false)
                                          <button type="submit" class="btn1" id="project_rebuttal_save" style="margin-right: -2rem">Submit</button>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            @endif
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
        var start = moment().startOf('month');
        var end = moment().endOf('month');
        $('.date_range').attr("autocomplete", "off");
        $('.date_range').daterangepicker({
            showOn: 'both',
            startDate: start,
            endDate: end,
            showDropdowns: true,
            ranges: {}
        });
        $('.date_range').val('');
        $(document).ready(function() {
            var qaSubStatusList = @json($qaSubStatusListVal);
            var qaStatusList = @json($qaStatusList);
            var arStatusList = @json( $arStatusList);
            var arActionList = @json($arActionListVal);
            function getUrlParam(param) {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(param);
            }
            const url = window.location.href;
            const startIndex = url.indexOf('projects_') + 'projects_'.length;
            const endIndex = url.indexOf('/', startIndex);
            const urlDynamicValue = url.substring(startIndex, endIndex);
            var uniqueId = 0;
            var d = new Date();
            var month = d.getMonth() + 1;
            var day = d.getDate();
            var date = (month < 10 ? '0' : '') + month + '-' +
                (day < 10 ? '0' : '') + day + '-' + d.getFullYear();
            var table = $("#client_rebuttal_list").DataTable({
                processing: true,
                ordering: true,
                lengthChange: false,
                searching: true,
                pageLength: 20,
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
                    "filename": 'procode_rework_' + date,
                    "exportOptions": {
                        "columns": ':not(.notexport)'
                    }
                }],
                dom: "B<'row'<'col-md-12'f><'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>"
            })
            table.buttons().container()
                .appendTo('.outside');
            $('.dataTables_filter').addClass('pull-left');

            var clientName = $('#clientName').val();
            var subProjectName = $('#subProjectName').val();

            function subStatus(statusVal, value) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "GET",
                    url: "{{ url('qa_production/qa_sub_status_list') }}",
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
                        console.log(sla_options, 'sla_options');
                        $('select[name="QA_sub_status_code"]').html(sla_options);
                        if (value) {
                            $('select[name="QA_sub_status_code"]').val(value);
                        }
                    },
                    error: function(jqXHR, exception) {}
                });
            }
            $(document).on('change', '#qa_status', function() {
                var status_code_id = $(this).val();
                subStatus(status_code_id, '');
            });


            $(document).on('click', '.clickable-view', function(e) {
                $('#myModal_status').modal('hide');
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
                    url: "{{ url('client_view_details') }}",
                    method: 'POST',
                    data: {
                        record_id: record_id,
                        clientName: clientName,
                        subProjectName: subProjectName
                    },
                    success: function(response) {
                        if (response.success == true) {

                            $('#myModal_view').modal('show');
                            headers.push('QA_rework_comments');
                            headers.push('ar_manager_rebuttal_status');
                            headers.push('ar_manager_rebuttal_comments');
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
                                if (data !== '') {

                                    var circle = $('<span>').addClass('circle');
                                    var span = $('<span>').addClass('date-label').text(
                                        data);
                                    span.prepend(circle);
                                    formattedDatas.push(span);
                                }
                            });
                            console.log(value, 'test value', values, formattedDatas);
                            formattedDatas.forEach(function(span, index) {
                                console.log(span, 'span', header);
                                if (header == 'QA_rework_comments') {
                                    $('label[id="qa_rework_comments_view"]').append(span);
                                }
                                $('label[id="' + header + '"]').append(span);
                            });
                        } else {
                            if (header === 'chart_status' && value.includes('CE_')) {
                                value = value.replace('CE_', '');
                                $('#title_status_view').text(value);
                            } else if (header === 'chart_status') {
                                value = value;
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
                            if (header == 'QA_rework_comments') {
                                $('label[id="qa_rework_comments_view"]').text(value);
                                if (value !== '') {
                                    $('#reworkNotesDiv').css('display','block');
                                    $('#qa_rework_comments_label').css('display','block');
                                } else {
                                    $('#reworkNotesDiv').css('display','none');
                                    $('#qa_rework_comments_label').css('display','none');
                                }
                                
                            }
                            if (header == 'ar_manager_rebuttal_status') {
                                $('select[name="ar_manager_rebuttal_status"]').val(value);
                            }
                            if (header == 'ar_manager_rebuttal_comments') {
                                $('textarea[name="ar_manager_rebuttal_comments"]').val(value);
                                if (value !== null) {
                                    $('#ar_manager_rebuttal_comments_label').css('display',
                                    'block');
                                    $('#ar_manager_rebuttal_comments').css('display', 'block');
                                } else {
                                    $('#ar_manager_rebuttal_comments_label').css('display', 'none');
                                    $('#ar_manager_rebuttal_comments').css('display', 'none');
                                }
                            }
                            $('input[name="parentId"]').val(clientData['parent_id']);
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

            $(document).on('click', '#project_rebuttal_save', function(e) {
                e.preventDefault();
                var ar_manager_rebuttal_status = $('#ar_manager_rebuttal_status').val();
                var ar_manager_rebuttal_comments = $('#ar_manager_rebuttal_comments').val();
                var parentIdVal = $('input[name="parentId"]').val();
                var chargeStatus = $('#chart_status').text();
                if (ar_manager_rebuttal_status == '') {
                    $('#ar_manager_rebuttal_status').css('border-color', 'red');
                    return false;
                } else {
                    $('#ar_manager_rebuttal_status').css('border-color', '');
                }
                if (ar_manager_rebuttal_comments == '') {
                    $('#ar_manager_rebuttal_comments').css('border-color', 'red');
                    return false;
                } else {
                    $('#ar_manager_rebuttal_comments').css('border-color', '');
                }
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
                        KTApp.block('#client_rebuttal_list', {
                            overlayColor: '#000000',
                            state: 'danger',
                            opacity: 0.1,
                            message: 'Fetching...',
                        });
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            type: "GET",
                            url: "{{ url('ar_rebuttal_update') }}",
                            data: {
                                clientName: clientName,
                                subProjectName: subProjectName,
                                ar_manager_rebuttal_status: ar_manager_rebuttal_status,
                                ar_manager_rebuttal_comments: ar_manager_rebuttal_comments,
                                parentId: parentIdVal,
                                chargeStatus: chargeStatus
                            },
                            success: function(res) {
                                if (res.success == true) {
                                    location.reload();
                                    KTApp.unblock('#client_rebuttal_list');
                                }
                            }
                        });

                    } else {
                        //   location.reload();
                    }
                });


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
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.three', function() {
                window.location.href = baseUrl + 'projects_hold/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] +
                    "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.four', function() {
                window.location.href = baseUrl + 'projects_completed/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.five', function() {
                window.location.href = baseUrl + 'projects_Revoke/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.six', function() {
                window.location.href = baseUrl + 'projects_duplicate/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.seven', function() {
                window.location.href = baseUrl + 'projects_unassigned/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.eight', function() {
                window.location.href = baseUrl + 'projects_non_workable/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.nine', function() {
                window.location.href = "{{ url('#') }}";
            })

            $(document).on('change', '#chart_status', function() {
                var claimStatus = $(this).val();
                if (claimStatus == "CE_Hold") {
                    $('#ce_hold_reason').css('display', 'block');
                    $('#ce_hold_reason_label').css('display', 'block');
                } else {
                    $('#ce_hold_reason').css('display', 'none');
                    $('#ce_hold_reason_label').css('display', 'none');
                    $('#ce_hold_reason').css('border-color', '');
                    $('#ce_hold_reason').val('');
                }
            })
            $(document).on('change', '#ar_manager_rebuttal_status', function() {
                var ar_manager_rebuttal_status = $(this).val();
                if (ar_manager_rebuttal_status) {
                    $('#ar_manager_rebuttal_comments').css('display', 'block');
                    $('#ar_manager_rebuttal_comments_label').css('display', 'block');
                } else {
                    $('#ar_manager_rebuttal_comments').css('display', 'none');
                    $('#ar_manager_rebuttal_comments_label').css('display', 'none');
                    $('#ar_manager_rebuttal_comments').css('border-color', '');
                    $('#ar_manager_rebuttal_comments').val('');
                }
            })
        })
    </script>
@endpush
