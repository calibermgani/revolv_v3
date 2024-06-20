/* ---------------------------------- Manage Admin User ------------------------------------------- */
/*                                                                                                  */
/* ---------------------------------- Manage Admin User ------------------------------------------- */


$(document).ready(function() {
    $('#loginForm').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            email: {
                message: 'Enter email',
                validators: {
                    notEmpty: {
                        message: 'Enter email'
                    },
                    stringLength: {
                        max: 40,
                        message: 'The email must be less than 40 characters'
                    }
                }
            },
			password: {
                validators: {
                    notEmpty: {
                        message: 'Enter password'
                    }
                }
            }
        }
    });
});





$(document).ready(function() {
    $('#adminUser').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            first_name: {
                message: 'Enter email',
                validators: {
                    notEmpty: {
                        message: 'Enter email'
                    },
                    stringLength: {
                        max: 40,
                        message: 'The email must be less than 40 characters'
                    }
                }
            },
			last_name: {
                message: 'Enter last name',
                validators: {
                    notEmpty: {
                        message: 'Enter last name'
                    },
                    stringLength: {
                        max: 40,
                        message: 'The last name must be less than 40 characters'
                    }
                }
            },
			phone: {
                validators: {
                    stringLength: {
                        min: 10,
                        message: 'phone number must be 10 characters'
                    }
                }
            },
			role_id: {
                validators: {
                    notEmpty: {
                        message: 'Select role'
                    }
                }
            },
			password: {
                validators: {
                    notEmpty: {
                        message: 'Enter password'
                    }
                }
            },
			email:{
                validators: {
					notEmpty: {
                        message: 'Enter email'
                    },
                    emailAddress: {
                        message: 'Enter valid email'
                    },
					remote: {
						message: 'Email ID already exist',
						url: assetBaseUrl +'/userEmailValidate',
						data: {
							'email':$('input[name="email"]'),
							'user_id':$('input[name="id"]').val(),
							'_token':$('input[name="_token"]').val()
							},
						type: 'POST'
					}
                }
            },
            image: {
                validators: {
                    file: {
                        extension: 'jpeg,png,jpg,JPEG,PNG,JPG',
                        type: 'image/jpeg,image/jpg,image/png,image/JPEG,image/JPG,image/PNG',
                        message: 'The selected file is not valid'
                    }
                }
            }
        }
    });
});



$(document).ready(function() {
    $('#adminEditUser').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            first_name: {
                message: 'Enter first name',
                validators: {
                    notEmpty: {
                        message: 'Enter first name'
                    },
                    stringLength: {
                        max: 40,
                        message: 'The first name must be less than 40 characters'
                    }
                }
            },
			last_name: {
                message: 'Enter last name',
                validators: {
                    notEmpty: {
                        message: 'Enter last name'
                    },
                    stringLength: {
                        max: 40,
                        message: 'The last name must be less than 40 characters'
                    }
                }
            },
			phone: {
                validators: {
                    stringLength: {
                        min: 10,
                        message: 'phone number must be 10 characters'
                    }
                }
            },
			role_id: {
                validators: {
                    notEmpty: {
                        message: 'Select role'
                    }
                }
            },
			email:{
                validators: {
					notEmpty: {
                        message: 'Enter email'
                    },
                    emailAddress: {
                        message: 'Enter valid email'
                    },
					remote: {
						message: 'Email ID already exist',
						url: assetBaseUrl +'/userEmailValidate',
						data: {
							'email':$('input[name="email"]'),
							'user_id':$('input[name="id"]').val(),
							'_token':$('input[name="_token"]').val()
							},
						type: 'POST'
					}
                }
            },
            image: {
                validators: {
                    file: {
                        extension: 'jpeg,png,jpg,JPEG,PNG,JPG',
                        type: 'image/jpeg,image/jpg,image/png,image/JPEG,image/JPG,image/PNG',
                        message: 'The selected file is not valid'
                    }
                }
            }
        }
    });
});


/* ---------------------------------- Manage Admin User ------------------------------------------- */
/*                                                                                                  */
/* ---------------------------------- Manage Admin User ------------------------------------------- */

$(document).ready(function() {
    $('#division').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            division_name: {
                message: 'Enter division name',
                validators: {
                    notEmpty: {
                        message: 'Enter division name'
                    },
                    stringLength: {
                        max: 40,
                        message: 'The division must be less than 40 characters'
                    }
                }
            }
        }
    });
});


