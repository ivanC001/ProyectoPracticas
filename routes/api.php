<?php

use App\Http\Controllers\ConductorController;
use App\Http\Controllers\CamionController;

use Illuminate\Support\Facades\Route;

// Rutas API para conductores
Route::get('/conductores', [ConductorController::class, 'index']);
Route::get('/conductores/eliminados', [ConductorController::class, 'deleted']);
Route::post('/conductores', [ConductorController::class, 'store']);
Route::get('/conductores/{id}', [ConductorController::class, 'show']);
Route::put('/conductores/{id}', [ConductorController::class, 'update']);
Route::delete('/conductores/{id}', [ConductorController::class, 'destroy']);
Route::patch('/conductores/{id}/restore', [ConductorController::class, 'restore']);



// Rutas API para Camiones
Route::get('camiones', [CamionController::class, 'index']); // Listar todos los camiones
Route::get('camiones/deleted', [CamionController::class, 'deleted']); // Listar los camiones eliminados (soft deleted)
Route::post('camiones', [CamionController::class, 'store']); // Crear un nuevo camión
Route::get('camiones/{id}', [CamionController::class, 'show']); // Mostrar un camión por su ID
Route::put('camiones/{id}', [CamionController::class, 'update']); // Actualizar un camión
Route::delete('camiones/{id}', [CamionController::class, 'destroy']); // Eliminar un camión (soft delete)
Route::put('camiones/{id}/restore', [CamionController::class, 'restore']); // Restaurar un camión eliminado
