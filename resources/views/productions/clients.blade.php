@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card">
        <div class="card-body pt-4 pb-0 px-2">
            <div class="my-client-div">
                {{-- <span class="svg-icon svg-icon-primary svg-icon-lg ">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="16" fill="currentColor"
                        class="bi bi-arrow-left project_header_row" viewBox="0 0 16 16"
                        style="width: 1.05rem !important;color: #000000 !important;margin-left: 4px !important;">
                        <path fill-rule="evenodd"
                            d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                    </svg>
                </span> --}}
                <span class="project_header" style="margin-left: 4px !important">Project List</span>
            </div>

            <div class="table-responsive pb-4">
                <table class="table table-separate table-head-custom no-footer dtr-column " id="clients_list">
                    <thead>
                        <tr>
                            <th width="15px"></th>
                            <th>Project</th>
                            <th>Assigned</th>
                            <th>Completed</th>
                            <th>Pending</th>
                            <th>On Hold</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @php
                                        $encodedId = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(1);
                                        $encodeProjectName = App\Http\Helper\Admin\Helpers::encodeAndDecodeID('aig');
                                    @endphp --}}
                        @if (isset($projects))
                            @foreach ($projects as $data)
                            @php
                                $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                                $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                                    $projectName = App\Http\Helper\Admin\Helpers::projectName($data["id"])->project_name;//$data["client_name"];
                                  //   $subproject_name = App\Models\subproject::where('project_id',$data['id'])->pluck('sub_project_name')->toArray();
                                            if (isset($data["subprject_name"]) && !empty($data["subprject_name"])) {
                                                $subproject_name = $data["subprject_name"];
                                                // $model_name = collect($subproject_name)->map(function ($item) use ($projectName) {
                                                //             return str_replace(' ', '',ucfirst($projectName) . ucfirst($item));
                                                //         })->all();
                                                $model_name = collect($subproject_name)->map(function ($item) use ($projectName) {
                                                            return Str::studly(Str::slug((Str::lower($projectName).'_'.Str::lower($item)),'_'));
                                                        })->all();
                                            } else {
                                                // $model_name = collect(str_replace(' ', '', ucfirst($projectName) . ucfirst($projectName)));
                                                $model_name = collect(Str::studly(Str::slug((Str::lower($projectName).'_project'),'_')));

                                            }


                                            $assignedTotalCount = 0; $completedTotalCount = 0; $pendingTotalCount = 0; $holdTotalCount = 0;
                                            foreach($model_name as $model) {
                                                $modelClass = "App\\Models\\" .  $model; $startDate = Carbon\Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon\Carbon::now()->endOfDay()->toDateTimeString();
                                                // $modelClass = "App\\Models\\" .  preg_replace('/[^A-Za-z0-9]/', '',$model);
                                                        $assignedCount = 0;
                                                        $completedCount = 0;
                                                        $pendingCount = 0;
                                                        $holdCount = 0;
                                                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false)) {
                                                            if (class_exists($modelClass)) {
                                                                $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->count();
                                                                $completedCount = $modelClass::where('chart_status','CE_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                                                $pendingCount = $modelClass::where('chart_status','CE_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                                                $holdCount = $modelClass::where('chart_status','CE_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                                                            } else {
                                                                $assignedCount = 0;
                                                                $completedCount = 0;
                                                                $pendingCount = 0;
                                                                $holdCount = 0;
                                                            }
                                                } else if($loginEmpId) {
                                                    if (class_exists($modelClass)) {
                                                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Assigned','CE_Inprocess'])->where('CE_emp_id',$loginEmpId)->count();
                                                        $completedCount = $modelClass::where('chart_status','CE_Completed')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                                        $pendingCount = $modelClass::where('chart_status','CE_Pending')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                                        $holdCount = $modelClass::where('chart_status','CE_Hold')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                                                    } else {
                                                        $assignedCount = 0;
                                                        $completedCount = 0;
                                                        $pendingCount = 0;
                                                        $holdCount = 0;
                                                    }
                                                }
                                                $assignedTotalCount += $assignedCount;
                                                $completedTotalCount += $completedCount;
                                                $pendingTotalCount += $pendingCount;
                                                $holdTotalCount += $holdCount;
                                            }
                            @endphp
                                <tr class="clickable-client cursor_hand">
                                    <td class="details-control"></td>
                                    <td>{{ $data['client_name'] }} <input type="hidden" value={{ $data['id'] }}></td>
                                    <td>{{$assignedTotalCount}}</td>
                                    <td>{{$completedTotalCount}}</td>
                                    <td>{{$pendingTotalCount}}</td>
                                    <td>{{$holdTotalCount}}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
    </div>
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

    #myDIV2 {
        width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }

    table#project_financess tr th {
        width: 10% !important;
    }

    table#project_financess tr {
        white-space: nowrap;
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
                    var subProjects; var subprojectCountData;
            var table = $("#clients_list").DataTable({
                processing: true,
                lengthChange: false,
                searching: true,
                pageLength: 20,
                    columnDefs: [{
                        className: 'details-control',
                        targets: [0],
                        orderable: false,
                    }, ],
                // language: {
                //     "search": '',
                //     "searchPlaceholder": "   Search",
                // },
                responsive: true

            })
            table.buttons().container()
                .appendTo('.outside');
            //   $('#clients_list_filter input').attr("placeholder", "Search");

            $('#clients_list tbody').on('click', 'td.details-control', function() {
                var client_id = $(this).closest('tr').find('td:eq(1) input').val();
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var subProjectName = '--';
                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    KTApp.block('#clients_list', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Fetching...',
                    });
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: "GET",
                        url: "{{ url('sub_projects') }}",
                        data: {
                            project_id: client_id,
                        },
                        success: function(res) {
                            console.log(res, 'res');
                            subProjects = res.subprojects;
                            subprojectCountData = Object.keys(subProjects).length;console.log(subprojectCountData,'subprojectCountData');

                            if(typeof subprojectCountData !== 'undefined' && subprojectCountData > 0) {
                              row.child(format(row.data(), subProjects)).show();
                            } else {
                                if(typeof subprojectCountData !== 'undefined') {
                                    window.location.href = baseUrl + 'projects_assigned/' + btoa(client_id) + '/' +
                                        subProjectName + "?parent=" +
                                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
                                }
                            }
                            tr.addClass('shown');
                            KTApp.unblock('#clients_list');
                        },
                        error: function(jqXHR, exception) {}
                    });

                }
            });


            function format(data, subProjects) {
                //  subprojectCountt = Object.keys(subProjects).length;
                console.log(subprojectCountData,'format');
                if(subprojectCountData > 0) {
                    var html =
                        '<table id="practice_list" class="inv_head" cellpadding="5" cellspacing="0" border="0" style="width:97%;border-radius: 10px !important;overflow: hidden;margin-left: 1.5rem;">' +
                        '<tr><th></th><th>Sub Project</th><th>Assigned</th> <th>Completed</th> <th>Pending</th><th>On Hold</th> </tr>';
                    $.each(subProjects, function(index, val) {
                        console.log(val, 'val',val.client_name,val.sub_project_name );
                        html +=
                            '<tbody><tr class="clickable-row cursor_hand">' +
                            '<td><input type="hidden" value=' + val.client_id + '></td>' +
                            '<td>' + val.sub_project_name + '<input type="hidden" value=' + val.sub_project_id + '></td>' +
                            '<td>' + val.assignedCount + '</td>' +
                            '<td>' + val.CompletedCount + '</td>' +
                            '<td>' + val.PendingCount + '</td>' +
                            '<td>' + val.holdCount + '</td>' +
                            '</tr></tbody>';
                    });
                    html += '</table>';
                    return html;
              }
            }

            $(document).on('click', '.clickable-row', function(e) {

                // var client_name = $(this).closest('tr').find('td:eq(1)').text();
                // var id = $(this).closest('tr').find('td:eq(0)').text();
                // var encodedId = $(this).closest('tr').find('td:eq(0) input').val();
                var clientName = $(this).closest('tr').find('td:eq(0) input').val();
                var subProjectName = $(this).closest('tr').find('td:eq(1) input').val();

                if (!clientName) {
                    console.error('encodedclientname is undefined or empty');
                    return;
                }

                // window.location.href = baseUrl + 'projects/' + encodedId + '/' + clientName + "?parent=" +
                //     getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
                KTApp.block('#clients_list', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Fetching...',
                    });
                window.location.href = baseUrl + 'projects_assigned/' + btoa(clientName) + '/' + btoa(
                        subProjectName) + "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
                    KTApp.unblock('#clients_list');


            })


                // $(document).on('click', '.clickable-client', function() {

                //             var clientName = $(this).closest('tr').find('td:eq(1) input').val();
                //             var subProjectName = '--';

                //             if (!clientName) {
                //                 console.error('encodedclientname is undefined or empty');
                //                 return;
                //             }

                //             console.log(subprojectCountData,'subprojectCount');
                //             if (typeof subprojectCountData !== 'undefined' && subprojectCountData <= 0) {
                //             window.location.href = baseUrl + 'projects_assigned/' + btoa(clientName) + '/' +
                //                     subProjectName + "?parent=" +
                //                 getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
                //             }
                // })


        })

    </script>
@endpush
