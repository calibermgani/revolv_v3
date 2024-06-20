function js_notification(type,msg){
	if(type == 'error'){
		toastr.error(msg, "", {
				showMethod: "slideDown",
				hideMethod: "slideUp",
				progressBar: !0,
				timeOut: 2e3
		});
	} else if(type == 'success'){
		toastr.success(msg, "", {
				showMethod: "slideDown",
				hideMethod: "slideUp",
				progressBar: !0,
				timeOut: 2e3
		});
	}
	
}

$(document).on('keypress','.js-number',function(evt){
	var charCode = (evt.which) ? evt.which : event.keyCode
	 if (charCode > 31 && (charCode < 48 || charCode > 57))
		return false;

	 return true;
});

$(document).on('change','.js-change-division',function(){
	$('#area_id').find('option').remove();
	var url = assetBaseUrl + "admin/manage-division/getArea";
	var division_id = $(this).val();
	var token = $('meta[name=csrf-token]').attr("content");
	var options = '<option value="">--Select--</option>';
	$.ajax({
		url: url,
		method: "post",
		data: {'division_id':division_id,'_token':token},
		success: function(result){
			$.each(result, function(key, value){
				options += '<option value="'+key+'">'+value+'</option>';
			});
			$('#area_id').append(options).trigger('change');
		}
	})
})

$(document).on('change','.js-change-division-multiple',function(){
	$('#area_id').find('option').remove();
	var url = assetBaseUrl + "admin/manage-division/getMultipleArea";
	var division_id = $(this).val();
	var token = $('meta[name=csrf-token]').attr("content");
	var options = '<option value="">--Select--</option>';
	$.ajax({
		url: url,
		method: "post",
		data: {'division_id':division_id,'_token':token},
		success: function(result){
			$.each(result, function(key, value){
				options += '<option value="'+key+'">'+value+'</option>';
			});
			$('#area_id').append(options).trigger('change');
		}
	})
})


$(document).on('change','.js-change-area',function(){
	$('#rso_id').find('option').remove();
	var url = assetBaseUrl + "admin/manage-division/getRSO";
	var area_id = $(this).val();
	var token = $('meta[name=csrf-token]').attr("content");
	var options = '<option value="">--Select--</option>';
	$.ajax({
		url: url,
		method: "post",
		data: {'area_id':area_id,'_token':token},
		success: function(result){
			$.each(result, function(key, value){
				options += '<option value="'+key+'">'+value+'</option>';
			});
			$('#rso_id').append(options).trigger('change');
		}
	})
})


$(document).ready(function() {
    $('.js-single-select').select2({placeholder: "Select"});
	$('.js-multiple-select').select2({placeholder: "Select"});
});

$(document).ready(function(){
	$('.price').mask('099.99');
  });

$(document).on('click','.js-shop-map',function(){
	var shop_id = $(this).attr('data-shop-id');
	var url = assetBaseUrl + "admin/manage-shop/shopMap";
	var token = $('meta[name=csrf-token]').attr("content");
	$.ajax({
		url: url,
		method: "post",
		data: {'shop_id':shop_id,'_token':token},
		success: function(result){
			$('#shopMap').html(result);
			$('#shopMap').modal('show');
		}
	})
})

  

