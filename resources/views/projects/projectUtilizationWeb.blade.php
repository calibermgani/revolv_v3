@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card" id="project_utilization">
        <div class="card-body p-0">
            <div class="card-header border-0 px-4">
                <div class="row">
                    <div class="col-md-6">
                        <span class="project_header" style="margin-left: 4px !important;">Utilization</span>
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
                {!! Form::open([
                    'url' => url('projects/project_work_web') . '?parent=' . request()->parent . '&child=' . request()->child,
                    'class' => 'form',
                    'id' => 'formSearch',
                    'enctype' => 'multipart/form-data',
                ]) !!}
                @csrf

                <div class="row mr-0 ml-0">
                    <div class="col-md-3">
                        <div class="form-group row row_mar_bm">
                            <div class="col-md-10">
                                {!! Form::date('request_date', isset($yesterday) && !empty($yesterday) ? $yesterday : null, [
                                    'class' => 'form-control white-smoke pop-non-edt-val',
                                    'autocomplete' => 'none',
                                    'style' => 'cursor:pointer',
                                    'rows' => 3,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group row">

                            <div class="col-md-10">
                                <button type="submit" class="btn  btn-white-black font-weight-bold"
                                    id="filter_search">Search</button>
                                {{-- &nbsp;&nbsp; <button class="btn btn-light-danger" id="filter_clear" tabindex="10"
                                    type="button">
                                    <span>
                                        <span>Clear</span>
                                    </span>
                                </button> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body py-0 px-7">
                <div class="table-responsive pb-2">

                    <table class="table table-separate table-head-custom no-footer dtr-column clients_list_filter"
                        id="project_utilization_table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Inventory Uploaded</th>
                                <th>Total Users - AR</th>
                                <th>Logged Resolv - AR</th>
                                <th>Production Users - AR</th>
                                <th>AR</th>
                                <th>Logged Resolv - QA</th>
                                <th>Production - QA</th>
                                <th>QA</th>
                            </tr>
                        </thead>
                        <tbody>

                            @if (isset($projectsPending) && count($projectsPending) > 0)
                                @foreach ($projectsPending as $data)
                                    <tr data-project-id="{{ $data['project_id'] }}">
                                        <td>{{ $data['project'] }}</td>
                                        <td>{{ $data['Chats'] == 0 ? 'No' : 'Yes' }}</td>
                                        {{-- <td>{{ $data['total_ar'] }}</td> --}}
                                        <td class="total-ar"></td>
                                        <td class="logged_resolv_ar"></td>
                                        <td>{{ $data['prodcution_ar'] }}</td>
                                        <td>{{ $data['Coder'] == 0 ? 'No Activity' : $data['Coder'] }}</td>
                                        {{-- <td>{{ $data['logged_resolv_qa'] }}</td> --}}
                                        <td class="logged_resolv_qa"></td>
                                        <td>{{ $data['prodcution_qa'] }}</td>
                                        <td>{{ $data['QA'] == 0 ? 'No Activity' : $data['QA'] }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 5px;">--No Records--</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('view.scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            KTApp.block('#project_utilization', {
                overlayColor: '#000000',
                state: 'danger',
                opacity: 0.1,
                message: 'Fetching...',
            });
            var table = $("#project_utilization_table").DataTable({
                processing: true,
                ordering: true,
                clientSide: true,
                lengthChange: false,
                searching: true,
                pageLength: 20,
                buttons: [{
                    "extend": 'excel',
                    "text": `<span data-dismiss="modal" data-toggle="tooltip" data-placement="left" data-original-title="Export" style="font-size:13px"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" fill="currentColor" class="bi bi-box-arrow-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1z"/><path fill-rule="evenodd" d="M7.646.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 1.707V10.5a.5.5 0 0 1-1 0V1.707L5.354 3.854a.5.5 0 1 1-.708-.708z"/>
                             </svg>&nbsp;&nbsp;&nbsp;<span>Export</span></span>`,
                    "className": 'btn btn-primary-export text-white',
                    "title": 'Resolv Utilization',
                    "filename": 'resolv_utilization_report',
                    "exportOptions": {
                        "columns": ':not(.notexport)', // Exclude first two columns
                        format: {
                            body: function(data, row, column, node) {
                                return $(node).text();
                            }
                        }
                    }
                }],
                dom: "<'row'<'col-md-12'f><'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>"
            })
            table.buttons().container()
                .appendTo('.outside');
            // var rows = $("#project_utilization_table tbody tr");
            // rows.each(function () {
            //     var rowProjectId = $(this).data('project-id');
            //     var projectId = @json($projectIds);

            //     var yesterDayStartDate = @json($yesterDayStartDate);
            //     var yesterDayEndDate = @json($yesterDayEndDate);
            //     if (projectId) {console.log('projectId',projectId);
            //         fetch(`project-ar-qa-counts/`+projectId+`/${yesterDayStartDate}/${yesterDayEndDate}/${rowProjectId}`)
            //             .then(response => response.json())
            //             .then(data => {
            //                 console.log(data,'totalArCount');
            //                 if (data.total_ar !== undefined) {
            //                     console.log(data.total_ar,'totalArCount');

            //                     $(this).find(".total-ar").text(data.total_ar);
            //                     $(this).find(".logged_resolv_ar").text(data.logged_resolv_ar);
            //                     $(this).find(".logged_resolv_qa").text(data.logged_resolv_qa);
            //                     // $(this).find(".total-qa").text(data.total_qa);
            //                     KTApp.unblock('#project_utilization');
            //                 }
            //             })
            //             .catch(error => console.error("Error fetching AR/QA counts:", error));
            //     }
            // });
            function processAllRows() {
                var rows = table.rows().nodes(); // Fetch all rows across all pages
                $(rows).each(function() {
                    var row = $(this);
                    var rowProjectId = row.data('project-id');
                    var projectId = @json($projectIds);
                    var yesterDayStartDate = @json($yesterDayStartDate);
                    var yesterDayEndDate = @json($yesterDayEndDate);
console.log(yesterDayStartDate,'yesterDay',yesterDayEndDate);

                    if (projectId) {
                        fetch(`project-ar-qa-counts/` + projectId +
                                `/${yesterDayStartDate}/${yesterDayEndDate}/${rowProjectId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.total_ar !== undefined) {
                                    row.find(".total-ar").text(data.total_ar);
                                    row.find(".logged_resolv_ar").text(data.logged_resolv_ar);
                                    row.find(".logged_resolv_qa").text(data.logged_resolv_qa);
                                    KTApp.unblock('#project_utilization');
                                }
                            })
                            .catch(error => console.error("Error fetching AR/QA counts:", error));
                    }
                });
            }

            // Call processAllRows on table draw
            table.on('draw', processAllRows);

            // Initial processing
            processAllRows();
        });
        $(document).on('click', '#filter_search', function() {
            KTApp.block('#project_utilization', {
                overlayColor: '#000000',
                state: 'danger',
                opacity: 0.1,
                message: 'Fetching...',
            });
        });
        $(document).on('click', '#filter_clear', function() {
            location.reload();
        })
    </script>
@endpush
