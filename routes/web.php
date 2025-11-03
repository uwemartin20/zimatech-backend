<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AdminProjectController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index']);
Route::get('/projects', [ProjectController::class, 'projectLogs'])->name('projects');
Route::get('/parse-log', [ProjectController::class, 'parseLog'])->name('parse.log');

// Admin Routes
Route::middleware(['auth', 'role:admin']) 
    ->prefix('admin') 
    ->name('admin.') 
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/search', [AdminController::class, 'search'])->name('search');
        Route::get('/projects', [AdminProjectController::class, 'index'])->name('projects');
        Route::get('/projects/logs', [AdminProjectController::class, 'projectLogs'])->name('projects.logs');
        Route::get('/projects/create', [AdminProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects/store', [AdminProjectController::class, 'store'])->name('projects.store');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/profile', [AdminUserController::class, 'profile'])->name('users.profile');
    
});