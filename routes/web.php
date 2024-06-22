<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//route resource
Route::resource('/posts', \App\Http\Controllers\PostController::class);
Route::resource('/install', \App\Http\Controllers\InstallController::class);
Route::resource('/redir', \App\Http\Controllers\RedirController::class);
Route::resource('/product/push', \App\Http\Controllers\Api\ProductController::class);

Route::get('/', function () {
    return view('index');
  });