$(document).ready(function() {
    $('#followForm').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            shop_id: {
                message: 'Select shop',
                validators: {
                    notEmpty: {
                        message: 'Select shop'
                    }
                }
            }
        }
    });
});

$(document).ready(function() {
    $('#area').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            division_id: {
                message: 'Select division',
                validators: {
                    notEmpty: {
                        message: 'Select division'
                    }
                }
            },
            area: {
                message: 'Enter area',
                validators: {
                    notEmpty: {
                        message: 'Enter area'
                    },
                    stringLength: {
                        max: 40,
                        message: 'The area must be less than 40 characters'
                    }
                }
            }
        }
    });
});

$(document).ready(function() {
    $('#productForm').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            product_type: {
                message: 'Select product',
                validators: {
                    notEmpty: {
                        message: 'Select product'
                    }
                }
            },
            product_name: {
                message: 'Enter product name',
                validators: {
                    notEmpty: {
                        message: 'Enter product name'
                    },
                    stringLength: {
                        max: 50,
                        message: 'The product name must be less than 50 characters'
                    }
                }
            },
            price: {
                message: 'Enter price',
                validators: {
                    notEmpty: {
                        message: 'Enter price'
                    }
                }
            },
            distributor_price: {
                message: 'Enter distributor price',
                validators: {
                    notEmpty: {
                        message: 'Enter distributor price'
                    }
                }
            },
            crate_quantity: {
                message: 'Enter crate quantity',
                validators: {
                    notEmpty: {
                        message: 'Enter crate quantity'
                    }
                }
            },
            image: {
                validators: {
                    file: {
                        extension: 'jpeg,png,jpg,JPEG,PNG,JPG',
                        type: 'image/jpeg,image/jpg,image/png,image/JPEG,image/JPG,image/PNG',
                        message: 'The selected file is not valid'
                    }
                }
            }
        }
    });
});


$(document).ready(function() {
    $('#shopForm').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            shop_name: {
                message: 'Enter shop name',
                validators: {
                    notEmpty: {
                        message: 'Enter shop name'
                    },
                    stringLength: {
                        max: 60,
                        message: 'The shop name must be less than 60 characters'
                    }
                }
            },
            'contact_name[]': {
                message: 'Enter contact name',
                validators: {
                    notEmpty: {
                        message: 'Enter contact name'
                    },
                    stringLength: {
                        max: 60,
                        message: 'The contact name must be less than 60 characters'
                    }
                }
            },
            'contact_number[]': {
                message: 'Enter contact name',
                validators: {
                    notEmpty: {
                        message: 'Enter contact name'
                    },
                    stringLength: {
                        max: 10,
                        message: 'The contact name must be less than 10 characters'
                    }
                }
            },
            'compertitor[]': {
                message: 'Select compertitor',
                validators: {
                    notEmpty: {
                        message: 'Select compertitor'
                    }
                }
            },
            'product_type[]': {
                message: 'Select product type',
                validators: {
                    notEmpty: {
                        message: 'Select product type'
                    }
                }
            },
            shop_type: {
                message: 'Select shop type',
                validators: {
                    notEmpty: {
                        message: 'Select shop type'
                    }
                }
            },
            address: {
                message: 'Enter address',
                validators: {
                    notEmpty: {
                        message: 'Enter address'
                    },
                    stringLength: {
                        max: 60,
                        message: 'The address must be less than 60 characters'
                    }
                }
            },
            city: {
                message: 'Enter city',
                validators: {
                    notEmpty: {
                        message: 'Enter city'
                    },
                    stringLength: {
                        max: 40,
                        message: 'The city must be less than 40 characters'
                    }
                }
            },
            state: {
                message: 'Enter state',
                validators: {
                    notEmpty: {
                        message: 'Enter state'
                    },
                    stringLength: {
                        max: 20,
                        message: 'The state must be less than 20 characters'
                    }
                }
            },
            country: {
                message: 'Enter country',
                validators: {
                    notEmpty: {
                        message: 'Enter country'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The country must be less than 30 characters'
                    }
                }
            },
            landmark: {
                message: 'Enter landmark',
                validators: {
                    notEmpty: {
                        message: 'Enter landmark'
                    },
                    stringLength: {
                        max: 60,
                        message: 'The landmark must be less than 60 characters'
                    }
                }
            },
            zipcode: {
                message: 'Enter zipcode',
                validators: {
                    notEmpty: {
                        message: 'Enter zipcode'
                    },
                    stringLength: {
                        max: 6,
                        message: 'The zipcode must be less than 6 characters'
                    }
                }
            },
            distributor_id: {
                message: 'Select distributor',
                validators: {
                    notEmpty: {
                        message: 'Select distributor'
                    }
                }
            },
            competitor_id: {
                message: 'Select competitor',
                validators: {
                    notEmpty: {
                        message: 'Select competitor'
                    }
                }
            },
            division_id: {
                message: 'Select division',
                validators: {
                    notEmpty: {
                        message: 'Select division'
                    }
                }
            },
            area_id: {
                message: 'Select area',
                validators: {
                    notEmpty: {
                        message: 'Select area'
                    }
                }
            },
            rso_id: {
                message: 'Select rso',
                validators: {
                    notEmpty: {
                        message: 'Select rso'
                    }
                }
            },
            image: {
                validators: {
                    file: {
                        extension: 'jpeg,png,jpg,JPEG,PNG,JPG',
                        type: 'image/jpeg,image/jpg,image/png,image/JPEG,image/JPG,image/PNG',
                        message: 'The selected file is not valid'
                    }
                }
            }
        }
    });
});

