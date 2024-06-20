// Sidebar notification common function
function js_notification(type, msg) {
    if (type == 'error') {
        toastr.error(msg, "", {
            showMethod: "slideDown",
            hideMethod: "slideUp",
            progressBar: !0,
            closeButton: true,
            timeOut: 2e4,
        });
    } else if (type == 'success') {
        toastr.success(msg, "", {
            showMethod: "slideDown",
            hideMethod: "slideUp",
            progressBar: !0,
            closeButton: true,
            timeOut: 2e4
        });
    }

}

//Right Click Disable

// document.addEventListener('contextmenu', function(e) {
//     e.preventDefault();
// },true);


/** Developer : Zuhail
 *  Date:   11-01-2023
 *  Purpose:  DateTable Design
 */

$('.table_date').DataTable({
    //paging: false,
    lengthChange: false,
    searching: false,
}
);

$(".date_picker1").attr("autocomplete", "off");


$('.tableModel').DataTable({
    ordering: false,
    lengthChange: false,
    searching: false,
    paging: false,
    ordering: false,
    info: false,
});

$('.DtableModel').DataTable({
    ordering: false,
    //paging: false,
    lengthChange: false,
    searching: false,

});



// Search field clear related changes
$('input').attr('autocomplete', 'nope');
jQuery(function ($) {
    $(document).on('click', '#clear_submit', function () {
        $(this).closest('form').find("#search_employee,#emp_id,#break_approve_date,#user_id").val("");
        $(this).closest('form').find("select").val("").trigger('change');
        $(this).closest('form').find("#search_submit").trigger('click');

        // var start_date = moment().startOf('date').subtract(1,'days');

        // var end_date = moment().startOf('date');
        // var search_date = start_date +' - '+ end_date;
        // alert(search_date);
        // $("#break_approve_date").val(search_date);
    });
});

jQuery(function ($) {
    $(document).on('click', '#clear_submit_month', function () {
        $(this).closest('form').find("#search_employee,#emp_id,#attendance_date,#user_id").val("");
        $(this).closest('form').find("#search_submit").trigger('click');
        $(this).closest('form').find("select").val("").trigger('change');

    });
});

// Datatable for server side function
$(function () {
    $.fn.dataTable.ext.errMode = 'none';

    console.log('production index Data');


    // logList
    var table = $("#logList").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        order: [[0, "desc"]],
        ajax: {
            url: baseUrl + "productions/indexData",
            data: function (d) {
                // d.user_type = $('#user_type').val();
                //d.status = $('#status').val();
            },
        },
        columns: [
            /*{
                data: "emp_id",
                name: "emp_id",
            },
            {
                data: "user_name",
                name: "user_name",
            },
            {
                data: "designation",
                name: "designation",
            },
            {
                data: "manager_id",
                name: "manager_id",
            },
            {
                data: "tl_id",
                name: "tl_id",
            },*/
            {
                data: "work_date",
                name: "work_date",
            },
            {
                data: "client",
                name: "client",
            },
            {
                data: "scope_of_work",
                name: "scope_of_work",
            },
            {
                data: "temp_movement",
                name: "temp_movement",
            },
            {
                data: "target",
                name: "target",
            },
            {
                data: "achieved",
                name: "achieved",
            },
            {
                data: "call_hours",
                name: "call_hours",
            },
            {
                data: "achieved%",
                name: "achieved%",
            },

            {
                data: "remarks",
                name: "remarks",
            },

            // {
            //     data: "leave",
            //     name: "leave",
            // },

            // {
            //     data: "late_in",
            //     name: "late_in",
            // },
            {
                data: "exceeded_break_hours",
                name: "exceeded_break_hours"
            },
        ]
    });

    //Production END Here


    $(document).on("change", ".js-filter-listing", function () {
        table.ajax.reload();
    });
});

// Login form validation

$(document).ready(function () {
    $("#loginForm").bootstrapValidator({
        message: "This value is not valid",
        feedbackIcons: {
            valid: "",
            invalid: "",
            validating: "",
        },
        fields: {
            email: {
                message: "Enter email",
                validators: {
                    notEmpty: {
                        message: "Enter email",
                    },
                    stringLength: {
                        max: 40,
                        message: "The email must be less than 40 characters",
                    },
                },
            },
            password: {
                validators: {
                    notEmpty: {
                        message: "Enter password",
                    },
                },
            },
        },
    });
});


// User create form validation