$(function(){
   	
	var table = $('#followupTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-shop/followup/followupData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'shop.shop_name',
				name: 'shop.shop_name'
			},
			{
				data: 'followup_date',
				name: 'followup_date'
			},
			{
				data: 'shop.division.division_name',
				name: 'shop.division.division_name'
			},
			{
				data: 'shop.area.area',
				name: 'shop.area.area'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#followupSiteTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'manage-shop/followup/followupData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'shop.shop_name',
				name: 'shop.shop_name'
			},
			{
				data: 'followup_date',
				name: 'followup_date'
			},
			{
				data: 'shop.division.division_name',
				name: 'shop.division.division_name'
			},
			{
				data: 'shop.area.area',
				name: 'shop.area.area'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});

$(function(){
   	
	var table = $('#divisionTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-division/division/indexData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'division_name',
				name: 'division_name'
			},
			{
				data: 'status',
				name: 'status'
			},
			{
				data: 'user.name',
				name: 'user.name'
			},
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});



$(function(){
   	
	var table = $('#areaTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-division/area/areaData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'division.division_name',
				name: 'division.division_name'
			},
			{
				data: 'area',
				name: 'area'
			},
			{
				data: 'status',
				name: 'status'
			},
			{
				data: 'user.name',
				name: 'user.name'
			},
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});

$(function(){
   	
	var table = $('#productTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-product/product/productData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'product_types.product_type',
				name: 'product_types.product_type'
			},
			{
				data: 'product_name',
				name: 'product_name'
			},
			{
				data: 'price',
				name: 'price'
			},
			{
				data: 'distributor_price',
				name: 'distributor_price'
			},
			{
				data: 'status',
				name: 'status'
			},
			{
				data: 'user.name',
				name: 'user.name'
			},
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});

$(function(){
   	
	var table = $('#complaintTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-shop/complaint/complaintData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'complaint_date',
				name: 'complaint_date'
			},
			{
				data: 'shop.shop_name',
				name: 'shop.shop_name'
			},
			{
				data: 'complaint_type',
				name: 'complaint_type'
			},
			{
				data: 'status',
				name: 'status'
			},
			
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#complaintSiteTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'manage-shop/complaint/complaintData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'complaint_date',
				name: 'complaint_date'
			},
			{
				data: 'shop.shop_name',
				name: 'shop.shop_name'
			},
			{
				data: 'complaint_type',
				name: 'complaint_type'
			},
			{
				data: 'status',
				name: 'status'
			},
			
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});



$(function(){
   	
	var table = $('#feedbackTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-shop/feedback/feedbackData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			
			{
				data: 'shop.shop_name',
				name: 'shop.shop_name'
			},
			{
				data: 'product_type',
				name: 'product_type'
			},
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#feedbackSiteTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'manage-shop/feedback/feedbackData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			
			{
				data: 'shop.shop_name',
				name: 'shop.shop_name'
			},
			{
				data: 'product_type',
				name: 'product_type'
			},
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#shopTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-shop/shop/shopData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'shop_name',
				name: 'shop_name'
			},
			{
				data: 'shop_types.shop_type',
				name: 'shop_types.shop_type'
			},
			{
				data: 'distributor.name',
				name: 'distributor.name'
			},
			{
				data: 'division.division_name',
				name: 'division.division_name'
			},
			{
				data: 'area.area',
				name: 'area.area'
			},
			{
				data: 'rso.name',
				name: 'rso.name'
			},
			{
				data: 'shop_conformed_date',
				name: 'shop_conformed_date'
			},
			
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#shopSiteTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'manage-shop/shop/shopData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'shop_name',
				name: 'shop_name'
			},
			
			{
				data: 'shop_types.shop_type',
				name: 'shop_types.shop_type'
			},
			{
				data: 'distributor.name',
				name: 'distributor.name'
			},
			{
				data: 'division.division_name',
				name: 'division.division_name'
			},
			{
				data: 'area.area',
				name: 'area.area'
			},
			{
				data: 'rso.name',
				name: 'rso.name'
			},
			{
				data: 'shop_conformed_date',
				name: 'shop_conformed_date'
			},
			
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#enquiryTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-shop/enquiry/enquiryData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'shop_name',
				name: 'shop_name'
			},
			
			{
				data: 'shop_types.shop_type',
				name: 'shop_types.shop_type'
			},
			{
				data: 'distributor.name',
				name: 'distributor.name'
			},
			{
				data: 'division.division_name',
				name: 'division.division_name'
			},
			{
				data: 'area.area',
				name: 'area.area'
			},
			{
				data: 'rso.name',
				name: 'rso.name'
			},
			{
				data: 'follow_date',
				name: 'follow_date'
			},
			
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#enquirySiteTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-shop/enquiry/enquiryData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'shop_name',
				name: 'shop_name'
			},
			
			{
				data: 'shop_types.shop_type',
				name: 'shop_types.shop_type'
			},
			{
				data: 'distributor.name',
				name: 'distributor.name'
			},
			{
				data: 'division.division_name',
				name: 'division.division_name'
			},
			{
				data: 'area.area',
				name: 'area.area'
			},
			{
				data: 'rso.name',
				name: 'rso.name'
			},
			{
				data: 'follow_date',
				name: 'follow_date'
			},
			
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#distributorTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-shop/distributor/distributorData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			
			{
				data: 'name',
				name: 'name'
			},
			{
				data: 'phone',
				name: 'phone'
			},
			{
				data: 'division.division_name',
				name: 'division.division_name'
			},
			{
				data: 'area.area',
				name: 'area.area'
			},
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#distributorSiteTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'manage-shop/distributor/distributorData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			
			{
				data: 'name',
				name: 'name'
			},
			{
				data: 'phone',
				name: 'phone'
			},
			{
				data: 'division.division_name',
				name: 'division.division_name'
			},
			{
				data: 'area.area',
				name: 'area.area'
			},
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});



