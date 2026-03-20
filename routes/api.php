<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PrivateFileController;
use App\Http\Controllers\Api\SchoolStudentController;
use App\Http\Controllers\Api\StudentPortfolioController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::get('/user', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('school')->group(function () {
    Route::get('/students', [SchoolStudentController::class, 'index']);
    Route::post('/students', [SchoolStudentController::class, 'store']);
    Route::get('/students/search', [SchoolStudentController::class, 'search']);
    Route::get('/students/{id}', [SchoolStudentController::class, 'show']);
    Route::put('/students/{id}', [SchoolStudentController::class, 'update']);
    Route::delete('/students/{id}', [SchoolStudentController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->prefix('student')->group(function () {
    Route::put('/profile', [StudentPortfolioController::class, 'updateProfile']);
    Route::post('/portfolio-items', [StudentPortfolioController::class, 'storePortfolioItem']);
    Route::get('/application-reminder', [StudentPortfolioController::class, 'applicationReminder']);
});

Route::middleware('auth:sanctum')->prefix('files')->group(function () {
    Route::get('/me/{type}', [PrivateFileController::class, 'downloadMyDocument']);
});
