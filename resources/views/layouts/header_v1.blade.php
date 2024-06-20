<div id="kt_header" class="header header-fixed">
    <div class="container-fluid d-flex align-items-stretch justify-content-between aims-main-header">
        <div class="header-menu-wrapper header-menu-wrapper-left" id="kt_header_menu_wrapper">
            <div id="kt_header_menu" class="header-menu header-menu-mobile header-menu-layout-default">
               @php
              // $permission_menu = App\Http\Helper\Admin\Helpers::getPermission();
              //dd( Session::get('menusOrder'));
               if (Session::get('menusOrder') &&  Session::get('menusOrder') !=null) {
                    $permission_menu = Session::get('menusOrder');
                }
               @endphp
                <ul class="menu-nav">
                   @if(isset($permission_menu))
                        @forelse($permission_menu as $main_menu)
                            @php
                            $menu_id = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(request()->parent,'decode');
                            //dd($menu_id);
                            $main_menu_id = App\Http\Helper\Admin\Helpers::encodeAndDecodeID($main_menu->id,'encode');
                            @endphp
                            {{-- @if($main_menu->id == 1) --}}
                                <li class="menu-item menu-item-open menu-item-submenu menu-item-rel menu-item-open {{ ($menu_id == $main_menu->id) ? 'menu-item-active' : '' }}" id="active{{$main_menu->id}}" data-menu-toggle="click" aria-haspopup="true">
                                    {{-- <a href="{{ url($main_menu->menu_url) }}/{{$menu_encode_id}}" class="menu-link"> --}}
                                        <a href="{{ url($main_menu->menu_url) }}" class="menu-link main_menu" id="{{$main_menu->id}}">

                                        <span class="menu-text"> {{ ucwords($main_menu->menu_name) }}</span>
                                        <i class="menu-arrow"></i>
                                    </a>
                                </li>
                                {{-- @endif --}}
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
                            <!--begin::Svg Icon | path:assets/media/svg/icons/Code/Compiling.svg-->
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
                              </svg>
                            <!--end::Svg Icon-->
                        </span>
                        <span class="badge rounded-pill badge-notification bg-danger" style="top: -10px; left: -13px; font-size: 75%;">0</span>
                    </div>
                </div>
                {{-- <div class="topbar-item" data-toggle="dropdown" data-offset="10px,0px">
                    <div class="btn btn-icon btn-hover-transparent-white btn-dropdown btn-lg mr-1 pulse pulse-primary">
                        <span class="svg-icon svg-icon-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24" />
                                    <path
                                        d="M2.56066017,10.6819805 L4.68198052,8.56066017 C5.26776695,7.97487373 6.21751442,7.97487373 6.80330086,8.56066017 L8.9246212,10.6819805 C9.51040764,11.267767 9.51040764,12.2175144 8.9246212,12.8033009 L6.80330086,14.9246212 C6.21751442,15.5104076 5.26776695,15.5104076 4.68198052,14.9246212 L2.56066017,12.8033009 C1.97487373,12.2175144 1.97487373,11.267767 2.56066017,10.6819805 Z M14.5606602,10.6819805 L16.6819805,8.56066017 C17.267767,7.97487373 18.2175144,7.97487373 18.8033009,8.56066017 L20.9246212,10.6819805 C21.5104076,11.267767 21.5104076,12.2175144 20.9246212,12.8033009 L18.8033009,14.9246212 C18.2175144,15.5104076 17.267767,15.5104076 16.6819805,14.9246212 L14.5606602,12.8033009 C13.9748737,12.2175144 13.9748737,11.267767 14.5606602,10.6819805 Z"
                                        fill="#000000" opacity="0.3" />
                                    <path
                                        d="M8.56066017,16.6819805 L10.6819805,14.5606602 C11.267767,13.9748737 12.2175144,13.9748737 12.8033009,14.5606602 L14.9246212,16.6819805 C15.5104076,17.267767 15.5104076,18.2175144 14.9246212,18.8033009 L12.8033009,20.9246212 C12.2175144,21.5104076 11.267767,21.5104076 10.6819805,20.9246212 L8.56066017,18.8033009 C7.97487373,18.2175144 7.97487373,17.267767 8.56066017,16.6819805 Z M8.56066017,4.68198052 L10.6819805,2.56066017 C11.267767,1.97487373 12.2175144,1.97487373 12.8033009,2.56066017 L14.9246212,4.68198052 C15.5104076,5.26776695 15.5104076,6.21751442 14.9246212,6.80330086 L12.8033009,8.9246212 C12.2175144,9.51040764 11.267767,9.51040764 10.6819805,8.9246212 L8.56066017,6.80330086 C7.97487373,6.21751442 7.97487373,5.26776695 8.56066017,4.68198052 Z"
                                        fill="#000000" />
                                </g>
                            </svg>
                           </span>
                        <span class="pulse-ring"></span>
                    </div>
                </div> --}}
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
             {{-- <div class="dropdown">
                <div class="topbar-item" data-toggle="dropdown" data-offset="10px,0px">
                    <div class="btn btn-icon btn-hover-transparent-white btn-dropdown btn-lg mr-1">
                        <span class="svg-icon svg-icon-success svg-icon-3x">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24" />
                                    <path
                                        d="M18,2 L20,2 C21.6568542,2 23,3.34314575 23,5 L23,19 C23,20.6568542 21.6568542,22 20,22 L18,22 L18,2 Z"
                                        fill="#000000" opacity="0.3" />
                                    <path
                                        d="M5,2 L17,2 C18.6568542,2 20,3.34314575 20,5 L20,19 C20,20.6568542 18.6568542,22 17,22 L5,22 C4.44771525,22 4,21.5522847 4,21 L4,3 C4,2.44771525 4.44771525,2 5,2 Z M12,11 C13.1045695,11 14,10.1045695 14,9 C14,7.8954305 13.1045695,7 12,7 C10.8954305,7 10,7.8954305 10,9 C10,10.1045695 10.8954305,11 12,11 Z M7.00036205,16.4995035 C6.98863236,16.6619875 7.26484009,17 7.4041679,17 C11.463736,17 14.5228466,17 16.5815,17 C16.9988413,17 17.0053266,16.6221713 16.9988413,16.5 C16.8360465,13.4332455 14.6506758,12 11.9907452,12 C9.36772908,12 7.21569918,13.5165724 7.00036205,16.4995035 Z"
                                        fill="#000000" />
                                </g>
                            </svg>
                         </span>
                    </div>
                </div>
                  <div class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-anim-up dropdown-menu-lg">
                     <div class="d-flex flex-column flex-center py-5 bgi-size-cover bgi-no-repeat rounded-top"
                        style="background-image: url(/assets/media/misc/bg-1.jpg)">
                        <h4 class="text-white font-weight-bold mb-0">Quick Actions</h4>

                    </div>
                     <div class="row row-paddingless">
                              <div class="col-6">
                                <a href="{{url('user_details/user_resignation_separate')}}"
                                     class="d-block py-10 px-5 text-center bg-hover-light border-right border-bottom">
                                    <span class="svg-icon svg-icon-success svg-icon-3x">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                            viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24" />
                                                <path
                                                    d="M18,2 L20,2 C21.6568542,2 23,3.34314575 23,5 L23,19 C23,20.6568542 21.6568542,22 20,22 L18,22 L18,2 Z"
                                                    fill="#000000" opacity="0.3" />
                                                <path
                                                    d="M5,2 L17,2 C18.6568542,2 20,3.34314575 20,5 L20,19 C20,20.6568542 18.6568542,22 17,22 L5,22 C4.44771525,22 4,21.5522847 4,21 L4,3 C4,2.44771525 4.44771525,2 5,2 Z M12,11 C13.1045695,11 14,10.1045695 14,9 C14,7.8954305 13.1045695,7 12,7 C10.8954305,7 10,7.8954305 10,9 C10,10.1045695 10.8954305,11 12,11 Z M7.00036205,16.4995035 C6.98863236,16.6619875 7.26484009,17 7.4041679,17 C11.463736,17 14.5228466,17 16.5815,17 C16.9988413,17 17.0053266,16.6221713 16.9988413,16.5 C16.8360465,13.4332455 14.6506758,12 11.9907452,12 C9.36772908,12 7.21569918,13.5165724 7.00036205,16.4995035 Z"
                                                    fill="#000000" />
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="d-block text-dark-75 font-weight-bold font-size-h6 mt-2 mb-1">Resign</span>
                                 </a>
                            </div>
                           <div class="col-6">
                                 <a href="{{url('user_details/user_resignation_separate')}}"
                                   class="d-block py-10 px-5 text-center bg-hover-light border-bottom border-right">
                                    <span class="svg-icon svg-icon-success svg-icon-3x">
                                 <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                            viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24" />
                                                <path
                                                    d="M18,2 L20,2 C21.6568542,2 23,3.34314575 23,5 L23,19 C23,20.6568542 21.6568542,22 20,22 L18,22 L18,2 Z"
                                                    fill="#000000" opacity="0.3" />
                                                <path
                                                    d="M5,2 L17,2 C18.6568542,2 20,3.34314575 20,5 L20,19 C20,20.6568542 18.6568542,22 17,22 L5,22 C4.44771525,22 4,21.5522847 4,21 L4,3 C4,2.44771525 4.44771525,2 5,2 Z M12,11 C13.1045695,11 14,10.1045695 14,9 C14,7.8954305 13.1045695,7 12,7 C10.8954305,7 10,7.8954305 10,9 C10,10.1045695 10.8954305,11 12,11 Z M7.00036205,16.4995035 C6.98863236,16.6619875 7.26484009,17 7.4041679,17 C11.463736,17 14.5228466,17 16.5815,17 C16.9988413,17 17.0053266,16.6221713 16.9988413,16.5 C16.8360465,13.4332455 14.6506758,12 11.9907452,12 C9.36772908,12 7.21569918,13.5165724 7.00036205,16.4995035 Z"
                                                    fill="#000000" />
                                            </g>
                                        </svg>
                                     </span>
                                    <span class="d-block text-dark-75 font-weight-bold font-size-h6 mt-2 mb-1">Resign</span>
                                </a>
                                 <a href="{{url('user_details/user_resignation_separate')}}"
                                     class="d-block py-10 px-5 text-center bg-hover-light border-bottom border-right">
                                    <span class="svg-icon svg-icon-success svg-icon-3x">
                                    <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                            viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24" />
                                                <path
                                                    d="M18,2 L20,2 C21.6568542,2 23,3.34314575 23,5 L23,19 C23,20.6568542 21.6568542,22 20,22 L18,22 L18,2 Z"
                                                    fill="#000000" opacity="0.3" />
                                                <path
                                                    d="M5,2 L17,2 C18.6568542,2 20,3.34314575 20,5 L20,19 C20,20.6568542 18.6568542,22 17,22 L5,22 C4.44771525,22 4,21.5522847 4,21 L4,3 C4,2.44771525 4.44771525,2 5,2 Z M12,11 C13.1045695,11 14,10.1045695 14,9 C14,7.8954305 13.1045695,7 12,7 C10.8954305,7 10,7.8954305 10,9 C10,10.1045695 10.8954305,11 12,11 Z M7.00036205,16.4995035 C6.98863236,16.6619875 7.26484009,17 7.4041679,17 C11.463736,17 14.5228466,17 16.5815,17 C16.9988413,17 17.0053266,16.6221713 16.9988413,16.5 C16.8360465,13.4332455 14.6506758,12 11.9907452,12 C9.36772908,12 7.21569918,13.5165724 7.00036205,16.4995035 Z"
                                                    fill="#000000" />
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="d-block text-dark-75 font-weight-bold font-size-h6 mt-2 mb-1">Resign</span>
                                </a>

                            @if(isset($user_resignation) && !empty($user_resignation))
                                @if($user_resignation['res_accepted'] == "yes")
                                    <a href="{{url('resignation/resignation_exit_form?parent=MzM=')}}"
                                        class="d-block py-10 px-5 text-center bg-hover-light border-bottom border-right">
                                        <span class="svg-icon svg-icon-success svg-icon-3x">
                                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <rect x="0" y="0" width="24" height="24" />
                                                    <path d="M18,2 L20,2 C21.6568542,2 23,3.34314575 23,5 L23,19 C23,20.6568542 21.6568542,22 20,22 L18,22 L18,2 Z" fill="#000000" opacity="0.3" />
                                                    <path d="M5,2 L17,2 C18.6568542,2 20,3.34314575 20,5 L20,19 C20,20.6568542 18.6568542,22 17,22 L5,22 C4.44771525,22 4,21.5522847 4,21 L4,3 C4,2.44771525 4.44771525,2 5,2 Z M12,11 C13.1045695,11 14,10.1045695 14,9 C14,7.8954305 13.1045695,7 12,7 C10.8954305,7 10,7.8954305 10,9 C10,10.1045695 10.8954305,11 12,11 Z M7.00036205,16.4995035 C6.98863236,16.6619875 7.26484009,17 7.4041679,17 C11.463736,17 14.5228466,17 16.5815,17 C16.9988413,17 17.0053266,16.6221713 16.9988413,16.5 C16.8360465,13.4332455 14.6506758,12 11.9907452,12 C9.36772908,12 7.21569918,13.5165724 7.00036205,16.4995035 Z" fill="#000000" />
                                                </g>
                                            </svg>
                                        </span>
                                        <span class="d-block text-dark-75 font-weight-bold font-size-h6 mt-2 mb-1">Exit Form</span>
                                    </a>
                                @endif
                            @endif
                        </div>

                        <div class="col-6">
                            <a href="{{url('policy')}}"
                                class="d-block py-10 px-5 text-center bg-hover-light border-right border-bottom">
                                <span class="svg-icon svg-icon-success svg-icon-3x">
                                 <svg
                                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect x="0" y="0" width="24" height="24" />
                                            <path
                                                d="M13.6855025,18.7082217 C15.9113859,17.8189707 18.682885,17.2495635 22,17 C22,16.9325178 22,13.1012863 22,5.50630526 L21.9999762,5.50630526 C21.9999762,5.23017604 21.7761292,5.00632908 21.5,5.00632908 C21.4957817,5.00632908 21.4915635,5.00638247 21.4873465,5.00648922 C18.658231,5.07811173 15.8291155,5.74261533 13,7 C13,7.04449645 13,10.79246 13,18.2438906 L12.9999854,18.2438906 C12.9999854,18.520041 13.2238496,18.7439052 13.5,18.7439052 C13.5635398,18.7439052 13.6264972,18.7317946 13.6855025,18.7082217 Z"
                                                fill="#000000" />
                                            <path
                                                d="M10.3144829,18.7082217 C8.08859955,17.8189707 5.31710038,17.2495635 1.99998542,17 C1.99998542,16.9325178 1.99998542,13.1012863 1.99998542,5.50630526 L2.00000925,5.50630526 C2.00000925,5.23017604 2.22385621,5.00632908 2.49998542,5.00632908 C2.50420375,5.00632908 2.5084219,5.00638247 2.51263888,5.00648922 C5.34175439,5.07811173 8.17086991,5.74261533 10.9999854,7 C10.9999854,7.04449645 10.9999854,10.79246 10.9999854,18.2438906 L11,18.2438906 C11,18.520041 10.7761358,18.7439052 10.4999854,18.7439052 C10.4364457,18.7439052 10.3734882,18.7317946 10.3144829,18.7082217 Z"
                                                fill="#000000" opacity="0.3" />
                                        </g>
                                    </svg>
                                </span>
                                <span
                                    class="d-block text-dark-75 font-weight-bold font-size-h6 mt-2 mb-1">Policy</span>
                             </a>
                        </div>
                          </div>
                 </div>
            </div> --}}
              <div class="dropdown">
                <div class="topbar-item" data-toggle="dropdown" data-offset="0px,0px">
                    <div
                        class="btn btn-icon btn-hover-transparent-white d-flex align-items-center btn-lg px-md-2 w-md-auto">

                        {{-- @guest
                            <span
                                class="text-white opacity-90 font-weight-bolder font-size-base d-none d-md-inline mr-4">{{ session()->get('loginDetails')['userDetail']['user_name'] }}</span>
                        @else --}}

                            {{-- <span
                                class="text-white opacity-70 font-weight-bold font-size-base d-none d-md-inline mr-1">Hi,</span> --}}
                                <span class="symbol symbol-md bg-light-primary mr-3 flex-shrink-0">
                                    <img src="{{ URL::asset('/assets/media/users/default.jpg') }}" alt="" />

                                </span>
                                <span
                                class="text-white opacity-90 font-weight-bolder font-size-base d-none d-md-inline">{{ session()->get('loginDetails') && session()->get('loginDetails')['userInfo'] && session()->get('loginDetails')['userDetail']['user_name'] ? session()->get('loginDetails')['userDetail']['user_name'] : '' }}</span>

                        {{-- @endguest --}}

                    </div>
                </div>
                 <div class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-anim-up dropdown-menu-md p-0">
                    {{-- <div class="d-flex align-items-center p-8 rounded-top"> --}}
                         {{-- <div class="symbol symbol-md bg-light-primary mr-3 flex-shrink-0">
                            <img src="{{ URL::asset('/assets/media/users/default.jpg') }}" alt="" />

                        </div>
                          <div class="text-dark m-0 flex-grow-1 mr-3 font-size-h5"> <a href="{{ url('userprofile/user_profile_create/change') }}" class="text-dark m-0 flex-grow-1 mr-3 font-size-h5">
                            Admin</a></div> --}}
                    {{-- </div> --}}
                    {{-- <div class="separator separator-solid"></div> --}}
                     <div class="navi navi-spacer-x-0 pt-5">
                        {{-- <a href="{{ url('profile') }}" class="navi-item px-8">
                            <div class="navi-link">
                                <div class="navi-icon mr-2">
                                    <i class="flaticon2-calendar-3 text-success"></i>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold">Change Password</div>

                                </div>
                            </div>
                        </a>

                        <a href="#" data-toggle="modal" data-target="#myModal" class="navi-item px-8">
                            <div class="navi-link">
                                <div class="navi-icon mr-2">
                                    <i class="flaticon2-mail text-warning"></i>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold">Break</div>
                                    <div class="text-muted">Break and Lunch Timing</div>
                                </div>
                            </div>
                        </a>
                            <a href="" class="navi-item px-8">
                                  <div class="navi-link">
                                    <div class="navi-icon mr-2">
                                        <i class="flaticon2-rocket-1 text-danger"></i>
                                    </div>
                                    <div class="navi-text">
                                        <div class="font-weight-bold">Reports</div>
                                        <div class="text-muted">Logs and notifications</div>
                                    </div>
                                </div>
                            </a> --}}
                            {{-- <a href="" class="px-8 py-4 project_header"><svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                              </svg>&nbsp;&nbsp;&nbsp;Settings</a> --}}
                        {{-- <div class="navi-separator mt-3"></div> --}}
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


