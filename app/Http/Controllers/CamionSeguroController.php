<?php

namespace App\Http\Controllers;

use App\Http\Requests\CamionSeguroRequest;
use App\Models\Camion;
use App\Models\CamionSeguro;
use Illuminate\Validation\ValidationException;

class CamionSeguroController extends Controller
{
    public function index($camionId)
    {
        $camion = Camion::find($camionId);

        if (!$camion) {
            throw ValidationException::withMessages([
                'camion' => ['Unidad no encontrada'],
            ]);
        }

        $seguros = CamionSeguro::where('camion_id', $camion->id)
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(fn ($seguro) => $this->appendEstado($seguro));

        return response()->json([
            'success' => true,
            'message' => 'Seguros de la unidad',
            'data' => $seguros,
        ]);
    }

    public function store(CamionSeguroRequest $request, $camionId)
    {
        $camion = Camion::find($camionId);

        if (!$camion) {
            throw ValidationException::withMessages([
                'camion' => ['Unidad no encontrada'],
            ]);
        }

        $data = $request->validated();
        $data['camion_id'] = $camion->id;
        $data['alertar_dias_antes'] = $data['alertar_dias_antes'] ?? 30;
        $data['activo'] = $data['activo'] ?? true;

        $seguro = CamionSeguro::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Seguro registrado',
            'data' => $this->appendEstado($seguro->fresh()),
        ], 201);
    }

    public function show($camionId, $seguroId)
    {
        $seguro = CamionSeguro::where('camion_id', $camionId)->find($seguroId);

        if (!$seguro) {
            throw ValidationException::withMessages([
                'seguro' => ['Seguro no encontrado'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Seguro encontrado',
            'data' => $this->appendEstado($seguro),
        ]);
    }

    public function update(CamionSeguroRequest $request, $camionId, $seguroId)
    {
        $seguro = CamionSeguro::where('camion_id', $camionId)->find($seguroId);

        if (!$seguro) {
            throw ValidationException::withMessages([
                'seguro' => ['Seguro no encontrado'],
            ]);
        }

        $data = $request->validated();
        $data['alertar_dias_antes'] = $data['alertar_dias_antes'] ?? $seguro->alertar_dias_antes ?? 30;
        $data['activo'] = $data['activo'] ?? $seguro->activo;

        $seguro->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Seguro actualizado',
            'data' => $this->appendEstado($seguro->fresh()),
        ]);
    }

    public function destroy($camionId, $seguroId)
    {
        $seguro = CamionSeguro::where('camion_id', $camionId)->find($seguroId);

        if (!$seguro) {
            throw ValidationException::withMessages([
                'seguro' => ['Seguro no encontrado'],
            ]);
        }

        $seguro->delete();

        return response()->json([
            'success' => true,
            'message' => 'Seguro eliminado',
        ]);
    }

    private function appendEstado(CamionSeguro $seguro): CamionSeguro
    {
        $diasRestantes = now()->startOfDay()->diffInDays($seguro->fecha_vencimiento->startOfDay(), false);

        $seguro->dias_restantes = $diasRestantes;
        $seguro->estado_alerta = $diasRestantes < 0
            ? 'vencido'
            : ($diasRestantes <= (int) $seguro->alertar_dias_antes ? 'por_vencer' : 'vigente');

        return $seguro;
    }
}
