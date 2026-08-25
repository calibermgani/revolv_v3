@extends('layouts.app3')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .btn.btn-add-new-button {
    color: #ffffff;
    background-color: #139AB3;
    border-color: #139AB3;
}
    .alert-rule-page {
        padding: 22px;
        background: #f5f7fb;
    }

    .page-heading {
        margin-bottom: 20px;
        font-size: 20px;
        font-weight: 600;
        color: #17233d;
    }

    .rule-card {
        background: #ffffff;
        border: 1px solid #dfe5ed;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(20, 38, 70, 0.05);
    }

    .rule-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 15px 18px;
        border-bottom: 1px solid #e3e8ef;
    }

    .rule-card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #17233d;
    }

    .rule-card-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .grid-search {
        width: 240px;
    }

    .rule-card-body {
        padding: 15px;
    }

    .rules-table {
        margin-bottom: 0;
        font-size: 13px;
    }

    .rules-table thead th {
        padding: 11px 9px;
        color: #354052;
        white-space: nowrap;
        vertical-align: middle;
        background: #f3f6fa;
    }

    .rules-table tbody td {
        padding: 10px 9px;
        vertical-align: middle;
    }

    .user-badge {
        display: inline-block;
        padding: 4px 7px;
        margin: 2px;
        font-size: 11px;
        color: #31597f;
        background: #eaf1fb;
        border-radius: 3px;
    }

    .logic-badge {
        display: inline-block;
        padding: 5px 9px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 4px;
    }

    .logic-and {
        color: #167044;
        background: #e5f7ec;
    }

    .logic-or {
        color: #8a5700;
        background: #fff0d5;
    }

    .ajax-message {
        display: none;
        margin-bottom: 15px;
    }

    /*
    |--------------------------------------------------------------------------
    | Modal design
    |--------------------------------------------------------------------------
    */

    .modal-content {
        border: 0;
        border-radius: 9px;
        box-shadow: 0 10px 40px rgba(23, 35, 61, 0.18);
    }

    .modal-header {
        padding: 17px 20px;
        border-bottom: 1px solid #e3e8ef;
    }

    .modal-title {
        font-size: 17px;
        font-weight: 600;
        color: #17233d;
    }

    .modal-body {
        padding: 20px;
        max-height: calc(100vh - 190px);
        overflow-y: auto;
    }

    .modal-footer {
        padding: 14px 20px;
        border-top: 1px solid #e3e8ef;
    }

    .section-title {
        margin-bottom: 13px;
        font-size: 14px;
        font-weight: 600;
        color: #25324a;
    }

    .project-selection-section {
        padding-bottom: 20px;
        margin-bottom: 18px;
        border-bottom: 1px solid #e5eaf1;
    }

    .form-label {
        margin-bottom: 7px;
        font-size: 13px;
        font-weight: 600;
        color: #354052;
    }

    .required {
        color: #dc3545;
    }

    .conditions-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 12px;
    }

    .match-type-box {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .match-type-box .form-label {
        margin: 0;
        white-space: nowrap;
    }

    .condition-note {
        padding: 10px 12px;
        margin-bottom: 14px;
        font-size: 13px;
        color: #245fa9;
        background: #edf5ff;
        border: 1px solid #c9e0ff;
        border-radius: 5px;
    }

    .condition-row {
        padding: 14px;
        margin-bottom: 12px;
        background: #fbfcfe;
        border: 1px solid #dfe6ef;
        border-radius: 7px;
    }

    .condition-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 25px;
        height: 25px;
        margin-bottom: 10px;
        font-size: 12px;
        font-weight: 600;
        color: #1261dc;
        background: #e6efff;
        border-radius: 50%;
    }

    .remove-condition-button {
        width: 42px;
        height: 38px;
        margin-top: 27px;
    }

    .add-condition-button {
        width: 100%;
        padding: 11px;
        font-weight: 500;
        color: #1261dc;
        background: #ffffff;
        border: 1px dashed #aebdd0;
        border-radius: 5px;
    }

    .add-condition-button:hover {
        background: #f3f7ff;
    }

    .add-condition-button:disabled {
        color: #8793a5;
        cursor: not-allowed;
        background: #f5f6f8;
    }

    /*
    |--------------------------------------------------------------------------
    | Select2
    |--------------------------------------------------------------------------
    */

    .select2-container {
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        background-color: #ffffff !important;
    }
     .select2-container .select2-selection--multiple {
        background-color: #ffffff !important;
    }

    .select2-container
    .select2-selection--single
    .select2-selection__rendered {
        padding-left: 12px;
        line-height: 36px;
    }

    .select2-container
    .select2-selection--single
    .select2-selection__arrow {
        height: 36px;
    }

    .select2-container--default
    .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
    }

    .select2-container--default
    .select2-selection--multiple
    .select2-selection__choice {
        padding: 3px 7px;
        margin-top: 5px;
        background: #edf1f6;
        border: 1px solid #d4dbe5;
    }

    .modal-error {
        display: none;
        margin-bottom: 15px;
    }

    .condition-value.between-value-input {
        cursor: pointer;
        background: #fff;
    }

    #alertRuleModal .modal-content {
        position: relative;
    }

    .between-range-overlay {
        display: none;
        position: absolute;
        inset: 0;
        z-index: 20;
        align-items: center;
        justify-content: center;
        background: rgba(20, 38, 70, 0.35);
    }

    .between-range-overlay.is-open {
        display: flex;
    }

    .between-range-dialog {
        width: 340px;
        max-width: calc(100% - 32px);
        background: #ffffff;
        border: 1px solid #c5d0de;
        border-radius: 8px;
        box-shadow: 0 12px 30px rgba(20, 38, 70, 0.22);
    }

    .between-range-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        font-size: 14px;
        font-weight: 600;
        color: #17233d;
        border-bottom: 1px solid #e3e8ef;
    }

    .between-range-close {
        padding: 0;
        font-size: 22px;
        line-height: 1;
        color: #6b778c;
        background: transparent;
        border: 0;
    }

    .between-range-body {
        padding: 14px;
    }

    .between-range-hint {
        margin-bottom: 12px;
        font-size: 12px;
        color: #5b6b82;
    }

    .between-range-body .form-label {
        margin-bottom: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .between-range-body .form-control {
        margin-bottom: 12px;
    }

    .between-range-error {
        display: none;
        margin-bottom: 0;
        font-size: 12px;
        color: #dc3545;
    }

    .between-range-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 10px 14px;
        border-top: 1px solid #e3e8ef;
    }

    @media (max-width: 992px) {
        .rule-card-header {
            align-items: stretch;
            flex-direction: column;
        }

        .rule-card-actions {
            width: 100%;
        }

        .grid-search {
            flex: 1;
            width: auto;
        }

        .conditions-heading {
            align-items: flex-start;
            flex-direction: column;
        }

        .remove-condition-button {
            margin-top: 10px;
        }
    }
