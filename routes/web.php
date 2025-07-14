<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Route::get('/logout', [HomeController::class, 'logout'])->name('logout');

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/chat', [ChatController::class, 'chatPage'])->name('chat.page')->middleware('auth');
Route::get('/chat/messages/{user}', [ChatController::class, 'getMessages'])->middleware('auth');
Route::post('/chat/send', [ChatController::class, 'send'])->middleware('auth');





