<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExploreController;
use App\Http\Controllers\Api\ForYouController;
use App\Http\Controllers\Api\MitraController;
use App\Http\Controllers\Api\PartnershipProposalController;
use App\Http\Controllers\Api\PrivateFileController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\SchoolStudentController;
use App\Http\Controllers\Api\StudentJobApplicationController;
use App\Http\Controllers\Api\StudentPortfolioController;
use Illuminate\Support\Facades\File;
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

Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::put('/', [ProfileController::class, 'update']);
});

Route::get('/docs/openapi', function () {
    $openApiPath = public_path('docs/swagger/openapi.yaml');

    if (! File::exists($openApiPath)) {
        return response()->json([
            'success' => false,
            'message' => 'OpenAPI spec tidak ditemukan.',
            'data' => null,
            'errors' => null,
            'meta' => null,
        ], 404);
    }

    return response()->file($openApiPath, [
        'Content-Type' => 'application/yaml; charset=UTF-8',
        'Cache-Control' => 'public, max-age=300',
    ]);
});

Route::middleware('auth:sanctum')->prefix('school')->group(function () {
    Route::get('/students', [SchoolStudentController::class, 'index']);
    Route::post('/students', [SchoolStudentController::class, 'store']);
    Route::get('/students/search', [SchoolStudentController::class, 'search']);
    Route::get('/students/{id}', [SchoolStudentController::class, 'show']);
    Route::put('/students/{id}', [SchoolStudentController::class, 'update']);
    Route::delete('/students/{id}', [SchoolStudentController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->prefix('student')->group(function () {
    Route::post('/portfolio-items', [StudentPortfolioController::class, 'storePortfolioItem']);
    Route::get('/application-reminder', [StudentPortfolioController::class, 'applicationReminder']);
    Route::get('/job-applications', [StudentJobApplicationController::class, 'index']);
    Route::post('/job-applications', [StudentJobApplicationController::class, 'store']);
    Route::get('/job-applications/{id}', [StudentJobApplicationController::class, 'show'])->whereNumber('id');
});

Route::middleware('auth:sanctum')->prefix('partnership-proposals')->group(function () {
    Route::get('/', [PartnershipProposalController::class, 'index']);
    Route::post('/', [PartnershipProposalController::class, 'store']);
    Route::get('/{id}', [PartnershipProposalController::class, 'show'])->whereNumber('id');
    Route::put('/{id}', [PartnershipProposalController::class, 'update'])->whereNumber('id');
    Route::post('/{id}/submit', [PartnershipProposalController::class, 'submit'])->whereNumber('id');
});

Route::middleware('auth:sanctum')->prefix('files')->group(function () {
    Route::get('/me/{type}', [PrivateFileController::class, 'downloadMyDocument']);
});

Route::prefix('public/explore')->group(function () {
    Route::get('/jobs', [ExploreController::class, 'index']);
    Route::get('/jobs/{slug}', [ExploreController::class, 'show']);
    Route::get('/filters', [ExploreController::class, 'filterOptions']);
});

Route::prefix('public/for-you')->group(function () {
    Route::get('/jobs', [ForYouController::class, 'index']);
    Route::get('/jobs/{slug}', [ForYouController::class, 'show']);
    Route::get('/sort-options', [ForYouController::class, 'sortOptions']);
});

Route::prefix('public/mitra')->group(function () {
    Route::get('/', [MitraController::class, 'index']);
    Route::get('/{id}', [MitraController::class, 'show'])->whereNumber('id');
});

Route::prefix('public/schools')->group(function () {
    Route::get('/', [SchoolController::class, 'index']);
    Route::get('/{id}', [SchoolController::class, 'show'])->whereNumber('id');
});