$(document).ready(function() {
    $('#enquiryForm').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            shop_name: {
                message: 'Enter shop name',
                validators: {
                    notEmpty: {
                        message: 'Enter shop name'
                    },
                    stringLength: {
                        max: 50,
                        message: 'The shop name must be less than 50 characters'
                    }
                }
            },
            'contact_name[]': {
                message: 'Enter contact name',
                validators: {
                    notEmpty: {
                        message: 'Enter contact name'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The contact name must be less than 30 characters'
                    }
                }
            },
            'contact_number[]': {
                message: 'Enter contact name',
                validators: {
                    notEmpty: {
                        message: 'Enter contact name'
                    },
                    stringLength: {
                        max: 10,
                        message: 'The contact name must be less than 10 characters'
                    }
                }
            },
            'compertitor[]': {
                message: 'Select compertitor',
                validators: {
                    notEmpty: {
                        message: 'Select compertitor'
                    }
                }
            },
            'product_type[]': {
                message: 'Select product type',
                validators: {
                    notEmpty: {
                        message: 'Select product type'
                    }
                }
            },
            shop_type: {
                message: 'Select shop type',
                validators: {
                    notEmpty: {
                        message: 'Select shop type'
                    }
                }
            },
            address: {
                message: 'Enter address',
                validators: {
                    notEmpty: {
                        message: 'Enter address'
                    },
                    stringLength: {
                        max: 60,
                        message: 'The address must be less than 60 characters'
                    }
                }
            },
            city: {
                message: 'Enter city',
                validators: {
                    notEmpty: {
                        message: 'Enter city'
                    },
                    stringLength: {
                        max: 40,
                        message: 'The city must be less than 40 characters'
                    }
                }
            },
            state: {
                message: 'Enter state',
                validators: {
                    notEmpty: {
                        message: 'Enter state'
                    },
                    stringLength: {
                        max: 20,
                        message: 'The state must be less than 20 characters'
                    }
                }
            },
            country: {
                message: 'Enter country',
                validators: {
                    notEmpty: {
                        message: 'Enter country'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The country must be less than 30 characters'
                    }
                }
            },
            landmark: {
                message: 'Enter landmark',
                validators: {
                    notEmpty: {
                        message: 'Enter landmark'
                    },
                    stringLength: {
                        max: 60,
                        message: 'The landmark must be less than 60 characters'
                    }
                }
            },
            zipcode: {
                message: 'Enter zipcode',
                validators: {
                    notEmpty: {
                        message: 'Enter zipcode'
                    },
                    stringLength: {
                        max: 6,
                        message: 'The zipcode must be less than 6 characters'
                    }
                }
            },
            /*distributor_id: {
                message: 'Select distributor',
                validators: {
                    notEmpty: {
                        message: 'Select distributor'
                    }
                }
            },*/
            competitor_id: {
                message: 'Select competitor',
                validators: {
                    notEmpty: {
                        message: 'Select competitor'
                    }
                }
            },
            division_id: {
                message: 'Select division',
                validators: {
                    notEmpty: {
                        message: 'Select division'
                    }
                }
            },
            area_id: {
                message: 'Select area',
                validators: {
                    notEmpty: {
                        message: 'Select area'
                    }
                }
            },
            rso_id: {
                message: 'Select rso',
                validators: {
                    notEmpty: {
                        message: 'Select rso'
                    }
                }
            },
            follow_date: {
                message: 'Select follow date',
                validators: {
                    notEmpty: {
                        message: 'Select follow date'
                    }
                }
            },
            image: {
                validators: {
                    file: {
                        extension: 'jpeg,png,jpg,JPEG,PNG,JPG',
                        type: 'image/jpeg,image/jpg,image/png,image/JPEG,image/JPG,image/PNG',
                        message: 'The selected file is not valid'
                    }
                }
            }
        }
    });
});



