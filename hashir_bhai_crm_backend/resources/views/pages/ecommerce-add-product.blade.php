<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Add Product | Cyprus Digital Solution</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Cyprus Digital Solution CRM & ERP" name="description" />
    <meta content="Themesdesign" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon-cyprus.svg">

    <!-- choices css -->
    <link href="assets/libs/choices.js/public/assets/styles/choices.min.css" rel="stylesheet" type="text/css" />

    <!-- Plugins css -->
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
                        <div id="addproduct-accordion" class="custom-accordion">
                            <div class="card">
                                <a href="#addproduct-productinfo-collapse" class="text-body" data-bs-toggle="collapse" aria-expanded="true" aria-controls="addproduct-productinfo-collapse">
                                    <div class="p-4">

                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <div class="avatar-title rounded-circle bg-primary-subtle  text-primary">
                                                        <h5 class="text-primary font-size-17 mb-0">01</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h5 class="font-size-16 mb-1">Product Info</h5>
                                                <p class="text-muted text-truncate mb-0">Fill all information below</p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="mdi mdi-chevron-up accor-down-icon font-size-24"></i>
                                            </div>

                                        </div>

                                    </div>
                                </a>

                                <div id="addproduct-productinfo-collapse" class="collapse show" data-bs-parent="#addproduct-accordion">
                                    <div class="p-4 border-top">
                                        <form method="POST" action="{{ route('ecommerce-products.store') }}" id="add-product-form">
@csrf
                                            <div class="mb-3">
                                                <label class="form-label" for="productname">Product Name</label>
                                                <input id="productname" name="name" placeholder="Enter Product Name" type="text" class="form-control">
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-4">

                                                    <div class="mb-3">
                                                        <label class="form-label" for="manufacturername">Manufacturer Name</label>
                                                        <input id="manufacturername" name="manufacturername" placeholder="Enter Manufacturer Name" type="text" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">

                                                    <div class="mb-3">
                                                        <label class="form-label" for="manufacturerbrand">Manufacturer Brand</label>
                                                        <input id="manufacturerbrand" name="manufacturerbrand" placeholder="Enter Manufacturer Brand" type="text" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="price">Price</label>
                                                        <input id="price" name="price" placeholder="Enter Price" type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="choices-single-default" class="form-label">Category</label>
                                                        <select class="form-control" data-trigger name="category_id" id="choices-single-category">
                                                            <option value="">Select</option>
                                                            @foreach (($categories ?? collect()) as $cat)
                                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="choices-single-specifications" class="form-label">Specifications</label>
                                                        <select class="form-control" data-trigger name="choices-single-category" id="choices-single-specifications">
                                                            <option value="HI" selected>High Quality</option>
                                                            <option value="LE" selected>Leather</option>
                                                            <option value="NO">Notifications</option>
                                                            <option value="SI">Sizes</option>
                                                            <option value="DI">Different Color</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-0">
                                                <label class="form-label" for="productdesc">Product Description</label>
                                                <textarea class="form-control" id="productdesc" name="description" placeholder="Enter Description" rows="4"></textarea>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <a href="#addproduct-img-collapse" class="text-body collbodyd" data-bs-toggle="collapse" aria-haspopup="true" aria-expanded="false" aria-haspopup="true" aria-controls="addproduct-img-collapse">
                                    <div class="p-4">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <div class="avatar-title rounded-circle bg-primary-subtle  text-primary">
                                                        <h5 class="text-primary font-size-17 mb-0">02</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h5 class="font-size-16 mb-1">Product Image</h5>
                                                <p class="text-muted text-truncate mb-0">Fill all information below</p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="mdi mdi-chevron-up accor-down-icon font-size-24"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                                <div id="addproduct-img-collapse" class="collapse" data-bs-parent="#addproduct-accordion">
                                    <div class="p-4 border-top">
                                        <form action="#" class="dropzone">
                                            <div class="fallback">
                                                <input name="file" type="file" multiple="multiple">
                                            </div>
                                            <div class="dz-message needsclick">
                                                <div class="mb-3">
                                                    <i class="display-4 text-muted mdi mdi-cloud-upload"></i>
                                                </div>
                                                <h4 class="mb-0">Drop files here or click to upload.</h4>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <a href="#addproduct-metadata-collapse" class="text-body collbodyd" data-bs-toggle="collapse" aria-haspopup="true" aria-expanded="false" aria-haspopup="true" aria-controls="addproduct-metadata-collapse">
                                    <div class="p-4">

                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <div class="avatar-title rounded-circle bg-primary-subtle  text-primary">
                                                        <h5 class="text-primary font-size-17 mb-0">03</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h5 class="font-size-16 mb-1">Meta Data</h5>
                                                <p class="text-muted text-truncate mb-0">Fill all information below</p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="mdi mdi-chevron-up accor-down-icon font-size-24"></i>
                                            </div>

                                        </div>

                                    </div>
                                </a>

                                <div id="addproduct-metadata-collapse" class="collapse" data-bs-parent="#addproduct-accordion">
                                    <div class="p-4 border-top">
                                        <form>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="metatitle">Meta Title</label>
                                                        <input id="metatitle" name="metatitle" placeholder="Enter Title" type="text" class="form-control">
                                                    </div>

                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="metakeywords">Meta Keywords</label>
                                                        <input id="metakeywords" name="metakeywords" placeholder="Enter Keywords" type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-0">
                                                <label class="form-label" for="metadescription">Meta Description</label>
                                                <textarea class="form-control" id="metadescription" placeholder="Enter Description" rows="4"></textarea>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->

                <div class="row mb-4">
                    <div class="col text-end">
                        <a href="/ecommerce-products" class="btn btn-danger"> <i class="bx bx-x me-1"></i> Cancel </a>
                        <button type="submit" form="add-product-form" class="btn btn-success"> <i class=" bx bx-file me-1"></i> Save </button>
                    </div> <!-- end col -->
                </div> <!-- end row-->




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

<!-- choices js -->
<script src="assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>

<!-- dropzone plugin -->
<script src="assets/libs/dropzone/dropzone-min.js"></script>

<!-- init js -->
<script src="assets/js/pages/ecommerce-choices.init.js"></script>

<script src="assets/js/app.js"></script>

</body>

</html>
