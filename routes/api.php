<?php

use App\Http\Controllers\ConductorController;
use App\Http\Controllers\CamionController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\ViaticoController;
use App\Http\Controllers\CombustibleController;
use App\Domains\Reportes\Controllers\ReporteController;


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


// Rutas API para Rutas
Route::get('rutas', [RutaController::class, 'index']);  // Obtener todas las rutas
Route::post('rutas', [RutaController::class, 'store']);  // Crear una nueva ruta
Route::get('rutas/{id}', [RutaController::class, 'show']);  // Mostrar una ruta específica
Route::put('rutas/{id}', [RutaController::class, 'update']);  // Actualizar una ruta específica
Route::delete('rutas/{id}', [RutaController::class, 'destroy']);  // Eliminar una ruta

//te aqui a habajo falta

// Rutas API para viaticos
Route::get('viaticos', [ViaticoController::class, 'index']); // Listar todos los viáticos no eliminados
Route::post('viaticos', [ViaticoController::class, 'store']); // Crear un nuevo viático
Route::get('viaticos/{id}', [ViaticoController::class, 'show']); // Mostrar un viático específico
Route::put('viaticos/{id}', [ViaticoController::class, 'update']); // Actualizar un viático
Route::delete('viaticos/{id}', [ViaticoController::class, 'destroy']); // Eliminar un viático (soft delete)



// Rutas API para combustible
Route::get('combustibles', [CombustibleController::class, 'index']); // Listar todos los registros de combustibles no eliminados
Route::post('combustibles', [CombustibleController::class, 'store']); // Crear un nuevo registro de combustible
Route::get('combustibles/{id}', [CombustibleController::class, 'show']); // Mostrar un registro específico de combustible
Route::put('combustibles/{id}', [CombustibleController::class, 'update']); // Actualizar un registro de combustible
Route::delete('combustibles/{id}', [CombustibleController::class, 'destroy']); // Eliminar un registro de combustible (soft delete)

// Reportes 

Route::get('reporte/viaticosRuta/{id}', [ReporteController::class, 'viaticosPorRuta']);
Route::get('reporte/combustibleRuta/{id}', [ReporteController::class, 'combustiblePorRuta']);
Route::get('reporte/completoRuta/{id}', [ReporteController::class, 'reporteCompletoPorRuta']);
// Ruta para obtener rutas, viáticos y combustible según los filtros (ID, fechas o todo)
Route::get('reporte/rutas-consumos', [ReporteController::class, 'rutasConsumos']);
