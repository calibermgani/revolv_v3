@extends('layouts.app3')
@section('content')
<div class="card card-custom custom-card" id="generateReportClass" style = "height:100%">
    <div class="card-body py-2 px-2">
        <div class="d-flex justify-content-between align-items-center m-2">
            <span class="project_header">Report</span>
            <div>
                <button class="btn1" id="reportModalBtn" style="width: 171px;">
                    <img src="{{ asset('assets/svg/generate_report.svg') }}">&nbsp;&nbsp;<strong>Generate Report</strong>
                </button>
            </div>
        </div>
        <div class="text-center" style="height:100%">
            <div>
                <img src="{{ asset('assets/svg/green_human_image.svg') }}">
                <p style="margin-top: 30px">Click Generate report to get response</p>
            </div>
        </div>
    </div>
</div>
<div class="card card-custom custom-card" style="display: none" id="listData">
    <div class="card-body  px-4">
        <div class="card-header border-0 px-4">
            <div class="row">
                <div class="col-md-6">
                    <a href="#"><span class="svg-icon svg-icon-primary svg-icon-lg ">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="16" fill="currentColor"
                            class="bi bi-arrow-left project_header_row" viewBox="0 0 16 16"
                            style="width: 1.05rem !important;color: #000000 !important;margin-left: 4px !important;" onClick="window.location.reload();">
                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                        </svg>
                    </span></a>
                    <span class="project_header" style="margin-left: 4px !important;">Report List</span>
                </div>
            </div>
        </div>
        <div class="table-responsive pt-5 pb-5" id="reportTable">
        </div>
        <a id="downloadBtn" href="#" class="btn btn-success" style="display:none">
   Download Report
</a>
    </div>
