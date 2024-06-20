
// Break Module JS Start

$(document).on("click",".adjust_break",function(e){

    $('#myBreakadjustModal').modal('show');

 });



 function validateHhMm(inputField) {
   var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(inputField.value);

   if (isValid) {
     $('#err_msg').html('Valid Format');
   } else {
       $('#err_msg').html('Start / End Time In Valid Format !');
       $('#break_start').val('');
       $('#break_end').val('');
   }

   return isValid;
 }


 $(document).on("click",".close_adjust",function(e){

	$('#myBreakadjustModal').modal('hide');

  });


  $(document).on("click","#break_adjust_submit",function(e){

    var is_form_valid = false;
    $("input:visible").each(function () {

        $(this).prop("required", true);
    });

    $('#break_adjust_form_hours').find('input').each(function () {

        if ($(this).prop('required') && $("#"+this.id).val()=='') {
            //alert('Please fill the required fields');
            $('#err_msg').html('* Please fill the required fields');
            is_form_valid =false;
            return false;
        }else {
            is_form_valid = true;
        }

    });

    if(is_form_valid){

        //event.preventDefault();
        var break_adjustment_form = $('#break_adjust_form_hours').serialize();

        alert(break_adjustment_form);
        $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        $.ajax({
            url:baseUrl + "attendance/break_adjustment",
            type: 'POST',
            data:{
                break_adjustment_form: break_adjustment_form,

            },
            success: function (data) {
               if(data==1){
               js_notification('success','Record updated Successfully');
               $('#myBreakadjustModal').modal('hide');

               } else if(data==0){
                js_notification('success','Please Contact Administrator');

               }
            }
        });

    }

  });



  var break_reject_id = [];
$(document).ready(function () {
    $('#user_log').DataTable({bFilter: false});

});



$("#check_all_break").click(function () {
     $('input:checkbox').not(this).prop('checked', this.checked);
 });


 $(document).on("click",".approve_break",function(e){

	var break_id = [];

	$('input:checkbox[name=break_id]:checked').each(function()
    {

		break_id.push($(this).val());

    });

	var break_idJsonString = break_id;

	if(break_idJsonString!=''){

	    event.preventDefault();

		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url:baseUrl + "attendance/break_approved",
            type: 'post',
            data:{
				break_idJsonString: break_idJsonString,

			},
            success: function (data) {
               if(data==1){


				js_notification('success','Record updated Successfully');

				location.reload();

			   } else if(data==0){

                js_notification('error','Something Wrong Contact Administrator');


			   }
            }
        });
        return false;


	} else {


		js_notification('error','Choose any one break');
	}


 });

/** Developer : Sathish
  * Purpose   : Assign HR
  * Date      : 03-11-2022
  */

$("#check_all_requests").click(function () {
    $('input:checkbox').not(this).prop('checked', this.checked);
});