$(document).ready(function () {
    $("#userCreateForm").bootstrapValidator({
        message: "This value is not valid",
        feedbackIcons: {
            valid: "",
            invalid: "",
            validating: "",
        },
        fields: {
            user_name: {
                message: "Enter user name",
                validators: {
                    notEmpty: {
                        message: "Enter user name",
                    },
                    stringLength: {
                        max: 80,
                        message: "The user name must be less than 80 characters",
                    },
                },
            },
            /*
            email: {
                message: "Enter email",
                validators: {
                    notEmpty: {
                        message: "Enter email",
                    },
                    stringLength: {
                        max: 120,
                        message: "The email must be less than 120 characters",
                    },
                    emailAddress: {
                        message: "Enter valid email",
                    },
                    remote: {
                        message: "Email ID already exist",
                        url: baseUrl + "users/userEmailValidate",
                        data: {
                            email: $('input[name="email"]'),
                            user_id: $('input[name="id"]').val(),
                            _token: $('input[name="_token"]').val(),
                        },
                        type: "POST",
                    },
                },
            },
            */
            user_type: {
                message: "Select user type",
                validators: {
                    notEmpty: {
                        message: "Select user type",
                    },
                },
            },
            password: {
                validators: {
                    callback: {
                        message: "",
                        callback: function (value, validator) {
                            var pwd = value;
                            var c_pwd = validator.getFieldElements("confirm_password").val();
                            var focus = $('[name="password"]').is(":focus");

                            if (pwd == "") {
                                return {
                                    valid: false,
                                    message: "Enter password",
                                };
                            } else if (c_pwd != "" && pwd != c_pwd) {
                                return {
                                    valid: false,
                                    message: "Password does not match",
                                };
                            }

                            return true;
                        },
                    },
                },
            },
            confirm_password: {
                validators: {

                    callback: {
                        message: "",
                        callback: function (value, validator) {
                            var pwd = validator.getFieldElements("password").val();
                            var c_pwd = value;

                            if (c_pwd == "") {
                                return {
                                    valid: false,
                                    message: "Enter your confirm password",
                                };
                            } else if (pwd != c_pwd) {
                                return {
                                    valid: false,
                                    message: "Password does not match",
                                };
                            }
                            return true;
                        },
                    },
                },
            },
        }
    });
});


// User edit form validation
$(document).ready(function () {
    $("#userEditForm").bootstrapValidator({
        message: "This value is not valid",
        feedbackIcons: {
            valid: "",
            invalid: "",
            validating: "",
        },
        fields: {
            /*
            user_name: {
                message: "Enter user name",
                validators: {
                    notEmpty: {
                        message: "Enter user name",
                    },
                    stringLength: {
                        max: 80,
                        message: "The user name must be less than 80 characters",
                    },
                },
            },
            email: {
                message: "Enter email",
                validators: {
                    notEmpty: {
                        message: "Enter email",
                    },
                    stringLength: {
                        max: 120,
                        message: "The email must be less than 120 characters",
                    },
                    emailAddress: {
                        message: "Enter valid email",
                    },
                    remote: {
                        message: "Email ID already exist",
                        url: baseUrl + "users/userEmailValidate",
                        data: {
                            email: $('input[name="email"]'),
                            user_id: $('input[name="id"]').val(),
                            _token: $('input[name="_token"]').val(),
                        },
                        type: "POST",
                    },
                },
            },
            user_type: {
                message: "Select user type",
                validators: {
                    notEmpty: {
                        message: "Select user type",
                    },
                },
            },
            */
            password: {
                validators: {
                    callback: {
                        message: "",
                        callback: function (value, validator) {
                            var pwd = value;
                            var c_pwd = validator.getFieldElements("confirm_password").val();
                            console.log(pwd);
                            console.log(c_pwd);
                            var focus = $('[name="password"]').is(":focus");

                            if (pwd != c_pwd && c_pwd != '') {
                                return {
                                    valid: false,
                                    message: "Password does not match",
                                };
                            }

                            return true;
                        },
                    },
                },
            },
            confirm_password: {
                validators: {
                    callback: {
                        message: "",
                        callback: function (value, validator) {
                            var pwd = validator.getFieldElements("password").val();
                            var c_pwd = value;

                            if (pwd != c_pwd) {
                                return {
                                    valid: false,
                                    message: "Password does not match",
                                };
                            }
                            return true;
                        },
                    },
                },
            }
        }
    });
});

// User delete confirm  popup
$(document).on('click', '.js-delete', function (e) {
    var url = $(this).attr('data-url');
    if (confirm('Are you sure to delete ?')) {
        window.location.href = url;
    }
})


// scope of work link confirm  popup
$(document).on('click', '.js-scope', function (e) {
    var url = $(this).attr('data-url');
    window.location.href = url;
})