{{-- {!! Form::open(['url' => url('/work_allocation/break'), 'id' => 'breakhoursForm']) !!}
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Break Hours - Reason</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="break_error" style="color:red; position: relative;left: 48px;bottom: 0px;"></div>
                <div class="radio-with-Icon text-center mb-5">

                    <p class="radioOption-Item">
                        <input type="radio" name="break_type" id="BannerType3" value="3"
                            class="ng-valid ng-dirty ng-touched ng-empty" aria-invalid="false" style="">
                        <label for="BannerType3">
                            <img src="{{ URL::asset('/img/tea.png') }}">
                            Tea Break
                        </label>
                    </p>

                    <p class="radioOption-Item">
                        <input type="radio" name="break_type" id="BannerType2" value="2"
                            class="ng-valid ng-dirty ng-touched ng-empty" aria-invalid="false" style="">
                        <label for="BannerType2">
                            <img src="{{ URL::asset('/img/lunch.png') }}">
                            Lunch Break
                        </label>
                    </p>

                    <p class="radioOption-Item">
                        <input type="radio" name="break_type" id="BannerType4" value="1"
                            class="ng-valid ng-dirty ng-touched ng-empty" aria-invalid="false" style="">
                        <label for="BannerType4">
                            <img src="{{ URL::asset('/img/meeting.png') }}">
                            Team Meeting
                        </label>
                    </p>

                    <p class="radioOption-Item">
                        <input type="radio" name="break_type" id="BannerType1" value="4"
                            class="ng-valid ng-dirty ng-touched ng-empty" aria-invalid="false" style="">
                        <label for="BannerType1">
                            <img src="{{ URL::asset('/img/events.png') }}">
                            Events
                        </label>
                    </p>


                    <p class="radioOption-Item">
                        <input type="radio" name="break_type" id="BannerType5" value="5"
                            class="ng-valid ng-dirty ng-touched ng-empty" aria-invalid="false" style="">
                        <label for="BannerType5">
                            <img src="{{ URL::asset('/img/system.png') }}">
                            System Issue
                        </label>
                    </p>
                </div>
                <p class="text-center mt-3"><input type='submit' value='Submit' id='break_submit'
                        class="btn btn-primary mt-5"></p>
            </div>
        </div>
    </div>
</div>
{!! Form::close() !!} --}}


@push('view.scripts')
    <script>
        $(document).ready(function() {
            Times();

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
