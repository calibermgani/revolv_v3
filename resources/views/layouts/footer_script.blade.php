<!--begin::Global Config(global config for global JS scripts)-->
<script>
    var KTAppSettings = {
        "breakpoints": {
            "sm": 576,
            "md": 768,
            "lg": 992,
            "xl": 1200,
            "xxl": 1200
        },
        "colors": {
            "theme": {
                "base": {
                    "white": "#ffffff",
                    "primary": "#6993FF",
                    "secondary": "#E5EAEE",
                    "success": "#1BC5BD",
                    "info": "#8950FC",
                    "warning": "#FFA800",
                    "danger": "#F64E60",
                    "light": "#F3F6F9",
                    "dark": "#212121"
                },
                "light": {
                    "white": "#ffffff",
                    "primary": "#E1E9FF",
                    "secondary": "#ECF0F3",
                    "success": "#C9F7F5",
                    "info": "#EEE5FF",
                    "warning": "#FFF4DE",
                    "danger": "#FFE2E5",
                    "light": "#F3F6F9",
                    "dark": "#D6D6E0"
                },
                "inverse": {
                    "white": "#ffffff",
                    "primary": "#ffffff",
                    "secondary": "#212121",
                    "success": "#ffffff",
                    "info": "#ffffff",
                    "warning": "#ffffff",
                    "danger": "#ffffff",
                    "light": "#464E5F",
                    "dark": "#ffffff"
                }
            },
            "gray": {
                "gray-100": "#F3F6F9",
                "gray-200": "#ECF0F3",
                "gray-300": "#E5EAEE",
                "gray-400": "#D6D6E0",
                "gray-500": "#B5B5C3",
                "gray-600": "#80808F",
                "gray-700": "#464E5F",
                "gray-800": "#1B283F",
                "gray-900": "#212121"
            }
        },
        "font-family": "Poppins"
    };
</script>
<!--end::Global Config-->
<!--begin::Global Theme Bundle(used by all pages)-->

<script src="{{ asset('/assets/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('/assets/plugins/custom/prismjs/prismjs.bundle.js') }}"></script>
<script src="{{ asset('/assets/js/scripts.bundle.js') }}"></script>

<script src="{{ asset('/assets/js/pages/crud/datatables/advanced/row-callback.js') }}"></script>
<script src="{{ asset('/assets/js/pages/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('/assets/js/pages/custom/wizard/wizard-4.js') }}"></script>
<script src="{{ asset('/assets/js/pages/custom/education/school/calendar.js') }}"></script>
<script src="{{ asset('/assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>


<script src="{{ asset('/assets/js/pages/custom/wizard/wizard-1.js') }}"></script>
<script src="{{ asset('/assets/js/pages/crud/forms/widgets/tagify.js') }}"></script>