// Scope list datatable start

function scopeListing(url) {
    var table = $("#scopeList").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: url,
            data: function (d) {
                // d.user_type = $('#user_type').val();
                //d.status = $('#status').val();
            },
        },
        columns: [
            {
                data: "client_name",
                name: "client_name",
            },
            {
                data: "manager_id",
                name: "manager_id",
            },
            {
                data: "tl_id",
                name: "tl_id",
            },
            {
                data: "created_at",
                name: "created_at",
            },
            {
                data: "action",
                name: "action",
            },
        ]
    });
    $(document).on("change", ".js-filter-listing", function () {
        table.ajax.reload();
    });
}


// Scope list datatable end
//

// Scope list datatable start

//workAllocationList

$(document).on('change', '#user_name', function () {
    user_id = $('#user_name').val();
    if (user_id != '') {
        $.ajax({
            type: 'GET',
            url: baseUrl + "/work_allocation/emp_details?user_id=" + user_id,
            dataType: "json",
            success: function (data) {
                $('#emp_id').val(data.emp_id);
                $('#designation').val(data.designation);
            }
        });
    } else {
        $('#emp_id').val("");
        $('#designation').val("");
    }

    return false;
});

// production page add

$(document).on('keyup', '#achieved', function () {
    achieved = $('#achieved').val();
    target = $('#target').val();
    var perc = (achieved / target) * 100;
    var pVal = perc.toFixed(3);
    $('.achieved_per').val(pVal);
});

// Production adminLogList Start here
function adminPrdList(user, submitted_date, report_mgr, client_name) {
    var table = $("#adminLogList").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        order: [[0, "desc"]],
        ajax: {
            url: baseUrl + "productions/admin_indexData",
            data: function (d) {
                d.user = user;
                d.submitted_date = submitted_date;
                d.report_mgr = report_mgr;
                d.client_name = client_name;
                //d.status = $('#status').val();
            },
        },
        columns: [
            {
                data: "action",
                name: "action"
            },
            {
                data: "emp_id",
                name: "emp_id",
            },
            {
                data: "user_name",
                name: "user_name",
            },
            {
                data: "designation",
                name: "designation",
            },
            {
                data: "reporting_person",
                name: "reporting_person",
            },
            {
                data: "client_id",
                name: "client_id",
            },
            {
                data: "work_date",
                name: "work_date",
            },
            {
                data: "scope_of_work",
                name: "scope_of_work",
            },
            {
                data: "temp_movement",
                name: "temp_movement",
            },
            {
                data: "target",
                name: "target",
            },
            {
                data: "achieved",
                name: "achieved",
            },
            {
                data: "remarks",
                name: "remarks",
            },
            {
                data: "call_hours",
                name: "call_hours",
            },
            {
                data: "leave",
                name: "leave",
            },
            {
                data: "late_in",
                name: "late_in",
            },
            {
                data: "exceeded_break_hours",
                name: "exceeded_break_hours"
            }
        ]
    });
}

function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}
function user_list_data(user, status, emp_id, search_employee, user_id, report_mgr, location, project, department, reporting_mgr) {


    //var name2 = getUrlVars()["name2"];
    var table = $("#userTable").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        // fnDrawCallback: function () {

        //     $('#userTable tbody tr').click(function (emp_id) {

        //       // get position of the selected row
        //      // var position = table.fnGetPosition(this);

        //       // value of the first column (can be hidden)
        //       //var id = table.fnGetData(position)[0];
        //        // location.reload();
        //        var id = $(this).attr('emp_id');
        //      alert( id);
        //         //window.location.href =  baseUrl + "users/"+ ${table.row(this).data()[1]}+"/edit";
        //       // redirect
        //       //document.location.href = '?q=node/6?id=' + id
        //     })

        //   },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {

            // Bind click event
            $(nRow).click(function () {

                //alert(btoa(aData.user_id));
                window.location.href = baseUrl + "users/" + btoa(aData.user_id) + "/edit?parent=" + getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];

            });

            return nRow;
        },
        ajax: {
            url: baseUrl + "users/indexData",
            data: function (d) {
                d.user_type = user;
                d.status = status;
                d.emp_id = emp_id;
                d.search_employee = search_employee;
                d.user_id = user_id;
                d.report_mgr = report_mgr;
                d.location = location;
                d.project = project;
                d.department = department;
                d.reporting_mgr = reporting_mgr;
            },
        },

        columns: [
            {
                data: "emp_id",
                name: "emp_id",
                // render:function(data, type, row){
                //     // $url = url('/').'/users/'.$id.'/edit';
                //     return "<a href='/users/"+ row.id +"'>" + row.id + "</a>";

                // }
            },

            {
                data: "user_name",
                name: "user_name",
            },

            {
                data: "designation",
                name: "designation",
            },
            {
                data: "project",
                name: "project",
            },
            {
                data: "reporting_mgr",
                name: "reporting_mgr",
            },

            {
                data: "user_shift",
                name: "user_shift",
            },

            {
                data: "location",
                name: "location",
            },

            {
                data: "department",
                name: "department",
            },


            // {
            //     data: "created_at",
            //     name: "created_at",
            // },
            // {
            //     data: "doj",
            //     name: "doj",
            // },
            // {
            //     data: "action",
            //     name: "action",
            // },
        ],
    });
}

