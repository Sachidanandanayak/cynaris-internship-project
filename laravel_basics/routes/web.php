<?php

use App\Http\Controllers\DatabaseDemoController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Cynaris Internship
|--------------------------------------------------------------------------
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
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about/{topic?}', [HomeController::class, 'about'])->name('about');
Route::get('/form', [FormController::class, 'index'])->name('form.index');
Route::post('/form', [FormController::class, 'submit'])->name('form.submit');
Route::post('/subscribe', [FormController::class, 'subscribe'])->name('newsletter.subscribe');

// Week 3 Day 3: Product Resource CRUD with Route Model Binding
Route::resource('products', ProductController::class);

// Week 3 Day 4: Database Integration & Eloquent Demonstration
Route::get('/database-demo', [DatabaseDemoController::class, 'index'])->name('database.demo');