</div>
<!-- Modal content-->
<div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #139AB3;height: 84px">
                <h5 class="modal-title" id="modalLabel" style="color: #ffffff;" >Generate report</h5>
                <button type="button" class="close comment_close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body" style="background-color: #139AB3;height: 84px">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="row form-group">
                            <div class="col-md-12">
                                @php $projectList = App\Http\Helper\Admin\Helpers::projectList(); @endphp
                                {!! Form::select('project_id', $projectList, request()->project_id,
                                    ['class' => 'text-black form-control select2 project_select', 'id' => 'project_id', 'placeholder'=> 'Select Project']
                                ) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="row form-group">
                            <div class="col-md-12">
                                @if (isset(request()->project_id))
                                    @php $subProjectList = App\Http\Helper\Admin\Helpers::subProjectList(request()->project_id); @endphp
                                    {!! Form::select('sub_project_id', $subProjectList, request()->sub_project_id,
                                        ['class' => 'text-black form-control select2 sub_project_select', 'id' => 'sub_project_id', 'placeholder'=> 'Select Sub Project']
                                    ) !!}
                                @else
                                    @php $subProjectList = []; @endphp
                                    {!! Form::select('sub_project_id', $subProjectList, null,
                                        ['class' => 'text-black form-control select2 sub_project_select', 'id' => 'sub_project_id', 'placeholder'=> 'Select Sub Project']
                                    ) !!}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="row form-group">
                            <div class="col-md-12">
                                {!!Form::text('wfcall_completed_date', null,
                                ['class'=>'form-control form-control daterange','autocomplete'=>'off','id' => 'work_date', 'placeholder'=> 'mm/dd/yyyy - mm/dd/yyyy']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="row form-group">
                            <div class="col-md-12">
                                    @php $userList = []; @endphp
                                    {!! Form::select('user', $userList, null,
                                        ['class' => 'text-black form-control select2 user_select', 'id' => 'user', 'placeholder'=> 'User']
                                    ) !!}
                                {{-- @endif --}}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="row form-group">
                            <div class="col-md-12">
                                {!! Form::select(
                                    'client_status',
                                    ['CE_Inprocess' => 'AR Inprocess','CE_Pending' => 'AR Pending','CE_Completed' => 'AR Completed','CE_Hold' => 'AR Hold','AR_non_workable'=>'Non Workable',
                                    'QA_Inprocess' => 'QA Inprocess','QA_Pending' => 'QA Pending','QA_Completed' => 'QA Completed','QA_Hold' => 'QA Hold','Revoke' => 'Rework'],null,
                                    ['class' => 'text-black form-control select2 report_client_status', 'id' => 'client_status', 'placeholder'=> 'Status']
                                ) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body m-10" id="project_assign_body">
                <p style="text-align: center">Select Projects to Generate Report</p>
            </div>
            <div class="modal-body m-10" id="no_data" style="display: none">
                <p style="text-align: center">No Data Available</p>
            </div>
            <div class="modal-body m-5" id="headers_modal" style="display: none">
                <div class="row" id="headers_row">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-danger" data-dismiss="modal">Close</button>
                <button type="submit" class="btn1" id="project_assign_save">Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal content End-->
@endsection
<style>
    .table thead th {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }
    .leave_color {
        background: #ff00000f;
    }

    .border-none {
        border: none !important
    }


    .table.table-separate .inv_lft th:last-child,
    .table.table-separate td:last-child {
        padding-right: 10 !important;
        }
</style>
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
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
                        'month')]
                }
            });
            $('.daterange').val('');

            $(document).on('click', '#reportModalBtn', function(e) {
                $('#reportModal').modal('show');
                $('#project_assign_body').show();
                  $('#no_data').hide();
                $('#headers_modal').hide();
            });

            $(document).on('change', '#project_id', function() {
                KTApp.block('#reportModal', {
                    overlayColor: '#000000',
                    state: 'danger',
                    opacity: 0.1,
                    message: 'Fetching...',
                });
                var project_id = $(this).val();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "{{ url('reports/get_sub_projects') }}",
                    data: {
                        project_id: project_id
                    },
                    success: function(res) {
                         $("#sub_project_id").val(res.subProject);
                        var sla_options = '<option value="">-- Select --</option>';
                        $.each(res.subProject, function(key, value) {
                            sla_options = sla_options + '<option value="' + key + '">' + value +
                                '</option>';
                        });
                        $("#sub_project_id").html(sla_options);
                        $("#user").val(res.resource);
                        var user_options = '<option value="">Select User</option>';
                        $.each(res.resource, function(key, value) {
                            user_options = user_options + '<option value="' + key + '">' + value +
                                '</option>';
                        });
                        $("#user").html(user_options);
                        KTApp.unblock('#reportModal');
                    },
                    error: function(jqXHR, exception) {}
                });
            });

            function getColumnsheaders() {
                var project_id = $('#project_id').val();
                var sub_project_id = $('#sub_project_id').val();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: baseUrl + 'reports/report_client_assigned_tab',
                    data: {
                        project_id: project_id,
                        sub_project_id: sub_project_id
                    },
                    success: function(res) {
                        if (res.columnsHeader != '') {
                            $('#exampleModalCenterTitle').hide();
                            $('#project_assign_body').hide();
                            $('#no_data').hide();
                            $('#headers_modal').show();
                            var columns = res.columnsHeader;
                            var $modalRow = $('#headers_row');
                            $modalRow.empty();
                            var $selectAllCheckbox = $('<div class="col-md-3 my-3 header_columns"><div class="checkbox-inline"><label class="checkbox checkbox-primary"><input type="checkbox" value="all" id="select_all_columns" checked>Select All<span></span></label></div></div>');
                            $modalRow.append($selectAllCheckbox);
                            $.each(columns, function(index, columnName) {
                                if (columnName !== 'id') {
                                    if(columnName === "chart_status") {
                                        var displayName = "Charge Status";
                                    }  else if(columnName === "CE_emp_id") {
                                        var displayName = "AR Emp Id";
                                    } else if(columnName === "ce_hold_reason") {
                                        var displayName = "AR Hold Reason";
                                    } else if(columnName === "coder_work_date") {
                                        var displayName = "AR Work Date";
                                    } else if(columnName === "coder_rework_status") {
                                        var displayName = "AR Rework Status";
                                    } else if(columnName === "coder_rework_reason") {
                                        var displayName = "AR Rework Reason";
                                    } else if(columnName === "coder_error_count") {
                                        var displayName = "AR Error Count";
                                    } else if(columnName === "ar_status_code") {
                                        var displayName = "Status Code";
                                    } else if(columnName === "ar_action_code") {
                                        var displayName = "Action Code";
                                    } else if(columnName === "ar_denial_codes") {
                                        var displayName = "Denial Code";
                                    }
                                     else {console.log(columnName,'if else');
                                        var displayName = columnName.split('_').map(function(word) {
                                            return word.charAt(0).toUpperCase() + word.slice(1);
                                        }).join(' ');
                                   }
                                    var $checkbox = $('<div class="col-md-3 my-3 header_columns"><div class="checkbox-inline"><label class="checkbox checkbox-primary"><input type="checkbox" name="project_columns" value="' + columnName + '" checked>' + displayName + '<span></span></label></div></div>');
                                    $modalRow.append($checkbox);
                                }
                            });
                            $('#select_all_columns').change(function() {
                                var isChecked = $(this).prop('checked');
                                $('input[name="project_columns"]').prop('checked', isChecked);
                            });
                            $('input[name="project_columns"]').change(function() {
                                var allChecked = $('input[name="project_columns"]:checked').length === $('input[name="project_columns"]').length;
                                $('#select_all_columns').prop('checked', allChecked);
                            });
                        } else {
                            $('#no_data').show();
                            $('#project_assign_body').hide();
                            $('#headers_modal').hide();
                        }
                    },
                    error: function(jqXHR, exception) {
                        console.error(jqXHR.responseText);
                    }
                });
            }

            $("#project_id").on('change', function() {
                getColumnsheaders();
            });

            $("#sub_project_id").on('change', function() {
                getColumnsheaders();
            });

            $('#reportModal').on('hidden.bs.modal', function () {
                $('#project_id').val('').change();
                $('#sub_project_id').val('').change();
                $('.daterange').val('');
            });         
               
            // $(document).on('click', '#project_assign_save', function () {
            //         var project_id = $('#project_id').val();
            //         var sub_project_id = $('#sub_project_id').val();
            //         var work_date = $('#work_date').val();
            //         var client_status = $('#client_status').val();
            //         var user = $('#user').val();
            //         var checkedValues = [];

            //         $('.header_columns').find('input[type="checkbox"]:checked').each(function () {
            //             checkedValues.push($(this).val());
            //         });

            //         KTApp.block('#reportModal', {
            //             overlayColor: '#000000',
            //             state: 'danger',
            //             opacity: 0.1,
            //             message: 'Processing started...',
            //         });

            //         $.ajax({
            //             type: "POST",
            //             url: "{{ url('reports/project_report_tracking') }}",
            //             data: {
            //                 project_id: project_id,
            //                 sub_project_id: sub_project_id,
            //                 work_date: work_date,
            //                 client_status: client_status,
            //                 user: user,
            //                 checkedValues: checkedValues,
            //                 _token: $('meta[name="csrf-token"]').attr('content')
            //             },
            //             success: function (res) {
            //                 if (res.success) {
            //                     // Polling every 5 seconds for job completion
            //                     var interval = setInterval(function () {
            //                         $.get("{{ url('reports/project_report_tracking_status') }}/" + project_id + "/" + sub_project_id,
            //                             function (statusRes) {
            //                                 if (statusRes.status === "End") {
            //                                     clearInterval(interval);

            //                                     // Instead of fetching body_info HTML, prepare download link
            //                                     var downloadUrl = "{{ url('reports/report_client_bulk_columns_list') }}?" + $.param({
            //                                         project_id: project_id,
            //                                         sub_project_id: sub_project_id,
            //                                         work_date: work_date,
            //                                         client_status: client_status,
            //                                         user: user,
            //                                         checkedValues: checkedValues
            //                                     });

            //                                     $('#reportModal').modal('hide');
            //                                     $('#generateReportClass').hide();
            //                                     $('#listData').show();

            //                                     // Show download button
            //                                     $('#reportTable').html(
            //                                         '<a href="' + downloadUrl + '" class="btn btn-success" target="_blank">Download Excel Report</a>'
            //                                     );
            //                                 }
            //                             });
            //                     }, 5000); // poll every 5 seconds

            //                     KTApp.unblock('#reportModal');
            //                 }
            //             }
            //         });
            // });
            $(document).on('click', '#project_assign_save', function () {
                var project_id = $('#project_id').val();
                var sub_project_id = $('#sub_project_id').val();
                var work_date = $('#work_date').val();
                var client_status = $('#client_status').val();
                var user = $('#user').val();
                var checkedValues = [];

                $('.header_columns').find('input[type="checkbox"]:checked').each(function () {
                    checkedValues.push($(this).val());
                });

                KTApp.block('#reportModal', {
                    overlayColor: '#000000',
                    state: 'danger',
                    opacity: 0.1,
                    message: 'Processing started...',
                });

                $.ajax({
                    type: "POST",
                    url: "{{ url('reports/project_report_tracking') }}",
                    data: {
                        project_id: project_id,
                        sub_project_id: sub_project_id,
                        work_date: work_date,
                        client_status: client_status,
                        user: user,
                        checkedValues: checkedValues,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if (res.success) {
                            // Polling every 5 seconds for job completion
                            var interval = setInterval(function () {
                                $.get("{{ url('reports/project_report_tracking_status') }}/" + project_id + "/" + sub_project_id,
                                    function (statusRes) {
                                        if (statusRes.status === "End") {
                                            clearInterval(interval);

                                            // Build download URL
                                            var downloadUrl = "{{ url('reports/report_client_bulk_columns_list') }}?" + $.param({
                                                project_id: project_id,
                                                sub_project_id: sub_project_id,
                                                work_date: work_date,
                                                client_status: client_status,
                                                user: user,
                                                checkedValues: checkedValues
                                            });

                                            // Hide modal
                                            $('#reportModal').modal('hide');
                                            $('#generateReportClass').show();
                                        // $('#listData').show();

                                            // Trigger download automatically
                                            var link = document.createElement('a');
                                            link.href = downloadUrl;console.log(link.href,'link.href');
                                            
                                            link.download = ''; // let server define filename
                                            document.body.appendChild(link);
                                            link.click();
                                            document.body.removeChild(link);

                                            KTApp.unblock('#reportModal');
                                        }
                                    });
                            }, 5000); // poll every 5 seconds
                        }
                    }
                });
           });



        });
    </script>
@endpush
