<?php

use App\Http\Controllers\Student\AiChatController;
use App\Http\Controllers\Student\AssignmentController;
use App\Http\Controllers\Student\AttendanceController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\LessonController;
use App\Http\Controllers\Student\QuizController;
use App\Http\Controllers\Student\ResultController;
use App\Http\Controllers\Student\RoutineController;
use App\Http\Controllers\Student\TimetableController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'student'])->prefix('student')->name('student.')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Timetable
    Route::get('timetable', TimetableController::class)->name('timetable');

    // Assignments
    Route::get('assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');

    // Results
    Route::get('results', ResultController::class)->name('results');

    // Attendance
    Route::get('attendance', AttendanceController::class)->name('attendance');

    // Quizzes
    Route::get('quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::post('quizzes/{quiz}/submit', [QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('quizzes/{quiz}/result', [QuizController::class, 'result'])->name('quizzes.result');

    // Lessons
    Route::get('lessons', [LessonController::class, 'index'])->name('lessons.index');
    Route::get('lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');

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
