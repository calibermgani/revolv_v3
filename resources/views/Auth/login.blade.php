@extends('layouts.app')
@section('content')
    {{-- <form  id="loginForm kt_login_signin_form" action="" class="form pt-3 loginForm"> --}}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="form-group ml-8 mr-8">
        <label for="exampleInputEmail" class="login-username">{{ __('Employee ID') }}</label>
        <div class="input-group">


            <input id="emp_id" type="text" placeholder="Username"
                class="form-control white-smoke h-auto py-5 px-6 @error('emp_id') is-invalid @enderror" name="emp_id"
                value="{{ old('emp_id') }}" required autocomplete="emp_id" autofocus>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>


    <div class="form-group ml-8 mr-8">
        <label for="exampleInputPassword" class="login-username">{{ __('Password') }}</label>
        <div class="input-group">
            <input id="password" type="password" placeholder="Password"
                class="form-control white-smoke h-auto py-5 px-6 @error('password') is-invalid @enderror"
                name="password" required autocomplete="current-password">
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        {{-- <input type="text" name="g-recaptcha-response" id="g-recaptcha-response"> --}}
    </div>
    <input type="hidden" id="pro_code_url" value={{config("constants.PRO_CODE_URL")}}>
    {{-- <div class="g-recaptcha ml-8" data-type="image" data-sitekey="{{ env('NOCAPTCHA_SITEKEY') }}"></div> --}}
    <div class="g-recaptcha ml-8" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 10000)" x-show="show">
            <div class="alert alert-danger">
                {{ session()->get('error') }}
            </div>
        </div>
    @endif

    <div class="form-group  mb-8 ml-8 mr-8 mt-14 mt-lg-14">
        <div class="my-4">
            <button type="submit" id="kt_login_signin_submit"
            class="btn btn-login btn-block font-weight-bold px-5 py-5 my-3 auth-form-btn">
                {{ __('LOGIN') }}
            </button>

            <?php /*
			@if (Route::has('password.request'))
				<a class="btn btn-link" href="{{ route('password.request') }}">
					{{ __('Forgot Your Password?') }}
				</a>
			@endif
			*/
            ?>
        </div>
        {{-- <div class="form-check text-dark-50 text-center text-hover-primary my-8 mr-2">
            <a class="text-dark-50  text-hover-primary my-3 mr-2" id="kt_login_forgot">Are you forgot your password ? <a
                    href="#" class="text-dark font-weight">Reset</a> </a>
        </div> --}}

    </div>



    <style>
        input {
            background: 000;
            outline: 0;
            border-width: 0 0 2px;
            border-color: blue;
        }
    </style>

    {{-- </form> --}}
    <div class="modal fade" id="myModal_status" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true">
        <div class="modal-dialog  modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Change Password</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                </div>
                <div class="modal-body">
                    <p style="color:red;">Password must be a minimum of 8 characters including atleast one numeric, one
                        upper case, one lower case and one special character</p>
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label rquired">New Password</label>
                        {!! Form::text('new_password', null, ['class' => 'form-control', 'id' => 'new_password']) !!}

                        <div class="messageBox" style="color:red;"></div>
                    </div>

                    <div class="form-group">
                        <label for="message-text" class="col-form-label">Confirm Password</label>
                        {!! Form::text('confirm_password', null, ['class' => 'form-control', 'id' => 'confirm_password']) !!}

                        <div class="messageBox1" style="color:red;"></div>
                    </div>

                    <div>
                        <p>Your password is confidential and not to be revealed/documented under any circumstances.</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="change_password">Save</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('view.scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <script>
            $(document).ready(function() {
                $("#error_close").click(function() {
                    $("#hide_div").hide();
                });


                // $(document).on('click', '#kt_login_signin_submit', function() {

                //     var emp_id = $("#emp_id").val();
                //     event.preventDefault();
                //     $.ajaxSetup({
                //         headers: {
                //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                //         }
                //     });

                //     $.ajax({
                //         type: "GET",
                //         url: "{{ url('check_user_password') }}",
                //         data: {
                //             emp_id: emp_id
                //         },
                //         //dataType: "json",
                //         success: function(res) {
                //             if (res == 'notexist') {
                //                 $('#myModal_status').modal('show');
                //             } else {
                //                 $(".loginForm").submit();
                //             }
                //         },
                //         error: function(jqXHR, exception) {

                //         }
                //     });
                // })

                $(document).on('click', '#change_password', function() {
                    var emp_id = $("#emp_id").val();
                    var new_password = $("#new_password").val();
                    var confirm_password = $("#confirm_password").val();

                    regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$!%*?&])[A-Za-z\d@#$!%*?&]{8,}$/;

                    if (regex.exec(new_password) == null) {
                        $('.messageBox1').html('');
                        $('.messageBox').html('Password Invalid');
                        return false;
                    } else if (new_password != confirm_password) {
                        $('.messageBox').html('');
                        $('.messageBox1').html('Password Not Matched');
                        return false;
                    } else {
                        $('.messageBox').html('');
                        $('.messageBox1').html('');

                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });

                        $.ajax({
                            type: "POST",
                            url: "{{ url('change_user_password') }}",
                            data: {
                                emp_id: emp_id,
                                new_password: new_password,
                                confirm_password: confirm_password
                            },
                            //dataType: "json",
                            success: function(res) {
                                // toastr.success('Password has been changed!');
                                js_notification('success', 'Password has been changed!');
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            },
                            error: function(jqXHR, exception) {
                                // toastr.error('Something went wrong!');
                                js_notification('danger', 'Something went wrong!');
                            }
                        });

                    }


                })
                // $(document).on('click', '#kt_login_signin_submit', function() {

                //     var token = "1a32e71a46317b9cc6feb7388238c95d";
                //     var userId = $('#emp_id').val();
                //     var userPassword = $('#password').val();

                //     console.log('in login', userId, userPassword);
                //     $.ajax({
                //         type: "POST",
                //         url: "https://aims.officeos.in/api/v1_users/login_authentication",
                //         data: {
                //             token: token,
                //             emp_id: userId,
                //             password: userPassword
                //         },
                //         //dataType: "json",
                //         success: function(res) {
                //             console.log('succes', res);
                //             if (res.code == 200 && res.message == 'success') {

                //                 sessionUserId = res;
                //                 console.log(sessionUserId, 'll');
                //                 $.ajaxSetup({
                //                     headers: {
                //                         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                //                             'content')
                //                     }
                //                 });

                //                 $.ajax({
                //                     url: "{{ url('store-in-session') }}",
                //                     method: 'POST',
                //                     data: {
                //                         value: sessionUserId
                //                     },
                //                     success: function(response) {
                //                         console.log(
                //                             'Value stored successfully in session');
                //                          window.location.href = baseUrl + 'dashboard';
                //                     },

                //                 });
                //             }
                //         },
                //         error: function(jqXHR, exception) {

                //         }
                //     });

                // })
                $(document).on('click', '#kt_login_signin_submit', function(e) {
                    var token = "1a32e71a46317b9cc6feb7388238c95d";
                    var userId = $('#emp_id').val();
                    var userPassword = $('#password').val();
                    var proCodeUrl = $('#pro_code_url').val();
                    console.log('in login', userId, userPassword);
                    if (userId == '' || userPassword == '') {
                        e.preventDefault();
                        if (userId == '') {
                            $('#emp_id').css('border-color', 'red');
                        } else {
                            $('#emp_id').css('border-color', '');
                        }
                        if (userPassword == '') {
                            $('#password').css('border-color', 'red');
                        } else {
                            $('#password').css('border-color', '');
                        }
                        return false;
                    }

                    $.ajax({
                        type: "POST",
                        url: proCodeUrl+ '/api/v1_users/login_authentication',
                        // url: "https://aims.officeos.in/api/v1_users/login_authentication",
                        data: {
                            token: token,
                            emp_id: userId,
                            password: userPassword,
                           ' g-recaptcha-response': grecaptcha.getResponse(),
                        },
                        success: function(res) {

                            if (res.code == 200 && res.message == 'success') {
                                var sessionUserId = res;

                                $.ajaxSetup({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
                                    }
                                });

                                $.ajax({
                                    url: "/store-in-session",
                                    method: 'POST',
                                    data: {
                                        value: sessionUserId
                                    },
                                    success: function(response) {
                                        console.log(
                                            'Value stored successfully in session');
                                        window.location.href = baseUrl + 'dashboard';
                                    },
                                    error: function(jqXHR, exception) {
                                        console.error('Error storing value in session',
                                            exception);
                                    }
                                });
                            } else if(res.code == 500 && res.message == 'error'){

                                js_notification('error', res.errorMessage);
                            } else if(res.code == 400 && res.message == 'Bad Request'){
                                console.log('error', res);
                                js_notification('error', 'Invalid Credentials');
                            }
                        },
                        error: function(jqXHR, exception) {
                            console.error('Login request failed', exception);
                        }
                    });
                });


            });
        </script>
    @endpush
