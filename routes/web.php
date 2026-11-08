<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\Master\DivisionController;
use App\Http\Controllers\Master\ItemController;
use App\Http\Controllers\Master\PeriodController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\NeedsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RecapController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{id}', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('/requisitions', [RequisitionController::class, 'index'])->name('requisitions.index');
    Route::middleware('permission:requisitions.create')->group(function () {
        Route::get('/requisitions/create', [RequisitionController::class, 'create'])->name('requisitions.create');
        Route::post('/requisitions', [RequisitionController::class, 'store'])->name('requisitions.store');
        Route::get('/requisitions/{requisition}/edit', [RequisitionController::class, 'edit'])->name('requisitions.edit');
        Route::put('/requisitions/{requisition}', [RequisitionController::class, 'update'])->name('requisitions.update');
    });
    Route::get('/requisitions/{requisition}', [RequisitionController::class, 'show'])->name('requisitions.show');
    Route::get('/requisitions/{requisition}/preview', [RequisitionController::class, 'preview'])->name('requisitions.preview');

    Route::get('/inbox', InboxController::class)->middleware('permission:requisitions.view_all')->name('inbox.index');

    Route::middleware('permission:approvals.manager')->group(function () {
        Route::get('/approvals/manager', [ApprovalController::class, 'managerIndex'])->name('approvals.manager.index');
        Route::get('/approvals/manager/{requisition}', [ApprovalController::class, 'managerShow'])->name('approvals.manager.show');
        Route::post('/approvals/manager/{requisition}', [ApprovalController::class, 'managerDecide'])->name('approvals.manager.decide');
    });
    Route::middleware('permission:approvals.ga')->group(function () {
        Route::get('/approvals/ga', [ApprovalController::class, 'gaIndex'])->name('approvals.ga.index');
        Route::get('/approvals/ga/{requisition}', [ApprovalController::class, 'gaShow'])->name('approvals.ga.show');
        Route::post('/approvals/ga/{requisition}', [ApprovalController::class, 'gaDecide'])->name('approvals.ga.decide');
    });
    Route::middleware('permission:approvals.sam')->group(function () {
        Route::get('/approvals/sam', [ApprovalController::class, 'samIndex'])->name('approvals.sam.index');
        Route::get('/approvals/sam/{requisition}', [ApprovalController::class, 'samShow'])->name('approvals.sam.show');
        Route::post('/approvals/sam/{requisition}', [ApprovalController::class, 'samDecide'])->name('approvals.sam.decide');
    });
    Route::middleware('permission:approvals.sm')->group(function () {
        Route::get('/approvals/sm', [ApprovalController::class, 'smIndex'])->name('approvals.sm.index');
        Route::get('/approvals/sm/{requisition}', [ApprovalController::class, 'smShow'])->name('approvals.sm.show');
        Route::post('/approvals/sm/{requisition}', [ApprovalController::class, 'smDecide'])->name('approvals.sm.decide');
    });

    Route::middleware('permission:procurement.manage')->group(function () {
        Route::get('/procurements', [ProcurementController::class, 'index'])->name('procurements.index');
        Route::get('/procurements/create', [ProcurementController::class, 'create'])->name('procurements.create');
        Route::post('/procurements', [ProcurementController::class, 'store'])->name('procurements.store');
        Route::get('/procurements/{procurement}/attachment/{attachment}', [ProcurementController::class, 'attachment'])->name('procurements.attachment');
        Route::get('/procurements/{procurement}', [ProcurementController::class, 'show'])->name('procurements.show');
    });

    Route::middleware('permission:receipts.manage')->group(function () {
        Route::get('/receipts', [ReceiptController::class, 'index'])->name('receipts.index');
        Route::get('/receipts/create', [ReceiptController::class, 'create'])->name('receipts.create');
        Route::post('/receipts', [ReceiptController::class, 'store'])->name('receipts.store');
        Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
    });

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/recap', RecapController::class)->name('recap.index');
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/needs', NeedsController::class)->name('needs.index');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/excel', [ReportController::class, 'excel'])->name('reports.excel');
        Route::get('/reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
        Route::get('/charts', ChartController::class)->name('charts.index');
    });

    Route::middleware('permission:stock.manage')->group(function () {
        Route::post('/stock/issue', [StockController::class, 'issue'])->name('stock.issue');
        Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
    });

    Route::middleware('permission:items.manage')->prefix('master')->name('master.')->group(function () {
        Route::resource('items', ItemController::class)->except(['show']);
    });
    Route::middleware('permission:divisions.manage')->prefix('master')->name('master.')->group(function () {
        Route::get('divisions', [DivisionController::class, 'index'])->name('divisions.index');
        Route::post('divisions', [DivisionController::class, 'store'])->name('divisions.store');
        Route::put('divisions/{division}', [DivisionController::class, 'update'])->name('divisions.update');
    });
    Route::middleware('permission:suppliers.manage')->prefix('master')->name('master.')->group(function () {
        Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });
    Route::middleware('permission:periods.manage')->prefix('master')->name('master.')->group(function () {
        Route::get('periods', [PeriodController::class, 'index'])->name('periods.index');
        Route::post('periods', [PeriodController::class, 'store'])->name('periods.store');
        Route::put('periods/{period}', [PeriodController::class, 'update'])->name('periods.update');
    });

    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });
    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
    Route::get('/audit', AuditController::class)->middleware('permission:audit.view')->name('audit.index');
});

require __DIR__.'/auth.php';
