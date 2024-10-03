<?php

namespace App\Domains\Reportes\Services; 

use App\Models\Ruta;

class ReporteViaticosService
{
    public function obtenerViaticosPorRuta($rutaId)
    {
        $ruta = Ruta::with(['viaticos'])
            ->select('rutas.id', 'rutas.origen', 'rutas.destino','rutas.fecha_inicio','rutas.fecha_fin')
            ->withSum('viaticos', 'importe')
            ->where('id', $rutaId)
            ->first();

        return $ruta;
    }

    // Obtener el total de viáticos por ruta
    public function obtenerTotalViaticosPorRuta($rutaId)
    {
        return Ruta::withSum('viaticos', 'importe')
            ->where('id', $rutaId)
            ->first()
            ->viaticos_sum_importe; // Devuelve el total de viáticos
    }
}