$(document).ready(function() {
    $('#complaint').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            product_type: {
                message: 'Select product type',
                validators: {
                    notEmpty: {
                        message: 'Select product type'
                    }
                }
            },
            area: {
                message: 'Enter area',
                validators: {
                    notEmpty: {
                        message: 'Enter area'
                    },
                    stringLength: {
                        max: 20,
                        message: 'The area must be less than 20 characters'
                    }
                }
            }
        }
    });
});



$(document).ready(function() {
    $('#distributorAddForm').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            name: {
                message: 'Enter name',
                validators: {
                    notEmpty: {
                        message: 'Enter name'
                    }
                }
            },
            password: {
                message: 'Enter password',
                validators: {
                    notEmpty: {
                        message: 'Enter password'
                    }
                }
            },
            phone: {
                message: 'Enter contact no',
                validators: {
                    notEmpty: {
                        message: 'Enter contact no'
                    },
                    stringLength: {
                        max: 10,
                        message: 'The  contact no must be less than 10 characters'
                    },
					remote: {
						message: 'Phone no already exist',
						url: assetBaseUrl +'/userPhoneValidate',
						data: {
							'phone':$('input[name="phone"]'),
							'user_id':$('input[name="id"]').val(),
							'_token':$('input[name="_token"]').val()
							},
						type: 'POST'
					}
                }
            },
            email: {
                message: 'Enter email',
                validators: {
                    notEmpty: {
                        message: 'Enter email'
                    },
                    stringLength: {
                        max: 60,
                        message: 'The email must be less than 60 characters'
                    },
                    emailAddress: {
                        message: 'Enter valid email'
                    },
					remote: {
						message: 'Email ID already exist',
						url: assetBaseUrl +'/userEmailValidate',
						data: {
							'email':$('input[name="email"]'),
							'user_id':$('input[name="id"]').val(),
							'_token':$('input[name="_token"]').val()
							},
						type: 'POST'
					}
                }
            },
            'division_id[]': {
                message: 'Select division',
                validators: {
                    notEmpty: {
                        message: 'Select division'
                    }
                }
            },
            'area_id[]': {
                message: 'Select area',
                validators: {
                    notEmpty: {
                        message: 'Select area'
                    }

                }
            },
            address: {
                message: 'Enter address',
                validators: {
                    notEmpty: {
                        message: 'Enter address'
                    },
                    stringLength: {
                        max: 80,
                        message: 'The address must be less than 80 characters'
                    }

                }
            },
            city: {
                message: 'Enter city',
                validators: {
                    notEmpty: {
                        message: 'Enter city'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The city must be less than 30 characters'
                    }

                }
            },
            state: {
                message: 'Enter state',
                validators: {
                    notEmpty: {
                        message: 'Enter state'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The state must be less than 30 characters'
                    }

                }
            },
            country: {
                message: 'Enter country',
                validators: {
                    notEmpty: {
                        message: 'Enter country'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The country must be less than 30 characters'
                    }

                }
            },
            zipcode: {
                message: 'Enter zipcode',
                validators: {
                    notEmpty: {
                        message: 'Enter zipcode'
                    },
                    stringLength: {
                        max: 6,
                        message: 'The zipcode must be less than 6 characters'
                    }

                }
            },
            image: {
                validators: {
                    file: {
                        extension: 'jpeg,png,jpg,JPEG,PNG,JPG',
                        type: 'image/jpeg,image/jpg,image/png,image/JPEG,image/JPG,image/PNG',
                        message: 'The selected file is not valid'
                    }
                }
            }
        }
    });
});

