@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card">
        <div class="card-body pt-4 pb-0 px-2">
            <div class="my-client-div">
                <span class="project_header" style="margin-left: 4px !important">QA Project List</span>
            </div>

            <div class="table-responsive pb-4">
                <table class="table table-separate table-head-custom no-footer dtr-column " id="clients_list">
                    <thead>
                        <tr>
                            <th width="15px"></th>
                            <th>Project</th>
                            <th>Assigned</th>
                            <th>Completed</th>
                            <th>Pending</th>
                            <th>On Hold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($projects))
                            @foreach ($projects as $data)
                                @php
                                    $loginEmpId =
                                        Session::get('loginDetails') &&
                                        Session::get('loginDetails')['userDetail'] &&
                                        Session::get('loginDetails')['userDetail']['emp_id'] != null
                                            ? Session::get('loginDetails')['userDetail']['emp_id']
                                            : '';
                                    $empDesignation =
                                        Session::get('loginDetails') &&
                                        Session::get('loginDetails')['userDetail']['user_hrdetails'] &&
                                        Session::get('loginDetails')['userDetail']['user_hrdetails'][
                                            'current_designation'
                                        ] != null
                                            ? Session::get('loginDetails')['userDetail']['user_hrdetails'][
                                                'current_designation'
                                            ]
                                            : '';
                                    $projectName = App\Http\Helper\Admin\Helpers::projectName($data["id"])->project_name;//$data['client_name'];
                                    if (isset($data['subprject_name']) && !empty($data['subprject_name'])) {
                                        $subproject_name = $data['subprject_name'];
                                        $model_name = collect($subproject_name)
                                            ->map(function ($item) use ($projectName) {
                                                return Str::studly(
                                                    Str::slug(Str::lower($projectName) . '_' . Str::lower($item), '_'),
                                                );
                                            })
                                            ->all();
                                    } else {
                                        $model_name = collect(
                                            Str::studly(
                                                Str::slug(
                                                    Str::lower($projectName) . '_project',
                                                    '_',
                                                ),
                                            ),
                                        );
                                    }

                                    $assignedTotalCount = 0;
                                    $completedTotalCount = 0;
                                    $pendingTotalCount = 0;
                                    $holdTotalCount = 0;$startDate = Carbon\Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon\Carbon::now()->endOfDay()->toDateTimeString();
                                    foreach ($model_name as $model) {
                                        $modelClass = 'App\\Models\\' . $model;
                                        $assignedCount = 0;
                                        $completedCount = 0;
                                        $pendingCount = 0;
                                        $holdCount = 0;
                                        if (
                                            $loginEmpId &&
                                            ($loginEmpId == 'Admin' ||
                                                strpos($empDesignation, 'Manager') !== false ||
                                                strpos($empDesignation, 'VP') !== false ||
                                                strpos($empDesignation, 'Leader') !== false ||
                                                strpos($empDesignation, 'Team Lead') !== false ||
                                                strpos($empDesignation, 'CEO') !== false ||
                                                strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)
                                        ) {
                                            if (class_exists($modelClass)) {
                                                $assignedCount = $modelClass
                                                    ::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')
                                                    ->count();
                                                $completedCount = $modelClass
                                                    ::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])
                                                    ->count();
                                                $pendingCount = $modelClass
                                                    ::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])
                                                    ->count();
                                                $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                            } else {
                                                $assignedCount = 0;
                                                $completedCount = 0;
                                                $pendingCount = 0;
                                                $holdCount = 0;
                                            }
                                        } elseif ($loginEmpId) {
                                            if (class_exists($modelClass)) {
                                                $assignedCount = $modelClass
                                                    ::whereIn('chart_status',['CE_Completed','QA_Inprocess'])->where('qa_work_status','Sampling')
                                                    ->where('QA_emp_id', $loginEmpId)
                                                    ->count();
                                                $completedCount = $modelClass
                                                    ::where('chart_status', 'QA_Completed')
                                                    ->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])
                                                    ->count();
                                                $pendingCount = $modelClass
                                                    ::where('chart_status', 'QA_Pending')
                                                    ->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])
                                                    ->count();
                                                $holdCount = $modelClass
                                                    ::where('chart_status', 'QA_Hold')
                                                    ->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])
                                                    ->count();
                                            } else {
                                                $assignedCount = 0;
                                                $completedCount = 0;
                                                $pendingCount = 0;
                                                $holdCount = 0;
                                            }
                                        }
                                        $assignedTotalCount += $assignedCount;
                                        $completedTotalCount += $completedCount;
                                        $pendingTotalCount += $pendingCount;
                                        $holdTotalCount += $holdCount;
                                    }
                                @endphp
                                <tr class="clickable-client cursor_hand">
                                    <td class="details-control"></td>
                                    <td>{{ $data['client_name'] }} <input type="hidden" value={{ $data['id'] }}></td>
                                    <td>{{ $assignedTotalCount }}</td>
                                    <td>{{ $completedTotalCount }}</td>
                                    <td>{{ $pendingTotalCount }}</td>
                                    <td>{{ $holdTotalCount }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <div class="modal fade" id="projectReasonModal" tabindex="-1" role="dialog" aria-labelledby="projectReasonModalTitle" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content p-5">        
                <h5 class="modal-title text-center mt-2" id="exampleModalLongTitle">QA Reason Type</h5>              
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-1">
                                @php $subProjectList = []; @endphp
                            {!! Form::select(
                                'sub_project_list',
                                $subProjectList,
                                null,
                                [
                                    'class' => 'form-control  kt_select2_sub_project',
                                    'id' => 'sub_project_list',
                                ],
                            ) !!}
                        </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-1">
                                @php $projectReasonTypeList = App\Http\Helper\Admin\Helpers::qaProjectReasonTypeList(); @endphp
                                {!! Form::select(
                                    'qa_reason',
                                    $projectReasonTypeList,
                                    null,
                                    [
                                        'class' => 'form-control kt_select2_project_reason_type',
                                        'id' => 'project_reason',
                                    ],
                                ) !!}
                            </div>
                        </div>
                    </div>
                        <div class="col-md-12 mt-2">
                            <div class="form-group mb-1">
                                <textarea name="qa_others_comments" id="other_comments" rows="4" class="form-control" maxlength="250" style="display:none !important" required></textarea>
                           </div>
                        </div>
                    
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="remindMeLater">Close</button>
                    <button type="button" class="btn btn-primary font-weight-bold" id="project_reason_save">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection
<style>
    .table thead th {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }
</style>
@push('view.scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            var subProjects;
            var subprojectCountData;
            var table = $("#clients_list").DataTable({
                processing: true,
                lengthChange: false,
                searching: true,
                pageLength: 20,
                columnDefs: [{
                    className: 'details-control',
                    targets: [0],
                    orderable: false,
                }, ],
                responsive: true

            })
            table.buttons().container().appendTo('.outside');

            $('#clients_list tbody').on('click', 'td.details-control', function() {
                var client_id = $(this).closest('tr').find('td:eq(1) input').val();
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var subProjectName = '--';
                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    KTApp.block('#clients_list', {
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
                        url: "{{ url('qa_production/qa_sub_projects') }}",
                        data: {
                            project_id: client_id,
                        },
                        success: function(res) {
                            console.log(res, 'res');
                            subProjects = res.subprojects;
                            subprojectCountData = Object.keys(subProjects).length;
                            console.log(subprojectCountData, 'subprojectCountData');

                            if (typeof subprojectCountData !== 'undefined' &&
                                subprojectCountData > 0) {
                                row.child(format(row.data(), subProjects)).show();
                            } else {
                                if (typeof subprojectCountData !== 'undefined') {
                                    window.location.href = baseUrl + 'qa_production/qa_projects_assigned/' +
                                        btoa(client_id) + '/' +
                                        subProjectName + "?parent=" +
                                        getUrlVars()["parent"] + "&child=" + getUrlVars()[
                                            "child"];
                                }
                            }
                            tr.addClass('shown');
                            KTApp.unblock('#clients_list');
                        },
                        error: function(jqXHR, exception) {}
                    });

                }
            });


            function format(data, subProjects) {
                console.log(subprojectCountData, 'format');
                if (subprojectCountData > 0) {
                    var html =
                        '<table id="practice_list" class="inv_head" cellpadding="5" cellspacing="0" border="0" style="width:97%;border-radius: 10px !important;overflow: hidden;margin-left: 1.5rem;">' +
                        '<tr><th></th><th>Sub Project</th><th>Assigned</th> <th>Completed</th> <th>Pending</th><th>On Hold</th> </tr>';
                    $.each(subProjects, function(index, val) {
                        console.log(val, 'val', val.client_name, val.sub_project_name);
                        html +=
                            '<tbody><tr class="clickable-row cursor_hand">' +
                            '<td><input type="hidden" value=' + val.client_id + '></td>' +
                            '<td>' + val.sub_project_name + '<input type="hidden" value=' + val
                            .sub_project_id + '></td>' +
                            '<td>' + val.assignedCount + '</td>' +
                            '<td>' + val.CompletedCount + '</td>' +
                            '<td>' + val.PendingCount + '</td>' +
                            '<td>' + val.holdCount + '</td>' +
                            '</tr></tbody>';
                    });
                    html += '</table>';
                    return html;
                }
            }

            $(document).on('click', '.clickable-row', function(e) {
                var clientName = $(this).closest('tr').find('td:eq(0) input').val();
                var subProjectName = $(this).closest('tr').find('td:eq(1) input').val();
                if (!clientName) {
                    console.error('encodedclientname is undefined or empty');
                    return;
                }
                KTApp.block('#clients_list', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Fetching...',
                    });
                    window.location.href = baseUrl + 'qa_production/qa_projects_assigned/' + btoa(clientName) + '/' + btoa(
                            subProjectName) + "?parent=" +
                        getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
                KTApp.unblock('#clients_list');

            })
            $(document).on('click','.clickable-client td:nth-child(2)',function(e){                
                $("#projectReasonModal").modal('show');
                 project_id = $(this).closest('tr').find('td:eq(1) input').val();
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
                            sla_options += '<option value="' + key + '">' + value +
                                '</option>';
                        });
                        $("#sub_project_list").html(sla_options);
                        $('select[name="sub_project_list"]').html(sla_options);
                    },
                    error: function(jqXHR, exception) {}
                });
            });
            $('#project_reason').on('change', function() {
                var projectReason = $(this).val();
                if(projectReason == 9) {
                    $('#other_comments').css('display', 'block');
                } else {
                    $('#other_comments').css('display', 'none');
                }

            })
            var project_id;
            $('#project_reason_save').on('click', function() {
               
                var sub_project_id = $('#sub_project_list').val();
                var project_reason = $('#project_reason').val();
                var other_comments = $('#other_comments').val();console.log(sub_project_id,'sub_project_id',project_reason);
                
                 if (sub_project_id == '' || project_reason == '' || (project_reason == 9 && other_comments == '')) {
                    if (sub_project_id == '') {
                        $('#sub_project_list').next('.select2').find(".select2-selection").css('border-color', 'red');
                    } else {
                        $('#sub_project_list').next('.select2').find(".select2-selection").css('border-color', '');
                    }
                    if (project_reason == '') {
                        console.log(project_reason,'project_reason');
                        
                        $('#project_reason').next('.select2').find(".select2-selection").css('border-color', 'red');
                    } else {
                        $('#project_reason').next('.select2').find(".select2-selection").css('border-color', '');
                    }
                    if (project_reason == 9 && other_comments == '') {
                        $('#other_comments').css('border-color', 'red');
                    } else {
                        $('#other_comments').css('border-color', '');
                    }
                    return false;
                 }
                 KTApp.block('#projectReasonModal', {
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
                    url: baseUrl + "project_reason_save",
                    type: 'POST',
                    data: {
                        project_id: project_id,
                        sub_project_id:sub_project_id,
                        qa_reason:project_reason,
                        qa_others_comments: other_comments,
                    },
                    success: function(res) {
                        if (res.success == true) {
                            js_notification('success', 'Reason has been submitted');
                            KTApp.unblock('#projectReasonModal');
                            setTimeout(function() {
                                location.reload();
                           }, 500);
                        } else {
                            js_notification('error', 'Reason has been failed to submit');
                            setTimeout(function() {
                                location.reload();
                           }, 500);
                        }
                    }
                });
            });
        })
    </script>
@endpush
