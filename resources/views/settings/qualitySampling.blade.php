@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card" id="quality_sampling">
        <div class="card-body pt-0 pb-2 pl-8" style="background-color: #ffffff !important">
            <div class="row mr-0 ml-0">
                <div class="col-6 mt-4 pt-0 pb-0 pl-0 pr-0">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <a class="project_header" href="" style="margin-left:-1.7rem">
                        <span class="svg-icon svg-icon-primary svg-icon-lg mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="16" fill="currentColor"
                                class="bi bi-arrow-left project_header_row" viewBox="0 0 16 16"
                                style="width: 1.05rem !important;color: #000000 !important;">
                                <path fill-rule="evenodd"
                                    d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                            </svg>
                        </span>Sampling</a>
                </div>
            </div>
            {!! Form::open([
                'url' => url('qa_sampling_store') . '?parent=' . request()->parent . '&child=' . request()->child,
                'id' => 'qa_sampling_form',
                'class' => 'form',
                'enctype' => 'multipart/form-data',
            ]) !!}
            @csrf
            <div class="row mb-2 mt-2 mr-0 ml-0 align-items-center pt-4 pb-3" style="background-color: #F1F1F1;border-radius:0.42rem">
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label class="required">Project</label>
                    @php $projectList = App\Http\Helper\Admin\Helpers::projectList(); @endphp
                    <fieldset class="form-group mb-1">
                        {!! Form::select('project_id', $projectList, null, [
                            'class' => 'form-control kt_select2_project',
                            'id' => 'project_id',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </fieldset>
                </div>
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>Subproject</label>
                    @php $subProjectList = []; @endphp
                    <fieldset class="form-group mb-1">
                        {!! Form::select('sub_project_id', $subProjectList, null, [
                            'class' => 'text-black form-control kt_select2_sub_project',
                            'id' => 'sub_project_list',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </fieldset>
                </div>
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label>Coder</label>
                    <fieldset class="form-group mb-1">
                        {!! Form::select('coder_emp_id', $coderList, null, [
                            'class' => 'form-control kt_select2_coder',
                            'id' => 'coder_id',
                            'style' => 'width: 100%;; background-color: #ffffff !important;',
                        ]) !!}
                    </fieldset>
                </div>
                <div class="col-lg-2 mb-lg-0 mb-6">
                    <label class="required">QA</label>
                    <fieldset class="form-group mb-1">
                        {!! Form::select('qa_emp_id', $qaList, null, [
                            'class' => 'form-control kt_select2_QA',
                            'id' => 'qa_id',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </fieldset>
                </div>
                <div class="col-lg-1 mb-lg-0 mb-6">
                    {!! Form::label('Percentage', 'Percentage', ['class' => 'required']) !!}
                    <fieldset class="form-group mb-1">
                        <input type="text" name="qa_percentage" id="qa_percentage" class="form-control qa_percentage"
                            autocomplete="nope" onkeypress = "return event.charCode >= 48 && event.charCode <= 57">
                    </fieldset>
                </div>

                <div class="col-lg-1 mb-lg-0 mb-6">
                    <label>Priority</label>
                    <fieldset class="form-group mb-1">
                        {!! Form::Select(
                            'claim_priority',
                            [
                                '' => '--Select--',
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High'
                            ],
                            null,
                            [
                                'class' => 'form-control kt_select2_priority',
                                'autocomplete' => 'none',
                                'id' => 'claim_priority',
                            ],
                        ) !!}
                    </fieldset>
                </div>

                <div class="col-lg-2 mt-8">
                    <button class="btn btn-light-danger" id="clear_submit" tabindex="10" type="button">
                        <span>
                            <span>Clear</span>
                        </span>
                    </button>&nbsp;&nbsp;
                    <button type="submit" class="btn btn-white-black font-weight-bold" id="form_submit"
                        style="background-color: #139AB3">Submit</button>

                </div>
            </div>
            {!! Form::close() !!}
            {{-- <div class="card card-custom" style="border-radius:0px 0px 10px 10px" id="page-loader">
                <div class="card-body pt-4 pb-0 px-5">
                    <div class="mb-0"> --}}
            <div class="table-responsive pb-4">
                <table class="table table-separate table-head-custom no-footer dtr-column " id="qa_sampling_table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Sub Project</th>
                            <th>Coder</th>
                            <th>QA</th>
                            <th>Percentage</th>
                            <th>Priority</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($qaSamplingList))
                            @foreach ($qaSamplingList as $data)
                                @php
                                if ($data['project_id'] != null) {
                                        $projectName = App\Models\project::where(
                                            'project_id',
                                            $data['project_id'],
                                        )->first();
                                    } else {
                                        $projectName = '--';
                                    }
                                    if ($data['sub_project_id'] != null && $data['project_id'] != null) {
                                        $subProjectName = App\Models\subproject::where(
                                            'project_id',
                                            $data['project_id'],
                                        )
                                            ->where('sub_project_id', $data['sub_project_id'])
                                            ->first();
                                    } else {
                                        $subProjectName = '--';
                                    }
                                    $coderName =
                                        $data['coder_emp_id'] != null
                                            ? App\Http\Helper\Admin\Helpers::getUserNameByEmpId($data['coder_emp_id'])
                                            : '--';
                                    $qaName =
                                        $data['qa_emp_id'] != null
                                            ? App\Http\Helper\Admin\Helpers::getUserNameByEmpId($data['qa_emp_id'])
                                            : '--';
                                @endphp
                                <tr class="clickable-row" data-toggle="modal" style="cursor:pointer">
                                    <td><input type="hidden"
                                            value={{ $data['project_id'] }}>{{  ($projectName == '--' || $projectName == null) ? '--' : $projectName->aims_project_name }}</td>
                                    <td><input type="hidden"
                                            value={{ $data['sub_project_id'] != null ? $data['sub_project_id'] : null }}>{{ ($subProjectName == '--' ||  $subProjectName == null) ? '--' : $subProjectName->sub_project_name }}
                                    </td>
                                    <td><input type="hidden"
                                            value={{ $data['coder_emp_id'] != null ? $data['coder_emp_id'] : null }}>{{ $coderName == null ? '--' : $coderName }}
                                    </td>
                                    <td><input type="hidden"
                                            value={{ $data['qa_emp_id'] != null ? $data['qa_emp_id'] : null }}>{{ $qaName == null ? '--' : $qaName }}
                                    </td>
                                    <td><input type="hidden"
                                        value={{ $data['id'] }}>{{ $data['qa_percentage'] . '%' }}</td>
                                    <td>{{ isset($data['claim_priority']) ? $data['claim_priority'] : '--' }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            {{-- </div>
                </div>
            </div> --}}
            <div class="modal fade" id="qa_sampling" role="dialog" data-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #139AB3;">
                            <h4 class="modal-title" style='float:left !important;color:#ffffff'>Edit Sampling</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        </div>

                        <div class="modal-body pb-0">
                            @include('settings.editSampling')
                        </div>

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
                var qaSamplingList = @json($qaSamplingList);console.log(qaSamplingList.length,'ddd');
                $('#qa_sampling_table').DataTable({
                    processing: true,
                    lengthChange: false,
                    searching: false,
                    pageLength: 20,

                });
                $(document).on('change', '#project_id,#edit_project_id', function() {
                    var project_id = $(this).val();
                    var subproject_id = '';
                    KTApp.block('#qa_sampling_form', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Fetching...',
                    });
                    subProjectNameList(project_id,subproject_id);
                    KTApp.unblock('#qa_sampling_form');
                });
                function subProjectNameList(project_id,subproject_id) {
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
                                // sla_options += '<option value="' + key + '">' + value +
                                //     '</option>';

                                sla_options +='<option value="' + key + '"' + (key === subproject_id ? 'selected="selected"' : '') +'>' + value+ '</option>';

                            });
                            $('select[name="sub_project_id"]').html(sla_options);

                        },
                        error: function(jqXHR, exception) {}
                    });
                };
                $(document).on('click', '.clickable-row td:not(:last-child)', function(e) {

                    var project_id = $(this).closest('tr').find('td:eq(0) input').val();
                    var subproject_id = $(this).closest('tr').find('td:eq(1) input').val();
                    var coder_id = $(this).closest('tr').find('td:eq(2) input').val();
                    var qa_emp_id = $(this).closest('tr').find('td:eq(3) input').val();
                    var qa_percentage = $(this).closest('tr').find('td:eq(4)').text();
                    var claim_priority = $(this).closest('tr').find('td:eq(5)').text();
                    var record_id = $(this).closest('tr').find('td:eq(4) input').val();
                    $('#qa_sampling').modal("show");

                    $('select[id="edit_project_id"]').val(project_id).trigger('change');
                    $('select[id="edit_sub_project_list"]').val(subproject_id).trigger('change');
                    $('select[id="edit_coder_id"]').val(coder_id).trigger('change');
                    $('select[id="edit_qa_id"]').val(qa_emp_id).trigger('change');
                    $('input[id="edit_qa_percentage"]').val(qa_percentage.slice(0, -1));
                    $('select[id="edit_claim_priority"]').val(claim_priority).trigger('change');
                    $('input[name="record_id"]').val(record_id);
                    subProjectNameList(project_id,subproject_id);
                });
                $(document).on('click', '#form_submit', function(e) {
                    e.preventDefault();

                    var project_id = $('#project_id');
                    var qa_id = $('#qa_id');
                    var qa_percentage = $('#qa_percentage');
                    var sub_project_id = $('#sub_project_list');
                    var coder_id = $('#coder_id');
                    var inputTypeValue = 0;
                    if (project_id.val() == '' || qa_id.val() == '' || qa_percentage.val() == '') {
                        if (project_id.val() == '') {
                            project_id.next('.select2').find(".select2-selection").css('border-color', 'red');
                        } else {
                            project_id.next('.select2').find(".select2-selection").css('border-color', '');
                        }
                        if (qa_id.val() == '') {
                            qa_id.next('.select2').find(".select2-selection").css('border-color', 'red');
                        } else {
                            qa_id.next('.select2').find(".select2-selection").css('border-color', '');
                        }
                        if (qa_percentage.val() == '') {
                            qa_percentage.css('border-color', 'red');
                        } else {
                            qa_percentage.css('border-color', '');
                        }
                        return false;
                    }
                    if(qaSamplingList.length > 0) {
                        $.each(qaSamplingList, function(key, val) {
                            projectId = project_id.val() != '' ? project_id.val() : null;
                            subProjectId = sub_project_id.val() != '' ? sub_project_id.val() : null;
                            qaId = qa_id.val() != '' ? qa_id.val() : null;
                            coderId = coder_id.val() != '' ? coder_id.val() : null;console.log(val,'val',(val.project_id),(val.sub_project_id),(val.qa_emp_id),(val.coder_emp_id),projectId,subProjectId,coderId,qaId);
                            if (projectId == val.project_id && subProjectId == val.sub_project_id && qaId == val.qa_emp_id && coderId == val.coder_emp_id) {
                                    js_notification('error', 'This Setting already exist!');
                                     inputTypeValue = 1;
                                    return false;
                                } else {
                                    inputTypeValue = 0;
                                }

                        });
                    }console.log(inputTypeValue,'inputTypeValue');
                     if(inputTypeValue == 0) {
                         document.querySelector('#qa_sampling_form').submit();
                     }
                });
                $('#qa_sampling_update').submit(function(e) {
                    e.preventDefault();
                    var edit_project_id = $('#edit_project_id');
                    var edit_qa_id = $('#edit_qa_id');
                    var edit_qa_percentage = $('#edit_qa_percentage');
                    var edit_sub_project_id = $('#edit_sub_project_list');
                    var edit_coder_id = $('#edit_coder_id');
                    var record_id = $('#record_id').val();
                    var inputTypeValue = 0;
                    if (edit_project_id.val() == '' || edit_qa_id.val() == '' || edit_qa_percentage.val() == '') {
                        if (edit_project_id.val() == '') {
                            edit_project_id.next('.select2').find(".select2-selection").css('border-color', 'red');
                        } else {
                            edit_project_id.next('.select2').find(".select2-selection").css('border-color', '');
                        }
                        if (edit_qa_id.val() == '') {
                            edit_qa_id.next('.select2').find(".select2-selection").css('border-color', 'red');
                        } else {
                            edit_qa_id.next('.select2').find(".select2-selection").css('border-color', '');
                        }
                        if (edit_qa_percentage.val() == '') {
                            edit_qa_percentage.css('border-color', 'red');
                        } else {
                            edit_qa_percentage.css('border-color', '');
                        }
                        return false;
                    }
                        if(qaSamplingList.length > 0) {console.log(qaSamplingList,'qaSamplingList');
                            $.each(qaSamplingList, function(key, val) {
                                projectId = edit_project_id.val() != '' ? edit_project_id.val() : null;
                                subProjectId = edit_sub_project_id.val() != '' ? edit_sub_project_id.val() : null;
                                qaId = edit_qa_id.val() != '' ? edit_qa_id.val() : null;
                                coderId = edit_coder_id.val() != '' ? edit_coder_id.val() : null;console.log(val,'val',(val.project_id),(val.sub_project_id),(val.qa_emp_id),(val.coder_emp_id),projectId,subProjectId,coderId,qaId);
                                if (projectId == val.project_id && subProjectId == val.sub_project_id && qaId == val.qa_emp_id && coderId == val.coder_emp_id && record_id != val.id) {
                                        js_notification('error', 'This Setting already exist!');
                                        inputTypeValue = 1;
                                        return false;
                                    } else {
                                        inputTypeValue = 0;
                                    }

                            });
                        }console.log(inputTypeValue,'inputTypeValue');
                        if(inputTypeValue == 0) {
                            document.querySelector('#qa_sampling_update').submit();
                        }

                });
            });
        </script>
    @endpush