{{-- <script src="{{ asset('/assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script> --}}
<script src="{{ asset('/assets/js/pages/widgets.js') }}"></script>
<script src="{{ asset('/assets/js/pages/features/charts/apexcharts.js') }}"></script>





{{-- <script src="{{ asset('/assets/js/pages/crud/datatables/advanced/row-callback.js') }}"></script>
<script src="{{ asset('/assets/js/pages/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('/assets/js/pages/custom/wizard/wizard-4.js') }}"></script>
<script src="{{ asset('/assets/js/pages/custom/wizard/wizard-1.js') }}"></script>
<script src="{{ asset('/assets/js/pages/crud/forms/widgets/tagify.js') }}"></script>
<script src="{{ asset('/assets/js/pages/widgets.js') }}"></script>
<script src="{{ asset('/assets/js/pages/features/charts/apexcharts.js') }}"></script> --}}







<!--end::Global Theme Bundle-->
<!--begin::Page Scripts(used by this page)-->
<script src="{{ asset('/assets/js/pages/custom/login/login.js') }}"></script>
<script src="{{asset('/assets/js/pages/crud/forms/editors/quill.js')}}"></script>
<script src="{{asset('/assets/js/pages/custom/todo/todo.js')}}"></script>

<!--end::Page Scripts-->

<script src="{{ asset('/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('/js/hire.js') }}"></script>

<script src="{{ asset('/assets/js/pages/crud/file-upload/dropzonejs.js') }}"></script>
<script src="{{ asset('/assets/js/pages/features/cards/tools.js') }}"></script>
<script src="{{asset('/assets/js/pages/crud/forms/validation/form-controls.js')}}"></script>
	<!--begin::Page Scripts(used by this Login Page)-->
<script src="{{asset('/assets/js/pages/custom/login/login.js')}}"></script>
<script src="{{ asset('/assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
<script src="{{ asset('/assets/js/pages/crud/forms/editors/tinymce.js') }}"></script>
<script src="{{ asset('/assets/js/pages/crud/file-upload/uppy.js') }}"></script>
<script src="{{ asset('/assets/plugins/custom/uppy/uppy.bundle.js') }}"></script>
<script src="{{asset('assets/js/pages/features/miscellaneous/sweetalert2.js')}}"></script>
<script src="{{asset('assets/js/pages/features/miscellaneous/sweetalert2.min.js')}}"></script>
<script src="{{asset('assets/js/pages/crud/forms/widgets/bootstrap-datetimepicker.js')}}"></script>
<script src="{{asset('assets/js/pages/crud/ktdatatable/child/data-local.js')}}"></script>
<script src="{{ asset('/assets/js/pages/crud/forms/widgets/select2.js') }}"></script>
<script>
$('#status_reason').on('change', function() {
	if ( this.value == '1')
	{  $("#status_hide").show(); }
	else
	{  $("#status_hide").hide(); }
	});
</script>

{{-- <script>
function offerLetterCheck() {
    if (document.getElementById('letterCheck').checked) {
        document.getElementById("div1").style.display="none";
    }
    else {
        document.getElementById("div1").style.visibility="show";
    }
}
</script> --}}

<script>
	var baseUrl = "{{ asset('') }}";
</script>

<script src="{{asset('vendors/js/extensions/toastr.min.js')}}"></script>
<!-- BEGIN Vendor JS-->

<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.2/js/bootstrapValidator.min.js"></script>
<script src="{{asset('js/admin/js/scripts/extensions/toastr.min.js')}}"></script>




<script src="{{asset('js/jquery.cookie.js')}}"></script>
<script src="{{asset('js/developer.js')}}"></script>

<script src="{{asset('js/leave.js')}}"></script>
<script src="{{asset('js/custom.js')}}"></script>
<script src="{{asset('js/asset.js')}}"></script>





{{-- <script>
    var api_site_url = '{{url("/")}}';
	var isVisibiltyChanged = 0;

	$(document).on("click",".export_production",function(e){
		var export_type = $(this).data('type');
		var production_report_form = $("#production_report").serialize();
        event.preventDefault();
        var URL = "{{ route('productions.getProductionReport') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				export_type: export_type,
				production_report_form:production_report_form
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;
            }
        });
        return false;
    });

	$(document).on("click",".fnProfileBtn",function(e){
		location.href = '{{url("profile")}}';
	});
	/*$(function () {
		$('.dm-time').mask('00:00 SS');
	});
*/
	document.addEventListener('visibilitychange', function(){
		isVisibiltyChanged = 1;
			if(document.visibilityState == 'visible'){
				console.log("Handle session check");
				/*
				var ajaxUrl = "<?php echo url('/get_practice_session_id'); ?>";
				$.ajax({
                    type: "GET",
                    url: ajaxUrl,
                    dataType: "json",
                    success: function (json) {
						var current_id = json.id;
						if(prv_id != current_id) {

							$('.modal').modal('hide');		// closes all active pop ups.
							$('.modal-backdrop').remove() 	// removes the grey overlay.

							event.stopPropagation();
							$("#confirm_modal #confirmMsg").html("{{ trans('auth.practice_change') }}");
							$("#confirm_modal")
								.modal({ backdrop: 'static', keyboard: false})
								.on('click', '.js-confirm', function (e) {
									var conformation = $(this).attr('id');
									if (conformation == "true") {
										location.reload();
										return true;
									} else {
										return false;
									}
								});
							return false;
						}
                    }
                });
				*/
			}
	});

/* export user excell option */

$(document).on("click",".export_user",function(e){
		var export_type = $(this).data('type');
		var users_report_form = $("#user_report").serialize();

		//alert(users_report_form);

        event.preventDefault();
        var URL = "{{ route('users.getUserReport') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				export_type: export_type,
				users_report_form:users_report_form
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;
            }
        });
        return false;
    });



	/** Executive production export option */


	$(document).on("click",".executive_export_production",function(e){
		var export_type = $(this).data('type');
		var executive_production_report = $("#executive_production_report").serialize();
        event.preventDefault();
        var URL = "{{ route('productions.getexeProductionReport') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				export_type: export_type,
				executive_production_report:executive_production_report
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;
            }
        });
        return false;
    });



/* export client option */