$(document).on("click",".assign_hr",function(e){

	var mrf_ticket_id = [];

	$('input:checkbox[name=mrf_ticket_id]:checked').each(function()
    {

		mrf_ticket_id.push($(this).val());

    });
    var assignTo = $('#assign_to').val();

	var mrf_ticket_idJsonString = mrf_ticket_id;

	if(mrf_ticket_idJsonString !='' && assignTo != ''){

	    event.preventDefault();

		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url:baseUrl + "attendance/assign_hr",
            type: 'post',
            data:{
				mrf_ticket_idJsonString: mrf_ticket_idJsonString,
                assignTo: assignTo,

			},
            success: function (data) {
                if(data==1){
                    js_notification('success','Record updated Successfully');
                    location.reload();

                } else if(data==0){
                    js_notification('error','Something Wrong Contact Administrator');

                }
            }
        });
        return false;


	} else {

		js_notification('error','Choose and select checkbox and Assign HR');
	}


 });


 /** Developer : Muhammed Gani
  *  Purpose : Reject The break
  *  Date : 27-05-2022
  */

  $(document).on("click",".reject_break",function(e){

		console.log('Debugging');

		$('input:checkbox[name=break_id]:checked').each(function()
		{

			break_reject_id.push($(this).val());

		});

		var break_idJsonString = break_reject_id;

		if(break_idJsonString!=''){

			$('#myBreakModal').modal('show');


		} else {
			js_notification('error','Choose any one break');

		}

  });

  $(document).on("click",".close",function(e){

	$('#myBreakModal').modal('hide');

  });



  $(document).on("click","#break_reject_submit",function(e){
    /*  new changes */
    var break_reason = $('#break_reject_reason').val();

    if(break_reason==''){
        $("#error").html("Please enter the rejected reason");
        return false;
    }else{
        var break_id = [];

	$('input:checkbox[name=break_id]:checked').each(function()
    {

		break_id.push($(this).val());

    });

	var break_id = break_id;
 /*  new changes  end */
	event.preventDefault();
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url:baseUrl + "attendance/break_rejected",
            type: 'post',
            data:{
				break_id: break_id,
				break_reason:break_reason

			},
            success: function (data) {

               if(data==1){

				//alert('Record updated Successfully');
                js_notification('success','Record Updated Successfully');
				$('#myBreakModal').modal('hide');
                location.reload();
			   } else if(data==0){

				alert('Something Wrong Contact Administrator');

			   }
            }
        });
        return false;


    }



  });




 // Break Module JS End


  /** Developer : Muhammed Gani
   *  Purpose : Break Hours details
   *  Date : 30-05-2022
   */

  function open_break_detail_popup(row_id,user_id) {





    $('#break_details').DataTable().destroy();
    $('#mybreakdetails').modal('show');
    $.fn.dataTable.ext.errMode = 'none';

    var table = $("#break_details").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 10,
        order: [[ 0, "desc" ]],
        ajax: {
            url:  baseUrl + "attendance/break_details",
            data: function (d) {
                d.break_details_rowid =row_id;
                d.user_id =user_id;

            },
        },
        columns: [

            {
                data: "date",
                name: "date",
            },
            {
                data: "break_type",
                name: "break_type",
            },
            {
                data: "break_start",
                name: "break_start",
            },
            {
                data: "break_end",
                name: "break_end",
            },
            {
                data: "duration",
                name: "duration",
            },
            {
                data: "approve_status",
                name: "approve_status",
            },
            {
                data: "break_rejected_reason",
                name: "break_rejected_reason",
            },

        ]
    });



  }

  $(document).on("click",".close_break_details",function(e){

	$('#mybreakdetails').modal('hide');

  });


  /** Purpose : Team Attendace Clear Filter
   *  Date : 03-06-2022
   *  Page : User Logs
   */

   $(document).on('click','#user_clear_submit',function() {
    location.reload();
});


  /** Purpose : Team Attendace Clear Filter
   *  Date : 03-06-2022
   *  Page : Manager Attendance
   */


   $(document).on('click','#mgr_clear_submit',function() {
    location.reload();
});





  /** Purpose : MPR Request Form
   *  Date : 04-06-2022
   *  Page : MPR form
   */


  /** MPR form function Start */

 $(document).on('click','#man_power_submit',function() {
    var is_form_valid = false;
    $("input:visible").each(function () {

        $(this).prop("required", true);
    });

    $('#man_power_request_form').find('input').each(function () {

        if ($(this).prop('required') && $("#"+this.id).val()=='') {
           // alert('Please fill the required fields');
           //$('#err_msg').html('All the fields are Mandatory');
           js_notification('error','All the Fields Mandatory');
            is_form_valid =false;
            return false;
        }else {
            is_form_valid = true;
        }

    });


     if(is_form_valid){


        event.preventDefault();
        var man_power_request_form = $('#man_power_request_form').serialize();


        $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        $.ajax({
            url:baseUrl + "attendance/create",
            type: 'POST',
            data:{
                man_power_request_form: man_power_request_form,

            },
            success: function (data) {


            alert(data);

               if(data==1){
               js_notification('success','Record Added Successfully');
               window.location.href=baseUrl+'attendance/list_of_mpr_request';

               } else if(data==0){
                js_notification('success','Please Contact Administrator');

               }
               else if(data==2){
                js_notification('error','All the Fields Mandatory');

               }
            }
        });


     }

});


   /**
    *  Developer : Muhammed Gani
    *  Date : 06-06-2022
    *  Purpose : Fetch MPR Request
    *  */


   function getMpr_Request() {

         $.fn.dataTable.ext.errMode = 'none';

         var table = $("#mptTable").DataTable({
             processing: true,
             serverSide: true,
             searching: false,
             lengthChange: false,
             pageLength: 10,
             order: [[ 0, "desc" ]],
             ajax: {
                 url:  baseUrl + "attendance/list_of_mpr_data",
                 data: function (d) {
                     //d.break_details_rowid =row_id;
                    // d.user_id =user_id;

                 },
             },
             columns: [

                 {
                     data: "date_of_request",
                     name: "date_of_request",
                 },
                 {
                     data: "expected_date",
                     name: "expected_date",
                 },
                 {
                     data: "request_by",
                     name: "request_by",
                 },
                 {
                     data: "client_name",
                     name: "client_name",
                 },
                 {
                     data: "shift_time",
                     name: "shift_time",
                 },
                 {
                     data: "job_profile",
                     name: "job_profile",
                 },

                 {
                    data: "remarks",
                    name: "remarks",
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


   $(document).on("click","#print_mpr",function(e){

    $("#man_power_submit").hide();
    $("#man_power_clear").hide();


       var divToPrint=document.getElementById("mpr_div");
       newWin= window.open("");
       newWin.document.write(divToPrint.outerHTML);
       newWin.print();
       newWin.close();

});






  /** MPR form function End */


/** Password Reset Feature Implement Start */

  /** Developer : Muhammed Gani
   *  Purpose : Password Reset for Admin
   *  Date : 07-06-2022
   */


    $(document).on("click",".password_reset_user",function(e){
    var myUserId = $(this).data('user_id');

    $('#reset_user_id').val(myUserId);

    });
    // SOP Email Details Show here
    $(document).on("click",".email_detail_sop",function(e){
        var myUserId = $(this).data('sop_id');
        $.ajax({
            url:baseUrl + "sop/sop_mail_details/"+myUserId,
            type: 'GET',
            success: function (data) {
                $('#c_email').val(data.from_email_id)
                $('#cc_email').val(data.cc_email_id)
                $('#client_id').val(data.client)
                $('#status').val(data.status)
            }
        });
    });

   $(document).on('click','#password_reset_submit',function() {


    var password = $('#password').val();
    var confirm_password = $('#confirm_password').val();
    var reset_user_id = $('#reset_user_id').val();

    if(password ==''){
        $('#pwd_err_msg').html('Password and Confirm Password mandatory !');


    } else{

    if(password==confirm_password){

        $('#pwd_err_msg').html('Password  match !');


        event.preventDefault();
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url:baseUrl + "attendance/password_reset",
            type: 'post',
            data:{
                 confirm_password: confirm_password,
                 reset_user_id:reset_user_id

			},
            success: function (data) {

               if(data==1){
                   js_notification('success','Password update sucessfully');
                   $('#passwordModal').hide();
                   location.reload();
               }

            }
        });


    } else{

        $('#pwd_err_msg').html('Password does not match !');
    }

}


});




  /** Password Reset Feature Implement End */


/* User Deactivate function start */


  /** Developer : Muhammed Gani
   *  Purpose : user inactive popup open
   *  Date : 08-06-2022
   */


$(document).on('click','#s_inactive',function() {


    $('#inactive_user_modal').modal('show');
    var user_id = $('#user_id').val();
    $('#inactive_user').val(user_id);


});

  /** Developer : Muhammed Gani
   *  Purpose : user inactive status update
   *  Date : 08-06-2022
   */
$(document).on('click','#user_inactive_submit',function() {



   if($('#user_inactive_reason').val()!='' && $('#dol').val()!='') {

    var inactive_reason = $('#user_inactive_reason').val();
    var dol = $('#dol').val();
    var user_id = $('#inactive_user').val();

    event.preventDefault();
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url:baseUrl + "users/user_inactive",
            type: 'post',
            data:{
                inactive_reason: inactive_reason,
                dol:dol,
                user_id:user_id

			},
            success: function (data) {

               if(data==1){
                   js_notification('success','User Status update sucessfully');
                   $('#inactive_user_modal').hide();
                   location.reload();
               }else{

                  js_notification('error','Try Again later !');
                   $('#inactive_user_modal').hide();
                   location.reload();

               }

            }
        });





   }else{
      $('#err_msg_user_inactive').html('Please fill mandatory fields');
   }

});

$(document).on('click','.close_inactive_user',function() {
    $('#inactive_user_modal').hide();
    location.reload();


});


$('#inactive_user_modal').on('hidden.bs.modal', function () {
    location.reload();
  });


/**  User Deactivate function End */



/**  Change Manager Checking
 * Developer : Muhammed Gani
 * Purpose : Change is manager changes
 * Date : 16-06-2022
 *
 */

 $(document).on('change','#manager',function() {

    var mgr_id = $('#manager').val();
    var user_id = $('#emp_id').val();
    var exist_mgr_id = $('#mgr_id').val();



    event.preventDefault();
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

    $.ajax({
        url:baseUrl + "work_allocation/isExistMgr",
        type: 'post',
        data:{
            mgr_id: mgr_id,
            user_id:user_id


        },
        success: function (data) {



           if(data==1){
               is_CheckReportingMgr(mgr_id,user_id,exist_mgr_id);

           }else{

              /* js_notification('error','Try Again later !');
               $('#inactive_user_modal').hide();
               location.reload(); */

           }

        }
    });


});

function is_CheckReportingMgr(mgr_id,user_id,exist_mgr_id) {


    const swalWithBootstrapButtons = Swal.mixin({
          customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
          },
          buttonsStyling: false
        })

    if(mgr_id != exist_mgr_id){
        swalWithBootstrapButtons.fire({
            title: 'Are you sure?',
            text: "You want to change Reporting Manager!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Change it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
          }).then((result) => {
            if (result.isConfirmed) {

              $('#change_mrg').val(mgr_id);
              swalWithBootstrapButtons.fire(
                'Approved!',
                'Reporting Manager Changed after you complete the allocation.',
                'success'
              )





            } else if (
              /* Read more about handling dismissals below */
              result.dismiss === Swal.DismissReason.cancel
            ) {
              swalWithBootstrapButtons.fire(
                'Cancelled',
                'Reporting Manager Change Cancelled :)',
                'error'
              )
            }
          });
    }else{
        $('#change_mrg').val(mgr_id);
    }





        }


 /** Change Manager End */


 /** MOM Script Start */

 /** Developer Name : nehru
	 *  Purpose : Get emp designation
	 *  Date : 013-06-2022
	 */
