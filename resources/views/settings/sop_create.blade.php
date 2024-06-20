@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card" id="sop_upload">
        <div class="card-header">
            <div class="card-title">
                <span class="text-muted font-weight-bold font-size-lg flex-grow-1">
                    <span class="project_header" href="" style="margin-left:0.3rem">SOP Upload</span>
                </span>
            </div>
        </div>
        <div class="card-body py-0 px-7">
            <div class="row">
                <div class="col-md-12 p-0">
                    {!! Form::open([
                        'url' => url('sop/sop_doc_store') . '?parent=' . request()->parent . '&child=' . request()->child,
                        'id' => 'sop_upload_form',
                        'class' => 'form',
                        'enctype' => 'multipart/form-data',
                    ]) !!}
                    @csrf

                    <div class="col-md-6">
                        <div class="form-group row ">
                            <label class="col-md-3 col-form-label required">Project Name</label>
                            <div class="col-md-9">
                                @php $projectList = App\Http\Helper\Admin\Helpers::projectList(); @endphp
                                {!! Form::select('project_id', $projectList, null, [
                                    'class' => 'form-control kt_select2_project',
                                    'id' => 'project_list',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row ">
                            <label class="col-md-3 col-form-label">Sub Project</label>
                            <div class="col-md-9">
                                @php $subProjectList = []; @endphp
                                {!! Form::select('sub_project_id', $subProjectList, null, [
                                    'class' => 'form-control kt_select2_sub_project',
                                    'id' => 'sub_project_list',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 attach" id="attach">
                        <div class="form-group row ">
                            <label class="col-md-3 col-form-label required" id="label_attach">SOP</label>
                            <div class="col-md-8">
                                <div class="uppy" id="drop-zone1">
                                    <div class="uppy-wrapper" id="sop_upload_btn">
                                        <div class="uppy-Root uppy-FileInput-container">
                                            <input class="uppy-FileInput-input uppy-input-control file-input" type="file"
                                                name="attachment" id="kt_uppy_5_input_control" style=""
                                                autocomplete="nope" accept=".pdf">
                                            <label class="uppy-input-label btn btn-light-primary btn-sm btn-bold"
                                                for="kt_uppy_5_input_control">Upload
                                            </label>
                                            <p id="p1"></p>
                                            <div id='sop_filename' class = "sop_filename"></div>
                                        </div>
                                    </div>
                                </div>
                                <div id="error_file" style="color:red"></div>
                            </div>
                        </div>
                        {{-- <div id="p1" style="margin-left: 12rem !important">
                            <p></p>
                        </div> --}}
                    </div>
                    <div class="form-footer" style="margin-right: 0 !important">
                        <button class="btn btn-light-danger" id="clear_submit" tabindex="10" type="button">
                            <span>
                                <span>Clear</span>
                            </span>
                        </button>&nbsp;&nbsp;
                        <button type="submit" class="btn btn-white-black font-weight-bold"
                            id="sop_upload_save">Submit</button>

                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('view.scripts')
    <script>
        $(document).on('change', '#project_list', function() {
            var project_id = $(this).val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "GET",
                url: "{{ url('sop/sub_project_list') }}",
                data: {
                    project_id: project_id
                },
                success: function(res) {
                    subprojectCount = Object.keys(res.subProject).length;
                    var myArray = res.existingSubProject;
                    var sla_options = '<option value="">-- Select --</option>';
                    $.each(res.subProject, function(key, value) {
                        sla_options += '<option value="' + key + '" ' +
                            (myArray.length > 0 && $.inArray(key, myArray) !== -1 ? 'disabled' :
                                '') +
                            '>' + value +
                            '</option>';
                    });
                    $('select[name="sub_project_id"]').html(sla_options);
                },
                error: function(jqXHR, exception) {}
            });
        });
        $('#kt_uppy_5_input_control').change(function() {
            var allowedExtensions = /(\.pdf)$/i;
            var fileName = $(this).val();

            if (!allowedExtensions.exec(fileName)) {
                $('#error_file').html('*Please select a PDF file Only');
                $(this).val('');
                return false;
            } else {
                $('#error_file').html('');
            }
        });
        $(document).on('click', '#sop_upload_btn', function() {
            var dropZoneId = "drop-zone1";
            var buttonId = "sop_upload_btn";
            var dropZone = $("#" + dropZoneId);
            var inputFile = dropZone.find("input");
            var finalFiles = {};
            $(function() {
                inputFile.on('change', function(e) {
                    finalFiles = {};
                    $('#sop_filename').html("");
                    var fileNum = this.files.length,
                        counter = 0;
                    $.each(this.files, function(idx, elm) {
                        finalFiles[idx] = elm;
                    });
                    for (let initial = 0; initial < fileNum; initial++) {
                        counter = counter + 1;
                        $('#sop_filename').append('<div class="mutipleupd" id="file_' + initial +
                            ' "><span class="fa-stack fa-lg"><i class="fa fa-file fa-stack-1x" style="color: #0e969c;font-size: 16px;"></i><strong class="fa-stack-1x" style="color:#FFF; font-size:12px; margin-top:2px;">' +
                            counter + '</strong></span> ' + this.files[initial].name +
                            '&nbsp;&nbsp;<i class="fas fa-check"></i></div>');
                    }
                });
            })
        })
        $(document).on('click', '#sop_upload_save', function(e) {
            var project_id = $('#project_list');
            var sub_project_id = $('#sub_project_list');
            var assestment_upload = $('#kt_uppy_5_input_control');
            if (project_id.val() == '' || (sub_project_id.val() == '' && subprojectCount > 0) || assestment_upload
                .val() == '') {
                if (project_id.val() == '') {
                    project_id.next('.select2').find(".select2-selection").css('border-color', 'red');
                } else {
                    project_id.next('.select2').find(".select2-selection").css('border-color', '');
                }
                if (sub_project_id.val() == '' && subprojectCount > 0) {
                    sub_project_id.next('.select2').find(".select2-selection").css('border-color', 'red');
                } else {
                    sub_project_id.next('.select2').find(".select2-selection").css('border-color', '');
                }
                if (assestment_upload.val() == '') {
                    $('#p1').text('* SOP Attachment Mandatory').css("color", "red");
                    assestment_upload.css('border-color', 'red');
                } else {
                    $('#p1').text('');
                }
                return false;
            }
        });
    </script>
@endpush
