<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ClassLevelController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\HostelController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LibraryController;
use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\FeeController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\GradeSchemeController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LeaveController;
use App\Http\Controllers\Admin\MarkController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\PeriodController;
use App\Http\Controllers\Admin\TransportController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TimetableController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Academic Years
    Route::get('academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
    Route::post('academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
    Route::put('academic-years/{academicYear}', [AcademicYearController::class, 'update'])->name('academic-years.update');
    Route::delete('academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');
    Route::post('academic-years/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent'])->name('academic-years.set-current');

    // Classes
    Route::get('classes', [ClassLevelController::class, 'index'])->name('classes.index');
    Route::post('classes', [ClassLevelController::class, 'store'])->name('classes.store');
    Route::put('classes/{classLevel}', [ClassLevelController::class, 'update'])->name('classes.update');
    Route::delete('classes/{classLevel}', [ClassLevelController::class, 'destroy'])->name('classes.destroy');

    // Sections
    Route::post('sections', [SectionController::class, 'store'])->name('sections.store');
    Route::put('sections/{section}', [SectionController::class, 'update'])->name('sections.update');
    Route::delete('sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

    // Subjects
    Route::get('subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::put('subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    // Departments
    Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    // Periods
    Route::get('periods', [PeriodController::class, 'index'])->name('periods.index');
    Route::post('periods', [PeriodController::class, 'store'])->name('periods.store');
    Route::put('periods/{period}', [PeriodController::class, 'update'])->name('periods.update');
    Route::delete('periods/{period}', [PeriodController::class, 'destroy'])->name('periods.destroy');

    // Timetable
    Route::get('timetable', [TimetableController::class, 'index'])->name('timetable.index');
    Route::post('timetable', [TimetableController::class, 'store'])->name('timetable.store');
    Route::post('timetable/bulk', [TimetableController::class, 'bulkStore'])->name('timetable.bulk-store');
    Route::delete('timetable/{timetable}', [TimetableController::class, 'destroy'])->name('timetable.destroy');

    // Attendance
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/bulk', [AttendanceController::class, 'bulkStore'])->name('attendance.bulk-store');
    Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');

    // Notices
    Route::get('notices', [NoticeController::class, 'index'])->name('notices.index');
    Route::post('notices', [NoticeController::class, 'store'])->name('notices.store');
    Route::put('notices/{notice}', [NoticeController::class, 'update'])->name('notices.update');
    Route::delete('notices/{notice}', [NoticeController::class, 'destroy'])->name('notices.destroy');

    // Calendar & Events
    Route::get('calendar', [EventController::class, 'index'])->name('calendar.index');
    Route::post('events', [EventController::class, 'store'])->name('events.store');
    Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('holidays', [EventController::class, 'storeHoliday'])->name('holidays.store');
    Route::delete('holidays/{holiday}', [EventController::class, 'destroyHoliday'])->name('holidays.destroy');

    // Exams
    Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
    Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
    Route::put('exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
    Route::delete('exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
    Route::get('exams/{exam}/schedules', [ExamController::class, 'schedules'])->name('exams.schedules');
    Route::post('exams/{exam}/schedules', [ExamController::class, 'storeSchedule'])->name('exams.schedules.store');
    Route::delete('exam-schedules/{examSchedule}', [ExamController::class, 'destroySchedule'])->name('exam-schedules.destroy');

    // Marks
    Route::get('marks', [MarkController::class, 'index'])->name('marks.index');
    Route::post('marks/bulk', [MarkController::class, 'bulkStore'])->name('marks.bulk-store');
    Route::get('results', [MarkController::class, 'results'])->name('results.index');

    // Grade Schemes
    Route::get('grades', [GradeSchemeController::class, 'index'])->name('grades.index');
    Route::post('grades', [GradeSchemeController::class, 'store'])->name('grades.store');
    Route::put('grades/{gradeScheme}', [GradeSchemeController::class, 'update'])->name('grades.update');
    Route::delete('grades/{gradeScheme}', [GradeSchemeController::class, 'destroy'])->name('grades.destroy');

    // Fee Structure
    Route::get('fees', [FeeController::class, 'index'])->name('fees.index');
    Route::post('fees', [FeeController::class, 'store'])->name('fees.store');
    Route::delete('fees/{feeStructure}', [FeeController::class, 'destroy'])->name('fees.destroy');
    Route::post('fee-categories', [FeeController::class, 'storeCategory'])->name('fee-categories.store');
    Route::delete('fee-categories/{feeCategory}', [FeeController::class, 'destroyCategory'])->name('fee-categories.destroy');

    // Invoices
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'recordPayment'])->name('invoices.pay');

    // Payroll
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
    Route::post('payslips/{payslip}/paid', [PayrollController::class, 'markPaid'])->name('payslips.paid');
    Route::get('salary-structures', [PayrollController::class, 'structures'])->name('salary-structures.index');
    Route::post('salary-structures', [PayrollController::class, 'storeStructure'])->name('salary-structures.store');
    Route::put('salary-structures/{salaryStructure}', [PayrollController::class, 'updateStructure'])->name('salary-structures.update');
    Route::delete('salary-structures/{salaryStructure}', [PayrollController::class, 'destroyStructure'])->name('salary-structures.destroy');

    // Leaves
    Route::get('leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::post('leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
    Route::post('leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');

    // Library
    Route::get('library', [LibraryController::class, 'index'])->name('library.index');
    Route::post('books', [LibraryController::class, 'store'])->name('books.store');
    Route::put('books/{book}', [LibraryController::class, 'update'])->name('books.update');
    Route::delete('books/{book}', [LibraryController::class, 'destroy'])->name('books.destroy');
    Route::get('library/issues', [LibraryController::class, 'issues'])->name('library.issues');
    Route::post('library/issue', [LibraryController::class, 'issueBook'])->name('library.issue');
    Route::post('library/return/{bookIssue}', [LibraryController::class, 'returnBook'])->name('library.return');
    Route::post('book-categories', [LibraryController::class, 'storeCategory'])->name('book-categories.store');
    Route::delete('book-categories/{bookCategory}', [LibraryController::class, 'destroyCategory'])->name('book-categories.destroy');

    // Transport
    Route::get('transport', [TransportController::class, 'index'])->name('transport.index');
    Route::post('vehicles', [TransportController::class, 'storeVehicle'])->name('vehicles.store');
    Route::delete('vehicles/{vehicle}', [TransportController::class, 'destroyVehicle'])->name('vehicles.destroy');
    Route::post('routes', [TransportController::class, 'storeRoute'])->name('routes.store');
    Route::put('routes/{route}', [TransportController::class, 'updateRoute'])->name('routes.update');
    Route::delete('routes/{route}', [TransportController::class, 'destroyRoute'])->name('routes.destroy');
    Route::post('routes/{route}/stops', [TransportController::class, 'storeStop'])->name('route-stops.store');
    Route::delete('route-stops/{routeStop}', [TransportController::class, 'destroyStop'])->name('route-stops.destroy');

    // Hostel
    Route::get('hostel', [HostelController::class, 'index'])->name('hostel.index');
    Route::post('hostels', [HostelController::class, 'store'])->name('hostels.store');
    Route::put('hostels/{hostel}', [HostelController::class, 'update'])->name('hostels.update');
    Route::delete('hostels/{hostel}', [HostelController::class, 'destroy'])->name('hostels.destroy');
    Route::post('hostels/{hostel}/rooms', [HostelController::class, 'storeRoom'])->name('rooms.store');
    Route::delete('rooms/{room}', [HostelController::class, 'destroyRoom'])->name('rooms.destroy');

    // Inventory
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::put('inventory/{inventoryItem}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('inventory/{inventoryItem}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('inventory/{inventoryItem}/stock', [InventoryController::class, 'addStock'])->name('inventory.stock');
    Route::post('item-categories', [InventoryController::class, 'storeCategory'])->name('item-categories.store');
    Route::delete('item-categories/{itemCategory}', [InventoryController::class, 'destroyCategory'])->name('item-categories.destroy');

    // Lessons
    Route::get('lessons', [LessonController::class, 'index'])->name('lessons.index');
    Route::post('lessons', [LessonController::class, 'store'])->name('lessons.store');
    Route::put('lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
    Route::delete('lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');

    // Assignments
    Route::get('assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::put('assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
    Route::get('assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('assignments.submissions');
    Route::post('submissions/{submission}/grade', [AssignmentController::class, 'gradeSubmission'])->name('submissions.grade');

    // Quizzes
    Route::get('quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::post('quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::delete('quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy');
    Route::get('quizzes/{quiz}/questions', [QuizController::class, 'questions'])->name('quizzes.questions');
    Route::post('quizzes/{quiz}/questions', [QuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::delete('quiz-questions/{quizQuestion}', [QuizController::class, 'destroyQuestion'])->name('quiz-questions.destroy');
    Route::get('quizzes/{quiz}/results', [QuizController::class, 'results'])->name('quizzes.results');
});