$(document).on("change","#emp_name1",function(e){

    var emp_id = $("#emp_name1").val();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
$.ajax({
    url:baseUrl + "mom/get_user_designation",
    type: 'GET',
    data:{
        emp_id: emp_id,

    },
    success: function (data) {
        //alert(data.id);

        if(data.email==null){
           var email = '';
        }else{
           var email = data.email;
        }
        $("#emp_table").append('<tr ><input type="hidden" class="form-control" id="emp_id1" name="emp_id[]" value='+ data.emp_id +'> </td><td><input type="text" class="form-control" id="emp_name1" name="name[]" value="'+ data.user_name +'"> </td> <td><input type="text" class="form-control" id="email_id" name="email_id[]" value="'+ email +'" /></td><td align="center"><a href="javascript:void(0);" class="remCF" ><i class="fas fa-plus-circle rotate-close" style="font-size: 22px !important; padding-top:10px; color:red;"></i></a></td> </tr>');
       }

    });

  });

  $("#emp_table").on('click','.remCF',function(){

    $(this).parent().parent().remove();
});

$(document).on('click','.meeting_rec_uploaded',function() {
  var upload_value = $('input[name=meeting_rec_uploaded]:checked').val();
  if(upload_value=='Yes'){
      $("#file_upload").show();
  }else{
    $("#meeting_file").val("");
    $("#file_upload").hide();
  }

});


  $("#addMore").click(function(){
    $("#client_details").append('<tr ><td><input type="hidden" class="form-control" id="client_emp_id" name="emp_id[]" value="null"><input type="text" class="form-control" id="client_name" name="name[]" > </td> <td><input type="text" class="form-control" id="client_email_id" name="email_id[]" /></td><td align="center"><a href="javascript:void(0);" class="client_remove" ><i class="fas fa-plus-circle rotate-close" style="font-size: 22px !important; color:red;padding-top:10px" title="Remove" ></i></a></td> </tr>');
});


