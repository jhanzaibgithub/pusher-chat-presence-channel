<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Auth;


Route::get('/chat', [ChatController::class, 'chatPage'])->middleware('auth');
Route::get('/chat/messages/{user}', [ChatController::class, 'getMessages'])->middleware('auth');
Route::post('/chat/send', [ChatController::class, 'send'])->middleware('auth');

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');
