<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommissionRuleController;
use App\Http\Controllers\CompensationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\EmailSettingsController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\GmailController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KitchenOrderController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PaymentPageController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesTeamController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserAllowanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\WhatsappWebhookController;
use App\Models\EmailMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/auth-login', [AuthController::class, 'showLogin'])->name('auth-login');
    Route::post('/auth-login', [AuthController::class, 'login'])->name('auth-login.post');
    Route::get('/auth-recoverpw', [AuthController::class, 'showRecover'])->name('auth-recoverpw');
    Route::post('/auth-recoverpw', [AuthController::class, 'recover'])->name('auth-recoverpw.post');
    Route::get('/auth-reset-password', [AuthController::class, 'showReset'])->name('auth-reset-password');
    Route::post('/auth-reset-password', [AuthController::class, 'reset'])->name('auth-reset-password.post');
    Route::get('/auth-confirm-mail', function () {
        return view('pages.auth-confirm-mail', [
            'resetUrl' => session('reset_url'),
            'status' => session('status'),
        ]);
    })->name('auth-confirm-mail');
});

Route::any('/auth-logout', function (Request $request) {
    if (auth()->check()) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    return redirect()->route('auth-login');
})->name('auth-logout');

// WhatsApp webhook (public, verified by token / signature)
Route::get('/webhooks/whatsapp', [WhatsappWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [WhatsappWebhookController::class, 'handle'])->name('webhooks.whatsapp.handle');
Route::post('/webhooks/whatsapp/whatchimp', [WhatsappWebhookController::class, 'handleWhatchimp'])->name('webhooks.whatsapp.whatchimp');

// Public customer payment page (token protected)
Route::get('/pay/{order:order_no}', [PaymentPageController::class, 'show'])->name('pay.show');
Route::post('/pay/{order:order_no}', [PaymentPageController::class, 'submit'])->name('pay.submit');
Route::post('/api/bot/lead', [WhatsappWebhookController::class, 'botLead'])->name('api.bot.lead');
Route::get('/api/bot/knowledge', [WhatsappWebhookController::class, 'knowledge'])->name('api.bot.knowledge');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'permission:dashboard.view'])->get('/dashboard', [DashboardController::class, 'index'])->name('home');
Route::middleware('auth')->get('/dashboard/profile', [ProfileController::class, 'edit'])->name('dashboard.profile');