$("#client_details").on('click','.client_remove',function(){
    $(this).parent().parent().remove();
});

    $('#meeting_start_time,#meeting_end_time').on('keyup change',function()
    {
      var start_time = $('#meeting_start_time').val();
      var end_time = $('#meeting_end_time').val();

    var diff =  Math.abs(new Date("1970-1-1 " + end_time) - new Date("1970-1-1 " + start_time));
    var seconds = Math.floor(diff/1000); //ignore any left over units smaller than a second
    var minutes = Math.floor(seconds/60);
    seconds = seconds % 60;
    var hours = Math.floor(minutes/60);
    minutes = minutes % 60;
    if(hours<10){
        hours = '0'+ hours;
    }
    if(minutes<10){
        minutes = '0'+ minutes;
    }

    $("#call_duration").val( hours + ":" + minutes);
//console.log("Diff = " + hours + ":" + minutes + ":" + seconds);



    });

    function getMom_datas() {

        $.fn.dataTable.ext.errMode = 'none';

        var table = $("#MeetingsTable").DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            pageLength: 10,
            order: [[ 0, "desc" ]],
            ajax: {
                url:  baseUrl + "mom/list_of_mom_data",
                data: function (d) {
                    //d.break_details_rowid =row_id;
                   // d.user_id =user_id;

                },
            },
            columns: [


                {
                    data: "meeting_date",
                    name: "meeting_date",
                },
                {
                    data: "client_id",
                    name: "client_id",
                },
                {
                    data: "subject",
                    name: "subject",
                },
                {
                    data: "meeting_start_time",
                    name: "meeting_start_time",
                },
                {
                    data: "meeting_end_time",
                    name: "meeting_end_time",
                },
                {
                    data: "call_duration",
                    name: "call_duration",
                },
                {
                    data: "action",
                    name: "action",
                },

            ]
        });

  }

  $(document).on("click","#print_mom",function(e){

    $("#man_power_submit").hide();
    $("#man_power_clear").hide();

       var divToPrint=document.getElementById("mom_full_page");
       newWin= window.open("");
       newWin.document.write(divToPrint.outerHTML);
       newWin.print();
       newWin.close();

});


