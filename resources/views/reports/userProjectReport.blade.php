@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card" id="page-loader">
        <div class="card-body pt-0 pb-2 pl-8" style="background-color: #ffffff !important">
            <div class="row mr-0 ml-0">
                <div class="col-6 mt-4 pt-0 pb-0 pl-0 pr-0">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <a class="project_header" href="" style="margin-left:-1.7rem">User Project Report</a>
                </div>
            </div>
            {!! Form::open([
                'url' => url('report/user_project_report') . '?parent=' . request()->parent . '&child=' . request()->child,
                'id' => 'production_report_form',
                'class' => 'form',
                'enctype' => 'multipart/form-data',
            ]) !!}
            @csrf
            <div class="row mb-2 mt-2 mr-0 ml-0 align-items-center pt-4 pb-3"
                style="background-color: #F1F1F1;border-radius:0.42rem">
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>Work Date</label>
                    <fieldset class="form-group mb-1">
                        {{-- <input type="text" name="work_date" id="work_date" class="form-control daterange"
                            autocomplete="nope"> --}}
                        {!! Form::text('work_date',request()->input('work_date'),['class'=>'form-control daterange','id'=>'work_date']) !!}
                        <input type="hidden" name="select_date" id="select_date" value= "{{ $workDate }}"
                            autocomplete="nope">
                    </fieldset>
                </div>

                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>User</label>
                    <fieldset class="form-group mb-1">
                        @php
                            $userList = App\Http\Helper\Admin\Helpers::getUserList();
                        @endphp
                        {!! Form::select('user_name', $userList, $userName, [
                            'class' => 'form-control kt_select2_manager',
                            'id' => 'manager_name',
                            'style' => 'width: 100%;background-color: #fff !important;',
                        ]) !!}
                    </fieldset>
                </div>
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>Project</label>
                    <fieldset class="form-group mb-1">
                        @php
                            $projectList = App\Http\Helper\Admin\Helpers::projectList();
                            // $projectId = null;
                        @endphp
                        {!! Form::select('project_id', $projectList, $projectId, [
                            'class' => 'form-control kt_select2_project',
                            'id' => 'project_id',
                            'style' => 'width: 100%;background-color: #fff !important;',
                        ]) !!}
                    </fieldset>
                </div>
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>Sub Project</label>
                    <fieldset class="form-group mb-1">
                          @if (isset(request()->sub_project_id))
                                @php
                                    $subProjectList = App\Http\Helper\Admin\Helpers::subProjectList(
                                        $projectId,
                                    );
                                @endphp
                                {!! Form::select('sub_project_id', $subProjectList, $subProjectId, [
                                    'class' => 'form-control kt_select2_sub_project',
                                    'id' => 'sub_project_list'
                                ]) !!}
                                <input type="hidden" name="sub_project_id_val"
                                    value="{{ $subProjectId ?? '' }}">
                         @else
                            @php $subProjectList = []; @endphp
                                {!! Form::select('sub_project_id', $subProjectList, null, [
                                    'class' => 'form-control kt_select2_sub_project',
                                    'id' => 'sub_project_list',
                                    'style' => 'width: 100%;',
                                ]) !!}
                        @endif
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
                @php
                    use Carbon\Carbon;
                    use Carbon\CarbonPeriod;

                      if (!empty($workDate)) {
                        $workDates = explode(' - ', $workDate);
                        $startDate = date('Y-m-d', strtotime($workDates[0]));
                        $endDate = date('Y-m-d', strtotime($workDates[1]));
                     
                     } else {
                        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                        $endDate = Carbon::now()->format('Y-m-d');;
                     }


                        $period = CarbonPeriod::create($startDate, $endDate)->filter(function ($date) {
                            return !in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
                        });
                    $dates = [];
                    foreach ($period as $date) {
                        $dates[] = $date->format('Y-m-d');
                   }
                         

                @endphp

                <table class="table table-separate table-head-custom no-footer dtr-column" id="comments_report">
                    <thead>
                        <tr>
                            <th rowspan="2">Emp Id</th>
                            <th rowspan="2">Emp Name</th>
                            <th rowspan="2">Manager Name</th>
                            <th rowspan="2">Project</th>
                            <th rowspan="2">Sub Project</th>
                            @foreach ($period as $date)
                                <th colspan="2" class="text-center">{{ $date->format('m/d/Y') }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($period as $date)
                                <th>Resolv Count</th>
                                <th>AIMS Count</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($prjDetailsList) && is_array($prjDetailsList))
                            {{-- @php
                                $clientIds = array_keys($prjDetailsList);
                                $subPrjIds = array_column($prjDetailsList, 'sub_prj_id');
                            @endphp --}}
                            @foreach ($prjDetailsList as $projectDetails)
                                @foreach ($projectDetails as $project)
                                    @php                          
                                    $subProjectName = $project['prj_id'] != null && $project['sub_prj_id'] != null ? App\Http\Helper\Admin\Helpers::subProjectName($project['prj_id'], $project['sub_prj_id'])['sub_project_name'] : '--'; 
                                    $matchKey =array_keys($clientIds, $project['prj_id']);
                                    @endphp
                                    @if($subProjectName !== '--' && !empty($matchKey) && in_array($project['sub_prj_id'], $subPrjIds[$matchKey[0]]))    
                                                                            
                                        <tr>
                                            <td>{{ $project['emp_id'] }}</td>
                                            <td>{{ $project['user_name'] }}</td>
                                            <td>{{ $project['manager_name'] }}</td>
                                            <td>{{  App\Http\Helper\Admin\Helpers::projectName($project['prj_id'])['aims_project_name'] }}</td>
                                            <td>{{   $subProjectName }}</td>
                                                @foreach ($dates as $date)
                                                    @php
                                                        $aimsCount = 0;
                                                        if(!empty($project['tool_data']) && !is_null($project['tool_data'])) {                                               
                                                            foreach ($project['tool_data'] as $entry) {
                                                                if ($entry['work_date'] === $date) {
                                                                    $aimsCount = $entry['achieved'];
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        $resolvStartDate = date('Y-m-d 17:00:00', strtotime($date));
                                                        $resolvEndDate = date('Y-m-d 09:00:00', strtotime($date . ' +1 day'));
                                                        $paProject =  App\Http\Helper\Admin\Helpers::projectName($project['prj_id']);
                                                        $decodedClientName = $paProject ? $paProject->project_name : null;
                                                        $decodedsubProjectName = $project['sub_prj_id'] == null ? 'project' :($project['prj_id'] != null ? (App\Http\Helper\Admin\Helpers::subProjectName($project['prj_id'], $project['sub_prj_id']) != null ? App\Http\Helper\Admin\Helpers::subProjectName($project['prj_id'], $project['sub_prj_id'])->sub_project_name : null) : null);
                                                        $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                                                        $modelName = Str::studly($table_name);
                                                        $modelClass = "App\\Models\\" .  $modelName;
                                                        $arColumnExists = Schema::hasColumn($table_name, 'ar_at');
                                                            $hasNonNullArAt = $arColumnExists && $modelClass::whereNotNull('ar_at')->exists();
                                                            $arColumnToUse = $hasNonNullArAt ? 'ar_at' : 'updated_at'; 
                                                        $resolvCount = $modelClass::whereBetween($arColumnToUse, [$resolvStartDate, $resolvEndDate])
                                                        ->where('CE_emp_id', $project['emp_id'])
                                                        ->whereIn('chart_status', ['CE_Inprocess','CE_Pending','CE_Completed','CE_Clarification','CE_Hold','AR_non_workable','Revoke','QA_Assigned','QA_Inprocess','QA_Pending','QA_Completed','QA_Clarification','QA_Hold'])
                                                        ->count();
                                                    @endphp
                                                    <td>{{$resolvCount}}</td> {{-- Resolv Count --}}
                                                    <td>{{ $aimsCount }}</td> {{-- AIMS Count --}}
                                                @endforeach                               
                                        </tr>
                                    @endif
                                @endforeach
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
                });
                KTApp.unblock('#page-loader');
                if($("#work_date").val()!=''){
                    var result = $("#work_date").val().split('-');
                    var start = result[0];
                    var end = result[1];
                }else{
                var start = moment().startOf('month');
                var end = moment();
                }
                // var start = moment().startOf('month');
                // var end = moment();
               // $('.daterange').attr("autocomplete", "off");
                $('.daterange').daterangepicker({
                    showOn: 'both',
                    startDate: start,
                    endDate: end,
                    showDropdowns: true,
                    maxDate: moment(),
                    ranges: {
                        'Today': [moment(), moment()],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf(
                            'month')]
                    },
                    //endDate: '+0d',
                });
                //var dateRangeValue = $('#select_date').val();
                // if (!dateRangeValue) {
                //     $('.daterange').val('');
                // } else {
                //     $('.daterange').val(dateRangeValue);
                // }
                var subprojectCount;
                var table = $('#comments_report').DataTable({
                    processing: true,
                    lengthChange: false,
                    clientSide: true,
                    searching: true,
                    pageLength: 20,
                    scrollCollapse: true,
                    scrollX: true,
                    "initComplete": function(settings, json) {
                        $('body').find('.dataTables_scrollBody').addClass("scrollbar");
                        $('body').find('.dataTables_scrollBody').css("margin-top",'-0.3rem','important');
                    },
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
                    KTApp.block('#page-loader', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Generating Report...',
                    });
                    project_id = 0;
                    sub_project_id = 0;
                    work_date = 0;
                    $('.daterange').val('');
                    $('#comments_report').DataTable().destroy();
                    window.location.href = baseUrl + "report/user_project_report/" + "?parent=" +
                        getUrlVars()[
                            "parent"] + "&child=" + getUrlVars()["child"];
                });
                    $(document).on('change', '#project_id', function() {
                         $('#manager_name').val('').change(); 
                        var project_id = $(this).val();
                        KTApp.block('#formConfigAddDiv', {
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
                            url: "{{ url('sub_project_list') }}",
                            data: {
                                project_id: project_id
                            },
                            success: function(res) {
                                subprojectCount = Object.keys(res.subProject).length;
                                var myArray = res.existingSubProject;
                                var sla_options = '<option value="">-- Select --</option>';
                                $.each(res.subProject, function(key, value) {
                                    sla_options += '<option value="' + key + '" ' +                                                        
                                                        '>' + value +
                                        '</option>';
                                });
                                $("#sub_project_id").html(sla_options);
                                $('select[name="sub_project_id"]').html(sla_options);
                                KTApp.unblock('#formConfigAddDiv');
                            },
                            error: function(jqXHR, exception) {}
                        });
                    });
            });
        </script>
    @endpush
