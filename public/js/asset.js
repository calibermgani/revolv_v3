

function assetIndexData(asset_id) {

    

        //$.fn.dataTable.ext.errMode = 'none';
        var table = $("#asset_index_table").DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            pageLength: 10,
            order: [[ 0, "desc" ]],
            ajax: {
                url:  baseUrl + "assets/indexDataAsset",
                data: function (d) {
                    d.asset_id =asset_id;
                   // d.user_id =user_id;

                },
            },
            columns: [

                {
                    data: "assets_code",
                    name: "assets_code",
                },

                {
                    data: "assets_name",
                    name: "assets_name",
                },
                {
                    data: "unit",
                    name: "unit",
                },
                {
                    data: "allocate_unit",
                    name: "allocate_unit",
                },

                {
                    data: "date_buy",
                    name: "date_buy",
                },
                {
                    data: "warranty_period",
                    name: "warranty_period",
                },
                {
                    data: "unit_price",
                    name: "unit_price",
                },
                {
                    data: "depreciation",
                    name: "depreciation",
                },
                {
                    data: "action",
                    name: "action",
                },

            ]
        });
}


/** Asset Allocation JS Start */




$(document).on("click",".allocation",function(e){    


    $("#asset_code_label").html($(this).data("id"));
    $("#asset_name_label").html($(this).data("name"));
    $('#asset_code').val($(this).data("assetid"));
    
    $('#asset_allocation_modal').modal('show');

});



$(document).on("click","#asset_allot_save",function(e){

    var users_report_form = $("#asset_allocation_user").serialize();

    event.preventDefault();
    
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    $.ajax({
        url:  baseUrl + "assets/store_allocation",
        type: 'post',
        data:{            
            users_report_form:users_report_form            
        },
        success: function (data) {
            

            if(data.success){

                js_notification('success','Record Added Successfully');
                location.reload();
                
            }else if(data.qty_error=='1'){
              
               // js_notification('error','InSufficient Balance');
                
               // $('#asset_allocation_modal').modal('hide');
                $('#error_msg').html('Error Msg : InSufficient Balance');

            }
            else {
                jQuery.each(data.errors, function(key, value){
                    jQuery('.alert-danger').show();
                    jQuery('.alert-danger').append('<p>'+value+'</p>');
                });

            }

             
            
            
        }
    });
    return false;


});

$(document).on("click",".asset_allocation_close",function(e){
    $('#asset_allocation_modal').modal('hide');

});



/** Asset Allocation JS End */





/** Assest Revoke Script Start */

$(document).on("click",".revoke",function(e){    


    $("#asset_rcode_label").html($(this).data("id"));
    $("#asset_rname_label").html($(this).data("name"));
    $('#asset_rcode').val($(this).data("assetid"));


    var identifer_value = get_identifier($(this).data("assetid"));


    


    
    $('#asset_revoke_modal').modal('show');

});



$(document).on("click","#asset_revoke_save",function(e){

    var revoke_form = $("#asset_revoke_user").serialize();

    event.preventDefault();
    
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    $.ajax({
        url:  baseUrl + "assets/store_revoke",
        type: 'post',
        data:{            
            revoke_form:revoke_form            
        },
        success: function (data) {
            
            console.log(data.qty_error);

            if(data.success){

                js_notification('success','Record Added Successfully');
                location.reload();
                
            }else if(data.qty_error===1){
                
                 $('#error_msg_revoke').html('Error : You can not revoke exceed allocation quantity');
 
             }
            
            else {
                jQuery.each(data.errors, function(key, value){
                    jQuery('.alert-danger').show();
                    jQuery('.alert-danger').append('<p>'+value+'</p>');
                });

            }

             
            
            
        }
    });
    return false;


});


$(document).on("click",".asset_revoke_close",function(e){
    $('#asset_revoke_modal').modal('hide');

});




/** Asset Revoke Script End */

/** Asset Report Lost JS Start */

$(document).on("click",".reportlost",function(e){    


    $("#asset_repcode_label").html($(this).data("id"));
    $("#asset_repname_label").html($(this).data("name"));
    $('#asset_repcode').val($(this).data("assetid"));
    
    $('#asset_reportlost_modal').modal('show');

});



$(document).on("click","#asset_report_lost_save",function(e){

    var report_lost_form = $("#asset_reportlost").serialize();

    event.preventDefault();
    
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    $.ajax({
        url:  baseUrl + "assets/store_report_lost",
        type: 'post',
        data:{            
            report_lost_form:report_lost_form            
        },
        success: function (data) {

            if(data.success){

                js_notification('success','Record Added Successfully');
                location.reload();
                
            }else {
                jQuery.each(data.errors, function(key, value){
                    jQuery('.alert-danger').show();
                    jQuery('.alert-danger').append('<p>'+value+'</p>');
                });

            }

             
            
            
        }
    });
    return false;


});


