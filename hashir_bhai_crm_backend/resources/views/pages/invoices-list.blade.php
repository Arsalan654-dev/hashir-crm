<!doctype html>
<html lang="en">

    <head>

        <meta charset="utf-8" />
        <title>Invoice List | Jaffri's CRM</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Jaffri's CRM CRM & ERP" name="description" />
        <meta content="Arsalan Jaffri" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon-jaffri.png">

        <!-- gridjs css -->
        <link rel="stylesheet" href="assets/libs/gridjs/theme/mermaid.min.css">

        <!-- flatpickr css -->
        <link href="assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css">

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

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="position-relative">
                                            <div class="modal-button mt-2">
                                                <div class="row align-items-start">
                                                    <div class="col-sm-auto">
                                                        <div class="d-flex gap-1">
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" id="datepicker-range">
                                                                <span class="input-group-text"><i class="bx bx-calendar-event"></i></span>
                                                            </div>
                                                            <div class="dropdown">
                                                                <a class="btn btn-link text-body shadow-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                                                </a>
                                                              
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li><a class="dropdown-item" href="#">Action</a></li>
                                                                    <li><a class="dropdown-item" href="#">Another action</a></li>
                                                                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm">
                                                        <div class="mt-3 mt-md-0 mb-3">
                                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addInvoiceModal"><i class="mdi mdi-plus me-1"></i> Add Invoice</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end row -->
                                            </div>
                                        </div>

                                      

                                        <div id="table-invoices-list"></div>

                                    </div>
                                    <!-- end card body -->
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->

                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                   <!-- Modal -->
                   <div class="modal fade" id="addInvoiceModal" tabindex="-1" aria-labelledby="addInvoiceModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addInvoiceModalLabel">Add Invoice</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <form method="POST" action="{{ route('invoices.store') }}">
                                    @csrf
                                    <div>
                                        <ul class="wizard-nav mb-5">
                                            <li class="wizard-list-item">
                                                <div class="list-item">
                                                    <div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Customer Info">
                                                        <i class="bx bx-user-circle"></i>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="wizard-list-item">
                                                <div class="list-item">
                                                    <div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Invoice Details">
                                                        <i class="bx bx-file"></i>
                                                    </div>
                                                </div>
                                            </li>

                                            <li class="wizard-list-item">
                                                <div class="list-item">
                                                    <div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Order Summery">
                                                        <i class="bx bx-edit"></i>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        <!-- wizard-nav -->

                                        <div class="wizard-tab">
                                            <div class="text-center mb-4">
                                                <h5>Customer Information</h5>
                                                <p class="card-title-desc">Fill all information below</p>
                                            </div>
                                            <div>
                                                <div class="row">
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="customerinfo-name-input" class="form-label">Customer Name :</label>
                                                            <input type="text" name="billing_name" class="form-control" placeholder="Enter Name" id="customerinfo-name-input" required>
                                                        </div>
                                                        
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="customerinfo-email-input" class="form-label">Email :</label>
                                                            <input type="email" name="email" class="form-control" placeholder="Enter Email" id="customerinfo-email-input">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        
                                                        <div class="mb-3">
                                                            <label for="customerinfo-phone-input" class="form-label">Phone :</label>
                                                            <input type="text" name="phone" class="form-control" placeholder="Enter Phone" id="customerinfo-phone-input">
                                                        </div>
                                                    </div>
                                                </div>
        
                                                <div class="mb-3">
                                                    <label for="customerinfo-address-input" class="form-label">Address :</label>
                                                    <textarea name="address" class="form-control" placeholder="Enter Address" id="customerinfo-address-input" rows="2"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- wizard-tab -->

                                        <div class="wizard-tab">
                                            
                                            <div class="text-center mb-4">
                                                <h5>Invoice Details</h5>
                                                <p class="card-title-desc">Fill Invoice Details.</p>
                                            </div>
                                            <div>
                                                <div class="row">
                                                    <div class="col-lg-6">
        
                                                        <div class="mb-3">
                                                            <label for="invoicenumberinput" class="form-label">Invoice #</label>
                                                            <input type="text" name="invoice_no" class="form-control" placeholder="Enter Invoice" id="invoicenumberinput">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Invoice Date</label>
                                                            <input type="text" name="invoice_date" class="form-control" placeholder="Enter Date" id="datepicker-invoice">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="invoicedescriptioninput" class="form-label">Description (Optional)</label>
                                                            <input type="text" name="description" class="form-control" placeholder="Enter Description" id="invoicedescriptioninput">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Payment method :</label>
                                                            <select name="payment_method" class="form-select">
                                                                <option selected>Select Payment method</option>
                                                                <option value="CR">Credit / Debit Card</option>
                                                                <option value="PY">Paypal</option>
                                                            </select>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- wizard-tab -->

                                        <div class="wizard-tab">
                                            <div class="text-center mb-4">
                                                <h5>Order Summery</h5>
                                                <p class="card-title-desc">Fill Order Summery Details.</p>
                                            </div>
                                            <div>
                                                <div class="table-responsive">
                                                    <table class="table table-nowrap">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">#</th>
                                                                <th scope="col">Item name</th>
                                                                <th scope="col">Description</th>
                                                                <th scope="col" width="120px">Price</th>
                                                                <th scope="col" width="120px">Quantity</th>
                                                                <th scope="col" width="120px">Total</th>
                                                                <th scope="col" class="text-center">Action</th>
                                                            </tr>
                                                            
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row">1</th>
                                                                <td>
                                                                    <div>
                                                                        <input class="form-control" name="item_name" placeholder="Enter Name" type="text">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        <textarea class="form-control" name="item_description" placeholder="Enter Description" rows="2"></textarea>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        <input class="form-control" name="amount" placeholder="Enter Price" type="text">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        <input class="form-control" name="quantity" placeholder="Enter Quantity" type="text">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        <input class="form-control" type="text"  value="0.00" readonly>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="text-center">
                                                                        <a href="#" class="text-danger p-2 d-inline-block" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash-alt"></i></a>
                                                                    </div>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <th scope="row">2</th>
                                                                <td>
                                                                    <div>
                                                                        <input class="form-control" name="item_name_2" placeholder="Enter Name" type="text">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        <textarea class="form-control" name="item_description_2" placeholder="Enter Description" rows="2"></textarea>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        <input class="form-control" name="amount_2" placeholder="Enter Price" type="text">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        <input class="form-control" name="quantity_2" placeholder="Enter Quantity" type="text">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        <input class="form-control" type="text"  value="0.00" readonly>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="text-center">
                                                                        <a href="#" class="text-danger p-2 d-inline-block" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash-alt"></i></a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="row justify-content-end">
                                                    <div class="col-xl-3 col-md-4">
                                                        <div>
                                                            <div>
                                                                <h5 class="font-size-14 py-2">Sub Total : <span class="float-end fw-normal text-body">0.00</span></h5>
                                                            </div>
                                                            <div>
                                                                <h5 class="font-size-14 py-2">Discount  : <span class="float-end fw-normal text-body"> - 0.00</span></h5>
                                                            </div>
                                                            <div>
                                                                <h5 class="font-size-14 py-2">Shipping Charge  : <span class="float-end fw-normal text-body"> 0.00</span></h5>
                                                            </div>
                                                            <div>
                                                                <h5 class="font-size-14 py-2">Tax  : <span class="float-end fw-normal text-body"> 0.00</span></h5>
                                                            </div>
                                                            <div>
                                                                <h5 class="font-size-14 py-2">Total  : <span class="float-end"> 0.00</span></h5>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- wizard-tab -->

                                        <div class="d-flex align-items-start gap-3 mt-4">
                                            <button type="button" class="btn btn-primary w-sm" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
                                            <button type="button" class="btn btn-primary w-sm ms-auto" id="nextBtn" onclick="nextPrev(1)">Next</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end modal -->

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

        <!-- gridjs js -->
        <script src="assets/libs/gridjs/gridjs.umd.js"></script>

        <!-- flatpickr js -->
        <script src="assets/libs/flatpickr/flatpickr.min.js"></script>
        <!-- invoice-list init -->
        <script>window.INVOICE_LIST_DATA = @json($invoiceData);</script>
        <script src="assets/js/pages/invoice-list.init.js"></script>

        <script src="assets/js/app.js"></script>

    </body>

</html>
