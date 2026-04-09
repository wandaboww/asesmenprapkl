<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;

Route::get('/', [HomeController::class, 'index'])->name('login');
Route::post('/login', [HomeController::class, 'attemptLogin']);
Route::get('/logout', [HomeController::class, 'logout'])->name('logout');

Route::get('/api/students-by-class', [HomeController::class, 'getStudentsByClass']);

// Student Area
Route::middleware('student')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    Route::get('/assessment', [StudentController::class, 'assessment'])->name('student.assessment');
    Route::post('/assessment/submit', [StudentController::class, 'submitAssessment'])->name('student.assessment.submit');
    Route::get('/result', [StudentController::class, 'result'])->name('student.result');
});

// Admin Area
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});
Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'attemptLogin']);
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/results', [AdminController::class, 'results'])->name('admin.results');
    Route::get('/export-excel', [AdminController::class, 'exportExcel'])->name('admin.export');
    Route::get('/students', [AdminController::class, 'manageStudents'])->name('admin.students');
    Route::post('/students/import', [AdminController::class, 'importStudents'])->name('admin.students.import');
    Route::get('/students/template', [AdminController::class, 'downloadTemplate'])->name('admin.students.template');
    Route::post('/students/{id}/reset-assessment', [AdminController::class, 'resetAssessment'])->name('admin.students.reset');
    Route::delete('/students/{id}', [AdminController::class, 'deleteStudent'])->name('admin.students.delete');
});
