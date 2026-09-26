<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\JobApiController;
use App\Http\Controllers\Api\PostApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Cynaris Internship (Week 4 Day 2 + Capstone Week 4 Day 5)
|--------------------------------------------------------------------------
|
| 1. Authentication Endpoints:
|    - POST /api/login          -> Obtain Sanctum Bearer token
|    - POST /api/auth/token     -> Alias for token creation
|    - POST /api/logout         -> Revoke current token (auth:sanctum)
|    - GET  /api/user           -> Authenticated user profile (auth:sanctum)
|
| 2. RESTful Post Model Endpoints:
|    - GET    /api/posts        -> Index collection (Public, 200 OK)
|    - GET    /api/posts/{post} -> Show single post (Public, 200 OK / 404)
|    - POST   /api/posts        -> Create post (Protected, 201 Created / 401 / 422)
|    - PUT    /api/posts/{post} -> Update post (Protected, 200 OK / 401 / 404 / 422)
|    - DELETE /api/posts/{post} -> Destroy post (Protected, 200 OK / 401 / 404)
|
| 3. Capstone Job Board & Recruitment REST Endpoints:
|    - GET    /api/jobs              -> List jobs with search & filters (Public, 200 OK)
|    - GET    /api/jobs/{job}        -> Job detail (Public, 200 OK / 404)
|    - POST   /api/jobs              -> Create job (auth:sanctum, 201 Created / 422)
|    - PUT    /api/jobs/{job}        -> Update job (auth:sanctum, 200 OK / 422 / 404)
|    - DELETE /api/jobs/{job}        -> Delete job (auth:sanctum, 200 OK / 404)
|    - POST   /api/jobs/{job}/apply  -> Apply to job (auth:sanctum, 201 Created / 422)
|    - GET    /api/user/applications -> User's submitted applications (auth:sanctum, 200 OK)
|--------------------------------------------------------------------------
*/

// ==========================================
// Public Authentication Flow
// ==========================================
Route::post('/login', [AuthApiController::class, 'login'])->name('api.login');
Route::post('/auth/token', [AuthApiController::class, 'login'])->name('api.auth.token');

// ==========================================
// Public Read Endpoints (Posts API)
// ==========================================
Route::get('/posts', [PostApiController::class, 'index'])->name('api.posts.index');
Route::get('/posts/{post}', [PostApiController::class, 'show'])->name('api.posts.show');

// ==========================================
// Public Read Endpoints (Capstone Jobs API)
// ==========================================
Route::get('/jobs', [JobApiController::class, 'index'])->name('api.jobs.index');
Route::get('/jobs/{job}', [JobApiController::class, 'show'])->name('api.jobs.show');

// ==========================================
// Protected Endpoints (auth:sanctum)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    // Current Authenticated User & Token Management
    Route::get('/user', [AuthApiController::class, 'user'])->name('api.user');
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('api.logout');

    // RESTful Post Write Operations
    Route::post('/posts', [PostApiController::class, 'store'])->name('api.posts.store');
    Route::match(['put', 'patch'], '/posts/{post}', [PostApiController::class, 'update'])->name('api.posts.update');
    Route::delete('/posts/{post}', [PostApiController::class, 'destroy'])->name('api.posts.destroy');

    // Capstone Job Management CRUD Operations
    Route::post('/jobs', [JobApiController::class, 'store'])->name('api.jobs.store');
    Route::match(['put', 'patch'], '/jobs/{job}', [JobApiController::class, 'update'])->name('api.jobs.update');
    Route::delete('/jobs/{job}', [JobApiController::class, 'destroy'])->name('api.jobs.destroy');

    // Capstone Job Applications Endpoints
    Route::post('/jobs/{job}/apply', [JobApiController::class, 'apply'])->name('api.jobs.apply');
    Route::get('/user/applications', [JobApiController::class, 'userApplications'])->name('api.user.applications');
});
