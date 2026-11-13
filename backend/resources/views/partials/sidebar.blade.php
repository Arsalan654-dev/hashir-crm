@php
    $authUser = auth()->user();
    $can = fn (string ...$permissions): bool => $authUser !== null && $authUser->hasAnyPermission($permissions);
    $isHome = request()->routeIs('home');
@endphp
@if ($authUser !== null)
<div id="sidebar-menu">
    <!-- Left Menu Start -->
    <ul class="metismenu list-unstyled" id="side-menu">
        <li class="menu-title">Main</li>

        @if ($can('dashboard.view'))
            <li class="{{ $isHome ? 'mm-active' : '' }}">
                <a href="{{ url('/') }}" class="waves-effect">
                    <i class="bx bx-home-circle"></i>
                    <span class="menu-item">Dashboard</span>
                </a>
            </li>
        @endif
        @if ($can('analytics.view_all', 'reports.view'))
            <li class="{{ request()->routeIs('analytics') ? 'mm-active' : '' }}">
                <a href="{{ url('/analytics') }}" class="waves-effect">
                    <i class="bx bx-pie-chart-alt-2"></i>
                    <span class="menu-item">Analytics</span>
                    <span class="badge rounded-pill bg-primary">Live</span>
                </a>
            </li>
        @endif
        @if ($can('sales.view'))
            <li class="{{ request()->routeIs('dashboard-sales') ? 'mm-active' : '' }}">
                <a href="{{ url('/dashboard-sales') }}" class="waves-effect">
                    <i class="bx bx-line-chart"></i>
                    <span class="menu-item">Sales Dashboard</span>
                </a>
            </li>
        @endif

        @if ($can('leads.view', 'sales.view', 'customers.view', 'orders.view', 'invoices.view', 'products.view'))
            <li class="menu-title">CRM &amp; Sales</li>
        @endif

        @if ($can('sales.view'))
            <li class="{{ request()->routeIs('sales.*') ? 'mm-active' : '' }}">
                <a href="javascript: void(0);" class="has-arrow waves-effect">
                    <i class="bx bx-cart-alt"></i>
                    <span class="menu-item">Sales</span>
                </a>
                <ul class="sub-menu" aria-expanded="false">
                    <li><a href="{{ url('/sales') }}">All Sales Records</a></li>
                    @if ($can('sales.create'))
                        <li><a href="{{ url('/sales/create') }}">New Sale Entry</a></li>
                    @endif
                    <li><a href="{{ url('/sales/my') }}">My Sales</a></li>
                    <li><a href="{{ url('/sales/payments') }}">Payments</a></li>
                    @if ($can('teams.view'))
                        <li><a href="{{ url('/sales-teams') }}">Sales Teams</a></li>
                    @endif
                    @if ($can('reports.view'))
                        <li><a href="{{ url('/sales/reports') }}">Sales Reports</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($can('leads.view'))
            <li class="{{ request()->routeIs('leads.*') ? 'mm-active' : '' }}">
                <a href="javascript: void(0);" class="has-arrow waves-effect">
                    <i class="bx bx-target-lock"></i>
                    <span class="menu-item">Leads</span>
                </a>
                <ul class="sub-menu" aria-expanded="false">
                    <li><a href="{{ url('/leads') }}">All Leads</a></li>
                    <li><a href="{{ url('/leads/pipeline') }}">Pipeline Board</a></li>
                    @if ($can('leads.view_team'))
                        <li><a href="{{ url('/leads/today') }}">Today's Leads</a></li>
                    @endif
                    @if ($can('leads.create'))
                        <li><a href="{{ url('/leads/create') }}">Add Lead</a></li>
                    @endif
                    @if ($can('leads.view_all'))
                        <li><a href="{{ url('/leads/assigned') }}">Assignment Queue</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($can('customers.view', 'contacts.view'))
            <li class="{{ request()->routeIs('customers.*', 'contacts-*') ? 'mm-active' : '' }}">
                <a href="javascript: void(0);" class="has-arrow waves-effect">
                    <i class="bx bx-user-voice"></i>
                    <span class="menu-item">Customers</span>
                </a>
                <ul class="sub-menu" aria-expanded="false">
                    @if ($can('customers.view'))
                        <li><a href="{{ url('/customers') }}">Customer List</a></li>
                    @endif
                    @if ($can('contacts.view'))
                        <li><a href="{{ url('/contacts-list') }}">Directory</a></li>
                        <li><a href="{{ url('/contacts-grid') }}">Directory Grid</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($can('products.view', 'orders.view', 'invoices.view'))
            <li class="{{ request()->routeIs('products.*', 'orders.*', 'invoices.*', 'ecommerce-*') ? 'mm-active' : '' }}">
                <a href="javascript: void(0);" class="has-arrow waves-effect">
                    <i class="bx bx-package"></i>
                    <span class="menu-item">Catalogue</span>
                </a>
                <ul class="sub-menu" aria-expanded="false">
                    @if ($can('products.view'))
                        <li><a href="{{ url('/products') }}">Products &amp; Services</a></li>
                    @endif
                    @if ($can('products.create'))
                        <li><a href="{{ url('/products/create') }}">Add Product</a></li>
                    @endif
                    @if ($can('orders.view'))
                        <li><a href="{{ url('/orders') }}">Orders</a></li>
                    @endif
                    @if ($can('invoices.view'))
                        <li><a href="{{ url('/invoices') }}">Invoices</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($can('orders.view'))
            <li class="menu-title">Operations</li>
            <li class="{{ request()->routeIs('kitchen.*') ? 'mm-active' : '' }}">
                <a href="{{ url('/kitchen') }}" class="waves-effect">
                    <i class="bx bx-restaurant"></i>
                    <span class="menu-item">Kitchen / Orders</span>
                    @php
                        $kitchenPending = \App\Models\KitchenOrder::query()->where('status', 'pending')->count();
                    @endphp
                    @if ($kitchenPending > 0)
                        <span class="badge rounded-pill bg-danger">{{ $kitchenPending }}</span>
                    @endif
                </a>
            </li>
        @endif

        @if ($can('hr.view', 'employees.view', 'attendance.view_own', 'leaves.view_own', 'payroll.view_own', 'targets.view_own'))
            <li class="menu-title">HR &amp; Payroll</li>

            <li class="{{ request()->routeIs('employees.*') ? 'mm-active' : '' }}">
                <a href="javascript: void(0);" class="has-arrow waves-effect">
                    <i class="bx bx-group"></i>
                    <span class="menu-item">Employees</span>
                </a>
                <ul class="sub-menu" aria-expanded="false">
                    @if ($can('employees.view'))
                        <li><a href="{{ url('/employees') }}">Employee Directory</a></li>
                    @endif
                    @if ($can('employees.create'))
                        <li><a href="{{ url('/employees/create') }}">Add Employee</a></li>
                    @endif
                    @if ($can('hr.view'))
                        <li><a href="{{ url('/departments') }}">Departments</a></li>
                        <li><a href="{{ url('/designations') }}">Designations</a></li>
                    @endif
                </ul>
            </li>

            @if ($can('attendance.view_all', 'attendance.view_own', 'attendance.manage'))
                <li class="{{ request()->routeIs('attendance.*') ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-time-five"></i>
                        <span class="menu-item">Attendance</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        @if ($can('attendance.view_all', 'attendance.manage'))
                            <li><a href="{{ url('/attendance') }}">Daily Register</a></li>
                            <li><a href="{{ url('/attendance/report') }}">Monthly Report</a></li>
                        @endif
                        <li><a href="{{ url('/attendance/my') }}">My Attendance</a></li>
                    </ul>
                </li>
            @endif

            @if ($can('leaves.view_all', 'leaves.view_own'))
                <li class="{{ request()->routeIs('leaves.*') ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-calendar-x"></i>
                        <span class="menu-item">Leave</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        @if ($can('leaves.view_all', 'leaves.approve'))
                            <li><a href="{{ url('/leaves') }}">Leave Requests</a></li>
                        @endif
                        <li><a href="{{ url('/leaves/my') }}">My Leaves</a></li>
                        @if ($can('holidays.view'))
                            <li><a href="{{ url('/holidays') }}">Holidays</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if ($can('payroll.view_all', 'payroll.view_own', 'compensations.view'))
                <li class="{{ request()->routeIs('payroll.*', 'compensations.*') ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-wallet"></i>
                        <span class="menu-item">Payroll</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        @if ($can('payroll.view_all', 'payroll.manage'))
                            <li><a href="{{ url('/payroll') }}">Monthly Payroll</a></li>
                        @endif
                        <li><a href="{{ url('/payroll/my') }}">My Payslips</a></li>
                        @if ($can('compensations.view', 'compensations.manage'))
                            <li><a href="{{ url('/compensations') }}">Bonuses &amp; Incentives</a></li>
                        @endif
                        @if ($can('allowances.view', 'allowances.manage'))
                            <li><a href="{{ url('/payroll/allowances') }}">Allowances &amp; Fuel</a></li>
                        @endif
                        @if ($can('commission_rules.view', 'commission_rules.manage'))
                            <li><a href="{{ url('/commission-rules') }}">Commission Rules</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if ($can('targets.view_all', 'targets.view_own'))
                <li class="{{ request()->routeIs('targets.*') ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-flag"></i>
                        <span class="menu-item">Targets</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        @if ($can('targets.manage', 'targets.view_all'))
                            <li><a href="{{ url('/targets') }}">Team Targets</a></li>
                            <li><a href="{{ url('/targets/create') }}">Set Target</a></li>
                        @endif
                        <li><a href="{{ url('/targets/my') }}">My Targets</a></li>
                    </ul>
                </li>
            @endif
        @endif

        @if ($can('chat.view', 'whatsapp.view', 'email.view', 'knowledge.view', 'bot.view', 'support.view'))
            <li class="menu-title">Communication</li>
        @endif

        @if ($can('support.view'))
            <li class="{{ request()->routeIs('support.*') ? 'mm-active' : '' }}">
                <a href="{{ url('/support') }}" class="waves-effect">
                    <i class="bx bx-headphone"></i>
                    <span class="menu-item">Support Inbox</span>
                    @php
                        $supportWaiting = \App\Models\BotConversation::query()->where('status', 'escalated')->count();
                    @endphp
                    @if ($supportWaiting > 0)
                        <span class="badge rounded-pill bg-danger">{{ $supportWaiting }}</span>
                    @endif
                </a>
            </li>
        @endif

        @if ($can('chat.view'))
            <li class="{{ request()->routeIs('chat.*', 'apps-chat') ? 'mm-active' : '' }}">
                <a href="{{ url('/chat') }}" class="waves-effect">
                    <i class="bx bx-message-dots"></i>
                    <span class="menu-item">Team Chat</span>
                </a>
            </li>
        @endif

        @if ($can('whatsapp.view'))
            <li class="{{ request()->routeIs('whatsapp.*') ? 'mm-active' : '' }}">
                <a href="javascript: void(0);" class="has-arrow waves-effect">
                    <i class="bx bxl-whatsapp"></i>
                    <span class="menu-item">WhatsApp</span>
                </a>
                <ul class="sub-menu" aria-expanded="false">
                    <li><a href="{{ url('/whatsapp') }}">Inbox</a></li>
                    <li><a href="{{ url('/whatsapp/contacts') }}">Contacts</a></li>
                    @if ($can('whatsapp.broadcast'))
                        <li><a href="{{ url('/whatsapp/broadcast') }}">Broadcast</a></li>
                    @endif
                    @if ($can('whatsapp.settings'))
                        <li><a href="{{ url('/whatsapp/settings') }}">API Settings</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($can('email.view'))
            <li class="{{ request()->routeIs('email-*', 'gmail.*') ? 'mm-active' : '' }}">
                <a href="javascript: void(0);" class="has-arrow waves-effect">
                    <i class="bx bx-envelope"></i>
                    <span class="menu-item">Email</span>
                </a>
                <ul class="sub-menu" aria-expanded="false">
                    <li><a href="{{ url('/email-inbox') }}">Inbox</a></li>
                    <li><a href="{{ url('/email/compose') }}">Compose</a></li>
                    @if ($can('email.settings'))
                        <li><a href="{{ url('/gmail/settings') }}">Gmail Connection</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($can('knowledge.view', 'bot.view'))
            <li class="{{ request()->routeIs('knowledge.*', 'bot.*') ? 'mm-active' : '' }}">
                <a href="javascript: void(0);" class="has-arrow waves-effect">
                    <i class="bx bx-bot"></i>
                    <span class="menu-item">AI Bot</span>
                </a>
                <ul class="sub-menu" aria-expanded="false">
                    @if ($can('knowledge.view'))
                        <li><a href="{{ url('/knowledge') }}">Knowledge Base</a></li>
                    @endif
                    @if ($can('bot.view'))
                        <li><a href="{{ url('/bot/conversations') }}">Bot Conversations</a></li>
                    @endif
                    @if ($can('bot.settings'))
                        <li><a href="{{ url('/bot/settings') }}">Bot Settings</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($can('calendar.view', 'todo.view', 'files.view'))
            <li class="menu-title">Workspace</li>
            @if ($can('calendar.view'))
                <li class="{{ request()->routeIs('apps-calendar') ? 'mm-active' : '' }}">
                    <a href="{{ url('/apps-calendar') }}" class="waves-effect">
                        <i class="bx bx-calendar"></i><span class="menu-item">Calendar</span>
                    </a>
                </li>
            @endif
            @if ($can('todo.view'))
                <li class="{{ request()->routeIs('apps-todo') ? 'mm-active' : '' }}">
                    <a href="{{ url('/apps-todo') }}" class="waves-effect">
                        <i class="bx bx-task"></i><span class="menu-item">Tasks</span>
                    </a>
                </li>
            @endif
            @if ($can('files.view'))
                <li class="{{ request()->routeIs('apps-file-manager') ? 'mm-active' : '' }}">
                    <a href="{{ url('/apps-file-manager') }}" class="waves-effect">
                        <i class="bx bx-folder"></i><span class="menu-item">File Manager</span>
                    </a>
                </li>
            @endif
        @endif

        @if ($can('reports.view', 'reports.export'))
            <li class="menu-title">Insights</li>
            <li class="{{ request()->routeIs('reports.*') ? 'mm-active' : '' }}">
                <a href="javascript: void(0);" class="has-arrow waves-effect">
                    <i class="bx bx-bar-chart-alt-2"></i>
                    <span class="menu-item">Reports</span>
                </a>
                <ul class="sub-menu" aria-expanded="false">
                    <li><a href="{{ url('/reports') }}">Report Centre</a></li>
                    <li><a href="{{ url('/sales/reports') }}">Sales Performance</a></li>
                    <li><a href="{{ url('/payroll/reports') }}">Payroll Summary</a></li>
                </ul>
            </li>
        @endif

        @if ($can('organizations.view'))
            <li class="menu-title">Platform</li>
            <li class="{{ request()->routeIs('organizations.*') ? 'mm-active' : '' }}">
                <a href="{{ url('/organizations') }}" class="waves-effect">
                    <i class="bx bx-buildings"></i><span class="menu-item">Organizations</span>
                </a>
            </li>
        @endif

        @if ($can('users.view', 'roles.view', 'settings.view', 'activity_logs.view', 'billing.view'))
            <li class="menu-title">Administration</li>
            @if ($can('billing.view'))
                <li class="{{ request()->routeIs('billing.*') ? 'mm-active' : '' }}">
                    <a href="{{ url('/billing') }}" class="waves-effect">
                        <i class="bx bx-credit-card"></i><span class="menu-item">Billing</span>
                    </a>
                </li>
            @endif
            @if ($can('users.view'))
                <li class="{{ request()->routeIs('users.*') ? 'mm-active' : '' }}">
                    <a href="{{ url('/users') }}" class="waves-effect">
                        <i class="bx bx-user-circle"></i><span class="menu-item">Users</span>
                    </a>
                </li>
            @endif
            @if ($can('roles.view', 'permissions.manage'))
                <li class="{{ request()->routeIs('roles.*', 'permissions.*') ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-shield-quarter"></i>
                        <span class="menu-item">Roles &amp; Access</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ url('/roles') }}">Roles</a></li>
                        @if ($can('permissions.manage', 'roles.view'))
                            <li><a href="{{ url('/permissions') }}">Permission Matrix</a></li>
                        @endif
                    </ul>
                </li>
            @endif
            @if ($can('settings.view', 'settings.theme', 'integrations.manage', 'settings.mail'))
                <li class="{{ request()->routeIs('settings.*', 'integrations.*') ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-cog"></i>
                        <span class="menu-item">Settings</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ url('/settings') }}">General</a></li>
                        <li><a href="{{ url('/settings/theme') }}">Theme &amp; Branding</a></li>
                        @if ($can('settings.mail'))
                            <li><a href="{{ url('/settings/email') }}">Email / SMTP</a></li>
                        @endif
                        <li><a href="{{ url('/settings/integrations') }}">Integrations &amp; API Keys</a></li>
                    </ul>
                </li>
            @endif
            @if ($can('activity_logs.view'))
                <li class="{{ request()->routeIs('activity-logs.*') ? 'mm-active' : '' }}">
                    <a href="{{ url('/activity-logs') }}" class="waves-effect">
                        <i class="bx bx-history"></i><span class="menu-item">Activity Log</span>
                    </a>
                </li>
            @endif
        @endif

        <li class="menu-title">Account</li>
        <li>
            <a href="{{ url('/auth-logout') }}" class="waves-effect text-danger"
               onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                <i class="bx bx-log-out-circle"></i>
                <span class="menu-item">Logout</span>
            </a>
        </li>
        <form id="sidebar-logout-form" action="{{ url('/auth-logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </ul>
</div>
<!-- Sidebar -->
@endif