$(document).on("click",".asset_report_lost_close",function(e){
    $('#asset_reportlost_modal').modal('hide');

});


/** Asset Report Lost JS End */

/* Asset Report Broken JS Start */


$(document).on("click",".reportbroken",function(e){    


    $("#asset_repbcode_label").html($(this).data("id"));
    $("#asset_repbname_label").html($(this).data("name"));
    $('#asset_repbcode').val($(this).data("assetid"));
    
    $('#asset_report_borken_modal').modal('show');

});



$(document).on("click","#asset_report_brokent_save",function(e){

    var asset_broken_form = $("#asset_report_borken").serialize();

    event.preventDefault();
    
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    $.ajax({
        url:  baseUrl + "assets/store_report_broken",
        type: 'post',
        data:{            
            asset_broken_form:asset_broken_form            
        },
        success: function (data) {

            if(data.success){

                js_notification('success','Record Added Successfully');
                location.reload();
                
            }else {
                jQuery.each(data.errors, function(key, value){
                    jQuery('.alert-danger').show();
                    jQuery('.alert-danger').append('<p>'+value+'</p>');
                });

            }

             
            
            
        }
    });
    return false;


});


$(document).on("click",".asset_report_broken_close",function(e){
    $('#asset_report_borken_modal').modal('hide');

});


/* Asset Report Broken JS End */


/* Asset Liquitation JS Start */


$(document).on("click",".liquidation",function(e){    

    
    $("#asset_liqcode_label").html($(this).data("id"));
    $("#asset_liqname_label").html($(this).data("name"));
    $('#asset_liqbcode').val($(this).data("assetid"));
    
    $('#asset_liquidation_modal').modal('show');

});




$(document).on("click","#asset_liquidation_save",function(e){

    var liquidation_form = $("#asset_liquidation").serialize();

    event.preventDefault();
    
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    $.ajax({
        url:  baseUrl + "assets/store_liquidation",
        type: 'post',
        data:{            
            liquidation_form:liquidation_form            
        },
        success: function (data) {

            if(data.success){

                js_notification('success','Record Added Successfully');
                location.reload();
                
            }else {
                jQuery.each(data.errors, function(key, value){
                    jQuery('.alert-danger').show();
                    jQuery('.alert-danger').append('<p>'+value+'</p>');
                });

            }

             
            
            
        }
    });
    return false;


});


$(document).on("click",".asset_liquidation_close",function(e){
    $('#asset_liquidation_modal').modal('hide');

});


/* Asset Liquitation JS End */


/* Asset Warranty JS Start */


$(document).on("click",".warranty",function(e){    

    
    $("#asset_wcode_label").html($(this).data("id"));
    $("#asset_wname_label").html($(this).data("name"));
    $('#asset_wcode').val($(this).data("assetid"));
    
    $('#asset_warranty_modal').modal('show');

});



$(document).on("click","#asset_warranty_save",function(e){

    var warranty_form = $("#asset_warranty").serialize();

    event.preventDefault();
    
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    $.ajax({
        url:  baseUrl + "assets/store_warranty",
        type: 'post',
        data:{            
            warranty_form:warranty_form            
        },
        success: function (data) {

            if(data.success){

                js_notification('success','Record Added Successfully');
                location.reload();
                
            }else {
                jQuery.each(data.errors, function(key, value){
                    jQuery('.alert-danger').show();
                    jQuery('.alert-danger').append('<p>'+value+'</p>');
                });

            }

             
            
            
        }
    });
    return false;


});



$(document).on("click",".asset_warranty_close",function(e){
    $('#asset_warranty_modal').modal('hide');

});

/* Asset Warranty JS End */

/** Asset Allocation is exist checking */


function isExisting_check() {


    $( "#identifer").autocomplete({
        source: function( request, response ) {
          $.ajax({
            url:  baseUrl + "users/autoSearchEmployee",
            type: 'GET',
            dataType: "json",
            data: {
              search_employee: request.term
            },
            success: function( data ) {
               response( data );
            }
          });
        },
        select: function (event, ui) {

           $('#search_employee').val(ui.item.label);
           $('#user_id').val(ui.item.id);
           console.log(ui.item);
           return false;
        }
      });

}


/** Purpose : Get Asset identifier 
 *  Developer : Muhammed Gani
 *  Date : 21-07-2022
*/

function get_identifier(asset_rcode) {

    
    var identifier_data;


    event.preventDefault();
    
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
    $.ajax({
        url:  baseUrl + "assets/get_identifier",
        type: 'post',
        data:{            
            asset_rcode:asset_rcode            
        },
        success: function (data) {

          return data;
             
            
            
        }
    });

   
}

