<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\ChargeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TemplateAidatController;
use App\Http\Controllers\TemplateExpenseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteSettingsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'site.scope'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global Search
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // Profile
    Route::get('/profile/password', [ProfileController::class, 'passwordForm'])->name('profile.password');
    Route::put('/profile/password', [ProfileController::class, 'passwordUpdate'])->name('profile.password.update');

    // Charges (Aidatlar)
    Route::prefix('charges')->name('charges.')->group(function () {
        Route::get('/', [ChargeController::class, 'index'])->name('index');
        Route::get('/create', [ChargeController::class, 'create'])->name('create');
        Route::post('/', [ChargeController::class, 'store'])->name('store');
        Route::get('/bulk', [ChargeController::class, 'createBulk'])->name('create-bulk');
        Route::post('/bulk', [ChargeController::class, 'storeBulk'])->name('store-bulk');
        Route::get('/{charge}', [ChargeController::class, 'show'])->name('show');
        Route::post('/{charge}/collect', [ChargeController::class, 'collect'])->name('collect');
        Route::delete('/{charge}', [ChargeController::class, 'destroy'])->name('destroy');
    });

    // Receipts (Tahsilatlar)
    Route::prefix('receipts')->name('receipts.')->group(function () {
        Route::get('/', [ReceiptController::class, 'index'])->name('index');
        Route::get('/{receipt}', [ReceiptController::class, 'show'])->name('show');
        Route::get('/{receipt}/pdf', [ReceiptController::class, 'pdf'])->name('pdf');
    });

    // Expenses (Giderler)
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('/create', [ExpenseController::class, 'create'])->name('create');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::get('/{expense}', [ExpenseController::class, 'show'])->name('show');
        Route::post('/{expense}/pay', [ExpenseController::class, 'pay'])->name('pay');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
    });

    // Payments (Ödemeler)
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
    });

    // Accounts (Hesaplar)
    Route::resource('accounts', AccountController::class)->except(['show']);

    // Cash Accounts (Kasa/Banka)
    Route::resource('cash-accounts', CashAccountController::class)->except(['show']);
    Route::get('cash-accounts/{cash_account}/statement', [CashAccountController::class, 'statement'])
        ->name('cash-accounts.statement');

    // Management (Yönetim) - Admin only
    Route::prefix('management')->name('management.')->middleware('role:admin')->group(function () {
        Route::resource('apartments', ApartmentController::class);
        Route::post('apartments/{apartment}/residents', [ApartmentController::class, 'addResident'])
            ->name('apartments.add-resident');
        Route::delete('apartments/{apartment}/residents/{user}', [ApartmentController::class, 'removeResident'])
            ->name('apartments.remove-resident');

        Route::resource('users', UserController::class);
        Route::post('users/{user}/apartments', [UserController::class, 'addApartment'])
            ->name('users.add-apartment');
        Route::delete('users/{user}/apartments/{apartment}', [UserController::class, 'removeApartment'])
            ->name('users.remove-apartment');
        Route::resource('vendors', VendorController::class);
        Route::get('site-settings', [SiteSettingsController::class, 'edit'])
            ->name('site-settings.edit');
        Route::put('site-settings', [SiteSettingsController::class, 'update'])
            ->name('site-settings.update');
    });

    // Templates (Şablonlar) - Admin only
    Route::prefix('templates')->name('templates.')->middleware('role:admin')->group(function () {
        Route::resource('aidat', TemplateAidatController::class);
        Route::resource('expense', TemplateExpenseController::class);
    });

    // Reports (Raporlar)
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/cash-statement', [ReportController::class, 'cashStatement'])->name('cash-statement');
        Route::get('/account-statement', [ReportController::class, 'accountStatement'])->name('account-statement');
        Route::get('/collections', [ReportController::class, 'collections'])->name('collections');
        Route::get('/payments', [ReportController::class, 'payments'])->name('payments');
        Route::get('/debt-status', [ReportController::class, 'debtStatus'])->name('debt-status');
        Route::get('/receivable-status', [ReportController::class, 'receivableStatus'])->name('receivable-status');
        Route::get('/charge-list', [ReportController::class, 'chargeList'])->name('charge-list');

        // PDF versions
        Route::get('/cash-statement/pdf', [ReportController::class, 'cashStatementPdf'])->name('cash-statement.pdf');
        Route::get('/account-statement/pdf', [ReportController::class, 'accountStatementPdf'])->name('account-statement.pdf');
        Route::get('/collections/pdf', [ReportController::class, 'collectionsPdf'])->name('collections.pdf');
        Route::get('/payments/pdf', [ReportController::class, 'paymentsPdf'])->name('payments.pdf');
        Route::get('/debt-status/pdf', [ReportController::class, 'debtStatusPdf'])->name('debt-status.pdf');
        Route::get('/receivable-status/pdf', [ReportController::class, 'receivableStatusPdf'])->name('receivable-status.pdf');
        Route::get('/charge-list/pdf', [ReportController::class, 'chargeListPdf'])->name('charge-list.pdf');
    });
});

// Auth routes
require __DIR__.'/auth.php';
