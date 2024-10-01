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
