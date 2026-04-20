<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BatchTwoCtAdminController;
use App\Http\Controllers\StudentBatchTwoCtController;

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

    Route::get('/batch2-ct/assessment', [StudentBatchTwoCtController::class, 'assessment'])->name('student.batch2ct.assessment');
    Route::post('/batch2-ct/assessment', [StudentBatchTwoCtController::class, 'submit'])->name('student.batch2ct.submit');
    Route::get('/batch2-ct/result/{result?}', [StudentBatchTwoCtController::class, 'result'])->name('student.batch2ct.result');
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
    Route::get('/questions', [AdminController::class, 'manageQuestions'])->name('admin.questions');
    Route::post('/questions', [AdminController::class, 'storeQuestion'])->name('admin.questions.store');
    Route::post('/questions/import', [AdminController::class, 'importQuestions'])->name('admin.questions.import');
    Route::get('/questions/export', [AdminController::class, 'exportQuestions'])->name('admin.questions.export');
    Route::get('/questions/template', [AdminController::class, 'downloadQuestionTemplate'])->name('admin.questions.template');
    Route::get('/questions/{question}/edit', [AdminController::class, 'editQuestion'])->name('admin.questions.edit');
    Route::put('/questions/{question}', [AdminController::class, 'updateQuestion'])->name('admin.questions.update');
    Route::delete('/questions/{question}', [AdminController::class, 'deleteQuestion'])->name('admin.questions.delete');
    Route::post('/batches', [AdminController::class, 'storeBatch'])->name('admin.batches.store');
    Route::put('/batches/{batch}', [AdminController::class, 'updateBatch'])->name('admin.batches.update');
    Route::post('/batches/{batch}/activate', [AdminController::class, 'activateBatch'])->name('admin.batches.activate');
    Route::get('/students', [AdminController::class, 'manageStudents'])->name('admin.students');
    Route::post('/students/import', [AdminController::class, 'importStudents'])->name('admin.students.import');
    Route::get('/students/template', [AdminController::class, 'downloadTemplate'])->name('admin.students.template');
    Route::post('/students/{id}/reset-assessment', [AdminController::class, 'resetAssessment'])->name('admin.students.reset');
    Route::delete('/students/{id}', [AdminController::class, 'deleteStudent'])->name('admin.students.delete');

    Route::get('/batch2-ct', [BatchTwoCtAdminController::class, 'index'])->name('admin.batch2ct.index');
    Route::post('/batch2-ct/questions', [BatchTwoCtAdminController::class, 'store'])->name('admin.batch2ct.questions.store');
    Route::get('/batch2-ct/questions/{question}/edit', [BatchTwoCtAdminController::class, 'edit'])->name('admin.batch2ct.questions.edit');
    Route::put('/batch2-ct/questions/{question}', [BatchTwoCtAdminController::class, 'update'])->name('admin.batch2ct.questions.update');
    Route::delete('/batch2-ct/questions/{question}', [BatchTwoCtAdminController::class, 'destroy'])->name('admin.batch2ct.questions.delete');
    Route::get('/batch2-ct/export/json', [BatchTwoCtAdminController::class, 'exportJson'])->name('admin.batch2ct.export.json');
    Route::post('/batch2-ct/import/json', [BatchTwoCtAdminController::class, 'importJson'])->name('admin.batch2ct.import.json');
    Route::get('/batch2-ct/export/excel', [BatchTwoCtAdminController::class, 'exportExcel'])->name('admin.batch2ct.export.excel');
    Route::post('/batch2-ct/import/excel', [BatchTwoCtAdminController::class, 'importExcel'])->name('admin.batch2ct.import.excel');
});
