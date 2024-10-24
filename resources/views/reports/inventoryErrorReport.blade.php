@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card" style="display: none" id="listData">
        <div class="card-body  px-4">
            <div class="card-header border-0 px-4">
                <div class="row">
                    <div class="col-md-6">
                        <span class="project_header" style="margin-left: 4px !important;">Error List</span>
                    </div>
                    <div class="col-md-6">
                        <div class="row" style="justify-content: flex-end;margin-right:1.4rem">
                        </div>
                    </div>
                </div>
                <div class="row mt-8 ml-2">
                    <div class="col-md-12">
                        <div class="row">

                            <div class="col-lg-2 mb-lg-0 mb-3" id="project_div">
                                @php $projectList = App\Http\Helper\Admin\Helpers::projectList(); @endphp
                                <div class="form-group mb-0">
                                    {!! Form::select('project_id', $projectList, null, [
                                        'class' => 'form-control kt_select2_project',
                                        'id' => 'project_list',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-lg-2 mb-lg-0 mb-3" id="sub_project_div">
                                <div class="form-group mb-0">
                                    @php $subProjectList = []; @endphp
                                    {!! Form::select('sub_project_id', $subProjectList, null, [
                                        'class' => 'form-control  kt_select2_sub_project',
                                        'id' => 'sub_project_list',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-lg-2 mb-lg-0 mb-3">
                                <fieldset class="form-group mb-0">
                                    <input type="text" name="error_date" id="error_date"
                                        class="form-control daterange_error_date" value="" autocomplete="nope">
                                </fieldset>
                            </div>

                            <div class="col-lg-4 mb-lg-0 mb-3 d-flex align-items-center">
                                <button class="btn btn-primary-export text-white mr-2" id="search_submit" tabindex="9"
                                    type="submit" value="Search" autocomplete="nope">
                                    <span>
                                        <i class="la la-search text-white"></i>
                                        <span>Search</span>
                                    </span>
                                </button>
                                <button class="btn btn-secondary btn-secondary--icon" id="clear_submit_month" tabindex="10"
                                    type="button">
                                    <span>
                                        <i class="la la-close"></i>
                                        <span>Clear</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="table-responsive" id="reportTable">
            </div>
        </div>
    </div>
@endsection
@push('view.scripts')
    <script>
        $(document).ready(function() {
            var start = moment().startOf('day')
            var end = moment().endOf('day');
            console.log(start, end, 'start');
            $('.daterange_error_date').daterangepicker({
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
                }
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var project_id = '';
            var sub_project_id = '';
            var error_date = $('#error_date').val();
            errorList(project_id, sub_project_id, error_date);

            function errorList(project_id, sub_project_id, error_date) {
                console.log('p1', project_id, sub_project_id, error_date);
                $.ajax({
                    type: "POST",
                    url: "{{ url('report/inventory_error_report') }}",
                    data: {
                        project_id: project_id,
                        sub_project_id: sub_project_id,
                        error_date: error_date
                    },
                    success: function(res) {
                        if (res.body_info) {
                            $('#reportModal').modal('hide');
                            $('#generateReportClass').hide();
                            $('#listData').show();
                            $('#reportTable').html(res.body_info);
                            var table = $('#report_list').DataTable({
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
            $(document).on("click", "#search_submit", function(e) {
                $('#report_list').DataTable().destroy();
                var project_id = $('#project_list').val();
                var sub_project_id = $('#sub_project_list').val();
                var error_date = $('#error_date').val();
                errorList(project_id, sub_project_id, error_date);
            });


            $(document).on('change', '#project_list', function() {
                var project_id = $(this).val();
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
                    },
                    error: function(jqXHR, exception) {}
                });
            });
            $(document).on('click','#clear_submit_month',function(){
                location.reload();
            })
        });
    </script>
@endpush
