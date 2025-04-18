<?php

use Illuminate\Support\Facades\Route;
use Mubeen\LaravelUserCrud\Http\Controllers\UserController;
use Mubeen\LaravelUserCrud\Http\Controllers\Auth\LoginController;
use Mubeen\LaravelUserCrud\Http\Controllers\Auth\RegisterController;

// Authentication Routes
Route::middleware('web')->group(function () {
    // Login Routes
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Registration Routes
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

// User CRUD Routes
Route::middleware(['web', 'auth'])->prefix(config('laravel-user-crud.route_prefix', 'admin'))->group(function () {
    Route::resource('users', UserController::class);
}); 