$(document).ready(function() {
    $('#distributorAddFormOnly').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            name: {
                message: 'Enter name',
                validators: {
                    notEmpty: {
                        message: 'Enter name'
                    }
                }
            },
            password: {
                message: 'Enter password',
                validators: {
                    notEmpty: {
                        message: 'Enter password'
                    }
                }
            },
            phone: {
                message: 'Enter contact no',
                validators: {
                    notEmpty: {
                        message: 'Enter contact no'
                    },
                    stringLength: {
                        max: 10,
                        message: 'The  contact no must be less than 10 characters'
                    },
					remote: {
						message: 'Phone no already exist',
						url: assetBaseUrl +'/userPhoneValidate',
						data: {
							'phone':$('input[name="phone"]'),
							'user_id':$('input[name="id"]').val(),
							'_token':$('input[name="_token"]').val()
							},
						type: 'POST'
					}
                }
            },
            email: {
                message: 'Enter email',
                validators: {
                    notEmpty: {
                        message: 'Enter email'
                    },
                    stringLength: {
                        max: 60,
                        message: 'The email must be less than 60 characters'
                    },
                    emailAddress: {
                        message: 'Enter valid email'
                    },
					remote: {
						message: 'Email ID already exist',
						url: assetBaseUrl +'/userEmailValidate',
						data: {
							'email':$('input[name="email"]'),
							'user_id':$('input[name="id"]').val(),
							'_token':$('input[name="_token"]').val()
							},
						type: 'POST'
					}
                }
            },
            division_id: {
                message: 'Select division',
                validators: {
                    notEmpty: {
                        message: 'Select division'
                    }
                }
            },
            area_id: {
                message: 'Select area',
                validators: {
                    notEmpty: {
                        message: 'Select area'
                    }

                }
            },
            address: {
                message: 'Enter address',
                validators: {
                    notEmpty: {
                        message: 'Enter address'
                    },
                    stringLength: {
                        max: 80,
                        message: 'The address must be less than 80 characters'
                    }

                }
            },
            city: {
                message: 'Enter city',
                validators: {
                    notEmpty: {
                        message: 'Enter city'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The city must be less than 30 characters'
                    }

                }
            },
            state: {
                message: 'Enter state',
                validators: {
                    notEmpty: {
                        message: 'Enter state'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The state must be less than 30 characters'
                    }

                }
            },
            country: {
                message: 'Enter country',
                validators: {
                    notEmpty: {
                        message: 'Enter country'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The country must be less than 30 characters'
                    }

                }
            },
            zipcode: {
                message: 'Enter zipcode',
                validators: {
                    notEmpty: {
                        message: 'Enter zipcode'
                    },
                    stringLength: {
                        max: 6,
                        message: 'The zipcode must be less than 6 characters'
                    }

                }
            },
            image: {
                validators: {
                    file: {
                        extension: 'jpeg,png,jpg,JPEG,PNG,JPG',
                        type: 'image/jpeg,image/jpg,image/png,image/JPEG,image/JPG,image/PNG',
                        message: 'The selected file is not valid'
                    }
                }
            }
        }
    });
});


$(document).ready(function() {
    $('#distributorEditFormOnly').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            name: {
                message: 'Enter name',
                validators: {
                    notEmpty: {
                        message: 'Enter name'
                    }
                }
            },
            phone: {
                message: 'Enter contact no',
                validators: {
                    notEmpty: {
                        message: 'Enter contact no'
                    },
                    stringLength: {
                        max: 10,
                        message: 'The  contact no must be less than 10 characters'
                    }
                }
            },
            email: {
                message: 'Enter email',
                validators: {
                    notEmpty: {
                        message: 'Enter email'
                    },
                    stringLength: {
                        max: 60,
                        message: 'The email must be less than 60 characters'
                    },
                    emailAddress: {
                        message: 'Enter valid email'
                    },
					remote: {
						message: 'Email ID already exist',
						url: assetBaseUrl +'/userEmailValidate',
						data: {
							'email':$('input[name="email"]'),
							'user_id':$('input[name="id"]').val(),
							'_token':$('input[name="_token"]').val()
							},
						type: 'POST'
					}
                }
            },
            division_id: {
                message: 'Select division',
                validators: {
                    notEmpty: {
                        message: 'Select division'
                    }
                }
            },
            area_id: {
                message: 'Select area',
                validators: {
                    notEmpty: {
                        message: 'Select area'
                    }

                }
            },
            address: {
                message: 'Enter address',
                validators: {
                    notEmpty: {
                        message: 'Enter address'
                    },
                    stringLength: {
                        max: 80,
                        message: 'The address must be less than 80 characters'
                    }

                }
            },
            city: {
                message: 'Enter city',
                validators: {
                    notEmpty: {
                        message: 'Enter city'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The city must be less than 30 characters'
                    }

                }
            },
            state: {
                message: 'Enter state',
                validators: {
                    notEmpty: {
                        message: 'Enter state'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The state must be less than 30 characters'
                    }

                }
            },
            country: {
                message: 'Enter country',
                validators: {
                    notEmpty: {
                        message: 'Enter country'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The country must be less than 30 characters'
                    }

                }
            },
            zipcode: {
                message: 'Enter zipcode',
                validators: {
                    notEmpty: {
                        message: 'Enter zipcode'
                    },
                    stringLength: {
                        max: 6,
                        message: 'The zipcode must be less than 6 characters'
                    }

                }
            }
        }
    });
});



