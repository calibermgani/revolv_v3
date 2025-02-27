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
                        <span class="project_header" style="margin-left: 4px !important;">Claim History</span>
                    </div>
                    <div class="col-md-6">
                        <div class="outside float-right" href="javascript:void(0);"></div>
                    </div>
                </div>               
            </div>                
            <div class="card-body py-0 px-7">
                <input type="hidden" value={{ $clientName }} id="clientName">
                <input type="hidden" value={{ $subProjectName }} id="subProjectName">
                <div class="table-responsive pt-5 pb-5 clietnts_table">
                    <table class="table table-separate table-head-custom no-footer dtr-column" id="client_non_workable_list" data-order='[[ 0, "desc" ]]'>
                        <thead>
                            @if (!empty($columnsHeader))
                                <tr>
                                    {{-- <th class='notexport'><input type="checkbox" id="ckbCheckAll" class="cursor_hand">
                                    </th> --}}
                                    @foreach ($columnsHeader as $columnName => $columnValue)
                                        @if ($columnValue != 'id')
                                            <th><input type="hidden" value={{ $columnValue }}>
                                                @if ($columnValue == 'chart_status')
                                                    Charge Status
                                                @elseif ($columnValue == 'CE_emp_id')
                                                    AR Emp Id
                                                @elseif ($columnValue == 'coder_work_date')
                                                    AR Work Date
                                                @elseif ($columnValue == 'coder_rework_status')
                                                    AR Rework Status
                                                @else
                                                    {{ ucwords(str_replace(['_else_', '_'], ['/', ' '], $columnValue)) }}
                                                @endif
                                            </th>
                                        @else
                                            <th style="display:none" class='notexport'><input type="hidden"
                                                    value={{ $columnValue }}>
                                                @if ($columnValue == 'chart_status')
                                                    Charge Status
                                                @elseif ($columnValue == 'CE_emp_id')
                                                    AR Emp Id
                                                @elseif ($columnValue == 'coder_work_date')
                                                    AR Work Date
                                                @elseif ($columnValue == 'coder_rework_status')
                                                    AR Rework Status
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
                            @if (isset($claimHistoryDetails))
                                @foreach ($claimHistoryDetails as $data)
                                    @php
                                        $arrayAttrributes = $data->getAttributes();
                                        $arrayAttrributes['aging'] = null;
                                        $arrayAttrributes['aging_range'] = null;
                                    @endphp
                                    <tr>
                                        {{-- <td><input type="checkbox" class="checkBoxClass cursor_hand" name='check[]'
                                                value="{{ $data->id }}">
                                        </td> --}}
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
                                                    'QA_status_code',
                                                    'QA_sub_status_code',
                                                    'qa_classification',
                                                    'qa_category',
                                                    'qa_scope',
                                                    'QA_followup_date',
                                                    'CE_status_code',
                                                    'CE_sub_status_code',
                                                    'CE_followup_date',
                                                    'cpt_trends',
                                                    'icd_trends',
                                                    'modifiers',
                                                    'annex_coder_trends',
                                                    'annex_qa_trends',
                                                    'qa_cpt_trends',
                                                    'qa_icd_trends',
                                                    'qa_modifiers',
                                                    'created_at',
                                                    'updated_at',
                                                    'deleted_at',
                                                    'parent_id','ar_manager_rebuttal_status','ar_manager_rebuttal_comments','qa_manager_rebuttal_status','qa_manager_rebuttal_comments','QA_comments_count'
                                                ];
                                                if (isset($arrayAttrributes['dos'])) {
                                                    $dosDate = Carbon::parse($arrayAttrributes['dos']);
                                                    $currentDate = Carbon::now();
                                                    $agingCount = $dosDate->diffInDays($currentDate);
                                                    if ($agingCount <= 30) {
                                                        $agingRange = '0-30';
                                                    } elseif ($agingCount <= 60) {
                                                        $agingRange = '31-60';
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
                                                    <td style="max-width: 300px;white-space: normal;">
                                                        @if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $columnValue))
                                                            {{ date('m/d/Y', strtotime($columnValue)) }}
                                                        @else
                                                            @if ($columnName == 'chart_status' && str_contains($columnValue, 'CE_'))
                                                               {{ str_replace('CE_', 'AR ', $columnValue) }}
                                                            @elseif ($columnName == 'chart_status' && str_contains($columnValue, 'QA_'))
                                                               {{ str_replace('QA_', 'QA ', $columnValue) }}
                                                            @elseif ($columnName == 'chart_status' && str_contains($columnValue, 'Auto_Close'))
                                                              Auto Close
                                                            @elseif ($columnName == 'aging')
                                                                {{ $agingCount }}
                                                            @elseif ($columnName == 'aging_range')
                                                                {{ $agingRange }}
                                                            @elseif(str_contains($columnValue, '_el_'))
                                                                {{str_replace('_el_', ',', $columnValue)}}
                                                            @elseif ($columnName == 'ar_status_code') 
                                                                @php
                                                                    if($columnValue != '--' && $columnValue != null) {                                                                   
                                                                            $status = App\Http\Helper\Admin\Helpers::arStatusById($columnValue);
                                                                            $columnValue = $status != null ? $status['status_code'] : $columnValue;                                                                  
                                                                    }                                                              
                                                                @endphp 
                                                                {{$columnValue}}  
                                                            @elseif ($columnName == 'ar_action_code')                                                      
                                                                @php
                                                                    if($columnValue != '--' && $columnValue != null) {                                                                   
                                                                        $action = App\Http\Helper\Admin\Helpers::arActionById($columnValue);
                                                                        $columnValue = $action != null ? $action['action_code'] : $columnValue;                                                                
                                                                    }                                                              
                                                                @endphp 
                                                                {{$columnValue}}  
                                                            @else
                                                                {{ $columnValue }}
                                                            @endif
                                                        @endif
                                                    </td>
                                                @else
                                                    <td style="display:none;max-width: 300px;
                                                                white-space: normal;"
                                                        id="table_id">
                                                         @if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $columnValue))
                                                            {{ date('m/d/Y', strtotime($columnValue)) }}
                                                        @elseif ($columnName == 'chart_status' && str_contains($columnValue, 'CE_'))
                                                            {{ str_replace('CE_', 'AR ', $columnValue) }}
                                                        @elseif ($columnName == 'chart_status' && str_contains($columnValue, 'QA_'))
                                                            {{ str_replace('QA_', 'QA ', $columnValue) }}
                                                        @elseif ($columnName == 'chart_status' && str_contains($columnValue, 'Auto_Close'))
                                                            Auto Close
                                                        @elseif ($columnName == 'aging')
                                                            {{ $agingCount }}
                                                        @elseif ($columnName == 'aging_range')
                                                            {{ $agingRange }}
                                                        @elseif(str_contains($columnValue, '_el_'))
                                                            {{str_replace('_el_', ',', $columnValue)}}
                                                        @elseif ($columnName == 'ar_status_code') 
                                                            @php
                                                                if($columnValue != '--' && $columnValue != null) {                                                                   
                                                                        $status = App\Http\Helper\Admin\Helpers::arStatusById($columnValue);
                                                                        $columnValue = $status != null ? $status['status_code'] : $columnValue;                                                                  
                                                                }                                                              
                                                            @endphp 
                                                            {{$columnValue}}  
                                                        @elseif ($columnName == 'ar_action_code')                                                      
                                                            @php
                                                                if($columnValue != '--' && $columnValue != null) {                                                                   
                                                                    $action = App\Http\Helper\Admin\Helpers::arActionById($columnValue);
                                                                    $columnValue = $action != null ? $action['action_code'] : $columnValue;                                                                
                                                                }                                                              
                                                            @endphp 
                                                            {{$columnValue}}  
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
        $(document).ready(function() {
            var indvidualSearchFieldsCount = Object.keys(@json($projectColSearchFields)).length;
            const url = window.location.href;
            const startIndex = url.indexOf('projects_') + 'projects_'.length;
            const endIndex = url.indexOf('/', startIndex);
            const urlDynamicValue = url.substring(startIndex, endIndex);
            var d = new Date();
            var month = d.getMonth() + 1;
            var day = d.getDate();
            var date = (month < 10 ? '0' : '') + month + '-' +
                (day < 10 ? '0' : '') + day + '-' + d.getFullYear();
            var table = $("#client_non_workable_list").DataTable({
                processing: true,
                ordering: true,
                clientSide: true,
                lengthChange: false,
                // searching: indvidualSearchFieldsCount > 0 ? false : true,
                searching: true,
                paging: true,
                info: true,
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
                    "title": 'Resolv',
                    "filename": 'claim_history_'+date,
                    "exportOptions": {
                        "columns": ':not(.notexport)'// Exclude first two columns
                    }
                }],
                dom: "<'row'<'col-md-12'f><'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>"
            })
            table.buttons().container()
                .appendTo('.outside');
            $('.dataTables_filter').addClass('pull-left');

            var clientName = $('#clientName').val();
            var subProjectName = $('#subProjectName').val();
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
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.three', function() {
                window.location.href = baseUrl + 'projects_hold/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
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
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.six', function() {
                window.location.href = baseUrl + 'projects_duplicate/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
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
                window.location.href = baseUrl + 'ar_rebuttal/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.ten', function() {
                window.location.href = "{{ url('#') }}";
            })

            $(document).on('click', '#sop_click', function(e) {
                $('#myModal_sop').modal('show');
            });
            $('#myModal_sop').on('shown.bs.modal', function() {
                $('#myModal_view').addClass('modal-right');
            });

            $('#myModal_sop').on('hidden.bs.modal', function() {
                $('#myModal_view').removeClass('modal-right');
            });
            $(document).on('click', '#filter_clear', function(e) {
                window.location.href = baseUrl + 'get_claim_History/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] +
                    "&child=" + getUrlVars()["child"];
            })

            $("#ckbCheckAll").click(function() {
                var isChecked = $(this).prop('checked');

                $(".checkBoxClass").prop('checked', isChecked);
                var table = $('#client_non_workable_list').DataTable();
                for (var i = 0; i < table.page.info().pages; i++) {
                    table.page(i).draw(false);
                    $(".checkBoxClass").prop('checked', isChecked);
                }
                if ($(this).prop('checked') == true && $('.checkBoxClass:checked').length > 0) {
                    $('#workable_dropdown').prop('disabled', false);
                    $('#select_p1').css('display', 'block');

                } else {
                    $('#select_p1').css('display', 'none')
                    $('#workable_dropdown').prop('disabled', true);

                }
            });
            $('#select_all_status').click(function() {
                $('#select_p1').css('display', 'none');
                $('#clear_p1').css('display', 'block');

            });
            $('#clear_all_status').click(function() {
                var isChecked = false;
                $("#ckbCheckAll").prop('checked', isChecked);
                $(".checkBoxClass").prop('checked', isChecked);
                $('#clear_p1').css('display', 'none');
                $('#workable_dropdown').prop('disabled', true);

            });

            function handleCheckboxChange() {
                var anyCheckboxChecked = $('.checkBoxClass:checked').length > 0;
                var allCheckboxesChecked = $('.checkBoxClass:checked').length === $('.checkBoxClass')
                    .length;
                if (allCheckboxesChecked) {
                    $("#ckbCheckAll").prop('checked', $(this).prop('checked'));
                    $('#select_p1').css('display', 'block');
                } else {
                    $("#ckbCheckAll").prop('checked', false);
                    $('#select_p1').css('display', 'none');
                    $('#clear_p1').css('display', 'none');
                }

                $('#workable_dropdown').prop('disabled', !(anyCheckboxChecked || allCheckboxesChecked));

            }

            function attachCheckboxHandlers() {
                $('.checkBoxClass').off('change').on('change', handleCheckboxChange);
            }
            attachCheckboxHandlers();

            table.on('draw', function() {
                attachCheckboxHandlers();
            });
         
        })
    </script>
@endpush
