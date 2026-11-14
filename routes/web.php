<?php

use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/work-orders', [WorkOrderController::class, 'index'])->middleware('auth')->name('work-orders.index');

Route::patch('/work-orders/{workOrder}', [WorkOrderController::class, 'update'])->middleware('auth')->name('work-orders.update');

Route::get('/work-orders/create', [WorkOrderController::class, 'create'])
    ->middleware('auth')
    ->name('work-orders.create');

Route::post('/work-orders', [WorkOrderController::class, 'store'])
    ->middleware('auth')
    ->name('work-orders.store');

require __DIR__.'/settings.php';
