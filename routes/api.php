<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HabitController;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/habits', [HabitController::class, 'index']);
    Route::post('/habits', [HabitController::class, 'store']);
    Route::put('/habits/{id}', [HabitController::class, 'update']);
    Route::delete('/habits/{id}', [HabitController::class, 'destroy']);

});