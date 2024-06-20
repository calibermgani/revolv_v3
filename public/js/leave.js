$( document ).ready(function() {   

   // disable_dropdown();
    $('#no_of_leave').val('1');
    
    
}); 



$(document).on("change","#to_date",function(e){   
   
    leave_Calculation();

});


$(document).on("change","#start_date",function(e){   
    
   
    leave_Calculation();

});

$(document).on("change","#start_day",function(e){   
    
    var start_day = $('#start_day').val();
    console.log(start_day);
    if(start_day==0){
        $('#start_end').removeAttr("disabled"); 
        $('#no_of_days').val('0.5');
        
    }else if(start_day==1){
        $('#start_end').attr("disabled", true);
        $('#start_end').prop('selectedIndex',0);
        $('#no_of_days').val(1);
    }

});


$(document).on("change","#start_day",function(e){   
    
    var start_day = $('#start_day').val();
    var actual_day;
    var no_of_days = dayCalculation();
    
    
    if(start_day==0){
       
        $('#start_end').removeAttr("disabled");
        
        actual_day = no_of_days - 0.5;

    }else if(start_day==1){
        $('#start_end').attr("disabled", true);
        $('#start_end').prop('selectedIndex',0);
       // $('#no_of_days').val(1);
       actual_day = no_of_days;
    }

    actual_day = parseFloat(actual_day).toFixed(1);
    $('#no_of_days').val(actual_day);
    $('#no_of_leave_text').html(actual_day);

});


$(document).on("change","#last_day",function(e){   
    
    var last_day = $('#last_day').val();
    var actual_day;
    var no_of_days = dayCalculation();
   
    if(last_day==0){

            
            
        $('#last_end').removeAttr("disabled"); 
       
        actual_day = no_of_days - 0.5;
        actual_day = parseFloat(actual_day).toFixed(1);
       
        
    }else if(last_day==1){
        $('#last_end').attr("disabled", true);
        $('#last_end').prop('selectedIndex',0);
        actual_day = no_of_days;
       
    }
    $('#no_of_days').val(actual_day);
    $('#no_of_leave_text').html(actual_day);

});


$(document).on("change","#last_end",function(e){  
    
    var no_of_days = dayCalculation();
    var last_end = $('#last_end').val();
    var start_day = $('#start_day').val();

    if(last_end=='First'){

        actual_day = no_of_days - 0.5;

    }else if(last_end=='Second'){

        if(start_day=='1' && last_end=='Second'){
            actual_day =  no_of_days;
        }
        if(start_day=='0' && last_end=='First'){
            actual_day =  no_of_days -1;
        }

        if(start_day=='0' &&  $('#start_end').val()=='First' && $('#start_end').val()=='First'){
            actual_day =  no_of_days -1;
        }
       
    }
    actual_day = parseFloat(actual_day).toFixed(1);
    $('#no_of_days').val(actual_day);
    $('#no_of_leave_text').html(actual_day);


});




function leave_Calculation() {

    var start= $("#start_date").datepicker("getDate");
    var end= $("#to_date").datepicker("getDate");
    days = (end- start) / (1000 * 60 * 60 * 24);
    var no_of_days = Math.round(days);
    no_of_days = no_of_days +1;
    

    if(no_of_days < 1 )
    {
        
       
        disable_dropdown();


    }

    //alert(no_of_days);
   


    if(no_of_days > 1 )   {

        $('#no_of_leave_text').html(no_of_days);
        $('#no_of_days').val(no_of_days);
        enable_dropdown();
    }
   
    //alert(no_of_days);

}

function disable_dropdown() {

    $('#start_end').attr("disabled", true);
    $('#last_day').attr("disabled", true);    
    $('#last_end').attr('disabled', true);
}

function enable_dropdown() {

    $('#start_end').removeAttr("disabled",false);
    $('#last_day').removeAttr("disabled",false);    
    $('#last_end').attr('disabled', true);
}


