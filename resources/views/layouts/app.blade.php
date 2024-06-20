<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Annexmed Product Tool') }}</title>
    @include('layouts/header_script')
    <style>
        /* Additional styling for the page */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        .login-container {
            display: flex;
            min-height: 100vh;
            background-color: #ffffff;
            color: white;
            position: relative;
        }

        .left-side, .right-side {
            flex: 1;
        }

        .left-side {
            /* background-image: url('{{ asset("assets/media/bg/login_img.svg") }}');
            background-size: cover;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center; */
            margin: 20px;
        }

        .resolv_img{
            background-image: url('{{ asset("assets/media/bg/login_img.svg") }}');
            background-size: cover;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            position: relative;
        }

        .right-side {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        .login-card {
            max-width: 550px;
            width: 100%;
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(226, 216, 216, 0.1);
            color: #191C24;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .left-side {
                min-height: 200px;
            }

            .right-side {
                padding: 20px 0;
            }

            .copyright-container {
                position: relative;
                text-align: center;
                margin-top: 20px;
                left:11rem !important;
            }

            .copyright-container p {
                color: black;
            }
        }

        @media (max-width: 480px) {
            .copyright-container p {
                color: black;
            }
            .copyright-container {
                left:0rem !important;
            }
        }
        @media (max-width: 321px) {
            .copyright-container p {
                color: black;
            }
            .copyright-container {
                left:0rem !important;
            }
        }

        .copyright-container {
            position: absolute;
            bottom: 10px;
            right: 0;
            text-align: center;
            font-size: 11px;
            width: 100%;
        }

        .copyright-container p {
            margin-right: 10px;
        }

        .copyright-container p:first-child {
            margin-right: auto;
        }

        .copyright-container p:not(:first-child) {
            color: #191C24;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="left-side">
            <!-- Left side content -->
            <div class="resolv_img">
                <div class="copyright-container">
                    <div style="display:flex;justify-content:space-between;align-items: center;margin:0px 15px">
                        <div>&copy; {{ date('Y') }} Procode - All rights reserved by Annexmed</div>
                    <div>&#x2709; : procodesupport@annexmed.net</div>
                    </div>
                    
                </div>
            </div>
            
        </div>
        <div class="right-side">
            <div class="login-card">
                <div class="ml-8 mr-8">
                  <img src="{{ asset("assets/media/bg/resolve_logo.svg") }}" alt="" style = "width: 194px;height: 56px;">
                </div>
                @yield('content')
            </div>
        </div>
    </div>

    
</body>
@include('layouts/footer_script')
@include('layouts/flashMessage')
</html>