$('#budget').keyup(function (event) {


    if (!this.value.match(/^(\d|-)+$/)) {
        this.value = this.value.replace(/[^.0-9-]/g, '');

    }
});


$('#age_limit').keyup(function (event) {


	if (!this.value.match(/^(\d|-)+$/)) {
        this.value = this.value.replace(/[^0-9-]/g, '');
    }


});

 $(document).on("click","#practice_client_by_mgr",function(e){

    $('#practice_client_mgr_modal').modal('show');


 });

  $(document).on("click","#practice_mgr_submit",function(e){
    var client_id = [];

	$('input:checkbox[name=practice_client_name]:checked').each(function()
    {

		client_id.push($(this).val());

    });


    $('#practice_client_list_mgr').val(client_id);
    $('#practice_client_mgr_modal').modal('hide');


  });

/**
 * developer: nehru
 * purpose  : mom form reset
 * date     : 27/06/2022
 */
 $(document).on("click","#mom_submit",function(){
    var meeting_date        = $("#meeting_date").val();
    var client_id           = $("#client_id").val();
    var subject             = $("#subject").val();
    var meeting_start_time  = $("#meeting_start_time").val();
    var meeting_end_time    = $("#meeting_end_time").val();
    var emp_name            = $("#emp_name1").val();
    var points_discussed    = tinyMCE.get('points_discussed').getContent();
    var upload_value        = $('input[name=meeting_rec_uploaded]:checked').val();
    var meeting_file        = $("#meeting_file").val();

  if(meeting_date==''){
    $("#error").html('* The Meeting Date field Required.');
    $(window).scrollTop(0);
    return false;
  }
  else if(client_id==''){
    $("#error").html('* The Client Name field Required.');
    $(window).scrollTop(0);
    return false;
  }
  else if(subject==''){
    $("#error").html('* The Subject field Required.');
    $(window).scrollTop(0);
    return false;
  }
  else if(meeting_start_time==''){
    $("#error").html('* The Start time field Required.');
    $(window).scrollTop(0);
    return false;
  }

  else if(meeting_end_time==''){
    $("#error").html('* The End time field Required.');
    $(window).scrollTop(0);
    return false;
  }


  else if(points_discussed==''){
    $("#error").html('* The Point Discussed field Required.');
    $(window).scrollTop(0);
    return false;
  }
  if(window.location.href== baseUrl+'mom/mom_create'){
    if(emp_name==''){
      $("#error").html('* The Attendees field Required.');
      $(window).scrollTop(0);
      return false;
    }
  }
  if(meeting_start_time!=''){

var time_format_start = momvalidateHhMm(meeting_start_time);
if(time_format_start==false){
   return false;
  }
}

if(meeting_end_time!=''){

  var time_format_end =   momvalidateHhMm(meeting_end_time);
  if(time_format_end==false){
    return false;
  }

  }
  if(upload_value=='Yes' && meeting_file==''){

    $("#error").html('* The File Path field Required.');
    $(window).scrollTop(0);
    return false;

  }

  });

 $(document).on("click","#mom_reset",function(){

    location.reload();
  });


  function momvalidateHhMm(inputField) {

    var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])?$/.test(inputField);
    if (!isValid) {
      $('#error').html('* Please Enter Valid Time Format');
      $(window).scrollTop(0);
    //return false;
    }
    // else {
    //     $('#err_msg').html('Start / End Time In Valid Format !');
    //     $('#break_start').val('');
    //     $('#break_end').val('');
    // }

    return isValid;
  }

  $("#annexaddMore").click(function(){
    $("#annex_emp_details").append('<tr ><td><input type="hidden" class="form-control" id="annex_emp_id" name="emp_id[]" value="annex_emp"><input type="text" class="form-control" id="annex_emp_name" name="name[]" > </td> <td><input type="text" class="form-control" id="annex_emp_email" name="email_id[]" /></td><td align="center"><a href="javascript:void(0);" class="annex_emp_remove" ><i class="fas fa-plus-circle rotate-close" style="font-size: 22px !important; color:red;padding-top:10px;"></i></a></td> </tr>');
});


