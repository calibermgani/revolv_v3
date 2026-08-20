@extends('layouts.app3')
@section('content')
    <div class="card card-custom mb-5 custom-card" id="formConfigDiv">
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
                    <div 
                        class="d-flex flex-row justify-content-between align-items-center float-right ml-2">
                          <!-- Non AR Crate table Upload Icon -->
                            {{-- <i class="fa fa-upload upload-icon cursor_hand mr-3"
                            style="font-size:18px; color:#139AB3; cursor:pointer;"
                            title="Non AR Projects tables create from Excel">
                            </i> --}}

                        <a id="navigate-btn" class="btn btn-white-black font-weight-bolder btn-sm mr-1"
                            href="{{ route('formCreationIndex') }}?parent={{ request()->parent }}&child={{ request()->child }}"><i
                                class="fa fa-plus" style="font-size:13px;color:#ffffff"></i>&nbsp;&nbsp;Add</a>

                    </div>
                    <div class="table-responsive pt-5">
                    <table class="table table-separate table-head-custom no-footer dtr-column" id="formConfigurationLsit">
                        <thead>
                            <tr>
                                <th width="15%">Project Name</th>
                                <th width="10%">Sub Project Name</th>
                                <th>Column Fields</th>
                                <th width="10%">Project Type</th>
                                <th width="6%"></th>
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
                                        data-href="{{ route('formEdit', ['parent' => request()->parent, 'child' => request()->child, 'project_id' => $project_id_encode, 'sub_project_id' => $sub_project_id_encode]) }}"
                                        style="cursor:pointer !important">
                                        <td width="15%"><input type="hidden" value="{{$data->project_id}}">{{ $projectName->aims_project_name }}</td>
                                        <td width="10%"><input type="hidden" value="{{$data->sub_project_id}}">{{ $subProjectName == '--' ? '--' : $subProjectName->sub_project_name }}</td>
                                        <td style="word-wrap: break-word;white-space: normal;overflow-wrap: break-word;word-break: break-word; ">{{$data->label_names}}</td>
                                        <td width="10%">{{$data->project_type != null ? 'Open Access' : 'Automation'}}</td>
                                        <td class="project_delete project_actions" data-value="{{$loop->iteration}}" width="6%">
                                            @if($data->sub_project_id != null)
                                            <i class="fa fa-clone text-primary icon-circle2 ml-1 mt-0 project_clone" title="Clone columns" style="cursor:pointer"></i>
                                            @endif
                                            <i class="fa fas fa-trash text-danger icon-circle2 ml-1 mt-0 record_delete"></i>
                                        </td>
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
    </div>

    {{-- Clone configuration → formConfigurationCloneStore --}}
    <div class="modal fade" id="cloneConfigurationModal" tabindex="-1" role="dialog" aria-labelledby="cloneConfigurationModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="formConfigurationCloneStoreForm" method="POST" action="{{ route('formConfigurationCloneStore') }}">
                    @csrf
                    <input type="hidden" name="parent" value="{{ request()->parent }}">
                    <input type="hidden" name="child" value="{{ request()->child }}">
                    <input type="hidden" name="project_id" id="clone_project_id" value="">
                    <input type="hidden" name="source_sub_project_id" id="clone_source_sub_project_id" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cloneConfigurationModalLabel">Clone column configuration</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3 text-muted">Copy column fields from <strong id="clone_source_sub_project_name"></strong> to another sub project under the same project.</p>
                        <div class="form-group mb-0">
                            <label for="clone_target_sub_project_id" class="required">Sub project</label>
                            <select class="form-control" name="sub_project_id" id="clone_target_sub_project_id" required>
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-white-black font-weight-bold" id="cloneConfigurationSubmit">Clone</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal" id="uploadModal" tabindex="-1" role="dialog"  aria-labelledby="uploadModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">
                        Non AR Projects Creation
                    </h5>

                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:black">&times;</button>
                </div>

                <form id="dynamicTableUploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        {{-- <input type="hidden" id="project_id" name="project_id" value="215">
                        <input type="hidden" id="sub_project_id" name="sub_project_id" value="357"> --}}
                        <input type="hidden" id="project_id" name="project_id">
                        <input type="hidden" id="sub_project_id" name="sub_project_id">
                        <div class="form-group">
                            <div class="form-group">
                                <label>Select File <span style="color:red;">(Upload only Excel file)</span></label>
                                <input type="file"
                                    class="form-control"
                                    name="file"
                                    id="uploadFile"
                                    accept=".xlsx"
                                    required>
                            </div>
                        </div>
                        <div id="uploadErrors"
                            class="alert alert-danger"
                            style="display:none;">
                        </div>
                        <div id="uploadSuccess"
                            class="alert alert-success"
                            style="display:none;">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <!-- Clear Button -->
                        <button type="button"
                                id="clearFileBtn"
                                class="btn btn-warning">
                            Clear
                        </button>                                 
                        <!-- Submit Button -->
                        <button type="submit"  id="submitFile"
                                class="btn" style="background-color: #139AB3 !important;color: #ffffff;">
                            Upload
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

