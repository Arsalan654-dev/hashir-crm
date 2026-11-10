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

        <!-- plugin css -->
        <link href="assets/libs/jsvectormap/css/jsvectormap.min.css" rel="stylesheet" type="text/css" />

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
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-body pb-0">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-4">Overview</h5>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="dropdown">
                                                    <a class="dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false">
                                                        <span class="fw-semibold">Sort By:</span>
                                                        <span class="text-muted">Yearly<i class="mdi mdi-chevron-down ms-1"></i></span>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Yearly</a>
                                                        <a class="dropdown-item" href="#">Monthly</a>
                                                        <a class="dropdown-item" href="#">Weekly</a>
                                                        <a class="dropdown-item" href="#">Today</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <div id="overview" data-colors='["#e6ecf9", "#e6ecf9", "#e6ecf9","#e6ecf9", "#e6ecf9", "#e6ecf9","#e6ecf9","#e6ecf9","#e6ecf9","#1f58c7","#1f58c7", "#1f58c7"]' class="apex-chart"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar">
                                                            <div class="avatar-title rounded bg-primary-subtle ">
                                                                <i class="bx bx-check-shield font-size-24 mb-0 text-primary"></i>
                                                            </div>
                                                        </div>

                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-0 font-size-15">Total Sales</h6>
                                                        </div>

                                                        <div class="flex-shrink-0">
                                                            <div class="dropdown">
                                                                <a class="dropdown-toggle" href="#"
                                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                                    aria-expanded="false">
                                                                    <i class="bx bx-dots-horizontal text-muted font-size-22"></i>
                                                                </a>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    <a class="dropdown-item" href="#">Yearly</a>
                                                                    <a class="dropdown-item" href="#">Monthly</a>
                                                                    <a class="dropdown-item" href="#">Weekly</a>
                                                                    <a class="dropdown-item" href="#">Today</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <h4 class="mt-4 pt-1 mb-0 font-size-22">{{ $totalSales }} <span class="text-success fw-medium font-size-13 align-middle"> <i class="mdi mdi-arrow-up"></i> 8.34% </span> </h4>
                                                        <div class="d-flex mt-1 align-items-end overflow-hidden">
                                                            <div class="flex-grow-1">
                                                                <p class="text-muted mb-0 text-truncate">Total Sales World Wide</p>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <div id="mini-1" data-colors='["#1f58c7"]' class="apex-charts"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar">
                                                            <div class="avatar-title rounded bg-primary-subtle ">
                                                                <i class="bx bx-cart-alt font-size-24 mb-0 text-primary"></i>
                                                            </div>
                                                        </div>

                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-0 font-size-15">Total Orders</h6>
                                                        </div>

                                                        <div class="flex-shrink-0">
                                                            <div class="dropdown">
                                                                <a class="dropdown-toggle" href="#"
                                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                                    aria-expanded="false">
                                                                    <i class="bx bx-dots-horizontal text-muted font-size-22"></i>
                                                                </a>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    <a class="dropdown-item" href="#">Yearly</a>
                                                                    <a class="dropdown-item" href="#">Monthly</a>
                                                                    <a class="dropdown-item" href="#">Weekly</a>
                                                                    <a class="dropdown-item" href="#">Today</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <h4 class="mt-4 pt-1 mb-0 font-size-22">{{ $totalOrders }} <span class="text-danger fw-medium font-size-13 align-middle"> <i class="mdi mdi-arrow-down"></i> 3.68% </span> </h4>
                                                        <div class="d-flex mt-1 align-items-end overflow-hidden">
                                                            <div class="flex-grow-1">
                                                                <p class="text-muted mb-0 text-truncate">Total Orders World Wide</p>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <div id="mini-2" data-colors='["#1f58c7"]' class="apex-charts"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar">
                                                            <div class="avatar-title rounded bg-primary-subtle ">
                                                                <i class="bx bx-package font-size-24 mb-0 text-primary"></i>
                                                            </div>
                                                        </div>

                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-0 font-size-15">Total Customers</h6>
                                                        </div>

                                                        <div class="flex-shrink-0">
                                                            <div class="dropdown">
                                                                <a class="dropdown-toggle" href="#"
                                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                                    aria-expanded="false">
                                                                    <i class="bx bx-dots-horizontal text-muted font-size-22"></i>
                                                                </a>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    <a class="dropdown-item" href="#">Yearly</a>
                                                                    <a class="dropdown-item" href="#">Monthly</a>
                                                                    <a class="dropdown-item" href="#">Weekly</a>
                                                                    <a class="dropdown-item" href="#">Today</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <h4 class="mt-4 pt-1 mb-0 font-size-22">{{ $totalCustomers }} <span class="text-danger fw-medium font-size-13 align-middle"> <i class="mdi mdi-arrow-down"></i> 2.64% </span> </h4>
                                                        <div class="d-flex mt-1 align-items-end overflow-hidden">
                                                            <div class="flex-grow-1">
                                                                <p class="text-muted mb-0 text-truncate">Total Customers World Wide</p>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <div id="mini-3" data-colors='["#1f58c7"]' class="apex-charts"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xl-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar">
                                                            <div class="avatar-title rounded bg-primary-subtle ">
                                                                <i class="bx bx-rocket font-size-24 mb-0 text-primary"></i>
                                                            </div>
                                                        </div>

                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-0 font-size-15">Total Products</h6>
                                                        </div>

                                                        <div class="flex-shrink-0">
                                                            <div class="dropdown">
                                                                <a class="dropdown-toggle" href="#"
                                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                                    aria-expanded="false">
                                                                    <i class="bx bx-dots-horizontal text-muted font-size-22"></i>
                                                                </a>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    <a class="dropdown-item" href="#">Yearly</a>
                                                                    <a class="dropdown-item" href="#">Monthly</a>
                                                                    <a class="dropdown-item" href="#">Weekly</a>
                                                                    <a class="dropdown-item" href="#">Today</a>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>

                                                    <div>
                                                        <h4 class="mt-4 pt-1 mb-0 font-size-22">{{ $totalProducts }} <span class="text-success fw-medium font-size-13 align-middle"> <i class="mdi mdi-arrow-down"></i> 5.79% </span> </h4>
                                                        <div class="d-flex mt-1 align-items-end overflow-hidden">
                                                            <div class="flex-grow-1">
                                                                <p class="text-muted mb-0 text-truncate">Total Products World Wide</p>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <div id="mini-4" data-colors='["#1f58c7"]' class="apex-charts"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                         <!-- end row -->

                        <div class="row">
                            <div class="col-xxl-8">
                                <div class="row">
                                    <div class="col-xl-7">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start mb-2">
                                                    <div class="flex-grow-1">
                                                        <h5 class="card-title">Popular Products</h5>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="dropdown">
                                                            <a class="dropdown-toggle text-muted" href="#"
                                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                Today<i class="mdi mdi-chevron-down ms-1"></i>
                                                            </a>

                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="#">Yearly</a>
                                                                <a class="dropdown-item" href="#">Monthly</a>
                                                                <a class="dropdown-item" href="#">Weekly</a>
                                                                <a class="dropdown-item" href="#">Today</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center">
                                                    <div class="col-md-5">
                                                    <div class="popular-product-img p-2">
                                                        <img src="./assets/images/product/img.png" alt="">
                                                    </div>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <span class="badge bg-primary-subtle text-primary  font-size-10 text-uppercase ls-05"> Popular Item</span>
                                                        <h5 class="mt-2 font-size-16"><a href="" class="text-body">Home & Office Chair Blue</a></h5>
                                                        <p class="text-muted">But who has any right to find chooses enjoy.</p>

                                                        <div class="row g-0 mt-3 pt-1 align-items-end">
                                                            <div class="col-4">
                                                                <div class="mt-1">
                                                                    <h4 class="font-size-16">800</h4>
                                                                    <p class="text-muted mb-1">Total Selling</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="mt-1">
                                                                    <h4 class="font-size-16">250</h4>
                                                                    <p class="text-muted mb-1">Total Stock</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="mt-1">
                                                                    <a href="" class="btn btn-primary btn-sm mb-1">Buy
                                                                        Now</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mx-n4" data-simplebar style="max-height: 205px;">
                                                        @foreach (($popularProducts ?? \App\Models\Product::query()->where('is_featured', true)->limit(5)->get()) as $popular)
                                                    <div class="popular-product-box rounded my-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <div class="avatar-md">
                                                                    <div class="product-img avatar-title img-thumbnail bg-primary-subtle  border-0">
                                                                        <img src="{{ $popular->image }}" class="img-fluid" alt="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3 overflow-hidden">
                                                                <h5 class="mb-1 text-truncate"><a href="/ecommerce-product-detail" class="font-size-15 text-body">{{ $popular->name }}</a></h5>
                                                                <p class="text-muted fw-semibold mb-0 text-truncate">${{ number_format((float) $popular->price, 2) }}</p>
                                                            </div>
                                                            <div class="flex-shrink-0 text-end ms-3">
                                                                <h5 class="mb-1"><a href="/ecommerce-product-detail" class="font-size-15 text-body">${{ number_format((float) $popular->price * max(1, $popular->stock ?? 1), 2) }}</a></h5>
                                                                <p class="text-muted fw-semibold mb-0">{{ $popular->stock ?? 0 }} Stock</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                    
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-5">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start mb-2">
                                                    <div class="flex-grow-1">
                                                        <h5 class="card-title">Loyal Customers</h5>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="dropdown">
                                                            <a class="dropdown-toggle text-muted" href="#"
                                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                <i class="bx bx-dots-horizontal font-size-22"></i>
                                                            </a>

                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="#">Yearly</a>
                                                                <a class="dropdown-item" href="#">Monthly</a>
                                                                <a class="dropdown-item" href="#">Weekly</a>
                                                                <a class="dropdown-item" href="#">Today</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mx-n4" data-simplebar style="max-height: 421px;">
                                                    @foreach (($loyalCustomers ?? \App\Models\Customer::query()->orderByDesc('rating')->limit(6)->get()) as $index => $loyal)
                                                    <div class="{{ $index === 0 ? 'pt-2 ' : '' }}{{ $index === 5 ? 'py-3' : 'border-bottom' }} loyal-customers-box">
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $loyal->avatar ? asset($loyal->avatar) : 'assets/images/users/avatar-4.jpg' }}" class="rounded-circle avatar img-thumbnail" alt="">
                                                            <div class="flex-grow-1 ms-3 overflow-hidden">
                                                                <h5 class="font-size-15 mb-1 text-truncate">{{ $loyal->name }}</h5>
                                                                <p class="text-muted text-truncate mb-0">{{ $loyal->email }}</p>
                                                            </div>
                                                            <div class="flex-shrink-0{{ $index === 0 ? '' : ' text-end' }}">
                                                                <h5 class="font-size-14 mb-0 text-truncate w-xs bg-light p-2 rounded text-center">
                                                                    {{ $loyal->rating }} <i class="bx bxs-star font-size-14 text-primary ms-1"></i></h5>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                    </div>
            
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xxl-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h5 class="card-title mb-4 text-truncate">Top Selling Categories</h5>
                                            </div>
                                            <div class="flex-shrink-0 ms-2">
                                                <div class="dropdown">
                                                    <a class="dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false">
                                                        <span class="fw-semibold">Sort By:</span> <span
                                                            class="text-muted">Weekly<i
                                                                class="mdi mdi-chevron-down ms-1"></i></span>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Yearly</a>
                                                        <a class="dropdown-item" href="#">Monthly</a>
                                                        <a class="dropdown-item" href="#">Weekly</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="saleing-categories" data-colors='["#1f58c7", "#4976cf","#6a92e1", "#e6ecf9"]' class="apex-charts" dir="ltr"></div> 

                                        <div class="row mt-3 pt-1">
                                            <div class="col-md-6">
                                                <div class="px-2 mt-2">
                                                    <div class="d-flex align-items-center mt-sm-0 mt-2">
                                                        <i class="mdi mdi-circle font-size-10 text-primary"></i>
                                                        <div class="flex-grow-1 ms-2 overflow-hidden">
                                                            <p class="font-size-15 mb-1 text-truncate">Men Fashion</p>
                                                        </div>
                                                        <div class="flex-shrink-0 ms-2">
                                                            <span class="fw-bold">34.3%</span>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-center mt-2">
                                                        <i class="mdi mdi-circle font-size-10 text-success"></i>
                                                        <div class="flex-grow-1 ms-2 overflow-hidden">
                                                            <p class="font-size-15 mb-0 text-truncate">Women Clothing</p>
                                                        </div>
                                                        <div class="flex-shrink-0 ms-2">
                                                            <span class="fw-bold">25.7%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="px-2 mt-2">
                                                    <div class="d-flex align-items-center mt-sm-0 mt-2">
                                                        <i class="mdi mdi-circle font-size-10 text-info"></i>
                                                        <div class="flex-grow-1 ms-2 overflow-hidden">
                                                            <p class="font-size-15 mb-1 text-truncate">Beauty Products</p>
                                                        </div>
                                                        <div class="flex-shrink-0 ms-2">
                                                            <span class="fw-bold">18.6%</span>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-center mt-2">
                                                        <i class="mdi mdi-circle font-size-10 text-secondary"></i>
                                                        <div class="flex-grow-1 ms-2 overflow-hidden">
                                                            <p class="font-size-15 mb-0 text-truncate">Others Products</p>
                                                        </div>
                                                        <div class="flex-shrink-0 ms-2">
                                                            <span class="fw-bold">21.4%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->

                        <div class="row"> 
                                <div class="col-xl-7">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="flex-grow-1">
                                                    <h5 class="card-title">Sales Revenue</h5>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="dropdown">
                                                        <a class="dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                            <span class="fw-semibold">Year:</span> <span
                                                                class="text-muted">2021<i
                                                                    class="mdi mdi-chevron-down ms-1"></i></span>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item" href="#">2019</a>
                                                            <a class="dropdown-item" href="#">2020</a>
                                                            <a class="dropdown-item" href="#">2021</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row align-items-center">
                                                <div class="col-xxl-7">
                                                    <div class="py-3">
                                                        <div id="world-map-markers" style="height: 300px"></div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-5">
                                                    <div class="table-responsive">
                                                            <table class="table table-centered align-middle table-nowrap mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width: 500px;">Countries</th>
                                                                        <th>Orders</th>
                                                                        <th>Earnings</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <img src="assets/images/flags/us.jpg" class="rounded" alt="user-image" height="18">
                                                                                <div class="flex-grow-1 ms-3">
                                                                                    <p class="mb-0 text-truncate">United States</p>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>46k</td>
                                                                        <td>$6,524.30</td>
                                                                    </tr>
                    
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <img src="assets/images/flags/italy.jpg" class="rounded" alt="user-image" height="18">
                                                                                <div class="flex-grow-1 ms-3">
                                                                                    <p class="mb-0 text-truncate">Italy</p>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>86k</td>
                                                                        <td>$6,985.94</td>
                                                                    </tr>
                    
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <img src="assets/images/flags/spain.jpg" class="rounded" alt="user-image" height="18">
                                                                                <div class="flex-grow-1 ms-3">
                                                                                    <p class="mb-0 text-truncate">Spain</p>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>86k</td>
                                                                        <td>$5,685.47</td>
                                                                    </tr>
                    
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <img src="assets/images/flags/french.jpg" class="rounded" alt="user-image" height="18">
                                                                                <div class="flex-grow-1 ms-3">
                                                                                    <p class="mb-0 text-truncate">French</p>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>56k</td>
                                                                        <td>$5,645.45</td>
                                                                    </tr>
                                                                
                                                                </tbody>
                                                            </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-5">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap align-items-center mb-3">
                                                <h5 class="card-title me-2">Invoice List</h5>
                                                <div class="ms-auto">
                                                    <div class="dropdown">
                                                        <a class="dropdown-toggle text-reset" href="#" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text-muted font-size-12">Sort By: </span> <span class="fw-medium"> Weekly<i class="mdi mdi-chevron-down ms-1"></i></span>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                                                            <a class="dropdown-item" href="#">Monthly</a>
                                                            <a class="dropdown-item" href="#">Yearly</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mx-n4" data-simplebar style="max-height: 332px;">
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-centered align-middle table-nowrap mb-0 table-check">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 30px;">
                                                                    <div class="form-check font-size-16">
                                                                        <input type="checkbox" name="check" class="form-check-input" id="checkAll">
                                                                        <label class="form-check-label" for="checkAll"></label>
                                                                    </div>
                                                                </th>
                                                                <th>#Invoice</th>
                                                                <th style="width: 190px;">User Name</th>
                                                                <th>Date</th>
                                                                <th>Status</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <div class="form-check font-size-16">
                                                                        <input type="checkbox" class="form-check-input">
                                                                        <label class="form-check-label"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="fw-semibold">#562354</td>
                                                                <td style="width: 190px;">
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="rounded-circle avatar-sm" src="assets/images/users/avatar-1.jpg" alt="">
                                                                        <div class="flex-grow-1 ms-3">
                                                                            Neal Matthews
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                10 Dec
                                                                </td>
                                                                <td>
                                                                    <div class="badge bg-success-subtle text-success  font-size-12">Paid</div>
                                                                </td>
                                                                
                                                                <td>
                                                                    <div class="dropdown">
                                                                        <a class="text-muted dropdown-toggle font-size-18" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                            <i class="mdi mdi-dots-horizontal"></i>
                                                                        </a>
                    
                                                                        <div class="dropdown-menu dropdown-menu-end">
                                                                            <a class="dropdown-item" href="#">Edit</a>
                                                                            <a class="dropdown-item" href="#">Print</a>
                                                                            <a class="dropdown-item" href="#">Delete</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>   
                                                            
                                                            <tr>
                                                                <td>
                                                                    <div class="form-check font-size-16">
                                                                        <input type="checkbox" class="form-check-input">
                                                                        <label class="form-check-label"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="fw-semibold">#485625</td>
                                                                <td style="width: 190px;">
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="rounded-circle avatar-sm" src="assets/images/users/avatar-2.jpg" alt="">
                                                                        <div class="flex-grow-1 ms-3">
                                                                            Connie Franco
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                10 Dec
                                                                </td>
                                                                <td>
                                                                    <div class="badge bg-success-subtle text-success  font-size-12">Paid</div>
                                                                </td>
                                                                
                                                                <td>
                                                                    <div class="dropdown">
                                                                        <a class="text-muted dropdown-toggle font-size-18" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                            <i class="mdi mdi-dots-horizontal"></i>
                                                                        </a>
                    
                                                                        <div class="dropdown-menu dropdown-menu-end">
                                                                            <a class="dropdown-item" href="#">Edit</a>
                                                                            <a class="dropdown-item" href="#">Print</a>
                                                                            <a class="dropdown-item" href="#">Delete</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>  
    
                                                            <tr>
                                                                <td>
                                                                    <div class="form-check font-size-16">
                                                                        <input type="checkbox" class="form-check-input">
                                                                        <label class="form-check-label"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="fw-semibold">#321458</td>
                                                                <td style="width: 190px;">
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="rounded-circle avatar-sm" src="assets/images/users/avatar-3.jpg" alt="">
                                                                        <div class="flex-grow-1 ms-3">
                                                                            Adella Perez
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                12 Dec
                                                                </td>
                                                                <td>
                                                                    <div class="badge bg-danger-subtle text-danger  font-size-12">Unpaid</div>
                                                                </td>
                                                                
                                                                <td>
                                                                    <div class="dropdown">
                                                                        <a class="text-muted dropdown-toggle font-size-18" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                            <i class="mdi mdi-dots-horizontal"></i>
                                                                        </a>
                    
                                                                        <div class="dropdown-menu dropdown-menu-end">
                                                                            <a class="dropdown-item" href="#">Edit</a>
                                                                            <a class="dropdown-item" href="#">Print</a>
                                                                            <a class="dropdown-item" href="#">Delete</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr> 
    
                                                            <tr>
                                                                <td>
                                                                    <div class="form-check font-size-16">
                                                                        <input type="checkbox" class="form-check-input">
                                                                        <label class="form-check-label"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="fw-semibold">#214569</td>
                                                                <td style="width: 190px;">
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="rounded-circle avatar-sm" src="assets/images/users/avatar-4.jpg" alt="">
                                                                        <div class="flex-grow-1 ms-3">
                                                                            Theresa Mayers
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                21 Dec
                                                                </td>
                                                                <td>
                                                                    <div class="badge bg-success-subtle text-success  font-size-12">Paid</div>
                                                                </td>
                                                                
                                                                <td>
                                                                    <div class="dropdown">
                                                                        <a class="text-muted dropdown-toggle font-size-18" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                            <i class="mdi mdi-dots-horizontal"></i>
                                                                        </a>
                    
                                                                        <div class="dropdown-menu dropdown-menu-end">
                                                                            <a class="dropdown-item" href="#">Edit</a>
                                                                            <a class="dropdown-item" href="#">Print</a>
                                                                            <a class="dropdown-item" href="#">Delete</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr> 
    
                                                            <tr>
                                                                <td>
                                                                    <div class="form-check font-size-16">
                                                                        <input type="checkbox" class="form-check-input">
                                                                        <label class="form-check-label"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="fw-semibold">#565423</td>
                                                                <td style="width: 190px;">
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="rounded-circle avatar-sm" src="assets/images/users/avatar-5.jpg" alt="">
                                                                        <div class="flex-grow-1 ms-3">
                                                                            Oliver Gonzales
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                25 Dec
                                                                </td>
                                                                <td>
                                                                    <div class="badge bg-danger-subtle text-danger  font-size-12">Unpaid</div>
                                                                </td>
                                                                
                                                                <td>
                                                                    <div class="dropdown">
                                                                        <a class="text-muted dropdown-toggle font-size-18" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                            <i class="mdi mdi-dots-horizontal"></i>
                                                                        </a>
                    
                                                                        <div class="dropdown-menu dropdown-menu-end">
                                                                            <a class="dropdown-item" href="#">Edit</a>
                                                                            <a class="dropdown-item" href="#">Print</a>
                                                                            <a class="dropdown-item" href="#">Delete</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr> 
    
                                                            <tr>
                                                                <td>
                                                                    <div class="form-check font-size-16">
                                                                        <input type="checkbox" class="form-check-input">
                                                                        <label class="form-check-label"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="fw-semibold">#565423</td>
                                                                <td style="width: 190px;">
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="rounded-circle avatar-sm" src="assets/images/users/avatar-6.jpg" alt="">
                                                                        <div class="flex-grow-1 ms-3">
                                                                            Willie Verner
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                30 Dec
                                                                </td>
                                                                <td>
                                                                    <div class="badge bg-success-subtle text-success  font-size-12">Paid</div>
                                                                </td>
                                                                
                                                                <td>
                                                                    <div class="dropdown">
                                                                        <a class="text-muted dropdown-toggle font-size-18" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                            <i class="mdi mdi-dots-horizontal"></i>
                                                                        </a>
                    
                                                                        <div class="dropdown-menu dropdown-menu-end">
                                                                            <a class="dropdown-item" href="#">Edit</a>
                                                                            <a class="dropdown-item" href="#">Print</a>
                                                                            <a class="dropdown-item" href="#">Delete</a>
                                                                        </div>
                                                                    </div>
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
                        <!-- end row -->

                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

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

        <!-- Vector map-->
        <script src="assets/libs/jsvectormap/js/jsvectormap.min.js"></script>
        <script src="assets/libs/jsvectormap/maps/world-merc.js"></script>
        
        <script src="assets/js/pages/dashboard.init.js"></script>

        <script src="assets/js/app.js"></script>

    </body>

</html>
