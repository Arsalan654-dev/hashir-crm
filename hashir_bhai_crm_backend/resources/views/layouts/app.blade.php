<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>

        <meta charset="utf-8" />
        <title>@yield('title', 'Dashboard') | {{ \App\Support\Branding::name() }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="{{ \App\Support\Branding::name() }} CRM &amp; ERP Suite" name="description" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset(\App\Support\Branding::favicon()) }}">

        <!-- plugin css -->
        <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- Bootstrap Css -->
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- Theme-Css (ids required by assets/js/app.js) -->
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

        <style>
            .cds-kpi-icon { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 22px; }
            .cds-table-actions .btn { --bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; }
            .cds-scroll { max-height: 420px; overflow-y: auto; }
            .cds-chat-bubble { border-radius: 12px; padding: .6rem .85rem; max-width: 78%; word-break: break-word; }
            .cds-chat-in { background: #f3f6fb; }
            .cds-chat-out { background: {{ \App\Support\Branding::primaryColor() }}; color: #fff; }
            .btn-primary { background-color: {{ \App\Support\Branding::primaryColor() }}; border-color: {{ \App\Support\Branding::primaryColor() }}; }
            .text-primary { color: {{ \App\Support\Branding::primaryColor() }} !important; }
        </style>

        @stack('styles')

    </head>

    <body data-layout="{{ \App\Support\Branding::layout() }}" data-topbar="{{ \App\Support\Branding::topbarStyle() }}" data-sidebar="{{ \App\Support\Branding::sidebarStyle() }}" data-sidebar-size="lg">

        <!-- Begin page -->
        <div id="layout-wrapper">

            @include('partials.impersonation-banner')
            @include('partials.topbar')

            <!-- ========== Left Sidebar Start ========== -->
            <div class="vertical-menu">

                <!-- LOGO -->
                <div class="navbar-brand-box">
                    <a href="{{ url('/') }}" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ asset('assets/images/logo-cyprus-mark.svg') }}" alt="" height="26">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset(\App\Support\Branding::logo()) }}" alt="" height="30">
                        </span>
                    </a>

                    <a href="{{ url('/') }}" class="logo logo-light">
                        <span class="logo-lg">
                            <img src="{{ asset(\App\Support\Branding::logo(true)) }}" alt="" height="30">
                        </span>
                        <span class="logo-sm">
                            <img src="{{ asset('assets/images/logo-cyprus-mark.svg') }}" alt="" height="26">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect vertical-menu-btn">
                    <i class="bx bx-menu align-middle"></i>
                </button>

                <div data-simplebar class="sidebar-menu-scroll">

                    <!--- Sidemenu -->
                    @include('partials.sidebar')
                    <!-- Sidebar -->
                </div>
            </div>
            <!-- Left Sidebar End -->
            <header class="ishorizontal-topbar">
                <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                        <div class="navbar-brand-box">
                            <a href="/" class="logo logo-dark">
                                <span class="logo-sm">
                                    <img src="assets/images/logo-cyprus-mark.svg" alt="" height="26">
                                </span>
                                <span class="logo-lg">
                                    <img src="assets/images/logo-cyprus.svg" alt="" height="28">
                                </span>
                            </a>

                            <a href="/" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="assets/images/logo-cyprus-mark.svg" alt="" height="26">
                                </span>
                                <span class="logo-lg">
                                    <img src="assets/images/logo-cyprus-light.svg" alt="" height="30">
                                </span>
                            </a>
                        </div>

                        <button type="button" class="btn btn-sm px-3 font-size-24 d-lg-none header-item" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                            <i class="bx bx-menu align-middle"></i>
                        </button>

                        <!-- start page title -->
                        <div class="page-title-box align-self-center d-none d-md-block">
                            <h4 class="page-title mb-0">Hi, Welcome Back!</h4>
                        </div>
                        <!-- end page title -->

                    </div>

                    <div class="d-flex">

                        <div class="dropdown d-inline-block language-switch ms-2 ms-xl-3">
                            <button type="button" class="btn header-item" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img class="header-lang-img" src="assets/images/flags/us.jpg" alt="Header Language" height="18">
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                    
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="eng">
                                    <img src="assets/images/flags/us.jpg" alt="user-image" class="me-1" height="12"> <span class="align-middle">English</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="sp">
                                    <img src="assets/images/flags/spain.jpg" alt="user-image" class="me-1" height="12"> <span class="align-middle">Spanish</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="gr">
                                    <img src="assets/images/flags/germany.jpg" alt="user-image" class="me-1" height="12"> <span class="align-middle">German</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="it">
                                    <img src="assets/images/flags/italy.jpg" alt="user-image" class="me-1" height="12"> <span class="align-middle">Italian</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="ru">
                                    <img src="assets/images/flags/russia.jpg" alt="user-image" class="me-1" height="12"> <span class="align-middle">Russian</span>
                                </a>
                            </div>
                        </div>

                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn header-item noti-icon"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-search icon-sm align-middle"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0">
                                <form class="p-2">
                                    <div class="search-box">
                                        <div class="position-relative">
                                            <input type="text" class="form-control rounded bg-light border-0" placeholder="Search...">
                                            <i class="bx bx-search search-icon"></i>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn header-item noti-icon" id="page-header-notifications-dropdown"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-bell icon-sm align-middle"></i>
                                <span class="noti-dot bg-danger rounded-pill">4</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                                aria-labelledby="page-header-notifications-dropdown">
                                <div class="p-3">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h5 class="m-0 font-size-15"> Notifications </h5>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#!" class="small fw-semibold text-decoration-underline"> Mark all as read</a>
                                        </div>
                                    </div>
                                </div>
                                <div data-simplebar style="max-height: 250px;">
                                    <a href="#!" class="text-reset notification-item">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <img src="assets/images/users/avatar-3.jpg" class="rounded-circle avatar-sm" alt="user-pic">
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">James Lemire</h6>
                                                <div class="font-size-13 text-muted">
                                                    <p class="mb-1">It will seem like simplified English.</p>
                                                    <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span>1 hour ago</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="#!" class="text-reset notification-item">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 avatar-sm me-3">
                                                <span class="avatar-title bg-primary rounded-circle font-size-16">
                                                    <i class="bx bx-cart"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">Your order is placed</h6>
                                                <div class="font-size-13 text-muted">
                                                    <p class="mb-1">If several languages coalesce the grammar</p>
                                                    <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span>3 min ago</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="#!" class="text-reset notification-item">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 avatar-sm me-3">
                                                <span class="avatar-title bg-success rounded-circle font-size-16">
                                                    <i class="bx bx-badge-check"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">Your item is shipped</h6>
                                                <div class="font-size-13 text-muted">
                                                    <p class="mb-1">If several languages coalesce the grammar</p>
                                                    <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span>3 min ago</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>

                                    <a href="#!" class="text-reset notification-item">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <img src="assets/images/users/avatar-6.jpg" class="rounded-circle avatar-sm" alt="user-pic">
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">Salena Layfield</h6>
                                                <div class="font-size-13 text-muted">
                                                    <p class="mb-1">As a skeptical Cambridge friend of mine occidental.</p>
                                                    <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span>1 hour ago</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="p-2 border-top d-grid">
                                    <a class="btn btn-sm btn-link font-size-14 btn-block text-center" href="javascript:void(0)">
                                        <i class="uil-arrow-circle-right me-1"></i> <span>View More..</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn header-item user text-start d-flex align-items-center" id="page-header-user-dropdown"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img class="rounded-circle header-profile-user" src="{{ auth()->user()?->avatarUrl() }}"
                                alt="Header Avatar">
                                <span class="d-none d-xl-inline-block ms-2 fw-medium font-size-15">{{ auth()->user()?->name ?? 'Guest' }}</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end pt-0">
                                <div class="p-3 border-bottom">
                                    <h6 class="mb-0">{{ auth()->user()?->name ?? 'Guest' }}</h6>
                                    <p class="mb-0 font-size-11 text-muted">{{ auth()->user()?->email }}</p>
                                </div>
                                <a class="dropdown-item" href="{{ url('/contacts-profile') }}"><i class="mdi mdi-account-circle text-muted font-size-16 align-middle me-2"></i> <span class="align-middle">Profile</span></a>
                                <a class="dropdown-item" href="{{ url('/apps-chat') }}"><i class="mdi mdi-message-text-outline text-muted font-size-16 align-middle me-2"></i> <span class="align-middle">Messages</span></a>
                                <a class="dropdown-item" href="{{ url('/settings') }}"><i class="mdi mdi-cog-outline text-muted font-size-16 align-middle me-2"></i> <span class="align-middle">Settings</span></a>
                                <a class="dropdown-item" href="{{ route('auth-lock-screen') }}"><i class="mdi mdi-lock text-muted font-size-16 align-middle me-2"></i> <span class="align-middle">Lock screen</span></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ url('/auth-logout') }}"><i class="mdi mdi-logout text-muted font-size-16 align-middle me-2"></i> <span class="align-middle">Logout</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>


            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="page-title-box">
                                    <h4 class="page-title mb-0">@yield('page-title', 'Dashboard')</h4>
                                    @hasSection('breadcrumb')
                                        <ol class="breadcrumb mb-0">
                                            @yield('breadcrumb')
                                        </ol>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                                    @yield('page-actions')
                                </div>
                            </div>
                        </div>

                        @yield('content')

                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">
                                <script>document.write(new Date().getFullYear())</script> &copy; {{ \App\Support\Branding::name() }}
                            </div>
                            <div class="col-sm-6">
                                <div class="text-sm-end d-none d-sm-block">
                                    {{ \App\Support\Branding::tagline() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->
        <!-- Right Sidebar -->
        <a href="#" class="right-bar-toggle layout-setting-btn" id="right-bar-toggle">
            <i class="bx bx-cog icon-sm font-size-18"></i> <span>Settings</span>
        </a>

        <!-- Right Sidebar -->
        <div class="right-bar">
            <div data-simplebar class="h-100">
                <div class="rightbar-title d-flex align-items-center bg-dark p-3">

                    <h5 class="m-0 me-2 text-white">Theme Customizer</h5>

                    <a href="javascript:void(0);" class="right-bar-toggle-close ms-auto">
                        <i class="mdi mdi-close noti-icon"></i>
                    </a>
                </div>

                <!-- Settings -->
                <hr class="m-0" />

                <div class="p-4">
                    <h6 class="mb-3">Layout</h6>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout"
                            id="layout-vertical" value="vertical">
                        <label class="form-check-label" for="layout-vertical">Vertical</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout"
                            id="layout-horizontal" value="horizontal">
                        <label class="form-check-label" for="layout-horizontal">Horizontal</label>
                    </div>

                    <h6 class="mt-4 mb-3">Layout Mode</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-mode"
                            id="layout-mode-light" value="light">
                        <label class="form-check-label" for="layout-mode-light">Light</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-mode"
                            id="layout-mode-dark" value="dark">
                        <label class="form-check-label" for="layout-mode-dark">Dark</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-mode"
                            id="layout-mode-bordered" value="bordered">
                        <label class="form-check-label" for="layout-mode-bordered">Bordered</label>
                    </div>

                    <h6 class="mt-4 mb-3">Layout Width</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-width"
                            id="layout-width-fluid" value="fluid" onchange="document.body.setAttribute('data-layout-size', 'fluid')">
                        <label class="form-check-label" for="layout-width-fluid">Fluid</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-width"
                            id="layout-width-boxed" value="boxed" onchange="document.body.setAttribute('data-layout-size', 'boxed')">
                        <label class="form-check-label" for="layout-width-boxed">Boxed</label>
                    </div>

                    <h6 class="mt-4 mb-3">Layout Position</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-position"
                            id="layout-position-fixed" value="fixed" onchange="document.body.setAttribute('data-layout-scrollable', 'false')">
                        <label class="form-check-label" for="layout-position-fixed">Fixed</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-position"
                            id="layout-position-scrollable" value="scrollable" onchange="document.body.setAttribute('data-layout-scrollable', 'true')">
                        <label class="form-check-label" for="layout-position-scrollable">Scrollable</label>
                    </div>

                    <h6 class="mt-4 mb-3">Topbar Type</h6>

            
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="topbar-color"
                            id="topbar-color-light" value="light" onchange="document.body.setAttribute('data-topbar', 'light')">
                        <label class="form-check-label" for="topbar-color-light">Light</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="topbar-color"
                            id="topbar-color-dark" value="dark" onchange="document.body.setAttribute('data-topbar', 'dark')">
                        <label class="form-check-label" for="topbar-color-dark">Dark</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="topbar-color"
                            id="topbar-type-hidden" value="hidden" onchange="document.body.setAttribute('data-topbar', 'hidden')">
                        <label class="form-check-label" for="topbar-type-hidden">Hidden</label>
                    </div>
            

                    <div id="sidebar-setting">
                    <h6 class="mt-4 mb-3 sidebar-setting">Sidebar Size</h6>

                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-size"
                            id="sidebar-size-default" value="default" onchange="document.body.setAttribute('data-sidebar-size', 'lg')">
                        <label class="form-check-label" for="sidebar-size-default">Default</label>
                    </div>
                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-size"
                            id="sidebar-size-compact" value="compact" onchange="document.body.setAttribute('data-sidebar-size', 'md')">
                        <label class="form-check-label" for="sidebar-size-compact">Compact</label>
                    </div>
                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-size"
                            id="sidebar-size-small" value="small" onchange="document.body.setAttribute('data-sidebar-size', 'sm')">
                        <label class="form-check-label" for="sidebar-size-small">Small (Icon View)</label>
                    </div>

                    <h6 class="mt-4 mb-3 sidebar-setting">Sidebar Color</h6>

                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-color"
                            id="sidebar-color-light" value="light" onchange="document.body.setAttribute('data-sidebar', 'light')">
                        <label class="form-check-label" for="sidebar-color-light">Light</label>
                    </div>
                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-color"
                            id="sidebar-color-dark" value="dark" onchange="document.body.setAttribute('data-sidebar', 'dark')">
                        <label class="form-check-label" for="sidebar-color-dark">Dark</label>
                    </div>
                    <div class="form-check sidebar-setting">
                        <input class="form-check-input" type="radio" name="sidebar-color"
                            id="sidebar-color-brand" value="brand" onchange="document.body.setAttribute('data-sidebar', 'brand')">
                        <label class="form-check-label" for="sidebar-color-brand">Brand</label>
                    </div>
                </div>

                    <h6 class="mt-4 mb-3">Direction</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-direction"
                            id="layout-direction-ltr" value="ltr">
                        <label class="form-check-label" for="layout-direction-ltr">LTR</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-direction"
                            id="layout-direction-rtl" value="rtl">
                        <label class="form-check-label" for="layout-direction-rtl">RTL</label>
                    </div>

                </div>

            </div> <!-- end slimscroll-menu-->
        </div>
        <!-- /Right-bar -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>

        <!-- chat offcanvas -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasActivity" aria-labelledby="offcanvasActivityLabel">
            <div class="offcanvas-header border-bottom">
              <h5 id="offcanvasActivityLabel">Offcanvas right</h5>
              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
              ...
            </div>
        <!-- JAVASCRIPT -->
        <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/libs/metismenujs/metismenujs.min.js') }}"></script>
        <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/libs/eva-icons/eva.min.js') }}"></script>
        <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('assets/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
        <script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}"></script>
        <script src="{{ asset('assets/js/app.js') }}"></script>
        <script>
            (function () {
                var bootstrapCss = '{{ asset('assets/css/bootstrap.min.css') }}';
                var appCss = '{{ asset('assets/css/app.min.css') }}';

                function restoreStylesheets() {
                    var bootstrapLink = document.getElementById('bootstrap-style');
                    var appLink = document.getElementById('app-style');

                    if (bootstrapLink && bootstrapLink.getAttribute('href') !== bootstrapCss) {
                        bootstrapLink.setAttribute('href', bootstrapCss);
                    }

                    if (appLink && appLink.getAttribute('href') !== appCss) {
                        appLink.setAttribute('href', appCss);
                    }
                }

                restoreStylesheets();
                document.addEventListener('DOMContentLoaded', restoreStylesheets);
                window.addEventListener('load', restoreStylesheets);
                setTimeout(restoreStylesheets, 250);
            })();
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.cdsPermissionDenied = function (message) {
                    var modalEl = document.getElementById('cds-permission-modal');
                    if (!modalEl) { window.alert(message || '403 - You cannot access this.'); return; }
                    if (message) { var body = modalEl.querySelector('[data-permission-message]'); if (body) { body.textContent = message; } }
                    new bootstrap.Modal(modalEl).show();
                };

                window.cdsFetch = function (url, options) {
                    options = options || {};
                    options.headers = Object.assign({
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    }, options.headers || {});

                    return fetch(url, options).then(function (response) {
                        if (response.status === 403) {
                            return response.json().catch(function () { return {}; }).then(function (data) {
                                window.cdsPermissionDenied(data.message);
                                throw new Error(data.message || '403');
                            });
                        }
                        return response;
                    });
                };
            });
        </script>
        @include('partials.permission-modal')
        @include('partials.attendance-prompt')
        <script>
            // After logout, force a fresh request when the browser restores a page
            // from the back-forward cache so protected pages cannot be seen again.
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        </script>
        @stack('scripts')
    </body>
</html>