</style>

<div class="alert-rule-page">

    <div class="page-heading">
        Alert Configuration
    </div>

    <div
        id="pageSuccessMessage"
        class="alert alert-success ajax-message"
    ></div>

    <div
        id="pageErrorMessage"
        class="alert alert-danger ajax-message"
    ></div>

    {{-- Configured Alert Rules only --}}
    <div class="rule-card">

        <div class="rule-card-header">

            <h5 class="rule-card-title">
                Configured Alert Rules
            </h5>

            <div class="rule-card-actions">

                <input
                    type="text"
                    id="gridSearch"
                    class="form-control grid-search"
                    placeholder="Search..."
                >

                <button
                    type="button"
                    id="addNewRuleButton"
                    class="btn btn-add-new-button font-weight-bolder btn-md"
                >
                    + Add New Rule
                </button>

            </div>

        </div>

        <div class="rule-card-body">

            <div class="table-responsive">

                <table
                    class="table table-bordered rules-table"
                    id="rulesTable"
                >

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Project</th>
                            <th>Sub Project</th>
                            <th>Project Column</th>
                            <th>Condition</th>
                            <th>Value</th>
                            <th>Users</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody id="rulesTableBody">

                        <tr>
                            <td
                                colspan="10"
                                class="text-center"
                            >
                                Loading...
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

{{-- Add / Edit Alert Rule modal --}}
<div
    class="modal fade"
    id="alertRuleModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="alertRuleModalLabel"
    aria-hidden="true"
    data-backdrop="static"
    data-keyboard="false"
>

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="alertRuleModalLabel"
                >
                    Add Alert Rule
                </h5>

              <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close"
                >
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>

            <div class="modal-body">

                <input
                    type="hidden"
                    id="ruleId"
                    value=""
                >

                <div
                    id="modalErrorMessage"
                    class="alert alert-danger modal-error"
                ></div>

                {{-- First row: Project and Subproject --}}
                <div class="project-selection-section">

                    <div class="section-title">
                        1. Select Project
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label">
                                Project
                                <span class="required"></span>
                            </label>
                            {!! Form::select(
                                'project_id',
                                $projectList,
                                null,
                                [
                                    'class' => 'text-black form-control select2 project_select',
                                    'id' => 'projectId',
                                    'placeholder' => 'Select Project'
                                ]
                            ) !!}
                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Sub Project
                                <span class="required"></span>
                            </label>

                            <select
                                id="subProjectId"
                                class="form-select"
                                disabled
                            >
                                <option value="">
                                    Select Sub Project
                                </option>
                            </select>

                        </div>

                    </div>

                </div>               
                <div
                    id="conditionNote"
                    class="condition-note"
                >
                    Each configured condition will be stored as a separate record.
                </div>

                <div id="conditionsContainer"></div>

                <div
                    id="optionWarning"
                    class="alert alert-warning"
                    style="display: none;"
                >
                    No project columns or mapped users were found for
                    the selected project and subproject.
                </div>

                <button
                    type="button"
                    id="addConditionButton"
                    class="add-condition-button"
                    disabled
                >
                    + Add Another Condition
                </button>

            </div>

            <div class="modal-footer">

                <button
    type="button"
    class="btn btn-light border"
    data-dismiss="modal"
>
    Cancel
</button>

                <button
                    type="button"
                    id="saveRuleButton"
                    class="btn btn-add-new-button"
                >
                    <span id="saveButtonText">
                        Save Rule
                    </span>
                </button>

            </div>

            {{-- BETWEEN min / max popup stays inside the modal so focus is not trapped on Close --}}
            <div
                id="betweenRangeOverlay"
                class="between-range-overlay"
            >
                <div class="between-range-dialog" role="dialog" aria-labelledby="betweenRangeTitle">

                    <div class="between-range-header">
                        <span id="betweenRangeTitle">BETWEEN</span>
                        <button
                            type="button"
                            id="betweenRangeClose"
                            class="between-range-close"
                            aria-label="Close"
                        >
                            &times;
                        </button>
                    </div>

                    <div class="between-range-body">
                        <p
                            id="betweenRangeHint"
                            class="between-range-hint"
                        ></p>

                        <label class="form-label" for="betweenMinValue">
                            Min value
                            <span class="required"></span>
                        </label>
                        <input
                            id="betweenMinValue"
                            class="form-control"
                            autocomplete="off"
                        >

                        <label class="form-label" for="betweenMaxValue">
                            Max value
                            <span class="required"></span>
                        </label>
                        <input
                            id="betweenMaxValue"
                            class="form-control"
                            autocomplete="off"
                        >

                        <div
                            id="betweenRangeError"
                            class="between-range-error"
                        ></div>
                    </div>

                    <div class="between-range-footer">
                        <button
                            type="button"
                            id="betweenRangeCancel"
                            class="btn btn-light border btn-sm"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            id="betweenRangeApply"
                            class="btn btn-add-new-button btn-sm"
                        >
                            OK
                        </button>
                    </div>

                </div>
            </div>

        </div>

    </div>

