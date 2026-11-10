<!doctype html>
<html lang="en">

    <head>

        <meta charset="utf-8" />
        <title>Dashboard | Cyprus Digital Solution</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Cyprus Digital Solution CRM & ERP" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon-cyprus.svg">

        <!-- Bootstrap Css -->
        <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    </head>

    
    <body>

    <!-- <body data-layout="horizontal"> -->

        <!-- Begin page -->
        <div id="layout-wrapper">

            
            @include('partials.topbar')
            <!-- ========== Left Sidebar Start ========== -->
            <div class="vertical-menu">

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
                        <span class="logo-lg">
                            <img src="assets/images/logo-cyprus-light.svg" alt="" height="30">
                        </span>
                        <span class="logo-sm">
                            <img src="assets/images/logo-cyprus-mark.svg" alt="" height="26">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect vertical-menu-btn">
                    <i class="bx bx-menu align-middle"></i>
                </button>

                <div data-simplebar class="sidebar-menu-scroll">

                    <!--- Sidemenu -->
                    @include('partials.sidebar')
                </div>
            </div>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">

                        <div class="row">
                            <div class="col-12">

                                <!-- Left sidebar -->
                                <div class="email-leftbar">
                                    <div class="card">
                                        <div class="card-body">
                                            <button type="button" class="btn btn-danger waves-effect waves-light w-100" data-bs-toggle="modal" data-bs-target="#composemodal">
                                                Compose
                                            </button>

                                            <div class="card p-0 overflow-hidden mt-4 shadow-none">
                                                <div class="mail-list">
                                                    <a href="#" class="active bg-primary-subtle ">
                                                        <div class="d-flex align-items-center">
                                                            <i class="mdi mdi-email-outline font-size-20 align-middle me-3"></i>
                                                            <div class="flex-grow-1">
                                                                <h5 class="font-size-14 mb-0">All Inbox</h5>
                                                                <span class="text-muted font-size-13 text-truncate">How To Boost Website</span>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <div class="float-end">
                                                                    <span class="bg-primary badge">18</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <a href="#" class="border-bottom">
                                                        <div class="d-flex align-items-center">
                                                            <i class="mdi mdi-star-outline font-size-20 align-middle me-3"></i>
                                                            <div class="flex-grow-1">
                                                                <h5 class="font-size-14 mb-0">Starred</h5>
                                                                <span class="text-muted font-size-13 text-truncate">Selected messages</span>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                            </div>
                                                        </div>
                                                    </a>
            
                                                    <a href="#" class="border-bottom">
                                                        <div class="d-flex align-items-center">
                                                            <i class="mdi mdi-diamond-stone font-size-20 align-middle me-3"></i>
                                                            <div class="flex-grow-1">
                                                                <h5 class="font-size-14 mb-0">Important</h5>
                                                                <span class="text-muted font-size-13 text-truncate">Selected messages</span>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                            </div>
                                                        </div>
                                                    </a>
            
                                                    <a href="#" class="border-bottom">
                                                        <div class="d-flex align-items-center">
                                                            <i class="mdi mdi-file-outline font-size-20 align-middle me-3"></i>
                                                            <div class="flex-grow-1">
                                                                <h5 class="font-size-14 mb-0">Draft</h5>
                                                                <span class="text-muted font-size-13 text-truncate">Re-edit your messages</span>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                            </div>
                                                        </div>
                                                    </a>
            
                                                    <a href="#" class="border-bottom">
                                                        <div class="d-flex align-items-center">
                                                            <i class="mdi mdi-email-check-outline font-size-20 align-middle me-3"></i>
                                                            <div class="flex-grow-1">
                                                                <h5 class="font-size-14 mb-0">Sent Mail</h5>
                                                                <span class="text-muted font-size-13 text-truncate">Successfully messages</span>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <div class="float-end">
                                                                    <span class="bg-success badge">08</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <a href="#">
                                                        <div class="d-flex align-items-center">
                                                            <i class="mdi mdi-trash-can-outline font-size-20 align-middle me-3"></i>
                                                            <div class="flex-grow-1">
                                                                <h5 class="font-size-14 mb-0">Trash</h5>
                                                                <span class="text-muted font-size-13 text-truncate">Removed messages</span>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>

                                            <h5 class="mt-3 font-size-15 text-uppercase">Labels</h5>

                                            <div class="card p-0 overflow-hidden mt-3 shadow-none">
                                                <div class="mail-list">
                                                    <a href="#" class="border-bottom"><span class="mdi mdi-arrow-right-drop-circle text-info float-end"></span>Theme Support</a>
                                                    <a href="#" class="border-bottom"><span class="mdi mdi-arrow-right-drop-circle text-warning float-end"></span>Freelance</a>
                                                    <a href="#" class="border-bottom"><span class="mdi mdi-arrow-right-drop-circle text-primary float-end"></span>Social</a>
                                                    <a href="#" class="border-bottom"><span class="mdi mdi-arrow-right-drop-circle text-danger float-end"></span>Friends</a>
                                                    <a href="#"><span class="mdi mdi-arrow-right-drop-circle text-success float-end"></span>Family</a>
                                                </div>
                                            </div>

                                            <h5 class="mt-3 font-size-15 text-uppercase">Chat</h5>

                                            <div class="card p-0 overflow-hidden mt-3 mb-1 shadow-none">
                                                <div class="mail-list">
                                                    @forelse (($emails ?? collect())->take(4) as $chatMail)
                                                    <a href="javascript: void(0);" class="d-flex align-items-start border-bottom">
                                                        <img class="flex-shrink-0 me-3 rounded-circle" src="{{ $chatMail?->avatar ?? 'assets/images/users/avatar-2.jpg' }}" alt="Generic placeholder image" height="36">
                                                        <div class="flex-grow-1 chat-user-box">
                                                            <p class="user-title m-0">{{ $chatMail?->from_name ?? 'User' }}</p>
                                                            <p class="text-muted mb-0 text-truncate">{{ \Illuminate\Support\Str::limit((string) ($chatMail?->subject ?? $chatMail?->body ?? ''), 28) }}</p>
                                                        </div>
                                                    </a>
                                                    @empty
                                                    <a href="javascript: void(0);" class="d-flex align-items-start border-bottom">
                                                        <img class="flex-shrink-0 me-3 rounded-circle" src="assets/images/users/avatar-2.jpg" alt="Generic placeholder image" height="36">
                                                        <div class="flex-grow-1 chat-user-box">
                                                            <p class="user-title m-0">No messages</p>
                                                            <p class="text-muted mb-0">-</p>
                                                        </div>
                                                    </a>
                                                    @endforelse
                                                </div>
                                            </div>

                                        </div>
                                    </div>
        
                                </div>
                                <!-- End Left sidebar -->

                                <!-- Right Sidebar -->
                                <div class="email-rightbar mb-3">

                                    <div class="card">
                                        <div class="card-body">

                                            <div class="">
                                                <div class="row mb-4">
                                                <div class="col-xl-3 col-md-12">
                                                    <div class="pb-3 pb-xl-0">
                                                        <form class="email-search">
                                                            <div class="position-relative">
                                                                <input type="text" class="form-control bg-light" placeholder="Search...">
                                                                <span class="bx bx-search font-size-18"></span>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="col-xl-9 col-md-12">
                                                    <div class="pb-3 pb-xl-0">
                                                        <div class="btn-toolbar float-end" role="toolbar">
                                                            <div class="btn-group me-2 mb-2">
                                                                <button type="button" class="btn btn-primary waves-light waves-effect"><i class="fa fa-inbox"></i></button>
                                                                <button type="button" class="btn btn-primary waves-light waves-effect"><i class="fa fa-exclamation-circle"></i></button>
                                                                <button type="button" class="btn btn-primary waves-light waves-effect"><i class="far fa-trash-alt"></i></button>
                                                            </div>
                                                            <div class="btn-group me-2 mb-2">
                                                                <button type="button" class="btn btn-primary waves-light waves-effect dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="fa fa-folder"></i> <i class="mdi mdi-chevron-down ms-1"></i>
                                                                </button>
                                                                <div class="dropdown-menu">
                                                                    <a class="dropdown-item" href="#">Updates</a>
                                                                    <a class="dropdown-item" href="#">Social</a>
                                                                    <a class="dropdown-item" href="#">Team Manage</a>
                                                                </div>
                                                            </div>
                                                            <div class="btn-group me-2 mb-2">
                                                                <button type="button" class="btn btn-primary waves-light waves-effect dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="fa fa-tag"></i> <i class="mdi mdi-chevron-down ms-1"></i>
                                                                </button>
                                                                <div class="dropdown-menu">
                                                                    <a class="dropdown-item" href="#">Updates</a>
                                                                    <a class="dropdown-item" href="#">Social</a>
                                                                    <a class="dropdown-item" href="#">Team Manage</a>
                                                                </div>
                                                            </div>
                            
                                                            <div class="btn-group me-2 mb-2">
                                                                <button type="button" class="btn btn-primary waves-light waves-effect dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    More <i class="mdi mdi-dots-vertical ms-2"></i>
                                                                </button>
                                                                <div class="dropdown-menu">
                                                                    <a class="dropdown-item" href="#">Mark as Unread</a>
                                                                    <a class="dropdown-item" href="#">Mark as Important</a>
                                                                    <a class="dropdown-item" href="#">Add to Tasks</a>
                                                                    <a class="dropdown-item" href="#">Add Star</a>
                                                                    <a class="dropdown-item" href="#">Mute</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                </div>

                                        <div>
                                            <h6 class="text-muted text-uppercase mb-3">Today</h6>
                                                <div class="mb-2">
                                                    @forelse ($emails as $email)
                                                    <div class="message-list mb-0 p-1 {{ $email->is_read ? '' : 'unread' }}">
                                                        <div class="list">
                                                            <div class="col-mail col-mail-1">
                                                                <div class="checkbox-wrapper-mail">
                                                                    <input type="checkbox" id="chk{{ $email->id }}">
                                                                    <label for="chk{{ $email->id }}" class="toggle"></label>
                                                                </div>
                                                                <div class="d-flex title align-items-center">
                                                                    <img src="{{ $email->avatar }}" class="avatar-sm rounded-circle" alt="">
                                                                    <div class="flex-1 ms-2 ps-1">
                                                                        <h5 class="font-size-14 mb-0"><a href="{{ route('email-read', $email) }}" class="text-body">{{ $email->from_name }}</a></h5>
                                                                        <a href="{{ route('email-read', $email) }}" class="text-muted text-uppercase font-size-12">{{ $email->threads }} Threads</a>
                                                                    </div>
                                                                </div>
                                                                <span class="star-toggle far fa-star"></span>
                                                            </div>
                                                            <div class="col-mail col-mail-2">
                                                                <a href="{{ route('email-read', $email) }}" class="subject text-body">{{ $email->subject }} – <span class="teaser text-muted fw-normal">{{ $email->teaser }}</span>
                                                                </a>
                                                                <div class="date"><i class="mdi mdi-link-variant me-2 font-size-15 align-middle"></i> {{ $email->time_label }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @empty
                                                    <div class="text-center text-muted py-4">No emails.</div>
                                                    @endforelse
                                                </div>
                                                <div class="row">
                                                <div class="col-7">
                                                    Showing {{ $emails->first() ? 1 : 0 }} - {{ $emails->count() }} of {{ $inboxCount }}
                                                </div>
                                                <div class="col-5">
                                                    <div class="btn-group float-end">
                                                        <button type="button" class="btn btn-sm btn-success waves-effect"><i class="fa fa-chevron-left"></i></button>
                                                        <button type="button" class="btn btn-sm btn-success waves-effect"><i class="fa fa-chevron-right"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                   </div>

                                        </div>
                                    </div>
        
                                </div> <!-- end Col-9 -->
        
                            </div>
                        </div><!-- End row -->

                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                <!-- Modal -->
                <div class="modal fade" id="composemodal" tabindex="-1" role="dialog" aria-labelledby="composemodalTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="composemodalTitle">New Message</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                </button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="{{ route('email.compose') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <input type="email" name="to_email" class="form-control" placeholder="To" required>
                                    </div>

                                    <div class="mb-3">
                                        <input type="text" name="subject" class="form-control" placeholder="Subject">
                                    </div>
                                    <div class="mb-3">
                                        <div id="email-editor"></div>
                                        <textarea name="body" class="d-none" id="email-body-field"></textarea>
                                    </div>

                                    <div class="modal-footer px-0 pb-0">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Send <i class="fab fa-telegram-plane ms-1"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">
                                <script>document.write(new Date().getFullYear())</script> © Cyprus Digital Solution.
                            </div>
                            <div class="col-sm-6">
                                <div class="text-sm-end d-none d-sm-block">
                                    Crafted with <i class="mdi mdi-heart text-danger"></i> by <a href="https://themesdesign.in/" target="_blank" class="text-reset">Themesdesign</a>
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
        </div>

        <!-- JAVASCRIPT -->
        <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/libs/metismenujs/metismenujs.min.js"></script>
        <script src="assets/libs/simplebar/simplebar.min.js"></script>
        <script src="assets/libs/eva-icons/eva.min.js"></script>

        <!-- ckeditor -->
        <script src="assets/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js"></script>

        <!-- email editor init -->
        <script src="assets/js/pages/email-editor.init.js"></script>

        <script src="assets/js/app.js"></script>

    </body>

</html>
