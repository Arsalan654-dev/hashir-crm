@php
    $authUser = auth()->user();
    $notifications = $authUser?->notifications()->latest()->take(6)->get() ?? collect();
    $unreadCount = $authUser?->unreadNotifications()->count() ?? 0;
    $orgBadge = ($authUser !== null && ! $authUser->isSuperAdmin()) ? $authUser->organization : null;
@endphp
<header id="page-topbar" class="isvertical-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ url('/') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/logo-cyprus-mark.svg') }}" alt="{{ \App\Support\Branding::name() }}" height="26">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset(\App\Support\Branding::logo()) }}" alt="{{ \App\Support\Branding::name() }}" height="30">
                    </span>
                </a>

                <a href="{{ url('/') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/logo-cyprus-mark.svg') }}" alt="{{ \App\Support\Branding::name() }}" height="26">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset(\App\Support\Branding::logo(true)) }}" alt="{{ \App\Support\Branding::name() }}" height="30">
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect vertical-menu-btn">
                <i class="bx bx-menu align-middle"></i>
            </button>

            <div class="d-none d-lg-block">
                <div class="dropdown d-inline-block">
                    <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-search-alt"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-start p-0">
                        <div class="p-3">
                            <form action="{{ url('/search') }}" method="GET">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="q" placeholder="Search leads, sales, customers...">
                                    <button class="btn btn-primary" type="submit"><i class="bx bx-search-alt"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center">
            @if ($orgBadge !== null && ! $orgBadge->isPaid() && auth()->user()->hasPermission('billing.view'))
                <a href="{{ route('billing.index') }}" class="badge bg-danger text-white me-2 py-2" title="Subscription payment required">
                    <i class="bx bx-error-circle me-1"></i> Not Paid
                </a>
            @endif

            @if ($authUser !== null && $authUser->hasPermission('notifications.view'))
                <div class="dropdown d-inline-block">
                    <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-bell bx-tada"></i>
                        @if ($unreadCount > 0)
                            <span class="badge bg-danger rounded-pill">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">
                        <div class="p-3">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0">Notifications</h6>
                                </div>
                                <div class="col-auto">
                                    <a href="{{ url('/notifications/read-all') }}" class="small text-decoration-underline">Mark all as read</a>
                                </div>
                            </div>
                        </div>
                        <div data-simplebar style="max-height: 260px;">
                            @forelse ($notifications as $notification)
                                <a href="{{ $notification->data['url'] ?? url('/notifications') }}" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title bg-{{ $notification->read_at ? 'secondary' : 'primary' }}-subtle text-{{ $notification->read_at ? 'secondary' : 'primary' }} rounded-circle font-size-16">
                                                <i class="bx bx-bell"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-1">{{ $notification->data['title'] ?? 'Notification' }}</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1">{{ \Illuminate\Support\Str::limit($notification->data['message'] ?? '', 70) }}</p>
                                                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> {{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="p-4 text-center text-muted">
                                    <i class="bx bx-bell-off font-size-24 d-block mb-2"></i>
                                    No notifications yet
                                </div>
                            @endforelse
                        </div>
                        <div class="p-2 border-top">
                            <a class="btn btn-sm btn-link font-size-14 w-100 text-center" href="{{ url('/notifications') }}">View all</a>
                        </div>
                    </div>
                </div>
            @endif

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-grid-alt bx-spin bx-rotate-90"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ url('/apps-chat') }}"><i class="bx bx-message-dots me-2"></i> Team Chat</a>
                    <a class="dropdown-item" href="{{ url('/sales/create') }}"><i class="bx bx-plus-circle me-2"></i> New Sale Entry</a>
                    <a class="dropdown-item" href="{{ url('/leads/create') }}"><i class="bx bx-target-lock me-2"></i> Add Lead</a>
                    <a class="dropdown-item" href="{{ url('/employees') }}"><i class="bx bx-group me-2"></i> Employees</a>
                    <a class="dropdown-item" href="{{ url('/reports') }}"><i class="bx bx-bar-chart-alt-2 me-2"></i> Reports</a>
                </div>
            </div>

            <div class="dropdown d-inline-block language-switch">
                <button type="button" class="btn header-item waves-effect" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="header-lang-img" src="{{ asset('assets/images/flags/us.jpg') }}" alt="Header Language" height="16">
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="eng">
                        <img src="{{ asset('assets/images/flags/us.jpg') }}" alt="user-image" class="me-1" height="12"> <span class="align-middle">English</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="sp">
                        <img src="{{ asset('assets/images/flags/spain.jpg') }}" alt="user-image" class="me-1" height="12"> <span class="align-middle">Spanish</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="gr">
                        <img src="{{ asset('assets/images/flags/germany.jpg') }}" alt="user-image" class="me-1" height="12"> <span class="align-middle">German</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="it">
                        <img src="{{ asset('assets/images/flags/italy.jpg') }}" alt="user-image" class="me-1" height="12"> <span class="align-middle">Italian</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="ru">
                        <img src="{{ asset('assets/images/flags/russia.jpg') }}" alt="user-image" class="me-1" height="12"> <span class="align-middle">Russian</span>
                    </a>
                </div>
            </div>

            <div class="d-none d-sm-inline-block">
                <button type="button" class="btn header-item waves-effect" data-toggle="fullscreen">
                    <i class="bx bx-fullscreen"></i>
                </button>
            </div>

            <button type="button" class="btn header-item noti-icon waves-effect right-bar-toggle">
                <i class="bx bx-cog bx-spin"></i>
            </button>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="{{ $authUser?->avatarUrl() }}" alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1 fw-medium font-size-15">{{ $authUser?->name }}</span>
                    <i class="bx bx-chevron-down align-middle d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <span class="dropdown-header text-truncate">
                        <span class="d-block fw-semibold">{{ $authUser?->name }}</span>
                        <span class="text-muted font-size-12">{{ $authUser?->assignedRole?->name ?? 'Member' }}</span>
                    </span>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ url('/contacts-profile') }}"><i class="mdi mdi-account-circle text-muted font-size-16 align-middle me-2"></i> <span class="align-middle">My Profile</span></a>
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
@include('partials.flash')
