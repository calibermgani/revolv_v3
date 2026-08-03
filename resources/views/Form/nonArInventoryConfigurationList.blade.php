@extends('layouts.app3')

@section('content')
<div
    class="card card-custom mb-5 custom-card"
    id="nonArConfigurationDiv">
    <div class="card-body pb-4 mt-2">
          <div class="my-div">
            <span class="project_header">Non AR Projects List</span>
         </div>    
        
        <div class="table-responsive pt-5">
            <table
                class="table table-separate table-head-custom no-footer dtr-column"
                id="nonArConfigurationList"
            >
                <thead>
                    <tr>
                        <th width="17%">Project Name</th>
                        <th width="13%">Sub Project Name</th>
                        <th>Column Fields</th>
                        <th width="12%" class="text-center">
                            Inventory Upload
                        </th>
                        <th width="4%"></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($nonArConfigurations as $configuration)
                        <tr
                            data-project-id="{{ $configuration->project_id }}"
                            data-sub-project-id="{{ $configuration->sub_project_id }}"
                        >
                            <td>
                                {{ $configuration->aims_project_name
                                    ?: $configuration->project_name }}
                            </td>

                            <td>
                                {{ $configuration->sub_project_name }}
                            </td>

                            <td
                                style="
                                    word-wrap:break-word;
                                    white-space:normal;
                                    overflow-wrap:break-word;
                                    word-break:break-word;
                                "
                            >
                                {{ $configuration->data_columns }}
                            </td>

                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-link p-1 non-ar-upload"
                                    data-project-id="{{ $configuration->project_id }}"
                                    data-sub-project-id="{{ $configuration->sub_project_id }}"
                                    title="Upload inventory"
                                >
                                    <i
                                        class="fa fa-upload"
                                        style="
                                            font-size:18px;
                                            color:#139AB3;
                                        "
                                    ></i>
                                </button>

                                <a
                                    href="{{ route('downloadNonArInventoryTemplate', [
                                        'project_id' => $configuration->project_id,
                                        'sub_project_id' => $configuration->sub_project_id
                                    ]) }}"
                                    class="btn btn-link p-1"
                                    title="Download inventory template"
                                >
                                    <i
                                        class="fa fa-download"
                                        style="
                                            font-size:18px;
                                            color:#139AB3;
                                        "
                                    ></i>
                                </a>
                            </td>

                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-link p-1 non-ar-delete"
                                    data-project-id="{{ $configuration->project_id }}"
                                    data-sub-project-id="{{ $configuration->sub_project_id }}"
                                    title="Delete configuration"
                                >
                                    <i
                                        class="fa fa-trash text-danger"
                                        style="font-size:15px;"
                                    ></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal" id="inventoryUploadModal" tabindex="-1" role="dialog"  aria-labelledby="inventoryUploadModalLabel" aria-hidden="true" data-backdrop="static">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="inventoryUploadModalLabel">
                                Inventory Upload
                            </h5>

                           <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:black">&times;</button>
                        </div>

                        <form id="inventoryuploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="inventoryProjectId" name="project_id">
                                <input type="hidden" id="inventorySubProjectId" name="sub_project_id">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label>Select File <span style="color:red;">(Upload only CSV file)</span></label>
                                        <input type="file"
                                            class="form-control"
                                            name="file"
                                            id="inventoryUploadFile"
                                            accept=".csv"
                                            required>
                                    </div>
                                </div>
                                <div id="inventoryUploadErrors"
                                    class="alert alert-danger"
                                    style="display:none;">
                                </div>
                                <div id="inventoryUploadSuccess"
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
                                <button type="submit"  
                                        class="btn" style="background-color: #139AB3 !important;color: #ffffff;">
                                    Upload
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
@endsection
@push('view.scripts')
<script>
    $(document).ready(function () {

                var nonArTable = $('#nonArConfigurationList').DataTable({
                    lengthChange: false,
                    searching: true,
                    pageLength: 20,
                    autoWidth: false,
                    language: {
                        search: '',
                        searchPlaceholder: '   Search'
                    },
                    columnDefs: [
                        {
                            targets: [3, 4],
                            orderable: false,
                            searchable: false
                        }
                    ]
                });

            

                $('.close, #clearFileBtn').on('click', function () {
                    $('#inventoryuploadForm')[0].reset();
                    $('#inventoryUploadErrors').hide().empty();
                    $('#inventoryUploadSuccess').hide().empty();

                    $('#inventoryUploadModal').modal('hide');
                });

                /*
                * Row-level inventory upload button.
                *
                * This stores project/sub-project IDs so they can be sent to your
                * actual inventory upload modal or upload route.
                */
                $(document).on('click', '.non-ar-upload', function () {
                    var projectId = $(this).data('project-id');
                    var subProjectId = $(this).data('sub-project-id');

                    /*
                    * Connect this event to your inventory file-upload modal.
                    */
                    $('#inventoryProjectId').val(projectId);
                    $('#inventorySubProjectId').val(subProjectId);

                    $('#inventoryUploadModal').modal('show');
                });

                /*
                * Delete configuration only when the generated _datas table is empty.
                */
                $(document).on('click', '.non-ar-delete', function () {
                    var button = $(this);
                    var row = button.closest('tr');

                    var projectId = button.data('project-id');
                    var subProjectId = button.data('sub-project-id');

                    Swal.fire({
                        text: 'Are you sure you want to delete this configuration?',
                        icon: 'warning',
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'No',
                        reverseButtons: true,
                        customClass: {
                            confirmButton:
                                'btn font-weight-bold btn-white-black',
                            cancelButton:
                                'btn font-weight-bold btn-light-danger'
                        }
                    }).then(function (result) {
                        if (!result.value) {
                            return;
                        }

                        KTApp.block('#nonArConfigurationDiv', {
                            overlayColor: '#000000',
                            state: 'danger',
                            opacity: 0.1,
                            message: 'Checking inventory data...'
                        });

                        $.ajax({
                            url: "{{ route('deleteNonArInventoryConfiguration') }}",
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                project_id: projectId,
                                sub_project_id: subProjectId,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },

                            success: function (response) {
                                KTApp.unblock('#nonArConfigurationDiv');

                                if (response.status === 'success') {
                                    nonArTable
                                        .row(row)
                                        .remove()
                                        .draw(false);

                                    js_notification(
                                        'success',
                                        response.message
                                    );

                                    return;
                                }

                                js_notification(
                                    'error',
                                    response.message
                                        || 'Unable to delete the configuration.'
                                );
                            },

                            error: function (xhr) {
                                KTApp.unblock('#nonArConfigurationDiv');

                                var message =
                                    xhr.responseJSON
                                    && xhr.responseJSON.message
                                        ? xhr.responseJSON.message
                                        : 'Unable to delete the configuration.';

                                js_notification('error', message);
                            }
                        });
                    });
                });

    
                $('#inventoryuploadForm').on('submit', function(e) {
                    e.preventDefault();

                    $('#inventoryUploadErrors').hide().empty();
                    $('#inventoryUploadSuccess').hide().empty();

                    var formData = new FormData(this);
                    var switchedToInsertMessage = false;

                    showGlobalLoader("File uploading...", true, true);

                    function switchToDataInserting() {
                        if (switchedToInsertMessage) {
                            return;
                        }

                        switchedToInsertMessage = true;

                        updateGlobalLoaderMessage("Data inserting...");
                        updateGlobalLoaderPercent(100);

                        setTimeout(function () {
                            $('#global-loader-percent').hide();
                            restartGlobalLoaderTimer();
                        }, 500);
                    }

                    $.ajax({
                        url: "{{ url('inventoryuploadFile') }}",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        global: false,

                        xhr: function() {
                            var xhr = new window.XMLHttpRequest();

                            xhr.upload.addEventListener("progress", function(event) {
                                if (event.lengthComputable) {
                                    var percent = Math.round((event.loaded / event.total) * 100);

                                    updateGlobalLoaderPercent(percent);

                                    if (percent >= 100) {
                                        switchToDataInserting();
                                    }
                                }
                            }, false);

                            xhr.upload.addEventListener("load", function() {
                                switchToDataInserting();
                            }, false);

                            return xhr;
                        },

                        success: function(response) {
                            hideGlobalLoader();

                            $('#inventoryUploadErrors').hide().empty();
                            $('#inventoryUploadSuccess').hide().empty();

                            if (response.status === 'success') {

                                js_notification(
                                    'success',
                                    response.message
                                );

                                $('#inventoryUploadModal').modal('hide');

                                $('#inventoryuploadForm')[0].reset();

                                $('#inventoryProjectId').val('');
                                $('#inventorySubProjectId').val('');

                            } else if (response.status === 'warning') {

                                js_notification(
                                    'error',
                                    response.message
                                );

                                /*
                                * Keep modal open so the user can see the error
                                * and select another file.
                                */
                                $('#inventoryUploadErrors')
                                    .html(response.message)
                                    .show();

                            } else {

                                $('#inventoryUploadErrors')
                                    .html('Something went wrong. Please try again.')
                                    .show();
                            }
                        },

                        error: function(xhr) {
                            hideGlobalLoader();

                            $('#inventoryUploadErrors').hide().empty();
                            $('#inventoryUploadSuccess').hide().empty();

                            var errorHtml = '';

                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                $.each(xhr.responseJSON.errors, function(key, value) {
                                    errorHtml += '<p>' + value + '</p>';
                                });
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorHtml = xhr.responseJSON.message;
                            } else {
                                errorHtml = 'Something went wrong. Please try again.';
                            }

                            $('#inventoryUploadErrors').html(errorHtml).show();
                            js_notification('error', errorHtml);
                        }
                    });
                });
    });
</script>
@endpush