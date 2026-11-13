<!doctype html>
<html lang="en">

    <head>

        <meta charset="utf-8" />
        <title>Chat | Jaffri's CRM</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Jaffri's CRM CRM & ERP" name="description" />
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

                        <div class="d-lg-flex">
                            <div class="chat-leftsidebar card">
                                <div class="card-body">
                                    
                                   <div class="text-center bg-light rounded px-4 py-3">
                                            <div class="text-end">
                                                <div class="dropdown chat-noti-dropdown">
                                                    <button class="btn dropdown-toggle p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="bx bx-cog"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Profile</a>
                                                        <a class="dropdown-item" href="#">Edit</a>
                                                        <a class="dropdown-item" href="#">Add Contact</a>
                                                        <a class="dropdown-item" href="#">Setting</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="chat-user-status">
                                                <img src="assets/images/users/avatar-3.jpg" class="avatar-md rounded-circle" alt="">
                                                <div class="">
                                                    <div class="status"></div>
                                                </div>
                                            </div>
                                            <h5 class="font-size-16 mb-1 mt-3"><a href="#" class="text-reset">{{ auth()->user()?->name ?? 'You' }} </a></h5>
                                            <p class="text-muted mb-0">Available</p>
                                   </div>
                                </div>

                                <div class="p-3">
                                    <div class="search-box position-relative">
                                        <input type="text" class="form-control rounded border" placeholder="Search...">
                                        <i class="search-icon" data-eva="search-outline" data-eva-height="26" data-eva-width="26"></i>
                                    </div>
                                </div>

                                <div class="chat-leftsidebar-nav">
                                    <ul class="nav nav-pills nav-justified bg-light m-3 rounded">
                                        <li class="nav-item">
                                            <a href="#chat" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                                <i class="bx bx-chat font-size-20 d-sm-none"></i>
                                                <span class="d-none d-sm-block">Chat</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#groups" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                <i class="bx bx-group font-size-20 d-sm-none"></i>
                                                <span class="d-none d-sm-block">Groups</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#contacts" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                <i class="bx bx-book-content font-size-20 d-sm-none"></i>
                                                <span class="d-none d-sm-block">Contacts</span>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane show active" id="chat">
                                            <div class="chat-message-list" data-simplebar>
                                                <div class="pt-3">
                                                    <div class="px-3">
                                                        <h5 class="font-size-14 mb-3">Recent</h5>
                                                    </div>
                                                    <ul class="list-unstyled chat-list p-3">
                                                        @foreach ($contacts as $contact)
                                                        <li class="{{ $active && $active->id === $contact->id ? 'active' : '' }}">
                                                            <a href="{{ route('apps-chat.show', $contact) }}">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 user-img online align-self-center me-3">
                                                                        @if ($contact->avatar)
                                                                        <img src="{{ $contact->avatar }}" class="rounded-circle avatar-sm" alt="">
                                                                        @else
                                                                        <div class="avatar-sm align-self-center">
                                                                            <span class="avatar-title rounded-circle bg-primary-subtle  text-primary">
                                                                                {{ strtoupper(substr($contact->name, 0, 1)) }}
                                                                            </span>
                                                                        </div>
                                                                        @endif
                                                                        <span class="user-status"></span>
                                                                    </div>
                                                                    <div class="flex-grow-1 overflow-hidden">
                                                                        <h5 class="text-truncate font-size-14 mb-0">{{ $contact->name }}</h5>
                                                                    </div>
                                                                    <div class="flex-shrink-0">
                                                                        @if ($contact->unread)
                                                                        <span class="badge bg-danger rounded-pill">{{ $contact->unread }}</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane" id="groups">
                                            <div class="chat-message-list" data-simplebar>
                                                <div class="pt-3">
                                                    <div class="px-3">
                                                        <h5 class="font-size-14 mb-3">Groups</h5>
                                                    </div>
                                                    <ul class="list-unstyled chat-list">
                                                        <li>
                                                            <a href="#">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 avatar-sm me-3">
                                                                        <span class="avatar-title rounded-circle bg-primary-subtle  text-primary">
                                                                            G
                                                                        </span>
                                                                    </div>
                                                                    
                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-13 mb-0">General</h5>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a href="#">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 avatar-sm me-3">
                                                                        <span class="avatar-title rounded-circle bg-primary-subtle  text-primary">
                                                                            R
                                                                        </span>
                                                                    </div>
                                                                    
                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-13 mb-0">Reporting</h5>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a href="#">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 avatar-sm me-3">
                                                                        <span class="avatar-title rounded-circle bg-primary-subtle  text-primary">
                                                                            M
                                                                        </span>
                                                                    </div>
                                                                    
                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-13 mb-0">Meeting</h5>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a href="#">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 avatar-sm me-3">
                                                                        <span class="avatar-title rounded-circle bg-primary-subtle  text-primary">
                                                                            A
                                                                        </span>
                                                                    </div>
                                                                    
                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-13 mb-0">Project A</h5>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a href="#">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 avatar-sm me-3">
                                                                        <span class="avatar-title rounded-circle bg-primary-subtle  text-primary">
                                                                            B
                                                                        </span>
                                                                    </div>
                                                                    
                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-13 mb-0">Project B</h5>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane" id="contacts">
                                            <div class="chat-message-list" data-simplebar>
                                                <div class="pt-3">
                                                    <div class="px-3">
                                                        <h5 class="font-size-14 mb-3">Contacts</h5>
                                                    </div>

                                                    <div>
                                                        <div>
                                                            <div class="px-3 contact-list">A</div>

                                                            <ul class="list-unstyled chat-list">
                                                                <li>
                                                                    <a href="#">
                                                                        <h5 class="font-size-13 mb-0">Adam Miller</h5>
                                                                    </a>
                                                                </li>
            
                                                                <li>
                                                                    <a href="#">
                                                                        <h5 class="font-size-13 mb-0">Alfonso Fisher</h5>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>

                                                        <div class="mt-4">
                                                            <div class="px-3 contact-list">B</div>

                                                            <ul class="list-unstyled chat-list">
                                                                <li>
                                                                    <a href="#">
                                                                        <h5 class="font-size-13 mb-0">Bonnie Harney</h5>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>

                                                        <div class="mt-4">
                                                            <div class="px-3 contact-list">C</div>

                                                            <ul class="list-unstyled chat-list">
                                                                <li>
                                                                    <a href="#">
                                                                        <h5 class="font-size-13 mb-0">Charles Brown</h5>
                                                                    </a>
                                                                    <a href="#">
                                                                        <h5 class="font-size-13 mb-0">Carmella Jones</h5>
                                                                    </a>
                                                                    <a href="#">
                                                                        <h5 class="font-size-13 mb-0">Carrie Williams</h5>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>

                                                        <div class="mt-4">
                                                            <div class="px-3 contact-list">D</div>

                                                            <ul class="list-unstyled chat-list">
                                                                <li>
                                                                    <a href="#">
                                                                        <h5 class="font-size-13 mb-0">Dolores Minter</h5>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- end chat-leftsidebar -->

                            <div class="w-100 user-chat mt-4 mt-sm-0 ms-lg-3">
                                <div class="card">
                                    <div class="p-3 px-lg-4 border-bottom">
                                        <div class="row">
                                            <div class="col-xl-4 col-7">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 avatar me-3 d-sm-block d-none">
                                                        <img src="assets/images/users/avatar-6.jpg" alt="" class="img-fluid d-block rounded-circle">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="font-size-16 mb-1 text-truncate"><a href="#" class="text-reset">{{ $active?->name ?? 'Chat' }}</a></h5>
                                                        <p class="text-muted text-truncate mb-0">Online</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-8 col-5">
                                                <ul class="list-inline user-chat-nav text-end mb-0">
                                                    <li class="list-inline-item">
                                                        <div class="dropdown">
                                                            <button class="btn nav-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="bx bx-search"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-md p-2">
                                                                <form class="px-2">
                                                                    <div>
                                                                        <input type="text" class="form-control border bg-light-subtle " placeholder="Search...">
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </li>
    
                                                    <li class="list-inline-item">
                                                        <div class="dropdown">
                                                            <button class="btn nav-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="bx bx-dots-horizontal-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="#">Profile</a>
                                                                <a class="dropdown-item" href="#">Archive</a>
                                                                <a class="dropdown-item" href="#">Muted</a>
                                                                <a class="dropdown-item" href="#">Delete</a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>                                                                                                                                                                                                                                                                                        
                                            </div>
                                        </div>
                                    </div>

                                    <div class="chat-conversation p-3" data-simplebar>
                                        <ul class="list-unstyled mb-0">
                                            <li class="chat-day-title"> 
                                                <span class="title">Today</span>
                                            </li>
                                            @forelse ($messages as $message)
                                            <li class="{{ $message->is_outgoing ? 'right' : '' }}">
                                                <div class="conversation-list">
                                                    <div class="d-flex">
                                                        @unless ($message->is_outgoing)
                                                        @if ($active?->avatar)
                                                        <img src="{{ $active->avatar }}" class="rounded-circle avatar" alt="">
                                                        @else
                                                        <div class="avatar me-2"><div class="avatar-title rounded-circle bg-primary-subtle text-primary">{{ strtoupper(substr($active?->name ?? 'U', 0, 1)) }}</div></div>
                                                        @endif
                                                        @endunless
                                                        <div class="flex-1">
                                                            <div class="ctext-wrap">
                                                                <div class="ctext-wrap-content">
                                                                    <div class="conversation-name"><span class="time">{{ $message->sent_at?->format('g:i A') ?? '' }}</span></div>
                                                                    <p class="mb-0{{ $message->is_outgoing ? ' text-start' : '' }}">{!! nl2br(e($message->body)) !!}</p>
                                                                </div>
                                                                <div class="dropdown align-self-start">
                                                                    <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                                    </a>
                                                                    <div class="dropdown-menu">
                                                                        <a class="dropdown-item" href="#">Copy</a>
                                                                        <a class="dropdown-item" href="#">Save</a>
                                                                        <a class="dropdown-item" href="#">Forward</a>
                                                                        <a class="dropdown-item" href="#">Delete</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if ($message->is_outgoing)
                                                        @if (auth()->user()?->avatar)
                                                        <img src="{{ auth()->user()->avatar }}" class="rounded-circle avatar" alt="">
                                                        @else
                                                        <div class="avatar ms-2"><div class="avatar-title rounded-circle bg-success text-white">{{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}</div></div>
                                                        @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </li>
                                            @empty
                                            <li>
                                                <div class="text-center text-muted py-4">No messages yet.</div>
                                            </li>
                                            @endforelse
                                            </ul>
                                                                </div>
                                                                
                                                                <div class="dropdown align-self-start">
                                                                    <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                                    </a>
                                                                    <div class="dropdown-menu">
                                                                        <a class="dropdown-item" href="#">Copy</a>
                                                                        <a class="dropdown-item" href="#">Save</a>
                                                                        <a class="dropdown-item" href="#">Forward</a>
                                                                        <a class="dropdown-item" href="#">Delete</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                           
                                                        </div>
                                                        <img src="assets/images/users/avatar-3.jpg" class="rounded-circle avatar" alt="">
                                                    </div>
                                                </div>
                                            </li>

                                            <li>
                                                <div class="conversation-list">
                                                    <div class="d-flex">
                                                        <img src="assets/images/users/avatar-6.jpg" class="rounded-circle avatar" alt="">
                                                        <div class="flex-1">
                                                            <div class="ctext-wrap">
                                                                <div class="ctext-wrap-content">
                                                                    <div class="conversation-name"><span class="time">10:06</span></div>
                                                                    <p class="mb-0">
                                                                       Thank You very much, I am waiting Project.
                                                                    </p>
                                                                </div>
                                                                <div class="dropdown align-self-start">
                                                                    <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                                    </a>
                                                                    <div class="dropdown-menu">
                                                                        <a class="dropdown-item" href="#">Copy</a>
                                                                        <a class="dropdown-item" href="#">Save</a>
                                                                        <a class="dropdown-item" href="#">Forward</a>
                                                                        <a class="dropdown-item" href="#">Delete</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            

                                            <li class="right">
                                                <div class="conversation-list">
                                                    <div class="d-flex">
                                                        <div class="flex-1">
                                                            <div class="ctext-wrap">
                                                                <div class="ctext-wrap-content">
                                                                    <div class="conversation-name"><span class="time">10:08</span></div>
                                                                    <p class="mb-0 text-start">
                                                                        When someone thanks us, our automatic response is to say, “You’re welcome.” This is something that we have 
                                                                        learned from our parents and family and have been doing for a long time.
                                                                    </p>
                                                                </div>
                                                                
                                                                <div class="dropdown align-self-start">
                                                                    <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                                    </a>
                                                                    <div class="dropdown-menu">
                                                                        <a class="dropdown-item" href="#">Copy</a>
                                                                        <a class="dropdown-item" href="#">Save</a>
                                                                        <a class="dropdown-item" href="#">Forward</a>
                                                                        <a class="dropdown-item" href="#">Delete</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                           
                                                        </div>
                                                        <img src="assets/images/users/avatar-3.jpg" class="rounded-circle avatar" alt="">
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="p-3 border-top">
                                        <div class="row">
                                            <div class="col">
                                                <div class="position-relative">
                                                    @if ($active)
                                                            <form method="POST" action="{{ route('apps-chat.store', $active) }}" class="d-flex gap-2">
                                                                @csrf
                                                                <input type="text" name="body" class="form-control border bg-light-subtle " placeholder="Enter Message..." required>
                                                                <button type="submit" class="btn btn-primary">Send</button>
                                                            </form>
                                                            @else
                                                            <input type="text" class="form-control border bg-light-subtle " placeholder="Enter Message..." disabled>
                                                            @endif
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-primary chat-send w-md waves-effect waves-light"><span class="d-none d-sm-inline-block me-2">Send</span> <i class="mdi mdi-send float-end"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end user chat -->
                        </div>
                        <!-- End d-lg-flex  -->

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

        <script src="assets/js/app.js"></script>

    </body>

</html>
