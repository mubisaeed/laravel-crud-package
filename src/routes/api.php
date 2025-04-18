<?php

use Illuminate\Support\Facades\Route;
use Mubeen\LaravelUserCrud\Http\Controllers\Api\AuthController;
use Mubeen\LaravelUserCrud\Http\Controllers\Api\UserController;

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    
    // Protected routes
    $authMiddleware = config('laravel-user-crud.auth_provider') === 'sanctum' ? 'auth:sanctum' : 
                      (config('laravel-user-crud.auth_provider') === 'passport' ? 'auth:api' : 'auth:api');
                      
    Route::middleware($authMiddleware)->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
    });
});

// User CRUD routes
Route::prefix(config('laravel-user-crud.route_prefix', 'api'))->middleware(config('laravel-user-crud.middleware'))->group(function () {
    Route::apiResource('users', UserController::class);
}); 