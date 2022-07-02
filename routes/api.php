<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\QuoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register-user', [AuthController::class, 'create'])->name('signup');
Route::post('/login', [AuthController::class, 'login'])->name('signin');
Route::post('/logout',[AuthController::class,'logout'])->name('logout');
Route::get('/all-movies', [MovieController::class, 'show'])->name('all.movies');
Route::get('/all-quotes',[QuoteController::class,'index'])->name('all.quotes');