@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card" id="quality_sampling">
        <div class="card-body pt-0 pb-2 pl-8" style="background-color: #ffffff !important">
            <div class="row mr-0 ml-0">
                <div class="col-6 mt-4 pt-0 pb-0 pl-0 pr-0">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <a class="project_header" href="" style="margin-left:-1.7rem">
                        <span class="svg-icon svg-icon-primary svg-icon-lg mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="16" fill="currentColor"
                                class="bi bi-arrow-left project_header_row" viewBox="0 0 16 16"
                                style="width: 1.05rem !important;color: #000000 !important;">
                                <path fill-rule="evenodd"
                                    d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                            </svg>
                        </span>Production Report</a>
                </div>
            </div>
            {!! Form::open([
                'url' => url('report/production_reports') . '?parent=' . request()->parent . '&child=' . request()->child,
                'id' => 'production_report_form',
                'class' => 'form',
                'enctype' => 'multipart/form-data',
            ]) !!}
            @csrf
            <div class="row mb-2 mt-2 mr-0 ml-0 align-items-center pt-4 pb-3"
                style="background-color: #F1F1F1;border-radius:0.42rem">
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label class="required">Project</label>
                    @php $projectList = App\Http\Helper\Admin\Helpers::projectList(); @endphp
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
                    @php $subProjectList = []; @endphp
                    <fieldset class="form-group mb-1">
                        {!! Form::select('sub_project_id', $subProjectList, $subProjectId != 0 ? $subProjectId : null, [
                            'class' => 'text-black form-control kt_select2_sub_project',
                            'id' => 'sub_project_list',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </fieldset>
                </div>
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>Work Date</label>
                    <fieldset class="form-group mb-1">
                        {!! Form::text('work_date', $workDate != 0 ? $workDate : null, [
                            'class' => 'form-control form-control daterange',
                            'autocomplete' => 'off',
                            'id' => 'work_date',
                            'placeholder' => 'mm/dd/yyyy - mm/dd/yyyy',
                        ]) !!}

                    </fieldset>
                </div>
                {{-- <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>User</label>
                    <fieldset class="form-group mb-1">
                        {!! Form::select('coder_emp_id', $coderList, null, [
                            'class' => 'form-control select2 user_select',
                            'id' => 'coder_id',
                            'style' => 'width: 100%;; background-color: #ffffff !important;',
                        ]) !!}
                    </fieldset>
                </div> --}}

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
                <table class="table table-separate table-head-custom no-footer dtr-column " id="prodcution_report_table">
                    <thead>
                        <tr>
                            <th>Emp Id</th>
                            <th>Emp Name</th>
                            <th>Project</th>
                            <th>Sub Project</th>
                            <th>Scope</th>
                            <th>Target</th>
                            <th>Per Hour target</th>
                            <th>Working Hrs</th>
                            <th>Production Count</th>
                            <th>Achieved Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($productionReportList))
                            @foreach ($productionReportList as $data)
                                @php
                                    if ($projectId != null) {
                                        $projectName = App\Models\project::where('project_id', $projectId)->first();
                                    } else {
                                        $projectName = '--';
                                    }
                                    if ($subProjectId != null && $projectId != null) {
                                        $subProjectName = App\Models\subproject::where('project_id', $projectId)
                                            ->where('sub_project_id', $subProjectId)
                                            ->first();
                                    } else {
                                        $subProjectName = '--';
                                    }
                                    // $coderName =
                                    //     $data['CE_emp_id'] != null
                                    //         ? App\Http\Helper\Admin\Helpers::getUserNameByEmpId($data['CE_emp_id'])
                                    //         : '--';
                                @endphp
                                <tr>
                                    <td>{{ $data['CE_emp_id'] }}
                                    </td>
                                    <td>{{ $data['CE_emp_id'] }}
                                    </td>
                                    <td>{{ $projectName == '--' || $projectName == null ? '--' : $projectName->aims_project_name }}
                                    </td>
                                    <td>{{ $subProjectName == '--' || $subProjectName == null ? '--' : $subProjectName->sub_project_name }}
                                    </td>
                                    <td>{{ $subProjectName == '--' || $subProjectName == null ? '--' : $subProjectName->sub_project_name }}
                                    </td>
                                    <td>--</td>
                                    <td>--</td>
                                    <td>--</td>
                                    <td>--</td>
                                    <td>--</td>

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
                    }
                });
                $('.daterange').val('');
                var work_date = @json($workDate);
                if (work_date != 0) {
                    $('.daterange').val(work_date);
                }
                var project_id = @json($projectId);
                var subproject_id = @json($subProjectId);
                $(document).on('click', '#clear_submit', function(e) {
                    project_id = 0;
                    sub_project_id = 0;
                    work_date = 0;
                    $('.daterange').val('');
                });
                console.log(subproject_id, 'subproject_id', project_id, work_date);

                if (subproject_id != 0) {
                    subProjectNameList(project_id, subproject_id);
                }
                var subprojectCount;
                $('#prodcution_report_table').DataTable({
                    processing: true,
                    lengthChange: false,
                    searching: false,
                    pageLength: 20,

                });
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
                    // var coder_id = $('#coder_id');
                    var inputTypeValue = 0;
                    if (project_id.val() == '' || sub_project_id.val() == "") {
                        if (project_id.val() == '') {
                            project_id.next('.select2').find(".select2-selection").css('border-color', 'red');
                        } else {
                            project_id.next('.select2').find(".select2-selection").css('border-color', '');
                        }
                        if (sub_project_id.val() == '' && subprojectCount != 0) {
                            sub_project_id.next('.select2').find(".select2-selection").css('border-color',
                                'red');
                        } else {
                            sub_project_id.next('.select2').find(".select2-selection").css('border-color', '');
                        }
                        return false;
                    }
                    if (inputTypeValue == 0) {
                        document.querySelector('#production_report_form').submit();
                    }
                });

            });
        </script>
    @endpush
