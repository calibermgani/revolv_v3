@extends('layouts.app3')
@php
    use Carbon\Carbon;
@endphp
@section('content')
    <div class="card card-custom custom-card">
        <div class="card-body p-0">
            <form id="filterForm">
                <div class="card-header border-0 px-4" style="background-color: #139AB3;height: 84px">
                    <div class="row">
                        <div class="col-md-6">
                            <span class="project_header" style="margin-left: 4px !important;color: #ffffff;">Generate Bulk
                                Report</span>
                        </div>
                    </div>
                </div>
                <div class="card-body py-0 px-7" style="background-color: #139AB3;height: 84px">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="row form-group">
                                <div class="col-md-12">
                                    @php $projectList = App\Http\Helper\Admin\Helpers::projectList(); @endphp
                                    {!! Form::select('project_id', $projectList, $searchData['project_id'] ?? null, [
                                        'class' => 'text-black form-control select2 project_select',
                                        'id' => 'project_id',
                                        'placeholder' => 'Select Project',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="row form-group">
                                <div class="col-md-12">
                                    @if (isset($searchData['project_id']))
                                        @php $subProjectList = App\Http\Helper\Admin\Helpers::subProjectList($searchData['project_id']); @endphp
                                        {!! Form::select('sub_project_id', $subProjectList, $searchData['sub_project_id'] ?? null, [
                                            'class' => 'text-black form-control select2 sub_project_select',
                                            'id' => 'sub_project_id',
                                            'placeholder' => 'Select Sub Project',
                                        ]) !!}
                                    @else
                                        @php $subProjectList = []; @endphp
                                        {!! Form::select('sub_project_id', $subProjectList, null, [
                                            'class' => 'text-black form-control select2 sub_project_select',
                                            'id' => 'sub_project_id',
                                            'placeholder' => 'Select Sub Project',
                                        ]) !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="row form-group">
                                <div class="col-md-12">
                                    {!! Form::text('work_date', $searchData['work_date'] ?? null, [
                                        'class' => 'form-control form-control daterange',
                                        'autocomplete' => 'off',
                                        'id' => 'work_date',
                                        'placeholder' => 'mm/dd/yyyy - mm/dd/yyyy',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="row form-group">
                                <div class="col-md-12">
                                    @if (isset($searchData['project_id']))
                                        @php $userList= App\Http\Helper\Admin\Helpers::getprojectResourceList($searchData['project_id']);  @endphp
                                        {!! Form::select('user', $userList, $searchData['user'] ?? null, [
                                            'class' => 'text-black form-control select2 user_select',
                                            'id' => 'user',
                                            'placeholder' => 'User',
                                        ]) !!}
                                    @else
                                        @php $userList = []; @endphp
                                        {!! Form::select('user', $userList, $searchData['user'] ?? null, [
                                            'class' => 'text-black form-control select2 user_select',
                                            'id' => 'user',
                                            'placeholder' => 'User',
                                        ]) !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="row form-group">
                                <div class="col-md-12">
                                    {!! Form::select(
                                        'client_status',
                                        [
                                            'CE_Inprocess' => 'AR Inprocess',
                                            'CE_Pending' => 'AR Pending',
                                            'CE_Completed' => 'AR Completed',
                                            'CE_Hold' => 'AR Hold',
                                            'AR_non_workable' => 'Non Workable',
                                            'QA_Inprocess' => 'QA Inprocess',
                                            'QA_Pending' => 'QA Pending',
                                            'QA_Completed' => 'QA Completed',
                                            'QA_Hold' => 'QA Hold',
                                            'Revoke' => 'Rework',
                                        ],
                                        $searchData['client_status'] ?? null,
                                        [
                                            'class' => 'text-black form-control select2 report_client_status',
                                            'id' => 'client_status',
                                            'placeholder' => 'Status',
                                        ],
                                    ) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-footer d-flex justify-content-between align-items-center w-100">

                    <!-- Left Side: Export Button -->
                    <div id="export_div">
                        <button type="button" class="btn btn-primary-export text-white" id="bulk_export"
                            style="font-size:13px">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" fill="currentColor"
                                class="bi bi-box-arrow-up" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1z" />
                                <path fill-rule="evenodd"
                                    d="M7.646.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 1.707V10.5a.5.5 0 0 1-1 0V1.707L5.354 3.854a.5.5 0 1 1-.708-.708z" />
                            </svg>&nbsp;&nbsp;<span>Export</span>
                        </button>

                    </div>
                    <div class="pr-10">
                        <button class="btn btn-light-danger" id="filter_clear" tabindex="10" type="button">
                            <span><span>Clear</span></span>
                        </button>&nbsp;&nbsp;
                        <button type="submit" class="btn btn-white-black font-weight-bold" id="formUpdate_save">
                            Submit
                        </button>
                    </div>

                </div>
            </form>

            <div class="table-responsive pt-5 pb-5 px-4">
                <table class="table table-separate table-head-custom no-footer dtr-column " id="bulk_list"
                    data-order='[[ 0, "desc" ]]'>
                    @include('reports.partials.bulkProductionTable', [
                        'completedProjectDetails' => $completedProjectDetails,
                        'columnsHeader' => $columnsHeader,
                    ])

                </table>
            </div>

            <div id="pagination_links">
                @include('reports.partials.pagination', [
                    'completedProjectDetails' => $completedProjectDetails,
                ])
            </div>


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
                    'Last Month': [
                        moment().subtract(1, 'month').startOf('month'),
                        moment().subtract(1, 'month').endOf('month')
                    ]
                }
            });

            // Clear value initially
           // $('.daterange').val('');

            // Validate range selection
            // $('.daterange').on('apply.daterangepicker', function(ev, picker) {
            //     var diffDays = picker.endDate.diff(picker.startDate, 'days');
            //     if (diffDays > 30) {
            //         //alert('Please select a date range of 30 days or less.');
            //         js_notification('error', 'Please select a date range of 30 days or less.');
            //         $(this).val(''); // clear field if invalid
            //         return false;
            //     }
            //     $(this).val(
            //         picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY')
            //     );
            // });


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
                            sla_options = sla_options + '<option value="' + key + '">' +
                                value +
                                '</option>';
                        });
                        $("#sub_project_id").html(sla_options);
                        $("#user").val(res.resource);
                        var user_options = '<option value="">Select User</option>';
                        $.each(res.resource, function(key, value) {
                            user_options = user_options + '<option value="' + key +
                                '">' + value +
                                '</option>';
                        });
                        $("#user").html(user_options);
                        KTApp.unblock('#reportModal');
                    },
                    error: function(jqXHR, exception) {}
                });
            });



            // $('#filterForm').on('submit', function(e) {
            $('#formUpdate_save').on('click', function(e) {
                 e.preventDefault();
                if ($('#project_id').val() == '' || $('#sub_project_id').val() == '') {
                   
                    if ($('#project_id').val() == '') {
                        $('#project_id').next('.select2').find(".select2-selection").css('border-color',
                            'red');
                    } else {
                        $('#project_id').next('.select2').find(".select2-selection").css('border-color',
                        '');
                    }
                    if ($('#sub_project_id').val() == '') {
                        $('#sub_project_id').next('.select2').find(".select2-selection").css('border-color',
                            'red');
                    } else {
                        $('#sub_project_id').next('.select2').find(".select2-selection").css('border-color',
                            '');
                    }
                    return false;
                }
                var clientName = btoa($('#project_id').val());
                var subProjectName = btoa($('#sub_project_id').val());
                var formData = $('#filterForm').serialize();
                formData = formData
                    .replace(/(^|&)project_id=[^&]*/g, '')
                    .replace(/(^|&)sub_project_id=[^&]*/g, '')
                    .replace(/^&+|&+$/g, ''); // clean up any leftover "&"
                formData += '&clientName=' + clientName;
                formData += '&subProjectName=' + subProjectName;

                console.log(formData);

                $.ajax({
                    url: "{{ route('reports.bulk.columns') }}",
                    method: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        $('#bulk_list').html(res.html);
                        $('#pagination_links').html(res.pagination);

                    }
                });
            });
            $('#bulk_export').on('click', function(e) {


                if ($('#project_id').val() == '' || $('#sub_project_id').val() == '') {
                    e.preventDefault();
                    if ($('#project_id').val() == '') {
                        $('#project_id').next('.select2').find(".select2-selection").css('border-color',
                            'red');
                    } else {
                        $('#project_id').next('.select2').find(".select2-selection").css('border-color',
                        '');
                    }
                    if ($('#sub_project_id').val() == '') {
                        $('#sub_project_id').next('.select2').find(".select2-selection").css('border-color',
                            'red');
                    } else {
                        $('#sub_project_id').next('.select2').find(".select2-selection").css('border-color',
                            '');
                    }
                    return false;
                }
                var clientName = btoa($('#project_id').val());
                var subProjectName = btoa($('#sub_project_id').val());
                var formData = $('#filterForm').serialize();

                // remove project_id and sub_project_id like your submit handler
                formData = formData
                    .replace(/(^|&)project_id=[^&]*/g, '')
                    .replace(/(^|&)sub_project_id=[^&]*/g, '')
                    .replace(/^&+|&+$/g, ''); // clean up any leftover "&"

                formData += '&clientName=' + clientName;
                formData += '&subProjectName=' + subProjectName;

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                KTApp.block('#export_div', {
                    overlayColor: '#000000',
                    state: 'danger',
                    opacity: 0.1,
                    message: 'Fetching...',
                });

                $.ajax({
                    url: "{{ url('bulk_export') }}",
                    method: 'POST',
                    data: formData,
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(response, status, xhr) {


                        var filename = "";
                        var disposition = xhr.getResponseHeader('Content-Disposition');
                        if (disposition && disposition.indexOf('attachment') !== -1) {
                            var matches = /filename[^;=\n]*=([^;\n]*)/.exec(disposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].trim().replace(/^"|"$/g, '');
                            }
                        }

                        var blob = new Blob([response], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        });
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        console.log('response', response, status, xhr, filename, link.href,
                            blob);
                        console.log('filename', filename);

                        link.download = filename || 'export.xlsx';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        KTApp.unblock('#export_div');
                    },
                    error: function(xhr) {
                        console.error('Error generating Excel file', xhr);
                        KTApp.unblock('#export_div');
                    }
                });
            });
            $('#filter_clear').on('click', function(e) {
                window.location.href = baseUrl + 'reports/bulk'
                "?parent=" +
                getUrlVars()[
                        "parent"] +
                    "&child=" + getUrlVars()["child"];
            });


            var table = $("#bulk_list").DataTable({
                processing: true,
                lengthChange: false,
                clientSide: true,
                searching: true,
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
                dom: "<'row'<'col-md-12'f><'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>"
            });
            table.buttons().container()
                .appendTo('.outside');





        });
    </script>
@endpush
