<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountController as ApiAccountController;
use App\Http\Controllers\Api\ApartmentController as ApiApartmentController;
use App\Http\Controllers\Api\CashAccountController as ApiCashAccountController;
use App\Http\Controllers\Api\ChargeController as ApiChargeController;
use App\Http\Controllers\Api\DashboardController as ApiDashboardController;
use App\Http\Controllers\Api\ExpenseController as ApiExpenseController;
use App\Http\Controllers\Api\PaymentController as ApiPaymentController;
use App\Http\Controllers\Api\ProfileController as ApiProfileController;
use App\Http\Controllers\Api\ReportController as ApiReportController;
use App\Http\Controllers\Api\ReceiptController as ApiReceiptController;
use App\Http\Controllers\Api\SiteSettingsController as ApiSiteSettingsController;
use App\Http\Controllers\Api\SuperSiteController as ApiSuperSiteController;
use App\Http\Controllers\Api\TemplateAidatController as ApiTemplateAidatController;
use App\Http\Controllers\Api\TemplateExpenseController as ApiTemplateExpenseController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\Api\VendorController as ApiVendorController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:login'])->name('api.auth.login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('api.auth.logout');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth')->name('api.auth.me');
});

Route::prefix('api/v1')->middleware(['auth', 'site.scope'])->group(function () {
    Route::get('/dashboard', [ApiDashboardController::class, 'index'])->name('api.dashboard.index');

    Route::get('/charges', [ApiChargeController::class, 'index'])->name('api.charges.index');
    Route::get('/charges/meta', [ApiChargeController::class, 'meta'])->name('api.charges.meta');
    Route::post('/charges', [ApiChargeController::class, 'store'])->name('api.charges.store');
    Route::post('/charges/bulk', [ApiChargeController::class, 'storeBulk'])->name('api.charges.store-bulk');
    Route::get('/charges/{charge}', [ApiChargeController::class, 'show'])->name('api.charges.show');
    Route::post('/charges/{charge}/collect', [ApiChargeController::class, 'collect'])->name('api.charges.collect');
    Route::delete('/charges/{charge}', [ApiChargeController::class, 'destroy'])->name('api.charges.destroy');

    Route::get('/expenses', [ApiExpenseController::class, 'index'])->name('api.expenses.index');
    Route::get('/expenses/meta', [ApiExpenseController::class, 'meta'])->name('api.expenses.meta');
    Route::post('/expenses', [ApiExpenseController::class, 'store'])->name('api.expenses.store');
    Route::get('/expenses/{expense}', [ApiExpenseController::class, 'show'])->name('api.expenses.show');
    Route::post('/expenses/{expense}/pay', [ApiExpenseController::class, 'pay'])->name('api.expenses.pay');
    Route::delete('/expenses/{expense}', [ApiExpenseController::class, 'destroy'])->name('api.expenses.destroy');

    Route::get('/receipts', [ApiReceiptController::class, 'index'])->name('api.receipts.index');
    Route::get('/receipts/meta', [ApiReceiptController::class, 'meta'])->name('api.receipts.meta');
    Route::get('/receipts/{receipt}', [ApiReceiptController::class, 'show'])->name('api.receipts.show');

    Route::get('/payments', [ApiPaymentController::class, 'index'])->name('api.payments.index');
    Route::get('/payments/meta', [ApiPaymentController::class, 'meta'])->name('api.payments.meta');
    Route::get('/payments/{payment}', [ApiPaymentController::class, 'show'])->name('api.payments.show');

    Route::get('/accounts', [ApiAccountController::class, 'index'])->name('api.accounts.index');
    Route::get('/accounts/meta', [ApiAccountController::class, 'meta'])->name('api.accounts.meta');
    Route::post('/accounts', [ApiAccountController::class, 'store'])->name('api.accounts.store');
    Route::put('/accounts/{account}', [ApiAccountController::class, 'update'])->name('api.accounts.update');
    Route::delete('/accounts/{account}', [ApiAccountController::class, 'destroy'])->name('api.accounts.destroy');

    Route::get('/cash-accounts', [ApiCashAccountController::class, 'index'])->name('api.cash-accounts.index');
    Route::get('/cash-accounts/meta', [ApiCashAccountController::class, 'meta'])->name('api.cash-accounts.meta');
    Route::post('/cash-accounts', [ApiCashAccountController::class, 'store'])->name('api.cash-accounts.store');
    Route::put('/cash-accounts/{cashAccount}', [ApiCashAccountController::class, 'update'])->name('api.cash-accounts.update');
    Route::delete('/cash-accounts/{cashAccount}', [ApiCashAccountController::class, 'destroy'])->name('api.cash-accounts.destroy');
    Route::get('/cash-accounts/{cashAccount}/statement', [ApiCashAccountController::class, 'statement'])->name('api.cash-accounts.statement');

    Route::get('/apartments', [ApiApartmentController::class, 'index'])->name('api.apartments.index');
    Route::get('/apartments/meta', [ApiApartmentController::class, 'meta'])->name('api.apartments.meta');
    Route::post('/apartments', [ApiApartmentController::class, 'store'])->name('api.apartments.store');
    Route::get('/apartments/{apartment}', [ApiApartmentController::class, 'show'])->name('api.apartments.show');
    Route::put('/apartments/{apartment}', [ApiApartmentController::class, 'update'])->name('api.apartments.update');
    Route::delete('/apartments/{apartment}', [ApiApartmentController::class, 'destroy'])->name('api.apartments.destroy');
    Route::post('/apartments/{apartment}/residents', [ApiApartmentController::class, 'addResident'])->name('api.apartments.add-resident');
    Route::delete('/apartments/{apartment}/residents/{user}', [ApiApartmentController::class, 'removeResident'])->name('api.apartments.remove-resident');

    Route::get('/users', [ApiUserController::class, 'index'])->name('api.users.index');
    Route::get('/users/meta', [ApiUserController::class, 'meta'])->name('api.users.meta');
    Route::post('/users', [ApiUserController::class, 'store'])->name('api.users.store');
    Route::get('/users/{user}', [ApiUserController::class, 'show'])->name('api.users.show');
    Route::put('/users/{user}', [ApiUserController::class, 'update'])->name('api.users.update');
    Route::delete('/users/{user}', [ApiUserController::class, 'destroy'])->name('api.users.destroy');
    Route::post('/users/{user}/apartments', [ApiUserController::class, 'addApartment'])->name('api.users.add-apartment');
    Route::delete('/users/{user}/apartments/{apartment}', [ApiUserController::class, 'removeApartment'])->name('api.users.remove-apartment');

    Route::get('/vendors', [ApiVendorController::class, 'index'])->name('api.vendors.index');
    Route::post('/vendors', [ApiVendorController::class, 'store'])->name('api.vendors.store');
    Route::get('/vendors/{vendor}', [ApiVendorController::class, 'show'])->name('api.vendors.show');
    Route::put('/vendors/{vendor}', [ApiVendorController::class, 'update'])->name('api.vendors.update');
    Route::delete('/vendors/{vendor}', [ApiVendorController::class, 'destroy'])->name('api.vendors.destroy');

    Route::get('/site-settings', [ApiSiteSettingsController::class, 'show'])->name('api.site-settings.show');
    Route::put('/site-settings', [ApiSiteSettingsController::class, 'update'])->name('api.site-settings.update');

    Route::get('/templates/aidat', [ApiTemplateAidatController::class, 'index'])->name('api.templates.aidat.index');
    Route::get('/templates/aidat/meta', [ApiTemplateAidatController::class, 'meta'])->name('api.templates.aidat.meta');
    Route::post('/templates/aidat', [ApiTemplateAidatController::class, 'store'])->name('api.templates.aidat.store');
    Route::get('/templates/aidat/{aidat}', [ApiTemplateAidatController::class, 'show'])->name('api.templates.aidat.show');
    Route::put('/templates/aidat/{aidat}', [ApiTemplateAidatController::class, 'update'])->name('api.templates.aidat.update');
    Route::delete('/templates/aidat/{aidat}', [ApiTemplateAidatController::class, 'destroy'])->name('api.templates.aidat.destroy');

    Route::get('/templates/expense', [ApiTemplateExpenseController::class, 'index'])->name('api.templates.expense.index');
    Route::get('/templates/expense/meta', [ApiTemplateExpenseController::class, 'meta'])->name('api.templates.expense.meta');
    Route::post('/templates/expense', [ApiTemplateExpenseController::class, 'store'])->name('api.templates.expense.store');
    Route::get('/templates/expense/{expense}', [ApiTemplateExpenseController::class, 'show'])->name('api.templates.expense.show');
    Route::put('/templates/expense/{expense}', [ApiTemplateExpenseController::class, 'update'])->name('api.templates.expense.update');
    Route::delete('/templates/expense/{expense}', [ApiTemplateExpenseController::class, 'destroy'])->name('api.templates.expense.destroy');

    Route::get('/reports/meta', [ApiReportController::class, 'meta'])->name('api.reports.meta');
    Route::get('/reports/cash-statement', [ApiReportController::class, 'cashStatement'])->name('api.reports.cash-statement');
    Route::get('/reports/account-statement', [ApiReportController::class, 'accountStatement'])->name('api.reports.account-statement');
    Route::get('/reports/collections', [ApiReportController::class, 'collections'])->name('api.reports.collections');
    Route::get('/reports/payments', [ApiReportController::class, 'payments'])->name('api.reports.payments');
    Route::get('/reports/debt-status', [ApiReportController::class, 'debtStatus'])->name('api.reports.debt-status');
    Route::get('/reports/receivable-status', [ApiReportController::class, 'receivableStatus'])->name('api.reports.receivable-status');
    Route::get('/reports/charge-list', [ApiReportController::class, 'chargeList'])->name('api.reports.charge-list');
    Route::get('/reports/cash-statement/pdf', [ApiReportController::class, 'cashStatementPdf'])->name('api.reports.cash-statement.pdf');
    Route::get('/reports/account-statement/pdf', [ApiReportController::class, 'accountStatementPdf'])->name('api.reports.account-statement.pdf');
    Route::get('/reports/collections/pdf', [ApiReportController::class, 'collectionsPdf'])->name('api.reports.collections.pdf');
    Route::get('/reports/payments/pdf', [ApiReportController::class, 'paymentsPdf'])->name('api.reports.payments.pdf');
    Route::get('/reports/debt-status/pdf', [ApiReportController::class, 'debtStatusPdf'])->name('api.reports.debt-status.pdf');
    Route::get('/reports/receivable-status/pdf', [ApiReportController::class, 'receivableStatusPdf'])->name('api.reports.receivable-status.pdf');
    Route::get('/reports/charge-list/pdf', [ApiReportController::class, 'chargeListPdf'])->name('api.reports.charge-list.pdf');

    Route::put('/profile/password', [ApiProfileController::class, 'updatePassword'])->name('api.profile.password');

    Route::get('/super/sites', [ApiSuperSiteController::class, 'index'])->name('api.super.sites.index');
    Route::get('/super/sites/meta', [ApiSuperSiteController::class, 'meta'])->name('api.super.sites.meta');
    Route::post('/super/sites', [ApiSuperSiteController::class, 'store'])->name('api.super.sites.store');
    Route::get('/super/sites/{site}', [ApiSuperSiteController::class, 'show'])->name('api.super.sites.show');
    Route::put('/super/sites/{site}', [ApiSuperSiteController::class, 'update'])->name('api.super.sites.update');
    Route::delete('/super/sites/{site}', [ApiSuperSiteController::class, 'destroy'])->name('api.super.sites.destroy');
});

Route::view('/{any?}', 'application')
    ->where('any', '^(?!api).*$')
    ->name('spa');
