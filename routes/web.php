<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\DailyStockInput;
use App\Livewire\ProcurementForm;
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
    // Manager — Procurement Form
    // ──────────────────────────────────────────────
    Route::get('/procurement/{id}', ProcurementForm::class)
        ->middleware('role:manager')
        ->name('procurement.create');

    // ──────────────────────────────────────────────
    // Admin UID & Manager — Warehouse Stock Detail
    // ──────────────────────────────────────────────
    Route::get('/warehouse/{id}', WarehouseStockDetail::class)
        ->middleware('role:admin_uid,manager')
        ->name('warehouse.detail');
});

require __DIR__.'/auth.php';
