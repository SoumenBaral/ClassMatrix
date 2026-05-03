<?php

use App\Http\Controllers\Student\AiChatController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\RoutineController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'student'])->prefix('student')->name('student.')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Routines
    Route::get('routines', [RoutineController::class, 'index'])->name('routines.index');
    Route::get('routines/preferences', [RoutineController::class, 'preferences'])->name('routines.preferences');
    Route::post('routines/preferences', [RoutineController::class, 'savePreferences'])->name('routines.preferences.save');
    Route::post('routines/generate', [RoutineController::class, 'generate'])->name('routines.generate');

    // AI Chat
    Route::get('chat', [AiChatController::class, 'index'])->name('chat.index');
    Route::post('chat', [AiChatController::class, 'create'])->name('chat.create');
    Route::get('chat/{aiChat}', [AiChatController::class, 'show'])->name('chat.show');
    Route::post('chat/{aiChat}/send', [AiChatController::class, 'send'])->name('chat.send');
    Route::delete('chat/{aiChat}', [AiChatController::class, 'destroy'])->name('chat.destroy');
});
