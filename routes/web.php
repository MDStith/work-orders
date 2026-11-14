<?php

use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/work-orders', [WorkOrderController::class, 'index'])->middleware('auth')->name('work-orders.index');

Route::patch('/work-orders/{workOrder}', [WorkOrderController::class, 'update'])->middleware('auth')->name('work-orders.update');

require __DIR__.'/settings.php';
