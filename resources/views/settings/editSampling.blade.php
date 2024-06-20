<div class="row">
    <div class="col-md-12 p-0">

        {!! Form::open([
            'url' =>
                url('qa_sampling_update') . '?parent=' . request()->parent . '&child=' . request()->child,
            'id' => 'qa_sampling_update',
            'class' => 'form',
            'enctype' => 'multipart/form-data',
        ]) !!}
        @csrf
        <div class="row" style="margin-left: 1rem">
            <div class="col-md-6">
                <div class="form-group row row_mar_bm">
                    <label class="col-md-12 col-form-label required">Project</label>
                    <div class="col-md-11">
                        @php $projectList = App\Http\Helper\Admin\Helpers::projectList(); @endphp
                        {!! Form::select('project_id', $projectList, null, [
                            'class' => 'form-control js-client-name kt_select2_project',
                            'id' => 'edit_project_id',
                        ]) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row row_mar_bm">
                    <label class="col-md-12 col-form-label">Subproject</label>
                    <div class="col-md-11">
                        @php $subProjectList = []; @endphp
                        <fieldset class="form-group mb-1">
                            {!! Form::select('sub_project_id', $subProjectList, null, [
                                'class' => 'text-black form-control kt_select2_sub_project',
                                'id' => 'edit_sub_project_list',
                                'style' => 'width: 100%;',
                            ]) !!}
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
        <div class="row"  style="margin-left: 1rem">
            <div class="col-md-6">
                <div class="form-group row row_mar_bm">
                    <label class="col-md-12 col-form-label">Coder</label>
                    <div class="col-md-11">
                        {!! Form::select('coder_emp_id', $coderList, null, [
                            'class' => 'form-control kt_select2_coder',
                            'id' => 'edit_coder_id',
                            'style' => 'width: 100%;; background-color: #ffffff !important;',
                        ]) !!}
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row row_mar_bm">
                    <label class="col-md-12 col-form-label required">QA</label>
                    <div class="col-md-11">
                        {!! Form::select('qa_emp_id', $qaList, null, [
                            'class' => 'form-control kt_select2_QA',
                            'id' => 'edit_qa_id',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="row"  style="margin-left: 1rem">
            <div class="col-md-6">
                <div class="form-group row row_mar_bm">
                    <label class="col-md-12 col-form-label required">Percentage</label>
                    <div class="col-md-11">
                        <input type="text" name="qa_percentage" id="edit_qa_percentage" class="form-control qa_percentage"
                            autocomplete="nope" onkeypress = "return event.charCode >= 48 && event.charCode <= 57">
                            <input type="hidden" name="record_id" id="record_id" class="form-control record_id">
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row row_mar_bm">
                    <label class="col-md-12 col-form-label">Priority</label>
                    <div class="col-md-11">
                        {!! Form::Select(
                            'claim_priority',
                            [
                                '' => '--Select--',
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                            ],
                            null,
                            [
                                'class' => 'form-control kt_select2_priority',
                                'autocomplete' => 'none',
                                'id' => 'edit_claim_priority',
                            ],
                        ) !!}
                    </div>
                </div>
            </div>
        </div><br>

        <div class="modal-footer">
            <button class="btn btn-light-danger" id="clear_submit" tabindex="10" type="button">
                <span>
                    <span>Clear</span>
                </span>
            </button>&nbsp;&nbsp;
            <button type="submit" class="btn btn-white-black font-weight-bold" id="formUpdate_save">Submit</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