$("#annex_emp_details").on('click','.annex_emp_remove',function(){
    $(this).parent().parent().remove();
});
 /** MOM Script End */

/* Print Option script added - Aaghash  */
function printDiv(divName) {
    $("#file_path").hide();
     var printContents = document.getElementById(divName).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
}

/* Print Option script Ends - Aaghash  */



/**
 * developer: nehru
 * purpose : sop
 * date     : 30-06-2022
 */
 function getSop_datas() {

    $.fn.dataTable.ext.errMode = 'none';

    var table = $("#SopTable").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 10,
        order: [[ 0, "desc" ]],
        ajax: {
            url:  baseUrl + "sop/list_of_sop_data",
            data: function (d) {
                //d.break_details_rowid =row_id;
               // d.user_id =user_id;

            },
        },
        columns: [


            {
                data: "client_id",
                name: "client_id",
            },
            {
                data: "upload_by",
                name: "upload_by",
            },
            {
                data: "upload_date",
                name: "upload_date",
            },
            {
                data: "approved_by",
                name: "approved_by",
            },
            {
                data: "approved_date",
                name: "approved_date",
            },
            {
                data: "approved_status",
                name: "approved_status",
            },
            {
                data: "file_name",
                name: "file_name",
            },
            {
                data: "file_uploaded_version",
                name: "file_uploaded_version",
            },
            {
                data: "action",
                name: "action",
            },

        ]
    });

}


