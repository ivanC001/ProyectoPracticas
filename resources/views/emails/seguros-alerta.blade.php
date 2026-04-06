<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alerta de seguros</title>
</head>
<body style="font-family: Arial, sans-serif; color:#1f2937;">
    <h2 style="margin-bottom: 8px;">Alerta de vencimiento de seguros</h2>
    <p style="margin-top: 0;">
        Se encontraron seguros vencidos o proximos a vencer en la flota.
    </p>

    <table cellpadding="8" cellspacing="0" border="1" width="100%" style="border-collapse: collapse; font-size: 14px;">
        <thead style="background: #e5eefb;">
            <tr>
                <th align="left">Unidad</th>
                <th align="left">Seguro</th>
                <th align="left">Aseguradora</th>
                <th align="left">Poliza</th>
                <th align="left">Vence</th>
                <th align="left">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($seguros as $seguro)
                <tr>
                    <td>{{ $seguro->camion?->placa_tracto }} / {{ $seguro->camion?->placa_carreto }}</td>
                    <td>{{ $seguro->tipo_seguro }}</td>
                    <td>{{ $seguro->aseguradora ?: '-' }}</td>
                    <td>{{ $seguro->numero_poliza ?: '-' }}</td>
                    <td>{{ optional($seguro->fecha_vencimiento)->format('Y-m-d') }}</td>
                    <td>
                        @if(($seguro->dias_restantes ?? 0) < 0)
                            Vencido hace {{ abs((int) $seguro->dias_restantes) }} dia(s)
                        @else
                            Vence en {{ (int) $seguro->dias_restantes }} dia(s)
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 16px;">
        Revisa el modulo de tractos y trailers para actualizar la informacion o renovar los seguros.
    </p>
</body>
</html>
