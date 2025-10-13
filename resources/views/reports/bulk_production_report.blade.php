@extends('layouts.app3')

@section('content')
<div class="card card-custom custom-card">
    <div class="card-body p-0">
        <form id="filterForm">
            <div class="card-header border-0 px-4" style="background-color: #139AB3;height: 84px">
                <div class="row">
                    <div class="col-md-6">
                        <span class="project_header text-white">Generate Bulk Report</span>
                    </div>
                </div>
            </div>

            <div class="card-body py-0 px-7" style="background-color: #139AB3;height: 84px">
                <div class="row">
                    <div class="col-lg-3">
                        <select id="project_id" name="project_id" class="form-control select2">
                            <option value="">Select Project</option>
                            @foreach(App\Http\Helper\Admin\Helpers::projectList() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <select id="sub_project_id" name="sub_project_id" class="form-control select2">
                            <option value="">Select Sub Project</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="text" name="work_date" id="work_date" class="form-control daterange" placeholder="mm/dd/yyyy - mm/dd/yyyy" autocomplete="off">
                    </div>

                    <div class="col-md-2">
                        <select id="user" name="user" class="form-control select2">
                            <option value="">Select User</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select id="client_status" name="client_status" class="form-control select2">
                            <option value="">Status</option>
                            <option value="CE_Inprocess">AR Inprocess</option>
                            <option value="CE_Pending">AR Pending</option>
                            <option value="CE_Completed">AR Completed</option>
                            <option value="CE_Hold">AR Hold</option>
                            <option value="QA_Inprocess">QA Inprocess</option>
                            <option value="QA_Pending">QA Pending</option>
                            <option value="QA_Completed">QA Completed</option>
                            <option value="QA_Hold">QA Hold</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-footer d-flex justify-content-between align-items-center w-100 px-4 pb-3">
                <div>
                    <button type="button" id="export_excel" class="btn btn-success">Export Excel</button>

                    <button class="btn btn-light-danger" id="filter_clear" type="button">Clear</button>&nbsp;
                    <button type="submit" class="btn btn-white-black font-weight-bold" id="formUpdate_save">Submit</button>
                </div>
            </div>
        </form>

        <div class="table-responsive p-4">
            <table class="table table-bordered" id="bulk_list" style="width: 100%;">
                <thead>
                    <tr id="bulk_columns">
                        <th>Loading...</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('view.scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
// $(document).ready(function() {

//     // Date Range Picker
//     $('.daterange').daterangepicker({
//         startDate: moment().startOf('month'),
//         endDate: moment().endOf('month'),
//         ranges: {
//             'Today': [moment(), moment()],
//             'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
//         }
//     });

//          $(document).on('change', '#project_id', function() {
//                 KTApp.block('#reportModal', {
//                     overlayColor: '#000000',
//                     state: 'danger',
//                     opacity: 0.1,
//                     message: 'Fetching...',
//                 });
//                 var project_id = $(this).val();
//                 $.ajaxSetup({
//                     headers: {
//                         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                     }
//                 });
//                 $.ajax({
//                     type: "POST",
//                     url: "{{ url('reports/get_sub_projects') }}",
//                     data: {
//                         project_id: project_id
//                     },
//                     success: function(res) {
//                         $("#sub_project_id").val(res.subProject);
//                         var sla_options = '<option value="">-- Select --</option>';
//                         $.each(res.subProject, function(key, value) {
//                             sla_options = sla_options + '<option value="' + key + '">' +
//                                 value +
//                                 '</option>';
//                         });
//                         $("#sub_project_id").html(sla_options);
//                         $("#user").val(res.resource);
//                         var user_options = '<option value="">Select User</option>';
//                         $.each(res.resource, function(key, value) {
//                             user_options = user_options + '<option value="' + key +
//                                 '">' + value +
//                                 '</option>';
//                         });
//                         $("#user").html(user_options);
//                         KTApp.unblock('#reportModal');
//                     },
//                     error: function(jqXHR, exception) {}
//                 });
//             });
//     var table;

//     function initDataTable() {
//         if (table) {
//             table.destroy();
//             $('#bulk_list').empty().append('<thead><tr id="bulk_columns"><th>Loading...</th></tr></thead>');
//         }

//         table = $('#bulk_list').DataTable({
//             processing: true,
//             serverSide: true,
//             paging: true,
//             searching: true,
//             scrollX: true,
//             ajax: {
//                 url: "{{ route('reports.bulk.columns') }}",
//                 type: "POST",
//                 data: function (d) {
//                     d._token = $('meta[name="csrf-token"]').attr('content');
//                     d.clientName = btoa($('#project_id').val());
//                     d.subProjectName = btoa($('#sub_project_id').val());
//                     d.work_date = $('#work_date').val();
//                     d.user = $('#user').val();
//                     d.client_status = $('#client_status').val();
//                 },
//                 dataSrc: function (json) {
//                     if (json.columnsHeader && json.columnsHeader.length > 0) {
//                         let headerRow = '';
//                         $.each(json.columnsHeader, function (i, col) {
//                             let label = col.replace(/_else_/g, '/').replace(/_/g, ' ');
//                             label = label.replace(/\b\w/g, l => l.toUpperCase());
//                             headerRow += '<th>' + label + '</th>';
//                         });
//                         $('#bulk_columns').html(headerRow);

//                         const columns = json.columnsHeader.map(c => ({
//                             data: c,
//                             name: c,
//                             defaultContent: '--',
//                             render: function(data) { return data ?? '--'; }
//                         }));

//                         table.clear();
//                         table.destroy();
//                         table = $('#bulk_list').DataTable({
//                             processing: true,
//                             serverSide: true,
//                             paging: true,
//                             searching: true,
//                             scrollX: true,
//                             ajax: {
//                                 url: "{{ route('reports.bulk.columns') }}",
//                                 type: "POST",
//                                 data: function (d) {
//                                     d._token = $('meta[name="csrf-token"]').attr('content');
//                                     d.clientName = btoa($('#project_id').val());
//                                     d.subProjectName = btoa($('#sub_project_id').val());
//                                     d.work_date = $('#work_date').val();
//                                     d.user = $('#user').val();
//                                     d.client_status = $('#client_status').val();
//                                 }
//                             },
//                             columns: columns
//                         });
//                     }
//                     return json.data;
//                 }
//             },
//             columns: [{ data: 'id', defaultContent: '' }]
//         });
//     }

//     $('#formUpdate_save').on('click', function(e) {
//         e.preventDefault();
//         if (!$('#project_id').val() || !$('#sub_project_id').val()) {
//             alert('Please select Project and Sub Project.');
//             return;
//         }
//         initDataTable();
//     });

//     $('#filter_clear').on('click', function() {
//         $('#filterForm')[0].reset();
//         $('.select2').val('').trigger('change');
//         if (table) table.clear().draw();
//     });
// });
$(document).ready(function() {

    // Date Range Picker
    $('.daterange').daterangepicker({
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        ranges: {
            'Today': [moment(), moment()],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    // Fetch subprojects when project changes
    $(document).on('change', '#project_id', function() {
        var project_id = $(this).val();
        $.ajax({
            type: "POST",
            url: "{{ url('reports/get_sub_projects') }}",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                project_id: project_id
            },
            success: function(res) {
                var sla_options = '<option value="">-- Select --</option>';
                $.each(res.subProject, function(key, value) {
                    sla_options += `<option value="${key}">${value}</option>`;
                });
                $("#sub_project_id").html(sla_options);

                var user_options = '<option value="">Select User</option>';
                $.each(res.resource, function(key, value) {
                    user_options += `<option value="${key}">${value}</option>`;
                });
                $("#user").html(user_options);
            }
        });
    });

    let table;

    function initDataTable() {
        if (table) {
            table.destroy();
            $('#bulk_list').empty().append('<thead><tr id="bulk_columns"><th>Loading...</th></tr></thead>');
        }

        // First, fetch columnsHeader from controller
        $.ajax({
            url: "{{ route('reports.bulk.columns') }}",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                clientName: btoa($('#project_id').val()),
                subProjectName: btoa($('#sub_project_id').val()),
                work_date: $('#work_date').val(),
                user: $('#user').val(),
                client_status: $('#client_status').val()
            },
            success: function(json) {
                if (!json.columnsHeader || json.columnsHeader.length === 0) {
                    alert('No columns found for this project/subproject.');
                    return;
                }

                // Build table header
                let headerRow = '';
                const columns = json.columnsHeader.map(c => {
                    let label = c.replace(/_else_/g, '/').replace(/_/g, ' ');
                    label = label.replace(/\b\w/g, l => l.toUpperCase());
                    headerRow += `<th>${label}</th>`;
                    return {
                        data: c,
                        name: c,
                        defaultContent: '--',
                        render: function(data) { return data ?? '--'; }
                    };
                });
                $('#bulk_columns').html(headerRow);

                // Initialize DataTable
                table = $('#bulk_list').DataTable({
                    processing: true,
                    serverSide: true,
                    paging: true,
                    searching: true,
                    scrollX: true,
                    ajax: {
                        url: "{{ route('reports.bulk.columns') }}",
                        type: "POST",
                        data: function(d) {
                            d._token = $('meta[name="csrf-token"]').attr('content');
                            d.clientName = btoa($('#project_id').val());
                            d.subProjectName = btoa($('#sub_project_id').val());
                            d.work_date = $('#work_date').val();
                            d.user = $('#user').val();
                            d.client_status = $('#client_status').val();
                        }
                    },
                    columns: columns,
                    order: [] // disable default ordering
                });
            },
            error: function(err) {
                console.log(err);
                alert('Error fetching columns.');
            }
        });
    }

    // Submit button
    $('#formUpdate_save').on('click', function(e) {
        e.preventDefault();
        if (!$('#project_id').val() || !$('#sub_project_id').val()) {
            alert('Please select Project and Sub Project.');
            return;
        }
        initDataTable();
    });

    // Clear filters
    $('#filter_clear').on('click', function() {
        $('#filterForm')[0].reset();
        $('.select2').val('').trigger('change');
        if (table) table.clear().draw();
    });
    $('#export_excel').on('click', function () {
        if (!$('#project_id').val() || !$('#sub_project_id').val()) {
            alert('Please select Project and Sub Project.');
            return;
        }

        const formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            clientName: btoa($('#project_id').val()),
            subProjectName: btoa($('#sub_project_id').val()),
            work_date: $('#work_date').val(),
            user: $('#user').val(),
            client_status: $('#client_status').val(),
        };

        const $btn = $(this);
        $btn.prop('disabled', true).text('Exporting...');

        $.ajax({
            url: "{{ route('reports.bulk.export') }}",
            type: "POST",
            data: formData,
            xhrFields: { responseType: 'blob' },
            success: function (data) {
                const blob = new Blob([data]);
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'bulk_production_report.xlsx';
                link.click();
                $btn.prop('disabled', false).text('Export Excel');
            },
            error: function (xhr) {
                alert('Error generating Excel file');
                $btn.prop('disabled', false).text('Export Excel');
            }
        });
    });


});

</script>
@endpush
