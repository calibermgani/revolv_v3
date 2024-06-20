"use strict";

// Class definition
var KTWizard4 = function () {
	// Base elements
	var _wizardEl;
	var _formEl;
	var _wizard;
	var _validations = [];

	// Private functions
	var initWizard = function () {
		// Initialize form wizard
		_wizard = new KTWizard(_wizardEl, {
			startStep: 1, // initial active step number
			clickableSteps: true  // allow step clicking
		});

		// Validation before going to next page
		_wizard.on('beforeNext', function (wizard) {
			_validations[wizard.getStep() - 1].validate().then(function (status) {
				if (status == 'Valid') {
					_wizard.goNext();
					KTUtil.scrollTop();
				} else {
					Swal.fire({
						text: "Sorry, Few Mandatory fileds are not filled. Fill those fields to move to next section",
						icon: "error",
						buttonsStyling: false,
						confirmButtonText: "Ok, got it!",
						customClass: {
							confirmButton: "btn font-weight-bold btn-light"
						}
					}).then(function () {
						KTUtil.scrollTop();
					});
				}
			});

			_wizard.stop();  // Don't go to the next step
		});

		// Change event
		_wizard.on('change', function (wizard) {
			KTUtil.scrollTop();
		});
	}

	var initValidation = function () {
		// Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
		// Step 1
		_validations.push(FormValidation.formValidation(
			_formEl,
			{
				// fields: {
				// 	marital_status: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Marital Status is required'
				// 			}
				// 		}
				// 	},
				// 	gender: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Gender is required'
				// 			}
				// 		}
				// 	},
				// 	father_spouse_name: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Father/Spouse Name is required'
				// 			}
				// 		}
				// 	},
				// 	emergency_contact_person: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Emergency Contact Person is required'
				// 			}
				// 		}
				// 	},
				// 	emergency_phone: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Phone is required'
				// 			}
				// 		}
				// 	},
				// 	phone: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Phone is required'
				// 			}
				// 		}
				// 	},
				// 	emp_id: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Emp ID is required'
				// 			}
				// 		}
				// 	},
				// 	Permanent_address_1: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Address is required'
				// 			}
				// 		}
				// 	},
				// 	Permanent_city: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'City is required'
				// 			}
				// 		}
				// 	},
				// 	Permanent_state: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'State is required'
				// 			}
				// 		}
				// 	},
				// 	Permanent_pincode: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Pincode is required'
				// 			}
				// 		}
				// 	},
				// 	address_1: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Address is required'
				// 			}
				// 		}
				// 	},
				// 	city: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'City is required'
				// 			}
				// 		}
				// 	},
				// 	state: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'State is required'
				// 			}
				// 		}
				// 	},
				// 	pincode: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Pincode is required'
				// 			}
				// 		}
				// 	},
				// 	emp_name:{
				// 		validators:{
				// 			notEmpty:{
				// 				message: 'Employee Name is required'
				// 			}
				// 		}
				// 	},
					
				// 	email: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Email is required'
				// 			},
				// 			emailAddress: {
				// 				message: 'The value is not a valid email address'
				// 			}
				// 		}
				// 	},
				// },
				plugins: {
					trigger: new FormValidation.plugins.Trigger(),
					bootstrap: new FormValidation.plugins.Bootstrap()
				}
			}
		));

		// Step 2
		_validations.push(FormValidation.formValidation(
			_formEl,
			{
				// fields: {
				// 	highest_educational_qualification: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Field is required'
				// 			}
				// 		}
				// 	},
				// 	institution_last_attended: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Field is required'
				// 			}
				// 		}
				// 	},
				// 	overall_experience: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Field is required'
				// 			}
				// 		}
				// 	},
				// 	healthcare_experience: {
				// 		validators: {
				// 			notEmpty: {
				// 				message: 'Field is required'
				// 			}
				// 		}
				// 	}
				// },
				plugins: {
					trigger: new FormValidation.plugins.Trigger(),
					bootstrap: new FormValidation.plugins.Bootstrap()
				}
			}
		));

		// Step 3
		_validations.push(FormValidation.formValidation(
			_formEl,
			{
				fields: {
					ccname: {
						validators: {
							notEmpty: {
								message: 'Credit card name is required'
							}
						}
					},
					ccnumber: {
						validators: {
							notEmpty: {
								message: 'Credit card number is required'
							},
							creditCard: {
								message: 'The credit card number is not valid'
							}
						}
					},
					ccmonth: {
						validators: {
							notEmpty: {
								message: 'Credit card month is required'
							}
						}
					},
					ccyear: {
						validators: {
							notEmpty: {
								message: 'Credit card year is required'
							}
						}
					},
					cccvv: {
						validators: {
							notEmpty: {
								message: 'Credit card CVV is required'
							},
							digits: {
								message: 'The CVV value is not valid. Only numbers is allowed'
							}
						}
					}
				},
				plugins: {
					trigger: new FormValidation.plugins.Trigger(),
					bootstrap: new FormValidation.plugins.Bootstrap()
				}
			}
		));
	}

	return {
		// public functions
		init: function () {
			_wizardEl = KTUtil.getById('kt_wizard_v4');
			_formEl = KTUtil.getById('kt_form');

			initWizard();
			initValidation();
		}
	};
}();

jQuery(document).ready(function () {
	KTWizard4.init();
});