// $(document).on("click",".sop_approver_class",function(){
//     alert($(this).data("id"));
//     $('#sop_approve_model').modal('show');
// });
function sop_approve_model(row_id,client,approve,client_id,status) {


    //$('#break_details').DataTable().destroy();
    $('#sop_approve_model').modal('show');
    $("#client_id_new").val(client_id);

    $("#client_name").val(client);
    $("#approved_by").val(approve);
    if(status=='Published'){

    $("#approved_status").empty().append("<option value=''>Select Status</option><option value='Rejected'>Rejected</option> <option value='Withdraw'>Withdraw</option>");
    }else{
        $("#approved_status").empty().append("<option value=''>Select Status</option><option value='Published'>Published</option> <option value='Rejected'>Rejected</option> <option value='Withdraw'>Withdraw</option>");
    }

  }

  $(document).on("click",".model_close",function(){

        $("#sop_approve_model").hide();
        location.reload();
  });

  $(document).on("click","#sop_submit",function(){
   var client =$("#client_id").val();
   var aproved =$("#approved_by").val();
   var file_name = $("#file").val();
   if(client ==''){
    $("#sop_error").html("Client Name Field is Required !");
    return false;
   }else if(aproved==''){
    $("#sop_error").html("Approver Field is Required !");
    return false;
   }else if(file_name==''){
    $("#sop_error").html("File Upload Field is Required !");
    return false;
   }else if(file_name!=''){
    var ext = $('#file').val().split('.').pop().toLowerCase();
    if($.inArray(ext, ['pdf']) == -1) {
        $("#sop_error").html("Upload Only pdf file !");
        return false;
    }

   }

});

$(document).on("click","#approver_submit",function(){

    var aproved =$("#approved_status").val();
    var file_name = $("#file").val();

  if(aproved==''){
     $("#status_error").html("Status Field is Required !");
     return false;
    }
       else if(file_name!=''){
        var ext = $('#file').val().split('.').pop().toLowerCase();
        if($.inArray(ext, ['pdf']) == -1) {
            $("#sop_error").html("Upload Only pdf file !");
            return false;
        }

       }

 });

 /**
  * developer   : nehru
  * purpose     : sop viewed report
  * date        : 07/02/2022
  */
  function getSop_reportdatas(client,version,view_type) {

    $.fn.dataTable.ext.errMode = 'none';

    var table = $("#SopviewTable").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 10,
        order: [[ 0, "desc" ]],
        ajax: {
            url:  baseUrl + "sop/report_list_data",
            data: function (d) {
                d.client =client;
                d.version =version;
                d.view_type =view_type;

            },
        },
        columns: [


            {
                data: "client_id",
                name: "client_id",
            },
            {
                data: "version",
                name: "version",
            },
            {
                data: "emp_name",
                name: "emp_name",
            },
            {
                data: "emp_id",
                name: "emp_id",
            },
            {
                data: "viewed_date",
                name: "viewed_date",
            },

        ]
    });

}

