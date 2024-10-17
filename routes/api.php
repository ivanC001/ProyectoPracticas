<?php

use App\Http\Controllers\ConductorController;
use App\Http\Controllers\CamionController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\ViaticoController;
use App\Http\Controllers\CombustibleController;
use App\Domains\Reportes\Controllers\ReporteController;
use App\Domains\Inventarios\Controllers\ProductoController;

use Illuminate\Support\Facades\Route;

// Rutas API para conductores
Route::prefix('conductores')->group(function () {
    Route::get('/', [ConductorController::class, 'index']);
    Route::get('/eliminados', [ConductorController::class, 'deleted']);
    Route::post('/', [ConductorController::class, 'store']);
    Route::get('/{id}', [ConductorController::class, 'show']);
    Route::put('/{id}', [ConductorController::class, 'update']);
    Route::delete('/{id}', [ConductorController::class, 'destroy']);
    Route::patch('/{id}/restore', [ConductorController::class, 'restore']);
});

// Rutas API para camiones
Route::prefix('camiones')->group(function () {
    Route::get('/', [CamionController::class, 'index']);
    Route::get('/deleted', [CamionController::class, 'deleted']);
    Route::post('/', [CamionController::class, 'store']);
    Route::get('/{id}', [CamionController::class, 'show']);
    Route::put('/{id}', [CamionController::class, 'update']);
    Route::delete('/{id}', [CamionController::class, 'destroy']);
    Route::put('/{id}/restore', [CamionController::class, 'restore']);
});

// Rutas API para rutas
Route::prefix('rutas')->group(function () {
    Route::get('/', [RutaController::class, 'index']);
    Route::post('/', [RutaController::class, 'store']);
    Route::get('/{id}', [RutaController::class, 'show']);
    Route::put('/{id}', [RutaController::class, 'update']);
    Route::delete('/{id}', [RutaController::class, 'destroy']);
});

// Rutas API para viáticos
Route::prefix('viaticos')->group(function () {
    Route::get('/', [ViaticoController::class, 'index']);
    Route::post('/', [ViaticoController::class, 'store']);
    Route::get('/{id}', [ViaticoController::class, 'show']);
    Route::put('/{id}', [ViaticoController::class, 'update']);
    Route::delete('/{id}', [ViaticoController::class, 'destroy']);
});

// Rutas API para combustible
Route::prefix('combustibles')->group(function () {
    Route::get('/', [CombustibleController::class, 'index']);
    Route::post('/', [CombustibleController::class, 'store']);
    Route::get('/{id}', [CombustibleController::class, 'show']);
    Route::put('/{id}', [CombustibleController::class, 'update']);
    Route::delete('/{id}', [CombustibleController::class, 'destroy']);
});

// Reportes 

// Rutas API para reportes
Route::prefix('reporte')->group(function () {
    Route::get('/viaticosRuta/{id}', [ReporteController::class, 'viaticosPorRuta']);
    Route::get('/combustibleRuta/{id}', [ReporteController::class, 'combustiblePorRuta']);
    Route::get('/completoRuta/{id}', [ReporteController::class, 'reporteCompletoPorRuta']);
    //Route::get('/rutas-consumos', [ReporteController::class, 'rutasConsumos']);
});

// Ruta para obtener rutas, viáticos y combustible según los filtros (ID, fechas o todo)
Route::get('reporte/rutas-consumos', [ReporteController::class, 'rutasConsumos']);  // todas las rutas y consumo total
Route::get('reporte/rutas-consumos?id=1', [ReporteController::class, 'rutasConsumos']); // de un id especifico
Route::get('reporte/rutas-consumos?fecha_inicio=2024-09-26', [ReporteController::class, 'rutasConsumos']); //todos los de una fecha
Route::get('reporte/rutas-consumos?fecha_inicio=2024-09-26&fecha_fin=2024-09-27', [ReporteController::class, 'rutasConsumos']); // en rango de fechas
Route::get('reporte/rutas-consumos?exportar=1', [ReporteController::class, 'rutasConsumos']); // en cualquier de las anteriores si es necesario con agreagr el exportar exporta en excel



Route::prefix('productos')->group(function () {
    Route::get('/', [ProductoController::class, 'index']);   // Obtener todos los productos
    Route::post('/', [ProductoController::class, 'store']);  // Crear un nuevo producto
    Route::get('/{id}', [ProductoController::class, 'show']); // Mostrar un producto específico
    Route::put('/{id}', [ProductoController::class, 'update']); // Actualizar un producto
    Route::delete('/{id}', [ProductoController::class, 'destroy']); // Eliminar un producto
});


//facturacion ::


use App\Domains\Comprobantes\Controllers\ComprobanteController;

Route::post('/comprobantes/create', [ComprobanteController::class, 'create']);