$(document).on("click",".export_client",function(e){
		var export_type = $(this).data('type');
		var client_report_form = $("#client_report").serialize();

		//alert(users_report_form);

        event.preventDefault();
        var URL = "{{ route('clients.getClientReport') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				export_type: export_type,
				client_report_form:client_report_form
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;
            }
        });
        return false;
    });




 /**
  * Developer : Muhamemd Gani
  * Purpose : Export scope
  */

  $(document).on("click",".export_scope",function(e){
		var export_type = $(this).data('type');
		var scopeof_report_form = $("#scopeof_report").serialize();

		//alert(users_report_form);

        event.preventDefault();
        var URL = "{{ route('work_allocation.getScopeReport') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				export_type: export_type,
				scopeof_report_form:scopeof_report_form
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;
            }
        });
        return false;
    });



$(document).on("click",".break_type_button",function(e){


		var URL = "{{ route('users.getBreakType') }}";

		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
		$.ajax({
            url: URL,
            type: 'POST',
            data:{
				/* export_type: export_type,
				scopeof_report_form:scopeof_report_form */
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
               alert(data);
            }
        });

	});


	/** Developer : Sathish
	 *  Purpose : Download Attendance Report
	 *  Date : 15-06-2022
	 */

     $(document).on("click",".export_attendance",function(e){
		var export_type = $(this).data('type');
		var attendance_search = $("#attendance_user_search").serialize();

		// alert(users_report_form);

        event.preventDefault();
        var URL = "{{ route('attendance.getAttendanceReport') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				attendance_search:attendance_search
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;

            }
        });
        return false;
    });


    /** Developer : Sathish
	 *  Purpose : Download Break List Report
	 *  Date : 23-06-2022
	 */

     $(document).on("click",".export_break_list",function(e){
		var export_type = $(this).data('type');
		var break_list_search = $("#break_list_search").serialize();

		// alert(users_report_form);

        event.preventDefault();
        var URL = "{{ route('breaklist.getBreakListReport') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				break_list_search:break_list_search
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;

            }
        });
        return false;
    });


    $(document).on("click",".export_emp_leave",function(e){
		var export_type = $(this).data('type');
		var leave_report_search = $("#leave_report").serialize();

		// alert(users_report_form);

       event.preventDefault();
        var URL = "{{ route('leave.getLeaveListReport') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				leave_report_search:leave_report_search
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;

            }
        });
        return false;
    });


    $(document).on("click",".export_user_manager",function(e){
		var export_type = $(this).data('type');
		var users_report_form = $("#user_report").serialize();

		//alert(users_report_form);

        event.preventDefault();
        var URL = "{{ route('users.getUserReportManager') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				export_type: export_type,
				users_report_form:users_report_form
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;
            }
        });
        return false;
    });


    $(document).on("click",".export_scope_manager",function(e){
		var export_type = $(this).data('type');
		var scopeof_report_form = $("#scopeof_report_form").serialize();

		//alert(users_report_form);

        event.preventDefault();
        var URL = "{{ route('work_allocation.getScopeReportManager') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				export_type: export_type,
				scopeof_report_form:scopeof_report_form
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;
            }
        });
        return false;
    });


    $(document).on("click",".export_production_manager",function(e){
		var export_type = $(this).data('type');
		var production_report_form = $("#production_report").serialize();

        event.preventDefault();
        var URL = "{{ route('productions.getProductionReportManager') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				export_type: export_type,
				production_report_form:production_report_form
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;
            }
        });
        return false;
    });

    $(document).on("click",".export_attendance_manager",function(e){
		var export_type = $(this).data('type');
		var attendance_search = $("#attendance_user_search").serialize();

		// alert(users_report_form);

        event.preventDefault();
        var URL = "{{ route('attendance.getAttendanceReportManager') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				attendance_search:attendance_search
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;

            }
        });
        return false;
    });


    $(document).on("click",".export_break_list_manager",function(e){
		var export_type = $(this).data('type');
		var break_list_search = $("#break_list_search").serialize();

		// alert(users_report_form);

        event.preventDefault();
        var URL = "{{ route('breaklist.getBreakListReportManager') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				break_list_search:break_list_search
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;

            }
        });
        return false;
    });


    $(document).on("click",".export_daily_production_manager",function(e){
		var export_type = $(this).data('type');
		var production_report_form = $("#daily_production_report").serialize();

        event.preventDefault();
        var URL = "{{ route('report.getProductionDailyReportManager') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				export_type: export_type,
				production_report_form:production_report_form
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {

                location.href = data.url;
            }
        });
        return false;
    });


    $(document).on("click",".export_dail_production_admin",function(e){
		var export_type = $(this).data('type');
		var production_report_form = $("#daily_production_report").serialize();

        event.preventDefault();
        var URL = "{{ route('report.getProductionDailyReportAdmin') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				export_type: export_type,
				production_report_form:production_report_form
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {

                location.href = data.url;
            }
        });
        return false;
    });

    $(document).on("click",".export_production_month_wise",function(e){
		var export_type = $(this).data('type');
		var production_month = $("#prduction_report_month").serialize();

		// alert(users_report_form);

        event.preventDefault();
        var URL = "{{ route('report.getProductionMonthReport') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				production_month:production_month
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;

            }
        });
        return false;
});

