<?php

use App\Http\Controllers\ConductorController;
use Illuminate\Support\Facades\Route;


Route::get('/conductores', [ConductorController::class, 'index']);
Route::post('/conductores', [ConductorController::class, 'store']);
Route::get('/conductores/{id}', [ConductorController::class, 'show']);
Route::put('/conductores/{id}', [ConductorController::class, 'update']);
Route::delete('/conductores/{id}', [ConductorController::class, 'destroy']);
Route::patch('/conductores/{id}/restore', [ConductorController::class, 'restore']);