function leave_list_data() {
    var table = $("#applyLeaveTable").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 10,
        ajax: {
            url: baseUrl + "leave/get_leave_list?parent=" + getUrlVars()["parent"] + "&child=" + getUrlVars()["child"],
            data: function (d) {
            },
        },
        columns: [
            { data: 'from_date', name: 'from_date' },
            { data: 'no_of_days', name: 'no_of_days' },
            { data: 'status', name: 'status' },
            { data: 'leave_type', name: 'leave_type' },
            { data: 'remarks', name: 'remarks' },
            { data: 'action', name: 'action' },
            { data: 'apply_date', name: 'apply_date', visible: false },
        ],
        "ordering": false
    });
}

function permission_list_data() {
    var table = $("#applyPermissionTable").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 10,
        order: [[7, "desc"]],
        ajax: {
            url: baseUrl + "leave/get_permission_list",
            data: function (d) {
            },
        },
        columns: [
            { data: 'permission_date', name: 'permission_date' },
            { data: 'start_time', name: 'start_time' },
            { data: 'hours', name: 'hours' },
            { data: 'status', name: 'status' },
            { data: 'permission_type', name: 'permission_type' },
            { data: 'permission_remarks', name: 'permission_remarks' },
            { data: 'action', name: 'action' },
            { data: 'created_at', name: 'created_at', visible: false },
        ],
        "ordering": false
    });
}

function user_info_list_data() {
    var table = $("#userInfoTable").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 10,
        ajax: {
            url: baseUrl + "user_info/indexData",
            data: function (d) {
            },
        },
        columns: [
            {
                data: "emp_id",
                name: "emp_id",
            },
            {
                data: "user_name",
                name: "user_name",
            },
            {
                data: "action",
                name: "action",
            },

        ],
    });
}



/* Executive employee list start here */

function ExecutivePrdList(user, submitted_date) {

    var user = user;
    var submitted_date = submitted_date;



    var table = $("#logList").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        order: [[0, "desc"]],
        ajax: {
            url: baseUrl + "productions/indexData",
            data: function (d) {
                d.user = user;
                d.submitted_date = submitted_date;
                //d.status = $('#status').val();
            },
        },
        columns: [
            {
                data: "work_date",
                name: "work_date",
            },
            {
                data: "client",
                name: "client",
            },

            {
                data: "scope_of_work",
                name: "scope_of_work",
            },
            {
                data: "temp_movement",
                name: "temp_movement",
            },
            {
                data: "target",
                name: "target",
            },
            {
                data: "achieved",
                name: "achieved",

            },
            {
                data: "call_hours",
                name: "call_hours",
            },
            {
                data: "achieved%",
                name: "Ach%",
            },

            {
                data: "remarks",
                name: "remarks",
            },

            // {
            //     data: "leave",
            //     name: "leave",
            // },

            // {
            //     data: "late_in",
            //     name: "late_in",
            // },
            {
                data: "exceeded_break_hours",
                name: "exceeded_break_hours"
            },
        ]
    });
}


function Client_datas(status, client_name) {


    // Datatable for server side function Client page
    var table = $("#clientsTable").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        colReorder: true,
        ajax: {
            url: baseUrl + "clients/indexData",
            data: function (d) {
                d.client_name = client_name;
                d.status = status;
            },
        },
        columns: [
            {
                data: "client_name",
                name: "client_name",
            },
            {
                data: "service_scope",
                name: "service_scope",
            },
            {
                data: "approved_fte",
                name: "approved_fte",
            },
            {
                data: "manager",
                name: "manager",
            },
            {
                data: "created_at",
                name: "created_at",
            },
            {
                data: "action",
                name: "action",
            },
        ],
    });

}



// Datatable for server side function Client page end


/** Purpose : Scope of work datatable
 *  Developer : Muhammed Gani
 */

