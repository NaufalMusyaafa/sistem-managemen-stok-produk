<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\DailyStockInput;
use App\Livewire\ManageProducts;
use App\Livewire\ManageOrders;
use App\Livewire\ManageUsers;
use App\Livewire\ManageWarehouseProducts;
use App\Livewire\ManageWarehouses;
use App\Livewire\ProcurementForm;
use App\Livewire\StatusDetail;
use App\Livewire\WarehouseStockDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    // Redirect admin_up3 to their daily-stock page
    if (Auth::user() && Auth::user()->role === 'admin_up3') {
        return redirect()->route('daily-stock');
    }
    // Manager (new monitoring role) uses a different dashboard view
    if (Auth::user() && Auth::user()->role === 'manager') {
        return view('dashboard-manager');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ──────────────────────────────────────────────
    // Admin UP3 — Daily Stock Input
    // ──────────────────────────────────────────────
    Route::get('/daily-stock', DailyStockInput::class)
        ->middleware('role:admin_up3')
        ->name('daily-stock');

    // ──────────────────────────────────────────────
    // Rent — Procurement Form
    // ──────────────────────────────────────────────
    Route::get('/procurement/{id}', ProcurementForm::class)
        ->middleware('role:rent')
        ->name('procurement.create');

    // ──────────────────────────────────────────────
    // Admin UID, Rent & Manager — Warehouse Stock Detail
    // ──────────────────────────────────────────────
    Route::get('/warehouse/{id}', WarehouseStockDetail::class)
        ->middleware('role:admin_uid,rent,manager')
        ->name('warehouse.detail');

    // ──────────────────────────────────────────────
    // Admin UID & Admin UP3 — Warehouse Product Management
    // ──────────────────────────────────────────────
    Route::get('/manage/warehouse-products', ManageWarehouseProducts::class)
        ->middleware('role:admin_uid,admin_up3')
        ->name('manage.warehouse-products');

    // ──────────────────────────────────────────────
    // Admin UID, Admin UP3, Rent — Order Management
    // ──────────────────────────────────────────────
    Route::get('/manage/orders', ManageOrders::class)
        ->middleware('role:admin_uid,admin_up3,rent')
        ->name('manage.orders');

    // ──────────────────────────────────────────────
    // Manager (new) — Status Detail Pages
    // ──────────────────────────────────────────────
    Route::get('/status/{type}', StatusDetail::class)
        ->middleware('role:manager')
        ->name('status.detail');

    // ──────────────────────────────────────────────
    // Admin UID — CMS Management Pages
    // ──────────────────────────────────────────────
    Route::middleware('role:admin_uid')->group(function () {
        Route::get('/manage/products', ManageProducts::class)->name('manage.products');
        Route::get('/manage/warehouses', ManageWarehouses::class)->name('manage.warehouses');
        Route::get('/manage/users', ManageUsers::class)->name('manage.users');
    });
});

require __DIR__.'/auth.php';
