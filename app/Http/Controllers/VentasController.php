<?php

namespace App\Http\Controllers;

use App\Models\VentasModel\Venta;
use Illuminate\Http\Request;

class VentasController extends Controller
{
    public function listaFacturas(Request $request)
    {
        $query = Venta::query();

        // 🔍 BUSCADOR
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('numero_comprobante', 'like', "%$search%")
                  ->orWhere('nombre_cliente', 'like', "%$search%")
                  ->orWhere('numero_documento_cliente', 'like', "%$search%");
            });
        }

        // 📌 FILTRO ESTADO
        if ($request->filled('estado')) {
            $query->where('estado_envio', $request->estado);
        }

        // 📅 FILTRO FECHA
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
        }

        // 🔽 CONSULTA FINAL
        $facturas = $query->orderBy('id', 'desc')
            ->select(
                'id',
                'numero_comprobante',
                'tipo_documento',
                'serie',
                'correlativo',
                'nombre_cliente',
                'numero_documento_cliente',
                'fecha_emision',
                'moneda',
                'total_venta',
                'estado_envio'
            )
            ->paginate(10);

        return response()->json($facturas);
    }
    
}