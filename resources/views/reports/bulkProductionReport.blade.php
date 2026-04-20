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
                                        @php $subProjectList = App\Http\Helper\Admin\Helpers::resolvSubProjectList($searchData['project_id']); @endphp
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
                <div class="form-footer d-flex justify-content-end align-items-center w-100">
                    <div class="pr-10">
                        <button class="btn btn-light-danger" id="filter_clear" tabindex="10" type="button">
                            <span>Clear</span>
                        </button>&nbsp;&nbsp;
                        <button type="submit" class="btn btn-white-black font-weight-bold" id="formUpdate_save">
                            Export Excel
                        </button>
                    </div>
                </div>
            </form>
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
            $('.daterange').val('');

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
                    url: "{{ url('reports/get_resolv_sub_projectList_details') }}",
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

            $('#filter_clear').on('click', function(e) {
                window.location.href = baseUrl + 'reports/bulk'
                "?parent=" +
                getUrlVars()[
                        "parent"] +
                    "&child=" + getUrlVars()["child"];
            });

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
                // Read values from inputs
                var clientName = $('#project_id').val();
                var subProjectName = $('#sub_project_id').val();

                // Build payload manually to avoid duplicates
                var formData = {
                    project_id: clientName,
                    sub_project_id: subProjectName
                };

                // Include other form fields if needed (that are not already in formData)
                $('#filterForm').serializeArray().forEach(function(item) {
                    if (!formData.hasOwnProperty(item.name)) {
                        formData[item.name] = item.value;
                    }
                });

                $.ajax({
                    url: "{{ url('run-python') }}",
                    method: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(response, status, xhr) {
                        var disposition = xhr.getResponseHeader('Content-Disposition');
                        var filename = 'export.xlsx';

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
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    },
                    error: function(err) {
                        console.error(err);
                        alert("Failed to export Excel");
                    }
                });
            });

        });
    </script>
@endpush