function Scopeof_work() {

    var client_report_form = $("#scopeof_report").serialize();

    // alert(client_report_form);


    var table = $("#workAllocationList").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: baseUrl + "work_allocation/indexData",
            data: function (d) {
                d.form_data = client_report_form
                //d.status = $('#status').val();
            },
        },
        columns: [
            {
                data: "emp_id",
                name: "emp_id",
            },
            {
                data: "user_id",
                name: "user_id",
            },
            {
                data: "client_name",
                name: "client_name",
            },
            {
                data: "work_scope",
                name: "work_scope",
            },

            {
                data: "report_person",
                name: "report_person",
            },
            {
                data: "target",
                name: "target",
            },
            {
                data: "created_at",
                name: "created_at",
            },
            {
                data: "action",
                name: "action",
            },
        ]
    });

}



/** Time Attendance change to Datatable
 *  Date : 03-06-2022
 */

function team_attendace() {

    var attendace_serial_form = $("#attendance_user_search").serialize();

    console.log(attendace_serial_form);

    var table = $("#tbl_myattendace").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: baseUrl + "work_allocation/userlogs_data",
            data: function (d) {
                d.form_data = attendace_serial_form;
                //d.status = $('#status').val();
            },
        },
        columns: [

            {
                data: "login_date",
                name: "login_date",
            },
            {
                data: "day",
                name: "day",
            },
            {
                data: "emp_id",
                name: "emp_id",
            },

            {
                data: "user_name",
                name: "user_name",
            },
            {
                data: "short_code",
                name: "short_code",
            },
            {
                data: "reporting_mgr",
                name: "reporting_mgr",
            },
            {
                data: "in_time",
                name: "in_time",
            },

            {
                data: "out_time",
                name: "out_time",
            },
            {
                data: "duration",
                name: "duration",
            },
            {
                data: "break_time",
                name: "break_time",
            },
            {
                data: "actual_hours",
                name: "actual_hours",
            },

        ]
    });



}


/** Developer: Sathish S
 *  Purpose : Reports Change On Table
 *  Date : 01-08-2022
 */

function report_details() {
    var report_serial_form = $("#report_search").serialize();
    console.log(report_serial_form);

    var table = $("#reportTable").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: baseUrl + "report/report_data",
            data: function (d) {
                d.form_data = report_serial_form;
                //d.status = $('#status').val();
            },
        },
        // columns: [

        //     {
        //         data: "emp_id",
        //         name: "emp_id",
        //     },
        //     {
        //         data: "project_name",
        //         name: "project_name",
        //     },

        //     {
        //         data: "user_name",
        //         name: "user_name",
        //     },
        //     {
        //         data: "user_type",
        //         name: "user_type",
        //     },
        //     {
        //         data: "manager",
        //         name: "manager",
        //     },

        //     {
        //         data: "team_leader",
        //         name: "team_leader",
        //     },
        //     {
        //         data: "target",
        //         name: "target",
        //     }

        // ]
    });



}
/**
 * heirarichy for manager
 * developed : Nehru
 *
 */
function user_list_manager_data(user, status, emp_id, search_employee, user_id, report_mgr) {
    var table = $("#userTableManager").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: baseUrl + "users/indexDataManager",
            data: function (d) {
                d.user_type = user;
                d.status = status;
                d.emp_id = emp_id;
                d.search_employee = search_employee;
                d.user_id = user_id;
                d.report_mgr = report_mgr;
            },
        },
        columns: [
            {
                data: "emp_id",
                name: "emp_id",
            },

            {
                data: "user_name",
                name: "user_name",
            },

            {
                data: "designation",
                name: "designation",
            },
            {
                data: "reporting_mgr",
                name: "reporting_mgr",
            },


            {
                data: "user_shift",
                name: "user_shift",
            },

            {
                data: "location",
                name: "location",
            },

            // {
            //     data: "user_type",
            //     name: "user_type",
            // },


            // {
            //     data: "created_at",
            //     name: "created_at",
            // },
            {
                data: "doj",
                name: "doj",
            },
            {
                data: "action",
                name: "action",
            },
        ],
    });
}



function ipAddress() {
    var i = 1;
    var table = $("#ipAddressList").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: baseUrl + "ipaddress/indexData",
            data: function (d) {

            },
        },
        columns: [
            {
                "render": function () {
                    return i++;
                }
            },
            {
                data: "ip_address",
                name: "ip_address",
            }
        ]
    });

}