$(document).on('click','.main_menu',function(){

    main_menu_id = $(this).attr('id');
    //$('#'+ main_menu_id +'').addClass('menu-item-active');


    $.ajaxSetup({
          headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
          });

          $.ajax({
            type: "POST",
            url: "{{url('permission/side_menu_list')}}",
            data: {main_menu_id:main_menu_id},

            success: function(res) {
                if(res.side_menu_list !=''){
                    $('.sub_menu_list').html(res.side_menu_list);
                }
            },
            error: function (jqXHR, exception) {

            }
        });



});

// var min = 29;
// var sec = 59;
// var timer;
// var timeon = 0;

/*$(document).ready(function () {
ActivateTimer();
});

function ActivateTimer() {

   if (!timeon) {

    timeon = 1;
    Timer();
   }
}

function Timer() {
  var _time = min + ":" + sec;
  document.getElementById("timer_count").innerHTML = _time;
  if (_time != "0:0") {
    if (sec == 0) {
      min = min - 1;
      sec = 59;
    } else {
      sec = sec - 1;
    }
    timer = setTimeout("Timer()", 1000);
  } else {
    window.location.href = "logout";
  }
}*/


// $(document).ready(function () {

//     Times()

// });

// function Times() {

// //var startTime = moment('17-08-2022 11:08:01', 'DD-MM-YYYY hh:mm:ss');
// //var endTime = moment().endOf('date');
// // alert(endTime);
// // var hoursDiff = endTime.diff(startTime, 'hours');
// // var minutesDiff = endTime.diff(startTime, 'minutes');
// // var secondsDiff = endTime.diff(startTime, 'seconds');

// // datetime = 'H:' + hoursDiff + ', M:' + minutesDiff + ', S:' + secondsDiff;

// // $("#timer_count").html(datetime);
// // timer = setTimeout("Times()", 1000);

// var time            = $("#login_timer").val();
// //var work_duration   = $("#work_duration").val();
// //var time_split = work_duration.split(':');

// //     var then = new Date(2022, 08, 17);
// //     alert(then);
// //     var minsDiff = Math.floor((then.getTime() - now.getTime()) / 1000 / 60);
// //     var hoursDiff = Math.floor(minsDiff / 60);
// //     minsDiff = minsDiff % 60;
// //    //alert(hoursDiff + 'h and ' + minsDiff + 'm');

// const today = new Date();
// const endDate = new Date(time);
// const days = parseInt((endDate - today) / (1000 * 60 * 60 * 24));
// const hours = parseInt(Math.abs(endDate - today) / (1000 * 60 * 60) % 24);

// const minutes = parseInt(Math.abs(endDate.getTime() - today.getTime()) / (1000 * 60) % 60);
// const seconds = parseInt(Math.abs(endDate.getTime() - today.getTime()) / (1000) % 60);
// if(hours<10){

//     tot_hours ='0' + hours;
// }else{
//     tot_hours = hours;
// }

// if(minutes<10){

//     tot_minutes ='0' + minutes;
//   }else{
//     tot_minutes = minutes;
//   }

// datetime = tot_hours + ':' + tot_minutes + ':' + seconds;
// $("#timer_count").html(datetime);
// timer = setTimeout("Times()", 1000);
// }


/*
* Developer : Nehru
* Purpose   : people overall report download
*Date       : 29-08-2022
*/

$(document).on("click",".export_people_report",function(e){
		var export_type = $(this).data('type');
		var users_report_form = $("#people_report").serialize();

		//alert(users_report_form);

        event.preventDefault();
        var URL = "{{ route('report.getPeopleReport') }}";
		$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
        $.ajax({
            url: URL,
            type: 'post',
            data:{
				export_type: export_type,
				users_report_form:users_report_form
				//org:org, practice:practice, is_export:is_export
			},
            success: function (data) {
                location.href = data.url;
            }
        });
        return false;
    });



</script>
 --}}
@stack('view.scripts')