</div>
@endsection
{{-- Remove these scripts if already loaded in layouts.app --}}

@push('view.scripts')
<script>
    $(document).ready(function () {

        const storeUrl = @json(
            route('alert-configurations.store')
        );

        const listUrl = @json(
            route('alert-configurations.list')
        );

        const optionsUrl = @json(
            route('alert-configurations.options')
        );

        const subProjectsUrl = @json(
            route('alert-configurations.subprojects')
        );

        const baseUrl = @json(
            url('/alert-configurations')
        );
    $('#alertRuleModal').modal({
    backdrop: 'static',
    keyboard: false,
    show: false
});

        let availableColumns = [];
        let availableUsers = [];
        let conditionCounter = 0;
        let betweenTargetRow = null;

        /*
        |--------------------------------------------------------------------------
        | AJAX CSRF
        |--------------------------------------------------------------------------
        */

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN':
                    $('meta[name="csrf-token"]').attr('content')
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Initialize Select2
        |--------------------------------------------------------------------------
        */

        $('#projectId').select2({
            placeholder: 'Select Project',
            dropdownParent: $('#alertRuleModal')
        });

        $('#subProjectId').select2({
            placeholder: 'Select Sub Project',
            allowClear: true,
            dropdownParent: $('#alertRuleModal')
        });

        $('#matchType').select2({
            minimumResultsForSearch: Infinity,
            dropdownParent: $('#alertRuleModal')
        });

        /*
        |--------------------------------------------------------------------------
        | Initial grid
        |--------------------------------------------------------------------------
        */

        loadGrid();

        /*
        |--------------------------------------------------------------------------
        | Add New Rule button
        |--------------------------------------------------------------------------
        */

        $('#addNewRuleButton').on('click', function () {

            resetModalForm();

            $('#alertRuleModalLabel').text(
                'Add Alert Rule'
            );

            $('#saveButtonText').text(
                'Save Rule'
            );

            $('#alertRuleModal').modal('show');
        });

        /*
        |--------------------------------------------------------------------------
        | Project changed
        |--------------------------------------------------------------------------
        */

        $('#projectId').on('change', function () {

            const projectId = $(this).val();

            clearSubProject();
            clearConditions();

            if (!projectId) {
                return;
            }

            loadSubProjects(projectId);
        });

        /*
        |--------------------------------------------------------------------------
        | Subproject changed
        |--------------------------------------------------------------------------
        */

        $('#subProjectId').on('change', function () {

            const projectId =
                $('#projectId').val();

            const subProjectId =
                $(this).val();

            clearConditions();

            if (!projectId || !subProjectId) {
                return;
            }

            loadOptions(
                projectId,
                subProjectId
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Match type changed
        |--------------------------------------------------------------------------
        */

        // $('#matchType').on('change', function () {

        //     if ($(this).val() === 'AND') {

        //         $('#conditionNote').text(
        //             'All configured conditions must match.'
        //         );

        //     } else {

        //         $('#conditionNote').text(
        //             'Any one configured condition may match.'
        //         );
        //     }
        // });

        /*
        |--------------------------------------------------------------------------
        | Add another condition
        |--------------------------------------------------------------------------
        */

        $('#addConditionButton').on('click', function () {
            addConditionRow();
        });

        /*
        |--------------------------------------------------------------------------
        | Remove condition
        |--------------------------------------------------------------------------
        */

        $(document).on(
            'click',
            '.remove-condition-button',
            function () {

                if ($('.condition-row').length <= 1) {

                    showModalError(
                        'At least one condition is required.'
                    );

                    return;
                }

                $(this)
                    .closest('.condition-row')
                    .remove();

                updateConditionNumbers();
            }
        );

        /*
        |--------------------------------------------------------------------------
        | BETWEEN min / max popup
        |--------------------------------------------------------------------------
        */

        $(document).on(
            'change',
            '.condition-column, .condition-operator',
            function () {
                syncBetweenValueInput(
                    $(this).closest('.condition-row')
                );
            }
        );

        $(document).on(
            'click',
            '.condition-value',
            function (event) {
                const $row = $(this).closest('.condition-row');

                if (!isBetweenOperator($row)) {
                    return;
                }

                event.preventDefault();
                openBetweenPopup($row);
            }
        );

        $(document).on(
            'keydown',
            '.condition-value',
            function (event) {
                if (!isBetweenOperator($(this).closest('.condition-row'))) {
                    return;
                }

                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openBetweenPopup($(this).closest('.condition-row'));
                }
            }
        );

        $('#betweenRangeApply').on('click', function () {
            applyBetweenPopup();
        });

        $('#betweenRangeCancel, #betweenRangeClose').on(
            'click',
            function () {
                closeBetweenPopup();
            }
        );

        $('#betweenRangeOverlay').on('click', function (event) {
            if (event.target === this) {
                closeBetweenPopup();
            }
        });

        function formatUsDateInput(input) {
            const cursorAtEnd =
                input.selectionStart === input.value.length;
            let digits = input.value.replace(/\D/g, '').slice(0, 8);
            let formatted = digits;

            if (digits.length >= 5) {
                formatted =
                    digits.slice(0, 2) +
                    '/' +
                    digits.slice(2, 4) +
                    '/' +
                    digits.slice(4);
            } else if (digits.length >= 3) {
                formatted =
                    digits.slice(0, 2) + '/' + digits.slice(2);
            }

            input.value = formatted;

            if (cursorAtEnd) {
                input.setSelectionRange(
                    formatted.length,
                    formatted.length
                );
            }
        }

        $(document).on(
            'input',
            '#betweenMinValue, #betweenMaxValue, .condition-value',
            function () {
                const isBetweenDate =
                    $(this).is('#betweenMinValue, #betweenMaxValue') &&
                    $(this).attr('data-range-type') === 'date';
                const isConditionDate =
                    $(this).hasClass('condition-value') &&
                    $(this).attr('data-value-mode') === 'date';

                if (!isBetweenDate && !isConditionDate) {
                    return;
                }

                formatUsDateInput(this);
            }
        );

        $(document).on('keydown', function (event) {
            if (
                event.key === 'Escape' &&
                $('#betweenRangeOverlay').hasClass('is-open')
            ) {
                closeBetweenPopup();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Save / update rule
        |--------------------------------------------------------------------------
        */

        $('#saveRuleButton').on('click', function () {
            saveRule();
        });

        /*
        |--------------------------------------------------------------------------
        | Edit rule
        |--------------------------------------------------------------------------
        */

        $(document).on('click', '.edit-rule', function () {

            const ruleId =
                $(this).data('id');

            editRule(ruleId);
        });

        /*
        |--------------------------------------------------------------------------
        | Delete rule
        |--------------------------------------------------------------------------
        */

        $(document).on('click', '.delete-rule', function () {

            const ruleId =
                $(this).data('id');

            deleteRule(ruleId);
        });

        /*
        |--------------------------------------------------------------------------
        | Grid search
        |--------------------------------------------------------------------------
        */

        $('#gridSearch').on('keyup', function () {

            const value = $(this)
                .val()
                .toLowerCase();

            $('#rulesTableBody tr').filter(function () {

                $(this).toggle(
                    $(this)
                        .text()
                        .toLowerCase()
                        .indexOf(value) > -1
                );
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Load subprojects
        |--------------------------------------------------------------------------
        */

        function loadSubProjects(
            projectId,
            selectedSubProjectId = null
        ) {
            $('#subProjectId')
                .prop('disabled', true)
                .html(
                    '<option value="">Loading...</option>'
                )
                .trigger('change.select2');

            return $.ajax({
                url: subProjectsUrl,
                method: 'GET',

                data: {
                    project_id: projectId
                },

                success: function (response) {

                    let options =
                        '<option value="">' +
                        'Select Sub Project' +
                        '</option>';

                    $.each(
                        response.sub_projects,
                        function (index, subProject) {

                            options +=
                                '<option value="' +
                                escapeHtml(subProject.id) +
                                '">' +
                                escapeHtml(subProject.name) +
                                '</option>';
                        }
                    );

                    $('#subProjectId')
                        .html(options)
                        .prop('disabled', false);

                    if (selectedSubProjectId !== null) {

                        $('#subProjectId')
                            .val(
                                String(selectedSubProjectId)
                            )
                            .trigger('change.select2');
                    }
                },

                error: function (xhr) {

                    clearSubProject();
                    showAjaxModalError(xhr);
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Load project columns and users
        |--------------------------------------------------------------------------
        */

        function loadOptions(
            projectId,
            subProjectId,
            existingConditions = null
        ) {
            $('#addConditionButton')
                .prop('disabled', true);

            return $.ajax({
                url: optionsUrl,
                method: 'GET',

                data: {
                    project_id: projectId,
                    sub_project_id: subProjectId
                },

                success: function (response) {

                    availableColumns =
                        response.columns || [];

                    availableUsers =
                        response.users || [];

                    if (
                        availableColumns.length === 0 ||
                        availableUsers.length === 0
                    ) {
                        $('#optionWarning').show();

                        return;
                    }

                    $('#optionWarning').hide();

                    $('#addConditionButton')
                        .prop('disabled', false);

                    if (
                        existingConditions !== null &&
                        existingConditions.length > 0
                    ) {
                        $.each(
                            existingConditions,
                            function (index, condition) {
                                addConditionRow(condition);
                            }
                        );

                    } else {
                        addConditionRow();
                    }
                },

                error: function (xhr) {
                    showAjaxModalError(xhr);
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Add dynamic condition row
        |--------------------------------------------------------------------------
        */

        function addConditionRow(condition = null) {

            if (
                availableColumns.length === 0 ||
                availableUsers.length === 0
            ) {
                showModalError(
                    'Project columns or users are not available.'
                );

                return;
            }

            conditionCounter++;

            let columnOptions =
                '<option value="">' +
                'Select Project Column' +
                '</option>';

            $.each(
                availableColumns,
                function (index, column) {

                    columnOptions +=
                        '<option value="' +
                        escapeHtml(column.id) +
                        '" data-type="' +
                        escapeHtml(column.type || 'text') +
                        '">' +
                        escapeHtml(column.name) +
                        '</option>';
                }
            );

            let userOptions = '';

            $.each(
                availableUsers,
                function (index, user) {

                    userOptions +=
                        '<option value="' +
                        escapeHtml(user.id) +
                        '">' +
                        escapeHtml(user.name) +
                        '</option>';
                }
            );

            const rowHtml = `
                <div class="condition-row">

                    <div class="condition-number"></div>

                    <div class="row g-3">

                        <div class="col-lg-3 col-md-6">

                            <label class="form-label">
                                Project Column
                                <span class="required"></span>
                            </label>

                            <select
                                class="form-select condition-column"
                            >
                                ${columnOptions}
                            </select>

                        </div>

                        <div class="col-lg-2 col-md-6">

                            <label class="form-label">
                                Condition
                                <span class="required"></span>
                            </label>

                            <select
                                class="form-select condition-operator"
                            >
                                <option value="">
                                    Select
                                </option>
                                <option value="=">
                                    =
                                </option>

                                <option value=">">
                                    &gt;
                                </option>

                                <option value=">=">
                                    &gt;=
                                </option>

                                <option value="<">
                                    &lt;
                                </option>

                                <option value="<=">
                                    &lt;=
                                </option>

                                <option value="!=">
                                    !=
                                </option>

                                <option value="between">
                                    Between
                                </option>
                                <option value="not between">
                                    Not Between
                                </option>
                                 <option value="like">
                                    Like
                                </option>
                                <option value="not like">
                                    Not Like
                                </option>

                                <option value="in">
                                    In
                                </option>

                                <option value="not in">
                                    Not In
                                </option>
                               
                            </select>

                        </div>

                        <div class="col-lg-2 col-md-6">

                            <label class="form-label">
                                Value
                                <span class="required"></span>
                            </label>

                            <input
                                type="text"
                                class="form-control condition-value"
                                placeholder="Example: 30"
                                autocomplete="off"
                            >

                        </div>

                        <div class="col-lg-4 col-md-10">

                            <label class="form-label">
                                Users
                                <span class="required"></span>
                            </label>

                            <select
                                class="form-select condition-users"
                                multiple
                            >
                                ${userOptions}
                            </select>

                        </div>

                        <div class="col-lg-1 col-md-2">

                            <button
                                type="button"
                                class="btn btn-danger remove-condition-button"
                                title="Delete condition"
                            >
                                ×
                            </button>

                        </div>

                    </div>

                </div>
            `;

            $('#conditionsContainer')
                .append(rowHtml);

            const currentRow =
                $('#conditionsContainer')
                    .children('.condition-row')
                    .last();

            currentRow
                .find('.condition-column')
                .select2({
                    placeholder: 'Select Column',
                    allowClear: true,
                    dropdownParent: $('#alertRuleModal')
                });

            currentRow
                .find('.condition-operator')
                .select2({
                    placeholder: 'Select',
                    minimumResultsForSearch: Infinity,
                    dropdownParent: $('#alertRuleModal')
                });

            currentRow
                .find('.condition-users')
                .select2({
                    placeholder: 'Select Users',
                    closeOnSelect: false,
                    dropdownParent: $('#alertRuleModal')
                });

            if (condition !== null) {

                currentRow
                    .find('.condition-column')
                    .val(
                        String(condition.column_id)
                    )
                    .trigger('change.select2');

                currentRow
                    .find('.condition-operator')
                    .val(condition.operator)
                    .trigger('change.select2');

                currentRow
                    .find('.condition-value')
                    .val(condition.value);

                currentRow
                    .find('.condition-users')
                    .val(
                        $.map(
                            condition.users,
                            function (userId) {
                                return String(userId);
                            }
                        )
                    )
                    .trigger('change.select2');
            }

            syncBetweenValueInput(currentRow);
            updateConditionNumbers();
        }

        function isBetweenOperator($row) {
            const operator = String(
                $row.find('.condition-operator').val() || ''
            ).toLowerCase();

            return operator === 'between' || operator === 'not between';
        }

        function getSelectedColumnType($row) {
            const columnId = String(
                $row.find('.condition-column').val() || ''
            );
            const column = availableColumns.find(function (item) {
                return String(item.id) === columnId;
            });

            return column && column.type
                ? String(column.type).toLowerCase()
                : 'text';
        }

        function parseBetweenValue(value) {
            const raw = $.trim(value || '');

            if (raw === '') {
                return { min: '', max: '' };
            }

            let parts = raw.split(/\s+AND\s+/i);

            if (parts.length !== 2) {
                parts = raw.split(/\s+to\s+/i);
            }

            if (parts.length !== 2) {
                const dashMatch = raw.match(
                    /^\s*(.+?)\s+-\s+(.+?)\s*$/
                );
                if (!dashMatch) {
                    return { min: '', max: '' };
                }

                return {
                    min: $.trim(dashMatch[1]),
                    max: $.trim(dashMatch[2])
                };
            }

            return {
                min: $.trim(parts[0]),
                max: $.trim(parts[1])
            };
        }

        function padDatePart(value) {
            return String(value).padStart(2, '0');
        }

        function toIsoDate(value) {
            const raw = $.trim(value || '');
            const usMatch = raw.match(
                /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/
            );

            if (usMatch) {
                return (
                    usMatch[3] +
                    '-' +
                    padDatePart(usMatch[1]) +
                    '-' +
                    padDatePart(usMatch[2])
                );
            }

            const isoMatch = raw.match(
                /^(\d{4})-(\d{2})-(\d{2})$/
            );

            return isoMatch ? raw : '';
        }

        function toUsDate(value) {
            const isoDate = toIsoDate(value);

            if (isoDate === '') {
                return '';
            }

            const parts = isoDate.split('-');

            return parts[1] + '/' + parts[2] + '/' + parts[0];
        }

        function isValidUsDate(value) {
            const isoDate = toIsoDate(value);

            if (isoDate === '') {
                return false;
            }

            const parts = isoDate.split('-');
            const date = new Date(
                Number(parts[0]),
                Number(parts[1]) - 1,
                Number(parts[2])
            );

            return (
                date.getFullYear() === Number(parts[0]) &&
                date.getMonth() === Number(parts[1]) - 1 &&
                date.getDate() === Number(parts[2])
            );
        }

        function normalizeNumericValue(value) {
            return $.trim(value || '')
                .replace(/\$/g, '')
                .replace(/,/g, '')
                .replace(/\s+/g, '');
        }

        function isValidNumericValue(value) {
            return /^-?\d+(\.\d+)?$/.test(
                normalizeNumericValue(value)
            );
        }

        function showBetweenError(message) {
            $('#betweenRangeError')
                .text(message)
                .show();
        }

        function hideBetweenError() {
            $('#betweenRangeError')
                .hide()
                .text('');
        }

        function getValueGuidance($row) {
            const operator = String(
                $row.find('.condition-operator').val() || ''
            ).toLowerCase();
            const isDate = getSelectedColumnType($row) === 'date';

            if (operator === 'between' || operator === 'not between') {
                return {
                    mode: 'range',
                    readonly: true,
                    placeholder: isDate
                        ? 'Click to enter min and max dates'
                        : 'Click to enter min and max numbers'
                };
            }

            if (['>', '>=', '<', '<='].indexOf(operator) !== -1) {
                return {
                    mode: isDate ? 'date' : 'number',
                    readonly: false,
                    placeholder: isDate
                        ? 'mm/dd/yyyy'
                        : 'Example: 300.00'
                };
            }

            if (operator === '=' || operator === '!=') {
                return {
                    mode: isDate ? 'date' : 'text',
                    readonly: false,
                    placeholder: isDate
                        ? 'mm/dd/yyyy'
                        : 'Enter value'
                };
            }

            if (operator === 'like' || operator === 'not like') {
                return {
                    mode: 'text',
                    readonly: false,
                    placeholder: 'Example: %text%'
                };
            }

            if (operator === 'in' || operator === 'not in') {
                return {
                    mode: 'list',
                    readonly: false,
                    placeholder: isDate
                        ? 'mm/dd/yyyy, mm/dd/yyyy'
                        : 'value1, value2, value3'
                };
            }

            return {
                mode: 'text',
                readonly: false,
                placeholder: 'Example: 30'
            };
        }

        function syncBetweenValueInput($row) {
            const $value = $row.find('.condition-value');
            const guidance = getValueGuidance($row);

            $value
                .prop('readonly', guidance.readonly)
                .attr('placeholder', guidance.placeholder)
                .attr('data-value-mode', guidance.mode)
                .toggleClass(
                    'between-value-input',
                    guidance.mode === 'range'
                );

            if (guidance.mode === 'date') {
                $value.attr('maxlength', 10);
            } else {
                $value.removeAttr('maxlength');
            }
        }

        function openBetweenPopup($row) {
            const columnType = getSelectedColumnType($row);
            const operator = String(
                $row.find('.condition-operator').val() || ''
            ).toUpperCase();

            if (!$row.find('.condition-column').val()) {
                showModalError(
                    'Please select a project column first.'
                );
                return;
            }

            hideModalError();
            hideBetweenError();

            betweenTargetRow = $row;

            const parsed = parseBetweenValue(
                $row.find('.condition-value').val()
            );
            const $min = $('#betweenMinValue');
            const $max = $('#betweenMaxValue');

            $('#betweenRangeTitle').text(operator || 'BETWEEN');

            if (columnType === 'date') {
                $('#betweenRangeHint').text(
                    'Enter min and max dates in mm/dd/yyyy format.'
                );
                $min.attr({
                    type: 'text',
                    placeholder: 'mm/dd/yyyy',
                    maxlength: 10,
                    'data-range-type': 'date'
                }).val(toUsDate(parsed.min) || parsed.min);
                $max.attr({
                    type: 'text',
                    placeholder: 'mm/dd/yyyy',
                    maxlength: 10,
                    'data-range-type': 'date'
                }).val(toUsDate(parsed.max) || parsed.max);
            } else {
                $('#betweenRangeHint').text(
                    'Enter numeric or decimal min and max values.'
                );
                $min.attr({
                    type: 'text',
                    inputmode: 'decimal',
                    placeholder: 'Example: 300.00',
                    maxlength: 30,
                    'data-range-type': 'number'
                }).val(parsed.min);
                $max.attr({
                    type: 'text',
                    inputmode: 'decimal',
                    placeholder: 'Example: 500.00',
                    maxlength: 30,
                    'data-range-type': 'number'
                }).val(parsed.max);
            }

            $('#betweenRangeOverlay').addClass('is-open');
            setTimeout(function () {
                $('#betweenMinValue').trigger('focus');
            }, 0);
        }

        function closeBetweenPopup() {
            const $row = betweenTargetRow;

            $('#betweenRangeOverlay').removeClass('is-open');
            hideBetweenError();
            betweenTargetRow = null;

            if ($row && $row.length) {
                setTimeout(function () {
                    $row.find('.condition-value').trigger('focus');
                }, 0);
            }
        }

        function applyBetweenPopup() {
            if (!betweenTargetRow) {
                return;
            }

            hideBetweenError();

            const columnType = getSelectedColumnType(betweenTargetRow);
            let minValue = $.trim($('#betweenMinValue').val() || '');
            let maxValue = $.trim($('#betweenMaxValue').val() || '');

            if (minValue === '' || maxValue === '') {
                showBetweenError(
                    'Please enter both min value and max value.'
                );
                return;
            }

            if (columnType === 'date') {
                if (!isValidUsDate(minValue) || !isValidUsDate(maxValue)) {
                    showBetweenError(
                        'Please enter valid dates in mm/dd/yyyy format.'
                    );
                    return;
                }

                minValue = toUsDate(minValue);
                maxValue = toUsDate(maxValue);

                if (toIsoDate(minValue) > toIsoDate(maxValue)) {
                    showBetweenError(
                        'Min date cannot be greater than max date.'
                    );
                    return;
                }
            } else {
                if (
                    !isValidNumericValue(minValue) ||
                    !isValidNumericValue(maxValue)
                ) {
                    showBetweenError(
                        'Please enter valid numeric or decimal values.'
                    );
                    return;
                }

                minValue = normalizeNumericValue(minValue);
                maxValue = normalizeNumericValue(maxValue);

                if (parseFloat(minValue) > parseFloat(maxValue)) {
                    showBetweenError(
                        'Min value cannot be greater than max value.'
                    );
                    return;
                }
            }

            betweenTargetRow
                .find('.condition-value')
                .val(minValue + ' AND ' + maxValue);

            closeBetweenPopup();
        }

        /*
        |--------------------------------------------------------------------------
        | Collect conditions
        |--------------------------------------------------------------------------
        */

        function collectConditions() {

            const conditions = [];
            let valid = true;

            $('.condition-row').each(function () {

                const columnId =
                    $(this)
                        .find('.condition-column')
                        .val();

                const operator =
                    $(this)
                        .find('.condition-operator')
                        .val();

                const value =
                    $(this)
                        .find('.condition-value')
                        .val()
                        .trim();

                const users =
                    $(this)
                        .find('.condition-users')
                        .val() || [];

                if (
                    !columnId ||
                    !operator ||
                    value === '' ||
                    users.length === 0
                ) {
                    valid = false;
                    return false;
                }

                if (isBetweenOperator($(this))) {
                    const parsed = parseBetweenValue(value);
                    if (parsed.min === '' || parsed.max === '') {
                        valid = false;
                        return false;
                    }
                }

                conditions.push({
                    column_id: columnId,
                    operator: operator,
                    value: value,
                    users: users
                });
            });

            return {
                valid: valid,
                conditions: conditions
            };
        }

        /*
        |--------------------------------------------------------------------------
        | Save or update
        |--------------------------------------------------------------------------
        */

        function saveRule() {

            hideModalError();

            const projectId =
                $('#projectId').val();

            const subProjectId =
                $('#subProjectId').val();

            // const matchType =
            //     $('#matchType').val();

            const conditionResult =
                collectConditions();

            if (!projectId) {

                showModalError(
                    'Please select a project.'
                );

                return;
            }

            if (!subProjectId) {

                showModalError(
                    'Please select a subproject.'
                );

                return;
            }

            if (
                $('.condition-row').length === 0 ||
                !conditionResult.valid
            ) {
                showModalError(
                    'Complete all condition fields and select at least one user.'
                );

                return;
            }

            const ruleId =
                $('#ruleId').val();

            const requestUrl =
                ruleId
                    ? baseUrl + '/' + ruleId
                    : storeUrl;

            const requestMethod =
                ruleId
                    ? 'PUT'
                    : 'POST';

            setSaveButtonLoading(true);

            $.ajax({
                url: requestUrl,
                method: requestMethod,

                data: {
                    project_id: projectId,
                    sub_project_id: subProjectId,
                    // match_type: matchType,
                    conditions: conditionResult.conditions
                },

                success: function (response) {

                    setSaveButtonLoading(false);

                    $('#alertRuleModal').modal('hide');

                    showPageSuccess(
                        response.message
                    );

                    loadGrid();
                    resetModalForm();
                },

                error: function (xhr) {

                    setSaveButtonLoading(false);
                    showAjaxModalError(xhr);
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Edit rule
        |--------------------------------------------------------------------------
        */

        function editRule(ruleId) {

    resetModalForm();

    $('#alertRuleModalLabel').text(
        'Edit Alert Rule'
    );

    $('#saveButtonText').text(
        'Update Rule'
    );

    $('#alertRuleModal').modal('show');

    setSaveButtonLoading(true);

    $.ajax({
        url: baseUrl + '/' + ruleId,
        method: 'GET',

        success: function (response) {

            const rule = response.data;

            $('#ruleId').val(rule.id);

            $('#projectId')
                .val(String(rule.project_id))
                .trigger('change.select2');

            loadSubProjects(
                rule.project_id,
                rule.sub_project_id
            ).done(function () {

                loadOptions(
                    rule.project_id,
                    rule.sub_project_id,
                    rule.conditions
                ).always(function () {

                    setSaveButtonLoading(false);
                });
            });
        },

        error: function (xhr) {

            setSaveButtonLoading(false);
            showAjaxModalError(xhr);
        }
    });
}

        /*
        |--------------------------------------------------------------------------
        | Delete rule
        |--------------------------------------------------------------------------
        */

        function deleteRule(ruleId) {

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

            // if (
            //     !confirm(
            //         'Are you sure you want to delete this alert rule?'
            //     )
            // ) {
            //     return;
            // }

            $.ajax({
                url: baseUrl + '/' + ruleId,
                method: 'DELETE',

                success: function (response) {

                    showPageSuccess(
                        response.message
                    );

                    loadGrid();
                },

                error: function (xhr) {
                    showAjaxPageError(xhr);
                }
            });
           
                        });
        }

        /*
        |--------------------------------------------------------------------------
        | Load grid
        |--------------------------------------------------------------------------
        */

        function loadGrid() {

            $('#rulesTableBody').html(`
                <tr>
                    <td
                        colspan="11"
                        class="text-center"
                    >
                        Loading...
                    </td>
                </tr>
            `);

            $.ajax({
                url: listUrl,
                method: 'GET',

                success: function (response) {
                    renderGrid(
                        response.data || []
                    );
                },

                error: function (xhr) {

                    $('#rulesTableBody').html(`
                        <tr>
                            <td
                                colspan="11"
                                class="text-center text-danger"
                            >
                                Unable to load alert rules.
                            </td>
                        </tr>
                    `);

                    showAjaxPageError(xhr);
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Render grid
        |--------------------------------------------------------------------------
        |
        | Multiple conditions are shown as multiple rows.
        | Project, subproject and action cells use rowspan.
        |
        */

        function renderGrid(rules) {

    if (rules.length === 0) {

        $('#rulesTableBody').html(`
            <tr>
                <td
                    colspan="10"
                    class="text-center text-muted"
                >
                    No alert configurations found.
                </td>
            </tr>
        `);

        return;
    }

    let html = '';

    $.each(rules, function (index, rule) {

        let usersHtml = '';

        $.each(
            rule.users || [],
            function (userIndex, userName) {

                usersHtml +=
                    '<span class="user-badge">' +
                    escapeHtml(userName) +
                    '</span>';
            }
        );

        if (usersHtml === '') {

            usersHtml =
                '<span class="text-muted">-</span>';
        }

        html += `
            <tr>

                <td>
                    ${index + 1}
                </td>

                <td>
                    ${escapeHtml(rule.project_name)}
                </td>

                <td>
                    ${escapeHtml(rule.sub_project_name)}
                </td>

                <td>
                    ${escapeHtml(rule.column_name)}
                </td>

                <td>
                    ${escapeHtml(rule.operator)}
                </td>

                <td>
                    ${escapeHtml(rule.value)}
                </td>

                <td>
                    ${usersHtml}
                </td>
                <td>

                    <button
                        type="button"
                        class="btn btn-add-new-button btn-sm
                               edit-rule mb-1"
                        data-id="${rule.id}"
                    >
                        Edit
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-danger
                               delete-rule mb-1"
                        data-id="${rule.id}"
                    >
                        Delete
                    </button>

                </td>

            </tr>
        `;
    });

    $('#rulesTableBody').html(html);
}

        /*
        |--------------------------------------------------------------------------
        | Reset modal
        |--------------------------------------------------------------------------
        */

        function resetModalForm() {

            $('#ruleId').val('');

            $('#alertRuleModalLabel').text(
                'Add Alert Rule'
            );

            $('#saveButtonText').text(
                'Save Rule'
            );

            $('#projectId')
                .val('')
                .trigger('change.select2');

            // $('#matchType')
            //     .val('AND')
            //     .trigger('change');

            clearSubProject();
            clearConditions();
            hideModalError();
            closeBetweenPopup();
            setSaveButtonLoading(false);
        }

        function clearSubProject() {

            $('#subProjectId')
                .html(
                    '<option value="">' +
                    'Select Sub Project' +
                    '</option>'
                )
                .prop('disabled', true)
                .trigger('change.select2');
        }

        function clearConditions() {

            $('#conditionsContainer')
                .find('.condition-column')
                .select2('destroy');

            $('#conditionsContainer')
                .find('.condition-operator')
                .select2('destroy');

            $('#conditionsContainer')
                .find('.condition-users')
                .select2('destroy');

            $('#conditionsContainer').empty();

            availableColumns = [];
            availableUsers = [];
            conditionCounter = 0;

            $('#addConditionButton')
                .prop('disabled', true);

            $('#optionWarning').hide();
        }

        function updateConditionNumbers() {

            $('.condition-row').each(
                function (index) {

                    $(this)
                        .find('.condition-number')
                        .text(index + 1);
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Messages
        |--------------------------------------------------------------------------
        */

        function showPageSuccess(message) {

            $('#pageErrorMessage').hide();

            $('#pageSuccessMessage')
                .text(message)
                .show();

            setTimeout(function () {
                $('#pageSuccessMessage')
                    .fadeOut();
            }, 4000);
        }

        function showModalError(message) {

            $('#modalErrorMessage')
                .html(escapeHtml(message))
                .show();

            $('.modal-body')
                .animate(
                    { scrollTop: 0 },
                    200
                );
        }

        function hideModalError() {

            $('#modalErrorMessage')
                .hide()
                .html('');
        }

        function showAjaxModalError(xhr) {

            let message =
                'Something went wrong.';

            if (
                xhr.responseJSON &&
                xhr.responseJSON.errors
            ) {
                let errorHtml =
                    '<ul class="mb-0">';

                $.each(
                    xhr.responseJSON.errors,
                    function (field, messages) {

                        $.each(
                            messages,
                            function (index, item) {

                                errorHtml +=
                                    '<li>' +
                                    escapeHtml(item) +
                                    '</li>';
                            }
                        );
                    }
                );

                errorHtml += '</ul>';

                $('#modalErrorMessage')
                    .html(errorHtml)
                    .show();

                return;
            }

            if (
                xhr.responseJSON &&
                xhr.responseJSON.message
            ) {
                message =
                    xhr.responseJSON.message;
            }

            showModalError(message);
        }

        function showAjaxPageError(xhr) {

            let message =
                'Something went wrong.';

            if (
                xhr.responseJSON &&
                xhr.responseJSON.message
            ) {
                message =
                    xhr.responseJSON.message;
            }

            $('#pageSuccessMessage').hide();

            $('#pageErrorMessage')
                .text(message)
                .show();
        }

        /*
        |--------------------------------------------------------------------------
        | Save button loading
        |--------------------------------------------------------------------------
        */

        function setSaveButtonLoading(loading) {

            $('#saveRuleButton')
                .prop('disabled', loading);

            if (loading) {

                $('#saveButtonText').text(
                    'Processing...'
                );

            } else {

                $('#saveButtonText').text(
                    $('#ruleId').val()
                        ? 'Update Rule'
                        : 'Save Rule'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Escape HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {

            return $('<div>')
                .text(
                    value === null ||
                    value === undefined
                        ? ''
                        : value
                )
                .html();
        }

    });
</script>
@endpush

