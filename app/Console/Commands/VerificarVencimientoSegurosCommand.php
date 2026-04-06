<?php

namespace App\Console\Commands;

use App\Mail\SegurosPorVencerMail;
use App\Models\CamionSeguro;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class VerificarVencimientoSegurosCommand extends Command
{
    protected $signature = 'seguros:verificar-vencimientos';

    protected $description = 'Envia alertas por correo para seguros vencidos o proximos a vencer';

    public function handle(): int
    {
        $destinatarios = collect(config('empresa.alerta_emails', []))
            ->filter()
            ->values();

        if ($destinatarios->isEmpty()) {
            $this->warn('No hay correos configurados para alertas de seguros.');
            return self::SUCCESS;
        }

        $hoy = now()->startOfDay();

        $seguros = CamionSeguro::with('camion')
            ->where('activo', true)
            ->get()
            ->filter(function ($seguro) use ($hoy) {
                $diasRestantes = $hoy->diffInDays($seguro->fecha_vencimiento->copy()->startOfDay(), false);
                $seguro->dias_restantes = $diasRestantes;

                if ($diasRestantes > (int) $seguro->alertar_dias_antes) {
                    return false;
                }

                if ($seguro->ultimo_aviso_enviado_at && $seguro->ultimo_aviso_enviado_at->isSameDay($hoy)) {
                    return false;
                }

                return true;
            })
            ->values();

        if ($seguros->isEmpty()) {
            $this->info('No hay seguros por alertar hoy.');
            return self::SUCCESS;
        }

        foreach ($destinatarios as $correo) {
            Mail::to($correo)->send(new SegurosPorVencerMail($seguros));
        }

        CamionSeguro::whereIn('id', $seguros->pluck('id'))
            ->update(['ultimo_aviso_enviado_at' => now()]);

        $this->info('Alertas enviadas: ' . $seguros->count());

        return self::SUCCESS;
    }
}
