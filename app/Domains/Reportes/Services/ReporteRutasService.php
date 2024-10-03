<?php

namespace App\Domains\Reportes\Services;

use App\Models\Ruta;

class ReporteRutasService
{
    // Obtener todas las rutas con paginación y los totales de viáticos y combustible
    public function obtenerTodasLasRutasPaginadas()
    {
        return Ruta::withSum('viaticos', 'importe')  // Incluye el total de viáticos
                   ->withSum('combustibles', 'importe')  // Incluye el total de combustibles
                   ->select('id', 'origen', 'destino', 'fecha_inicio', 'fecha_fin') // Campos de la ruta
                   ->paginate(10); // Devuelve las rutas paginadas
    }

    // Obtener rutas por un rango de fechas con paginación y los totales de viáticos y combustible
    public function obtenerRutasPorRangoDeFechas($fechaInicio, $fechaFin)
    {
        return Ruta::withSum('viaticos', 'importe')  // Incluye el total de viáticos
                   ->withSum('combustibles', 'importe')  // Incluye el total de combustibles
                   ->select('id', 'origen', 'destino', 'fecha_inicio', 'fecha_fin')  // Campos de la ruta
                   ->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])  // Filtro por fechas
                   ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])  // Filtro por fechas
                   ->paginate(10);  
    }

    // Obtener rutas por una fecha específica con paginación y los totales de viáticos y combustible
    public function obtenerRutasPorFecha($fecha)
    {
        return Ruta::withSum('viaticos', 'importe')  // Incluye el total de viáticos
                   ->withSum('combustibles', 'importe')  // Incluye el total de combustibles
                   ->select('id', 'origen', 'destino', 'fecha_inicio', 'fecha_fin')  // Campos de la ruta
                   ->where('fecha_inicio', $fecha)  // Filtro por fecha específica
                   ->orWhere('fecha_fin', $fecha)  // Filtro por fecha específica
                   ->paginate(10);  // Devuelve los resultados paginados
    }

    // Obtener una ruta específica por su ID con los totales de viáticos y combustible
    public function obtenerRutaPorId($rutaId)
    {
        return Ruta::withSum('viaticos', 'importe')  // Incluye el total de viáticos
                   ->withSum('combustibles', 'importe')  // Incluye el total de combustibles
                   ->select('id', 'origen', 'destino', 'fecha_inicio', 'fecha_fin')  // Campos de la ruta
                   ->where('id', $rutaId)
                   ->first();  // Devuelve una única ruta
    }
}
