<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

Route::get('/work-orders', [WorkOrderController::class, 'index'])->middleware('auth')->name('work-orders.index');

Route::patch('/work-orders/{workOrder}', [WorkOrderController::class, 'update'])->middleware('auth')->name('work-orders.update');

Route::get('/work-orders/create', [WorkOrderController::class, 'create'])
    ->middleware('auth')
    ->name('work-orders.create');

Route::post('/work-orders', [WorkOrderController::class, 'store'])
    ->middleware('auth')
    ->name('work-orders.store');

Route::get('/work-orders/{workOrder}/edit', [WorkOrderController::class, 'edit'])
    ->middleware('auth')
    ->name('work-orders.edit');

Route::put('/work-orders/{workOrder}', [WorkOrderController::class, 'updateDetails'])
    ->middleware('auth')
    ->name('work-orders.update-details');

Route::delete('/work-orders/{workOrder}', [WorkOrderController::class, 'destroy'])
    ->middleware('auth')
    ->name('work-orders.destroy');

require __DIR__.'/settings.php';
