@extends('layouts.app3')
@section('content')

                <div class="card card-custom custom-card">
                    <div class="card-body p-0">
                        @php
                             $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                             $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
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
                                                            // $pdfName =  preg_replace('/[^A-Za-z0-9]/', '_',$clientNameDetails->project_name);
                                                    @endphp
                                                    @else
                                                    @php
                                                        $sopDetails = '';
                                                        // $pdfName = '';
                                                    @endphp
                                                @endif
                                            <a href= {{ isset($sopDetails) && isset($sopDetails->sop_path) ? asset($sopDetails->sop_path) : '#' }} target="_blank">
                                            <button type="button" class="btn text-white mr-3" style="background-color:#139AB3">SOP</button>
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
                                    <div class="wizard-step mb-0 two" data-wizard-type="step">
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
                                    {{-- @if ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)
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
                            <div class="card-body py-0 px-7">
                                <input type="hidden" value={{ $clientName }} id="clientName">
                                <input type="hidden" value={{ $subProjectName }} id="subProjectName">
                                <div class="table-responsive pt-5 pb-5 clietnts_table">
                                    <table class="table table-separate table-head-custom no-footer dtr-column "
                                        id="client_pending_list" data-order='[[ 0, "desc" ]]'>
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
                                            @if (isset($pendingProjectDetails))
                                                @foreach ($pendingProjectDetails as $data)
                                                    <tr
                                                        style="{{ $data->invoke_date == 125 ? 'background-color: #f77a7a;' : '' }}">
                                                        <td>
                                                               @if (($loginEmpId !== "Admin" || strpos($empDesignation, 'Manager') !== true || strpos($empDesignation, 'VP') !== true || strpos($empDesignation, 'Leader') !== true || strpos($empDesignation, 'Team Lead') !== true || strpos($empDesignation, 'CEO') !== true || strpos($empDesignation, 'Vice') !== true) && $loginEmpId != $data->QA_emp_id)
                                                                @else
                                                                @if (empty($existingCallerChartsWorkLogs))
                                                                    <button class="task-start clickable-row start"
                                                                        title="Start"><i class="fa fa-play-circle icon-circle1 mt-0" aria-hidden="true" style="color:#ffffff"></i></button>
                                                                @elseif(in_array($data->id, $existingCallerChartsWorkLogs))
                                                                    <button class="task-start clickable-row start"
                                                                        title="Start"><i class="fa fa-play-circle icon-circle1 mt-0" aria-hidden="true" style="color:#ffffff"></i></button>
                                                                @endif
                                                            @endif
                                                                    <button class="task-start clickable-view"
                                                                    title="View"><i
                                                                    class="fa far fa-eye text-eye icon-circle1 mt-0"></i></button>
                                                        </td>
                                                        @foreach ($data->getAttributes() as $columnName => $columnValue)
                                                            @php
                                                                $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                                                                'coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                                                                'created_at', 'updated_at', 'deleted_at'];
                                                            @endphp
                                                            @if (!in_array($columnName, $columnsToExclude))
                                                                @if ($columnName != 'id')
                                                                    <td style="max-width: 300px;
                                                                    white-space: normal;">
                                                                        @if (str_contains($columnValue, '-') && strtotime($columnValue))
                                                                            {{ date('m/d/Y', strtotime($columnValue)) }}
                                                                        @else
                                                                            @if ($columnName == 'chart_status' && str_contains($columnValue, 'QA_'))
                                                                                {{ str_replace('QA_', '', $columnValue) }}
                                                                            @elseif ($columnName == 'QA_status_code')
                                                                                @php $statusCode = App\Http\Helper\Admin\Helpers::qaStatusById($columnValue);@endphp
                                                                                {{ $statusCode['status_code'] }}
                                                                            @elseif ($columnName == 'QA_sub_status_code')
                                                                                @php $subStatusCode = App\Http\Helper\Admin\Helpers::qaSubStatusById($columnValue);@endphp
                                                                                {{ $subStatusCode['sub_status_code'] }}
                                                                            @else
                                                                                {{ $columnValue }}
                                                                            @endif
                                                                        @endif
                                                                    </td>
                                                                @else
                                                                    <td style="display:none;max-width: 300px;
                                                                    white-space: normal;" id="table_id">
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
                                            <div class="row" style="height: auto;width:100%">
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

                                                {{-- <div class="col-md-8 justify-content-end" style="display: -webkit-box !important;">
                                                    <button type="button" class="btn btn-black-white mr-3 sop_click" id="sop_click" style="padding: 0.35rem 1rem;">SOP</button>
                                                </div> --}}
                                        </div>
                                        </div>
                                        {!! Form::open([
                                            'url' =>
                                                url('qa_production/qa_project_update/' . $projectName . '/' . $subProjectName) .
                                                '?parent=' .
                                                request()->parent .
                                                '&child=' .
                                                request()->child,
                                            'class' => 'form',
                                            'id' => 'pendingFormConfiguration',
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
                                                    <h6 class="title-h6">Coder</h6>&nbsp;&nbsp;
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
                                                                    class="col-md-12 {{ $data->field_type_2 == 'mandatory' ? 'required' : '' }}">
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
                                                                                $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                                ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? '' : 'readonly'
                                                                            ]) !!}
                                                                            {{-- @if($columnName == "am_cpt" || $columnName == "am_icd") 
                                                                                {!! Form::$inputType($columnName.'_hidden' . '[]', null, [
                                                                                'class' => 'form-control ' . $columnName.'_hidden' . ' white-smoke pop-non-edt-val',
                                                                                'autocomplete' => 'none',
                                                                                'style' => 'cursor:pointer; display:none;',
                                                                                'rows' => 3,
                                                                                'id' => $columnName.'_hidden',
                                                                                $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                                ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? '' : 'readonly'
                                                                            ]) !!}
                                                                           @endif --}}
                                                                        @else
                                                                            {!! Form::text($columnName . '[]', null, [
                                                                                'class' => 'form-control date_range ' . $columnName . ' white-smoke pop-non-edt-val',
                                                                                'autocomplete' => 'none',
                                                                                'style' => 'cursor:pointer',
                                                                                'id' => $columnName,
                                                                                $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                                ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? '' : 'readonly'
                                                                            ]) !!}
                                                                        @endif
                                                                    @else
                                                                        @if ($inputType == 'select')
                                                                            {!! Form::$inputType($columnName . '[]', ['' => '-- Select --'] + $associativeOptions, null, [
                                                                                'class' => 'form-control ' . $columnName . ' white-smoke pop-non-edt-val',
                                                                                'autocomplete' => 'none',
                                                                                'style' => 'cursor:pointer;' . (($data->input_type_editable == 2 || $data->input_type_editable == 3) ? '' : 'pointer-events: none;'),
                                                                                'id' => $columnName,
                                                                                $data->field_type_2 == 'mandatory' ? 'required' : '',
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
                                                                                                    $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                                                    'onclick' => $data->input_type_editable != 2 && $data->input_type_editable != 3 ? 'return false;' : '',
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
                                                                                                    $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                                                    'disabled' => $data->input_type_editable != 2 && $data->input_type_editable != 3
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
                                                    {{-- <div class="row mt-4 trends_div">
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <label class="col-md-12">
                                                                    Coder Trends
                                                                </label>
                                                                <div class="col-md-11">
                                                                    {!!Form::textarea('annex_coder_trends',  null, ['class' => 'text-black form-control white-smoke annex_coder_trends','rows' => 6,'readonly']) !!}
        
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <label class="col-md-12">
                                                                    QA Trends
                                                                </label>
                                                                <div class="col-md-11">
                                                                    {!!Form::textarea('annex_qa_trends',  null, ['class' => 'text-black form-control white-smoke annex_qa_trends','rows' => 6,'readonly']) !!}
        
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> --}}
                                                    <hr>
                                                    <h6 class="title-h6">QA</h6>&nbsp;&nbsp;
                                                @if (count($popupQAEditableFields) > 0)
                                                    @php $count = 0; @endphp
                                                    @foreach ($popupQAEditableFields as $key => $data)
                                                            @php
                                                                $labelName = $data->label_name;
                                                                $columnName = Str::lower(str_replace([' ', '/'], ['_', '_else_'], $data->label_name));
                                                                $inputType = $data->input_type;
                                                                $options = $data->options_name != null ? explode(',', $data->options_name) : null;
                                                                $associativeOptions = [];
                                                                if ($options !== null) {
                                                                    foreach ($options as $option) {
                                                                        $associativeOptions[$option] = $option;
                                                                    }
                                                                }
                                                            @endphp
                                                            @if ($count % 2 == 0)
                                                                <div class="row" id={{ $columnName }}>
                                                            @endif
                                                                <div class="col-md-6 dynamic-field">
                                                                <div class="form-group row row_mar_bm">
                                                                    <label class="col-md-12 {{ $data->field_type_2 == 'mandatory' ? 'required' : '' }}">
                                                                        {{ $labelName }}
                                                                    </label>
                                                                    <div class="col-md-10">
                                                                        @if ($options == null)
                                                                            @if ($inputType != 'date_range')
                                                                                {!! Form::$inputType($columnName . '[]', null, [
                                                                                    'class' => 'form-control ' . $columnName . ' white-smoke pop-non-edt-val exclude',
                                                                                    'autocomplete' => 'none',
                                                                                    'style' => 'cursor:pointer',
                                                                                    'rows' => 3,
                                                                                    'id' => $columnName,
                                                                                    $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                                ]) !!}
                                                                            @else
                                                                                {!! Form::text($columnName . '[]', null, [
                                                                                    'class' => 'form-control date_range daterange_' . $columnName . ' white-smoke pop-non-edt-val exclude',
                                                                                    'autocomplete' => 'none',
                                                                                    'style' => 'cursor:pointer',
                                                                                    'id' => 'date_range',
                                                                                    $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                                ]) !!}
                                                                            @endif
                                                                        @else
                                                                            @if ($inputType == 'select')
                                                                                {!! Form::$inputType($columnName . '[]', ['' => '-- Select --'] + $associativeOptions, null, [
                                                                                    'class' => 'form-control ' . $columnName . ' white-smoke pop-non-edt-val exclude',
                                                                                    'autocomplete' => 'none',
                                                                                    'style' => 'cursor:pointer',
                                                                                    'id' => $columnName,
                                                                                    $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                                ]) !!}
                                                                            @elseif ($inputType == 'checkbox')
                                                                                <p id="check_p1" style="display:none;color:red; margin-left: 3px;">
                                                                                    Checkbox
                                                                                    is not checked</p>
                                                                                <div class="form-group row">
                                                                                    @for ($i = 0; $i < count($options); $i++)
                                                                                        <div class="col-md-6">
                                                                                            <div class="checkbox-inline mt-2">
                                                                                                <label class="checkbox pop-non-edt-val"
                                                                                                    style="word-break: break-all;">
                                                                                                    {!! Form::$inputType($columnName . '[]', $options[$i], false, [
                                                                                                        'class' => 'exclude '.$columnName,
                                                                                                        'id' => $columnName,
                                                                                                        $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                                                    ]) !!}{{ $options[$i] }}
                                                                                                    <span></span>
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endfor
                                                                                </div>
                                                                            @elseif ($inputType == 'radio')
                                                                                <p id="radio_p1" style="display: none; color: red; margin-left: 3px;">
                                                                                    Radio
                                                                                    is not selected</p>
                                                                                <div class="form-group row">
                                                                                    @for ($i = 0; $i < count($options); $i++)
                                                                                        <div class="col-md-6">
                                                                                            <div class="radio-inline mt-2">
                                                                                                <label class="radio pop-non-edt-val"
                                                                                                    style="word-break: break-all;">
                                                                                                    {!! Form::$inputType($columnName, $options[$i], false, [
                                                                                                        'class' => $columnName.' exclude',
                                                                                                        'id' => $columnName,
                                                                                                        $data->field_type_2 == 'mandatory' ? 'required' : '',
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
                                                                            <i class="fa fa-plus add_more exclude" id="add_more_{{ $columnName }}"
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
                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <input type="hidden" name="invoke_date">
                                                    <input type="hidden" name="QA_emp_id">
                                                    <div class="form-group row">
                                                        <label class="col-md-12 required">
                                                            Charge Status
                                                        </label>
                                                        <div class="col-md-10">
                                                            {!! Form::Select(
                                                                'chart_status',
                                                                [
                                                                    '' => '--Select--',
                                                                    'QA_Inprocess' => 'Inprocess',
                                                                    'QA_Pending' => 'Pending',
                                                                    'QA_Completed' => 'Completed',
                                                                    'QA_Hold' => 'Hold',
                                                                    'Revoke' =>'Revoke'
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
                                                        <label class="col-md-12 required" id="qa_hold_reason_label" style ="display:none">
                                                            Hold Reason
                                                        </label>
                                                        <div class="col-md-10">
                                                            {!! Form::textarea('qa_hold_reason', null, [
                                                                'class' => 'text-black form-control',
                                                                'rows' => 3,
                                                                'id' => 'qa_hold_reason_editable',
                                                                'style' => 'display:none',
                                                            ]) !!}

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <input type="hidden" name="invoke_date">
                                                    <input type="hidden" name="QA_emp_id">
                                                    <div class="form-group row">
                                                        <label class="col-md-12 required">
                                                            Error Category
                                                        </label>
                                                        @php $qaStatusList = App\Http\Helper\Admin\Helpers::qaStatusList(); @endphp
                                                        <div class="col-md-10">
                                                            <input type="hidden" id="status_val">
                                                            {!! Form::Select(
                                                                'QA_status_code',
                                                                $qaStatusList,
                                                                null,
                                                                [
                                                                    'class' => 'form-control white-smoke  kt_select2_qa_status pop-non-edt-val ',
                                                                    'autocomplete' => 'none',
                                                                    'id' => 'qa_status',
                                                                    'style' => 'cursor:pointer',
                                                                ],
                                                            ) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-md-12 required">
                                                             Sub Category
                                                        </label>
                                                        @php
                                                             $qaSubStatusList = [];
                                                        @endphp
                                                        <div class="col-md-10">
                                                            {!! Form::Select(
                                                                'QA_sub_status_code',
                                                                $qaSubStatusList,
                                                                null,
                                                                [
                                                                    'class' => 'form-control white-smoke  kt_select2_qa_sub_status pop-non-edt-val ',
                                                                    'autocomplete' => 'none',
                                                                    'id' => 'qa_sub_status',
                                                                    'style' => 'cursor:pointer',
                                                                ],
                                                            ) !!}

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="col-md-12" id="QA_rework_comments_label">
                                                          Notes
                                                        </label>
                                                        <div class="col-md-10">
                                                            {!! Form::textarea('QA_rework_comments',  null, ['class' => 'text-black form-control QA_rework_comments','rows' => 6,'id' => 'QA_rework_comments','readonly']) !!}

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-md-12" style="display:none" id="coder_rework_status_label">
                                                           Coder Status
                                                        </label>
                                                        <div class="col-md-10">
                                                            <label class="col-md-12 pop-non-edt-val coder_rework_status" id="coder_rework_status" style="display:none">
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-md-12" id="coder_rework_reason_label" style="display:none">
                                                           Coder Comments
                                                        </label>
                                                        <div class="col-md-10">
                                                            <label class="col-md-12 pop-non-edt-val coder_rework_reason" id="coder_rework_reason" style="display:none">
                                                            </label>
                                                            {{-- {!! Form::textarea('coder_rework_reason',  null, ['class' => 'text-black form-control coder_rework_reason','rows' => 3,'id' => 'rework_reason']) !!} --}}

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer" style="justify-content: space-between;">


                                                <p class="timer_1" aria-haspopup="true" aria-expanded="false" data-toggle="modal"
                                                    data-target="#exampleModalCustomScrollable" style="margin-left: -2rem">

                                                    <span title="Total hours">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="22"
                                                            fill="currentColor" class="bi bi-stopwatch" viewBox="0 0 16 16">
                                                            <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5z" />
                                                            <path
                                                                d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64l.012-.013.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5M8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3" />
                                                        </svg>
                                                    </span><span id="elapsedTime" class="timer_2"></span>
                                                </p>

                                                <button type="submit" class="btn1" id="project_pending_save" style="margin-right: -2rem">Submit</button>
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
                                        $subProjectName = '';
                                    }

                                @endphp
                                    <div class="modal-content" style="margin-top: 7rem">
                                        <div class="modal-header" style="background-color: #139AB3;height: 84px">
                                            <div class="row" style="height: auto;width:100%">
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

                                                {{-- <div class="col-md-8 justify-content-end" style="display: -webkit-box !important;">
                                                    <button type="button" class="btn btn-black-white mr-3 sop_click" id="sop_click" style="padding: 0.35rem 1rem;">SOP</button>
                                                </div> --}}
                                            </div>
                                        </div>

                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-3" data-scroll="true" data-height="400">
                                                    <h6 class="title-h6">Basic Information</h6>&nbsp;&nbsp;
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
                                                    <h6 class="title-h6">Coder</h6>&nbsp;&nbsp;
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
                                                    <hr>
                                                    <h6 class="title-h6">QA</h6>&nbsp;&nbsp;
                                                    @if (count($popupQAEditableFields) > 0)
                                                        @php $count = 0; @endphp
                                                        @foreach ($popupQAEditableFields as $key => $data)
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
                                                         <div class="row mt-4">
                                                                <div class="col-md-6">
                                                                    <div class="form-group row">
                                                                            <label class="col-md-12">
                                                                                Charge Status
                                                                            </label>
                                                                            <label class="col-md-12 pop-non-edt-val"
                                                                            id="chart_status">
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-md-12 required" id="qa_hold_reason_view_label" style ="display:none">
                                                                            Hold Reason
                                                                        </label>
                                                                            <label class="col-md-12 pop-non-edt-val" id="qa_hold_reason" style = 'display:none'> </label>
                                                                    </div>
                                                                </div>
                                                         </div>

                                                        <div class="row mt-4">
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <label class="col-md-12" id="qa_status_label">
                                                                        Error Category
                                                                    </label>
                                                                    <label class="col-md-12 pop-non-edt-val"
                                                                    id="qa_status_view"></label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <label class="col-md-12">
                                                                         Sub Category
                                                                    </label>
                                                                    <label class="col-md-12 pop-non-edt-val"
                                                                    id="qa_sub_status_view"></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr style="display:none" id="hr_view">
                                                        <div class="row mt-4">
                                                            <div class="col-md-12">
                                                                <div class="form-group row">
                                                                    <label class="col-md-12" id="QA_rework_comments_label_view" style="display:none">
                                                                      Notes
                                                                    </label>
                                                                    <div class="col-md-10">
                                                                         <label class="col-md-12 pop-non-edt-val coder_rework_status" id="QA_rework_comments_view" style="display:none">
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-4">
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <label class="col-md-12" style="display:none" id="coder_rework_status_label_view">
                                                                       Coder Status
                                                                    </label>
                                                                    <div class="col-md-10">
                                                                        <label class="col-md-12 pop-non-edt-val coder_rework_status" id="coder_rework_status_view" style="display:none">
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group row">
                                                                    <label class="col-md-12" id="coder_rework_reason_label_view" style="display:none">
                                                                       Coder Comments
                                                                    </label>
                                                                    <div class="col-md-10">
                                                                        <label class="col-md-12 pop-non-edt-val coder_rework_reason" id="coder_rework_reason_view" style="display:none">
                                                                        </label>
                                                                    </div>
                                                                </div>
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
                                                // $pdfName =  preg_replace('/[^A-Za-z0-9]/', '_',$clientName->project_name);
                                        @endphp
                                    @endif
                                <div class="modal-header" style="background-color: #139AB3;height: 84px">
                                    <h5 class="modal-title" id="exampleModalLabel" style="color: #ffffff;" >SOP</h5>
                                        <a href= {{ isset($sopDetails) && isset($sopDetails->sop_path) ? asset($sopDetails->sop_path) : '#' }} target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-arrow-up-right-square" viewBox="0 0 16 16" style="color: #ffffff; margin-left: 365px;">
                                                <path fill-rule="evenodd" d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.854 8.803a.5.5 0 1 1-.708-.707L9.243 6H6.475a.5.5 0 1 1 0-1h3.975a.5.5 0 0 1 .5.5v3.975a.5.5 0 1 1-1 0V6.707z"/>
                                            </svg>
                                        </a>
                                    <button type="button" class="close comment_close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <iframe src={{ isset($sopDetails) && isset($sopDetails->sop_path) ? asset($sopDetails->sop_path) : '#' }} style="width: 100%; height: 418px;" frameborder="0" type="application/pdf"></iframe>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-light-danger" data-dismiss="modal">Close</button>
                                </div>
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
        var startTime_db;
        $(document).ready(function() {
            $('.cpt').attr('readonly', true);
            $('.icd').attr('readonly', true);
            var qaSubStatusList = @json($qaSubStatusListVal);
            var qaStatusList = @json( $qaStatusList);
            function getUrlParam(param) {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(param);
            }
            const url = window.location.href;
            const startIndex = url.indexOf('projects_') + 'projects_'.length;
            const endIndex = url.indexOf('/', startIndex);
            const urlDynamicValue = url.substring(startIndex, endIndex);

            var uniqueId = 0;
            $('.modal-body').on('click', '.add_more', function() {
                var addBtnClasses = $(this).attr('class');
                var btnLastClass = '';
                if (addBtnClasses) {
                    var classArray = addBtnClasses.split(' ');
                    var btnLastClass = classArray[classArray.length - 1];
                }
                btnLastClass = btnLastClass == 'exclude' ? btnLastClass : 'include';
                var ids = [];
                clumnClassName = $(this).attr('id').replace(/^add_more_/, '');
                $('.' + clumnClassName).each(function() {
                    ids.push($(this).attr('id'));
                });
                var lastElement = ids[ids.length - 1];
                var lastId = lastElement.replace(new RegExp('^' + clumnClassName), '');
                if (lastId) {
                    uniqueId=lastId;
                }
                uniqueId++;
                var labelName =$('.'+clumnClassName).closest('.row_mar_bm').find('.add_labelName').val();
                var columnName = $('.'+clumnClassName).closest('.row_mar_bm').find('.add_columnName').val();
                var inputType = $('.'+clumnClassName).closest('.row_mar_bm').find('.add_inputtype').val();
                var addMandatory = $('.'+clumnClassName).closest('.row_mar_bm').find('.add_mandatory').val();
                var optionsJson = $('.'+clumnClassName).closest('.row_mar_bm').find('.add_options').val();
                var optionsObject = optionsJson ? JSON.parse(optionsJson) : null;
                var optionsArray = optionsObject ? Object.values(optionsObject) : null;

                var newElementId = 'dynamicElement_' + clumnClassName + uniqueId;
                var newElement;
                if (optionsArray == null) {
                    if (inputType !== 'date_range') {
                        if (inputType == 'textarea') {
                            newElement = '<textarea name="' + columnName +
                                '[]"  class="form-control ' + columnName + ' '+ btnLastClass +' white-smoke pop-non-edt-val mt-0" rows="3" id="' +
                                columnName +
                                uniqueId +
                                '" '+ addMandatory +'></textarea>';

                        } else {
                            newElement = '<input type="' + inputType + '" name="' + columnName +
                                '[]"  class="form-control ' + columnName + ' '+ btnLastClass +' white-smoke pop-non-edt-val "  id="' +
                                columnName +
                                uniqueId +
                                '" '+ addMandatory +'>';
                        }
                    } else {
                        newElement = '<input type="text" name="' + columnName +
                            '[]" class="form-control date_range ' + columnName +' '+ btnLastClass +
                            ' white-smoke pop-non-edt-val"  style="cursor:pointer" autocomplete="none" id="' +
                            columnName +
                            uniqueId +
                            '" '+ addMandatory +'>';
                    }
                } else if (inputType === 'select') {

                    newElement = '<select name="' + columnName + '[]"  class="form-control ' +
                        columnName + ' '+ btnLastClass +' white-smoke pop-non-edt-val" id="' +
                        columnName +
                        uniqueId +
                        '" '+ addMandatory +'>';

                    optionsArray.unshift('-- Select --');
                    optionsArray.forEach(function(option) {
                        newElement += option != '-- Select --' ? '<option value="' + option + '">' +
                            option + '</option>' : '<option value="">' + option + '</option>';
                    });
                    newElement += '</select>';
                } else if (inputType === 'checkbox' && Array.isArray(optionsArray)) {
                    newElement = '<div class="form-group row">';

                    optionsArray.forEach(function(option) {
                        newElement +=
                            '<div class="col-md-6">' +
                            '<div class="checkbox-inline mt-2">' +
                            '<label class="checkbox pop-non-edt-val" style="word-break: break-all;" ' +
                            addMandatory + '>' +
                            '<input type="checkbox" name="' + columnName + '[]" value="' + option +
                            '" id="' +
                            columnName +
                            uniqueId +
                            '" class="' +
                            columnName +' '+ btnLastClass +
                            '" '+ addMandatory +'>' + option +
                            '<span></span>' +
                            '</label>' +
                            '</div>' +
                            '</div>';
                    });

                    newElement += '</div>';
                } else if (inputType === 'radio' && Array.isArray(optionsArray)) {
                    newElement = '<div class="form-group row">';
                    optionsArray.forEach(function(option) {
                        newElement +=
                            '<div class="col-md-6">' +
                            '<div class="radio-inline mt-2">' +
                            '<label class="radio pop-non-edt-val" style="word-break: break-all;" ' + addMandatory +
                            '>' +
                            '<input type="radio" name="' + columnName + '_' + uniqueId +
                            '" value="' + option + '" class="' + columnName +' '+ btnLastClass + '" id="' +
                            columnName +
                            uniqueId +
                            '"  '+ addMandatory +'>' + option +
                            '<span></span>' +
                            '</label>' +
                            '</div>' +
                            '</div>';
                    });

                    newElement += '</div>';
                }

                var plusButton = '<i class="fa fa-plus add_more" id="' +'add_more_'+columnName +'"></i>';
                var newRow = '<div class="row mt-6" id="' + newElementId + '">' +
                    '<div class="col-md-10">' + newElement + '</div>' +
                    '<div  class="col-md-1 col-form-label text-lg-right pt-0 pb-4" style="margin-left: -1.3rem;">' +
                        plusButton +
                    '</div><div></div>' +
                    '</div>';
                var modalBody = $('.'+clumnClassName).closest('.modal-content').find('.modal-body');


                $(this).closest('.col-md-6').append(newRow);
                     elementToRemove = 'add_more_'+clumnClassName;
                                $('#'+elementToRemove).remove();
                                uniqueId = uniqueId-1;
                                removeId = uniqueId == 0 ? clumnClassName : clumnClassName+ uniqueId;
                               if(uniqueId > 0) {
                                  $('#'+lastElement).closest('.col-md-10').next('.col-md-1').append('<i class="fa fa-minus minus_button remove_more" id="'+removeId +'"></i>');
                                }


                if (inputType === 'date_range') {
                    var newDateRangePicker = modalBody.find('#' + newElementId).find('.date_range');
                    newDateRangePicker.daterangepicker({
                        showOn: 'both',
                        startDate: start,
                        endDate: end,
                        showDropdowns: true,
                        ranges: {}
                    }).attr("autocomplete", "off");
                    newDateRangePicker.val('');
                }
            });

            $(document).on('click', '.remove_more', function() {
                var uniqueId = $(this).attr('id');
                var elementId = 'dynamicElement_' + uniqueId;
            });

                var d = new Date();
                var month = d.getMonth() + 1;
                var day = d.getDate();
                var date = (month < 10 ? '0' : '') + month + '-' +
                    (day < 10 ? '0' : '') + day + '-' + d.getFullYear();

            var table = $("#client_pending_list").DataTable({
                // processing: true,
                // lengthChange: false,
                // searching: true,
                // pageLength: 20,
                // scrollCollapse: true,
                // scrollX: true,
                // columnDefs: [{
                //     targets: [4, 5, 6],
                //     visible: false
                // }],
                // dom: '<"top"lfB>rt<"bottom"ip><"clear">',
                // buttons: [{
                //     extend: 'colvis',
                //     className: 'btn-colvis',
                //     text: 'Column Visibility'
                // }]
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
                    "filename": 'procode_qa_pending_'+date,
                    "exportOptions": {
                        "columns": ':not(.notexport)'// Exclude first two columns
                    }
                }],
                dom: "B<'row'<'col-md-12'f><'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>"
            })
            table.buttons().container()
                .appendTo('.outside');
                $('.dataTables_filter').addClass('pull-left');
                var clientName = $('#clientName').val();
                var subProjectName = $('#subProjectName').val();
            $(document).on('click', '.clickable-row', function(e) {
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
                        url: "{{ url('qa_production/qa_client_completed_datas_details') }}",
                        method: 'POST',
                        data: {
                            record_id: record_id,
                            clientName: clientName,
                            subProjectName: subProjectName,
                            urlDynamicValue: urlDynamicValue
                        },
                        success: function(response) {
                            if(lastClass == 'start'){
                                if (response.success == true) {
                                    $('#myModal_status').modal('show');
                                    startTime_db = response.startTimeVal;
                                    headers.push('QA_rework_comments');
                                    headers.push('coder_rework_status');
                                    headers.push('coder_rework_reason');
                                    handleClientPendData(response.clientData,headers);
                                } else {
                                    $('#myModal_status').modal('hide');
                                    js_notification('error', 'Something went wrong');
                                }
                            }
                        },
                    });
                    function handleClientPendData(clientData,headers) {

                        $.each(headers, function(index, header) {
                            value = clientData[header];
                            // if (header == 'annex_coder_trends') {
                            //     if (/_el_/.test(value)) {
                            //         var commentsValues = value.split('_el_');
                            //         var commentsText = commentsValues.join('\n');
                            //         $('textarea[name="annex_coder_trends"]').val(commentsText);
                            //     } else {
                            //         $('textarea[name="annex_coder_trends"]').val(value);
                            //     }
                            // }
                            // if (header == 'annex_qa_trends') {
                            //         if (/_el_/.test(value)) {
                            //             var commentsValues = value.split('_el_');
                            //             var commentsText = commentsValues.join('\n');
                            //             $('textarea[name="annex_qa_trends"]').val(commentsText);
                            //         } else {
                            //             $('textarea[name="annex_qa_trends"]').val(value);
                            //         }
                            // }
                            if (header == 'QA_rework_comments') {
                                if (/_el_/.test(value)) {
                                    var commentsValues = value.split('_el_');
                                    var commentsText = commentsValues.join('\n');
                                    $('textarea[name="QA_rework_comments"]').val(commentsText);
                                } else {
                                    $('textarea[name="QA_rework_comments"]').val(value);
                                }
                           }
                            $('label[id="' + header + '"]').html("");
                            $('input[name="' + header + '[]"]').html("");
                            if (/_el_/.test(value)) {
                                elementToRemove = 'add_more_'+header;
                                var multBtnClasses =  $('#'+elementToRemove).attr('class');
                                var multBtnLastClass = '';
                                if (multBtnClasses && multBtnClasses !== undefined) {
                                    var classArray = multBtnClasses.split(' ');
                                    var multBtnLastClass = classArray[classArray.length - 1];
                                }
                                multBtnLastClass = multBtnLastClass == 'exclude' ? multBtnLastClass : 'include';
                                $('#'+elementToRemove).remove();     var values = value.split('_el_');
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
                                }else if($('input[name="' + header + '"][type="radio"]').length > 0) {

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
                                                    class: 'form-control ' + header + ' '+multBtnLastClass+ ' white-smoke pop-non-edt-val',
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
                                                    inputType =  '<textarea name="' + header + '[]" '+addMandatory+' class="form-control ' + header + ' '+multBtnLastClass+' white-smoke pop-non-edt-val mt-0" rows="3" id="' + header + i + '">' + values[i] + '</textarea>';
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
                                                                '<input type="checkbox" name="' + header + '[]" value="' + option + '" '+addMandatory+' class="'+header +' '+multBtnLastClass+'" id="' +header + i + '" ' + checked + '>' + option +
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
                                                                '<input type="radio" name="' + header + '_' + i +'" '+addMandatory+' class="'+header +' '+multBtnLastClass+'" value="' + option + '" id="' +
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
                                                if(classes != undefined) {
                                                    var classArray = classes.split(' ');
                                                } else {
                                                    var classArray = [];
                                                }
                                                var dateRangeClass = '';
                                                for (var j = 0; j < classArray.length; j++) {
                                                    if (classArray[j] === 'date_range') {
                                                        dateRangeClass = classArray[j];
                                                        break;
                                                    }
                                                }
                                                if(dateRangeClass == 'date_range') {

                                                  inputType = '<input type="'+fieldType+'" name="' + header +'[]"  '+addMandatory+' class="form-control date_range ' + header +' '+multBtnLastClass+ ' white-smoke pop-non-edt-val" autocomplete="none" style="cursor:pointer" value="' + values[i] + '" id="' +header + i + '">';
                                                    if(i === values.length - 1) {
                                                            var minusButton = '<i class="fa fa-plus add_more" id="' +'add_more_'+header +'"></i>';
                                                    } else {
                                                        var minusButton = '<i class="fa fa-minus minus_button remove_more" id="'+ header+ i +'"></i>';
                                                    }
                                                }
                                                else {
                                                    inputType = '<input type="'+fieldType+'" name="' + header +'[]"  '+addMandatory+' class="form-control ' + header +' '+multBtnLastClass+ ' white-smoke pop-non-edt-val"  value="' + values[i] + '" id="' +header + i + '">';
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
                                      if (header === 'chart_status' && value.includes('QA_')) {
                                            claimStatus = value;
                                            value = value.replace('QA_', '');
                                            $('select[name="chart_status"]').val(claimStatus).trigger('change');
                                        $('#title_status').text(value);
                                    }
                                    if (header == 'id') {
                                        $('input[name="idValue"]').val(value);
                                    }
                                    if (header == 'invoke_date') {
                                        $('input[name="invoke_date"]').val(value);
                                    }
                                    if (header == 'QA_emp_id') {
                                        $('input[name="QA_emp_id"]').val(value);
                                    }
                                    if (header == 'QA_status_code') {
                                        $('select[name="QA_status_code"]').val(value).trigger('change');
                                        $('#status_val').val(value);
                                    }
                                    if (header == 'QA_sub_status_code') {
                                        statusVal = $('#status_val').val();
                                         subStatus(statusVal,value);
                                    }
                                    if (header == 'coder_rework_status') {
                                        $('label[id="coder_rework_status"]').text(value);
                                        if (value !== null) {
                                            $('#coder_rework_status_label').css('display','block');
                                            $('#coder_rework_status').css('display','block');
                                        } else {
                                            $('#coder_rework_status_label').css('display','none');
                                            $('#coder_rework_status').css('display','none');
                                        }
                                    }
                                    if (header == 'coder_rework_reason') {

                                        $('label[id="coder_rework_reason"]').text(value);
                                        if (value !== null) {
                                            $('#coder_rework_reason_label').css('display','block');
                                            $('#coder_rework_reason').css('display','block');
                                        } else {
                                            $('#coder_rework_reason_label').css('display','none');
                                            $('#coder_rework_reason').css('display','none');
                                        }
                                    }
                                    $('textarea[name="' + header + '[]"]').val(value);
                                    $('label[id="' + header + '"]').text(value);
                                    if(value != null) {
                                      $('input[name="' + header + '[]"]').val(value);
                                      $('input[name="' + header + '"]').val(value);
                                    }
                                    // if (header == 'am_cpt' || header == 'am_icd') {
                                    //     $('textarea[name="' + header + '_hidden[]"]').val(value);
                                    // }
                                }
                        });

                    }
            });
                function subStatus(statusVal,value) {
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
                    KTApp.block('#myModal_status', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Fetching...',
                    });
                    subStatus(status_code_id,'');
                    KTApp.unblock('#myModal_status');
                });
            $(document).on('click', '.clickable-view', function(e) {
                $('#myModal_status').modal('hide');
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
                        url: "{{ url('qa_production/qa_client_view_details') }}",
                        method: 'POST',
                        data: {
                            record_id: record_id,
                            clientName: clientName,
                            subProjectName: subProjectName
                        },
                        success: function(response) {
                            if (response.success == true) {
                                headers.push('QA_rework_comments');
                                headers.push('coder_rework_status');
                                headers.push('coder_rework_reason');
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
                                    if(data !== '') {
                                        var circle = $('<span>').addClass('circle');
                                        var span = $('<span>').addClass('date-label').text(data);
                                            span.prepend(circle);
                                            formattedDatas.push(span);
                                    }
                                });
                                formattedDatas.forEach(function(span, index) {
                                    if (header == 'QA_rework_comments') {
                                        $('label[id="QA_rework_comments_view"]').append(span);
                                        if (span !== null) {
                                            $('#QA_rework_comments_label_view').css('display','block');
                                            $('#QA_rework_comments_view').css('display','block');
                                            $('#hr_view').css('display','block');
                                        } else {
                                            $('#QA_rework_comments_label_view').css('display','none');
                                            $('#QA_rework_comments_view').css('display','none');
                                            $('#hr_view').css('display','none');
                                        }
                                    }
                                    $('label[id="' + header + '"]').append(span);
                                });
                            } else {
                                if (header === 'chart_status' && value.includes('QA_')) {
                                        value = value.replace('QA_', '');
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
                                if (header == 'coder_rework_status') {
                                   $('label[id="coder_rework_status_view"]').text(value);
                                    if (value !== null) {
                                        $('#coder_rework_status_label_view').css('display','block');
                                        $('#coder_rework_status_view').css('display','block');
                                    } else {
                                        $('#coder_rework_status_label_view').css('display','none');
                                        $('#coder_rework_status_view').css('display','none');
                                    }
                                }
                                if (header == 'coder_rework_reason') {

                                    $('label[id="coder_rework_reason_view"]').text(value);
                                    if (value !== null) {
                                        $('#coder_rework_reason_label_view').css('display','block');
                                        $('#coder_rework_reason_view').css('display','block');
                                        $('#hr_view').css('display','block');
                                    } else {
                                        $('#coder_rework_reason_label_view').css('display','none');
                                        $('#coder_rework_reason_view').css('display','none');
                                        $('#hr_view').css('display','none');
                                    }
                                }
                                if (header == 'QA_rework_comments') {
                                        $('label[id="QA_rework_comments_view"]').text(value);
                                        if (value !== null) {
                                            $('#QA_rework_comments_label_view').css('display','block');
                                            $('#QA_rework_comments_view').css('display','block');
                                            $('#hr_view').css('display','block');
                                        } else {
                                            $('#QA_rework_comments_label_view').css('display','none');
                                            $('#QA_rework_comments_view').css('display','none');
                                            $('#hr_view').css('display','none');
                                        }
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

            $(document).on('click', '.sop_click', function(e) {
                $('#myModal_sop').modal('show');
            });
                $('#myModal_sop').on('shown.bs.modal', function () {
                    $('#myModal_status').addClass('modal-right');
                    $('#myModal_view').addClass('modal-right');
                });

                $('#myModal_sop').on('hidden.bs.modal', function () {
                    $('#myModal_status').removeClass('modal-right');
                    $('#myModal_view').removeClass('modal-right');
                });

                $(document).on('click', '#project_pending_save', function(e) {
                    e.preventDefault();
                    var inputTypeValue = 0; var inputTypeRadioValue = 0;
                    var claimStatus =  $('#chart_status').val();
                        if(claimStatus == "QA_Hold") {
                            var ceHoldReason = $('#qa_hold_reason_editable');
                            if(ceHoldReason.val() == '') {
                                ceHoldReason.css('border-color', 'red', 'important');
                                    inputTypeValue = 1;
                            } else {
                                    ceHoldReason.css('border-color', '');
                                    inputTypeValue = 0;
                            }
                        }

                    var qaStatus = $('#qa_status');
                    var qaSubStatus =  $('#qa_sub_status');
                    if (qaStatus.val() == '' || qaStatus.val() == null) {
                        qaStatus.next('.select2').find(".select2-selection").css('border-color', 'red','important');
                        inputTypeValue = 1;
                        return false;
                    }

                    if (qaSubStatus.val() == '' || qaSubStatus.val() == null) {
                        qaSubStatus.next('.select2').find(".select2-selection").css('border-color', 'red','important');
                        inputTypeValue = 1;
                        return false;
                    }

                    $('#pendingFormConfiguration').serializeArray().map(function(input) {
                        labelName = input.name;
                            if(labelName.substring(0, 3).toLowerCase() == "cpt") {
                                var textValue = input.value;
                                if(textValue.length < 5) {
                                    inputTypeValue = 1;
                                    js_notification('error',"The CPT value must be at least 5 characters long" );
                                } else {
                                    inputTypeValue = 0;
                                }
                            }
                            if(labelName.substring(0, 3).toLowerCase() == "icd") {
                                var textValue = input.value;
                                if(textValue.length < 3) {
                                    inputTypeValue = 1;
                                    js_notification('error', "The ICD value must be at least 3 characters long" );
                                } else {
                                    inputTypeValue = 0;
                                }
                            }
                            return inputTypeValue;
                    });
                    var fieldNames = $('#pendingFormConfiguration').serializeArray().map(function(input) {
                        return input.name;
                    });
                    var requiredFields = {};
                    var requiredFieldsType = {};
                    var inputclass = [];
                    $('#pendingFormConfiguration').find(':input[required], select[required], textarea[required]',
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

                                            if(label_id == "cpt") {
                                                var textValue = $(this).val();
                                                if(textValue.length < 5) {
                                                    js_notification('error',"The " + label_id.toUpperCase() + " value must be at least 5 characters long" );
                                                }
                                            }
                                            if(label_id == "icd") {
                                                var textValue = $(this).val();
                                                if(textValue.length < 3) {
                                                    js_notification('error', "The " + label_id.toUpperCase() + " value must be at least 3 characters long" );
                                                }
                                            }
                                    });
                                }
                            });

                        }
                    }

                    var fieldValuesByFieldName = {};

                    $('input[type="radio"]:checked').each(function() {
                        var fieldName =  $(this).attr('class').split(' ')[0];
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
                            }).appendTo('form#pendingFormConfiguration');
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
                                KTApp.block('#myModal_status', {
                                    overlayColor: '#000000',
                                    state: 'danger',
                                    opacity: 0.1,
                                    message: 'Fetching...',
                                });
                                document.querySelector('#pendingFormConfiguration').submit();
                                KTApp.unblock('#myModal_status');

                            } else {
                                //   location.reload();
                            }
                        });

                    } else {
                        return false;
                    }
                });
            $(document).on('click', '.one', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_assigned/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] +
                    "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.two', function() {
                window.location.href = "{{ url('#') }}";
            })
            $(document).on('click', '.three', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_hold/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] +
                    "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.four', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_completed/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.five', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_unAssigned/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.six', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_duplicate/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.seven', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_auto_close/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })

            $(document).on('change', '#chart_status', function() {
                    var claimStatus = $(this).val();
                    if(claimStatus == "QA_Hold") {
                        $('#qa_hold_reason_editable').css('display', 'block');
                        $('#qa_hold_reason_label').css('display', 'block');
                    } else {
                        $('#qa_hold_reason_editable').css('display', 'none');
                        $('#qa_hold_reason_label').css('display', 'none');
                        $('#qa_hold_reason_editable').css('border-color', '');
                       $('#qa_hold_reason_editable').val('');
                    }
            })
            var excludedFields = ['QA_rework_comments', 'chart_status','coder_rework_status','coder_rework_reason','QA_status_code','QA_sub_status_code','qa_hold_reason','	ce_hold_reason'];
            var previousValue;
                $('#pendingFormConfiguration').on('focus', 'input:not(.exclude), select:not(.exclude), textarea:not(.exclude)', function() {
                            previousValue = $(this).val().trim();
                }).on('focusout', 'input:not(.exclude), select:not(.exclude), textarea:not(.exclude)', function() {
                    var fieldName = $(this).attr('name');
                    var trimmedFiled = $(this).attr('id') !== undefined ? $(this).attr('id') : $(this).attr('class');
                    var trimmedFiled1 = $(this).attr('name').replace(/\[\]$/, '');
                    var formattedValue = trimmedFiled.toUpperCase().replace(/_else_/g, '/').replace(/_/g, ' ');
                    var formattedValue1 = trimmedFiled1.toUpperCase().replace(/_else_/g, '/').replace(/_/g, ' ');
                    if (excludedFields.indexOf(fieldName) === -1) {
                        var currentValue = '';
                        if ($(this).is('input[type="checkbox"]')) {
                            currentValue = $(this).is(':checked') ? ' Checked '+$(this).closest('label').text().trim() : ' Unchecked '+$(this).closest('label').text().trim();
                        } else if ($(this).is('input[type="radio"]')) {
                            currentValue = $(this).is(':checked') ? ' Checked '+$(this).closest('label').text().trim() : ' Unchecked '+$(this).closest('label').text().trim();console.log(currentValue,'currentValue',$(this).closest('label').text());
                        } else if ($(this).is('input[type="date"]')) {
                            currentValue = $(this).val().trim();
                        } else {
                            currentValue = $(this).val().trim();
                        }
                        var newLine = '';
                        if ($(this).is('input[type="checkbox"]') || $(this).is('input[type="radio"]')) {
                                newLine =  formattedValue1 + currentValue;
                        }  else {
                            if(currentValue != '') {
                                newLine = previousValue != '' ? formattedValue1 + ' '+previousValue + ' Changed to ' + currentValue : formattedValue1 + '  added ' + currentValue;
                            } else if (previousValue !== currentValue && currentValue == ''){
                                newLine = previousValue != '' ? formattedValue1 + ' '+previousValue+ ' removed' : formattedValue1 + '  added ' + currentValue;
                            }
                        }
                        var textAreaValue = $('#QA_rework_comments').val();
                        if (textAreaValue.includes(previousValue) && previousValue !== currentValue) {
                            var lines = textAreaValue.split('\n');
                            var matchedLine = lines.find(line => line.includes(previousValue));
                            textAreaValue = textAreaValue.replace(matchedLine, newLine);
                        } else {
                            if(textAreaValue == "" && previousValue !== currentValue) {
                                textAreaValue += newLine;
                            } else {
                                if(previousValue !== currentValue) {
                                    newLine = '\n'+newLine;
                                    textAreaValue += newLine;
                                }
                            }
                        }

                        $('#QA_rework_comments').val(textAreaValue);
                    }

                });
                    // $('#pendingFormConfiguration').on('focus', 'input:not(.exclude), select:not(.exclude), textarea:not(.exclude)', function() {
                    //     currentClass = $(this).attr('name').replace(/\[\]$/, '');
                    //     if (currentClass == 'am_cpt'|| currentClass =='am_icd'){
                    //         previousValue = $('.'+currentClass+'_hidden').val().trim();
                    //     } else {
                    //         previousValue = $(this).val().trim();
                    //     }
                    // }).on('focusout', 'input:not(.exclude), select:not(.exclude), textarea:not(.exclude)', function() {
                    //     //   var currentValue = $(this).val();
                    //         var fieldName = $(this).attr('name');
                    //         var trimmedFiled = $(this).attr('id') !== undefined ? $(this).attr('id') : $(this).attr('class');
                    //         var trimmedFiled1 = $(this).attr('name').replace(/\[\]$/, '');
                    //         var formattedValue = trimmedFiled.toUpperCase().replace(/_else_/g, '/').replace(/_/g, ' ');
                    //         var formattedValue1 = trimmedFiled1.toUpperCase().replace(/_else_/g, '/').replace(/_/g, ' ');
                    //     if (excludedFields.indexOf(fieldName) === -1) {
                    //         var currentValue = '';
                    //         if ($(this).is('input[type="checkbox"]')) {
                    //             currentValue = $(this).is(':checked') ? ' Checked '+$(this).closest('label').text().trim() : ' Unchecked '+$(this).closest('label').text().trim();
                    //         } else if ($(this).is('input[type="radio"]')) {
                    //             currentValue = $(this).is(':checked') ? ' Checked '+$(this).closest('label').text().trim() : ' Unchecked '+$(this).closest('label').text().trim();
                    //         } else if ($(this).is('input[type="date"]')) {
                    //             currentValue = $(this).val().trim();
                    //         } else {
                    //             currentValue = $(this).val().trim();
                    //         }
                    //         var newLine = '';
                    //         if ($(this).is('input[type="checkbox"]') || $(this).is('input[type="radio"]')) {
                    //             if(previousValue !== currentValue) {
                    //                 newLine =  formattedValue1 + currentValue;
                    //             }
                    //         } else {
                    //             var textAreaValue = $('#QA_rework_comments').val();
                    //             var processedText = fieldName.replace('am_', '').toUpperCase();
                    //             processedText = processedText.replace('[]', '').toUpperCase();
                    //             var errorPreviousValue = [];
                    //             if(currentValue != '') {                                  
                    //                 if (fieldName == 'am_cpt[]'|| fieldName =='am_icd[]') {
                    //                     var notes = $('.QA_rework_comments').val().trim();
                    //                     var annexPrevious = previousValue.split(',').map(value => value.trim()); 
                    //                        annexPrevious = annexPrevious.filter(function(item) {
                    //                             return item && item.trim();
                    //                         });
                    //                     var annexcurrent = currentValue.split(',').map(value => value.trim());
                    //                        annexcurrent = annexcurrent.filter(function(item) {
                    //                             return item && item.trim();
                    //                         });
                                      
                    //                     let notesMap = {};
                    //                     var annexInfMap = {};
                                     
                    //                         annexcurrent.forEach(function (value, index) {
                    //                                 annexInfMap[value] = (annexInfMap[value] || 0)+1 ;
                    //                             });
                    //                     for (var i = 0; i < annexPrevious.length; i++) {
                    //                         if (annexcurrent[i] !== undefined && annexcurrent[i] !== '') {
                    //                             if (annexPrevious[0] !== '' && annexPrevious[i] !== annexcurrent[i]) {
                    //                                 if (annexPrevious[i].includes('-') && annexcurrent[i].includes('-')) {
                    //                                     var clientParts = annexPrevious[i].split('-');
                    //                                     var annexParts = annexcurrent[i].split('-');
                    //                                     const clientPart0 = clientParts[0].trim(); 
                    //                                     const annexPart0 = annexParts[0].trim(); 
                    //                                     const part1 = clientParts[1].trim(); 
                    //                                     const part2 = annexParts[1].trim(); 
                    //                                     if(part1 != part2) {
                    //                                         notesMap[part1] = processedText + ' - modifier ' +  part1 + ' changed to ' +  part2 + ' belongs to ' +  clientPart0;
                    //                                         errorPreviousValue[part1] = processedText + ' - modifier ' + part1;
                    //                                     }
                    //                                         var noteLines =  notes.split('\n');
                    //                                         for (var j = 0; j < noteLines.length; j++) {
                    //                                             if(noteLines[j].includes(processedText + ' - modifier ' +  part1)) {
                    //                                                 // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                    //                                                 // notes = noteLines; 
                    //                                                 noteLines = noteLines.filter(line => line !== noteLines[j]);
                    //                                                 notes = noteLines.join('\n');  

                    //                                                 var lines = notes.split('\n');  
                    //                                                 var matchedLine = lines.find(lines => lines.includes(processedText + ' - modifier ' +  part1));
                    //                                                 if (matchedLine) {
                    //                                                     notes = lines.filter(lines => lines !== matchedLine).join('\n');
                    //                                                 }                                    
                                                                                                    
                    //                                             }
                    //                                         }
                                                    
                    //                                     if(clientPart0 != annexPart0) {
                    //                                         notesMap[clientPart0] = processedText + ' - ' + clientPart0 + ' changed to ' + annexPart0;
                    //                                         errorPreviousValue[clientPart0] = processedText + ' - ' + clientPart0;
                                                        
                    //                                     }
                    //                                     var lines1 = notes.split('\n');
                    //                                     var matchedLine = lines1.find(lines => lines.includes(processedText + ' - ' + clientPart0));
                    //                                     if (matchedLine) {
                    //                                         notes = lines1.filter(lines => lines !== matchedLine).join('\n');
                    //                                     }
                    //                                     var noteLines =  notes.split('\n');
                    //                                     for (var j = 0; j < noteLines.length; j++) {
                    //                                         if(noteLines[j].includes(processedText + ' - ' + clientPart0)){
                    //                                             // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                    //                                             // notes = noteLines;
                    //                                             noteLines = noteLines.filter(line => line !== noteLines[j]);
                    //                                             notes = noteLines.join('\n');                              
                    //                                         }
                    //                                     }
                    //                                 } else if (annexPrevious[i].includes('-') && !annexcurrent[i].includes('-')) {
                    //                                     var clientParts = annexPrevious[i].split('-');
                    //                                     const client1 = clientParts[0].trim(); 
                    //                                     const annex1 =annexcurrent[i].trim(); 
                    //                                     const cpart1 = clientParts[1].trim(); //console.log(annexPrevious[i],annexcurrent[i],annex1,cpart1,client1,);
                                                        
                    //                                     notesMap[cpart1] = processedText + ' - modifier ' +  cpart1 + ' removed belongs to ' + client1;
                    //                                     errorPreviousValue[cpart1] = processedText + ' - modifier ' + cpart1; 
                    //                                     var lines = notes.split('\n');
                    //                                     var matchedLine = lines.find(lines => lines.includes(processedText + ' - modifier ' +  cpart1));
                    //                                     if (matchedLine) {
                    //                                         notes = lines.filter(lines => lines !== matchedLine).join('\n');
                    //                                     }
                    //                                     if(client1 != annex1) {
                    //                                         notesMap[i] = processedText + ' - ' + client1 + ' changed to ' + annex1;
                    //                                         errorPreviousValue[client1] = processedText + ' - ' + client1;
                    //                                     }
                    //                                     var noteLines =  notes.split('\n');
                    //                                     for (var j = 0; j < noteLines.length; j++) {
                    //                                         if(noteLines[j].includes(processedText + ' - ' + client1)){
                    //                                             // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                    //                                             // notes = noteLines;
                    //                                             noteLines = noteLines.filter(line => line !== noteLines[j]);
                    //                                             notes = noteLines.join('\n');                                                                                          
                    //                                         }
                    //                                     }
                    //                                 } else if (!annexPrevious[i].includes('-') && annexcurrent[i].includes('-')) {
                    //                                     var parts = annexcurrent[i].split('-');
                    //                                     const client2 = annexPrevious[i].trim(); 
                    //                                     const annex2 = parts[0].trim();
                    //                                     const apart1 = parts[0].trim(); 
                    //                                     const apart2 = parts[1].trim(); 
                    //                                     notesMap[apart1] = processedText + ' - modifier ' +  parts[1] + ' added to ' +  client2;
                    //                                     errorPreviousValue[client2] = ' added to ' + client2;
                    //                                     var noteLines =  notes.split('\n');
                    //                                     for (var j = 0; j < noteLines.length; j++) {
                    //                                         if(noteLines[j].includes(processedText + ' - modifier ')){
                    //                                             // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                    //                                             // notes = noteLines;   
                    //                                             noteLines = noteLines.filter(line => line !== noteLines[j]);
                    //                                             notes = noteLines.join('\n');                              
                    //                                         }
                    //                                     }
                    //                                     var lines = notes.split('\n');
                    //                                     var matchedLine = lines.find(lines => lines.includes(processedText + ' - modifier '));
                    //                                     if (matchedLine) {
                    //                                         notes = lines.filter(lines => lines !== matchedLine).join('\n');
                    //                                     }
                    //                                     if(client2 != annex2) {
                    //                                         notesMap[i] = processedText + ' - ' + client2 + ' changed to ' + annex2;
                    //                                         errorPreviousValue[annexPrevious[i]] = processedText + ' - ' + annexPrevious[i];
                    //                                     }
                    //                                     var noteLines =  notes.split('\n');
                    //                                     for (var j = 0; j < noteLines.length; j++) {
                    //                                             if(noteLines[j].includes(processedText + ' - ' + client2)){
                    //                                                 // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                    //                                                 // notes = noteLines;
                    //                                                 noteLines = noteLines.filter(line => line !== noteLines[j]);
                    //                                                 notes = noteLines.join('\n');                                
                    //                                             }
                    //                                     }
                    //                                     // var lines = notes.split('\n');
                    //                                     // var matchedLine = lines.find(lines => lines.includes(processedText + ' - ' + client2));
                    //                                     // if (matchedLine) {
                    //                                     //     notes = lines.filter(lines => lines !== matchedLine).join('\n');
                    //                                     // }
                    //                                 } else {
                    //                                     notesMap[i] = processedText + ' - ' + annexPrevious[i] + ' changed to ' + annexcurrent[i];
                    //                                     errorPreviousValue[annexPrevious[i]] = processedText + ' - ' + annexPrevious[i];
                    //                                     var noteLines =  notes.split('\n');
                    //                                     for (var j = 0; j < noteLines.length; j++) {console.log(annexPrevious[i],'annexPrevious[i]');
                                                        
                    //                                          if(noteLines[j].includes(processedText + ' - ') &&noteLines[j].includes(annexPrevious[i])){
                    //                                         // if(noteLines[j].includes(processedText + ' - ' + annexPrevious[i]) || noteLines[j].includes(processedText + ' - ' + annexcurrent[i])){
                    //                                             // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                    //                                             // notes = noteLines; 
                    //                                             noteLines = noteLines.filter(line => line !== noteLines[j]);
                    //                                             notes = noteLines.join('\n');                                              
                    //                                         }
                    //                                     }
                    //                                 }
                                                
                    //                                 var noteLines =  notes.split('\n');
                                                    
                    //                                 for (var j = 0; j < noteLines.length; j++) {//console.log('else error',noteLines,annexPrevious[j],noteLines[j]);
                    //                                     if(noteLines[j].includes(processedText + ' - ' + annexPrevious[i])) {
                    //                                         noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                    //                                         notes = noteLines;
                    //                                         // noteLines = noteLines.filter(line => line !== noteLines[j]);
                    //                                         // notes = noteLines.join('\n');  
                    //                                     }                                                    
                    //                                 }
                    //                             } else {
                    //                                 var lines = notes.split('\n');
                                                    
                    //                                 if (annexPrevious[i].includes('-')) {
                    //                                     var clientParts = annexPrevious[i].split('-');
                    //                                     var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientParts[0]));// console.log('else no -', matchedLine, processedText + ' - ' + clientParts[0]);
                    //                                     var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1])); //console.log('else no -', matchedLine1, processedText + ' - modifier ' + clientParts[1]);
                    //                                     var matchedLine2 = lines.find(line => line.includes(processedText + ' - ' + clientParts[0] + ' changed to '));
                    //                                     var matchedLine3 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'removed '));
                    //                                     var matchedLine4 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'added '));
                    //                                     if (matchedLine || matchedLine1 || matchedLine2 || matchedLine2 || matchedLine3 || matchedLine4) {
                    //                                         lines = lines.filter(line => line !== matchedLine && line !== matchedLine1 && line !== matchedLine2 && line !== matchedLine3 && line !== matchedLine4);
                    //                                         notes = lines.join('\n');
                    //                                     }
                    //                                 } else {
                    //                                     var clientParts = annexcurrent[i].split('-');
                    //                                     var matchedLine = lines.find(line => line.includes(processedText + ' - ' + annexPrevious[i]));//console.log('else else no -',matchedLine,processedText + ' - ' + annexPrevious[i]);
                    //                                     var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1])); //console.log('else no -', matchedLine1, processedText + ' - modifier ' + clientParts[1]);
                    //                                     var matchedLine2 = lines.find(line => line.includes(processedText + ' - ' + clientParts[0] + ' changed to '));
                    //                                     var matchedLine3 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'removed '));
                    //                                     var matchedLine4 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'added '));
                    //                                     if (matchedLine || matchedLine1 || matchedLine2 || matchedLine2 || matchedLine3 || matchedLine4) {
                    //                                         lines = lines.filter(line => line !== matchedLine && line !== matchedLine1  && line !== matchedLine2 && line !== matchedLine3 && line !== matchedLine4);
                    //                                         notes = lines.join('\n');
                    //                                     }
                    //                                     var noteLines =  notes.split('\n');
                    //                                     for (var j = 0; j < noteLines.length; j++) {
                    //                                         if(noteLines[j].includes(processedText + ' - ' + annexPrevious[i])) {
                    //                                             // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                    //                                             // notes = noteLines;
                    //                                             noteLines = noteLines.filter(line => line !== noteLines[j]);
                    //                                             notes = noteLines.join('\n');                                                          
                    //                                         }    
                    //                                     }
                    //                                 }//console.log(notes,'notes else');
                                                    
                    //                             }
                    //                             if (annexInfMap[annexcurrent[i]] > 0) {
                    //                                 annexInfMap[annexcurrent[i]]--;
                    //                                 if (annexInfMap[annexcurrent[i]] === 0) {
                    //                                     delete annexInfMap[annexcurrent[i]];
                    //                                 }
                    //                             }
                    //                         } else {
                    //                             var lines = notes.split('\n');
                    //                             if (annexPrevious[i].includes('-')) {
                    //                                 var clientParts = annexPrevious[i].split('-');
                    //                                 var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientParts[0])); //console.log('else no -', matchedLine, clientParts[0],lines);
                    //                                 var matchedLine2 = lines.find(line => line.includes(processedText + ' - ' + clientParts[0] + ' changed to ')); //console.log('else no -', matchedLine2, clientParts[0],lines);
                    //                                 var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1])); //console.log('else no -', matchedLine1,clientParts[1]);
                    //                                 var matchedLine3 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'removed '));
                    //                                 var matchedLine4 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'added '));
                    //                                 if (matchedLine || matchedLine1 || matchedLine2 || matchedLine3 || matchedLine4) {
                    //                                     lines = lines.filter(line => line !== matchedLine && line !== matchedLine1 && line !== matchedLine2 && line !== matchedLine3 && line !== matchedLine4);
                    //                                     notes = lines.join('\n');
                    //                                 }
                    //                             } else {
                    //                                 var matchedLine = lines.find(line => line.includes(processedText + ' - ' + annexPrevious[i]));
                    //                                 if (matchedLine) {
                    //                                     notes = lines.filter(line => line !== matchedLine).join('\n');
                    //                                 }
                    //                                 var noteLines =  notes.split('\n');
                    //                                 for (var j = 0; j < noteLines.length; j++) {
                    //                                     if(noteLines[j].includes(processedText + ' - ' + annexPrevious[i])){
                    //                                         // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                    //                                         // notes = noteLines;  
                    //                                         noteLines = noteLines.filter(line => line !== noteLines[j]);
                    //                                         notes = noteLines.join('\n');                                                    
                    //                                     }
                    //                                 }      
                    //                                 }   
                    //                             if(annexcurrent.length > 1 && annexcurrent[0] == ''){
                    //                                 notesMap[annexPrevious[i]] = processedText + ' - ' + annexPrevious[i] + ' removed';
                    //                             } else if(annexcurrent[0] !== '') {
                    //                                 notesMap[annexPrevious[i]] = processedText + ' - ' + annexPrevious[i] + ' removed';
                    //                             } else {
                    //                                 // var lines = notes.split('\n');
                    //                                 // for (var j = 0; j < lines.length; j++) {
                    //                                 //     var matchedLine = lines.find(line => line.includes(processedText )); 
                    //                                 //         notes = lines.filter(line => line !== matchedLine).join('\n');
                    //                                 // }
                    //                                      var lines = notes.split('\n');
                    //                                     if (annexPrevious[i].includes('-')) {
                    //                                         var clientParts = annexPrevious[i].split('-');
                    //                                         var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientParts[0])); //console.log('else no -', matchedLine, clientParts[0],lines);
                    //                                         var matchedLine2 = lines.find(line => line.includes(processedText + ' - ' + clientParts[0] + ' changed to ')); //console.log('else no -', matchedLine2, clientParts[0],lines);
                    //                                         var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1])); //console.log('else no -', matchedLine1,clientParts[1]);
                    //                                         var matchedLine3 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'removed '));
                    //                                         var matchedLine4 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'added '));
                    //                                         if (matchedLine || matchedLine1 || matchedLine2 || matchedLine3) {
                    //                                             lines = lines.filter(line => line !== matchedLine && line !== matchedLine1 && line !== matchedLine2 && line !== matchedLine3 && line !== matchedLine4);
                    //                                             notes = lines.join('\n');
                    //                                         }
                    //                                     } else {
                    //                                         var matchedLine = lines.find(line => line.includes(processedText + ' - ' + annexPrevious[i]));
                    //                                         if (matchedLine) {
                    //                                             notes = lines.filter(line => line !== matchedLine).join('\n');
                    //                                         }
                    //                                         var noteLines =  notes.split('\n');
                    //                                         for (var j = 0; j < noteLines.length; j++) {
                    //                                             if(noteLines[j].includes(processedText + ' - ' + annexPrevious[i])){
                    //                                                 // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                    //                                                 // notes = noteLines;  
                    //                                                 noteLines = noteLines.filter(line => line !== noteLines[j]);
                    //                                                 notes = noteLines.join('\n');                                                    
                    //                                             }
                    //                                         }      
                    //                                      }          
                    //                             }
                    //                             errorPreviousValue[annexPrevious[i]] = processedText + ' - ' + annexPrevious[i];
                    //                         }
                    //                     }

                    //                     for (var key in annexInfMap) {
                    //                         if (annexInfMap.hasOwnProperty(key) && annexInfMap[key] > 0) {
                    //                             if(key && (annexPrevious[0] !== '')) {
                    //                                 notesMap[key] = processedText + ' - ' + key + ' added';
                    //                                 var lines = notes.split('\n');
                    //                                 var matchedLine = lines.find(line => line.includes(notesMap[key]));
                    //                                 if (matchedLine) {
                    //                                     notes = lines.filter(line => line !== matchedLine).join('\n');
                    //                                 }
                    //                             }
                    //                         } 
                    //                     }
                    //                     annexPrevious.forEach(function (value) {
                    //                         let combinedArray = Object.values(errorPreviousValue);
                    //                         let filteredArray = combinedArray.filter(item => item !== null && item !== '');
                    //                          if (value.includes('-')) {
                    //                             var clientParts = value.split('-');
                    //                             clientParts.forEach(function(innerValue){
                    //                                 if (notesMap[innerValue]) { 
                    //                                     var lines = notes.split('\n');
                    //                                     if (lines.includes(filteredArray[innerValue])) {
                    //                                     var matchedLine = lines.find(line => line.includes(filteredArray[innerValue]));
                    //                                         if (matchedLine !== undefined) {
                    //                                             notes = notes.replace(matchedLine, notesMap[innerValue]);
                    //                                         } else {
                    //                                             notes += '\n' + notesMap[innerValue];
                    //                                         }
                    //                                     } else {
                    //                                         if (notes === "") {
                    //                                             notes += notesMap[innerValue];
                    //                                         } else {
                    //                                             notes += '\n' + notesMap[innerValue];
                    //                                         }
                    //                                     }
                    //                                     delete notesMap[innerValue];
                    //                              }
                    //                             })
                                               
                    //                         } else {
                    //                             if (notesMap[value]) { 
                    //                                 var lines = notes.split('\n');
                    //                                 if (lines.includes(filteredArray[value])) {
                    //                                 var matchedLine = lines.find(line => line.includes(filteredArray[value]));
                    //                                     if (matchedLine !== undefined) {
                    //                                         notes = notes.replace(matchedLine, notesMap[value]);
                    //                                     } else {
                    //                                         notes += '\n' + notesMap[value];
                    //                                     }
                    //                                 } else {
                    //                                     if (notes === "") {
                    //                                         notes += notesMap[value];
                    //                                     } else {
                    //                                         notes += '\n' + notesMap[value];
                    //                                     }
                    //                                 }
                    //                                 delete notesMap[value];
                    //                             }
                    //                         }
                    //                     });
                    //                     for (var key in notesMap) {
                    //                         if (notesMap.hasOwnProperty(key)) {
                    //                             notes += '\n' + notesMap[key];
                    //                         }
                    //                     }
                                        
                    //                     var notes1 = notes.split('\n').filter(line => line.trim() !== '');//console.log(notesMap,'notesMap',notes1);
                    //                     var matchedLine = notes1.find(line => line.includes(processedText + ' - ') && line.includes(' added') );
                    //                     if (matchedLine !== undefined && !matchedLine.includes(' added to')) {
                    //                         let modifiedString = matchedLine.replace(processedText + ' - ', '').replace(' added', '');//console.log(modifiedString,'modifiedString',annexcurrent,matchedLine);
                    //                         var notes2 = textAreaValue.split('\n').filter(line => line.includes(processedText + ' - '));//console.log(notes2,'notes2',textAreaValue.split('\n'));
                    //                         if (!annexcurrent.includes(modifiedString) || notes2.length > annexcurrent.length) {
                    //                             notes1 = notes1.filter(line => line !== matchedLine);
                    //                             notes = notes1.join('\n');
                    //                         }                                
                    //                     }

                    //                     var noteLines11 =  notes.split('\n').filter(line => line.trim() !== '');
                    //                     var filteredNoteLines = [];
                    //                     var filteredNoteLines1 = [];
                    //                     for (var q = 0; q < noteLines11.length; q++) {                                         
                    //                         if(noteLines11[q].includes(processedText + ' - ') && noteLines11[q].includes(' added') && !noteLines11[q].includes(' added to')){                                  
                    //                             let modifiedString = noteLines11[q].replace(processedText + ' - ', '').replace(' added', '');
                    //                             if (!annexcurrent.includes(modifiedString)) {
                    //                                 noteLines11 = noteLines11.filter(line => line !== noteLines11[q]);
                    //                                 notes = noteLines11.join('\n');  
                    //                             }                                           
                    //                         }                                        
                    //                         if (annexcurrent.length == 0 && noteLines11[q].includes(processedText + ' - ')) {
                    //                             annexPrevious.forEach(function (item,value) {                                        
                    //                                 filteredNoteLines.push(processedText + ' - ' + item + ' removed');
                    //                             });
                    //                         } else {console.log(noteLines11,'noteLines11');
                                            
                    //                             if(noteLines11[q].includes(processedText + ' - ')) {console.log(q,'if q',noteLines11[q]);
                                                
                    //                                 filteredNoteLines1.push(noteLines11[q]);
                    //                             } else {console.log(q,'else q',noteLines11[q]);
                    //                                filteredNoteLines.push(noteLines11[q]);
                    //                             }
                    //                         }
                    //                     }      
                                       
                    //                     // noteLines11 = noteLines11.filter(function(item) {
                    //                     //     return filteredNoteLines1.indexOf(item) === -1;
                    //                     // }); console.log(filteredNoteLines1,'afer filteredNoteLines1',filteredNoteLines,processedText + ' - ',noteLines11);
                    //                     //  noteLines11 = filteredNoteLines;console.log('notes',noteLines11.join('\n'));//console.log(filteredNoteLines1,'afer filteredNoteLines1',filteredNoteLines,processedText + ' - ',noteLines11);
                    //                     //  notes = noteLines11.join('\n');
                                         
                    //                     let noteLines1 = notes.trim().split('\n');
                    //                     let uniqueNotes = Array.from(new Set(noteLines1));
                    //                     let finalNotes = uniqueNotes.join('\n');
                    //                     newLine = finalNotes;console.log(newLine,'newLine if');
                    //                 } else {
                    //                     newLine = previousValue != '' ? formattedValue1 + ' '+previousValue + ' Changed to ' + currentValue : formattedValue1 +' ' + currentValue + ' added';
                    //                 }
                                    
                    //             } else if(previousValue !== currentValue && currentValue == '') {//console.log(previousValue,'previousValue',currentValue);
                    //                 if(currentClass == 'am_cpt'|| currentClass =='am_icd') {
                    //                     newLine = previousValue != '' ? processedText + ' - '+previousValue+ ' removed' : processedText +' ' + currentValue + ' added';
                    //                 } else {
                    //                     newLine = previousValue != '' ? formattedValue1 + ' '+previousValue+ ' removed' : formattedValue1 +' ' + currentValue + ' added';
                    //                 }
                    //             }
                    //         }
                    //         if(currentClass == 'am_cpt'|| currentClass =='am_icd') {
                    //                     var annexPrevious = previousValue.split(',').map(value => value.trim()); 
                    //                        annexPrevious = annexPrevious.filter(function(item) {
                    //                             return item && item.trim();
                    //                         });
                    //                     var annexcurrent = currentValue.split(',').map(value => value.trim());
                    //                        annexcurrent = annexcurrent.filter(function(item) {
                    //                             return item && item.trim();
                    //                         });
                    //                     var noteLines11 =  textAreaValue.split('\n').filter(line => line.trim() !== '');//console.log(noteLines11,'noteLines11');                                
                    //                     var filteredNoteLines = [];
                    //                     var filteredNoteLines1 = [];
                    //                 for (var q = 0; q < noteLines11.length; q++) {                                     
                    //                     if(noteLines11[q].includes(processedText + ' - ') && noteLines11[q].includes(' added') && !noteLines11[q].includes(' added to')){                                  
                    //                         let modifiedString = noteLines11[q].replace(processedText + ' - ', '').replace(' added', '');//console.log('noteLines11[q]',noteLines11[q],noteLines11);
                    //                         if (!annexcurrent.includes(modifiedString)) {
                    //                             noteLines11 = noteLines11.filter(line => line !== noteLines11[q]);
                    //                             notes = noteLines11.join('\n');  
                    //                         }                                           
                    //                     }
                    //                     // if (annexcurrent.length == 0 && noteLines11[q].includes(processedText + ' - ')) {
                    //                     //     clientInf.forEach(function (item,value) {                                        
                    //                     //          filteredNoteLines.push(processedText + ' - ' + item + ' removed');
                    //                     //     });
                    //                     // } else {
                    //                     //     if(noteLines11[q].includes(processedText + ' - ')) {
                    //                     //          filteredNoteLines1.push(noteLines11[q]);
                    //                     //     } else {
                    //                     //        filteredNoteLines.push(noteLines11[q]);
                    //                     //     }
                    //                     // }
                    //                 }      
                               
                    //                 noteLines11 = noteLines11.filter(function(item) {
                    //                     return filteredNoteLines1.indexOf(item) === -1;
                    //                 });
                                    
                    //                 noteLines11 = filteredNoteLines;
                    //                 textAreaValue = noteLines11.join('\n');

                    //                 var notes1 = textAreaValue.split('\n');//console.log(newLine,'newLine',textAreaValue,notes1);
                    //                 let modifiedString;//console.log(filteredNoteLines1,'filteredNoteLines1',notes1,noteLines11);
                    //                 var matchedLine = notes1.find(line => line.includes(processedText + ' - ') && line.includes(' added') && !line.includes(' added to'));
                    //                 if (matchedLine !== undefined && !matchedLine.includes(' added to')) {
                    //                      modifiedString = matchedLine.replace(processedText + ' - ', '').replace(' added', '');                         
                    //                 }
                                    
                    //                 if (modifiedString !== undefined && textAreaValue.includes(modifiedString) && !annexcurrent.includes(modifiedString)) {
                    //                         var lines = textAreaValue.split('\n');
                    //                         notes1 = lines.filter(line => line !== matchedLine);
                    //                         textAreaValue = notes1.join('\n');
                                                 
                    //                 } else {
                    //                     if(textAreaValue == "") {//console.log(textAreaValue,'textAreaValue',newLine);
                                        
                    //                         textAreaValue += newLine;
                    //                     } else {
                    //                     var textAreaValueLines = textAreaValue.split('\n');console.log(textAreaValueLines,'textAreaValueLines',newLine);
                                        
                    //                     let combinedArray = Object.values(errorPreviousValue);
                    //                     let filteredArray = combinedArray.filter(item => item !== null && item !== '');
                    //                     if( filteredArray.length >= 1) {
                    //                         for (var j = 0; j < filteredArray.length; j++) {//console.log(filteredArray[j],'filteredArray[j] fin',filteredArray);
                    //                             if (jQuery.inArray(filteredArray[j], textAreaValueLines)) {
                    //                                 var matchedLine = textAreaValueLines.find(line => line.includes(processedText) && line.includes(filteredArray[j])); //console.log(matchedLine,'matchedLine fin1');
                    //                                 if (matchedLine) {                         
                    //                                     textAreaValue = textAreaValue.replace(matchedLine, newLine);
                    //                                 } else {
                    //                                         newLine = '\n'+newLine;
                    //                                         textAreaValue += newLine;
                    //                                 }
                    //                             } else {
                    //                                     newLine = '\n'+newLine;
                    //                                 textAreaValue += newLine;
                    //                             }
                    //                         }   
                    //                     } else {
                    //                         var textAreaValueLines = textAreaValue.split('\n');
                    //                         for(var a=0;a < textAreaValueLines.length; a++) {
                    //                             var matchedLine = textAreaValueLines[a].includes(processedText);//console.log(matchedLine,'matchedLine fin',textAreaValueLines[a],newLine);
                    //                             // var matchedLine = textAreaValueLines.find(line => line.includes(processedText));console.log(matchedLine,'matchedLine fin  - modifier ');
                    //                             if (matchedLine) {
                    //                                 textAreaValue = textAreaValue.replace(textAreaValueLines[a], newLine);
                    //                             } else {
                    //                                  newLine = '\n'+newLine;
                    //                                 textAreaValue += newLine;
                    //                             }
                    //                         }
                                          
                    //                     }    
                    //                 }
                    //             }
                    //             // textAreaValue += newLine;
                    //         } else {
                    //             var textAreaValue = $('#QA_rework_comments').val();
                    //              if (textAreaValue.includes(previousValue) && previousValue !== currentValue) {
                    //                 var lines = textAreaValue.split('\n');
                    //                 var matchedLine = lines.find(line => line.includes(previousValue) && line.includes(formattedValue1));  
                    //                 textAreaValue = textAreaValue.replace(matchedLine, newLine);
                    //             } else {
                    //                 if(textAreaValue == "" && previousValue !== currentValue) {
                    //                     textAreaValue += newLine;
                    //                 } else {
                    //                     if(previousValue !== currentValue) {
                    //                         newLine = '\n'+newLine;
                    //                         textAreaValue += newLine;
                    //                     }
                    //                 }
                    //             }
                    //         }
                    //             let textAreaValue1 = textAreaValue.trim().split('\n');
                    //             let uniqueNotes1 = Array.from(new Set(textAreaValue1));
                    //             let finalNotes1 = uniqueNotes1.join('\n');
                    //             $('#QA_rework_comments').val(finalNotes1);
                    //     }

                    // });

                // function handleBlurEvent(clientClass, annexClass) {
                //     var clientInf = $(clientClass).val().split(',').map(value => value.trim()); // Trimming spaces
                //     var annexInf = $(annexClass).val().split(',').map(value => value.trim()); // Trimming spaces
                //     let notesMap = {};
                //     var previousValue = [];
                //     var processedText = annexClass.replace('.am_', '').toUpperCase();
                //     var annexInfMap = {};
                //     var notes = $('.annex_qa_trends').val().trim();

                //     annexInf.forEach(function (value, index) {
                //         annexInfMap[value] = (annexInfMap[value] || 0)+1 ;
                //     });
                
                //     for (var i = 0; i < clientInf.length; i++) {
                //         if (annexInf[i] !== undefined && annexInf[i] !== '') {
                //             if (clientInf[0] !== '' && clientInf[i] !== annexInf[i]) {
                //                 if (annexInf[i].includes('-')) {
                //                     const parts = annexInf[i].split('-');
                //                     const part1 = parts[0].trim(); // 23
                //                     const part2 = parts[1].trim(); // 12
                //                     notesMap[clientInf[i]] = processedText + ' - modifier ' +  parts[1] + ' added to ' +  parts[0];
                //                     // notesMap[clientInf[i]] = processedText + ' - ' + clientInf[i] + ' modifier added ' + annexInf[i];
                //                 } else {
                //                     notesMap[clientInf[i]] = processedText + ' - ' + clientInf[i] + ' changed to ' + annexInf[i];
                //                 }
                //                 previousValue[clientInf[i]] = processedText + ' - ' + clientInf[i];
                //                 var noteLines =  notes.split('\n');
                //                 for (var j = 0; j < noteLines.length; j++) {
                //                         if(noteLines[j].includes(processedText)){
                //                             if(noteLines[j].includes(processedText + ' - ' + clientInf[i])) {
                //                             } else {
                //                                 noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                //                                 notes = noteLines;                                         
                //                             }
                //                         }
                //                 }
                //             } else {
                //                 var lines = notes.split('\n');
                //                 var matchedLine = lines.find(line => line.includes(processedText + ' - ' + annexInf[i]));
                //                 if (matchedLine) {
                //                     notes = lines.filter(line => line !== matchedLine).join('\n');
                //                 }
                //                 var noteLines =  notes.split('\n');
                //                 for (var j = 0; j < noteLines.length; j++) {
                //                         if(noteLines[j].includes(processedText)){
                //                             if(noteLines[j].includes(processedText + ' - ' + clientInf[i])) {
                //                             } else {
                //                                 noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                //                                 notes = noteLines;
                                            
                //                             }
                //                         }
                //                 }
                //             }
                //             if (annexInfMap[annexInf[i]] > 0) {
                //                 annexInfMap[annexInf[i]]--;
                //                 if (annexInfMap[annexInf[i]] === 0) {
                //                     delete annexInfMap[annexInf[i]];
                //                 }
                //             }
                        
                //         } else {
                //             if(annexInf.length > 1 && annexInf[0] == ''){
                //                 notesMap[clientInf[i]] = processedText + ' - ' + clientInf[i] + ' removed';
                //             } else if(annexInf[0] !== '') {
                //                 notesMap[clientInf[i]] = processedText + ' - ' + clientInf[i] + ' removed';
                //             } else {
                //                 var lines = notes.split('\n');
                //                 for (var j = 0; j < lines.length; j++) {
                //                     var matchedLine = lines.find(line => line.includes(processedText )); 
                //                         notes = lines.filter(line => line !== matchedLine).join('\n');
                //                 }
                //             }
                //             previousValue[clientInf[i]] = processedText + ' - ' + clientInf[i];
                        
                //         }
                //     }
                
                //     for (var key in annexInfMap) {
                //         if (annexInfMap.hasOwnProperty(key) && annexInfMap[key] > 0) {
                //             if(key) {
                //                 notesMap[key] = processedText + ' - ' + key + ' added';
                //                 var lines = notes.split('\n');
                //                 var matchedLine = lines.find(line => line.includes(notesMap[key]));
                //                 if (matchedLine) {
                //                     notes = lines.filter(line => line !== matchedLine).join('\n');
                //                 }
                //             }
                //         } 
                //     }

                //     // Convert notesMap to a single string in the order of clientInf
                //     clientInf.forEach(function (value) {
                //         if (notesMap[value]) {
                //             if (notes.includes(previousValue[value])) {
                //                 var lines = notes.split('\n');
                //                 var matchedLine = lines.find(line => line.includes(previousValue[value]));
                //                 if (matchedLine !== undefined) {
                //                     notes = notes.replace(matchedLine, notesMap[value]);
                //                 } else {
                //                     notes += '\n' + notesMap[value];
                //                 }
                //             } else {
                //                 if (notes === "") {
                //                     notes += notesMap[value];
                //                 } else {
                //                     notes += '\n' + notesMap[value];
                //                 }
                //             }
                //             delete notesMap[value];
                //         }
                //     });

                //     // Add remaining notes for new additions
                //     for (var key in notesMap) {
                //         if (notesMap.hasOwnProperty(key)) {
                //             notes += '\n' + notesMap[key];
                //         }
                //     }
                
                //     let noteLines1 = notes.trim().split('\n');
                //     let uniqueNotes = Array.from(new Set(noteLines1));
                //     let finalNotes = uniqueNotes.join('\n');
                //     $('.annex_qa_trends').val(finalNotes);
                // }

                // $('.am_cpt').on('blur', function () {
                //     handleBlurEvent('.am_cpt_hidden', '.am_cpt');
                // });

                // $('.am_icd').on('blur', function () {
                //     handleBlurEvent('.am_icd_hidden', '.am_icd');
                // });
                // function toggleCoderTrends() {
                //     var hasAMFields = $('.am_cpt').length > 0 || $('.am_icd').length > 0;
                //     if (hasAMFields) {
                //         $('.trends_div').show();
                //     } else {
                //         $('.trends_div').hide();
                //     }
                // }
                // toggleCoderTrends();
        })
        function updateTime() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();
            var startTime = new Date(startTime_db).getTime();
            var elapsedTimeMs = new Date().getTime() - startTime;
            var elapsedHours = Math.floor(elapsedTimeMs / (1000 * 60 * 60));
            var remainingMinutes = Math.floor((elapsedTimeMs % (1000 * 60 * 60)) / (1000 * 60));
            elapsedHours = (elapsedHours < 10 ? "0" : "") + elapsedHours;
            remainingMinutes = (remainingMinutes < 10 ? "0" : "") + remainingMinutes;
            document.getElementById("elapsedTime").innerHTML = elapsedHours + " : " + remainingMinutes;
            setTimeout(updateTime, 1000);
        }
       updateTime();

    </script>
@endpush