function designation() {
    var i = 1;
    var table = $("#designationList").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: baseUrl + "designation/indexData",
            data: function (d) {

            },
        },
        columns: [
            {
                "render": function () {
                    return i++;
                }
            },
            {
                data: "designation",
                name: "designation",
            },
            {
                data: "status",
                name: "status",
            },
            {
                data: "action",
                name: "action",
            }
        ]
    });

}

/**
 * developer    : nehru
 * purpose      : heirarichy for manager
 * date         : 15-07-2022
 */
function team_attendace_manager() {

    var attendace_serial_form = $("#attendance_user_search").serialize();

    console.log(attendace_serial_form);

    var table = $("#tbl_myattendace_manager").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: baseUrl + "work_allocation/userlogs_data_manager",
            data: function (d) {
                d.form_data = attendace_serial_form;
                //d.status = $('#status').val();
            },
        },
        columns: [

            {
                data: "login_date",
                name: "login_date",
            },
            {
                data: "day",
                name: "day",
            },
            {
                data: "emp_id",
                name: "emp_id",
            },

            {
                data: "user_name",
                name: "user_name",
            },
            {
                data: "short_code",
                name: "short_code",
            },
            {
                data: "reporting_mgr",
                name: "reporting_mgr",
            },
            {
                data: "in_time",
                name: "in_time",
            },

            {
                data: "out_time",
                name: "out_time",
            },
            {
                data: "duration",
                name: "duration",
            },
            {
                data: "break_time",
                name: "break_time",
            },
            {
                data: "actual_hours",
                name: "actual_hours",
            },

        ]
    });



}



/**
 * developer : nehru
 * purpose   : production hierarichy
 *
 */


function managerPrdList(user, submitted_date, report_mgr, client_name) {
    var table = $("#managerLogList").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        order: [[0, "desc"]],
        ajax: {
            url: baseUrl + "productions/manager_indexData",
            data: function (d) {
                d.user = user;
                d.submitted_date = submitted_date;
                d.report_mgr = report_mgr;
                d.client_name = client_name;
                //d.status = $('#status').val();
            },
        },
        columns: [
            {
                data: "action",
                name: "action"
            },
            {
                data: "emp_id",
                name: "emp_id",
            },
            {
                data: "user_name",
                name: "user_name",
            },
            {
                data: "designation",
                name: "designation",
            },
            {
                data: "reporting_person",
                name: "reporting_person",
            },
            {
                data: "client_id",
                name: "client_id",
            },
            {
                data: "work_date",
                name: "work_date",
            },
            {
                data: "scope_of_work",
                name: "scope_of_work",
            },
            {
                data: "temp_movement",
                name: "temp_movement",
            },
            {
                data: "target",
                name: "target",
            },
            {
                data: "achieved",
                name: "achieved",
            },
            {
                data: "remarks",
                name: "remarks",
            },
            {
                data: "call_hours",
                name: "call_hours",
            },
            // {
            //     data: "leave",
            //     name: "leave",
            // },
            // {
            //     data: "late_in",
            //     name: "late_in",
            // },
            {
                data: "exceeded_break_hours",
                name: "exceeded_break_hours"
            }
        ]
    });
}


function managerDailyProduction(user, submitted_date, report_mgr, client_name) {

    var table = $("#managerDailyProduction").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        order: [[0, "desc"]],
        ajax: {
            url: baseUrl + "report/daily_manager_indexData",
            data: function (d) {
                d.user = user;
                d.submitted_date = submitted_date;
                d.report_mgr = report_mgr;
                d.client_name = client_name;
                //d.status = $('#status').val();
            },
        },
        columns: [
            {
                data: "work_date",
                name: "work_date"
            },
            {
                data: "emp_id",
                name: "emp_id",
            },
            {
                data: "user_name",
                name: "user_name",
            },
            {
                data: "designation",
                name: "designation",
            },
            {
                data: "reporting_person",
                name: "reporting_person",
            },

            {
                data: "target",
                name: "target",
            },
            {
                data: "achieved",
                name: "achieved",
            },



        ]
    });
}

function adminDailyProduction(user, submitted_date, report_mgr, client_name) {

    var table = $("#adminDailyProduction").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        order: [[0, "desc"]],
        ajax: {
            url: baseUrl + "report/daily_admin_indexData",
            data: function (d) {
                d.user = user;
                d.submitted_date = submitted_date;
                d.report_mgr = report_mgr;
                d.client_name = client_name;
                //d.status = $('#status').val();
            },
        },
        columns: [
            {
                data: "work_date",
                name: "work_date"
            },
            {
                data: "emp_id",
                name: "emp_id",
            },
            {
                data: "user_name",
                name: "user_name",
            },
            {
                data: "designation",
                name: "designation",
            },
            {
                data: "reporting_person",
                name: "reporting_person",
            },

            {
                data: "target",
                name: "target",
            },
            {
                data: "achieved",
                name: "achieved",
            },



        ]
    });
}

