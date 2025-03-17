@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card" id="page-loader">
        <div class="card-body pt-0 pb-2 pl-8" style="background-color: #ffffff !important">
            <div class="row mr-0 ml-0">
                <div class="col-6 mt-4 pt-0 pb-0 pl-0 pr-0">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <a class="project_header" href="" style="margin-left:-1.7rem">

                        {{-- <span class="svg-icon svg-icon-primary svg-icon-lg mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="16" fill="currentColor"
                                class="bi bi-arrow-left project_header_row" viewBox="0 0 16 16"
                                style="width: 1.05rem !important;color: #000000 !important;">
                                <path fill-rule="evenodd"
                                    d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                            </svg>
                        </span> --}}
                        Production Reasons Report</a>
                </div>
            </div>
            {!! Form::open([
                'url' =>
                    url('report/production_mgr_comments_report') . '?parent=' . request()->parent . '&child=' . request()->child,
                'id' => 'production_report_form',
                'class' => 'form',
                'enctype' => 'multipart/form-data',
            ]) !!}
            @csrf
            <div class="row mb-2 mt-2 mr-0 ml-0 align-items-center pt-4 pb-3"
                style="background-color: #F1F1F1;border-radius:0.42rem">
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>Project</label>
                    @php $projectList = App\Http\Helper\Admin\Helpers::resolvProjectList(); @endphp
                    <fieldset class="form-group mb-1">
                        {!! Form::select('project_id', $projectList, $projectId != 0 ? $projectId : null, [
                            'class' => 'form-control kt_select2_project',
                            'id' => 'project_id',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </fieldset>
                </div>
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>Subproject</label>
                    @if (isset(request()->sub_project_id))
                        @php
                            $subProjectList = App\Http\Helper\Admin\Helpers::subProjectList($projectId);
                        @endphp
                        {!! Form::select('sub_project_id', $subProjectList, $subProjectId, [
                            'class' => 'form-control kt_select2_sub_project',
                            'id' => 'sub_project_list',
                            'style' => 'width: 100%;',
                        ]) !!}
                        <input type="hidden" name="sub_project_id_val" value="{{ $projectDetails->sub_project_id ?? '' }}">
                    @else
                        @php $subProjectList = []; @endphp
                        <fieldset class="form-group mb-1">
                            {!! Form::select('sub_project_id', $subProjectList, $subProjectId != 0 ? $subProjectId : null, [
                                'class' => 'text-black form-control kt_select2_sub_project',
                                'id' => 'sub_project_list',
                                'style' => 'width: 100%;',
                            ]) !!}
                        </fieldset>
                    @endif
                </div>
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>Work Date</label>
                    <fieldset class="form-group mb-1">
                        <input type="text" name="work_date" id="work_date" class="form-control daterange"
                            autocomplete="nope">
                        <input type="hidden" name="select_date" id="select_date" value= "{{ $workDate }}"
                            autocomplete="nope">
                    </fieldset>

                </div>
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>Remarks</label>
                     <fieldset class="form-group mb-1">
                        @php $remarkStatus = [''=>'Select','with_remarks'=>'With Remarks','without_remarks'=>'Without Remarks']@endphp
                        {!! Form::select('remarks_status', $remarkStatus, $remarkStatusVal, [
                            'class' => 'form-control kt_select2_remarks',
                            'id' => 'remarks_status',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </fieldset>
                </div>
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>Reason</label>
                     <fieldset class="form-group mb-1">
                        @php $reasonType = [''=>'Select','ar_reason'=>'AR Reason','qa_reason'=>'QA Reason']@endphp
                        {!! Form::select('reason_type', $reasonType, $reasonTypeVal, [
                            'class' => 'form-control kt_select2_remarks',
                            'id' => 'reason_type',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </fieldset>
                </div>
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>Manager</label>
                     <fieldset class="form-group mb-1">
                        @php $mgrList = App\Http\Helper\Admin\Helpers::getProjectSubPrjAboveTlLevel();
                        $mgrList = ['' => 'Select'] + $mgrList;
                        @endphp
                        {!! Form::select('manager_name', $mgrList, $managerName, [
                            'class' => 'form-control kt_select2_manager',
                            'id' => 'manager_name',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </fieldset>
                </div>
                <div class="col-lg-2 mt-8">
                    <button class="btn btn-light-danger" id="clear_submit" tabindex="10" type="button">
                        <span>
                            <span>Clear</span>
                        </span>
                    </button>&nbsp;&nbsp;
                    <button type="submit" class="btn btn-white-black font-weight-bold" id="form_submit"
                        style="background-color: #139AB3">Search</button>
                </div>
            </div>
            {!! Form::close() !!}

            <div class="table-responsive pb-4">
                <table class="table table-separate table-head-custom no-footer dtr-column " id="comments_report">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Sub Project</th>
                            <th>Manager Name</th>
                            <th>AR Reason</th>
                            <th>QA Reason</th>
                            <th>Work Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            if(isset($productionMgrs)&& !empty($productionMgrs)) {
                                $productionManagers =  App\Http\Helper\Admin\Helpers::getUserNameListById($productionMgrs);
                            } else {
                                $productionManagers =  '--';
                            }
                        @endphp
                        @if (isset($productionReasons) && !empty($productionReasons))
                            @foreach ($productionReasons as $data)
                                @php
                          
                                    if ($data['project_id'] != null) {
                                        $projectName = App\Models\project::where(
                                            'project_id',
                                            $data['project_id'],
                                        )->first();
                                    } else {
                                        $projectName = '--';
                                    }
                                    if ($data['sub_project_id'] != null && $data['project_id'] != null) {
                                        $subProjectName = App\Models\subproject::where('project_id', $data['project_id'])
                                            ->where('sub_project_id', $data['sub_project_id'])
                                            ->first();
                                    } else {
                                        $subProjectName = '--';
                                    }
                                    if ($data['sub_project_id'] != null && $data['project_id'] != null) {
                                        $subProjectName = App\Models\subproject::where('project_id', $data['project_id'])
                                            ->where('sub_project_id', $data['sub_project_id'])
                                            ->first();
                                    } else {
                                        $subProjectName = '--';
                                    }
                                    if(isset($productionMgrs)&& !empty($productionMgrs)) {
                                    $reasonList = App\Models\ProjectReason::with([
                                        'project_ar_reason_type',
                                        'project_qa_reason_type',
                                    ])
                                        ->where('project_id', $data['project_id'])
                                        ->where('sub_project_id', $data['sub_project_id'])
                                        ->where('manager_id', $data['manager_id'])
                                        ->whereDate('created_at', $data['created_date']);
                                        if($startTime != "" && $endTime != ""){
                                            $reasonList = $reasonList->whereBetween('updated_at', [$startTime, $endTime])
                                            ->get();
                                        } else {
                                            $reasonList = $reasonList->get();
                                        }

                                    $arReasons = $qaReasons = [];
                                    if (count($reasonList) > 0) {
                                        foreach ($reasonList as $reasonData) {
                                            $arReason =
                                                isset($reasonData) && isset($reasonData->project_ar_reason_type)
                                                    ? $reasonData->project_ar_reason_type->reason_type
                                                    : '--';
                                            if ($reasonData->ar_others_comments != null) {
                                                $arReasons[] =
                                                    $arReason .
                                                    ' - ' .
                                                    $reasonData->ar_others_comments .
                                                    '(' .
                                                    date('m/d/Y h:i A', strtotime($reasonData->updated_at)) .
                                                    ')';
                                            } else {
                                                $arReasons[] =
                                                    $arReason != '--'
                                                        ? $arReason .
                                                            '(' .
                                                            date('m/d/Y h:i A', strtotime($reasonData->updated_at)) .
                                                            ')'
                                                        : '';
                                            }
                                            $qaReason =
                                                isset($reasonData) && isset($reasonData->project_qa_reason_type)
                                                    ? $reasonData->project_qa_reason_type->reason_type
                                                    : '--';
                                            if ($reasonData->qa_others_comments != null) {
                                                $qaReasons[] =
                                                    $qaReason .
                                                    ' - ' .
                                                    $reasonData->qa_others_comments .
                                                    '(' .
                                                    date('m/d/Y h:i A', strtotime($reasonData->updated_at)) .
                                                    ')';
                                            } else {
                                                $qaReasons[] =
                                                    $qaReason != '--'
                                                        ? $qaReason .
                                                            '(' .
                                                            date('m/d/Y h:i A', strtotime($reasonData->updated_at)) .
                                                            ')'
                                                        : '';
                                            }
                                        }
                                        $arReasonString = implode(', ', array_filter($arReasons));
                                        $qaReasonString = implode(', ', array_filter($qaReasons));
                                    } else {
                                        $arReasons[] = '--';
                                        $arReasonString = '--';
                                        $qaReasons[] = '--';
                                        $qaReasonString = '--';
                                    };
                                }else {
                                    $arReasons[] = '--';
                                        $arReasonString = '--';
                                        $qaReasons[] = '--';
                                        $qaReasonString = '--';
                                }
                                @endphp
                                <tr>
                                    <td>{{ $projectName ? $projectName->aims_project_name : '--' }}</td>
                                    <td>  {{ $subProjectName && $subProjectName != '--' ? $subProjectName->sub_project_name  :'--' }}</td>
                                    <td>{{$productionManagers !== '--' ? $productionManagers[$data->manager_id] : $data['scope_manager']}}</td>
                                    {{-- <td>{{ App\Http\Helper\Admin\Helpers::getUserNameById($data->manager_id) }}</td> --}}
                                    <td>{{ is_string($arReasonString) ? trim($arReasonString, ',') : implode(', ', (array) $arReasonString) }}
                                    </td>
                                    <td>{{ is_string($qaReasonString) ? trim($qaReasonString, ',') : implode(', ', (array) $qaReasonString) }}
                                    </td>
                                    <td>{{$data && isset($data['created_date']) ? date('m/d/Y',strtotime($data['created_date'])) : '--'}}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
    @endsection
    @push('view.scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script>
            $(document).ready(function() {
                KTApp.block('#page-loader', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Fetching...',
                    }); KTApp.unblock('#page-loader');
                var start = moment().startOf('month');
                var end = moment().endOf('month');
                $('.daterange').attr("autocomplete", "off");
                $('.daterange').daterangepicker({
                    showOn: 'both',
                    startDate: start,
                    endDate: end,
                    showDropdowns: true,
                    ranges: {
                        'Today': [moment(), moment()],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf(
                            'month')]
                    },
                    endDate: '+0d',
                });
                var dateRangeValue = $('#select_date').val();
                if (!dateRangeValue) {
                    $('.daterange').val('');
                } else {
                    $('.daterange').val(dateRangeValue);
                }
                var subprojectCount;
                var table = $('#comments_report').DataTable({
                    processing: true,
                    lengthChange: false,
                    clientSide: true,
                    searching: true,
                    pageLength: 20,
                    order: [
                        [0, 'desc']
                    ],
                    language: {
                        "search": '',
                        "searchPlaceholder": "   Search",
                    },

                    buttons: [{
                        "extend": 'excel',
                        "text": `<span data-dismiss="modal" data-toggle="tooltip" data-placement="left" data-original-title="Export" style="font-size:13px"> <svg xmlns="http://www.w3.org/2000/svg" width="14" height="12" fill="currentColor" class="bi bi-box-arrow-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1z"/><path fill-rule="evenodd" d="M7.646.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 1.707V10.5a.5.5 0 0 1-1 0V1.707L5.354 3.854a.5.5 0 1 1-.708-.708z"/>
                                                        </svg>&nbsp;&nbsp;&nbsp;<span>Export</span></span>`,
                        "className": 'btn btn-primary-export text-white',
                        "title": 'Remarks Report',
                        "filename": 'Manger_comments_report',
                    }],
                    dom: "<'row'<'col-md-6 text-left'f><'col-md-6 text-right'B>>" +
                        "<'row'<'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>",
                    columnDefs: [{
                        targets: [0], // Assuming the date column is the first column (index 0)
                        type: 'date', // Treat it as a date type column
                    }]
                })
                table.buttons().container().appendTo($('.dataTables_wrapper .col-md-6.text-right'));
                $(document).on('change', '#project_id', function() {
                    var project_id = $(this).val();
                    var subproject_id = '';
                    KTApp.block('#production_report_form', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Fetching...',
                    });
                    subProjectNameList(project_id, subproject_id);
                    KTApp.unblock('#production_report_form');
                });

                function subProjectNameList(project_id, subproject_id) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: "GET",
                        url: "{{ url('sub_project_list') }}",
                        data: {
                            project_id: project_id
                        },
                        success: function(res) {
                            subprojectCount = Object.keys(res.subProject).length;
                            var myArray = res.existingSubProject;
                            var sla_options = '<option value="">-- Select --</option>';
                            $.each(res.subProject, function(key, value) {
                                sla_options += '<option value="' + key + '"' + (key ===
                                        subproject_id ? 'selected="selected"' : '') + '>' + value +
                                    '</option>';

                            });
                            $('select[name="sub_project_id"]').html(sla_options);
                        },
                        error: function(jqXHR, exception) {}
                    });
                };

                $(document).on('click', '#form_submit', function(e) {
                    e.preventDefault();
                    var project_id = $('#project_id');
                    var sub_project_id = $('#sub_project_list');
                    var inputTypeValue = 0;
                    // if (project_id.val() == '' || sub_project_id.val() == "") {
                    //     if (project_id.val() == '') {
                    //         project_id.next('.select2').find(".select2-selection").css('border-color', 'red');
                    //     } else {
                    //         project_id.next('.select2').find(".select2-selection").css('border-color', '');
                    //     }
                    //     if (sub_project_id.val() == '' && subprojectCount != 0) {
                    //         sub_project_id.next('.select2').find(".select2-selection").css('border-color',
                    //             'red');
                    //     } else {
                    //         sub_project_id.next('.select2').find(".select2-selection").css('border-color', '');
                    //     }
                    //     return false;
                    // }
                    if (inputTypeValue == 0) {
                        document.querySelector('#production_report_form').submit();
                    }
                    KTApp.block('#page-loader', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Generating Report...',
                    });
                });
                $(document).on('click', '#clear_submit', function(e) {
                    project_id = 0;
                    sub_project_id = 0;
                    work_date = 0;
                    $('.daterange').val('');
                    $('#comments_report').DataTable().destroy();
                    window.location.href = baseUrl + "report/production_mgr_comments_report/" + "?parent=" +
                        getUrlVars()[
                            "parent"] + "&child=" + getUrlVars()["child"];
                });
            });
        </script>
    @endpush
