// Class definition
var KTSelect2 = function() {
    // Private functions
    var demos = function() {


        // Trigger Mybox Select Boxes
        //$('#srch_user, #hired_current_role,#source_from,#bgv_status, #role_changed, #report_mgr,#served_notice,#exit_interview_completed, #marital_status, #gender, #client_name1, #status_br, #scope_of_work, #request_status, .js-approver, .js-status, #status, #user_type ,#client_id, .emp_name1, .js-shift, .js-scope, .js-gender, .js-client-name, .js-reporting-manager, .js-shift-type, .js-designation, #location, #user_name,#pref_gender, #client, #manager,#job_profile, #client_id, #project_scope, #short_code, #shift_time,#project_type,#project_status,#project_status,#department,#designation,#resource_type,#scope_list').select2({
        $('.select2, .js-approver, .js-status, .js-shift, .js-scope, .js-gender, .js-client-name, .js-reporting-manager, .js-shift-type, .js-designation, #bgv_by').select2({
            placeholder: "-- Select --",
        });

        $('.project_select').select2({
            placeholder: "Select Project"
        });

        $('.sub_project_select').select2({
            placeholder: "Select Sub Project"
        });

        $('.user_select').select2({
            placeholder: "Select User"
        });

        // basic
        $('#kt_select2_1, #kt_select2_1_validate').select2({
            placeholder: "Select a state"
        });

        // nested
        $('#kt_select2_2, #kt_select2_2_validate').select2({
            placeholder: "Select a state"
        });

        // multi select
        $('#kt_select2_3, #kt_select2_3_validate').select2({
            placeholder: "Select a state",
        });

        // basic
        $('#kt_select2_4').select2({
            placeholder: "-- Select --",
            allowClear: true
        });

        $('#kt_select2_5, #kt_select2_8, #kt_select2_7').select2({
            placeholder: "-- Select --",
            allowClear: true
        });
        $('.kt_select2_project').select2({
            placeholder: "Select Project"
        });
        $('.kt_select2_sub_project').select2({
            placeholder: "Select Sub Project"
        });
        $('.kt_select2_assignee').select2({
            placeholder: "Assignee"
        });
        $('.kt_select2_coder').select2({
            placeholder: "Select Coder"
        });
        $('.kt_select2_QA').select2({
            placeholder: "Select QA"
        });
        $('.kt_select2_priority').select2({
            placeholder: "Priority"
        });
        $('.kt_select2_qa_status').select2({
            placeholder: "Status"
        });
        $('.kt_select2_qa_sub_status').select2({
            placeholder: "Sub Status"
        });
        $('.kt_select2_qa_required_sampling').select2({
            placeholder: "QA Required"
        });
        $('.report_client_status').select2({
            placeholder: "Select Status"
        });
        // loading data from array
        // var data = [{
        //     id: 0,
        //     text: 'Enhancement'
        // }, {
        //     id: 1,
        //     text: 'Bug'
        // }, {
        //     id: 2,+
        //     text: 'Duplicate'
        // }, {
        //     id: 3,
        //     text: 'Invalid'
        // }, {
        //     id: 4,
        //     text: 'Wontfix'
        // }];

        $('#kt_select2_5').select2({
            placeholder: "Select a value",
            //data: data
        });

        // $('#department').select2({
        //     placeholder: "Select a Department",
        //     //data: data
        // });

        // loading remote data

        function formatRepo(repo) {
            if (repo.loading) return repo.text;
            var markup = "<div class='select2-result-repository clearfix'>" +
                "<div class='select2-result-repository__meta'>" +
                "<div class='select2-result-repository__title'>" + repo.full_name + "</div>";
            if (repo.description) {
                markup += "<div class='select2-result-repository__description'>" + repo.description + "</div>";
            }
            markup += "<div class='select2-result-repository__statistics'>" +
                "<div class='select2-result-repository__forks'><i class='fa fa-flash'></i> " + repo.forks_count + " Forks</div>" +
                "<div class='select2-result-repository__stargazers'><i class='fa fa-star'></i> " + repo.stargazers_count + " Stars</div>" +
                "<div class='select2-result-repository__watchers'><i class='fa fa-eye'></i> " + repo.watchers_count + " Watchers</div>" +
                "</div>" +
                "</div></div>";
            return markup;
        }

        function formatRepoSelection(repo) {
            return repo.full_name || repo.text;
        }

        $("#kt_select2_6").select2({
            placeholder: "Search for git repositories",
            allowClear: true,
            ajax: {
                url: "https://api.github.com/search/repositories",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    // parse the results into the format expected by Select2
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data, except to indicate that infinite
                    // scrolling can be used
                    params.page = params.page || 1;

                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            },
            escapeMarkup: function(markup) {
                return markup;
            }, // let our custom formatter work
            minimumInputLength: 1,
            templateResult: formatRepo, // omitted for brevity, see the source of this page
            templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
        });

        // custom styles

        // tagging support
        $('#kt_select2_12_1, #kt_select2_12_2, #kt_select2_12_3, #kt_select2_12_4').select2({
            placeholder: "Choose option",
        });

        // disabled mode
        $('#kt_select2_7').select2({
            placeholder: "Select an option"
        });

        // disabled results
        $('#kt_select2_8').select2({
            placeholder: "Select an option"
        });

        // limiting the number of selections
        $('#kt_select2_9').select2({
            placeholder: "Select an option",
            maximumSelectionLength: 2
        });

        // hiding the search box
        $('#kt_select2_10').select2({
            placeholder: "Select an option",
            minimumResultsForSearch: Infinity
        });

        // tagging support
        $('#kt_select2_11').select2({
            placeholder: "Add a tag",
            tags: true
        });

        // disabled results
        $('.kt-select2-general').select2({
            placeholder: "Select an option"
        });

        $('#kt_select2_12').select2({
            placeholder: "Select a Reason",
            allowClear: true
        });

        $('#kt_select2_13').select2({
            placeholder: "Select a Specialty",
            allowClear: true
        });
    }

    var modalDemos = function() {
        $('#kt_select2_modal').on('shown.bs.modal', function () {
            // basic
            $('#kt_select2_1_modal').select2({
                placeholder: "Select a state"
            });

            // nested
            $('#kt_select2_2_modal').select2({
                placeholder: "Select a state"
            });

            // multi select
            $('#kt_select2_3_modal').select2({
                placeholder: "Select a state",
            });

            // basic
            $('#kt_select2_4_modal').select2({
                placeholder: "Select a state",
                allowClear: true
            });
        });
    }

    // Public functions
    return {
        init: function() {
            demos();
            modalDemos();
        }
    };
}();

// Initialization
jQuery(document).ready(function() {
    KTSelect2.init();
});