function Scopeof_work_manager() {

    var client_report_form = $("#scopeof_report_form").serialize();

    // alert(client_report_form);


    var table = $("#workAllocationListManager").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: baseUrl + "work_allocation/indexDataManager",
            data: function (d) {
                d.form_data = client_report_form
                //d.status = $('#status').val();
            },
        },
        columns: [
            {
                data: "emp_id",
                name: "emp_id",
            },
            {
                data: "user_id",
                name: "user_id",
            },
            {
                data: "client_name",
                name: "client_name",
            },
            {
                data: "work_scope",
                name: "work_scope",
            },

            {
                data: "report_person",
                name: "report_person",
            },
            {
                data: "target",
                name: "target",
            },
            {
                data: "created_at",
                name: "created_at",
            },
            {
                data: "action",
                name: "action",
            },
        ]
    });

}

/** Developer : Sathish S
 *  Purpose : To view Ticket List In Datatable
 *  Date : 09-08-2022
 */

function ticket_list_data() {
    var table = $("#ticketReport").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: baseUrl + "ticketdiscussions/indexData",
            data: function (d) {
            },
        },
        columns: [
            {
                data: "type",
                name: "type",
            },
            {
                data: "ticket_id",
                name: "ticket_id",
            },
            {
                data: "summary",
                name: "summary",
            },
            {
                data: "status",
                name: "status",
            },
            {
                data: "action",
                name: "action",
            },
        ],
    });
}

function people_list_data(user, status, emp_id, search_employee, user_id, report_mgr, date_join, project, location, department) {
    var table = $("#PeopleReport").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: baseUrl + "report/peopleReportData",
            data: function (d) {
                d.user_type = user;
                d.status = status;
                d.emp_id = emp_id;
                d.search_employee = search_employee;
                d.user_id = user_id;
                d.report_mgr = report_mgr;
                d.date_join = date_join;
                d.project = project;
                d.location = location;
                d.department = department;
            },
        },
        columns: [
            {
                data: "emp_id",
                name: "emp_id",
            },

            {
                data: "user_name",
                name: "user_name",
            },

            {
                data: "designation",
                name: "designation",
            },
            {
                data: "project",
                name: "project",
            },
            {
                data: "reporting_mgr",
                name: "reporting_mgr",
            },


            {
                data: "user_shift",
                name: "user_shift",
            },

            {
                data: "location",
                name: "location",
            },

            // {
            //     data: "user_type",
            //     name: "user_type",
            // },


            // {
            //     data: "created_at",
            //     name: "created_at",
            // },
            // {
            //     data: "status",
            //     name: "status",
            // },
            {
                data: "doj",
                name: "doj",
            },

        ],
    });
}



// New user list

function new_user_list_data() {
    var table = $("#newuserTable").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {

            // Bind click event
            $(nRow).click(function () {

                window.location.href = baseUrl + "users/" + btoa(aData.user_id) + "/edit";

            });

            return nRow;
        },
        ajax: {
            url: baseUrl + "users/indexNewJoinerData",
            data: function (d) {
                /*  d.user_type = user;
                 d.status = status;
                 d.emp_id = emp_id;
                 d.search_employee = search_employee;
                 d.user_id = user_id;
             d.report_mgr = report_mgr; */
            },
        },
        columns: [
            {
                data: "emp_id",
                name: "emp_id",
                // render:function(data, type, row){
                //     // $url = url('/').'/users/'.$id.'/edit';
                //     return "<a href='/users/"+ row.id +"'>" + row.id + "</a>";

                // }
            },

            {
                data: "user_name",
                name: "user_name",
            },

            {
                data: "designation",
                name: "designation",
            },
            {
                data: "project",
                name: "project",
            },
            {
                data: "reporting_mgr",
                name: "reporting_mgr",
            },

            {
                data: "user_shift",
                name: "user_shift",
            },

            {
                data: "location",
                name: "location",
            },
        ],
    });
}