/**
 * Developer : Muhammed Gani
 * Puspose : Fetch the Leave Balance
 * Date : 13-06-2022
 */


function leave_indexData(emp_id, leave_type, leave_status) {

    

    var table = $("#leaveTable").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
		order: [[ 0, "desc" ]],
        ajax: {
            url:  baseUrl + "leave/adminIndexData",
            data: function (d) {
                console.log(d);
                d.emp_id = emp_id
                d.leave_type = leave_type;
                d.leave_status = leave_status;
                //d.status = $('#status').val();
            },
        },
        columns: [
            {
                data: "emp_id",
                name: "emp_id"
            },

            {
                data: "emp_name",
                name: "emp_name"
            },

            {
                data: "leave_type",
                name: "leave_type"
            },

            
            {
                data: "no_of_days",
                name: "no_of_days"
            },


            {
                data: "from_date",
                name: "from_date"
            },

            {
                data: "to_date",
                name: "to_date"
            },

            {
                data: "applied_date",
                name: "applied_date"
            },

            {
                data: "status",
                name: "status"
            },
            {
                data: "remarks",
                name: "remarks"
            },
            {
                data: "action",
                name: "action"
            },
            
        ]
    });

}

/**
 * Developer : MuhammedGani
 * Purpose : Apporved Leave
 * Date : 14-06-2022 
 
 */



function approvedLeave(leave_id) {


    const swalWithBootstrapButtons = Swal.mixin({
      customClass: {
        confirmButton: 'btn btn-success',
        cancelButton: 'btn btn-danger'
      },
      buttonsStyling: false
    })
    
    swalWithBootstrapButtons.fire({
      title: 'Are you sure?',
      text: "You approved this leave!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Approve it!',
      cancelButtonText: 'No, cancel!',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {



        var is_flag = approveRejectAjax(leave_id,status='Approved');
        
        swalWithBootstrapButtons.fire(
          'Approved!',
          'Leave has been approved.',
          'success'
        )
       

        location.reload();


      } else if (
        /* Read more about handling dismissals below */
        result.dismiss === Swal.DismissReason.cancel
      ) {
        swalWithBootstrapButtons.fire(
          'Cancelled',
          'Leave Approved Canceled :)',
          'error'
        )
      }
    });
    
    }







    /**
 * Developer : MuhammedGani
 * Purpose : Apporved Leave
 * Date : 14-06-2022 
 
 */


     function rejectLeave(leave_id) {


        const swalWithBootstrapButtons = Swal.mixin({
          customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
          },
          buttonsStyling: false
        })
        
        swalWithBootstrapButtons.fire({
          title: 'Are you sure?',
          text: "You reject this leave!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, Reject it!',
          cancelButtonText: 'No, cancel!',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
    
    
    
            var is_flag = approveRejectAjax(leave_id,status='Rejected');
            
            swalWithBootstrapButtons.fire(
              'Rejected!',
              'Leave has been rejected.',
              'success'
            )
            
    
            location.reload();
    
    
    
          } else if (
            /* Read more about handling dismissals below */
            result.dismiss === Swal.DismissReason.cancel
          ) {
            swalWithBootstrapButtons.fire(
              'Cancelled',
              'Leave reject Canceled :)',
              'error'
            )
          }
        });
        
        }


 /**
 * Developer : MuhammedGani
 * Purpose : Apporved Leave
 * Date : 14-06-2022 
 
 */

 function withdrawLeave(leave_id) {


    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: 'btn btn-success',
          cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
      })
      
      swalWithBootstrapButtons.fire({
        title: 'Are you sure?',
        text: "You Withdraw this leave!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Withdraw it!',
        cancelButtonText: 'No, cancel!',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
  
  
  
          var is_flag = approveRejectAjax(leave_id,status='Withdraw');
          
          swalWithBootstrapButtons.fire(
            'Withdraw!',
            'Leave has been withdraw.',
            'success'
          )
          
  
          location.reload();
  
  
  
        } else if (
          /* Read more about handling dismissals below */
          result.dismiss === Swal.DismissReason.cancel
        ) {
          swalWithBootstrapButtons.fire(
            'Cancelled',
            'Leave withdraw Canceled :)',
            'error'
          )
        }
      });

 }




    function approveRejectAjax(leave_id,status) {


        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            });
            $.ajax({
            url:baseUrl + "leave/approverejectLeave",
            type: 'POST',
            data:{
            leave_id:leave_id,
            status:status		
    
            },
            success: function (data) {
    
            return data;
    
    
            }
            });   

    }


    // $(document).on("click","#search_leave_report",function(e){   
   
    //     var leave_form_data = ($('#leave_report')).serialize();
    //     console.log('Form Data '+leave_form_data);  
    //    leave_indexData(leave_form_data);
    
    // });
    
    
 /**
 * Developer : MuhammedGani
 * Purpose : Leave Balance
 * Date : 14-06-2022 
 
 */
    
    $(document).on("click","#leave_balance_modal_id",function(e){  
        
        console.log('Leave balance modal open');
        $('#leave_balance_modal').modal('show');

        var search_id =  $('#search').val();
        console.log('Search ID : '+search_id);


        var table = $("#leave_balance_details").DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            pageLength: 50,
            order: [[ 0, "desc" ]],
            ajax: {
                url:  baseUrl + "leave/leaveBalance",
                data: function (d) {
                    console.log(d);
                    d.search_id = search_id
                   // d.submitted_date = submitted_date;
                    //d.status = $('#status').val();
                },
            },
            columns: [
                {
                    data: "leave_type",
                    name: "leave_type"
                },
                {
                    data: "total_leave",
                    name: "total_leave"
                },

                {
                    data: "taken_leave",
                    name: "taken_leave"
                },
    
               {
                    data: "balance_leave",
                    name: "balance_leave"
                },
                
                
            ]
        });
        
    
    });



    $(document).on("click",".close_leave_details",function(e){  

    $('#leave_balance_modal').modal('hide');

    });



