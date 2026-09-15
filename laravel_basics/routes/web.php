<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Cynaris Internship Week 3 Day 2 (Laravel Basics)
|--------------------------------------------------------------------------
| Exactly 5 demonstration routes:
| 1. GET  /               -> HomeController@index       (name: home)
| 2. GET  /about/{topic?} -> HomeController@about       (name: about)
| 3. GET  /form           -> FormController@index       (name: form.index)
| 4. POST /form           -> FormController@submit      (name: form.submit)
| 5. POST /subscribe      -> FormController@subscribe   (name: newsletter.subscribe)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about/{topic?}', [HomeController::class, 'about'])->name('about');
Route::get('/form', [FormController::class, 'index'])->name('form.index');
Route::post('/form', [FormController::class, 'submit'])->name('form.submit');
Route::post('/subscribe', [FormController::class, 'subscribe'])->name('newsletter.subscribe');