$(document).ready(function() {
    $('#distributorEditForm').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            name: {
                message: 'Enter name',
                validators: {
                    notEmpty: {
                        message: 'Enter name'
                    }
                }
            },
            phone: {
                message: 'Enter contact no',
                validators: {
                    notEmpty: {
                        message: 'Enter contact no'
                    },
                    stringLength: {
                        max: 10,
                        message: 'The  contact no must be less than 10 characters'
                    }
                }
            },
            email: {
                message: 'Enter email',
                validators: {
                    notEmpty: {
                        message: 'Enter email'
                    },
                    stringLength: {
                        max: 60,
                        message: 'The email must be less than 60 characters'
                    },
                    emailAddress: {
                        message: 'Enter valid email'
                    },
					remote: {
						message: 'Email ID already exist',
						url: assetBaseUrl +'/userEmailValidate',
						data: {
							'email':$('input[name="email"]'),
							'user_id':$('input[name="id"]').val(),
							'_token':$('input[name="_token"]').val()
							},
						type: 'POST'
					}
                }
            },
            'division_id[]': {
                message: 'Select division',
                validators: {
                    notEmpty: {
                        message: 'Select division'
                    }
                }
            },
            'area_id[]': {
                message: 'Select area',
                validators: {
                    notEmpty: {
                        message: 'Select area'
                    }

                }
            },
            address: {
                message: 'Enter address',
                validators: {
                    notEmpty: {
                        message: 'Enter address'
                    },
                    stringLength: {
                        max: 80,
                        message: 'The address must be less than 80 characters'
                    }

                }
            },
            city: {
                message: 'Enter city',
                validators: {
                    notEmpty: {
                        message: 'Enter city'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The city must be less than 30 characters'
                    }

                }
            },
            state: {
                message: 'Enter state',
                validators: {
                    notEmpty: {
                        message: 'Enter state'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The state must be less than 30 characters'
                    }

                }
            },
            country: {
                message: 'Enter country',
                validators: {
                    notEmpty: {
                        message: 'Enter country'
                    },
                    stringLength: {
                        max: 30,
                        message: 'The country must be less than 30 characters'
                    }

                }
            },
            zipcode: {
                message: 'Enter zipcode',
                validators: {
                    notEmpty: {
                        message: 'Enter zipcode'
                    },
                    stringLength: {
                        max: 6,
                        message: 'The zipcode must be less than 6 characters'
                    }

                }
            }
        }
    });
});



$(document).ready(function() {
    $('#competitorForm').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            competitor: {
                message: 'Enter competitor name',
                validators: {
                    notEmpty: {
                        message: 'Enter competitor name'
                    }
                }
            }
        }
    });
});

$(document).ready(function() {
    $('#feedbackForm').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            shop_id: {
                message: 'Select shop',
                validators: {
                    notEmpty: {
                        message: 'Select shop'
                    }
                }
            },
            product_type: {
                message: 'Select product type',
                validators: {
                    notEmpty: {
                        message: 'Select product type'
                    }
                }
            }
        }
    });
});




$(document).ready(function() {
    $('#attendanceForm').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: '',
            invalid: '',
            validating: ''
        },
        fields: {
            user_id: {
                message: 'Select user',
                validators: {
                    notEmpty: {
                        message: 'Select user'
                    }
                }
            },
            date: {
                message: 'Select date',
                validators: {
                    notEmpty: {
                        message: 'Select date'
                    }
                }
            },
            time_slot: {
                message: 'Select time slot',
                validators: {
                    notEmpty: {
                        message: 'Select time slot'
                    }
                }
            },
            time: {
                message: 'Select time',
                validators: {
                    notEmpty: {
                        message: 'Select time'
                    }
                }
            }
        }
    });
});