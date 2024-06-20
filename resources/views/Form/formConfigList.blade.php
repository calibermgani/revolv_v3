@extends('layouts.app3')
@section('content')
    <div class="card card-custom mb-5 custom-card">
        <div class="card-body pb-4 mt-2">
            <div class="mb-0">
                <div>
                    <div class="my-div">
                        {{-- <span class="svg-icon svg-icon-primary svg-icon-lg ">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="16" fill="currentColor"
                                class="bi bi-arrow-left project_header_row" viewBox="0 0 16 16"
                                style="width: 1.05rem !important;color: #000000 !important;margin-left: 4px !important;">
                                <path fill-rule="evenodd"
                                    d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                            </svg>
                        </span> --}}
                        <span class="project_header">Project Creation List</span>
                    </div>
                    <div style="margin-bottom:-2rem"
                        class="d-flex flex-row justify-content-between align-items-center float-right ml-2">

                        <a class="btn btn-white-black font-weight-bolder btn-sm mr-1"
                            href="{{ url('form_creation') }}?parent={{ request()->parent }}&child={{ request()->child }}"><i
                                class="fa fa-plus" style="font-size:13px;color:#ffffff"></i>&nbsp;&nbsp;Add</a>

                    </div>
                    <table class="table table-separate table-head-custom no-footer dtr-column " id="formConfigurationLsit">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Sub Project Name</th>
                                <th>Column Fields</th>
                                <th  style="width: 3%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($formConfiguration))
                                @foreach ($formConfiguration as $data)
                                    @php
                                        // $projectName = App\Models\project::where('id', $data->project_id)->first();
                                        // $subProjectName = App\Models\subproject::where('project_id', $data->project_id)
                                        //     ->where('id', $data->sub_project_id)
                                        //     ->first();
                                        $projectName = App\Models\project::where('project_id', $data->project_id)->first();
                                        if($data->sub_project_id != null) {
                                            $subProjectName = App\Models\subproject::where('project_id', $data->project_id)
                                                ->where('sub_project_id', $data->sub_project_id)
                                                ->first();
                                                $sub_project_id_encode = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(
                                            $data->sub_project_id,
                                        );
                                        } else {
                                            $subProjectName = '--';
                                            $sub_project_id_encode = '--';
                                        }
                                        $project_id_encode = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(
                                            $data->project_id,
                                        );

                                    @endphp
                                    @if($projectName !== null  && $subProjectName !== null )
                                    <tr
                                        data-href="{{ route('formEdit', ['parent' => request()->parent, 'child' => request()->child, 'project_id' => $project_id_encode, 'sub_project_id' => $sub_project_id_encode]) }}" style="cursor:pointer !important">
                                        <td><input type="hidden" value="{{$data->project_id}}">{{ $projectName->project_name }}</td>
                                        <td><input type="hidden" value="{{$data->sub_project_id}}">{{ $subProjectName == '--' ? '--' : $subProjectName->sub_project_name }}</td>
                                        <td>{{ $data->label_names }}</td>
                                        <td class="project_delete" data-value="{{$loop->iteration}}"><i
                                            class="fa fas fa-trash text-danger icon-circle2 ml-1 mt-0 record_delete"></i></a></td>
                                    </tr>
                                    @endif
                                @endforeach
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
                  $('#formConfigurationLsit').DataTable({
                    lengthChange: false,
                    searching: true,
                    pageLength: 20,
                    language: {
                        "search": '',
                        "searchPlaceholder": "   Search",
                    },
                    "columnDefs": [
                        { "width": "200px", "targets": 0 }, // Adjust the width as needed
                        { "width": "150px", "targets": 1 }, // Adjust the width as needed
                        // Add more columnDefs for each column as needed
                        { "className": "dt-wrap", "targets": "_all" } // Enable text wrapping for all columns
                    ]
                });

                // $('tr[data-href]').click(function() { // full row click
                //     var url = $(this).data('href');
                //     window.location.href = url;
                // });
                $('#formConfigurationLsit tbody tr td:not(:last-child)').click(function() {
                        // Your row click event handler logic here
                        var href = $(this).closest('tr').data('href');
                        window.location.href = href;
                });
                $('#formConfigurationLsit tbody').on('click', 'td.project_delete', function(e){
                    var projectId = $(this).closest('tr').find('td:eq(0) input').val();
                    var subProjectId = $(this).closest('tr').find('td:eq(1) input').val();
                    swal.fire({
                            text: "Are you sure you want to delete?",
                            icon: "success",
                            buttonsStyling: false,
                            showCancelButton: true,
                            confirmButtonText: "Yes",
                            cancelButtonText: "No",
                            reverseButtons: true,
                            customClass: {
                                confirmButton: "btn font-weight-bold btn-white-black",
                                cancelButton: "btn font-weight-bold  btn-light-danger",
                            }
                        }).then(function(result) {
                            if (result.value == true) {
                                $.ajaxSetup({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
                                    }
                                });

                                $.ajax({
                                    url: "{{ url('project_config_delete') }}",
                                    method: 'POST',
                                    data: {
                                        projectId: projectId,
                                        subProjectId: subProjectId,
                                    },
                                    success: function(response) {
                                        if (response.success == true) {
                                            js_notification('success', 'Project configuration deleted successfully');
                                            setTimeout(function() {
                                                    location.reload();
                                            }, 2000);
                                        } else {
                                            js_notification('error', 'We can not delete the project because it contains data.');
                                        }
                                    },
                                });
                            } else {
                               location.reload();
                            }
                        });
                     console.log('project delete',projectId,subProjectId);
                });
        });
    </script>
@endpush
