<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\TryoutManagementController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\TryoutQuestionController;
use App\Http\Controllers\StudentTryoutController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Admin Manajemen routes
    Route::middleware('role:admin_manajemen')->prefix('admin')->group(function () {
        // User management
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::post('/users', [UserManagementController::class, 'store']);
        Route::get('/users/{id}', [UserManagementController::class, 'show']);
        Route::put('/users/{id}', [UserManagementController::class, 'update']);
        Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);

        // Tryout management
        Route::get('/tryouts', [TryoutManagementController::class, 'index']);
        Route::post('/tryouts', [TryoutManagementController::class, 'store']);
        Route::get('/tryouts/{id}', [TryoutManagementController::class, 'show']);
        Route::put('/tryouts/{id}', [TryoutManagementController::class, 'update']);
        Route::delete('/tryouts/{id}', [TryoutManagementController::class, 'destroy']);
        Route::post('/tryouts/{id}/toggle-active', [TryoutManagementController::class, 'toggleActive']);

        // Reports
        Route::get('/reports/tryouts', [ReportController::class, 'tryoutsList']);
        Route::get('/reports/tryouts/{tryout_id}', [ReportController::class, 'tryoutDetail']);
        Route::get('/reports/students/{student_id}', [ReportController::class, 'studentHistory']);
    });

    // Admin Pembuat Soal routes
    Route::middleware('role:admin_pembuat_soal,admin_manajemen')->group(function () {
        // Subject management
        Route::get('/subjects', [QuestionBankController::class, 'indexSubjects']);
        Route::post('/subjects', [QuestionBankController::class, 'storeSubject']);
        Route::get('/subjects/{id}', [QuestionBankController::class, 'showSubject']);
        Route::put('/subjects/{id}', [QuestionBankController::class, 'updateSubject']);
        Route::delete('/subjects/{id}', [QuestionBankController::class, 'destroySubject']);

        // Question bank management
        Route::get('/questions', [QuestionBankController::class, 'indexQuestions']);
        Route::post('/questions', [QuestionBankController::class, 'storeQuestion']);
        Route::get('/questions/{id}', [QuestionBankController::class, 'showQuestion']);
        Route::put('/questions/{id}', [QuestionBankController::class, 'updateQuestion']);
        Route::delete('/questions/{id}', [QuestionBankController::class, 'destroyQuestion']);

        // Tryout question sets
        Route::get('/tryouts/{tryout_id}/questions', [TryoutQuestionController::class, 'index']);
        Route::post('/tryouts/{tryout_id}/questions', [TryoutQuestionController::class, 'store']);
        Route::put('/tryouts/{tryout_id}/questions/{tryout_question_set_id}', [TryoutQuestionController::class, 'update']);
        Route::delete('/tryouts/{tryout_id}/questions/{tryout_question_set_id}', [TryoutQuestionController::class, 'destroy']);
    });

    // Student routes
    Route::middleware('role:siswa')->prefix('student')->group(function () {
        Route::get('/tryouts', [StudentTryoutController::class, 'index']);
        Route::post('/tryouts/{tryout_id}/start', [StudentTryoutController::class, 'start']);
        Route::get('/tryouts/{student_tryout_id}/questions', [StudentTryoutController::class, 'getQuestions']);
        Route::post('/tryouts/{student_tryout_id}/answers', [StudentTryoutController::class, 'saveAnswer']);
        Route::post('/tryouts/{student_tryout_id}/submit', [StudentTryoutController::class, 'submit']);
        Route::get('/tryouts/{student_tryout_id}/result', [StudentTryoutController::class, 'result']);
        Route::get('/tryouts/{student_tryout_id}/review', [StudentTryoutController::class, 'review']);
    });
});