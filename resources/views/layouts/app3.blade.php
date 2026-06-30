<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@php
    $currentURL = URL::current();
    $routex = explode('/', Route::current()->uri());
    $current_page = Route::getFacadeRoot()
        ->current()
        ->uri();
    $url_segment = Request::segment(1);
@endphp

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <title>{{ config('app.name', 'Annexmed Product Tool') }}</title>
    <meta name="description" content="." />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    @include('layouts/header_script')
</head>

 <style>
    #global-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        opacity: 1;
        transition: opacity 0.3s ease;
    }

    .ngx-spinner .loading-text {
        font-size: 20px;
        font-weight: 500;
        color: #333;
        margin-bottom: 20px;
    }

    .spinner-dots {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .dot {
        width: 12px;
        height: 12px;
        margin: 0 5px;
        border-radius: 50%;
        background-color: #3498db;
        animation: bounce 1.2s infinite ease-in-out;
    }

    .dot1 { animation-delay: -0.32s; }
    .dot2 { animation-delay: -0.16s; }
    .dot3 { animation-delay: 0s; }

    @keyframes bounce {
        0%, 80%, 100% {
            transform: scale(0);
        } 40% {
            transform: scale(1);
        }
    }
   
    .ngx-spinner .loading-timer {
        font-size: 16px;
        font-weight: 500;
        color: #555;
        margin-bottom: 8px;
        text-align: center;
    }

    .ngx-spinner .loading-percent {
        font-size: 15px;
        font-weight: 500;
        color: #555;
        margin-bottom: 18px;
        text-align: center;
    }

</style>

<body id="kt_body"
    class="header-fixed header-mobile-fixed subheader-enabled subheader-fixed aside-enabled aside-fixed aside-minimize-hoverable page-loading">
     <!-- ngx-style Global Loader -->
   <div id="global-loader" style="display: flex;">
    <div class="ngx-spinner">
        <div class="loading-text" id="global-loader-text">Loading...</div>
        <div class="loading-timer" id="global-loader-timer" style="display: none;">00:00</div>
        <div class="loading-percent" id="global-loader-percent" style="display: none;">0%</div>

        <div class="spinner-dots">
            <div class="dot dot1"></div>
            <div class="dot dot2"></div>
            <div class="dot dot3"></div>
        </div>
    </div>
</div>
    @include('layouts/mobile_header')
    <div class="d-flex flex-column flex-root">
        <div class="d-flex flex-row flex-column-fluid page">
            @include('layouts/side_menu')
            <div class="d-flex flex-column flex-row-fluid wrapper wrapper-back" id="kt_wrapper">
                @yield('subheader')
                @include('layouts/header_v1')
                <div class="content d-flex flex-column flex-column-fluid" id="kt_content" style="margin-top: -3rem;">
                    <div class="d-flex flex-column-fluid">
                        <div class="container-fluid  my-4">
                            @yield('content')
                        </div>
                    </div>
                </div>
                @include('layouts/footer')
            </div>
        </div>
    </div>
    <div id="kt_scrolltop" class="scrolltop">
        <span class="svg-icon">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                height="24px" viewBox="0 0 24 24" version="1.1">
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <polygon points="0 0 24 0 24 24 0 24" />
                    <rect fill="#000000" opacity="0.3" x="11" y="10" width="2" height="10" rx="1" />
                    <path
                        d="M6.70710678,12.7071068 C6.31658249,13.0976311 5.68341751,13.0976311 5.29289322,12.7071068 C4.90236893,12.3165825 4.90236893,11.6834175 5.29289322,11.2928932 L11.2928932,5.29289322 C11.6714722,4.91431428 12.2810586,4.90106866 12.6757246,5.26284586 L18.6757246,10.7628459 C19.0828436,11.1360383 19.1103465,11.7686056 18.7371541,12.1757246 C18.3639617,12.5828436 17.7313944,12.6103465 17.3242754,12.2371541 L12.0300757,7.38413782 L6.70710678,12.7071068 Z"
                        fill="#000000" fill-rule="nonzero" />
                </g>
            </svg>
        </span>
    </div>
    @include('layouts/footer_script')
</body>
@include('layouts/flashMessage')
{{-- @include('layouts/questionAndAnswers') --}}

</html>
<script>
    let timeout;
    let globalLoaderTimerInterval = null;
    let globalLoaderSeconds = 0;
    let activeGlobalAjaxRequests = 0;

    function formatGlobalLoaderTime(totalSeconds) {
        const minutes = Math.floor(totalSeconds / 60).toString().padStart(2, "0");
        const seconds = (totalSeconds % 60).toString().padStart(2, "0");

        return minutes + ":" + seconds;
    }

    function showGlobalLoader(message = "Loading...", showTimer = false, showPercent = false) {
        const loader = document.getElementById("global-loader");
        const loaderText = document.getElementById("global-loader-text");
        const loaderTimer = document.getElementById("global-loader-timer");
        const loaderPercent = document.getElementById("global-loader-percent");

        if (!loader || !loaderText) {
            return;
        }

        loaderText.textContent = message;
        loader.style.display = "flex";
        loader.style.opacity = 1;

        clearInterval(globalLoaderTimerInterval);
        globalLoaderTimerInterval = null;
        globalLoaderSeconds = 0;

        if (loaderTimer) {
            if (showTimer) {
                loaderTimer.textContent = "00:00";
                loaderTimer.style.display = "block";

                globalLoaderTimerInterval = setInterval(function () {
                    globalLoaderSeconds++;
                    loaderTimer.textContent = formatGlobalLoaderTime(globalLoaderSeconds);
                }, 1000);
            } else {
                loaderTimer.textContent = "00:00";
                loaderTimer.style.display = "none";
            }
        }

        if (loaderPercent) {
            if (showPercent) {
                loaderPercent.textContent = "0%";
                loaderPercent.style.display = "block";
            } else {
                loaderPercent.textContent = "0%";
                loaderPercent.style.display = "none";
            }
        }
    }

    function updateGlobalLoaderMessage(message) {
        const loaderText = document.getElementById("global-loader-text");

        if (loaderText) {
            loaderText.textContent = message;
        }
    }

    function updateGlobalLoaderPercent(percent) {
        const loaderPercent = document.getElementById("global-loader-percent");

        if (loaderPercent) {
            loaderPercent.textContent = percent + "%";
        }
    }

    function hideGlobalLoaderPercent() {
        const loaderPercent = document.getElementById("global-loader-percent");

        if (loaderPercent) {
            loaderPercent.textContent = "0%";
            loaderPercent.style.display = "none";
        }
    }

    function restartGlobalLoaderTimer() {
        const loaderTimer = document.getElementById("global-loader-timer");

        clearInterval(globalLoaderTimerInterval);
        globalLoaderTimerInterval = null;
        globalLoaderSeconds = 0;

        if (loaderTimer) {
            loaderTimer.textContent = "00:00";
            loaderTimer.style.display = "block";
        }

        globalLoaderTimerInterval = setInterval(function () {
            globalLoaderSeconds++;

            if (loaderTimer) {
                loaderTimer.textContent = formatGlobalLoaderTime(globalLoaderSeconds);
            }
        }, 1000);
    }

    function hideGlobalLoader() {
        const loader = document.getElementById("global-loader");
        const loaderText = document.getElementById("global-loader-text");
        const loaderTimer = document.getElementById("global-loader-timer");
        const loaderPercent = document.getElementById("global-loader-percent");

        clearInterval(globalLoaderTimerInterval);
        globalLoaderTimerInterval = null;
        globalLoaderSeconds = 0;

        if (loaderText) {
            loaderText.textContent = "Loading...";
        }

        if (loaderTimer) {
            loaderTimer.textContent = "00:00";
            loaderTimer.style.display = "none";
        }

        if (loaderPercent) {
            loaderPercent.textContent = "0%";
            loaderPercent.style.display = "none";
        }

        if (loader) {
            loader.style.opacity = 0;

            setTimeout(function () {
                loader.style.display = "none";
            }, 300);
        }
    }

    window.onload = function () {
        hideGlobalLoader();
    };

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll("form").forEach(function (form) {
            form.addEventListener("submit", function () {
                if (form.id === "uploadForm") {
                    return;
                }

                showGlobalLoader("Loading...", false, false);
            });
        });

        window.addEventListener("beforeunload", function () {
            showGlobalLoader("Loading...", false, false);
        });

        if (window.jQuery) {
            $(document).ajaxSend(function (event, jqXHR, options) {
                const ajaxUrl = options && options.url ? options.url : "";

                if (ajaxUrl.includes("project_report_tracking_status")) {
                    return;
                }

                activeGlobalAjaxRequests++;
                showGlobalLoader("Loading...", false, false);
            });

            $(document).ajaxComplete(function (event, jqXHR, options) {
                const ajaxUrl = options && options.url ? options.url : "";

                if (ajaxUrl.includes("project_report_tracking_status")) {
                    return;
                }

                activeGlobalAjaxRequests = Math.max(0, activeGlobalAjaxRequests - 1);

                if (activeGlobalAjaxRequests === 0) {
                    hideGlobalLoader();
                }
            });

            $(document).ajaxError(function (event, jqXHR, options) {
                const ajaxUrl = options && options.url ? options.url : "";

                if (ajaxUrl.includes("project_report_tracking_status")) {
                    return;
                }

                activeGlobalAjaxRequests = Math.max(0, activeGlobalAjaxRequests - 1);

                if (activeGlobalAjaxRequests === 0) {
                    hideGlobalLoader();
                }
            });
        }
    });

    const resetTimeout = () => {
        clearTimeout(timeout);

        timeout = setTimeout(() => {
            window.location.href = "/logout";
        }, 7200000);
    };

    ["click", "mousemove", "keypress"].forEach((event) => {
        document.addEventListener(event, resetTimeout);
    });

    resetTimeout();

    document.addEventListener("click", function (event) {
        const target = event.target.closest("a, button");

        if (!target) {
            return;
        }

        const href = target.getAttribute("href") || target.getAttribute("data-href") || "";

        if (href.includes("export") || href.includes("download")) {
            showGlobalLoader("Loading...", false, false);

            setTimeout(function () {
                hideGlobalLoader();
            }, 3000);
        }
    });
</script>