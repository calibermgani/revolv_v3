@extends('layouts.app3')
@section('subheader')
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-2">
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Client Information</h5>

            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="card card-custom"  style="">
        <div class="card-header border-0 px-4">
            <div class="card-title">
                <span class="text-muted font-weight-bold font-size-lg flex-grow-1">
                    <span class="svg-icon svg-icon-primary svg-icon-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                            height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"></rect>
                                <path
                                    d="M8,4 L16,4 C17.1045695,4 18,4.8954305 18,6 L18,17.726765 C18,18.2790497 17.5522847,18.726765 17,18.726765 C16.7498083,18.726765 16.5087052,18.6329798 16.3242754,18.4639191 L12.6757246,15.1194142 C12.2934034,14.7689531 11.7065966,14.7689531 11.3242754,15.1194142 L7.67572463,18.4639191 C7.26860564,18.8371115 6.63603827,18.8096086 6.26284586,18.4024896 C6.09378519,18.2180598 6,17.9769566 6,17.726765 L6,6 C6,4.8954305 6.8954305,4 8,4 Z"
                                    fill="#000000"></path>
                            </g>
                        </svg>
                    </span>
                    <span style="color:#0e969c">Client Information</span>
                </span>
            </div>
            <div class="card-toolbar d-inline float-right mt-3">
                <div class="outside" href="javascript:void(0);"></div>
            </div>
        </div>
        <div class="card-body py-0 px-7">
            @php
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['designation'] && Session::get('loginDetails')['userDetail']['designation']['designation'] != null ? Session::get('loginDetails')['userDetail']['designation']['designation'] : '';

            @endphp
            <div class="mb-4">
                <ul class="nav nav-tabs nav-tabs-line" id="myTab">
                    <li class="nav-item">
                        <a class="nav-link active font-size-lg text-primary " data-toggle="tab" href="#kt_tab_pane_1"
                            style="font-size:16px">Assigned</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-size-lg text-primary " data-toggle="tab" href="#kt_tab_pane_2"
                            style="font-size:16px">Pending</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-size-lg text-primary " data-toggle="tab" href="#kt_tab_pane_3"
                            style="font-size:16px">On Hold</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-size-lg text-primary " data-toggle="tab" href="#kt_tab_pane_4"
                            style="font-size:16px">Completed</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-size-lg text-primary " data-toggle="tab" href="#kt_tab_pane_4"
                            style="font-size:16px">Rework</a>
                    </li>
                    @if ($empDesignation == 'Administrator')
                        <li class="nav-item">
                            <a class="nav-link font-size-lg text-primary " data-toggle="tab" href="#kt_tab_pane_5"
                                style="font-size:16px">Duplicate</a>
                        </li>
                    @endif
                </ul>
                <div class="tab-content mt-3" id="myTabContent">
                    <input type="hidden" value={{ $databaseConnection }} id="dbConnection">
                    @include('productions.clientAssignedTab')
                    @include('productions.clientPendingTab')
                    @include('productions.clientOnholdTab')
                    @include('productions.clientCompletedTab')
                    @include('productions.clientReworkTab')
                    @include('productions.clientDuplicateTab')




                </div>
            </div>
        </div>
        <div class="modal fade" id="myModal_status" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
            data-backdrop="static" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Test Patient - 123</h4>
                        <a href="" style="margin-left: 40rem;">SOP</a>
                        <button type="button" class="close comment_close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>

                    </div>
                    <div class="modal-body">

                        <div class="row"
                            style="
                        background-color: #ecf0f3;
                    ">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">Ticket Number
                                    </label>
                                    <label class="col-md-5 col-form-label">123
                                    </label>

                                </div>
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">Doctor
                                    </label>
                                    <label class="col-md-5 col-form-label">
                                        Test
                                    </label>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">Patient Name
                                    </label>
                                    <label class="col-md-5 col-form-label">Test
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">Patient Id
                                    </label>
                                    <label class="col-md-5 col-form-label">Test
                                    </label>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">DOB
                                    </label>
                                    <label class="col-md-5 col-form-label">12/03/2023
                                    </label>

                                </div>
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">DOS
                                    </label>
                                    <label class="col-md-5 col-form-label">12/03/2023
                                    </label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">Coders E/M CPT
                                    </label>
                                    <div class="col-md-5">
                                        {!! Form::text('Coders_CPT', null, [
                                            'class' => 'form-control',
                                            'autocomplete' => 'none',
                                            'style' => 'background-color: #fff !important;cursor:pointer',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">Coders E/M ICD 10
                                    </label>
                                    <div class="col-md-5">
                                        {!! Form::text('Coders_icd_10', null, [
                                            'class' => 'form-control',
                                            'autocomplete' => 'none',
                                            'style' => 'background-color: #fff !important;cursor:pointer',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">Coders Procedure CPT
                                    </label>
                                    <div class="col-md-5">
                                        {!! Form::text('Coders_pro_cpt', null, [
                                            'class' => 'form-control',
                                            'autocomplete' => 'none',
                                            'style' => 'background-color: #fff !important;cursor:pointer',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">Status
                                    </label>
                                    <div class="col-md-5">
                                        <select id="statusDropdownValue" class="form-control">
                                            <option value="">--select--</option>
                                            <option value="Hold">Hold</option>
                                            <option value="Clarification">Clarification</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Pending">Pending</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">Coders Procedure ICD 10
                                    </label>
                                    <div class="col-md-5">
                                        {!! Form::text('Coders_pro_CPT', null, [
                                            'class' => 'form-control',
                                            'autocomplete' => 'none',
                                            'style' => 'background-color: #fff !important;cursor:pointer',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">Billers Audit CPT - comments
                                    </label>
                                    <div class="col-md-5">
                                        {!! Form::text('billers_audit', null, [
                                            'class' => 'form-control',
                                            'autocomplete' => 'none',
                                            'style' => 'background-color: #fff !important;cursor:pointer',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-7 col-form-label required">Billers Audit ICD
                                    </label>
                                    <div class="col-md-5">
                                        {!! Form::text('billers_audit_icd', null, [
                                            'class' => 'form-control',
                                            'autocomplete' => 'none',
                                            'style' => 'background-color: #fff !important;cursor:pointer',
                                        ]) !!}
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label required">Remarks
                                    </label>
                                    <div class="col-md-8" style="margin-left: 2.5rem;">
                                        {!! Form::textarea('Coders_pro_CPT', null, [
                                            'class' => 'form-control',
                                            'autocomplete' => 'none',
                                            'rows' => 4,
                                            'maxlength' => 250,
                                            'style' => 'background-color: #fff !important;cursor:pointer',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default comment_close" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="evidence_status_update">Submit</button>
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
    <script>
        $(document).ready(function() {
            $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
                localStorage.setItem('activeTab', $(e.target).attr('href'));
            });
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
                        dbConn: dbConn,
                        dropdownStatus: dropdownStatus,
                        checkedRowValues: checkedRowValues
                    },
                    success: function(response) {
                        location.reload();
                    },

                });
            })
            var start = moment().startOf('month')
            var end = moment().endOf('month');
            $('.date_range_no_swipe').attr("autocomplete", "off");
            $('.date_range_no_swipe').daterangepicker({
                showOn: 'both',
                startDate: start,
                endDate: end,
                showDropdowns: true,

                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf(
                        'month')]
                },
                endDate: '+0d',
            });
            var dateRangeValue = $('#select_date').val();
            if (!dateRangeValue) {
                $('.date_range_no_swipe').val('');
            } else {
                $('.date_range_no_swipe').val(dateRangeValue);
            }

            var d = new Date();
            var month = d.getMonth() + 1;
            var day = d.getDate();
            var date = (month < 10 ? '0' : '') + month + '-' +
                (day < 10 ? '0' : '') + day + '-' + d.getFullYear();
            var table = $("#client_assigned_list").DataTable({
                processing: true,
                lengthChange: false,
                searching: true,
                pageLength: 20,
            })
            table.buttons().container()
                .appendTo('.outside');

            $('#clients_list tbody').on('click', 'tr', function() {
                window.location.href = $(this).data('href');
            });
            $('#client_pending_list').dataTable({
                processing: true,
                lengthChange: false,
                searching: false,
                pageLength: 20,

                columnDefs: [{
                    targets: [4, 5, 6],
                    visible: false
                }],
                dom: '<"top"lfB>rt<"bottom"ip><"clear">',
                buttons: [
                    {
                    extend: 'colvis',
                    className: 'btn-colvis',
                    text: 'Column Visibility'
                    }
                ]
            });
            $('#client_onhold_list').dataTable({
                processing: true,
                lengthChange: false,
                searching: true,
                pageLength: 20,
            });
            $('#client_completed_list').dataTable({
                processing: true,
                lengthChange: false,
                searching: true,
                pageLength: 20,
            });
            $('#client_rework_list').dataTable({
                processing: true,
                lengthChange: false,
                searching: true,
                pageLength: 20,
            });
            $('#client_duplicate_list').dataTable({
                processing: true,
                lengthChange: false,
                searching: true,
                pageLength: 20,
                columnDefs: [{
                    targets: [0],
                    orderable: false,
                }, ],
            });

            $(document).on('click', '.clickable-row', function(e) {
                $('#myModal_status').modal('show');
            });
        })
    </script>
@endpush
