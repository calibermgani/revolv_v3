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
                    <button type="button" class="btn btn-success" id="exportExcel">Export Excel</button>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    $('.daterange').daterangepicker({
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        ranges: {
            'Today': [moment(), moment()],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    // Fetch subprojects and users
    $('#project_id').on('change', function() {
        var project_id = $(this).val();
        $.ajax({
            type: "POST",
            url: "{{ url('reports/get_sub_projects') }}",
            data: {_token: $('meta[name="csrf-token"]').attr('content'), project_id},
            success: function(res) {
                let subOptions = '<option value="">-- Select --</option>';
                $.each(res.subProject, (k,v)=>subOptions+=`<option value="${k}">${v}</option>`);
                $("#sub_project_id").html(subOptions);

                let userOptions = '<option value="">Select User</option>';
                $.each(res.resource, (k,v)=>userOptions+=`<option value="${k}">${v}</option>`);
                $("#user").html(userOptions);
            }
        });
    });

    let table;
    function initDataTable() {
        if(table){ table.destroy(); $('#bulk_list').empty().append('<thead><tr id="bulk_columns"><th>Loading...</th></tr></thead>'); }

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
            success: function(json){
                if(!json.columnsHeader || !json.columnsHeader.length){ alert('No columns found.'); return; }

                let headerRow = '';
                const columns = json.columnsHeader.map(c=>{
                    let label = c.replace(/_else_/g,'/').replace(/_/g,' ');
                    label = label.replace(/\b\w/g,l=>l.toUpperCase());
                    headerRow += `<th>${label}</th>`;
                    return {data:c, name:c, defaultContent:'--', render: data=>data??'--'};
                });
                $('#bulk_columns').html(headerRow);

                table = $('#bulk_list').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('reports.bulk.columns') }}",
                        type: "POST",
                        data: function(d){
                            d._token = $('meta[name="csrf-token"]').attr('content');
                            d.clientName = btoa($('#project_id').val());
                            d.subProjectName = btoa($('#sub_project_id').val());
                            d.work_date = $('#work_date').val();
                            d.user = $('#user').val();
                            d.client_status = $('#client_status').val();
                        }
                    },
                    columns: columns,
                    scrollX: true,
                    pageLength: 50,
                    order: [],
                });
            }
        });
    }

    $('#formUpdate_save').click(function(e){
        e.preventDefault();
        if(!$('#project_id').val() || !$('#sub_project_id').val()){ alert('Select Project/Sub Project'); return; }
        initDataTable();
    });

    $('#filter_clear').click(function(){
        $('#filterForm')[0].reset();
        if(table) table.clear().draw();
    });

    // Excel Export
    $('#exportExcel').click(function(){
        if(!$('#project_id').val() || !$('#sub_project_id').val()){ alert('Select Project/Sub Project'); return; }
        const url = `{{ url('reports/export_bulk_report') }}?clientName=${btoa($('#project_id').val())}&subProjectName=${btoa($('#sub_project_id').val())}`;
        window.location.href = url;
    });

});
</script>
@endpush
