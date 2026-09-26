<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseDemoController;
use App\Http\Controllers\DebuggingDemoController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Cynaris Internship
|--------------------------------------------------------------------------
|
| Week 3 Day 2 Demonstration Routes:
| 1. GET  /               -> HomeController@index       (name: home)
| 2. GET  /about/{topic?} -> HomeController@about       (name: about)
| 3. GET  /form           -> FormController@index       (name: form.index)
| 4. POST /form           -> FormController@submit      (name: form.submit)
| 5. POST /subscribe      -> FormController@subscribe   (name: newsletter.subscribe)
|
| Week 3 Day 3 MVC Architecture Routes (Resource Routing):
| - GET    /products          -> ProductController@index    (name: products.index)
| - GET    /products/create   -> ProductController@create   (name: products.create)
| - POST   /products          -> ProductController@store    (name: products.store)
| - GET    /products/{product}-> ProductController@show     (name: products.show)
| - GET    /products/{product}/edit -> ProductController@edit (name: products.edit)
| - PUT    /products/{product}-> ProductController@update   (name: products.update)
| - DELETE /products/{product}-> ProductController@destroy  (name: products.destroy)
|
| Week 3 Day 4 Database Integration & Eloquent Queries:
| - GET    /database-demo     -> DatabaseDemoController@index (name: database.demo)
|
| Week 3 Day 5 Blog CRUD Application:
| - GET    /blog              -> BlogPostController@index   (name: blog.index)
| - GET    /blog/create       -> BlogPostController@create  (name: blog.create)
| - POST   /blog              -> BlogPostController@store   (name: blog.store)
| - GET    /blog/{blog}       -> BlogPostController@show    (name: blog.show)
| - GET    /blog/{blog}/edit  -> BlogPostController@edit    (name: blog.edit)
| - PUT    /blog/{blog}       -> BlogPostController@update  (name: blog.update)
| - DELETE /blog/{blog}       -> BlogPostController@destroy (name: blog.destroy)
|
| Week 4 Day 1 Authentication Protected Routes:
| - GET    /dashboard         -> Dashboard View             (middleware: auth, verified)
| - GET    /profile           -> ProfileController@edit     (middleware: auth)
| - PATCH  /profile           -> ProfileController@update   (middleware: auth)
| - DELETE /profile           -> ProfileController@destroy  (middleware: auth)
| - GET    /account           -> AccountController@index    (middleware: auth)
|--------------------------------------------------------------------------
*/

// ==========================================
// Week 3 Routes (Preserved Exactly)
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about/{topic?}', [HomeController::class, 'about'])->name('about');
Route::get('/form', [FormController::class, 'index'])->name('form.index');
Route::post('/form', [FormController::class, 'submit'])->name('form.submit');
Route::post('/subscribe', [FormController::class, 'subscribe'])->name('newsletter.subscribe');

// Week 3 Day 3: Product Resource CRUD with Route Model Binding
Route::resource('products', ProductController::class);

// Week 3 Day 4: Database Integration & Eloquent Demonstration
Route::get('/database-demo', [DatabaseDemoController::class, 'index'])->name('database.demo');

// Week 3 Day 5: Blog CRUD Application with Form Request Validation & Pagination
Route::resource('blog', BlogPostController::class);

// ==========================================
// Week 4 Day 1: Protected Authenticated Routes
// ==========================================
Route::middleware('auth')->group(function () {
    // 1. Protected Route 1: Dashboard (also verified protected)
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('verified')->name('dashboard');

    // 2. Protected Route 2: Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 3. Protected Route 3: Account details and verification overview
    Route::get('/account', [AccountController::class, 'index'])->name('account');
});

// ==========================================
// Week 4 Day 5: Full-Stack Capstone Job Board
// ==========================================
// Public Job Listings & Details
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show')->whereNumber('job');

// Authenticated Job Management & Applications
Route::middleware('auth')->group(function () {
    Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit')->whereNumber('job');
    Route::match(['put', 'patch'], '/jobs/{job}', [JobController::class, 'update'])->name('jobs.update')->whereNumber('job');
    Route::delete('/jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy')->whereNumber('job');

    // Job Application Submission
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'store'])->name('jobs.apply')->whereNumber('job');

    // Candidate Application History
    Route::get('/my-applications', [ApplicationController::class, 'index'])->name('applications.index');
});

// ==========================================
// Week 4 Day 2: Frontend API Consumer Demonstration
// ==========================================
Route::get('/api-demo', function () {
    return view('api-demo');
})->name('api.demo');

// ==========================================
// Week 4 Day 4: Testing & Debugging Demonstration
// ==========================================
Route::get('/debug-demo', [DebuggingDemoController::class, 'index'])->name('debug.demo');

// Laravel Breeze Authentication Routes
require __DIR__.'/auth.php';
