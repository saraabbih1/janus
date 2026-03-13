<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\HabitLogController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('test' , function() {
    return response()->json();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/habits', [HabitController::class, 'index']);
    Route::post('/habits', [HabitController::class, 'store']);
    Route::get('/habits/{id}', [HabitController::class, 'show']);
    Route::put('/habits/{id}', [HabitController::class, 'update']);
    Route::delete('/habits/{id}', [HabitController::class, 'destroy']);

    Route::post('/habits/{id}/logs', [HabitLogController::class, 'store']);
    Route::get('/habits/{id}/logs', [HabitLogController::class, 'index']);
    Route::delete('/habits/{id}/logs/{logId}', [HabitLogController::class, 'destroy']);

    Route::get('/habits/{id}/stats', [StatsController::class, 'habitStats']);
    Route::get('/stats/overview', [StatsController::class, 'overview']);
});
