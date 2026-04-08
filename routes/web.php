<?php

use App\Http\Controllers\ControladorCotizacion\CotizacionController;
use App\Http\Controllers\Factura\Controllers\FacturaPdfController;
use App\Http\Controllers\ReporteRutaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('Inicio.welcome');
})->name('');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/conductor', fn () => view('vista_conductor.index'));
Route::get('/camion', fn () => view('camion.index'));
Route::get('/rutas', fn () => view('ruta.index'));
Route::get('/viaticos', fn () => view('viaticos.index'));
Route::get('/combustible', fn () => view('combustible.index'));

Route::get('/reporte-ruta', fn () => view('reporte.index'));
Route::get('/reportes/rutas/{id}', function ($id) {
    return view('reporte.rutaCompleto', ['id' => $id]);
});
Route::get('/reportes/rutas/{id}/pdf', [ReporteRutaController::class, 'pdf']);
Route::get('/reporte/ruta-viaticos/{id}', function ($id) {
    return view('reporte.rutaViaticos', ['id' => $id]);
});
Route::get('/reporte/ruta-combustible/{id}', function ($id) {
    return view('reporte.rutaCombustible', ['id' => $id]);
});
Route::get('/reporte/ruta-completo', fn () => redirect('/reporte-ruta'));

Route::get('/producto', fn () => view('gestionProductos.producto.index'));
Route::get('/servicios', fn () => view('gestionProductos.servicio.index'));
Route::get('/venta', fn () => view('factura.index'));
Route::get('/nueva-venta', fn () => view('factura.registro'))->name('nueva-venta');
Route::get('/factura/pdf/{id}', [FacturaPdfController::class, 'show']);
Route::get('/factura/pdf/{id}/descargar', [FacturaPdfController::class, 'download']);

Route::get('/ruta/{id}/rutaviatico', fn ($id) => view('ruta.rutaviatico'));
Route::get('/ruta/{id}/rutacombustible', fn ($id) => view('ruta.rutacombustible'));
Route::get('/ruta/{id}/rutapeaje', fn ($id) => view('ruta.rutapeaje'));

Route::get('/login', fn () => view('auth.login'));
Route::get('/layouts', fn () => view('layouts.app'))->name('app');

Route::get('/clientes', fn () => view('vistaCliente.index'));
Route::get('/cotizaciones', fn () => view('vistaCotizacion.index'));
Route::get('/cotizaciones/registro', fn () => view('vistaCotizacion.registro'));
Route::get('/cotizaciones/pdf/{id}', [CotizacionController::class, 'pdf']);
Route::get('/usuarios', fn () => view('usuarios.index'));
Route::get('/notascredito', fn () => view('NotasCredito.index'));
