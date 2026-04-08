<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CamionController;
use App\Http\Controllers\CamionSeguroController;
use App\Http\Controllers\CombustibleController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\ControladorClientes\ClienteController;
use App\Http\Controllers\ControladorCotizacion\CotizacionController;
use App\Http\Controllers\Factura\Controllers\FacturaController;
use App\Http\Controllers\Factura\Controllers\FacturaPdfController;
use App\Http\Controllers\ProductosController\ProductoController;
use App\Http\Controllers\ProductosController\ServicioController;
use App\Http\Controllers\ReporteRutaController;
use App\Http\Controllers\RutasController\RutaCombustibleController;
use App\Http\Controllers\RutasController\RutaController;
use App\Http\Controllers\RutasController\RutaPeajeController;
use App\Http\Controllers\RutasController\RutaViaticosController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\ViaticoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Factura\NotaCreditoController;

Route::post('register', [RegisterController::class, 'store'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:api'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);

    Route::middleware('role:admin')->apiResource('usuarios', UserController::class);

    Route::middleware('role:admin,operaciones')->group(function () {
        Route::prefix('conductores')->group(function () {
            Route::get('/', [ConductorController::class, 'index']);
            Route::get('/eliminados', [ConductorController::class, 'deleted']);
            Route::post('/', [ConductorController::class, 'store']);
            Route::get('/{id}', [ConductorController::class, 'show']);
            Route::put('/{id}', [ConductorController::class, 'update']);
            Route::delete('/{id}', [ConductorController::class, 'destroy']);
            Route::patch('/{id}/restore', [ConductorController::class, 'restore']);
        });

        Route::prefix('camiones')->group(function () {
            Route::get('/', [CamionController::class, 'index']);
            Route::get('/deleted', [CamionController::class, 'deleted']);
            Route::post('/', [CamionController::class, 'store']);
            Route::get('/{id}', [CamionController::class, 'show']);
            Route::put('/{id}', [CamionController::class, 'update']);
            Route::delete('/{id}', [CamionController::class, 'destroy']);
            Route::put('/{id}/restore', [CamionController::class, 'restore']);
            Route::get('/{camion}/seguros', [CamionSeguroController::class, 'index']);
            Route::post('/{camion}/seguros', [CamionSeguroController::class, 'store']);
            Route::get('/{camion}/seguros/{seguro}', [CamionSeguroController::class, 'show']);
            Route::put('/{camion}/seguros/{seguro}', [CamionSeguroController::class, 'update']);
            Route::delete('/{camion}/seguros/{seguro}', [CamionSeguroController::class, 'destroy']);
        });

        Route::prefix('rutas')->group(function () {
            Route::get('/', [RutaController::class, 'index']);
            Route::post('/', [RutaController::class, 'store']);
            Route::get('/{id}', [RutaController::class, 'show']);
            Route::put('/{id}', [RutaController::class, 'update']);
            Route::delete('/{id}', [RutaController::class, 'destroy']);
        });

        Route::prefix('viaticos')->group(function () {
            Route::get('/', [ViaticoController::class, 'index']);
            Route::post('/', [ViaticoController::class, 'store']);
            Route::get('/{id}', [ViaticoController::class, 'show']);
            Route::put('/{id}', [ViaticoController::class, 'update']);
            Route::delete('/{id}', [ViaticoController::class, 'destroy']);
        });

        Route::prefix('combustibles')->group(function () {
            Route::get('/', [CombustibleController::class, 'index']);
            Route::post('/', [CombustibleController::class, 'store']);
            Route::get('/{id}', [CombustibleController::class, 'show']);
            Route::put('/{id}', [CombustibleController::class, 'update']);
            Route::delete('/{id}', [CombustibleController::class, 'destroy']);
        });

        Route::get('/rutas/{ruta_id}/combustibles', [RutaCombustibleController::class, 'index']);
        Route::post('/rutas/{ruta_id}/combustibles', [RutaCombustibleController::class, 'store']);
        Route::get('/rutas/{ruta_id}/combustibles/{id}', [RutaCombustibleController::class, 'show']);
        Route::put('/rutas/{ruta_id}/combustibles/{id}', [RutaCombustibleController::class, 'update']);
        Route::delete('/rutas/{ruta_id}/combustibles/{id}', [RutaCombustibleController::class, 'destroy']);

        Route::prefix('rutas/{ruta}')->group(function () {
            Route::get('/peajes', [RutaPeajeController::class, 'index']);
            Route::post('/peajes', [RutaPeajeController::class, 'store']);
            Route::get('/peajes/{id}', [RutaPeajeController::class, 'show']);
            Route::put('/peajes/{id}', [RutaPeajeController::class, 'update']);
            Route::delete('/peajes/{id}', [RutaPeajeController::class, 'destroy']);
        });

        Route::prefix('reportes')->group(function () {
            Route::get('/rutas', [ReporteRutaController::class, 'index']);
            Route::get('/rutas/{id}', [ReporteRutaController::class, 'show']);
        });

        Route::get('/rutasViaticos', [RutaViaticosController::class, 'index']);
        Route::post('/rutasViaticos', [RutaViaticosController::class, 'store']);
        Route::get('/rutasViaticos/{id}', [RutaViaticosController::class, 'show']);
        Route::put('/rutasViaticos/{id}', [RutaViaticosController::class, 'update']);
        Route::delete('/rutasViaticos/{id}', [RutaViaticosController::class, 'destroy']);
    });

    Route::middleware('role:admin,comercial')->group(function () {
        Route::post('/factura/nuevaventa', [FacturaController::class, 'newventa']);
        Route::get('/facturas', [VentasController::class, 'listaFacturas']);
        Route::match(['get', 'post'], '/factura/pdf/{id?}', [FacturaPdfController::class, 'show']);

        Route::apiResource('clientes', ClienteController::class);
        Route::apiResource('cotizaciones', CotizacionController::class);
        Route::apiResource('servicios', ServicioController::class);
        Route::apiResource('productos', ProductoController::class);
    });
});

Route::post('/NotasCredito/', [FacturaController::class, 'newNotas']);
Route::get('/listaNotacredito', [VentasController::class, 'listaNotas']);
Route::post('/NotasCredito/pdf', [FacturaController::class, 'pdf']);

Route::prefix('facturacion')->group(function () {
    Route::get('notas', [NotaCreditoController::class, 'index']);
    Route::post('notas', [NotaCreditoController::class, 'store']);
});
