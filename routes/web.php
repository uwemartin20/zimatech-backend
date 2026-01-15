<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Admin\ProjectController as AdminProject;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\HomeController as AdminHome;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\TimeRecordController;
use App\Http\Controllers\Admin\TimeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Admin\Settings\MachineSettingsController;
use App\Http\Controllers\Admin\Settings\MachineController;

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index']);

// Project routes
Route::get('/projects/index', [ProjectController::class, 'index'])->name('projects');
Route::get('/projects/logs', [ProjectController::class, 'projectLogs'])->name('projects.logs');
Route::get('/parse-log', [ProjectController::class, 'parseLog'])->name('parse.log');

// Time Recording Routes
Route::prefix('time-records')->name('time-records.')->group(function() {
    Route::get('/', [TimeRecordController::class, 'index'])->name('list');
    Route::get('/create', [TimeRecordController::class, 'create'])->name('create');
    Route::get('/show/{id}', [TimeRecordController::class, 'show'])->name('show');
    Route::get('/change-request/{id}', [TimeRecordController::class, 'changeRequest'])->name('change-request');
    Route::post('/store-change-request/{id}', [TimeRecordController::class, 'storeChangeRequest'])->name('store-change-request');
    Route::post('/store', [TimeRecordController::class, 'store'])->name('store');
    Route::post('/end/{id}', [TimeRecordController::class, 'end'])->name('end');
    Route::post('/switch/{log}', [TimeRecordController::class, 'switch'])->name('switch');
});

// Language routes
Route::get('/language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');

// Admin Routes
Route::middleware(['auth', 'role:admin']) 
    ->prefix('admin') 
    ->name('admin.') 
    ->group(function () {
        Route::get('/dashboard', [AdminHome::class, 'index'])->name('dashboard');
        Route::get('/search', [AdminHome::class, 'search'])->name('search');
        Route::post('/notifications/read/{id}', [AdminHome::class, 'markRead'])->name('notifications.read');
        
        // Projects Routes
        Route::get('/projects', [AdminProject::class, 'index'])->name('projects');
        Route::get('/projects/create', [AdminProject::class, 'create'])->name('projects.create');
        Route::post('/projects/store', [AdminProject::class, 'store'])->name('projects.store');

        // Users Routes
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

        // Time Recording Routes
        Route::get('/time/logs', [TimeController::class, 'machineLogs'])->name('time.logs');
        Route::get('/parse-log', [TimeController::class, 'parseLog'])->name('parse.log');
        Route::get('/time/records', [TimeController::class, 'records'])->name('time.records');
        Route::get('/time/records/show/{id}', [TimeController::class, 'show'])->name('time.show');
        Route::get('/time/records/edit/{id}', [TimeController::class, 'editrecord'])->name('time.edit');
        Route::put('/time/records/update/{id?}', [TimeController::class, 'updateRecord'])->name('time.update');
        Route::delete('/time/records/delete/{id}', [TimeController::class, 'deleteRecord'])->name('time.delete');
        Route::get('/time/records/change-logs/{id}', [TimeController::class, 'changeTimeLogs'])->name('time.change-logs');
        Route::post('/time/store-changed-logs/{id}', [TimeController::class, 'storeAndApproveLogs'])->name('time.store-changed-logs');
        Route::post('/time/end/{id}', [TimeController::class, 'end'])->name('time.end');
        Route::post('/time/switch/{log}', [TimeController::class, 'switch'])->name('time.switch');
        Route::get('/time/compare', [TimeController::class, 'compare'])->name('time.compare');
        Route::get('/time/change', [TimeController::class, 'change'])->name('time.change');
        Route::post('/time/change/accept/{id}', [TimeController::class, 'acceptChange'])->name('time.change.accept');
        Route::post('/time/change/reject/{id}', [TimeController::class, 'rejectChange'])->name('time.change.reject');


        Route::prefix('settings')->name('settings.')->group(function () {

            // Machine Status
            Route::get('/machine-status', [MachineSettingsController::class, 'machineStatus'])
                ->name('machine-status');

            Route::get('/machine-status/show/{id?}', [MachineSettingsController::class, 'machineStatusShow'])
                ->name('machine-status.show');

            Route::post('/machine-status/update/{id?}', [MachineSettingsController::class, 'machineStatusUpdate'])
                ->name('machine-status.update');

            Route::patch('/machine-status/toggle/{id}', [MachineSettingsController::class, 'toggleMachineStatus'])
                ->name('machine-status.toggle');

            Route::delete('/machine-status/{id}', [MachineSettingsController::class, 'deleteMachineStatus'])
                ->name('machine-status.delete');

            // Machines
            Route::get('/machines', [MachineController::class, 'index'])
                ->name('machines');

            Route::get('/machines/show/{id?}', [MachineController::class, 'show'])
                ->name('machines.show');

            Route::post('/machines/update/{id?}', [MachineController::class, 'update'])
                ->name('machines.update');

            Route::patch('/machines/toggle/{id}', [MachineController::class, 'toggle'])
                ->name('machines.toggle');

            Route::delete('/machines/{id}', [MachineController::class, 'delete'])
                ->name('machines.delete');
        });
});