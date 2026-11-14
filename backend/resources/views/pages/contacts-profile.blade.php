<!doctype html>
<html lang="en">

    <head>

        <meta charset="utf-8" />
        <title>Profile | Jaffri's CRM</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Jaffri's CRM & ERP" name="description" />
        <meta content="Arsalan Jaffri" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon-jaffri.png">

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
                            <div class="col-xxl-3">
                                <div class="card">
                                    <div class="card-body p-0">
                                        <div class="user-profile-img">
                                            <img src="assets/images/pattern-bg.jpg"
                                                class="profile-img profile-foreground-img rounded-top" style="height: 120px;"
                                                alt="">
                                            <div class="overlay-content rounded-top">
                                                <div>
                                                    <div class="user-nav p-3">
                                                        <div class="d-flex justify-content-end">
                                                            <div class="dropdown">
                                                                <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                    <i class="bx bx-dots-vertical text-white font-size-20"></i>
                                                                </a>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    <a class="dropdown-item" href="#">Edit</a>
                                                                    <a class="dropdown-item" href="#">Action</a>
                                                                    <a class="dropdown-item" href="#">Remove</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end user-profile-img -->


<div class="p-4 pt-0">

                                            <div class="mt-n5 position-relative text-center border-bottom pb-3">
                                                <img src="{{ $user?->avatar && ! str_starts_with($user->avatar, 'http') ? asset($user->avatar) : asset('assets/images/users/avatar-3.jpg') }}" alt="" class="avatar-xl rounded-circle img-thumbnail">

                                                <div class="mt-3">
                                                   <h5 class="mb-1">{{ $user?->name ?? 'N/A' }}</h5>
                                                    <p class="text-muted mb-0">
                                                        <i class="bx bxs-star text-warning font-size-14"></i>
                                                        <i class="bx bxs-star text-warning font-size-14"></i>
                                                        <i class="bx bxs-star text-warning font-size-14"></i>
                                                        <i class="bx bxs-star text-warning font-size-14"></i>
                                                        <i class="bx bxs-star-half text-warning font-size-14"></i>
                                                    </p>
                                                </div>

                                            </div>

                                            <div class="table-responsive mt-3 border-bottom pb-3">
                                                <table class="table align-middle table-sm table-nowrap table-borderless table-centered mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <th class="fw-bold">
                                                                City :</th>
                                                            <td class="text-muted">{{ $user?->city ?? 'N/A' }}</td>
                                                        </tr>
                                                        <!-- end tr -->
                                                        <tr>
                                                            <th class="fw-bold">
                                                                Address :</th>
                                                            <td class="text-muted">{{ $user?->address ?? 'N/A' }}</td>
                                                        </tr>
                                                        <!-- end tr -->
                                                        <tr>
                                                            <th class="fw-bold">
                                                                Country :</th>
                                                            <td class="text-muted">{{ $user?->country ?? 'N/A' }}</td>
                                                        </tr>
                                                        <!-- end tr -->
                                                        <tr>
                                                            <th class="fw-bold">Designation :</th>
                                                            <td class="text-muted">{{ $user?->designation ?? 'N/A' }}</td>
                                                        </tr>
                                                        <!-- end tr -->

                                                        <tr>
                                                            <th class="fw-bold">Phone :</th>
                                                            <td class="text-muted">{{ $user?->phone ?? 'N/A' }}</td>
                                                        </tr>
                                                        <!-- end tr -->

                                                        <tr>
                                                            <th class="fw-bold">Email :</th>
                                                            <td class="text-muted">{{ $user?->email ?? 'N/A' }}</td>
                                                        </tr>
                                                        <!-- end tr -->
                                                        <tr>
                                                            <th class="fw-bold">Username :</th>
                                                            <td class="text-muted">{{ $user?->username ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="fw-bold">Status :</th>
                                                            <td class="text-muted">
                                                                @if ($user?->is_active)
                                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                                @else
                                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </tbody><!-- end tbody -->
                                                </table>
                                            </div>

                                            <div class="pt-2 text-center border-bottom pb-4 mt-3">
                                                <a href="{{ route('profile.pdf') }}" class="btn btn-primary waves-effect waves-light btn-sm">Download PDF <i class="bx bx-download ms-1 align-middle"></i></a>
                                            </div>

                                            <form method="POST" action="{{ route('profile.destroy') }}" class="mt-3" onsubmit="return confirm('Delete your account? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <div class="mb-3">
                                                    <label class="form-label" for="delete-password">Confirm password to delete</label>
                                                    <input type="password" class="form-control form-control-sm" id="delete-password" name="password" placeholder="Enter password" required>
                                                    @error('password')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <button type="submit" class="btn btn-outline-danger w-100 btn-sm">Delete Account</button>
                                            </form>

                                            <div class="mt-3 pt-1 text-center">
                                                <ul class="list-inline mb-0">
                                                    <li class="list-inline-item">
                                                        <a href="javascript:void()" class="social-list-item bg-primary text-white border-primary">
                                                            <i class="bx bxl-facebook"></i>
                                                        </a>
                                                    </li>
                                                    <li class="list-inline-item">
                                                        <a href="javascript:void()" class="social-list-item bg-info text-white border-info">
                                                            <i class="bx bxl-linkedin"></i>
                                                        </a>
                                                    </li>
                                                    <li class="list-inline-item">
                                                        <a href="javascript:void()" class="social-list-item bg-danger text-white border-danger">
                                                            <i class="bx bxl-google"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xxl-9">
                                <div class="card">
                                    <div class="card-body">
                                         <!-- Nav tabs -->
                                         <ul class="nav nav-pills nav-justified" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="tab" href="#overview" role="tab">
                                                    <span>Overview</span> 
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#messages" role="tab">
                                                    <span>Messages</span> 
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#post" role="tab">
                                                    <span>Post</span>   
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Tab content -->
                                <div class="tab-content">
                                    <div class="tab-pane active" id="overview" role="tabpanel">
                                        <div class="card">
                                           <div class="card-body">
                                                <h5 class="font-size-16 mb-3">Edit Profile</h5>

                                                @if (session('status'))
                                                    <div class="alert alert-success">{{ session('status') }}</div>
                                                @endif
                                                @if ($errors->any())
                                                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                                                @endif

                                                <form method="POST" action="{{ route('profile.update') }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="profile-name">Full Name</label>
                                                                <input type="text" class="form-control" id="profile-name" name="name" value="{{ old('name', $user?->name) }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="profile-email">Email</label>
                                                                <input type="email" class="form-control" id="profile-email" name="email" value="{{ old('email', $user?->email) }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="profile-phone">Phone</label>
                                                                <input type="text" class="form-control" id="profile-phone" name="phone" value="{{ old('phone', $user?->phone) }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="profile-designation">Designation</label>
                                                                <input type="text" class="form-control" id="profile-designation" name="designation" value="{{ old('designation', $user?->designation) }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="profile-city">City</label>
                                                                <input type="text" class="form-control" id="profile-city" name="city" value="{{ old('city', $user?->city) }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="profile-country">Country</label>
                                                                <input type="text" class="form-control" id="profile-country" name="country" value="{{ old('country', $user?->country) }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="profile-address">Address</label>
                                                                <input type="text" class="form-control" id="profile-address" name="address" value="{{ old('address', $user?->address) }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="profile-bio">Bio</label>
                                                                <textarea class="form-control" id="profile-bio" name="bio" rows="3">{{ old('bio', $user?->bio) }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="profile-current-password">Current Password</label>
                                                                <input type="password" class="form-control" id="profile-current-password" name="current_password" placeholder="Required to change password">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="profile-password">New Password</label>
                                                                <input type="password" class="form-control" id="profile-password" name="password" placeholder="Leave blank to keep current">
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="profile-password-confirmation">Confirm New Password</label>
                                                                <input type="password" class="form-control" id="profile-password-confirmation" name="password_confirmation" placeholder="Confirm new password">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">Update Profile</button>
                                                </form>

                                           </div>
                                        </div>
                                    </div>

                                                <div class="mt-4">
                                                    <h5 class="font-size-16 mb-4">Projects</h5>
                                                    <div class="table-responsive">
                                                        <table class="table table-nowrap table-hover mb-1">
                                                            <thead class="bg-light">
                                                                <tr>
                                                                    <th scope="col">#</th>
                                                                    <th scope="col">Projects</th>
                                                                    <th scope="col">Date</th>
                                                                    <th scope="col">Budget</th>
                                                                    <th scope="col">Status</th>
                                                                    <th scope="col" style="width: 120px;">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <th scope="row">01</th>
                                                                    <td><a href="#" class="text-body">Brand Logo Design</a></td>
                                                                    <td>
                                                                        18 Jun, 2021
                                                                    </td>
                                                                    <td>
                                                                        $523
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-primary-subtle text-primary  font-size-12">Open</span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="dropdown">
                                                                            <a class="text-muted dropdown-toggle font-size-18 px-2" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                                            </a>
                                                                        
                                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                                <a class="dropdown-item" href="#">Action</a>
                                                                                <a class="dropdown-item" href="#">Another action</a>
                                                                                <a class="dropdown-item" href="#">Something else here</a>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row">02</th>
                                                                    <td><a href="#" class="text-body">Chat app Design</a></td>
                                                                    <td>
                                                                        28 May, 2021
                                                                    </td>
                                                                    <td>
                                                                        $356
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-success-subtle text-success  font-size-12">Complete</span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="dropdown">
                                                                            <a class="text-muted dropdown-toggle font-size-18 px-2" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                                            </a>
                                                                        
                                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                                <a class="dropdown-item" href="#">Action</a>
                                                                                <a class="dropdown-item" href="#">Another action</a>
                                                                                <a class="dropdown-item" href="#">Something else here</a>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row">03</th>
                                                                    <td><a href="#" class="text-body">Minible Landing</a></td>
                                                                    <td>
                                                                        13 May, 2021
                                                                    </td>
                                                                    <td>
                                                                        $425
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-success-subtle text-success  font-size-12">Complete</span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="dropdown">
                                                                            <a class="text-muted dropdown-toggle font-size-18 px-2" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                                            </a>
                                                                        
                                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                                <a class="dropdown-item" href="#">Action</a>
                                                                                <a class="dropdown-item" href="#">Another action</a>
                                                                                <a class="dropdown-item" href="#">Something else here</a>
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

                                    <div class="tab-pane" id="messages" role="tabpanel">
                                        <div class="card">
                                           <div class="card-body">

                                            <div class="py-2">

                                                <div class="mx-n4 px-4" data-simplebar style="max-height: 360px;">
                                                    <div class="border-bottom pb-3">
                                                        <p class="float-sm-end text-muted font-size-13">12 July, 2021</p>
                                                        <div class="badge bg-success mb-2"><i class="mdi mdi-star"></i> 4.1</div>
                                                        <p class="text-muted mb-4">Maecenas non vestibulum ante, nec efficitur orci. Duis eu ornare mi, quis bibendum quam. Etiam imperdiet aliquam purus sit amet rhoncus. Vestibulum pretium consectetur leo, in mattis ipsum sollicitudin eget. Pellentesque vel mi tortor.
                                                            Nullam vitae maximus dui dolor sit amet, consectetur adipiscing elit.</p>
                                                        <div class="d-flex align-items-start">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex">
                                                                    <img src="assets/images/users/avatar-2.jpg" class="avatar-sm rounded-circle" alt="">
                                                                    <div class="flex-1 ms-2 ps-1">
                                                                        <h5 class="font-size-15 mb-0">Samuel</h5>
                                                                        <p class="text-muted mb-0 mt-1">65 Followers, 86 Reviews</p>
                                                                    </div>
                                                                </div>
                                                            </div>
        
                                                            <div class="flex-shrink-0">
                                                                <ul class="list-inline product-review-link mb-0">
                                                                    <li class="list-inline-item">
                                                                        <a href="#"><i class="bx bx-like"></i></a>
                                                                    </li>
                                                                    <li class="list-inline-item">
                                                                        <a href="#"><i class="bx bx-comment-dots"></i></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        
                                                    </div>
    
                                                    <div class="border-bottom py-3">
                                                        <p class="float-sm-end text-muted font-size-13">06 July, 2021</p>
                                                        <div class="badge bg-success mb-2"><i class="mdi mdi-star"></i> 4.0</div>
                                                        <p class="text-muted mb-4">Cras ac condimentum velit. Quisque vitae elit auctor quam egestas congue. Duis eget lorem fringilla, ultrices justo consequat, gravida lorem. Maecenas orci enim, sodales id condimentum et, nisl arcu aliquam velit,
                                                            sit amet vehicula turpis metus cursus dolor cursus eget dui.</p>
                                                        <div class="d-flex align-items-start">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex">
                                                                    <img src="assets/images/users/avatar-3.jpg" class="avatar-sm rounded-circle" alt="">
                                                                    <div class="flex-1 ms-2 ps-1">
                                                                        <h5 class="font-size-15 mb-0">Joseph</h5>
                                                                        <p class="text-muted mb-0 mt-1">85 Followers, 102 Reviews</p>
                                                                    </div>
                                                                </div>
                                                            </div>
        
                                                            <div class="flex-shrink-0">
                                                                <ul class="list-inline product-review-link mb-0">
                                                                    <li class="list-inline-item">
                                                                        <a href="#"><i class="bx bx-like"></i></a>
                                                                    </li>
                                                                    <li class="list-inline-item">
                                                                        <a href="#"><i class="bx bx-comment-dots"></i></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
    
                                                    <div class="pt-3">
                                                        <p class="float-sm-end text-muted font-size-13">26 June, 2021</p>
                                                        <div class="badge bg-success mb-2"><i class="mdi mdi-star"></i> 4.2</div>
                                                        <p class="text-muted mb-4">Aliquam sit amet eros eleifend, tristique ante sit amet, eleifend arcu. Cras ut diam quam. Fusce quis diam eu augue semper ullamcorper vitae sed massa. Mauris lacinia, massa a feugiat mattis, leo massa porta eros, sed congue arcu sem nec orci.
                                                            In ac consectetur augue. Nullam pulvinar risus non augue tincidunt blandit.</p>
                                                        <div class="d-flex align-items-start">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex">
                                                                    <img src="assets/images/users/avatar-6.jpg" class="avatar-sm rounded-circle" alt="">
                                                                    <div class="flex-1 ms-2 ps-1">
                                                                        <h5 class="font-size-15 mb-0">Paul</h5>
                                                                        <p class="text-muted mb-0 mt-1">27 Followers, 66 Reviews</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="flex-shrink-0">
                                                                <ul class="list-inline product-review-link mb-0">
                                                                    <li class="list-inline-item">
                                                                        <a href="#"><i class="bx bx-like"></i></a>
                                                                    </li>
                                                                    <li class="list-inline-item">
                                                                        <a href="#"><i class="bx bx-comment-dots"></i></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        
                                                    </div>
                                                </div>

                                                <div class="mt-2">
                                                    <div class="border rounded mt-4">
                                                        <form action="#">
                                                            <div class="px-2 py-1 bg-light">
                                                                <div class="btn-group" role="group">
                                                                    <button type="button" class="btn btn-sm btn-link text-darbodyxt-decoration-none"><i class="bx bx-link"></i></button>
                                                                    <button type="button" class="btn btn-sm btn-link text-darbodyxt-decoration-none"><i class="bx bx-smile"></i></button>
                                                                    <button type="button" class="btn btn-sm btn-link text-darbodyxt-decoration-none"><i class="bx bx-at"></i></button>
                                                                </div>
                                                            </div>
                                                            <textarea rows="3" class="form-control border-0 resize-none" placeholder="Your Message..."></textarea>
                                                        </form>
                                                    </div>

                                                    <div class="text-end mt-3">
                                                        <button type="button" class="btn btn-success w-sm text-truncate ms-2"> Send <i class="bx bx-send ms-2 align-middle"></i></button>
                                                    </div>
                                               </div>

                                            </div>

                                           </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane" id="post" role="tabpanel">
                                            <div class="card">
                                                <div class="card-body"> 
                                                    <div class="mx-n3 px-3" data-simplebar style="max-height: 580px;">
                                                        <div class="blog-post">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-3">
                                                                    <img src="assets/images/users/avatar-6.jpg" alt="" class="avatar-md rounded-circle img-thumbnail">
                                                                </div>
                                                                <div class="flex-1">
                                                                    <h5 class="font-size-16 text-truncate mb-1"><a href="#" class="text-body">Richard Johnson</a></h5>
                                                                    <p class="font-size-13 text-muted mb-0">24 Mar, 2021</p>
                                                                </div>
                                                            </div>
                                                            <div class="pt-3">
                                                                <p class="text-muted">Aenean ornare mauris velit. Donec imperdiet, massa sit amet porta maximus, massa justo faucibus nisi,
                                                                     eget accumsan nunc ipsum nec lacus. Etiam dignissim turpis sit amet lectus porttitor eleifend. Maecenas ornare molestie metus eget feugiat.
                                                                     Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
    
                                                            </div>
                                                            <div class="position-relative mt-3">
                                                                <img src="assets/images/post-1.jpg" alt="" class="img-thumbnail">
                                                            </div>
                                                            <div class="pt-3">
                                                                <div class="d-flex align-items-center justify-content-between border-bottom pb-3">
                                                                    <div>
                                                                        <ul class="list-inline mb-0">
                                                                            <li class="list-inline-item me-3">
                                                                                <a href="javascript: void(0);" class="text-muted">
                                                                                    <i class="bx bx-purchase-tag-alt text-muted me-1"></i> Project
                                                                                </a>
                                                                            </li>
                                                                            <li class="list-inline-item me-3">
                                                                                <a href="javascript: void(0);" class="text-muted">
                                                                                    <i class="bx bx-like align-middle text-muted me-1"></i> 12 Like
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
    
                                                                    <div>
                                                                        <div class="d-flex align-items-center">
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
                                                                            <div class="ms-2">
                                                                                <button type="button" class="btn btn-outline-primary btn-sm waves-effect">Share <i class="bx bx-share-alt align-middle ms-1"></i></button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- end blog post --> 

                                                        <div class="blog-post mt-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-3">
                                                                    <img src="assets/images/users/avatar-5.jpg" alt="" class="avatar-md rounded-circle img-thumbnail">
                                                                </div>
                                                                <div class="flex-1">
                                                                    <h5 class="font-size-16 text-truncate mb-1"><a href="#" class="text-body">James Delgado</a></h5>
                                                                    <p class="font-size-13 text-muted mb-0">29 July, 2021</p>
                                                                </div>
                                                            </div>
                                                            <div class="pt-3">
                                                                <p class="text-muted">Aenean ornare mauris velit. Donec imperdiet, massa sit amet porta maximus, massa justo faucibus nisi,
                                                                     eget accumsan nunc ipsum nec lacus. Etiam dignissim turpis sit amet lectus porttitor eleifend. Maecenas ornare molestie metus eget feugiat.
                                                                     Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
    
                                                            </div>
                                                            <div class="position-relative mt-3">
                                                                <img src="assets/images/post-2.jpg" alt="" class="img-thumbnail">
                                                            </div>
                                                            <div class="pt-3">
                                                                <div class="d-flex align-items-center justify-content-between border-bottom pb-3">
                                                                    <div>
                                                                        <ul class="list-inline mb-0">
                                                                            <li class="list-inline-item me-3">
                                                                                <a href="javascript: void(0);" class="text-muted">
                                                                                    <i class="bx bx-purchase-tag-alt text-muted me-1"></i> Project
                                                                                </a>
                                                                            </li>
                                                                            <li class="list-inline-item me-3">
                                                                                <a href="javascript: void(0);" class="text-muted">
                                                                                    <i class="bx bx-like align-middle text-muted me-1"></i> 12 Like
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
    
                                                                    <div>
                                                                        <div class="d-flex align-items-center">
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
                                                                            <div class="ms-2">
                                                                                <button type="button" class="btn btn-outline-primary btn-sm waves-effect">Share <i class="bx bx-share-alt align-middle ms-1"></i></button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- end blog post --> 
                                                    </div>
                                                        
                                                </div>
                                                <!-- end card body -->
                                            </div>
                                    </div>
                                </div>

                            </div>
                          
                        </div>
                        <!-- end row -->


                        <div class="row">

                            <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Weekly Hours</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="overview-chart" data-colors='["#312E81"]' class="apex-charts" dir="ltr"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Team Members</h5>
                                    </div>

                                    <div class="card-body pt-1">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-nowrap mb-0">
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

                            <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Skill</h5>
                                    </div>
                                    <div class="card-body">
                            
                                        <div class="row align-items-center g-0">
                                            <div class="col-sm-3">
                                                <p class="text-truncate mt-1 mb-0"><i class="mdi mdi-circle-medium text-primary me-2"></i> Photoshop </p>
                                            </div>
                        
                                            <div class="col-sm-9">
                                                <div class="progress mt-1" style="height: 6px;">
                                                    <div class="progress-bar progress-bar bg-primary" role="progressbar" style="width: 72%" aria-valuenow="52" aria-valuemin="0" aria-valuemax="52">
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                        
                                        <div class="row align-items-center g-0 mt-3">
                                            <div class="col-sm-3">
                                                <p class="text-truncate mt-1 mb-0"><i class="mdi mdi-circle-medium text-primary me-2"></i> illustrator </p>
                                            </div>
                                            <div class="col-sm-9">
                                                <div class="progress mt-1" style="height: 6px;">
                                                    <div class="progress-bar progress-bar bg-primary" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="45">
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                        
                                        <div class="row align-items-center g-0 mt-3">
                                            <div class="col-sm-3">
                                                <p class="text-truncate mt-1 mb-0"><i class="mdi mdi-circle-medium text-primary me-2"></i> HTML </p>
                                            </div>
                                            <div class="col-sm-9">
                                                <div class="progress mt-1" style="height: 6px;">
                                                    <div class="progress-bar progress-bar bg-primary" role="progressbar" style="width: 68%" aria-valuenow="48" aria-valuemin="0" aria-valuemax="48">
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                        
                                        <div class="row align-items-center g-0 mt-3">
                                            <div class="col-sm-3">
                                                <p class="text-truncate mt-1 mb-0"><i class="mdi mdi-circle-medium text-primary me-2"></i> CSS </p>
                                            </div>
                                            <div class="col-sm-9">
                                                <div class="progress mt-1" style="height: 6px;">
                                                    <div class="progress-bar progress-bar bg-primary" role="progressbar" style="width: 78%" aria-valuenow="78" aria-valuemin="0" aria-valuemax="78">
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                        
                                        <div class="row align-items-center g-0 mt-3">
                                            <div class="col-sm-3">
                                                <p class="text-truncate mt-1 mb-0"><i class="mdi mdi-circle-medium text-primary me-2"></i> Javascript </p>
                                            </div>
                                            <div class="col-sm-9">
                                                <div class="progress mt-1" style="height: 6px;">
                                                    <div class="progress-bar progress-bar bg-primary" role="progressbar" style="width: 63%" aria-valuenow="63" aria-valuemin="0" aria-valuemax="63">
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->

                                        <div class="row align-items-center g-0 mt-3">
                                            <div class="col-sm-3">
                                                <p class="text-truncate mt-1 mb-0"><i class="mdi mdi-circle-medium text-primary me-2"></i> Php </p>
                                            </div>
                                            <div class="col-sm-9">
                                                <div class="progress mt-1" style="height: 6px;">
                                                    <div class="progress-bar progress-bar bg-primary" role="progressbar" style="width: 48%" aria-valuenow="48" aria-valuemin="0" aria-valuemax="48">
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                        
                                        <div class="row align-items-center g-0 mt-3">
                                            <div class="col-sm-3">
                                                <p class="text-truncate mt-1 mb-0"><i class="mdi mdi-circle-medium text-primary me-2"></i> Python </p>
                                            </div>
                                            <div class="col-sm-9">
                                                <div class="progress mt-1" style="height: 6px;">
                                                    <div class="progress-bar progress-bar bg-primary" role="progressbar" style="width: 78%" aria-valuenow="78" aria-valuemin="0" aria-valuemax="78">
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- end row-->
                                       
                                    </div>
                                </div>
                            </div>

                        </div>


                       

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

        <script src="assets/js/pages/profile.init.js"></script>

        <script src="assets/js/app.js"></script>

    </body>

</html>
