<!doctype html>
<html lang="en">

    <head>

        <meta charset="utf-8" />
        <title>File Manager | Jaffri's CRM</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Jaffri's CRM CRM & ERP" name="description" />
        <meta content="Arsalan Jaffri" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon-jaffri.png">

        <!-- dropzone css -->
        <link href="assets/libs/dropzone/dropzone.css" rel="stylesheet" type="text/css" />

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
                            <img src="assets/images/logo-jaffri-mark.png" alt="" height="26">
                        </span>
                        <span class="logo-lg">
                            <img src="assets/images/logo-jaffri.png" alt="" height="28">
                        </span>
                    </a>

                    <a href="/" class="logo logo-light">
                        <span class="logo-lg">
                            <img src="assets/images/logo-jaffri-light.png" alt="" height="30">
                        </span>
                        <span class="logo-sm">
                            <img src="assets/images/logo-jaffri-mark.png" alt="" height="26">
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

                        <div class="d-xl-flex">
                            <div class="w-100">
                                <div class="d-xl-flex">
                                    <div class="card filemanager-sidebar me-md-3">
                                        <div class="card-body">
                                            <div class="d-flex flex-column h-100">
                                                <div class="mb-4">
                                                    <div class="mb-3">
                                                        <div class="dropdown">
                                                            <button class="btn btn-primary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="mdi mdi-plus me-1"></i> Create New
                                                            </button>
                                                            <div class="dropdown-menu">
                                                              <a class="dropdown-item" href="#"><i class="bx bx-folder me-1"></i> Folder</a>
                                                              <a class="dropdown-item" href="#"><i class="bx bx-file me-1"></i> File</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <ul class="list-unstyled categories-list">
                                                        <li>
                                                            <div class="custom-accordion">
                                                                <a class="text-body fw-medium py-1 d-flex align-items-center" data-bs-toggle="collapse" href="#categories-collapse" role="button" aria-expanded="false" aria-controls="categories-collapse">
                                                                    <i class="mdi mdi-folder font-size-20 text-warning me-2"></i> My Files <i class="mdi mdi-chevron-up accor-down-icon ms-auto"></i>
                                                                </a>
                                                                <div class="collapse show" id="categories-collapse">
                                                                    <div class="card border-0 shadow-none ps-2 mb-0">
                                                                        <ul class="list-unstyled mb-0">
                                                                            <li><a href="#" class="d-flex align-items-center"><span class="me-auto">Analytics</span></a></li>
                                                                            <li><a href="#" class="d-flex align-items-center"><span class="me-auto">Design</span></a></li>
                                                                            <li><a href="#" class="d-flex align-items-center"><span class="me-auto">Development</span> <i class="mdi mdi-pin ms-auto"></i></a></li>
                                                                            <li><a href="#" class="d-flex align-items-center"><span class="me-auto">Project A</span></a></li>
                                                                            <li><a href="#" class="d-flex align-items-center"><span class="me-auto">Admin</span> <i class="mdi mdi-pin ms-auto"></i></a></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <a href="javascript: void(0);" class="text-body d-flex align-items-center">
                                                                <i class="mdi mdi-google-drive font-size-20 text-muted me-2"></i> <span class="me-auto">Google Drive</span> 
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript: void(0);" class="text-body d-flex align-items-center">
                                                                <i class="mdi mdi-dropbox font-size-20 me-2 text-primary"></i> <span class="me-auto">Dropbox</span>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript: void(0);" class="text-body d-flex align-items-center">
                                                                <i class="mdi mdi-share-variant font-size-20 me-2"></i> <span class="me-auto">Shared</span> <i class="mdi mdi-circle-medium text-danger ms-2"></i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript: void(0);" class="text-body d-flex align-items-center">
                                                                <i class="mdi mdi-star-outline text-muted font-size-20 me-2"></i> <span class="me-auto">Starred</span>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript: void(0);" class="text-body d-flex align-items-center">
                                                                <i class="mdi mdi-trash-can text-danger font-size-20 me-2"></i> <span class="me-auto">Trash</span>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript: void(0);" class="text-body d-flex align-items-center">
                                                                <i class="mdi mdi-cog text-muted font-size-20 me-2"></i> <span class="me-auto">Setting</span><span class="badge bg-success rounded-pill ms-2">01</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="mt-4 pt-3 mt-auto text-center">
                                                    <div class="card mb-0">
                                                        <div class="card-body">
                                                            <div class="px-4">
                                                                <img src="assets/images/Upgrade-img.png" class="img-fluid" alt="">
                                                            </div>
                                                            <h5 class="mt-4">Upgrade Features</h5>
                                                            <p class="pt-1">4 integrations, 30 team members, advanced features </p>
                                                            <div class="text-center pt-2">
                                                                <button type="button" class="btn btn-primary w-100">Upgrade <i class="mdi mdi-arrow-right ms-1"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <!-- filemanager-leftsidebar -->
        
                                    <div class="w-100">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="font-size-16 me-3 mb-0">My Files</h5>

                                                <div class="row mt-4">
                                                    <div class="col-xl-4 col-sm-6">
                                                        <div class="card">
                                                            <div class="card-body p-3">
                                                                <div class="">
                                                                    <div class="dropdown float-end">
                                                                        <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                            <i class="bx bx-dots-vertical-rounded font-size-20"></i>
                                                                        </a>
                                                                        <div class="dropdown-menu dropdown-menu-end">
                                                                            <a class="dropdown-item" href="#">Edit</a>
                                                                            <a class="dropdown-item" href="#">Action</a>
                                                                            <a class="dropdown-item" href="#">Remove</a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex align-items-center overflow-hidden">

                                                                        <div class="flex-shrink-0 me-3">
                                                                            <div class="avatar align-self-center">
                                                                                <div class="avatar-title rounded bg-primary-subtle  text-primary font-size-24">
                                                                                    <i class="mdi mdi-google-drive"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                       
                                                                        <div class="flex-grow-1">
                                                                            <h5 class="font-size-15 mb-1 text-truncate">Google Drive</h5>
                                                                            <a href="" class="font-size-14 text-muted text-truncate"><u>View Folder</u></a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mt-3 pt-1">
                                                                        <div class="d-flex justify-content-between">
                                                                            <p class="text-muted font-size-13 mb-1">15GB</p>
                                                                            <p class="text-muted font-size-13 mb-1 text-truncate">25GB used</p>
                                                                        </div>
                                                                        <div class="progress animated-progess custom-progress">
                                                                            <div class="progress-bar bg-gradient bg-primary" role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="90">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- end col -->
    
                                                    <div class="col-xl-4 col-sm-6">
                                                        <div class="card">
                                                            <div class="card-body p-3">

                                                                <div class="">
                                                                    <div class="dropdown float-end">
                                                                        <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                            <i class="bx bx-dots-vertical-rounded font-size-20"></i>
                                                                        </a>
                                                                        <div class="dropdown-menu dropdown-menu-end">
                                                                            <a class="dropdown-item" href="#">Edit</a>
                                                                            <a class="dropdown-item" href="#">Action</a>
                                                                            <a class="dropdown-item" href="#">Remove</a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex align-items-center overflow-hidden">

                                                                        <div class="flex-shrink-0 me-3">
                                                                            <div class="avatar align-self-center">
                                                                                <div class="avatar-title rounded bg-info-subtle  text-info font-size-24">
                                                                                    <i class="mdi mdi-dropbox"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                       
                                                                        <div class="flex-grow-1">
                                                                            <h5 class="font-size-15 mb-1 text-truncate">Dropbox</h5>
                                                                            <a href="" class="font-size-14 text-muted text-truncate"><u>View Folder</u></a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mt-3 pt-1">
                                                                        <div class="d-flex justify-content-between">
                                                                            <p class="text-muted font-size-13 mb-1">20GB</p>
                                                                            <p class="text-muted font-size-13 mb-1 text-truncate">50GB used</p>
                                                                        </div>
                                                                        <div class="progress animated-progess custom-progress">
                                                                            <div class="progress-bar bg-gradient bg-primary" role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="90">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- end col -->
    
                                                    <div class="col-xl-4 col-sm-6">
                                                        <div class="card">
                                                            <div class="card-body p-3">
                                                                <div class="">
                                                                    <div class="dropdown float-end">
                                                                        <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                            <i class="bx bx-dots-vertical-rounded font-size-20"></i>
                                                                        </a>
                                                                        <div class="dropdown-menu dropdown-menu-end">
                                                                            <a class="dropdown-item" href="#">Edit</a>
                                                                            <a class="dropdown-item" href="#">Action</a>
                                                                            <a class="dropdown-item" href="#">Remove</a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex align-items-center overflow-hidden">

                                                                        <div class="flex-shrink-0 me-3">
                                                                            <div class="avatar align-self-center me-3">
                                                                                <div class="avatar-title rounded bg-primary-subtle  text-primary font-size-24">
                                                                                    <i class="mdi mdi-apple-icloud"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
    
                                                                        <div class="flex-grow-1">
                                                                            <h5 class="font-size-15 mb-1 text-truncate">One Drive</h5>
                                                                            <a href="" class="font-size-14 text-muted text-truncate"><u>View Folder</u></a>
                                                                        </div>
                                                                    
                                                                    </div>
                                                                    <div class="mt-3 pt-1">
                                                                        <div class="d-flex justify-content-between">
                                                                            <p class="text-muted font-size-13 mb-1">10GB</p>
                                                                            <p class="text-muted font-size-13 mb-1 text-truncate">20GB used</p>
                                                                        </div>
                                                                        <div class="progress animated-progess custom-progress">
                                                                            <div class="progress-bar bg-gradient bg-primary" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="70">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- end col -->
    
                                                </div>
                                                <!-- end row -->

                                                <h5 class="font-size-16 me-3 mb-0">Folders</h5>

                                                <div class="row mt-4">
                                                    <div class="col-xl-4 col-sm-6">
                                                        <div class="card">
                                                            <div class="card-body p-3">
                                                                <div class="">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <i class="bx bxs-folder h2 mb-0 text-warning"></i>
                                                                        </div>
                                                                        <div class="avatar-group">
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <img src="assets/images/users/avatar-4.jpg" alt="" class="rounded-circle avatar-sm">
                                                                                </a>
                                                                            </div>
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <div class="avatar-sm">
                                                                                        <span class="avatar-title rounded-circle bg-success text-white font-size-16">
                                                                                            A
                                                                                        </span>
                                                                                    </div>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex mt-3">
                                                                        <div class="overflow-hidden me-auto">
                                                                            <h5 class="font-size-15 text-truncate mb-1"><a href="javascript: void(0);" class="text-body">Analytics</a></h5>
                                                                            <p class="text-muted text-truncate mb-0">12 Files</p>
                                                                        </div>
                                                                        <div class="align-self-end ms-2">
                                                                            <p class="text-muted mb-0 font-size-13"><i class="bx bx-time-five align-middle me-1"></i> 15 min ago</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- end col -->
    
                                                    <div class="col-xl-4 col-sm-6">
                                                        <div class="card">
                                                            <div class="card-body p-3">
                                                                <div class="">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <i class="bx bxs-folder h2 mb-0 text-warning"></i>
                                                                        </div>
                                                                        <div class="avatar-group">
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <img src="assets/images/users/avatar-3.jpg" alt="" class="rounded-circle avatar-sm">
                                                                                </a>
                                                                            </div>
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <img src="assets/images/users/avatar-6.jpg" alt="" class="rounded-circle avatar-sm">
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex mt-3">
                                                                        <div class="overflow-hidden me-auto">
                                                                            <h5 class="font-size-15 text-truncate mb-1"><a href="javascript: void(0);" class="text-body">Sketch Design</a></h5>
                                                                            <p class="text-muted text-truncate mb-0">235 Files</p>
                                                                        </div>
                                                                        <div class="align-self-end ms-2">
                                                                            <p class="text-muted mb-0 font-size-13"><i class="bx bx-time-five align-middle me-1"></i> 23 min ago</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- end col -->
    
                                                    <div class="col-xl-4 col-sm-6">
                                                        <div class="card">
                                                            <div class="card-body p-3">
                                                                <div class="">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <i class="bx bxs-folder h2 mb-0 text-warning"></i>
                                                                        </div>
                                                                        <div class="avatar-group">
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <div class="avatar-sm">
                                                                                        <span class="avatar-title rounded-circle bg-info text-white font-size-16">
                                                                                            K
                                                                                        </span>
                                                                                    </div>
                                                                                </a>
                                                                            </div>
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <img src="assets/images/users/avatar-2.jpg" alt="" class="rounded-circle avatar-sm">
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex mt-3">
                                                                        <div class="overflow-hidden me-auto">
                                                                            <h5 class="font-size-15 text-truncate mb-1"><a href="javascript: void(0);" class="text-body">Applications</a></h5>
                                                                            <p class="text-muted text-truncate mb-0">20 Files</p>
                                                                        </div>
                                                                        <div class="align-self-end ms-2">
                                                                            <p class="text-muted mb-0 font-size-13"><i class="bx bx-time-five align-middle me-1"></i> 45 min ago</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- end col -->

                                                    <div class="col-xl-4 col-sm-6">
                                                        <div class="card">
                                                            <div class="card-body p-3">
                                                                <div class="">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <i class="bx bxs-folder h2 mb-0 text-warning"></i>
                                                                        </div>
                                                                        <div class="avatar-group">
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <img src="assets/images/users/avatar-6.jpg" alt="" class="rounded-circle avatar-sm">
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex mt-3">
                                                                        <div class="overflow-hidden me-auto">
                                                                            <h5 class="font-size-15 text-truncate mb-1"><a href="javascript: void(0);" class="text-body">Photoshop</a></h5>
                                                                            <p class="text-muted text-truncate mb-0">366 Files</p>
                                                                        </div>
                                                                        <div class="align-self-end ms-2">
                                                                            <p class="text-muted mb-0 font-size-13"><i class="bx bx-time-five align-middle me-1"></i> 5 hrs ago</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- end col -->
    
                                                    <div class="col-xl-4 col-sm-6">
                                                        <div class="card">
                                                            <div class="card-body p-3">
                                                                <div class="">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <i class="bx bxs-folder h2 mb-0 text-warning"></i>
                                                                        </div>
                                                                        <div class="avatar-group">
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <img src="assets/images/users/avatar-8.jpg" alt="" class="rounded-circle avatar-sm">
                                                                                </a>
                                                                            </div>
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <div class="avatar-sm">
                                                                                        <span class="avatar-title rounded-circle bg-danger text-white font-size-16">
                                                                                            3+
                                                                                        </span>
                                                                                    </div>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex mt-3">
                                                                        <div class="overflow-hidden me-auto">
                                                                            <h5 class="font-size-15 text-truncate mb-1"><a href="javascript: void(0);" class="text-body">Laravel Design</a></h5>
                                                                            <p class="text-muted text-truncate mb-0">185 Files</p>
                                                                        </div>
                                                                        <div class="align-self-end ms-2">
                                                                            <p class="text-muted mb-0 font-size-13"><i class="bx bx-time-five align-middle me-1"></i> 23 min ago</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- end col -->
    
                                                    <div class="col-xl-4 col-sm-6">
                                                        <div class="card">
                                                            <div class="card-body p-3">
                                                                <div class="">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <i class="bx bxs-folder h2 mb-0 text-warning"></i>
                                                                        </div>
                                                                        <div class="avatar-group">
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <img src="assets/images/users/avatar-6.jpg" alt="" class="rounded-circle avatar-sm">
                                                                                </a>
                                                                            </div>
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <img src="assets/images/users/avatar-5.jpg" alt="" class="rounded-circle avatar-sm">
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex mt-3">
                                                                        <div class="overflow-hidden me-auto">
                                                                            <h5 class="font-size-15 text-truncate mb-1"><a href="javascript: void(0);" class="text-body">Ecommerce</a></h5>
                                                                            <p class="text-muted text-truncate mb-0">56 Files</p>
                                                                        </div>
                                                                        <div class="align-self-end ms-2">
                                                                            <p class="text-muted mb-0 font-size-13"><i class="bx bx-time-five align-middle me-1"></i> 8 hrs ago</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- end col -->
    
                                                </div>
                                                <!-- End row -->

                                                <h5 class="font-size-16 me-3 mb-0">Recent Files</h5>

                                               
                                                <div class="mx-n4 mt-4" data-simplebar style="max-height: 350px;">
                                                    <div class="table-responsive">
                                                   
                                                        <table class="table align-middle table-nowrap table-hover mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                  <th scope="col">Name</th>
                                                                  <th scope="col">Date modified</th>
                                                                  <th scope="col">Size</th>
                                                                  <th scope="col" colspan="2">Members</th>
                                                                </tr>
                                                              </thead>
                                                            <tbody>
                                                                @foreach ($files as $fileItem)
                                                                <tr>
                                                                    <td><a href="javascript: void(0);" class="text-body fw-medium"><i class="{{ $fileItem->icon ?? 'bx bx-file' }} font-size-20 align-middle {{ $fileItem->icon_class ?? 'text-primary' }} me-2"></i> {{ $fileItem->name }}</a></td>
                                                                    <td>{{ $fileItem->date_label }}</td>
                                                                    <td>{{ $fileItem->size ?? '-' }}</td>
                                                                    <td>
                                                                        <div class="avatar-group">
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <img src="assets/images/users/avatar-4.jpg" alt="" class="rounded-circle avatar-sm">
                                                                                </a>
                                                                            </div>
                                                                            <div class="avatar-group-item">
                                                                                <a href="javascript: void(0);" class="d-inline-block">
                                                                                    <img src="assets/images/users/avatar-5.jpg" alt="" class="rounded-circle avatar-sm">
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <div class="dropdown">
                                                                            <a class="font-size-16 text-muted" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                                <i class="mdi mdi-dots-horizontal"></i>
                                                                            </a>
                                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                                <a class="dropdown-item" href="#">Open</a>
                                                                                <a class="dropdown-item" href="#">Edit</a>
                                                                                <a class="dropdown-item" href="#">Rename</a>
                                                                                <div class="dropdown-divider"></div>
                                                                                <form method="POST" action="{{ route('apps-file-manager.destroy', $fileItem) }}" class="d-inline">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit" class="dropdown-item">Remove</button>
                                                                                </form>
                                                                            </div>
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
                                        <!-- end card -->
                                    </div>
                                    <!-- end w-100 -->
                                </div>
                            </div>

                            <div class="card filemanager-sidebar ms-lg-3">
                                <div class="card-body">
                                    <div class="d-flex flex-column h-100">

                                        <div class="mb-4">
                                            <h5 class="font-size-16 me-3 mb-0">Usage Storage</h5>
                                            <div class="text-center mt-3">
                                                <div id="chart-radialBar" class="apex-charts" data-colors='["#312E81"]'></div>
                                                <p class="text-muted mt-3 pt-1">48.02 GB (76%) of 64 GB used</p>
                                            </div>

                                            <h5 class="font-size-16 mb-0 mt-5">Recent Files</h5>

                                            <div class="mt-4">
                                                <div class="pb-2 mb-2">
                                                    <a href="javascript: void(0);" class="text-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar align-self-center me-3">
                                                                <div class="avatar-title rounded bg-success-subtle  text-success font-size-24">
                                                                    <i class="mdi mdi-image"></i>
                                                                </div>
                                                            </div>
                                                            <div class="overflow-hidden me-auto">
                                                                <h5 class="font-size-15 text-truncate mb-1">Images</h5>
                                                                <p class="text-muted text-truncate mb-0">1,876 Files</p>
                                                            </div>
                                                            <div class="ms-2">
                                                                <p class="text-muted font-size-14">8.4 GB</p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>

                                                <div class="py-2 mb-2">
                                                    <a href="javascript: void(0);" class="text-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar align-self-center me-3">
                                                                <div class="avatar-title rounded bg-danger-subtle  text-danger font-size-24">
                                                                    <i class="mdi mdi-play-circle-outline"></i>
                                                                </div>
                                                            </div>
                                                            <div class="overflow-hidden me-auto">
                                                                <h5 class="font-size-15 text-truncate mb-1">Video</h5>
                                                                <p class="text-muted text-truncate mb-0">45 Files</p>
                                                            </div>
                                                            <div class="ms-2">
                                                                <p class="text-muted font-size-14">4.1 GB</p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>

                                                <div class="py-2 mb-2">
                                                    <a href="javascript: void(0);" class="text-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar align-self-center me-3">
                                                                <div class="avatar-title rounded bg-info-subtle  text-info font-size-24">
                                                                    <i class="mdi mdi-music"></i>
                                                                </div>
                                                            </div>
                                                            <div class="overflow-hidden me-auto">
                                                                <h5 class="font-size-15 text-truncate mb-1">Music</h5>
                                                                <p class="text-muted text-truncate mb-0">21 Files</p>
                                                            </div>
                                                            <div class="ms-2">
                                                                <p class="text-muted font-size-14">3.2 GB</p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>

                                                <div class="py-2 mb-2">
                                                    <a href="javascript: void(0);" class="text-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar align-self-center me-3">
                                                                <div class="avatar-title rounded bg-primary-subtle  text-primary font-size-24">
                                                                    <i class="mdi mdi-file-document"></i>
                                                                </div>
                                                            </div>
                                                            <div class="overflow-hidden me-auto">
                                                                <h5 class="font-size-15 text-truncate mb-1">Document</h5>
                                                                <p class="text-muted text-truncate mb-0">21 Files</p>
                                                            </div>
                                                            <div class="ms-2">
                                                                <p class="text-muted font-size-14">2 GB</p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>

                                                <div class="py-2 mb-2">
                                                    <a href="javascript: void(0);" class="text-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar align-self-center me-3">
                                                                <div class="avatar-title rounded bg-warning-subtle  text-warning font-size-24">
                                                                    <i class="mdi mdi-folder"></i>
                                                                </div>
                                                            </div>
                                                            <div class="overflow-hidden me-auto">
                                                                <h5 class="font-size-15 text-truncate mb-1">Others</h5>
                                                                <p class="text-muted text-truncate mb-0">20 Files</p>
                                                            </div>
                                                            <div class="ms-2">
                                                                <p class="text-muted font-size-14">1.4 GB</p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-5 mt-auto">
                                            <form action="#" class="dropzone">
                                                <div class="fallback">
                                                    <input name="file" type="file" multiple="multiple">
                                                </div>
                                                <div class="dz-message needsclick">
                                                    <div class="mb-3">
                                                        <i class="display-4 text-muted mdi mdi-cloud-upload"></i>
                                                    </div>
                                                    
                                                    <h5>Import Files</h5>
                                                </div>
                                            </form>
                                        </div>

                                    </div>


                                </div>
                            </div>
                        </div>
                        <!-- end row -->

                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">
                                <script>document.write(new Date().getFullYear())</script> © Jaffri's CRM.
                            </div>
                            <div class="col-sm-6">
                                <div class="text-sm-end d-none d-sm-block">
                                    Crafted with <i class="mdi mdi-heart text-danger"></i> by <a href="https://arsalan-portfolio-eight.vercel.app" target="_blank" class="text-reset">Arsalan Jaffri</a>
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

        <!-- dropzone plugin -->
        <script src="assets/libs/dropzone/dropzone-min.js"></script>

        <!-- file-manager js -->
        <script src="assets/js/pages/file-manager.init.js"></script>

        <script src="assets/js/app.js"></script>

    </body>

</html>
