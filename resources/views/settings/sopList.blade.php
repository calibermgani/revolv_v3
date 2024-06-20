@extends('layouts.app3')
@section('content')
    <div class="card card-custom mb-5 custom-card">
        <div class="card-body pb-4 mt-2">
            <div class="mb-0">
                <div>
                    <div class="my-div">
                        <span class="project_header">SOP List</span>
                    </div>
                    <div style="margin-bottom:-2rem"
                        class="d-flex flex-row justify-content-between align-items-center float-right ml-2">

                        <a class="btn btn-white-black font-weight-bolder btn-sm mr-1"
                            href="{{ url('sop/sop_upload') }}?parent={{ request()->parent }}&child={{ request()->child }}"><i
                                class="fa fa-plus" style="font-size:13px;color:#ffffff"></i>&nbsp;&nbsp;Add</a>

                    </div>
                    <table class="table table-separate table-head-custom no-footer dtr-column " id="sopList">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Sub Project Name</th>
                                <th>SOP Doc.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($sopList))
                                @foreach ($sopList as $data)
                                    @php
                                        $projectName = App\Models\project::where(
                                            'project_id',
                                            $data->project_id,
                                        )->first();
                                        if ($data->sub_project_id != null) {
                                            $subProjectName = App\Models\subproject::where(
                                                'project_id',
                                                $data->project_id,
                                            )
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
                                        $filename = $data->sop_doc;
                                        $withoutExtension = str_replace('.pdf', '', $filename);
                                        $withoutSuffix = substr($withoutExtension, 0, strrpos($withoutExtension, '_'));
                                    @endphp
                                    @if ($projectName !== null && $subProjectName !== null)
                                        <tr style="cursor:pointer !important">
                                            <td><input type="hidden"
                                                    value="{{ $data->project_id }}">{{ $projectName->project_name }}</td>
                                            <td><input type="hidden"
                                                    value="{{ $data->sub_project_id }}">{{ $subProjectName == '--' ? '--' : $subProjectName->sub_project_name }}
                                            </td>
                                            <td onclick="window.open('{{ asset($data->sop_path) }}', '_blank')"
                                                style="cursor:pointer !important">{{ $filename }}</td>
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
            $('#sopList').DataTable({
                lengthChange: false,
                searching: true,
                pageLength: 20,
                language: {
                    "search": '',
                    "searchPlaceholder": "   Search",
                },
                "columnDefs": [{
                        "width": "200px",
                        "targets": 0
                    }, // Adjust the width as needed
                    {
                        "width": "150px",
                        "targets": 1
                    }, // Adjust the width as needed
                    // Add more columnDefs for each column as needed
                    {
                        "className": "dt-wrap",
                        "targets": "_all"
                    } // Enable text wrapping for all columns
                ]
            });



        });
    </script>
@endpush
