<?php

use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);

Route::post('/feedback/store', [FeedbackController::class, 'store']);
