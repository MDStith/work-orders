<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkOrderController;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/work-orders', [WorkOrderController::class, 'index'])->middleware('auth')->name('work-orders.index');

require __DIR__.'/settings.php';
