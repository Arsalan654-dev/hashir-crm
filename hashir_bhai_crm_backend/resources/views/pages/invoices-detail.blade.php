<!doctype html>
<html lang="en">

    <head>

        <meta charset="utf-8" />
        <title>Invoice Detail | Cyprus Digital Solution</title>
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
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="invoice-title">
                                            <h4 class="float-end font-size-15">Invoice {{ $invoice?->invoice_no ?? '#N/A' }} <span class="badge bg-success font-size-12 ms-2">{{ $invoice?->status ?? '' }}</span></h4>
                                            <div class="mb-4">
                                                <img src="assets/images/logo-cyprus-mark.svg" alt="logo" height="34"/>
                                            </div>
                                            <div class="text-muted">
                                                <p class="mb-1">{{ $invoice?->address ?? '' }}</p>
                                                <p class="mb-1"><i class="mdi mdi-email-outline me-1"></i> {{ $invoice?->email ?? '' }}</p>
                                                <p><i class="mdi mdi-phone-outline me-1"></i> {{ $invoice?->phone ?? '' }}</p>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="text-muted">
                                                    <h5 class="font-size-16 mb-3">Billed To:</h5>
                                                    <h5 class="font-size-15 mb-2">{{ $invoice?->billing_name ?? '' }}</h5>
                                                    <p class="mb-1">{{ $invoice?->address ?? '' }}</p>
                                                    <p class="mb-1">{{ $invoice?->email ?? '' }}</p>
                                                    <p>{{ $invoice?->phone ?? '' }}</p>
                                                </div>
                                            </div>
                                            <!-- end col -->
                                            <div class="col-sm-6">
                                                <div class="text-muted text-sm-end">
                                                    <div>
                                                        <h5 class="font-size-15 mb-1">Invoice No:</h5>
                                                        <p>{{ $invoice?->invoice_no ?? '' }}</p>
                                                    </div>
                                                    <div class="mt-4">
                                                        <h5 class="font-size-15 mb-1">Invoice Date:</h5>
                                                        <p>{{ $invoice?->invoice_date?->format('d M, Y') ?? '' }}</p>
                                                    </div>
                                                    <div class="mt-4">
                                                        <h5 class="font-size-15 mb-1">Order No:</h5>
                                                        <p>#1123456</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- end col -->
                                        </div>
                                        <!-- end row -->
                                        
                                        <div class="py-2">
                                            <h5 class="font-size-15">Order Summary</h5>

                                            <div class="table-responsive">
                                                <table class="table align-middle table-nowrap table-centered mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th class="fw-bold" style="width: 70px;">No.</th>
                                                            <th class="fw-bold">Item</th>
                                                            <th class="fw-bold">Price</th>
                                                            <th class="fw-bold">Quantity</th>
                                                            <th class="text-end fw-bold" style="width: 120px;">Total</th>
                                                        </tr>
                                                    </thead><!-- end thead -->
                                                    <tbody>
                                                        @foreach ($items as $index => $item)
                                                        <tr>
                                                            <th scope="row">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</th>
                                                            <td>
                                                                <div>
                                                                    <h5 class="text-truncate font-size-14 mb-1">{{ $item->description ?? 'Item' }}</h5>
                                                                    <p class="text-muted mb-0">{{ $item->description ?? '' }}</p>
                                                                </div>
                                                            </td>
                                                            <td>$ {{ number_format((float) ($item->price ?? 0), 2) }}</td>
                                                            <td>{{ $item->quantity ?? 1 }}</td>
                                                            <td class="text-end">$ {{ number_format((float) (($item->price ?? 0) * ($item->quantity ?? 1)), 2) }}</td>
                                                        </tr>
                                                        @endforeach
                                                        @php
                                                            $sub = (float) $items->sum(fn ($i) => ((float) ($i->price ?? 0)) * ((int) ($i->quantity ?? 1)));
                                                            $tax = $sub * 0.1;
                                                            $ship = $sub > 0 ? 20.0 : 0.0;
                                                            $total = $sub + $tax + $ship;
                                                        @endphp
                                                        <tr>
                                                            <th scope="row" colspan="4" class="text-end fw-bold">Sub Total</th>
                                                            <td class="text-end">$ {{ number_format($sub, 2) }}</td>
                                                        </tr>
                                                        <!-- end tr -->
                                                        <tr>
                                                            <th scope="row" colspan="4" class="border-0 text-end fw-bold">
                                                                Discount :</th>
                                                            <td class="border-0 text-end">- $0.00</td>
                                                        </tr>
                                                        <!-- end tr -->
                                                        <tr>
                                                            <th scope="row" colspan="4" class="border-0 text-end fw-bold">
                                                                Shipping Charge :</th>
                                                            <td class="border-0 text-end">$ {{ number_format($ship, 2) }}</td>
                                                        </tr>
                                                        <!-- end tr -->
                                                        <tr>
                                                            <th scope="row" colspan="4" class="border-0 text-end fw-bold">
                                                                Tax</th>
                                                            <td class="border-0 text-end">$ {{ number_format($tax, 2) }}</td>
                                                        </tr>
                                                        <!-- end tr -->
                                                        <tr>
                                                            <th scope="row" colspan="4" class="border-0 text-end fw-bold">Total</th>
                                                            <td class="border-0 text-end"><h4 class="m-0 fw-semibold">$ {{ number_format($total, 2) }}</h4></td>
                                                        </tr>
                                                        <!-- end tr -->
                                                    </tbody><!-- end tbody -->
                                                </table><!-- end table -->
                                            </div><!-- end table responsive -->
                                            <div class="d-print-none mt-4">
                                                <div class="float-end">
                                                    <a href="javascript:window.print()" class="btn btn-success me-1"><i class="fa fa-print"></i></a>
                                                    <a href="#" class="btn btn-primary w-md">Send</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end col -->
                        </div><!-- end row -->
                       

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

        <script src="assets/js/app.js"></script>

    </body>

</html>
