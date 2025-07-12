<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// routes/web.php
Route::get('/chat/{user}', [ChatController::class, 'index'])->middleware('auth');
Route::post('/chat/send', [ChatController::class, 'send'])->middleware('auth');
