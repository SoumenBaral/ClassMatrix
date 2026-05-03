<?php

use App\Http\Controllers\ParentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('dashboard', [ParentController::class, 'dashboard'])->name('dashboard');
    Route::get('children', [ParentController::class, 'children'])->name('children');
    Route::get('attendance', [ParentController::class, 'attendance'])->name('attendance');
    Route::get('results', [ParentController::class, 'results'])->name('results');
    Route::get('notices', [ParentController::class, 'notices'])->name('notices');
});
