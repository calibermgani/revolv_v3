@extends('layouts.app3')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid p-0">
                <div class="card card-custom custom-card">
                    <div class="card-body p-0">
                        @php
                             $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                             $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                        @endphp
                          <div class="card-header border-0 px-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        {{-- <span class="svg-icon svg-icon-primary svg-icon-lg ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="16" fill="currentColor"
                                                class="bi bi-arrow-left project_header_row" viewBox="0 0 16 16"
                                                style="width: 1.05rem !important;color: #000000 !important;margin-left: 4px !important;">
                                                <path fill-rule="evenodd"
                                                    d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                                            </svg>
                                        </span> --}}
                                        <span class="project_header" style="margin-left: 4px !important;">Practice List</span>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row" style="justify-content: flex-end;margin-right:1.4rem">
                                            <select id="statusDropdown" class="form-control col-md-2" disabled>
                                            <option value="">--select--</option>
                                            <option value="agree">Agree</option>
                                            <option value="dis_agree">Dis Agree</option>
                                            </select> &nbsp;&nbsp;
                                            <div class="outside" href="javascript:void(0);"></div>
                                        </div>
                                    </div>
                              </div>
                            </div>
                            <div class="wizard wizard-4 custom-wizard" id="kt_wizard_v4" data-wizard-state="step-first"
                                data-wizard-clickable="true" style="margin-top:-2rem !important">
                                <div class="wizard-nav">
                                    <div class="wizard-steps">
                                        <!--begin:: Tab Menu View -->
                                        <div class="wizard-step mb-0 one" data-wizard-type="done">
                                            <div class="wizard-wrapper py-2">
                                                <div class="wizard-label p-2 mt-2">
                                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                                        <h6 style="margin-right: 5px;">Assigned</h6>
                                                        {{-- <div class="rounded-circle code-badge-tab">
                                                            {{ $assignedCount }}
                                                        </div> --}}
                                                        @include('CountVar.countRectangle', ['count' => $assignedCount])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)
                                            <div class="wizard-step mb-0 seven" data-wizard-type="done">
                                                <div class="wizard-wrapper py-2">
                                                    <div class="wizard-label p-2 mt-2">
                                                        <div class="wizard-title" style="display: flex; align-items: center;">
                                                            <h6 style="margin-right: 5px;">UnAssigned</h6>
                                                            @include('CountVar.countRectangle', ['count' => $unAssignedCount])
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
                                                        {{-- <div class="rounded-circle code-badge-tab">
                                                            {{ $pendingCount }}
                                                        </div> --}}
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
                                                        {{-- <div class="rounded-circle code-badge-tab">
                                                            {{ $holdCount }}
                                                        </div> --}}
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
                                                        {{-- <div class="rounded-circle code-badge-tab">
                                                            {{ $reworkCount }}
                                                        </div> --}}
                                                        @include('CountVar.countRectangle', ['count' => $reworkCount])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)
                                            <div class="wizard-step mb-0 six" data-wizard-type="step">
                                                <div class="wizard-wrapper py-2">
                                                    <div class="wizard-label p-2 mt-2">
                                                        <div class="wizard-title" style="display: flex; align-items: center;">
                                                            <h6 style="margin-right: 5px;">Duplicate</h6>
                                                            {{-- <div class="rounded-circle code-badge-tab-selected">
                                                                {{ $duplicateCount }}
                                                            </div> --}}
                                                            @include('CountVar.countRectangle', ['count' => $duplicateCount])
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        <div class="card card-custom custom-top-border">
                            <div class="card-body py-0 px-7">
                                {{-- <input type="hidden" value={{ $databaseConnection }} id="dbConnection">
                                <input type="hidden" value={{ $encodedId }} id="encodeddbConnection"> --}}
                                <input type="hidden" value={{ $clientName }} id="clientName">
                                <input type="hidden" value={{ $subProjectName }} id="subProjectName">
                                {{-- <div class="d-flex justify-content-between align-items-center">
                                    <select class="form-control col-md-1"
                                        disabled>
                                        <option value="">--select--</option>
                                        <option value="agree">Agree</option>
                                        <option value="dis_agree">Dis Agree</option>
                                    </select>
                                </div> --}}
                                {{-- <div class="form-group row" style="margin-left: 25rem;margin-bottom: -5rem;">
                                    <select id="statusDropdown" class="form-control col-md-1" style="margin-bottom: 1rem;"
                                    disabled>
                                    <option value="">--select--</option>
                                    <option value="agree">Agree</option>
                                    <option value="dis_agree">Dis Agree</option>
                                </select>
                                </div> --}}
                                <div class="table-responsive pt-5 pb-5 clietnts_table">
                                    <table class="table table-separate table-head-custom no-footer dtr-column "
                                        id="client_duplicate_list" data-order='[[ 0, "desc" ]]'>
                                        <thead>

                                            <tr>
                                                @if ($duplicateProjectDetails->contains('key', 'value'))
                                                    @foreach ($duplicateProjectDetails[0]->getAttributes() as $columnName => $columnValue)
                                                        @php
                                                            $columnsToExclude =  ['id','QA_emp_id','duplicate_status','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date','updated_at','created_at', 'deleted_at'];
                                                        @endphp
                                                          <th class='notexport' style="color:white !important"><input type="checkbox" id="ckbCheckAll"></th>
                                                        @if (!in_array($columnName, $columnsToExclude))
                                                            <th><input type="hideen"
                                                                    value={{ $columnValue }}>{{ str_replace(['_', '_or_'], [' ', '/'], ucwords(str_replace('_', ' ', $columnValue))) }}
                                                            </th>
                                                        @endif
                                                    @endforeach
                                                @else
                                                <th class='notexport'><input type="checkbox" id="ckbCheckAll"></th>
                                                    @foreach ($columnsHeader as $columnName => $columnValue)
                                                        <th><input type="hidden"
                                                                value={{ $columnValue }}>
                                                            {{ ucwords(str_replace(['_or_', '_'], ['/', ' '], $columnValue)) }}
                                                        </th>
                                                    @endforeach
                                                @endif
                                            </tr>


                                        </thead>

                                        <tbody>
                                            @if (isset($duplicateProjectDetails))
                                                @foreach ($duplicateProjectDetails as $data)
                                                    <tr>
                                                        <td><input type="checkbox" class="checkBoxClass" name='check[]' value="{{$data->id}}">
                                                        </td>
                                                        @foreach ($data->getAttributes() as $columnName => $columnValue)
                                                            @php
                                                                  $columnsToExclude =  ['id','QA_emp_id','duplicate_status','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date','updated_at','created_at', 'deleted_at'];
                                                           @endphp
                                                            @if (!in_array($columnName, $columnsToExclude))

                                                                <td  style="max-width: 300px;white-space: normal;">
                                                                    @if (str_contains($columnValue, '-') && strtotime($columnValue))
                                                                         {{ date('m/d/Y', strtotime($columnValue)) }}
                                                                    @else
                                                                        @if ($columnName == 'chart_status' && str_contains($columnValue, 'CE_'))
                                                                            {{ str_replace('CE_', '', $columnValue) }}
                                                                        @else
                                                                            {{ $columnValue }}
                                                                        @endif
                                                                @endif
                                                                </td>
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
                </div>
            </div>
        </div>
    </div>