Route::middleware('auth')->group(function () {
    // Lock screen / two step (kept from template)
    Route::get('/auth-lock-screen', [AuthController::class, 'showLock'])->name('auth-lock-screen');
    Route::post('/auth-lock-screen', [AuthController::class, 'unlock'])->name('auth-lock-screen.post');
    Route::get('/auth-two-step-verification', [AuthController::class, 'showTwoStep'])->name('auth-two-step-verification');
    Route::post('/auth-two-step-verification', [AuthController::class, 'twoStep'])->name('auth-two-step-verification.post');

    // Dashboard & analytics
    Route::get('/admin-dashboard', [DashboardController::class, 'index'])->name('admin-dashboard');
    Route::get('/dashboard-sales', [DashboardController::class, 'sales'])->name('dashboard-sales');
    Route::middleware('permission:settings.view')->get('/dashboard-ecommerce', [DashboardController::class, 'ecommerce'])->name('dashboard-ecommerce');
    Route::get('/analytics', [AnalyticsController::class, 'index'])
        ->middleware('permission:analytics.view_all,analytics.view_own,reports.view')
        ->name('analytics');
    Route::get('/analytics/data', [AnalyticsController::class, 'data'])
        ->middleware('permission:analytics.view_all,analytics.view_own,reports.view')
        ->name('analytics.data');
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Profile
    Route::get('/contacts-profile', [ProfileController::class, 'edit'])->name('contacts-profile');
    Route::get('/contacts-profile-template', [ProfileController::class, 'template'])
        ->middleware('permission:settings.view')
        ->name('profile.template');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/pdf', [ProfileController::class, 'pdf'])->name('profile.pdf');

    // Notifications
    Route::get('/api/notifications', [NotificationController::class, 'index'])->name('notifications.list');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read');
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.mark');

    /*
    |----------------------------------------------------------------------
    | Sales
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:sales.view')->get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/my', [SaleController::class, 'my'])->name('sales.my');
    Route::get('/sales/payments', [SaleController::class, 'payments'])->name('sales.payments.index');
    Route::get('/sales/reports', [SaleController::class, 'reports'])
        ->middleware('permission:reports.view,sales.view_all')
        ->name('sales.reports');
    Route::get('/sales/export', [SaleController::class, 'export'])
        ->middleware('permission:reports.export,sales.view_all')
        ->name('sales.export');
    Route::middleware('permission:sales.create')->group(function () {
        Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    });
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::middleware('permission:sales.update')->group(function () {
        Route::get('/sales/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
        Route::put('/sales/{sale}', [SaleController::class, 'update'])->name('sales.update');
    });
    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])
        ->middleware('permission:sales.delete')
        ->name('sales.destroy');
    Route::post('/sales/{sale}/confirm', [SaleController::class, 'confirm'])
        ->middleware('permission:sales.confirm')
        ->name('sales.confirm');
    Route::post('/sales/{sale}/done', [SaleController::class, 'markDone'])
        ->middleware('permission:sales.mark_done')
        ->name('sales.done');
    Route::post('/sales/{sale}/reject', [SaleController::class, 'reject'])
        ->middleware('permission:sales.mark_done,sales.confirm')
        ->name('sales.reject');
    Route::post('/sales/{sale}/payments', [SaleController::class, 'storePayment'])
        ->middleware('permission:sales.payments,sales.update')
        ->name('sales.payments.store');
    Route::post('/sales/{sale}/invoice', [SaleController::class, 'generateInvoice'])
        ->middleware('permission:invoices.create')
        ->name('sales.invoice');

    // Sales teams
    Route::middleware('permission:teams.view')->group(function () {
        Route::get('/sales-teams', [SalesTeamController::class, 'index'])->name('teams.index');
    });
    Route::middleware('permission:teams.manage')->group(function () {
        Route::post('/sales-teams', [SalesTeamController::class, 'store'])->name('teams.store');
        Route::put('/sales-teams/{team}', [SalesTeamController::class, 'update'])->name('teams.update');
        Route::delete('/sales-teams/{team}', [SalesTeamController::class, 'destroy'])->name('teams.destroy');
    });
    Route::post('/sales-teams/{team}/members', [SalesTeamController::class, 'addMembers'])
        ->middleware('permission:teams.manage,teams.manage_members')
        ->name('teams.members.store');
    Route::delete('/sales-teams/{team}/members/{user}', [SalesTeamController::class, 'removeMember'])
        ->middleware('permission:teams.manage,teams.manage_members')
        ->name('teams.members.destroy');

    /*
    |----------------------------------------------------------------------
    | Leads
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:leads.view')->group(function () {
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/pipeline', [LeadController::class, 'pipeline'])->name('leads.pipeline');
        Route::get('/leads/assigned', [LeadController::class, 'assigned'])
            ->middleware('permission:leads.view_all,leads.assign')
            ->name('leads.assigned');
        Route::get('/leads/export', [LeadController::class, 'export'])
            ->middleware('permission:reports.export,leads.view_all')
            ->name('leads.export');
    });
    Route::middleware('permission:leads.create')->group(function () {
        Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
        Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    });
    Route::get('/leads/today', [LeadController::class, 'today'])
        ->middleware('permission:leads.view_team')
        ->name('leads.today');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::middleware('permission:leads.update')->put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::post('/leads/{lead}/activity', [LeadController::class, 'storeActivity'])->name('leads.activity.store');
    Route::post('/leads/{lead}/assign', [LeadController::class, 'assign'])
        ->middleware('permission:leads.assign')
        ->name('leads.assign');
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])
        ->middleware('permission:leads.convert')
        ->name('leads.convert');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])
        ->middleware('permission:leads.delete')
        ->name('leads.destroy');

    /*
    |----------------------------------------------------------------------
    | Customers / contacts
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:customers.view')->get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::middleware('permission:customers.create')->post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::middleware('permission:customers.update')->put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
        ->middleware('permission:customers.delete')
        ->name('customers.destroy');

    Route::middleware('permission:contacts.view')->group(function () {
        Route::get('/contacts-list', [ContactController::class, 'index'])->name('contacts-list');
        Route::get('/contacts-grid', [ContactController::class, 'grid'])->name('contacts-grid');
    });
    Route::middleware('permission:contacts.manage')->group(function () {
        Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
        Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
        Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Catalogue
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:products.view')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    });
    Route::middleware(['permission:products.create', 'subscribed'])->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    });
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::middleware(['permission:products.update', 'subscribed'])->group(function () {
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::post('/products/{product}/media', [ProductController::class, 'storeMedia'])->name('products.media.store');
    });
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->middleware('permission:products.delete')
        ->name('products.destroy');
    Route::delete('/products/media/{media}', [ProductController::class, 'destroyMedia'])
        ->middleware('permission:products.update')
        ->name('products.media.destroy');

    Route::middleware('permission:orders.view')->get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::middleware('permission:orders.manage')->post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::middleware('permission:orders.manage')->post('/orders', [OrderController::class, 'store'])->name('ecommerce-orders.store');

    /*
    |----------------------------------------------------------------------
    | Kitchen / Order desk (WhatsApp orders)
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:orders.view')->group(function () {
        Route::get('/kitchen', [KitchenOrderController::class, 'index'])->name('kitchen.index');
        Route::get('/kitchen/feed', [KitchenOrderController::class, 'feed'])->name('kitchen.feed');
        Route::get('/kitchen/{order}', [KitchenOrderController::class, 'show'])->name('kitchen.show');
    });
    Route::middleware('permission:orders.manage')->group(function () {
        Route::post('/kitchen/{order}/status', [KitchenOrderController::class, 'updateStatus'])->name('kitchen.status');
        Route::post('/kitchen/{order}/invoice', [KitchenOrderController::class, 'sendInvoice'])->name('kitchen.invoice');
        Route::post('/kitchen/{order}/payment', [KitchenOrderController::class, 'markPayment'])->name('kitchen.payment');
    });

    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    });
    Route::middleware('permission:invoices.create')->post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::middleware('permission:invoices.update')->put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])
        ->middleware('permission:invoices.delete')
        ->name('invoices.destroy');
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])
        ->middleware('permission:invoices.send')
        ->name('invoices.send');

    Route::middleware('permission:cart.view')->group(function () {
        Route::get('/ecommerce-cart', [CartController::class, 'cart'])->name('ecommerce-cart');
        Route::get('/ecommerce-checkout', [CartController::class, 'checkout'])->name('ecommerce-checkout');
        Route::post('/ecommerce-cart/add/{product}', [CartController::class, 'add'])->name('ecommerce-cart.add');
        Route::patch('/ecommerce-cart/{item}', [CartController::class, 'update'])->name('ecommerce-cart.update');
        Route::delete('/ecommerce-cart/{item}', [CartController::class, 'remove'])->name('ecommerce-cart.remove');
    });

    /*
    |----------------------------------------------------------------------
    | HR & payroll
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:employees.view')->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    });
    Route::middleware('permission:employees.create')->group(function () {
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    });
    Route::middleware('permission:employees.view')->group(function () {
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    });
    Route::middleware('permission:employees.update')->group(function () {
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    });
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])
        ->middleware('permission:employees.delete')
        ->name('employees.destroy');

    Route::middleware('permission:hr.view')->group(function () {
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/designations', [DesignationController::class, 'index'])->name('designations.index');
    });
    Route::middleware('permission:hr.view,employees.create,employees.update')->group(function () {
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
        Route::post('/designations', [DesignationController::class, 'store'])->name('designations.store');
        Route::put('/designations/{designation}', [DesignationController::class, 'update'])->name('designations.update');
        Route::delete('/designations/{designation}', [DesignationController::class, 'destroy'])->name('designations.destroy');
    });

    Route::middleware('permission:attendance.view_all,attendance.view_own,attendance.manage,attendance.mark_own')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])
            ->middleware('permission:attendance.view_all,attendance.manage')
            ->name('attendance.index');
        Route::get('/attendance/report', [AttendanceController::class, 'report'])
            ->middleware('permission:attendance.view_all,attendance.manage,reports.view')
            ->name('attendance.report');
        Route::get('/attendance/my', [AttendanceController::class, 'my'])->name('attendance.my');
        Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
            ->middleware('permission:attendance.mark_own,attendance.manage')
            ->name('attendance.clock-in');
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
            ->middleware('permission:attendance.mark_own,attendance.manage')
            ->name('attendance.clock-out');
        Route::post('/attendance/mark', [AttendanceController::class, 'mark'])
            ->middleware('permission:attendance.manage')
            ->name('attendance.mark');
        Route::post('/attendance/prompt', [AttendanceController::class, 'prompt'])
            ->middleware('permission:attendance.mark_own')
            ->name('attendance.prompt');
    });

    Route::middleware('permission:leaves.view_all,leaves.view_own,leaves.approve,leaves.request')->group(function () {
        Route::get('/leaves', [LeaveController::class, 'index'])
            ->middleware('permission:leaves.view_all,leaves.approve')
            ->name('leaves.index');
        Route::get('/leaves/my', [LeaveController::class, 'my'])->name('leaves.my');
        Route::post('/leaves', [LeaveController::class, 'store'])
            ->middleware('permission:leaves.request,leaves.approve')
            ->name('leaves.store');
        Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])
            ->middleware('permission:leaves.approve')
            ->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [LeaveController::class, 'reject'])
            ->middleware('permission:leaves.approve')
            ->name('leaves.reject');
        Route::delete('/leaves/{leave}', [LeaveController::class, 'destroy'])->name('leaves.destroy');
    });

    Route::middleware('permission:holidays.view,holidays.manage')->group(function () {
        Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    });
    Route::middleware('permission:holidays.manage')->group(function () {
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
        Route::put('/holidays/{holiday}', [HolidayController::class, 'update'])->name('holidays.update');
        Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
    });

    // Salary allowances (fuel, benefits, etc.) — registered before /payroll/{payroll}
    Route::middleware('permission:allowances.view,allowances.manage')->get('/payroll/allowances', [UserAllowanceController::class, 'index'])->name('allowances.index');
    Route::middleware('permission:allowances.manage')->group(function () {
        Route::post('/payroll/allowances', [UserAllowanceController::class, 'store'])->name('allowances.store');
        Route::put('/payroll/allowances/{allowance}', [UserAllowanceController::class, 'update'])->name('allowances.update');
        Route::delete('/payroll/allowances/{allowance}', [UserAllowanceController::class, 'destroy'])->name('allowances.destroy');
    });

    Route::middleware('permission:payroll.view_all,payroll.view_own,payroll.manage')->group(function () {
        Route::get('/payroll', [PayrollController::class, 'index'])
            ->middleware('permission:payroll.view_all,payroll.manage')
            ->name('payroll.index');
        Route::get('/payroll/my', [PayrollController::class, 'my'])->name('payroll.my');
        Route::get('/payroll/reports', [PayrollController::class, 'reports'])
            ->middleware('permission:payroll.view_all,payroll.manage,reports.view')
            ->name('payroll.reports');
        Route::get('/payroll/{payroll}/payslip', [PayrollController::class, 'payslip'])->name('payroll.payslip');
        Route::get('/payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    });
    Route::middleware('permission:payroll.manage')->group(function () {
        Route::post('/payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
        Route::put('/payroll/{payroll}', [PayrollController::class, 'update'])->name('payroll.update');
        Route::delete('/payroll/{payroll}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
    });
    Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve'])
        ->middleware('permission:payroll.approve')
        ->name('payroll.approve');
    Route::post('/payroll/{payroll}/pay', [PayrollController::class, 'pay'])
        ->middleware('permission:payroll.pay,payroll.manage')
        ->name('payroll.pay');

    Route::middleware('permission:compensations.view,compensations.manage')->get('/compensations', [CompensationController::class, 'index'])->name('compensations.index');
    Route::middleware('permission:compensations.manage')->group(function () {
        Route::post('/compensations', [CompensationController::class, 'store'])->name('compensations.store');
        Route::put('/compensations/{compensation}', [CompensationController::class, 'update'])->name('compensations.update');
        Route::delete('/compensations/{compensation}', [CompensationController::class, 'destroy'])->name('compensations.destroy');
    });

    Route::middleware('permission:commission_rules.view,commission_rules.manage')->get('/commission-rules', [CommissionRuleController::class, 'index'])->name('commission-rules.index');
    Route::middleware('permission:commission_rules.manage')->group(function () {
        Route::post('/commission-rules', [CommissionRuleController::class, 'store'])->name('commission-rules.store');
        Route::put('/commission-rules/{commissionRule}', [CommissionRuleController::class, 'update'])->name('commission-rules.update');
        Route::delete('/commission-rules/{commissionRule}', [CommissionRuleController::class, 'destroy'])->name('commission-rules.destroy');
    });

    Route::middleware('permission:targets.view_all,targets.view_own,targets.manage')->group(function () {
        Route::get('/targets', [TargetController::class, 'index'])->name('targets.index');
        Route::get('/targets/my', [TargetController::class, 'my'])->name('targets.my');
    });
    Route::middleware('permission:targets.manage')->group(function () {
        Route::get('/targets/create', [TargetController::class, 'create'])->name('targets.create');
        Route::post('/targets', [TargetController::class, 'store'])->name('targets.store');
        Route::put('/targets/{target}', [TargetController::class, 'update'])->name('targets.update');
        Route::delete('/targets/{target}', [TargetController::class, 'destroy'])->name('targets.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Communication
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:chat.view')->group(function () {
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/apps-chat', [ChatController::class, 'index'])->name('apps-chat');
        Route::get('/chat/{channel}', [ChatController::class, 'show'])->name('chat.show');
        Route::post('/chat/{channel}/messages', [ChatController::class, 'store'])
            ->middleware('permission:chat.send,chat.view')
            ->name('chat.store');
        Route::get('/chat/{channel}/messages', [ChatController::class, 'fetch'])->name('chat.fetch');
        Route::get('/chat/direct/{user}', [ChatController::class, 'startDirect'])->name('chat.direct');
    });
    Route::post('/chat/groups', [ChatController::class, 'storeGroup'])
        ->middleware('permission:chat.create_group')
        ->name('chat.groups.store');
    Route::post('/chat/{channel}/members', [ChatController::class, 'addMembers'])
        ->middleware('permission:chat.manage_members,chat.create_group')
        ->name('chat.members.store');
    Route::delete('/chat/{channel}/members/{user}', [ChatController::class, 'removeMember'])
        ->middleware('permission:chat.manage_members,chat.create_group')
        ->name('chat.members.destroy');

    Route::middleware(['permission:whatsapp.settings', 'subscribed'])->group(function () {
        Route::get('/whatsapp/settings', [WhatsappController::class, 'settings'])->name('whatsapp.settings');
        Route::post('/whatsapp/settings', [WhatsappController::class, 'saveSettings'])->name('whatsapp.settings.save');
        Route::post('/whatsapp/settings/test', [WhatsappController::class, 'testConnection'])->name('whatsapp.settings.test');
    });
    Route::middleware(['permission:whatsapp.broadcast', 'subscribed'])->group(function () {
        Route::get('/whatsapp/broadcast', [BroadcastController::class, 'index'])->name('whatsapp.broadcast');
        Route::post('/whatsapp/broadcast', [BroadcastController::class, 'send'])->name('whatsapp.broadcast.send');
    });
    Route::middleware('permission:whatsapp.view')->group(function () {
        Route::get('/whatsapp', [WhatsappController::class, 'index'])->name('whatsapp.index');
        Route::get('/whatsapp/contacts', [WhatsappController::class, 'contacts'])->name('whatsapp.contacts');
        Route::get('/whatsapp/{contact}', [WhatsappController::class, 'show'])->name('whatsapp.show');
        Route::get('/whatsapp/{contact}/messages', [WhatsappController::class, 'fetch'])->name('whatsapp.fetch');
    });
    Route::post('/whatsapp/{contact}/send', [WhatsappController::class, 'send'])
        ->middleware(['permission:whatsapp.send', 'subscribed'])
        ->name('whatsapp.send');
    Route::post('/whatsapp/contacts/import', [WhatsappController::class, 'import'])
        ->middleware(['permission:whatsapp.import_contacts', 'subscribed'])
        ->name('whatsapp.contacts.import');
    Route::post('/whatsapp/contacts/{contact}/lead', [WhatsappController::class, 'createLead'])
        ->middleware('permission:leads.create')
        ->name('whatsapp.contacts.lead');
    Route::middleware('permission:email.view')->group(function () {
        Route::get('/email-inbox', [EmailController::class, 'inbox'])->name('email-inbox');
        Route::get('/email/compose', [EmailController::class, 'compose'])->name('email.compose.form');
        Route::get('/email-read/{email}', [EmailController::class, 'show'])->name('email-read');
    });
    Route::post('/email/compose', [EmailController::class, 'compose'])
        ->middleware('permission:email.send')
        ->name('email.compose');
    Route::delete('/email/{email}', [EmailController::class, 'destroy'])
        ->middleware('permission:email.send,email.settings')
        ->name('email.destroy');
    Route::get('/email-read', function () {
        $email = EmailMessage::query()->orderByDesc('id')->first();

        if ($email === null) {
            abort(404);
        }

        return app(EmailController::class)->show($email);
    })->middleware('permission:email.view')->name('email-read.default');
    Route::middleware('permission:email.settings')->group(function () {
        Route::get('/gmail/settings', [GmailController::class, 'settings'])->name('gmail.settings');
        Route::post('/gmail/settings', [GmailController::class, 'saveSettings'])->name('gmail.settings.save');
        Route::get('/gmail/connect', [GmailController::class, 'connect'])->name('gmail.connect');
        Route::get('/gmail/callback', [GmailController::class, 'callback'])->name('gmail.callback');
        Route::post('/gmail/sync', [GmailController::class, 'sync'])->name('gmail.sync');
        Route::delete('/gmail/{account}', [GmailController::class, 'destroy'])->name('gmail.destroy');
    });

    Route::middleware('permission:knowledge.view,knowledge.manage')->group(function () {
        Route::get('/knowledge', [KnowledgeController::class, 'index'])->name('knowledge.index');
    });
    Route::middleware(['permission:knowledge.manage', 'subscribed'])->group(function () {
        Route::post('/knowledge', [KnowledgeController::class, 'store'])->name('knowledge.store');
        Route::put('/knowledge/{article}', [KnowledgeController::class, 'update'])->name('knowledge.update');
        Route::delete('/knowledge/{article}', [KnowledgeController::class, 'destroy'])->name('knowledge.destroy');
    });

    Route::middleware('permission:bot.view,bot.settings')->group(function () {
        Route::get('/bot/conversations', [BotController::class, 'conversations'])->name('bot.conversations');
        Route::get('/bot/conversations/{conversation}', [BotController::class, 'show'])->name('bot.conversations.show');
    });
    Route::middleware(['permission:bot.settings', 'subscribed'])->group(function () {
        Route::get('/bot/settings', [BotController::class, 'settings'])->name('bot.settings');
        Route::post('/bot/settings', [BotController::class, 'saveSettings'])->name('bot.settings.save');
    });

    /*
    |----------------------------------------------------------------------
    | Support inbox (bot escalations handled by humans)
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:support.view')->group(function () {
        Route::get('/support', [SupportController::class, 'index'])->name('support.index');
        Route::get('/support/{conversation}', [SupportController::class, 'show'])->name('support.show');
    });
    Route::middleware('permission:support.manage')->group(function () {
        Route::post('/support/{conversation}/reply', [SupportController::class, 'reply'])->name('support.reply');
        Route::post('/support/{conversation}/tags', [SupportController::class, 'updateTags'])->name('support.tags');
        Route::post('/support/{conversation}/resolve', [SupportController::class, 'resolve'])->name('support.resolve');
        Route::post('/support/{conversation}/reopen', [SupportController::class, 'reopen'])->name('support.reopen');
        Route::post('/support/{conversation}/hand-to-bot', [SupportController::class, 'handToBot'])->name('support.hand-to-bot');
    });

    // Workspace apps kept from template
    Route::middleware('permission:calendar.view')->group(function () {
        Route::view('/apps-calendar', 'pages.apps-calendar')->name('apps-calendar');
        Route::get('/apps-calendar/events', [CalendarController::class, 'events'])->name('apps-calendar.events');
    });
    Route::middleware('permission:calendar.manage')->group(function () {
        Route::post('/apps-calendar/events', [CalendarController::class, 'store'])->name('apps-calendar.events.store');
        Route::put('/apps-calendar/events/{event}', [CalendarController::class, 'update'])->name('apps-calendar.events.update');
        Route::delete('/apps-calendar/events/{event}', [CalendarController::class, 'destroy'])->name('apps-calendar.events.destroy');
    });
    Route::middleware('permission:todo.view')->get('/apps-todo', [TodoController::class, 'index'])->name('apps-todo');
    Route::middleware('permission:todo.manage')->group(function () {
        Route::post('/apps-todo', [TodoController::class, 'store'])->name('apps-todo.store');
        Route::patch('/apps-todo/{todo}', [TodoController::class, 'update'])->name('apps-todo.update');
        Route::delete('/apps-todo/{todo}', [TodoController::class, 'destroy'])->name('apps-todo.destroy');
    });
    Route::middleware('permission:files.view')->get('/apps-file-manager', [FileManagerController::class, 'index'])->name('apps-file-manager');
    Route::middleware('permission:files.manage')->group(function () {
        Route::post('/apps-file-manager', [FileManagerController::class, 'store'])->name('apps-file-manager.store');
        Route::delete('/apps-file-manager/{file}', [FileManagerController::class, 'destroy'])->name('apps-file-manager.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Reports & administration
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:reports.view,reports.export')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])
            ->middleware('permission:reports.export')
            ->name('reports.export');
    });

    Route::middleware('permission:users.view')->get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])
        ->middleware('permission:users.create')
        ->name('users.create');
    Route::middleware('permission:users.create')->post('/users', [UserController::class, 'store'])->name('users.store');
    Route::middleware('permission:users.update')->get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::middleware('permission:users.update')->put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle', [UserController::class, 'toggle'])
        ->middleware('permission:users.update')
        ->name('users.toggle');
    Route::post('/users/{user}/permissions', [UserController::class, 'syncPermissions'])
        ->middleware('permission:permissions.manage')
        ->name('users.permissions.sync');
    Route::post('/users/{user}/role', [UserController::class, 'assignRole'])
        ->middleware('permission:roles.assign')
        ->name('users.role.assign');
    Route::post('/users/{user}/impersonate', [UserController::class, 'impersonate'])
        ->middleware('permission:users.impersonate')
        ->name('users.impersonate');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('users.destroy');
    Route::post('/impersonation/stop', [UserController::class, 'stopImpersonating'])->name('impersonation.stop');

    Route::middleware('permission:roles.view')->get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::middleware('permission:roles.create')->post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::middleware('permission:roles.update')->put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.delete')
        ->name('roles.destroy');

    Route::get('/permissions', [PermissionController::class, 'index'])
        ->middleware('permission:permissions.manage,roles.view')
        ->name('permissions.index');
    Route::post('/permissions/roles/{role}', [PermissionController::class, 'syncRole'])
        ->middleware('permission:permissions.manage')
        ->name('permissions.sync');
    Route::post('/permissions/users/{user}', [PermissionController::class, 'syncUser'])
        ->middleware('permission:permissions.manage')
        ->name('permissions.user.sync');

    Route::middleware('permission:settings.view,settings.manage,settings.theme,integrations.manage')->get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::middleware('permission:settings.manage')->group(function () {
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::put('/settings', [SettingsController::class, 'update']);
    });
    Route::post('/settings/theme', [SettingsController::class, 'updateTheme'])
        ->middleware('permission:settings.theme,settings.manage')
        ->name('settings.theme');
    Route::post('/settings/integrations', [SettingsController::class, 'updateIntegrations'])
        ->middleware('permission:integrations.manage')
        ->name('settings.integrations');
    Route::get('/settings/integrations', [SettingsController::class, 'integrations'])
        ->middleware('permission:integrations.manage')
        ->name('integrations.index');
    Route::get('/settings/theme', [SettingsController::class, 'theme'])
        ->middleware('permission:settings.theme,settings.manage')
        ->name('settings.theme.form');

    Route::middleware('permission:activity_logs.view')->get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    /*
    |----------------------------------------------------------------------
    | Organizations (multi-tenant platform)
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:organizations.view')->group(function () {
        Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
        Route::get('/organizations/create', [OrganizationController::class, 'create'])
            ->middleware('permission:organizations.create')
            ->name('organizations.create');
        Route::post('/organizations', [OrganizationController::class, 'store'])
            ->middleware('permission:organizations.create')
            ->name('organizations.store');
        Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
        Route::get('/organizations/{organization}/edit', [OrganizationController::class, 'edit'])
            ->middleware('permission:organizations.update')
            ->name('organizations.edit');
        Route::put('/organizations/{organization}', [OrganizationController::class, 'update'])
            ->middleware('permission:organizations.update')
            ->name('organizations.update');
        Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy'])
            ->middleware('permission:organizations.delete')
            ->name('organizations.destroy');

        Route::post('/organizations/{organization}/payments', [OrganizationController::class, 'recordPayment'])
            ->middleware('permission:organizations.payments')
            ->name('organizations.payments.store');
        Route::post('/organizations/{organization}/payments/{payment}/approve', [OrganizationController::class, 'approvePayment'])
            ->middleware('permission:organizations.payments')
            ->name('organizations.payments.approve');

        Route::post('/organizations/{organization}/toggle-crm', [OrganizationController::class, 'toggleCrm'])
            ->middleware('permission:organizations.block')
            ->name('organizations.toggle-crm');
        Route::post('/organizations/{organization}/toggle-bot', [OrganizationController::class, 'toggleBot'])
            ->middleware('permission:organizations.block')
            ->name('organizations.toggle-bot');
        Route::post('/organizations/{organization}/send-credentials', [OrganizationController::class, 'sendCredentials'])
            ->name('organizations.send-credentials');
        Route::post('/organizations/{organization}/reset-owner-password', [OrganizationController::class, 'resetOwnerPassword'])
            ->name('organizations.reset-owner-password');
        Route::post('/organizations/{organization}/impersonate-owner', [OrganizationController::class, 'impersonateOwner'])
            ->name('organizations.impersonate-owner');
    });

    Route::middleware('permission:billing.view')->group(function () {
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/billing/submit', [BillingController::class, 'submit'])->name('billing.submit');
    });

    Route::middleware('permission:settings.mail')->group(function () {
        Route::get('/settings/email', [EmailSettingsController::class, 'index'])->name('settings.email');
        Route::post('/settings/email', [EmailSettingsController::class, 'update'])->name('settings.email.update');
        Route::post('/settings/email/test', [EmailSettingsController::class, 'test'])->name('settings.email.test');
    });

    Route::fallback(fn () => response()->view('pages.pages-404', [], 404));
});
