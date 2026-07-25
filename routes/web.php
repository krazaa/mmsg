<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AgentCommissionPayoutController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\CommissionRuleController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerNotificationController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\CustomerWithdrawalController;
use App\Http\Controllers\EmailCampaignController;
use App\Http\Controllers\EmailUnsubscribeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallmentSchedulesController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PaymentGatewaySettingController;
use App\Http\Controllers\PlotAllotmentController;
use App\Http\Controllers\PlotController;
use App\Http\Controllers\PlotPackageController;
use App\Http\Controllers\PlotPlanImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\WhatsAppSettingsController;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $projects = Project::query()
        ->where('status', true)
        ->orderBy('name')
        ->get();

    return view('welcome', compact('projects'));
});

Route::get('/email/unsubscribe/{token}', [EmailUnsubscribeController::class, 'show'])->name('email-unsubscribe.show');
Route::post('/email/unsubscribe/{token}', [EmailUnsubscribeController::class, 'store'])->name('email-unsubscribe.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'permission:view dashboard'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::patch('/theme', [ThemeController::class, 'update'])->name('theme.update');
    Route::middleware(['verified', 'permission:use customer portal'])->group(function () {
        Route::get('/customer/team', [DashboardController::class, 'team'])->name('customer.team');
        Route::get('/customer/commissions', [DashboardController::class, 'commissions'])->name('customer.commissions');
        Route::get('/customer/withdrawals', [CustomerWithdrawalController::class, 'index'])->name('customer.withdrawals.index');
        Route::post('/customer/withdrawals', [CustomerWithdrawalController::class, 'store'])->name('customer.withdrawals.store');
        Route::get('/customer/notifications', [CustomerNotificationController::class, 'index'])->name('customer.notifications.index');
        Route::post('/customer/notifications/read-all', [CustomerNotificationController::class, 'readAll'])->name('customer.notifications.read-all');
        Route::post('/customer/notifications/{notification}/read', [CustomerNotificationController::class, 'read'])->name('customer.notifications.read');
        Route::get('/customer/book-plot', [CustomerBookingController::class, 'create'])->middleware('permission:customer.bookings.create')->name('customer.bookings.create');
        Route::post('/customer/book-plot', [CustomerBookingController::class, 'store'])->middleware('permission:customer.bookings.store')->name('customer.bookings.store');
        Route::post('/customer/payments', [CustomerPaymentController::class, 'store'])->middleware('permission:customer.payments.store')->name('customer.payments.store');
        Route::get('/customer/payments/{payment}/receipt', [CustomerPaymentController::class, 'receipt'])->middleware('permission:customer.payments.receipt')->name('customer.payments.receipt');
    });
    Route::middleware('management')->group(function () {
        Route::get('/management/activity-log', [ActivityLogController::class, 'index'])->middleware('permission:view activity log')->name('management.activity-log.index');
        Route::get('/withdrawal-requests', [CustomerWithdrawalController::class, 'managementIndex'])->middleware('permission:manage commissions')->name('withdrawal-requests.index');
        Route::patch('/withdrawal-requests/{withdrawalRequest}', [CustomerWithdrawalController::class, 'review'])->middleware('permission:manage commissions')->name('withdrawal-requests.review');
        Route::get('/management/notifications', [CustomerNotificationController::class, 'managementIndex'])->middleware('permission:manage notifications')->name('management.notifications.index');
        Route::get('/management/whatsapp', [WhatsAppSettingsController::class, 'index'])->middleware('permission:manage notifications')->name('management.whatsapp.index');
        Route::post('/management/whatsapp/test', [WhatsAppSettingsController::class, 'test'])->middleware('permission:manage notifications')->name('management.whatsapp.test');
        Route::post('/management/notifications/read-all', [CustomerNotificationController::class, 'managementReadAll'])->middleware('permission:manage notifications')->name('management.notifications.read-all');
        Route::post('/management/notifications/{notification}/read', [CustomerNotificationController::class, 'managementRead'])->middleware('permission:manage notifications')->name('management.notifications.read');
        Route::resource('projects', ProjectController::class)->except('show')->middleware('permission:manage projects');
        Route::resource('blocks', BlockController::class)->except('show')->middleware('permission:manage allotments');
        Route::resource('plots', PlotController::class)->except('show')->middleware('permission:manage allotments');
        Route::get('/projects/{project}/plot-plan', [PlotPlanImportController::class, 'create'])->middleware('permission:manage projects')->name('projects.plot-plan.create');
        Route::post('/projects/{project}/plot-plan/analyze', [PlotPlanImportController::class, 'analyze'])->middleware('permission:manage projects')->name('projects.plot-plan.analyze');
        Route::post('/projects/{project}/plot-plan/import', [PlotPlanImportController::class, 'store'])->middleware('permission:manage projects')->name('projects.plot-plan.store');
        Route::get('/sales/new-booking', [BookingController::class, 'sales'])->middleware('permission:manage bookings')->name('sales.create');
        Route::get('/customers/{customer}/downline', [CustomerController::class, 'downlinePage'])->middleware('permission:manage customers')->name('customers.downline');
        Route::get('/customers/{customer}/portal', [DashboardController::class, 'customerPortal'])->middleware('permission:manage customers')->name('customers.portal');
        Route::get('/customers/{customer}/team', [DashboardController::class, 'customerTeam'])->middleware('permission:manage customers')->name('customers.team');
        Route::get('/customers/{customer}/commissions', [DashboardController::class, 'customerCommissions'])->middleware('permission:manage customers')->name('customers.commissions');
        Route::resource('customers', CustomerController::class)->middleware('permission:manage customers');
        Route::resource('staff', StaffController::class)->except('show')->parameters(['staff' => 'staff'])->middleware('permission:manage staff');
        Route::resource('agents', AgentController::class)->parameters(['agents' => 'agent'])->middleware('permission:manage agents');
        Route::post('/agents/{agent}/payouts', [AgentCommissionPayoutController::class, 'store'])->middleware('permission:manage commissions')->name('agents.payouts.store');
        Route::get('/commission-rules', [CommissionRuleController::class, 'index'])->middleware('permission:manage commissions')->name('commission-rules.index');
        Route::put('/commission-rules/{package}', [CommissionRuleController::class, 'update'])->middleware('permission:manage commissions')->name('commission-rules.update');
        Route::resource('packages', PlotPackageController::class)->except('show')->parameters(['packages' => 'package'])->middleware('permission:manage packages');
        Route::get('/allotments', [PlotAllotmentController::class, 'index'])->middleware('permission:manage allotments')->name('allotments.index');
        Route::post('/inventory/plots', [PlotAllotmentController::class, 'storeInventory'])->middleware('permission:manage allotments')->name('inventory.plots.store');
        Route::post('/allotments', [PlotAllotmentController::class, 'store'])->middleware('permission:manage allotments')->name('allotments.store');
        Route::get('/installments', [InstallmentSchedulesController::class, 'index'])->middleware('permission:manage installments')->name('installments.index');
        Route::get('/installments/{installment}/edit', [InstallmentSchedulesController::class, 'edit'])->middleware('permission:manage installments')->name('installments.edit');
        Route::put('/installments/{installment}', [InstallmentSchedulesController::class, 'update'])->middleware('permission:manage installments')->name('installments.update');
        Route::get('/bookings', [BookingController::class, 'index'])->middleware('permission:manage bookings')->name('bookings.index');
        Route::post('/bookings', [BookingController::class, 'store'])->middleware('permission:manage bookings')->name('bookings.store');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->middleware('permission:manage bookings')->name('bookings.show');
        Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->middleware('permission:manage bookings')->name('bookings.edit');
        Route::get('/bookings/{booking}/manage', [BookingController::class, 'manage'])->middleware('permission:manage bookings')->name('bookings.manage');
        Route::patch('/bookings/{booking}/status', [BookingController::class, 'status'])->middleware('permission:manage bookings')->name('bookings.status');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])->middleware('permission:manage bookings')->name('bookings.update');
        Route::post('/bookings/{booking}/payments', [PaymentController::class, 'store'])->middleware('permission:manage payments')->name('bookings.payments.store');
        Route::get('/payments', [PaymentController::class, 'index'])->middleware('permission:manage payments')->name('payments.index');
        Route::resource('payment-methods', PaymentMethodController::class)->only(['index', 'store', 'update', 'destroy'])->middleware('permission:manage payments');
        Route::get('/payment-gateways', [PaymentGatewaySettingController::class, 'index'])->middleware('permission:manage payments')->name('payment-gateways.index');
        Route::put('/payment-gateways/{provider}', [PaymentGatewaySettingController::class, 'update'])->middleware('permission:manage payments')->name('payment-gateways.update');
        Route::post('/email-campaigns/test', [EmailCampaignController::class, 'test'])->middleware(['permission:manage notifications', 'role:super_admin|admin'])->name('email-campaigns.test');
        Route::post('/email-campaigns/{email_campaign}/retry', [EmailCampaignController::class, 'retry'])->middleware(['permission:manage notifications', 'role:super_admin|admin'])->name('email-campaigns.retry');
        Route::resource('email-campaigns', EmailCampaignController::class)->only(['index', 'create', 'store', 'show'])->middleware(['permission:manage notifications', 'role:super_admin|admin']);
        Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->middleware('permission:manage payments')->name('payments.edit');
        Route::get('/payments/{payment}/proof', [PaymentController::class, 'proof'])->middleware('permission:manage payments')->name('payments.proof');
        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->middleware('permission:manage payments')->name('payments.update');
    });
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
