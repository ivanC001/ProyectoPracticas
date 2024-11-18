<?php

use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return view('dashboard');
});
Route::get('/conductor', function () {
    return view('vista_conductor.index');
});
Route::get('/camion', function () {
    return view('camion.index');
});
Route::get('/rutas', function () {
    return view('ruta.index');
});
Route::get('/viaticos', function () {
    return view('viaticos.index');
});
Route::get('/combustible', function () {
    return view('combustible.index');
});

///reportes
 /////reporte general
Route::get('/reporte-ruta', function () {
    return view('reporte.index');
});
// reporte ruta y lista de viáticos
Route::get('/reporte/ruta-viaticos/{id}', function ($id) {
    return view('reporte.rutaViaticos', ['id' => $id]);
});

// reporte ruta y lista combustible
Route::get('/reporte/ruta-combustible/{id}', function ($id) {
    return view('reporte.rutaCombustible', ['id' => $id]);
});
// reporte completo de ruta

Route::get('/reporte/ruta-completo', function () {
    return view('reporte.rutaCompleto');
});
Route::get('/producto', function () {
    return view('gestionProductos.producto.index');
});
Route::get('/venta', function () {
    return view('factura.index');
});
Route::get('/nueva-venta', function () {
    return view('factura.registro');
})->name('nueva-venta');