//Resigned People Data
function resigned_people_list_data() {
    var table = $("#resignedPeopleTable").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {

            // Bind click event
            $(nRow).click(function () {

                window.location.href = baseUrl + "user_details/user_resignation/" + btoa(aData.user_id);

            });

            return nRow;
        },
        ajax: {
            url: baseUrl + "users/indexResignedPeopleData",
            data: function (d) {

            },
        },
        columns: [
            {
                data: "date_of_request",
                name: "date_of_request",
            },

            {
                data: "request_by",
                name: "request_by",
            },

            // {
            //     data: "designation",
            //     name: "designation",
            // },
            // {
            //     data: "project",
            //     name: "project",
            // },
            // {
            //     data: "reporting_mgr",
            //     name: "reporting_mgr",
            // },

            // {
            //     data: "user_shift",
            //     name: "user_shift",
            // },

            // {
            //     data: "location",
            //     name: "location",
            // },
        ],
    });
}

function mrf_list_data(location, vertical, client_name, request_date, view_all, request_by) {
    var table = $("#mrf_list_data").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,

        "fnRowCallback": function (nRow, aData, iDisplayIndex) {

            // Bind click event
            $('td:not(:last-child)', nRow).on('click', function () {

                //alert(btoa(aData.user_id));
                //   window.location.href =  baseUrl + "attendance/"+btoa(aData.user_id)+"/edit";
                window.location.href = baseUrl + "attendance/approver/" + btoa(aData.id) + "?parent=" + getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];

            });

            return nRow;
        },
        ajax: {
            url: baseUrl + "attendance/approver_index_data",
            data: function (d) {

                d.location = location;
                d.vertical = vertical;
                d.client_name = client_name;
                d.request_date = request_date;
                d.view_all = view_all;
                d.request_by = request_by;
            },
        },

        columns: [
            {
                data: "id",
                name: "id",
                visible: false,
            },
            {
                data: "date_of_request",
                name: "date_of_request",
                // render:function(data, type, row){
                //     // $url = url('/').'/users/'.$id.'/edit';
                //     return "<a href='/users/"+ row.id +"'>" + row.id + "</a>";

                // }
            },

            {
                data: "request_by",
                name: "request_by",
            },

            {
                data: "location",
                name: "location",
            },
            {
                data: "vertical",
                name: "vertical",
            },
            {
                data: "client_name",
                name: "client_name",
            },

            {
                data: "request_type",
                name: "request_type",
            },
            {
                data: "action",
                name: "action",
            },



        ],
    });
}


function mprequest_list_ticket(vertical, job_profile, my_lists, others, support, date_of_request, location) {
    var table = $("#mrf_list").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: baseUrl + "attendance/mpr_ticket_list",
            data: function (d) {
                d.vertical = vertical;
                d.job_profile = job_profile;
                d.my_lists = my_lists;
                d.others = others;
                d.support = support;
                d.date_of_request = date_of_request;
                d.location = location;
            },
        },
        columns: [
            {
                data: "emp_id",
                name: "emp_id",
            },

            {
                data: "user_name",
                name: "user_name",
            },

            {
                data: "designation",
                name: "designation",
            },
            {
                data: "project",
                name: "project",
            },
            {
                data: "reporting_mgr",
                name: "reporting_mgr",
            },


            {
                data: "user_shift",
                name: "user_shift",
            },

            {
                data: "location",
                name: "location",
            },

            {
                data: "doj",
                name: "doj",
            },

        ],
    });
}


function wfm_list_data(location, vertical, client_name, request_date, view_all, request_by) {
    var table = $("#mrf_list_data").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,

        "fnRowCallback": function (nRow, aData, iDisplayIndex) {

            // Bind click event
            $(nRow).click(function () {

                //alert(btoa(aData.user_id));
                //   window.location.href =  baseUrl + "attendance/"+btoa(aData.user_id)+"/edit";
                window.location.href = baseUrl + "attendance/approver/" + btoa(aData.id);

            });

            return nRow;
        },
        ajax: {
            url: baseUrl + "attendance/wfm_index_data",
            data: function (d) {

                d.location = location;
                d.vertical = vertical;
                d.client_name = client_name;
                d.request_date = request_date;
                d.view_all = view_all;
                d.request_by = request_by;
            },
        },

        columns: [
            {
                data: "id",
                name: "id",
                visible: false,
            },
            {
                data: "date_of_request",
                name: "date_of_request",
                // render:function(data, type, row){
                //     // $url = url('/').'/users/'.$id.'/edit';
                //     return "<a href='/users/"+ row.id +"'>" + row.id + "</a>";

                // }
            },

            {
                data: "request_by",
                name: "request_by",
            },

            {
                data: "location",
                name: "location",
            },
            {
                data: "vertical",
                name: "vertical",
            },
            {
                data: "client_name",
                name: "client_name",
            },

            {
                data: "request_type",
                name: "request_type",
            },
            {
                data: "action",
                name: "action",
            },



        ],
    });
}