$(document).on("change","#start_date",function(e){  

    dayCalculation();

    });


    function dayCalculation() {

        var start= $("#start_date").datepicker("getDate");
        var end= $("#to_date").datepicker("getDate");

        
            days = (end- start) / (1000 * 60 * 60 * 24);
            var no_of_days = Math.round(days);
            no_of_days = no_of_days +1;
            return no_of_days;

        
       
    }


    /** 
     * developer : nehru
     * pupose    : leave blance for the single emplployee
     * date      : 09-08-2022
     */

    //  $(document).on("click","#check",function(e){  

    //  alert('hii');
  
    //   });

     function emp_leave_bal(id,username) {
      $('#emp_leave_balance').DataTable().destroy();
      
      $('#emp_leave_popup_model').modal('show');
      $('#emp_name').text("Employee : "+username);
      $.fn.dataTable.ext.errMode = 'none';
  
      var table = $("#emp_leave_balance").DataTable({
          processing: true,
          serverSide: true,
          searching: false,
          lengthChange: false,
          pageLength: 10,
          order: [[ 0, "desc" ]],
          ajax: {
              url:  baseUrl + "leave/emp_leave_bal",
              data: function (d) {
                  d.id =id;
                  
  
              },
          },
          columns: [
            {
                data: "leave_type",
                name: "leave_type"
            },
            {
                data: "total_leave",
                name: "total_leave"
            },

            {
                data: "taken_leave",
                name: "taken_leave"
            },

           {
                data: "balance_leave",
                name: "balance_leave"
            },
            
            
        ]
      });

  
  
    }

    function CloseModalPopup() {       
      $("#emp_leave_popup_model").modal('hide');
}

$(document).on('click','#clear_leave_submit',function() {
  $(this).closest('form').find("#leave_status,#emp_id,#leave_type").val("");
  $(this).closest('form').find("#search_leave_report").trigger('click');
   $(this).closest('form').find("select").val("").trigger('change');

});


    
    
    



     

   
  
    