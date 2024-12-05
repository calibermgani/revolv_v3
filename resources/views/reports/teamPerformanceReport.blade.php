@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card">
        <div class="card-body p-0">
            <div class="card-header border-0 px-4">
                <div class="row">
                    <div class="col-md-6">
                        <span class="project_header" style="margin-left: 4px !important;">Team Performance Report</span>
                    </div>
                    <div class="col-md-6">
                        <div class="row" style="justify-content: flex-end;margin-right:1.4rem">
                            <div class="outside" href="javascript:void(0);"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-custom custom-top-border">

            <div class="card-body mr-8 ml-12" id="filter_section">
            
                <div class="row mr-0 ml-0">
                    <div class="col-md-2">
                        <div class="form-group row row_mar_bm">
                            <div class="col-md-10">
                                @php $projectList = App\Http\Helper\Admin\Helpers::projectList(); @endphp
                                {!! Form::select('project_id', $projectList, request()->project_id, [
                                    'class' => 'text-black form-control select2 project_select',
                                    'id' => 'project_id',
                                    'placeholder' => 'Select Project',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group row row_mar_bm">
                            <div class="col-md-10">
                                @if (isset(request()->project_id))
                                    @php $subProjectList = App\Http\Helper\Admin\Helpers::subProjectList(request()->project_id); @endphp
                                    {!! Form::select('sub_project_id', $subProjectList, request()->sub_project_id, [
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
                    <div class="col-md-2">
                        <div class="form-group row row_mar_bm">
                            <div class="col-md-10">
                                @php $userList = []; @endphp
                                {!! Form::select('user', $userList, null, [
                                    'class' => 'text-black form-control select2 user_select',
                                    'id' => 'user',
                                    'placeholder' => 'User',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="row form-group">
                            <div class="col-md-12">
                                <input type="text" name="error_date" id="error_date"
                                class="form-control daterange_error_date" value="" autocomplete="nope">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group row">

                            <div class="col-md-10">
                                <button type="submit" class="btn  btn-white-black font-weight-bold"
                                    id="search_submit_1">Search</button>
                                &nbsp;&nbsp; <button class="btn btn-light-danger" id="filter_clear" tabindex="10"
                                    type="button">
                                    <span>
                                        <span>Clear</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" id="reportTable1">
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

            $('.daterange_error_date').attr("autocomplete", "off");
            $('.daterange_error_date').daterangepicker({
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
            $('.daterange_error_date').val('');

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
           
            $(document).on("click", "#search_submit_1", function(e) {
                $('#team_list').DataTable().destroy();
                var project_id = $('#project_list').val();
                var sub_project_id = $('#sub_project_list').val();
                var user = $('#user').val();
                var error_date = $('#error_date').val();
                teamPerformanceList(project_id, sub_project_id, user, error_date);
            });
            function teamPerformanceList(project_id, sub_project_id, user, error_date) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                 $.ajax({
                    type: "POST",
                    url: "{{ url('report/team_performance_report') }}",
                    data: {
                        project_id: project_id,
                        sub_project_id: sub_project_id,
                        user: user,
                        error_date: error_date
                    },
                    success: function(res) {
                        if (res.body_info) {
                            //  $('#listData').show();
                            $('#reportTable1').html(res.body_info);
                            var table = $('#team_list').DataTable({
                                processing: true,
                                lengthChange: false,
                                clientSide: true,
                                searching: true,
                                pageLength: 20,
                                scrollCollapse: true,
                                scrollX: true,
                                order: [],  
                                language: {
                                    "search": '',
                                    "searchPlaceholder": "   Search",
                                },

                            })

                        } else {
                            console.error('Error fetching data');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                    }
                });
            }
        });
    </script>
@endpush
