@extends('layouts.app3')

@php
    use Carbon\Carbon;
@endphp

@section('content')

    <style>
        .non-ar-report-card {
            overflow: hidden;
            border: 0;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(15, 81, 95, 0.12);
        }

        .non-ar-report-header {
            position: relative;
            min-height: 88px;
            padding: 22px 26px;
            background: linear-gradient(
                135deg,
                #139ab3 0%,
                #118ea6 55%,
                #0d7e94 100%
            );
        }

        .non-ar-report-header::before {
            position: absolute;
            top: -75px;
            right: -40px;
            width: 210px;
            height: 210px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            content: "";
        }

        .non-ar-report-header::after {
            position: absolute;
            right: 130px;
            bottom: -90px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            content: "";
        }

        .non-ar-title-wrapper {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
        }

        .non-ar-title-icon {
            display: flex;
            width: 44px;
            height: 44px;
            margin-right: 13px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.17);
            color: #ffffff;
            font-size: 19px;
        }

        .non-ar-report-title {
            margin: 0;
            color: #ffffff;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .non-ar-report-description {
            margin: 4px 0 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 12px;
            font-weight: 400;
        }

        .non-ar-filter-section {
            padding: 25px 26px 12px;
            border-bottom: 1px solid #e7eff1;
            background: #ffffff;
        }

        .non-ar-filter-section .form-group {
            margin-bottom: 16px;
        }

        .non-ar-field-label {
            display: block;
            margin-bottom: 8px;
            color: #344054;
            font-size: 13px;
            font-weight: 600;
        }

        .non-ar-required {
            margin-left: 2px;
            color: #e53935;
        }

        .non-ar-field-wrapper {
            position: relative;
        }

        .non-ar-field-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            z-index: 5;
            transform: translateY(-50%);
            color: #139ab3;
            font-size: 15px;
            pointer-events: none;
        }

        /*
         * Normal text input.
         */
        .non-ar-filter-section .form-control {
            width: 100%;
            height: 46px;
            padding: 10px 14px 10px 41px;
            border: 1px solid #d4e1e4;
            border-radius: 8px;
            background: #fbfdfe;
            color: #263b40;
            font-size: 14px;
            box-shadow: none;
            transition:
                border-color 0.2s ease,
                background-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .non-ar-filter-section .form-control::placeholder {
            color: #98a7ab;
        }

        .non-ar-filter-section .form-control:hover {
            border-color: #86c9d5;
            background: #ffffff;
        }

        .non-ar-filter-section .form-control:focus {
            border-color: #139ab3;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(19, 154, 179, 0.12);
        }

        /*
         * Select2 design.
         */
        .non-ar-filter-section .select2-container {
            width: 100% !important;
        }

        .non-ar-filter-section
        .select2-container
        .select2-selection--single {
            height: 46px !important;
            border: 1px solid #d4e1e4 !important;
            border-radius: 8px !important;
            background: #fbfdfe !important;
            box-shadow: none !important;
            transition:
                border-color 0.2s ease,
                background-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .non-ar-filter-section
        .select2-container
        .select2-selection--single:hover {
            border-color: #86c9d5 !important;
            background: #ffffff !important;
        }

        .non-ar-filter-section
        .select2-container--focus
        .select2-selection--single,
        .non-ar-filter-section
        .select2-container--open
        .select2-selection--single {
            border-color: #139ab3 !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(19, 154, 179, 0.12) !important;
        }

        .non-ar-filter-section
        .select2-container
        .select2-selection--single
        .select2-selection__rendered {
            padding-right: 38px !important;
            padding-left: 41px !important;
            color: #263b40 !important;
            font-size: 14px;
            line-height: 44px !important;
        }

        .non-ar-filter-section
        .select2-container
        .select2-selection--single
        .select2-selection__placeholder {
            color: #667b80 !important;
        }

        .non-ar-filter-section
        .select2-container
        .select2-selection--single
        .select2-selection__arrow {
            top: 1px !important;
            right: 10px !important;
            height: 44px !important;
        }

        /*
         * Validation styles.
         *
         * Existing JavaScript directly applies border-color:red to the
         * Select2 selection. The !important rule below ensures the red
         * validation border is not overwritten by normal/focus styles.
         */
        .non-ar-filter-section
        .select2-container
        .select2-selection--single[style*="border-color: red"],
        .non-ar-filter-section
        .select2-container
        .select2-selection--single[style*="border-color:red"] {
            border-color: #e53935 !important;
            background-color: #fffafa !important;
            box-shadow: 0 0 0 3px rgba(229, 57, 53, 0.12) !important;
        }

        .non-ar-filter-section .form-control.is-invalid,
        .non-ar-filter-section .form-control.error,
        .non-ar-filter-section .has-error .form-control {
            border-color: #e53935 !important;
            background-color: #fffafa !important;
            box-shadow: 0 0 0 3px rgba(229, 57, 53, 0.12) !important;
        }

        .non-ar-filter-section
        select.is-invalid
        + .select2-container
        .select2-selection--single,
        .non-ar-filter-section
        select.error
        + .select2-container
        .select2-selection--single,
        .non-ar-filter-section
        .has-error
        .select2-container
        .select2-selection--single {
            border-color: #e53935 !important;
            background-color: #fffafa !important;
            box-shadow: 0 0 0 3px rgba(229, 57, 53, 0.12) !important;
        }

        .non-ar-filter-section label.error,
        .non-ar-filter-section .invalid-feedback {
            display: block;
            margin-top: 6px;
            color: #e53935 !important;
            font-size: 12px;
            font-weight: 500;
        }

        /*
         * Footer and buttons.
         */
        .non-ar-report-footer {
            display: flex;
            min-height: 78px;
            padding: 17px 26px;
            align-items: center;
            justify-content: flex-end;
            gap: 11px;
            background: #f8fbfc;
        }

        .non-ar-action-button {
            display: inline-flex;
            min-width: 125px;
            height: 42px;
            padding: 9px 18px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition:
                border-color 0.2s ease,
                background-color 0.2s ease,
                color 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .non-ar-clear-button {
            border: 1px solid #f3b5b2;
            background: #fff5f5;
            color: #d9363e;
        }

        .non-ar-clear-button:hover,
        .non-ar-clear-button:focus {
            border-color: #e67a76;
            background: #fdeaea;
            color: #bd252d;
            transform: translateY(-1px);
        }

        .non-ar-export-button {
            border: 1px solid #139ab3;
            background: linear-gradient(
                135deg,
                #139ab3 0%,
                #0d8198 100%
            );
            color: #ffffff;
            box-shadow: 0 4px 11px rgba(19, 154, 179, 0.24);
        }

        .non-ar-export-button:hover,
        .non-ar-export-button:focus {
            border-color: #0b758a;
            background: linear-gradient(
                135deg,
                #118da5 0%,
                #0a7185 100%
            );
            color: #ffffff;
            box-shadow: 0 6px 14px rgba(19, 154, 179, 0.3);
            transform: translateY(-1px);
        }

        .non-ar-action-button:active {
            transform: translateY(0);
        }

        @media (max-width: 991px) {
            .non-ar-filter-section {
                padding-bottom: 8px;
            }
        }

        @media (max-width: 575px) {
            .non-ar-report-header {
                padding: 20px 17px;
            }

            .non-ar-title-icon {
                width: 40px;
                height: 40px;
            }

            .non-ar-filter-section {
                padding: 20px 17px 7px;
            }

            .non-ar-report-footer {
                padding: 16px 17px;
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .non-ar-action-button {
                width: 100%;
            }
        }
    </style>

    <div class="card card-custom custom-card non-ar-report-card">
        <div class="card-body p-0">

            <form id="filterForm">

                <div class="non-ar-report-header">
                    <div class="non-ar-title-wrapper">
                        <div class="non-ar-title-icon">
                            <i class="fas fa-file-excel"></i>
                        </div>

                        <div>
                            <h4 class="non-ar-report-title">
                                Non AR Projects Report
                            </h4>

                            <p class="non-ar-report-description">
                                Select the report filters and export the required data
                            </p>
                        </div>
                    </div>
                </div>

                <div class="non-ar-filter-section">
    <div class="row">

        <div class="col-lg-4 col-md-6">
            <div class="form-group">
                <label for="project_id" class="non-ar-field-label">
                    Project
                    <span class="non-ar-required">*</span>
                </label>

                <div class="non-ar-field-wrapper">
                    <span class="non-ar-field-icon">
                        <i class="fas fa-building"></i>
                    </span>

                    @php
                        $projectList =
                            App\Http\Helper\Admin\Helpers::projectList();
                    @endphp

                    {!! Form::select(
                        'project_id',
                        $projectList,
                        $searchData['project_id'] ?? null,
                        [
                            'class' => 'text-black form-control select2 project_select',
                            'id' => 'project_id',
                            'placeholder' => 'Select Project',
                        ]
                    ) !!}
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="form-group">
                <label for="sub_project_id" class="non-ar-field-label">
                    Sub Project
                    <span class="non-ar-required">*</span>
                </label>

                <div class="non-ar-field-wrapper">
                    <span class="non-ar-field-icon">
                        <i class="fas fa-layer-group"></i>
                    </span>

                    @if (isset($searchData['project_id']))
                        @php
                            $subProjectList =
                                App\Http\Helper\Admin\Helpers::resolvSubProjectList(
                                    $searchData['project_id']
                                );
                        @endphp

                        {!! Form::select(
                            'sub_project_id',
                            $subProjectList,
                            $searchData['sub_project_id'] ?? null,
                            [
                                'class' => 'text-black form-control select2 sub_project_select',
                                'id' => 'sub_project_id',
                                'placeholder' => 'Select Sub Project',
                            ]
                        ) !!}
                    @else
                        @php
                            $subProjectList = [];
                        @endphp

                        {!! Form::select(
                            'sub_project_id',
                            $subProjectList,
                            null,
                            [
                                'class' => 'text-black form-control select2 sub_project_select',
                                'id' => 'sub_project_id',
                                'placeholder' => 'Select Sub Project',
                            ]
                        ) !!}
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12">
            <div class="form-group">
                <label for="work_date" class="non-ar-field-label">
                    Work Date
                </label>

                <div class="non-ar-field-wrapper">
                    <span class="non-ar-field-icon">
                        <i class="far fa-calendar-alt"></i>
                    </span>

                    {!! Form::text(
                        'work_date',
                        $searchData['work_date'] ?? null,
                        [
                            'class' => 'form-control form-control daterange',
                            'autocomplete' => 'off',
                            'id' => 'work_date',
                            'placeholder' => 'mm/dd/yyyy - mm/dd/yyyy',
                        ]
                    ) !!}
                </div>
            </div>
        </div>

    </div>
</div>

                <div class="non-ar-report-footer">
                    <button
                        class="btn non-ar-action-button non-ar-clear-button"
                        id="filter_clear"
                        tabindex="10"
                        type="button"
                    >
                        <i class="fas fa-undo-alt"></i>
                        <span>Clear</span>
                    </button>

                    <button
                        type="submit"
                        class="btn non-ar-action-button non-ar-export-button"
                        id="formUpdate_save"
                    >
                        <i class="fas fa-file-export"></i>
                        <span>Export Excel</span>
                    </button>
                </div>

            </form>

        </div>
    </div>
@endsection


@push('view.scripts')
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css"
    />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script>
        $(document).ready(function() {
            var start = moment().startOf('month');
            var end = moment().endOf('month');

            $('.daterange').attr("autocomplete", "off");

            $('.daterange').daterangepicker({
                showOn: 'both',
                startDate: start,
                endDate: end,
                showDropdowns: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Last Month': [
                        moment().subtract(1, 'month').startOf('month'),
                        moment().subtract(1, 'month').endOf('month')
                    ]
                }
            });

            // Clear value initially
            $('.daterange').val('');

            $(document).on('change', '#project_id', function() {
                KTApp.block('#filterForm', {
                    overlayColor: '#000000',
                    state: 'danger',
                    opacity: 0.1,
                    message: 'Fetching...',
                });

                var project_id = $(this).val();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type: "GET",
                    url: "{{ url('reports/get_non_ar_sub_projectList_details') }}",
                    data: {
                        project_id: project_id
                    },
                    success: function(res) {
                        $("#sub_project_id").val(res.subProject);

                        var sla_options =
                            '<option value="">-- Select --</option>';

                        $.each(res.subProject, function(key, value) {
                            sla_options =
                                sla_options +
                                '<option value="' + key + '">' +
                                value +
                                '</option>';
                        });

                        $("#sub_project_id").html(sla_options);

                        $("#user").val(res.resource);

                        var user_options =
                            '<option value="">Select User</option>';

                        $.each(res.resource, function(key, value) {
                            user_options =
                                user_options +
                                '<option value="' + key + '">' +
                                value +
                                '</option>';
                        });

                        $("#user").html(user_options);

                        KTApp.unblock('#filterForm');
                    },
                    error: function(jqXHR, exception) {}
                });
            });

            var table = $("#bulk_list").DataTable({
                processing: true,
                lengthChange: false,
                clientSide: true,
                searching: true,
                paging: false,
                info: false,
                scrollCollapse: true,
                scrollX: true,
                "initComplete": function(settings, json) {
                    $('body')
                        .find('.dataTables_scrollBody')
                        .addClass("scrollbar");
                },
                language: {
                    "search": '',
                    "searchPlaceholder": "   Search",
                },
                dom: "<'row'<'col-md-12'f><'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>"
            });

            table.buttons().container()
                .appendTo('.outside');

            $('#filter_clear').on('click', function(e) {
                window.location.href =
                    baseUrl +
                    'reports/non_ar_report' +
                    "?parent=" +
                    getUrlVars()["parent"] +
                    "&child=" +
                    getUrlVars()["child"];
            });

            $('#formUpdate_save').on('click', function(e) {
                e.preventDefault();

                if (
                    $('#project_id').val() == '' ||
                    $('#sub_project_id').val() == ''
                ) {
                    if ($('#project_id').val() == '') {
                        $('#project_id')
                            .next('.select2')
                            .find(".select2-selection")
                            .css('border-color', 'red');
                    } else {
                        $('#project_id')
                            .next('.select2')
                            .find(".select2-selection")
                            .css('border-color', '');
                    }

                    if ($('#sub_project_id').val() == '') {
                        $('#sub_project_id')
                            .next('.select2')
                            .find(".select2-selection")
                            .css('border-color', 'red');
                    } else {
                        $('#sub_project_id')
                            .next('.select2')
                            .find(".select2-selection")
                            .css('border-color', '');
                    }

                    return false;
                }

                // START LOADER
                KTApp.block('#filterForm', {
                    overlayColor: '#000000',
                    state: 'primary',
                    opacity: 0.2,
                    message: 'Generating report... Please wait',
                });

                var formData = $('#filterForm').serialize();

                $.post(
                    "{{ url('non-ar-run-python') }}",
                    formData,
                    function(res) {
                        checkFileReady(res.job_id);
                    }
                ).fail(function() {
                    KTApp.unblock('#filterForm');
                    alert("Failed to start report");
                });
            });

            function checkFileReady(jobId) {
                var interval = setInterval(function() {
                    $.get(
                        "{{ url('non-ar-check-report') }}/" + jobId,
                        function(res) {
                            if (res.ready) {
                                clearInterval(interval);

                                window.location.href =
                                    "{{ url('non-ar-download-report') }}/" +
                                    res.file;

                                KTApp.unblock('#filterForm');
                            }
                        }
                    ).fail(function() {
                        clearInterval(interval);
                        KTApp.unblock('#filterForm');
                        alert("Error checking report status");
                    });
                }, 5000);
            }
        });
    </script>
@endpush