<?php

namespace App\Domains\Reportes\Services; 

use App\Models\Ruta;

class ReporteCombustibleService
{
    public function obtenerCombustiblePorRuta($rutaId)
    {
        $ruta = Ruta::with(['combustibles'])
            ->select('rutas.id', 'rutas.origen', 'rutas.destino','rutas.fecha_inicio','rutas.fecha_fin')
            ->withSum('combustibles', 'importe')
            ->where('id', $rutaId)
            ->first();

        return $ruta;
    }

    public function obtenerTotalCombustiblePorRuta($rutaId)
    {
        return Ruta::withSum('combustibles', 'importe')
            ->where('id', $rutaId)
            ->first()
            ->combustibles_sum_importe; 
    }
}