$(function(){
   	
	var table = $('#competitorTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-shop/competitor/competitorData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			
			{
				data: 'competitor',
				name: 'competitor'
			},
			{
				data: 'status',
				name: 'status'
			},
			{
				data: 'user.name',
				name: 'user.name'
			},
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});



$(function(){
   	
	var table = $('#asoTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-user/aso/asoData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			
			{
				data: 'name',
				name: 'name'
			},
			{
				data: 'email',
				name: 'email'
			},
			{
				data: 'phone',
				name: 'phone'
			},
			
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});

$(function(){
   	
	var table = $('#asoSiteTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'manage-user/aso/asoData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			
			{
				data: 'name',
				name: 'name'
			},
			{
				data: 'email',
				name: 'email'
			},
			{
				data: 'phone',
				name: 'phone'
			},
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#asmTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-user/asm/asmData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			
			{
				data: 'name',
				name: 'name'
			},
			{
				data: 'email',
				name: 'email'
			},
			{
				data: 'phone',
				name: 'phone'
			},
			
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#rsoTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-user/rso/rsoData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			
			{
				data: 'name',
				name: 'name'
			},
			{
				data: 'email',
				name: 'email'
			},
			{
				data: 'phone',
				name: 'phone'
			},
			
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#rsoSiteTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'manage-user/rso/rsoData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			
			{
				data: 'name',
				name: 'name'
			},
			{
				data: 'email',
				name: 'email'
			},
			{
				data: 'phone',
				name: 'phone'
			},
			{
				data: 'created_at',
				name: 'created_at'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});


$(function(){
   	
	var table = $('#attendanceTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'admin/manage-user/attendance/attendanceData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'date',
				name: 'date'
			},
			{
				data: 'user.name',
				name: 'user.name'
			},
			{
				data: 'user.user_type',
				name: 'user.user_type'
			},
			{
				data: 'first_half_login',
				name: 'first_half_login'
			},
			{
				data: 'first_half_logout',
				name: 'first_half_logout'
			},
			{
				data: 'second_half_login',
				name: 'second_half_login'
			},
			{
				data: 'second_half_logout',
				name: 'second_half_logout'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});



$(function(){
   	
	var table = $('#attendanceSiteTable').DataTable({
		processing: true,
		serverSide: true,
		searching: false, 
		lengthChange: false,
		pageLength: 50,
		ajax:{
			url: assetBaseUrl + 'manage-user/attendance/attendanceData',
			data: function (d) {
				d.test = 'testse'
			}
		},
		columns:[
			{
				data: 'date',
				name: 'date'
			},
			{
				data: 'user.name',
				name: 'user.name'
			},
			{
				data: 'user.user_type',
				name: 'user.user_type'
			},
			{
				data: 'first_half_login',
				name: 'first_half_login'
			},
			{
				data: 'first_half_logout',
				name: 'first_half_logout'
			},
			{
				data: 'second_half_login',
				name: 'second_half_login'
			},
			{
				data: 'second_half_logout',
				name: 'second_half_logout'
			},
			{
				data: 'action',
				name: 'action'
			}
		]
	})

	$(document).on('click','.js-filter-listing',function(){
		table.ajax.reload();
	})
});