$(document).on("click","#Proceed",function(){
  var id = $("#file_id").val();

        $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


        $.ajax({
            url:baseUrl + "sop/get_file_view_popup/"+id,
            type: 'POST',
            data:{
                id: id,

            },
            success: function (data) {
                $(function () {


               var window_url =baseUrl + 'sop/get_file_view/'+data;

                window.open(window_url, '_blank');


                $("#sop_publish").modal("hide");

                });
            }
        });


    });


    /**
     * developer : nehru
     * purpose   : policy for HR
     * Date      : 27-07-22
     */
     function getPolicy_datas() {

        $.fn.dataTable.ext.errMode = 'none';

        var table = $("#PolicyTable").DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            pageLength: 10,
            order: [[ 0, "desc" ]],
            ajax: {
                url:  baseUrl + "policy/list_of_policy_data",
                data: function (d) {
                    //d.break_details_rowid =row_id;
                   // d.user_id =user_id;

                },
            },
            columns: [


                {
                    data: "policy_name",
                    name: "policy_name",
                },
                {
                    data: "created_date",
                    name: "created_date",
                },
                {
                    data: "file_name",
                    name: "file_name",
                },
                {
                    data: "file_version",
                    name: "file_version",
                },
                {
                    data: "action",
                    name: "action",
                },

            ]
        });

    }


    /**
     * developer : Siva
     * purpose   : Ticket listing
     * Date      : 08-08-2022
     */
    function getTickets_details() {
/*
        $.fn.dataTable.ext.errMode = 'none';
    //alert(baseUrl + "tickets/list_of_ticket_data");
        var table = $("#ticketsTable").DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            pageLength: 10,
            order: [[ 0, "desc" ]],
            ajax: {
                url:  baseUrl + "tickets/list_of_ticket_data",
                data: function (d) {
                    //d.break_details_rowid =row_id;
                   // d.user_id =user_id;

                },
            },
            columns: [
                {
                    data: "select_orders",
                    name: "select_orders",
                    orderable: false,
                    searchable: false
                },
                {
                    data: "user_id",
                    name: "user_id",
                },
                {
                    data: "subject",
                    name: "subject",
                },
                {
                    data: "type_id",
                    name: "type_id",
                },
                {
                    data: "status_id",
                    name: "status_id",
                },
                {
                    data: "priority_id",
                    name: "priority_id",
                },
                {
                    data: "department_id",
                    name: "department_id",
                },
                {
                    data: "action",
                    name: "action",
                },

            ]
        });
    */
    }





 $(document).on("click",".assign_to_ticket",function(e){

	var ticket_id = [];

	$('input:checkbox[name=ticket_id]:checked').each(function()
    {

		ticket_id.push($(this).val());

    });
    var data =[];// { 'user_ids[]' : []};
    $('input[name="ticket_id[]"]:checked').each(function() {
    data.push($(this).val());
    });
    console.log(data);
    //ticket_id = [1,2,35];
	var ticket_idJsonString = data;//ticket_id;

	if(ticket_idJsonString!=''){

	    event.preventDefault();
        var assign_to_emp_name = $('#search_employee_tickets').val();
        alert(assign_to_emp_name)
        //var assign_to_emp_id = $('#emp_id').val();

		 $.ajaxSetup({
		 		headers: {
		 			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 		}
		 	});
        $.ajax({
            url:baseUrl + "tickets/approve_ticket",
            type: 'post',
            data:{
				ticket_idJsonString: data,
                assign_to_emp_name:assign_to_emp_name,
			},
            success: function (data) {
               if(data==1){


				js_notification('success','Record updated Successfully');

				location.reload();

			   } else if(data==0){

                js_notification('error','Something Wrong Contact Administrator');


			   }
            }
        });
        return false;


	} else {


		js_notification('error','Choose any one ticket');
	}


 });

$("#check_all_tickets").click(function () {
    $('input:checkbox').not(this).prop('checked', this.checked);
});



$(document).on("click","#rlh_id_yes",function(e){
    $("#relocation_history").attr("style", "display:block")
});

$(document).on("click","#ost_id_yes",function(e){
    $("#onsite_travel_details").attr("style", "display:block")
});


$(document).on("click","#rlh_id_no",function(e){
    $("#relocation_history").attr("style", "display:none")
});

$(document).on("click","#ost_id_no",function(e){
    $("#onsite_travel_details").attr("style", "display:none")
});


$(document).on("click","#resource_save",function(e){

    var emp_id = [];
$('input:checkbox[name=check_people_id]:checked').each(function()
{

    emp_id.push($(this).val());

});
var emp_id = emp_id;

var client_id = $('#clients_id').val();
var resource =  $('#resource_type').val();
var scope    =  $('#scope_list').val();
var utilization = $('#utilization').val();

if(resource==''){
    $("#error_resource").html("Please Choose Bill Type");
    return false;
}else if(scope ==null || scope ==''){
    $("#error_resource").html("Please Choose Scope List");
    return false;
}else if(utilization ==''){
    $("#error_resource").html("Please Fill the Utilization");
    return false;
}else if(emp_id==''){
$("#error_resource").html("Please Choose One People");
return false;

}
//else if(resource!='' && scope!=null && utilization!='' & emp_id!=''){
    else{
            event.preventDefault();
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

        $.ajax({
            url:baseUrl + "clients/resource_store",
            type: 'post',
            data:{
				emp_id: emp_id,
                client_id: client_id,
                resource: resource,
                scope: scope,
                utilization: utilization


			},
            success: function (data) {
            $("#error_resource").html('Record Inserted Successfully');
            $("input[name='check_people_id']").prop("checked", false);
            $("input[name='check_all_people']").prop("checked", false);
            //    if(data==1){

			// 	//alert('Record updated Successfully');
            //     js_notification('success','Record Updated Successfully');
			// 	$('#myBreakModal').modal('hide');
            //     location.reload();
			//    } else if(data==0){

			// 	alert('Something Wrong Contact Administrator');

			//    }
            }
        });
    }



  });
