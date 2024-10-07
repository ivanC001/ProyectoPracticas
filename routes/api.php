<?php

use App\Http\Controllers\ConductorController;
use App\Http\Controllers\CamionController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\ViaticoController;
use App\Http\Controllers\CombustibleController;
use App\Domains\Reportes\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

// Agrupar las rutas API con prefijo `api`
Route::prefix('api')->group(function () {

    /**
     * Rutas API para Conductores
     */
    Route::prefix('conductores')->group(function () {
        Route::get('/', [ConductorController::class, 'index']); // Listar conductores
        Route::get('/eliminados', [ConductorController::class, 'deleted']); // Listar conductores eliminados
        Route::post('/', [ConductorController::class, 'store']); // Crear conductor
        Route::get('/{id}', [ConductorController::class, 'show']); // Mostrar conductor por ID
        Route::put('/{id}', [ConductorController::class, 'update']); // Actualizar conductor
        Route::delete('/{id}', [ConductorController::class, 'destroy']); // Eliminar conductor
        Route::patch('/{id}/restore', [ConductorController::class, 'restore']); // Restaurar conductor eliminado
    });

    /**
     * Rutas API para Camiones
     */
    Route::prefix('camiones')->group(function () {
        Route::get('/', [CamionController::class, 'index']); // Listar todos los camiones
        Route::get('/deleted', [CamionController::class, 'deleted']); // Listar camiones eliminados
        Route::post('/', [CamionController::class, 'store']); // Crear un nuevo camión
        Route::get('/{id}', [CamionController::class, 'show']); // Mostrar camión por ID
        Route::put('/{id}', [CamionController::class, 'update']); // Actualizar un camión
        Route::delete('/{id}', [CamionController::class, 'destroy']); // Eliminar un camión
        Route::put('/{id}/restore', [CamionController::class, 'restore']); // Restaurar camión eliminado
    });

    /**
     * Rutas API para Rutas
     */
    Route::prefix('rutas')->group(function () {
        Route::get('/', [RutaController::class, 'index']); // Listar todas las rutas
        Route::post('/', [RutaController::class, 'store']); // Crear una nueva ruta
        Route::get('/{id}', [RutaController::class, 'show']); // Mostrar ruta por ID
        Route::put('/{id}', [RutaController::class, 'update']); // Actualizar una ruta específica
        Route::delete('/{id}', [RutaController::class, 'destroy']); // Eliminar una ruta
    });

    /**
     * Rutas API para Viáticos
     */
    Route::prefix('viaticos')->group(function () {
        Route::get('/', [ViaticoController::class, 'index']); // Listar todos los viáticos
        Route::post('/', [ViaticoController::class, 'store']); // Crear un nuevo viático
        Route::get('/{id}', [ViaticoController::class, 'show']); // Mostrar un viático específico
        Route::put('/{id}', [ViaticoController::class, 'update']); // Actualizar un viático
        Route::delete('/{id}', [ViaticoController::class, 'destroy']); // Eliminar un viático
    });

    /**
     * Rutas API para Combustibles
     */
    Route::prefix('combustibles')->group(function () {
        Route::get('/', [CombustibleController::class, 'index']); // Listar todos los combustibles
        Route::post('/', [CombustibleController::class, 'store']); // Crear un nuevo registro de combustible
        Route::get('/{id}', [CombustibleController::class, 'show']); // Mostrar un combustible por ID
        Route::put('/{id}', [CombustibleController::class, 'update']); // Actualizar un combustible
        Route::delete('/{id}', [CombustibleController::class, 'destroy']); // Eliminar un combustible
    });

    /**
     * Rutas API para Reportes
     */
    Route::prefix('reporte')->group(function () {
        Route::get('/rutas-consumos', [ReporteController::class, 'rutasConsumos']); // Rutas y consumos
        Route::get('/viaticosRuta/{id}', [ReporteController::class, 'viaticosPorRuta']); // Viáticos por ruta
        Route::get('/combustibleRuta/{id}', [ReporteController::class, 'combustiblePorRuta']); // Combustible por ruta
        Route::get('/completoRuta/{id}', [ReporteController::class, 'reporteCompletoPorRuta']); // Reporte completo de la ruta
        //Route::get('reporte/rutas-consumos', [ReporteController::class, 'rutasConsumos']);  // todas las rutas y consumo total

    });
    // Ruta para obtener rutas, viáticos y combustible según los filtros (ID, fechas o todo)
    Route::get('reporte/rutas-consumos?id=1', [ReporteController::class, 'rutasConsumos']); // de un id especifico
    Route::get('reporte/rutas-consumos?fecha_inicio=2024-09-26', [ReporteController::class, 'rutasConsumos']); //todos los de una fecha
    Route::get('reporte/rutas-consumos?fecha_inicio=2024-09-26&fecha_fin=2024-09-27', [ReporteController::class, 'rutasConsumos']); // en rango de fechas
    Route::get('reporte/rutas-consumos?exportar=1', [ReporteController::class, 'rutasConsumos']); // en cualquier de las anteriores si es necesario con agreagr el exportar exporta en excel


});
