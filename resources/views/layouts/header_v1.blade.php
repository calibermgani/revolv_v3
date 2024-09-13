<div id="kt_header" class="header header-fixed">
    <div class="container-fluid d-flex align-items-stretch justify-content-between aims-main-header">
        <div class="header-menu-wrapper header-menu-wrapper-left" id="kt_header_menu_wrapper">
            <div id="kt_header_menu" class="header-menu header-menu-mobile header-menu-layout-default">
               @php
                if (Session::get('menusOrder') &&  Session::get('menusOrder') !=null) {
                    $permission_menu = Session::get('menusOrder');
                }
               @endphp
                <ul class="menu-nav">
                   @if(isset($permission_menu))
                        @forelse($permission_menu as $main_menu)
                            @php
                            $menu_id = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(request()->parent,'decode');
                             $main_menu_id = App\Http\Helper\Admin\Helpers::encodeAndDecodeID($main_menu->id,'encode');
                            @endphp
                                <li class="menu-item menu-item-open menu-item-submenu menu-item-rel menu-item-open {{ ($menu_id == $main_menu->id) ? 'menu-item-active' : '' }}" id="active{{$main_menu->id}}" data-menu-toggle="click" aria-haspopup="true">
                                         <a href="{{ url($main_menu->menu_url) }}" class="menu-link main_menu" id="{{$main_menu->id}}">

                                        <span class="menu-text"> {{ ucwords($main_menu->menu_name) }}</span>
                                        <i class="menu-arrow"></i>
                                    </a>
                                </li>
                                 @empty
                                <li></li>
                        @endforelse
                   @endif
                </ul>
            </div>
        </div>

        <div class="topbar">

            <div class="dropdown">
                <div class="topbar-item" data-toggle="dropdown" data-offset="10px,0px">
                    <div class="btn btn-icon btn-hover-transparent-white btn-dropdown btn-lg mr-1 pulse pulse-primary">
                        <span class="svg-icon svg-icon-xl">
                             <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
                              </svg>
                        </span>
                        <span class="badge rounded-pill badge-notification bg-danger" style="top: -10px; left: -13px; font-size: 75%;">0</span>
                    </div>
                </div>
                <div class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-anim-up dropdown-menu-lg">
                    <form>
                        <div class="d-flex flex-column pt-12 bgi-size-cover bgi-no-repeat rounded-top"
                            style="background-image: url(/assets/media/misc/bg-1.jpg)">
                            <h4 class="d-flex flex-center rounded-top">
                                <span class="text-white">User Notifications</span>
                                <span class="btn btn-text btn-success btn-sm font-weight-bold btn-font-md ml-2">0
                                    new</span>
                            </h4>
                             <ul class="nav nav-bold nav-tabs nav-tabs-line nav-tabs-line-3x nav-tabs-line-transparent-white nav-tabs-line-active-border-success mt-3 px-8"
                                role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active show" data-toggle="tab"
                                        href="#topbar_notifications_notifications">Alerts</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab"
                                        href="#topbar_notifications_events">Events</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#topbar_notifications_logs">Logs</a>
                                </li>
                            </ul>
                         </div>
                         <div class="tab-content">
                            <div class="tab-pane active show p-8" id="topbar_notifications_notifications"
                                role="tabpanel">
                                 <div class="scroll pr-7 mr-n7" data-scroll="true" data-height="300"
                                    data-mobile-height="200">
                                </div>
                             </div>
                           <div class="tab-pane" id="topbar_notifications_events" role="tabpanel">
                                 <div class="navi navi-hover scroll my-4" data-scroll="true" data-height="300"
                                    data-mobile-height="200">
                                    </div>
                               </div>
                             <div class="tab-pane" id="topbar_notifications_logs" role="tabpanel">
                                <div class="d-flex flex-center text-center text-muted min-h-200px">All caught up!
                                    <br />No new notifications.
                                </div>
                             </div>
                         </div>
                     </form>
                </div>
            </div>
              <div class="dropdown">
                <div class="topbar-item" data-toggle="dropdown" data-offset="0px,0px">
                    <div
                        class="btn btn-icon btn-hover-transparent-white d-flex align-items-center btn-lg px-md-2 w-md-auto">
                                <span class="symbol symbol-md bg-light-primary mr-3 flex-shrink-0">
                                    <img src="{{ URL::asset('/assets/media/users/default.jpg') }}" alt="" />

                                </span>
                                <span
                                class="text-white opacity-90 font-weight-bolder font-size-base d-none d-md-inline">{{ session()->get('loginDetails') && session()->get('loginDetails')['userInfo'] && session()->get('loginDetails')['userDetail']['user_name'] ? session()->get('loginDetails')['userDetail']['user_name'] : '' }}</span>
                    </div>
                </div>
                 <div class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-anim-up dropdown-menu-md p-0">
                     <div class="navi navi-spacer-x-0 pt-5">
                        <div class="navi-footer px-8 py-3">
                            <a href={{ asset('assets/media/userManual/resolv_user_manual_Managers.pdf') }} target="_blank" class="project_header"
                               ><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
                                <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
                              </svg>&nbsp;&nbsp;&nbsp;Resolv Manual</a>
                        </div>
                        <div class="navi-footer px-8 py-5">
                            <a href="{{ url('logout') }}" class="project_header"
                               ><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16" color="red">
                                <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                                <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                              </svg>&nbsp;&nbsp;&nbsp;{{ __('Logout') }}</a>
                        </div>
                     </div>
                </div>
             </div>
         </div>
    </div>
</div>


@push('view.scripts')
    <script>
        $(document).ready(function() {
            Times();
            $('a.main_menu').on('click', function(e) {
                KTApp.block('#kt_header', {
                        overlayColor: '#000000',
                        state: 'danger',
                        opacity: 0.1,
                        message: 'Fetching...',
                    });
            });

            $(window).on('load', function() {
                KTApp.unblock('#kt_header');
            });
        });

        function Times() {
            var time = $("#login_timer").val();
            const today = new Date();
            const endDate = new Date(time);
            const days = parseInt((endDate - today) / (1000 * 60 * 60 * 24));
            const hours = parseInt(Math.abs(endDate - today) / (1000 * 60 * 60) % 24);
            const minutes = parseInt(Math.abs(endDate.getTime() - today.getTime()) / (1000 * 60) % 60);
            const seconds = parseInt(Math.abs(endDate.getTime() - today.getTime()) / (1000) % 60);
            if (hours < 10) {
                tot_hours = '0' + hours;
            } else {
                tot_hours = hours;
            }
            if (minutes < 10) {
                tot_minutes = '0' + minutes;
            } else {
                tot_minutes = minutes;
            }
            if (seconds < 10) {
                tot_seconds = '0' + seconds;
            } else {
                tot_seconds = seconds;
            }
            datetime = tot_hours + ':' + tot_minutes + ':' + tot_seconds;
            if (datetime == 'NaN:NaN:NaN') {
                $("#timer_count").html();
            } else {
                $("#timer_count").html(datetime);
            }
            timer = setTimeout("Times()", 1000);
        }
    </script>
    <script>
        $(document).on('click', '#break_submit', function() {
            var isChecked = jQuery("input[name=break_type]:checked").val();
            var booleanVlaueIsChecked = false;
            if (isChecked) {
                booleanVlaueIsChecked = true;
            } else {
                $("#break_error").html("Please choose any one break!.");
                return false;
            }


        });
    </script>
@endpush
