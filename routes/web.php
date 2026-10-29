<?php

use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MerchantDepositController;
use App\Http\Controllers\OrderInvoiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SuperAdminApprovalController;
use App\Http\Controllers\TenantOrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherPackageController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard/login
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/password', [AuthController::class, 'showPasswordForm'])->name('password.edit');
    Route::post('/password', [AuthController::class, 'updatePassword'])->name('password.update');

    // Dokumen Cetak / Digital: Faktur Tagihan & Kwitansi Pelunasan (Akses: Mitra & Admin)
    Route::get('/orders/{order}/invoice', [OrderInvoiceController::class, 'show'])->name('orders.invoice');
    Route::get('/deposits/{deposit}/invoice', [MerchantDepositController::class, 'invoice'])->name('deposits.invoice');

    // 1. TENANT ROUTES (Reseller / Warung)
    Route::middleware('role:tenant')->prefix('tenant')->name('tenant.')->group(function () {
        Route::get('/orders', [TenantOrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [TenantOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}/download-pdf', [TenantOrderController::class, 'downloadPdf'])->name('orders.download_pdf');

        // Beli Saldo Kuota Cetak Mandiri
        Route::get('/deposits', [MerchantDepositController::class, 'tenantIndex'])->name('deposits.index');
        Route::post('/deposits', [MerchantDepositController::class, 'tenantStore'])->name('deposits.store');
    });

    // 2. OPERATIONAL ROUTES (Admin & Super Admin)
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/offline', [AdminOrderController::class, 'createOffline'])->name('orders.create_offline');
            Route::post('/orders/offline', [AdminOrderController::class, 'storeOffline'])->name('orders.store_offline');
            Route::post('/orders/{order}/confirm-payment', [AdminOrderController::class, 'confirmPayment'])->name('orders.confirm_payment');
            Route::post('/orders/{order}/confirm', [AdminOrderController::class, 'confirmPayment'])->name('orders.confirm');
            Route::post('/orders/{order}/cancel-unpaid', [AdminOrderController::class, 'cancelUnpaid'])->name('orders.cancel_unpaid');
            Route::post('/orders/{order}/request-delete', [AdminOrderController::class, 'requestDelete'])->name('orders.request_delete');

            // Deposit & Saldo Cetak Mandiri (Admin & Super Admin)
            Route::get('/deposits', [MerchantDepositController::class, 'adminIndex'])->name('deposits.index');
            Route::post('/deposits', [MerchantDepositController::class, 'adminStore'])->name('deposits.store');
            Route::post('/deposits/{deposit}/confirm', [MerchantDepositController::class, 'confirm'])->name('deposits.confirm');
            Route::get('/deposits/{deposit}/settle', fn() => redirect()->route('admin.deposits.index'));
            Route::post('/deposits/{deposit}/settle', [MerchantDepositController::class, 'settle'])->name('deposits.settle');
            Route::post('/deposits/{deposit}/cancel', [MerchantDepositController::class, 'cancel'])->name('deposits.cancel');
            Route::delete('/deposits/{deposit}', [MerchantDepositController::class, 'destroy'])->name('deposits.destroy');
        });

        // Legacy / shortcut route alias without /admin prefix
        Route::get('/deposits', fn() => redirect()->route('admin.deposits.index'));
        Route::post('/deposits/{deposit}/settle', [MerchantDepositController::class, 'settle']);
        Route::post('/deposits/{deposit}/confirm', [MerchantDepositController::class, 'confirm']);
        Route::post('/deposits/{deposit}/cancel', [MerchantDepositController::class, 'cancel']);

        // Paket Voucher & Stok Management (Admin & Super Admin can view & add stock)
        Route::get('/packages', [VoucherPackageController::class, 'index'])->name('packages.index');
        Route::post('/packages/{package}/add-stock', [VoucherPackageController::class, 'addStock'])->name('packages.add_stock');

        // User Management (Admin can manage tenants, Super Admin manages both)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset_password');

        // Settings (Admin & Super Admin)
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Beban & Pengeluaran Harian (Admin & Super Admin)
        Route::get('/finances/expenses', [FinanceController::class, 'expenses'])->name('finance.expenses');
        Route::post('/finances/expenses', [FinanceController::class, 'storeExpense'])->name('finance.expenses.store');
        Route::delete('/finances/expenses/{expense}', [FinanceController::class, 'destroyExpense'])->name('finance.expenses.destroy');

        // Pemasukan Kas Manual (Admin & Super Admin)
        Route::get('/finances/incomes', [FinanceController::class, 'incomes'])->name('finance.incomes');
        Route::post('/finances/incomes', [FinanceController::class, 'storeIncome'])->name('finance.incomes.store');
        Route::delete('/finances/incomes/{income}', [FinanceController::class, 'destroyIncome'])->name('finance.incomes.destroy');

        // Inventaris & Logistik Barang (Router, Alat, AP, Kabel FO, dll - Admin & Super Admin)
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/items', [InventoryController::class, 'storeItem'])->name('inventory.items.store');
        Route::put('/inventory/items/{item}', [InventoryController::class, 'updateItem'])->name('inventory.items.update');
        Route::delete('/inventory/items/{item}', [InventoryController::class, 'destroyItem'])->name('inventory.items.destroy');
        Route::post('/inventory/movements/in', [InventoryController::class, 'storeStockIn'])->name('inventory.movements.in');
        Route::post('/inventory/movements/out', [InventoryController::class, 'storeStockOut'])->name('inventory.movements.out');
    });

    // 3. SUPER ADMIN EXCLUSIVE ROUTES (Keuangan, Approve Hapus, Paket Voucher CRUD, Reset Password, Batal Mutasi)
    Route::middleware('role:super_admin')->group(function () {
        // Batal Mutasi Inventaris (Khusus Super Admin)
        Route::delete('/inventory/movements/{movement}', [InventoryController::class, 'destroyMovement'])->name('inventory.movements.destroy');
        // Approvals (Review & Approve/Reject Hapus Bayar)
        Route::get('/approvals', [SuperAdminApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/{order}/approve', [SuperAdminApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{order}/reject', [SuperAdminApprovalController::class, 'reject'])->name('approvals.reject');

        // Paket Voucher Full CRUD & Toggle
        Route::get('/packages/create', [VoucherPackageController::class, 'create'])->name('packages.create');
        Route::post('/packages', [VoucherPackageController::class, 'store'])->name('packages.store');
        Route::get('/packages/{package}/edit', [VoucherPackageController::class, 'edit'])->name('packages.edit');
        Route::put('/packages/{package}', [VoucherPackageController::class, 'update'])->name('packages.update');
        Route::delete('/packages/{package}', [VoucherPackageController::class, 'destroy'])->name('packages.destroy');
        Route::post('/packages/{package}/toggle', [VoucherPackageController::class, 'toggle'])->name('packages.toggle');

        // Keuangan & Analisis Laba
        Route::get('/finances', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('/finances/print', [FinanceController::class, 'printReport'])->name('finance.print');

        // Unduh Backup Database Mandiri (Super Admin)
        Route::get('/settings/backup-database', [SettingController::class, 'downloadBackup'])->name('settings.backup_database');
    });
});
