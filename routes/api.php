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
use App\Http\Controllers\Factura\GuiaRemisionController;
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
        Route::get('conductores/eliminados', [ConductorController::class, 'deleted']);
        Route::apiResource('conductores', ConductorController::class)->parameters(['conductores' => 'id']);
        Route::patch('conductores/{id}/restore', [ConductorController::class, 'restore']);

        Route::get('camiones/deleted', [CamionController::class, 'deleted']);
        Route::apiResource('camiones', CamionController::class)->parameters(['camiones' => 'id']);
        Route::put('camiones/{id}/restore', [CamionController::class, 'restore']);

        Route::prefix('camiones')->group(function () {
            Route::get('/{camion}/seguros', [CamionSeguroController::class, 'index']);
            Route::post('/{camion}/seguros', [CamionSeguroController::class, 'store']);
            Route::get('/{camion}/seguros/{seguro}', [CamionSeguroController::class, 'show']);
            Route::put('/{camion}/seguros/{seguro}', [CamionSeguroController::class, 'update']);
            Route::delete('/{camion}/seguros/{seguro}', [CamionSeguroController::class, 'destroy']);
        });

        Route::apiResource('rutas', RutaController::class)->parameters(['rutas' => 'id']);
        Route::apiResource('viaticos', ViaticoController::class)->parameters(['viaticos' => 'id']);
        Route::apiResource('combustibles', CombustibleController::class)->parameters(['combustibles' => 'id']);

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

        Route::apiResource('rutasViaticos', RutaViaticosController::class)->parameters(['rutasViaticos' => 'id']);
    });

    Route::middleware('role:admin,comercial')->group(function () {
        Route::post('/factura/nuevaventa', [FacturaController::class, 'newventa']);
        Route::get('/facturas', [VentasController::class, 'listaFacturas']);
        Route::get('/reportes/ventas', [VentasController::class, 'reporteVentas']);
        Route::post('/facturas/{id}/reintentar', [VentasController::class, 'reintentarFactura']);
        Route::get('/facturas/{id}/duplicar-rechazada', [VentasController::class, 'duplicarRechazada']);
        Route::match(['get', 'post'], '/factura/pdf/{id?}', [FacturaPdfController::class, 'show']);
        Route::get('/factura/xml/{id?}', [FacturaPdfController::class, 'showXml']);
        Route::prefix('facturacion')->group(function () {
            Route::get('notas', [NotaCreditoController::class, 'index']);
            Route::post('notas', [NotaCreditoController::class, 'store']);
            Route::get('facturas-emitidas', [NotaCreditoController::class, 'facturasEmitidas']);
            Route::get('notas/pdf/{id?}', [NotaCreditoController::class, 'showPdf']);
            Route::get('notas/xml/{id?}', [NotaCreditoController::class, 'showXml']);
        });

        Route::apiResource('clientes', ClienteController::class);
        Route::apiResource('cotizaciones', CotizacionController::class);
        Route::apiResource('servicios', ServicioController::class);
        Route::apiResource('productos', ProductoController::class);
    });

    Route::middleware('role:admin,comercial,operaciones')->group(function () {
        Route::prefix('guias-remision')->group(function () {
            Route::get('/facturas', [GuiaRemisionController::class, 'facturasRelacionadas']);
            Route::get('/facturas/{id}', [GuiaRemisionController::class, 'facturaRelacionada']);
            Route::get('/remitentes', [GuiaRemisionController::class, 'remitentesRelacionados']);
            Route::get('/remitentes/{id}', [GuiaRemisionController::class, 'remitenteRelacionado']);
            Route::get('/clientes', [GuiaRemisionController::class, 'clientesRelacionados']);
            Route::get('/clientes/{id}', [GuiaRemisionController::class, 'clienteRelacionado']);
            Route::post('/clientes', [GuiaRemisionController::class, 'registrarClienteRelacionado']);
        });

        Route::apiResource('guias-remision', GuiaRemisionController::class)
            ->only(['index', 'store', 'show'])
            ->parameters(['guias-remision' => 'id']);
    });
});

Route::post('/NotasCredito/', [FacturaController::class, 'newNotas']);
Route::get('/listaNotacredito', [VentasController::class, 'listaNotas']);
Route::post('/NotasCredito/pdf', [FacturaController::class, 'pdf']);