</style>
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
                    autoWidth: false
                });

                // $('tr[data-href]').click(function() { // full row click
                //     var url = $(this).data('href');
                //     window.location.href = url;
                // });
                $('#formConfigurationLsit tbody').on('click', 'tr td:not(:last-child)', function () {
                        // Your row click event handler logic here
                        var href = $(this).closest('tr').data('href');
                        if (href) {
                            window.location.href = href;
                        }
                });
                function subProjectIsConfigured(subProjectId, configuredIds) {
                    return configuredIds.some(function(id) {
                        return String(id) === String(subProjectId);
                    });
                }

                $('#formConfigurationLsit tbody').on('click', '.project_clone', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var $row = $(this).closest('tr');
                    var projectId = $row.find('td:eq(0) input').val();
                    var sourceSubProjectId = $row.find('td:eq(1) input').val();
                    var sourceSubProjectName = $.trim($row.find('td:eq(1)').text());

                    $('#clone_project_id').val(projectId);
                    $('#clone_source_sub_project_id').val(sourceSubProjectId);
                    $('#clone_source_sub_project_name').text(sourceSubProjectName);
                    $('#clone_target_sub_project_id').html('<option value="">Loading...</option>');
                    $('#cloneConfigurationModal').modal('show');

                    $.ajax({
                        type: 'GET',
                        url: "{{ url('sub_project_list') }}",
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        data: {
                            project_id: projectId
                        },
                        success: function(res) {
                            var myArray = res.existingSubProject || [];
                            var existingSubProjectWithDeltedAt = res.existingSubProjectWithDeltedAt || [];
                            var options = '<option value="">-- Select --</option>';
                            var hasTarget = false;

                            $.each(res.subProject || {}, function(key, value) {
                                if (String(key) === String(sourceSubProjectId)) {
                                    return;
                                }
                                var isExisting = subProjectIsConfigured(key, myArray);
                                var isDeleted = subProjectIsConfigured(key, existingSubProjectWithDeltedAt);
                                if (!isExisting && !isDeleted) {
                                    hasTarget = true;
                                    options += '<option value="' + key + '">' + value + '</option>';
                                }
                            });

                            $('#clone_target_sub_project_id').html(options);

                            if (!hasTarget) {
                                js_notification('error', 'No sub projects available to clone into for this project.');
                            }
                        },
                        error: function() {
                            $('#clone_target_sub_project_id').html('<option value="">-- Select --</option>');
                            js_notification('error', 'Unable to load sub projects.');
                        }
                    });
                });

                $('#formConfigurationCloneStoreForm').on('submit', function() {
                    if (!$('#clone_target_sub_project_id').val()) {
                        return false;
                    }
                    KTApp.block('#formConfigDiv', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Cloning...',
                    });
                });

                $('#formConfigurationLsit tbody').on('click', 'td.project_delete', function(e){
                    var projectId = $(this).closest('tr').find('td:eq(0) input').val();
                    var subProjectId = $(this).closest('tr').find('td:eq(1) input').val();
                    if ($(e.target).closest('.project_clone').length) {
                        return;
                    }
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
                                KTApp.block('#formConfigDiv', {
                                    overlayColor: '#000000',
                                    state: 'danger',
                                    opacity: 0.1,
                                    message: 'Fetching...',
                                });
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
                                        console.log(response,'response');
                                        
                                        if (response.success == true) {
                                            js_notification('success', 'Project configuration deleted successfully');
                                            setTimeout(function() {
                                                    location.reload();
                                            }, 2000);
                                            KTApp.unblock('#formConfigDiv');
                                        } else {
                                            js_notification('error', 'We can not delete the project because it contains data.');
                                             KTApp.unblock('#formConfigDiv');
                                        }
                                    },
                                });
                            } else {
                               location.reload();
                            }
                        });
                     console.log('project delete',projectId,subProjectId);
                });
                $('#navigate-btn').on('click', function () {
                    KTApp.block('#formConfigDiv', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Fetching...',
                    });
                });
                $(document).on('click', '.upload-icon', function () {
                    $('#dynamicTableUploadForm')[0].reset();
                    $('#uploadErrors').hide().empty();
                    $('#uploadSuccess').hide().empty();

                    $('#uploadModal').modal('show');
                });

                $('.close, #clearFileBtn').on('click', function () {
                    $('#dynamicTableUploadForm')[0].reset();
                    $('#uploadErrors').hide().empty();
                    $('#uploadSuccess').hide().empty();

                    $('#uploadModal').modal('hide');
                });
                 $(document).on('click', '.upload-icon', function () {
                    $('#dynamicTableUploadForm')[0].reset();
                    $('#uploadErrors').hide().empty();
                    $('#uploadSuccess').hide().empty();

                    $('#uploadModal').modal('show');
                });

                $('.close, #clearFileBtn').on('click', function () {
                    $('#dynamicTableUploadForm')[0].reset();
                    $('#uploadErrors').hide().empty();
                    $('#uploadSuccess').hide().empty();

                    $('#uploadModal').modal('hide');
                });
         

                
            $('#dynamicTableUploadForm').on('submit', function (event) {
                event.preventDefault();

                $('#uploadErrors').hide().empty();
                $('#uploadSuccess').hide().empty();

                var formData = new FormData(this);

                showGlobalLoader(
                    'Creating Non-AR tables...',
                    true,
                    true
                );

                $.ajax({
                    url: "{{ url('dynamicTablecreationUploadFile') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    global: false,

                    success: function (response) {
                        hideGlobalLoader();

                        if (response.status === 'success') {
                            $('#uploadModal').modal('hide');

                            js_notification(
                                'success',
                                response.message
                            );

                            setTimeout(function () {
                                window.location.reload();
                            }, 1000);

                            return;
                        }

                        $('#uploadErrors')
                            .html(
                                response.message
                                    || 'Dynamic table creation failed.'
                            )
                            .show();

                        js_notification(
                            'error',
                            response.message
                                || 'Dynamic table creation failed.'
                        );
                    },

                    error: function (xhr) {
                        hideGlobalLoader();

                        var message =
                            xhr.responseJSON
                            && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : 'Something went wrong. Please try again.';

                        $('#uploadErrors')
                            .html(message)
                            .show();

                        js_notification('error', message);
                    }
                });
            });

                
        });
    </script>
@endpush
