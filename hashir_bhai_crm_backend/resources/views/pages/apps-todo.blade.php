<!doctype html>
<html lang="en">

    <head>

        <meta charset="utf-8" />
        <title>Todo | Cyprus Digital Solution</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Cyprus Digital Solution CRM & ERP" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon-cyprus.svg">

        <!-- datepicker css -->
        <link rel="stylesheet" href="assets/libs/flatpickr/flatpickr.min.css">

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
                            <div class="col-xl-8">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="">
                                            <div class="row mb-2">
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
                                                    <div class="text-sm-end">
                                                        <button type="button" class="btn btn-success btn-rounded waves-effect waves-light mb-2 me-2" data-bs-toggle="modal" data-bs-target=".create-task"><i class="mdi mdi-plus me-1"></i> Create Task</button>
                                                     </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-nowrap align-middle mb-0">
                                                <tbody>
                                                    @foreach ($todos as $index => $todo)
                                                    <tr>
                                                        <td style="width: 40px;">
                                                            <div class="form-check font-size-16">
                                                                <form method="POST" action="{{ route('apps-todo.update', $todo) }}" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="is_completed" value="{{ $todo->is_completed ? 0 : 1 }}">
                                                                    <input class="form-check-input" type="checkbox" id="upcomingtaskCheck{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}" onchange="this.form.submit()" {{ $todo->is_completed ? 'checked' : '' }}>
                                                                </form>
                                                                <label class="form-check-label" for="upcomingtaskCheck{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}"></label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <h5 class="text-truncate font-size-14 m-0"><a href="javascript: void(0);" class="text-body">{{ $todo->title }}</a></h5>
                                                        </td>
                                                        <td>
                                                            <p class="mb-0">
                                                                <i class="mdi mdi-comment-outline align-middle font-size-16 me-1"></i> {{ $todo->comments }}
                                                            </p>
                                                        </td>
                                                        <td>
                                                            <p class="mb-0">{{ $todo->team }}</p>
                                                        </td>
                                                        <td>
                                                            <div class="text-center">
                                                                @php
                                                                    $statusClass = match ($todo->status) {
                                                                        'Approved' => 'bg-primary-subtle text-primary',
                                                                        'Completed' => 'bg-success-subtle text-success',
                                                                        default => 'bg-secondary-subtle text-secondary',
                                                                    };
                                                                @endphp
                                                                <span class="badge rounded-pill {{ $statusClass }}  font-size-11">{{ $todo->status }}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-group">
                                                                @foreach (($todo->members ?? []) as $member)
                                                                <div class="avatar-group-item">
                                                                    <a href="javascript: void(0);" class="d-inline-block">
                                                                        @if (! empty($member['avatar']))
                                                                            <img src="{{ $member['avatar'] }}" alt="" class="rounded-circle avatar-sm">
                                                                        @else
                                                                            <div class="avatar-sm">
                                                                                <span class="avatar-title rounded-circle {{ $member['color'] ?? 'bg-primary' }} text-white font-size-16">
                                                                                    {{ $member['letter'] ?? 'U' }}
                                                                                </span>
                                                                            </div>
                                                                        @endif
                                                                    </a>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Avg. Team Time</h5>
                                    </div>
                                    <div class="card-body pt-0 pb-3">
                                        <div id="overview-chart" data-colors='["#1f58c7"]' class="apex-charts" dir="ltr"></div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Team Members</h5>
                                    </div>
                                    <div class="card-body pt-2">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-nowrap mb-1">
                                                <tbody>
                                                    <tr>
                                                        <td style="width: 50px;"><img src="assets/images/users/avatar-2.jpg" class="rounded-circle avatar-sm" alt=""></td>
                                                        <td><h5 class="font-size-14 m-0"><a href="javascript: void(0);" class="text-body">Daniel Canales</a></h5></td>
                                                        <td>
                                                            <div>
                                                                <a href="javascript: void(0);" class="badge bg-primary-subtle  text-primary font-size-11">Frontend</a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <i class="mdi mdi-circle-medium font-size-18 text-success align-middle me-1"></i> Online
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><img src="assets/images/users/avatar-1.jpg" class="rounded-circle avatar-sm" alt=""></td>
                                                        <td><h5 class="font-size-14 m-0"><a href="javascript: void(0);" class="text-body">Jennifer Walker</a></h5></td>
                                                        <td>
                                                            <div>
                                                                <a href="javascript: void(0);" class="badge bg-primary-subtle  text-primary font-size-11">UI / UX</a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <i class="mdi mdi-circle-medium font-size-18 text-warning align-middle me-1"></i> Buzy
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="avatar-sm">
                                                                <span class="avatar-title rounded-circle bg-primary text-white font-size-16">
                                                                    C
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td><h5 class="font-size-14 m-0"><a href="javascript: void(0);" class="text-body">Carl Mackay</a></h5></td>
                                                        <td>
                                                            <div>
                                                                <a href="javascript: void(0);" class="badge bg-primary-subtle  text-primary font-size-11">Backend</a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <i class="mdi mdi-circle-medium font-size-18 text-success align-middle me-1"></i> Online
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><img src="assets/images/users/avatar-4.jpg" class="rounded-circle avatar-sm" alt=""></td>
                                                        <td><h5 class="font-size-14 m-0"><a href="javascript: void(0);" class="text-body">Janice Cole</a></h5></td>
                                                        <td>
                                                            <div>
                                                                <a href="javascript: void(0);" class="badge bg-primary-subtle  text-primary font-size-11">Frontend</a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <i class="mdi mdi-circle-medium font-size-18 text-success align-middle me-1"></i> Online
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="avatar-sm">
                                                                <span class="avatar-title rounded-circle bg-primary text-white font-size-16">
                                                                    T
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td><h5 class="font-size-14 m-0"><a href="javascript: void(0);" class="text-body">Tony Brafford</a></h5></td>
                                                        <td>
                                                            <div>
                                                                <a href="javascript: void(0);" class="badge bg-primary-subtle  text-primary font-size-11">Backend</a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <i class="mdi mdi-circle-medium font-size-18 text-secondary align-middle me-1"></i> Offline
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

            <!--  Extra Large modal example -->
            <div class="modal fade create-task" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="myExtraLargeModalLabel">Create Task</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                           <form method="POST" action="{{ route('apps-todo.store') }}">
                            @csrf
                           <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="CreateTask-Task-Name">Task Name</label>
                                        <input type="text" name="title" class="form-control" placeholder="Enter Task Name" id="CreateTask-Task-Name" required>
                                    </div>
                                </div>
                               <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="CreateTask-Team-Member">Team Member</label>
                                        <input type="text" name="team" class="form-control" placeholder="Enter Team Member" id="CreateTask-Team-Member">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Due Date</label>
                                        <input type="text" name="due_date" class="form-control" placeholder="Select Due Date" id="CreateTask-due-date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="CreateTask-Category">Category</label>
                                        <select name="status" class="form-select">
                                            <option value="Waiting" selected> Select Category </option>
                                            <option value="Waiting">Waiting</option>
                                            <option value="Approved">Approved</option>
                                            <option value="Completed">Completed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label" for="CreateTask-Task Description">Task Description</label>
                                        <textarea class="form-control" id="projectdesc" name="description" rows="3" placeholder="Enter Task Description..."></textarea>
                                    </div>
                                </div>
                           </div>
                           <div class="row mt-2">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal"><i class="bx bx-x me-1 align-middle"></i> Cancel</button>
                                <button type="submit" class="btn btn-success" id="btn-save-event"><i class="bx bx-check me-1 align-middle"></i> Confirm</button>
                            </div>
                        </div>
                           </form>

                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->


            <!--  successfully modal  -->
            <div id="success-btn" class="modal fade" tabindex="-1" aria-labelledby="success-btnLabel" aria-hidden="true" data-bs-scroll="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                           <div class="text-center">
                               <i class="bx bx-check-circle display-1 text-success"></i>
                               <h4 class="mt-3">Task Completed Successfully</h4>
                           </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
                

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

        <!-- apexcharts -->
        <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

        <!-- datepicker js -->
        <script src="assets/libs/flatpickr/flatpickr.min.js"></script>

        <!--  -->
        <script src="assets/js/pages/todo.init.js"></script>

        <script src="assets/js/app.js"></script>

    </body>

</html>