@endsection
<style>
    /* Increase modal width */
    #myModal_status .modal-dialog {
        max-width: 800px;
        /* Adjust the width as needed */
    }

    /* Style for labels */
    #myModal_status .modal-body label {
        margin-bottom: 5px;
    }

    /* Style for textboxes */
    #myModal_status .modal-body input[type="text"] {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
    }

    /* .dt-buttons {
    position: absolute;
    top: 0;
    right: 0;
    z-index: 1000;
  } */


    .dropdown-item.active {
        color: #ffffff;
        text-decoration: none;
        background-color: #888a91;
    }
</style>
@push('view.scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
                var d = new Date();
                var month = d.getMonth() + 1;
                var day = d.getDate();
                var date = (month < 10 ? '0' : '') + month + '-' +
                    (day < 10 ? '0' : '') + day + '-' + d.getFullYear();
            var table = $("#client_duplicate_list").DataTable({
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
                columnDefs: [{

                    targets: [0],
                    orderable: false,
                }, ],
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
                    "filename": 'procode_duplicate_'+date,
                    "exportOptions": {
                        "columns": ':not(.notexport)'// Exclude first two columns
                    }
                }],
                dom: "B<'row'<'col-md-12'f><'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>"
            })
            table.buttons().container()
                .appendTo('.outside');
                $('.dataTables_filter').addClass('pull-left');

            // var encodedProjectId = $('#encodeddbConnection').val();
            var clientName = $('#clientName').val();
            var subProjectName = $('#subProjectName').val();

            $(document).on('click', '.one', function() {
                window.location.href = baseUrl + 'projects_assigned/' + clientName + '/' + subProjectName +
                    "?parent=" + getUrlVars()[
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
                    getUrlVars()[
                        "parent"] +
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
                window.location.href = "{{ url('#') }}";
            })
            $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
                localStorage.setItem('activeTab', $(e.target).attr('href'));
            });
            $(document).on('click', '.seven', function() {
                window.location.href = baseUrl + 'projects_unassigned/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })

            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                $('#myTab a[href="' + activeTab + '"]').tab('show');
            }

            $("#ckbCheckAll").click(function() {
                $(".checkBoxClass").prop('checked', $(this).prop('checked'));
                console.log($(this).prop('checked'), $(".checkBoxClass").length, 'log');
                if ($(this).prop('checked') == true && $('.checkBoxClass:checked').length > 0) {
                    $('#statusDropdown').prop('disabled', false);
                } else {
                    $('#statusDropdown').prop('disabled', true);

                }
            });
            $('.checkBoxClass').change(function() {
                var anyCheckboxChecked = $('.checkBoxClass:checked').length > 0;
                var allCheckboxesChecked = $('.checkBoxClass:checked').length === $('.checkBoxClass')
                    .length;
                if (allCheckboxesChecked) {
                    $("#ckbCheckAll").prop('checked', $(this).prop('checked'));
                } else {
                    $("#ckbCheckAll").prop('checked', false);
                }
                $('#statusDropdown').prop('disabled', !(anyCheckboxChecked || allCheckboxesChecked));
            });
            $('#statusDropdown').change(function() {
                dropdownStatus = $(this).val();
                checkedRowValues = $("input[name='check[]']").serializeArray();
                dbConn = $('#dbConnection').val();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content')
                    }
                });
                $.ajax({
                    url: "{{ url('clients_duplicate_status') }}",
                    method: 'POST',
                    data: {
                        // dbConn: dbConn,
                        clientName: clientName,
                        subProjectName: subProjectName,
                        dropdownStatus: dropdownStatus,
                        checkedRowValues: checkedRowValues
                    },
                    success: function(response) {
                        location.reload();
                    },

                });
            })

            $('#clients_list tbody').on('click', 'tr', function() {
                window.location.href = $(this).data('href');
            });
        });
    </script>
@endpush
