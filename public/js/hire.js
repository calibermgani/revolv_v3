function hireData(date_range_hire) {

    var date_range = date_range_hire;
    $.fn.dataTable.ext.errMode = 'none';

    var table = $("#hire_table").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 10,
        order: [[0, "desc"]],
        ajax: {
            url: baseUrl + "hire/indexData",
            data: function (d) {
                d.search_date = date_range;


            },
        },
        columns: [


            {
                data: "candidate_name",
                name: "candidate_name",
            },
            {
                data: "candidate_scope",
                name: "candidate_scope",
            },
            {
                data: "applied_position",
                name: "applied_position",
            },
            {
                data: "referred_by",
                name: "referred_by",
            },
            {
                data: "age",
                name: "age",
            },


            {
                data: "gender",
                name: "gender",
            },

            {
                data: "location",
                name: "location",
            },


            {
                data: "contact_no_1",
                name: "contact_no_1",
            },
            {
                data: "personal_email_id",
                name: "personal_email_id",
            },

            {
                data: "relevant_experience",
                name: "relevant_experience",
            },

            {
                data: "interviewer_name",
                name: "interviewer_name",
            },
            {
                data: "interview_date",
                name: "interview_date",
            },
            {
                data: "current_employer",
                name: "current_employer",
            },

            {
                data: "proposed_designation",
                name: "proposed_designation",
            },

            {
                data: "finalized_gross",
                name: "finalized_gross",
            },

            {
                data: "tentative_doj",
                name: "tentative_doj",
            },

            {
                data: "status",
                name: "status",
            },
            {
                data: "action",
                name: "action",
            },

        ]
    });
}




$(document).on("change", ".date_of_birth", function (e) {

    var today = new Date();
    var current_year = today.getFullYear();
    var dob_value = $(".date_of_birth[name=dob]").datepicker('getDate').getFullYear();

    var actual_age = parseInt(current_year - dob_value);
    if (actual_age != 0) {
        $('#age').val(actual_age);
    } else {
        $('#age').val('');
    }



});

$('#total_work_experience').keyup(function (event) {


    if (!this.value.match(/^(\d|-)+$/)) {
        this.value = this.value.replace(/[^.0-9-]/g, '');

    }
});

$('#relevant_experience').keyup(function (event) {


    if (!this.value.match(/^(\d|-)+$/)) {
        this.value = this.value.replace(/[^.0-9-]/g, '');

    }
});


/** Developer : Sathish
 *  Purpose : Download Hire Track Report
 *  Date : 03-08-2022
 */

$(document).on("click",".export_hire",function(e){

    var hire_list = $("#hire_report").serialize();
    location.href = baseUrl + "hire/downloadhireReport?form_value="+hire_list;

    event.preventDefault();
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    $.ajax({
        url: baseUrl + "hire/downloadhireReport",
        type: 'post',
        data:{
            hire_list:hire_list
        },
        success: function (data) {
            location.href = data.url;

        }
    });
    return false;
});


