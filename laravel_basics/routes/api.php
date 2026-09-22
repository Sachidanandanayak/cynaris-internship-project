<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PostApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Week 4 Day 2 (Cynaris Internship)
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
});
