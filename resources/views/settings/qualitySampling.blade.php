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
            <div class="row  mt-2 mr-0 ml-0 align-items-center pt-4 pb-3" style="background-color: #F1F1F1;border-radius:0.42rem">
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
                    <label>AR</label>
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
            </div>
            <div id="sampling_container" class="row mr-0 ml-0 align-items-center pt-4 pb-3" style="background-color: #F1F1F1;border-radius:0.42rem;display:none"></div>
            <div class="row mb-2  mr-0 ml-0 align-items-center pt-4 pb-3" style="background-color: #F1F1F1;border-radius:0.42rem">
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
            <div class="table-responsive pb-4">
                <table class="table table-separate table-head-custom no-footer dtr-column " id="qa_sampling_table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Sub Project</th>
                            <th>AR</th>
                            <th>QA</th>
                            <th>Percentage</th>
                            <th>Priority</th>
                            <th>Column Name</th>
                            <th>Column Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        if(isset($qaSamplingCoders)&& !empty($qaSamplingCoders)) {
                            $samplingCoders =  App\Http\Helper\Admin\Helpers::getUserNameListByEmpId($qaSamplingCoders);
                        } else {
                            $samplingCoders =  '--';
                        }
                        if(isset($qaSamplingQaEmpList)&& !empty($qaSamplingQaEmpList)) {
                            $samplingQas =  App\Http\Helper\Admin\Helpers::getUserNameListByEmpId($qaSamplingQaEmpList);
                        } else {
                            $samplingQas =  '--';
                        }
                    @endphp
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
                                @endphp
                                <tr class="clickable-row" data-toggle="modal" style="cursor:pointer">
                                    <td><input type="hidden"
                                            value={{ $data['project_id'] }}>{{  ($projectName == '--' || $projectName == null) ? '--' : $projectName->aims_project_name }}</td>
                                    <td><input type="hidden"
                                            value={{ $data['sub_project_id'] != null ? $data['sub_project_id'] : null }}>{{ ($subProjectName == '--' ||  $subProjectName == null) ? '--' : $subProjectName->sub_project_name }}
                                    </td>
                                    <td><input type="hidden"
                                            value={{ $data['coder_emp_id'] != null ? $data['coder_emp_id'] : null }}>
                                            {{$samplingCoders != '--' && $data['coder_emp_id'] != null ? $samplingCoders[$data['coder_emp_id']] : '--'}}
                                    </td>
                                    <td><input type="hidden"
                                            value={{ $data['qa_emp_id'] != null ? $data['qa_emp_id'] : null }}>
                                            {{$samplingQas != '--' && $data['qa_emp_id'] != null ? $samplingQas[$data['qa_emp_id']] : '--'}}
                                     </td>
                                    <td><input type="hidden"
                                        value={{ $data['id'] }}>{{ $data['qa_percentage'] . '%' }}</td>
                                    <td>{{ isset($data['claim_priority']) ? $data['claim_priority'] : '--' }}</td>
                                    <td>{{ isset($data['qa_sample_column_name']) ? ($data['qa_sample_column_name'] != NULL ? ucwords(str_replace(['_else_', '_'], ['/', ' '], $data['qa_sample_column_name'])) :'--'): '--' }}</td>
                                    <td>{{ isset($data['qa_sample_column_value']) ? ($data['qa_sample_column_value'] != NULL ? $data['qa_sample_column_value'] : '--') : '--' }}</td>
                               
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
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
    <style>
        .kt_select2_value {
            background-color: white !important; 
        }
        .select2-container--default .select2-selection--single {
            background-color: white !important; 
            border: 1px solid #ced4da; /* Optional: Match Bootstrap styling */
            height: 38px; /* Adjust height if needed */
        }
    </style>
    @push('view.scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script>
            $(document).ready(function() {
                var qaSamplingList = @json($qaSamplingList);
                var subprojectCount;
                $('#qa_sampling_table').DataTable({
                    processing: true,
                    lengthChange: false,
                    searching: true,
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
                             var qa_id = $('#qa_id');
                            var qa_percentage = $('#qa_percentage');
                            var sub_project_id = $('#sub_project_list');
                            qa_id.next('.select2').find(".select2-selection").css('border-color', '');
                            qa_percentage.css('border-color', '');
                            sub_project_id.next('.select2').find(".select2-selection").css('border-color', '');
                            var edit_qa_id = $('#edit_qa_id');
                            var edit_qa_percentage = $('#edit_qa_percentage');
                            var edit_sub_project_id = $('#edit_sub_project_list');
                            edit_qa_id.next('.select2').find(".select2-selection").css('border-color', '');
                            edit_qa_percentage.css('border-color', '');
                            edit_sub_project_id.next('.select2').find(".select2-selection").css('border-color', '');
                           

                        },
                        error: function(jqXHR, exception) {}
                    });
                };
                $(document).on('click', '.clickable-row', function(e) {

                    var project_id = $(this).closest('tr').find('td:eq(0) input').val();
                    var subproject_id = $(this).closest('tr').find('td:eq(1) input').val();
                    var coder_id = $(this).closest('tr').find('td:eq(2) input').val();
                    var qa_emp_id = $(this).closest('tr').find('td:eq(3) input').val();
                    var qa_percentage = $(this).closest('tr').find('td:eq(4)').text();
                    var claim_priority = $(this).closest('tr').find('td:eq(5)').text();
                    var record_id = $(this).closest('tr').find('td:eq(4) input').val();
                    var qa_sample_column_name = $(this).closest('tr').find('td:eq(6)').text();
                    var qa_sample_column_val = $(this).closest('tr').find('td:eq(7)').text();
                    $('#qa_sampling').modal("show");

                    $('select[id="edit_project_id"]').val(project_id).trigger('change');
                    $('select[id="edit_sub_project_list"]').val(subproject_id).trigger('change');
                    $('select[id="edit_coder_id"]').val(coder_id).trigger('change');
                    $('select[id="edit_qa_id"]').val(qa_emp_id).trigger('change');
                    $('input[id="edit_qa_percentage"]').val(qa_percentage.slice(0, -1));
                    $('select[id="edit_claim_priority"]').val(claim_priority).trigger('change');
                    $('input[name="record_id"]').val(record_id);
                    subProjectNameList(project_id,subproject_id);console.log(qa_sample_column_name,'qa_sample_column_name',qa_sample_column_val);
                    var editContainer = $('#edit_sampling_container');
                    if(qa_sample_column_name != '--' && qa_sample_column_val != '--') {   
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        KTApp.block('#qa_sampling_update', {
                            overlayColor: '#000000',
                            state: 'danger',
                            opacity: 0.1,
                            message: 'Fetching...',
                        });
                        $.ajax({
                            type: "GET",
                            url: "{{ url('sampling_columns_list') }}",
                            data: {
                                project_id: project_id,
                                sub_project_id: subproject_id
                            },
                            success: function(res) {                    
                              
                                editContainer.empty(); // Clear previous entries

                                if (res.sampling_column_name.length > 0) {
                                    editContainer.css('display', 'block');

                                    var row = $('<div class="row mr-0 ml-0 align-items-center pt-4 pb-3"></div>'); // Create a new row
                                    $.each(res.sampling_column_name, function(index, columnDetails) {
                                        var labelName = columnDetails.sampling_column_name.replace(/_else_/g, '/').replace(/_/g, ' ').replace(/\b\w/g, function(l) {
                                            return l.toUpperCase();
                                        });
                                        qaSampleColumnValArr = qa_sample_column_val.split(",").map(item => item.trim());
                                        if(columnDetails.sampling_column_input_type == 'text') {
                                            var inputTypeHtml = ` <input type="text" name="${columnDetails.sampling_column_name}" class="form-control" autocomplete="off" value="${qaSampleColumnValArr[index]}">`;
                                        } else  {
                                            // var optionValues = columnDetails.sampling_column_value.split(','); // Convert CSV string to array
                                            // var optionsHtml = optionValues.map(value => `<option value="${value.trim()}">${value.trim()}</option>`).join('');
                                            var optionValues = columnDetails.sampling_column_value; // Already an array

                                            var optionsHtml = optionValues.map(value => 
                                                `<option value="${value}" ${value === qaSampleColumnValArr[index] ? 'selected' : ''}>${value}</option>`
                                            ).join('');

                                            inputTypeHtml = `<select class="form-control kt_select2_value" name="${columnDetails.sampling_column_name}">
                                                    <option value="">Select</option>
                                                ${optionsHtml}</select>`;
                                                setTimeout(() => {
                                                    $('.kt_select2_value').select2({
                                                        placeholder: "Select",
                                                        allowClear: false
                                                    }).next('.select2-container').css('background-color', 'white !important');
                                                }, 100);
                                        }

                                        var fieldHTML = `<div class="col-md-6"><div class="form-group row row_mar_bm"><label class="col-md-12 col-form-label">${labelName}</label>
                                            <div class="col-md-11">${inputTypeHtml}</div></div></div>`;

                                        row.append(fieldHTML);

                                        // If 6 columns are added, start a new row
                                        if ((index + 1) % 6 === 0) {
                                            editContainer.append(row);
                                            row = $('<div class="row"></div>'); // Create a new row
                                        }
                                        KTApp.unblock('#qa_sampling_update');
                                    });

                                    // Append the last row if it has remaining columns
                                    if (row.children().length > 0) {
                                        editContainer.append(row);
                                    }
                                } else {
                                    editContainer.css('display', 'none');
                                }
                            },
                            error: function(jqXHR, exception) {
                                console.error("Error fetching data", exception);
                            }
                        });                           
                        
                    } else {
                        editContainer.css('display', 'none');
                    }
                    
                });
                $(document).on('click', '#form_submit', function(e) {
                    e.preventDefault();

                    var project_id = $('#project_id');
                    var qa_id = $('#qa_id');
                    var qa_percentage = $('#qa_percentage');
                    var sub_project_id = $('#sub_project_list');
                    var coder_id = $('#coder_id');
                     var inputTypeValue = 0;
                    if (project_id.val() == '' || qa_id.val() == '' || qa_percentage.val() == '' || sub_project_id.val() == "") {
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
                        if (sub_project_id.val() == '' && subprojectCount != 0) {
                            sub_project_id.next('.select2').find(".select2-selection").css('border-color', 'red');
                        } else {
                            sub_project_id.next('.select2').find(".select2-selection").css('border-color', '');
                        }
                        return false;
                    }
                    if(qaSamplingList.length > 0) {
                        $.each(qaSamplingList, function(key, val) {
                            projectId = project_id.val() != '' ? project_id.val() : null;
                            subProjectId = sub_project_id.val() != '' ? sub_project_id.val() : null;
                            qaId = qa_id.val() != '' ? qa_id.val() : null;
                            coderId = coder_id.val() != '' ? coder_id.val() : null;
                            var storeFormData = $('#qa_sampling_form').serialize();
                            var params = new URLSearchParams(storeFormData); 
                            var excludeKeys = ["_token", "project_id", "sub_project_id", "coder_emp_id", "qa_emp_id", "qa_percentage", "claim_priority"];
                            var qaSampleColumnNames = [];
                            var qaSampleColumnValues = [];
                            params.forEach((value, key) => {
                                if (!excludeKeys.includes(key)) {
                                    qaSampleColumnNames.push(key);
                                    if(value) {
                                       qaSampleColumnValues.push(value);
                                    }
                                }
                            });
                            qaSampleColumnNames = qaSampleColumnValues.length > 0 ? qaSampleColumnNames.join(',') : null;
                            qaSampleColumnValues = qaSampleColumnValues.length > 0 ? qaSampleColumnValues.join(',') : null;
                                                     
                            if (projectId == val.project_id && subProjectId == val.sub_project_id && qaId == val.qa_emp_id && coderId == val.coder_emp_id && qaSampleColumnNames == val.qa_sample_column_name && qaSampleColumnValues == val.qa_sample_column_value) {
                                    js_notification('error', 'This Setting already exist!');
                                     inputTypeValue = 1;
                                    return false;
                                } else {
                                    inputTypeValue = 0;
                                }

                        });
                    }
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
                    if (edit_project_id.val() == '' || edit_qa_id.val() == '' || edit_qa_percentage.val() == '' || edit_sub_project_id.val() == '') {
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
                        if (edit_sub_project_id.val() == '' && subprojectCount != 0) {
                            edit_sub_project_id.next('.select2').find(".select2-selection").css('border-color', 'red');
                        } else {
                            edit_sub_project_id.next('.select2').find(".select2-selection").css('border-color', '');
                        }
                        return false;
                    }
                        if(qaSamplingList.length > 0) {
                            $.each(qaSamplingList, function(key, val) {
                                projectId = edit_project_id.val() != '' ? edit_project_id.val() : null;
                                subProjectId = edit_sub_project_id.val() != '' ? edit_sub_project_id.val() : null;
                                qaId = edit_qa_id.val() != '' ? edit_qa_id.val() : null;
                                coderId = edit_coder_id.val() != '' ? edit_coder_id.val() : null;
                                var storeFormData = $('#qa_sampling_update').serialize();
                                var params = new URLSearchParams(storeFormData); 
                                var excludeKeys = ["_token", "project_id", "sub_project_id", "coder_emp_id", "qa_emp_id", "qa_percentage", "claim_priority"];
                                var qaSampleColumnNames = [];
                                var qaSampleColumnValues = [];
                                params.forEach((value, key) => {
                                    if (!excludeKeys.includes(key)) {
                                        qaSampleColumnNames.push(key);
                                        if(value) {
                                        qaSampleColumnValues.push(value);
                                        }
                                    }
                                });
                            qaSampleColumnNames = qaSampleColumnValues.length > 0 ? qaSampleColumnNames.join(',') : null;
                            qaSampleColumnValues = qaSampleColumnValues.length > 0 ? qaSampleColumnValues.join(',') : null;console.log(val,'qaSampleColumnNames',qaSampleColumnNames,qaSampleColumnValues);
                            
                                if (projectId == val.project_id && subProjectId == val.sub_project_id && qaId == val.qa_emp_id && coderId == val.coder_emp_id && qaSampleColumnNames == val.qa_sample_column_name && qaSampleColumnValues == val.qa_sample_column_value) {
                                        js_notification('error', 'This Setting already exist!');
                                        inputTypeValue = 1;
                                        return false;
                                    } else {
                                        inputTypeValue = 0;
                                    }

                            });
                        }
                        if(inputTypeValue == 0) {
                            document.querySelector('#qa_sampling_update').submit();
                        }

                });
                $(document).on('change', '#sub_project_list', function() {
                    var sam_project_id = $('#project_id').val();
                    var sam_subproject_id = $(this).val();

                    KTApp.block('#qa_sampling_form', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Fetching...',
                    });

                    samplingColumnsList(sam_project_id, sam_subproject_id);

                    KTApp.unblock('#qa_sampling_form');
                });

                function samplingColumnsList(sam_project_id, sam_subproject_id) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: "GET",
                        url: "{{ url('sampling_columns_list') }}",
                        data: {
                            project_id: sam_project_id,
                            sub_project_id: sam_subproject_id
                        },
                        success: function(res) {
                            var container = $('#sampling_container');
                            container.empty(); // Clear previous entries

                            if (res.sampling_column_name.length > 0) {
                                container.css('display', 'block');

                                var row = $('<div class="row mr-0 ml-0 align-items-center pt-4 pb-3"></div>'); // Create a new row
                                $.each(res.sampling_column_name, function(index, columnDetails) {
                                    var labelName = columnDetails.sampling_column_name.replace(/_else_/g, '/').replace(/_/g, ' ').replace(/\b\w/g, function(l) {
                                        return l.toUpperCase();
                                    });
                               //     Var OptionValues = columnDetails.sampling_column_value;//ins1,ins2,ins3
                                    if(columnDetails.sampling_column_input_type == 'text') {
                                        var inputTypeHtml = ` <input type="text" name="${columnDetails.sampling_column_name}" class="form-control" autocomplete="off">`;
                                    } else  {
                                        // var optionValues = columnDetails.sampling_column_value.split(','); // Convert CSV string to array
                                        // var optionsHtml = optionValues.map(value => `<option value="${value.trim()}">${value.trim()}</option>`).join('');
                                        var optionValues = columnDetails.sampling_column_value; // Already an array

                                        var optionsHtml = optionValues.map(value => 
                                            `<option value="${value}">${value}</option>`
                                        ).join('');

                                        inputTypeHtml = `<select class="form-control kt_select2_value" name="${columnDetails.sampling_column_name}">
                                                <option value="">Select</option>
                                            ${optionsHtml}</select>`;
                                            setTimeout(() => {
                                                $('.kt_select2_value').select2({
                                                    placeholder: "Select",
                                                    allowClear: true
                                                }).next('.select2-container').css('background-color', 'white !important');
                                            }, 100);
                                    }

                                    var fieldHTML = `<div class="col-lg-2 mb-lg-0 mb-6"><label>${labelName}</label>${inputTypeHtml}</div>`;

                                    row.append(fieldHTML);

                                    // If 6 columns are added, start a new row
                                    if ((index + 1) % 6 === 0) {
                                        container.append(row);
                                        row = $('<div class="row"></div>'); // Create a new row
                                    }
                                });

                                // Append the last row if it has remaining columns
                                if (row.children().length > 0) {
                                    container.append(row);
                                }
                            } else {
                                container.css('display', 'none');
                            }
                        },
                        error: function(jqXHR, exception) {
                            console.error("Error fetching data", exception);
                        }
                    });
                }

                $(document).on('click','#clear_submit',function(){
                location.reload();
            })

            });
        </script>
    @endpush
