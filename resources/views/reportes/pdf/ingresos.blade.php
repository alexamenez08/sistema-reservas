<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de ingresos y saldos pendientes</title>
    @include('reportes.pdf._estilos')
</head>
<body>
    @php
        $subtituloIngresos = 'Eventos del ' . $desde->format('d/m/Y') . ' al ' . $hasta->format('d/m/Y');
    @endphp
    @include('reportes.pdf._cabecera', ['titulo' => 'Ingresos y saldos pendientes', 'subtitulo' => $subtituloIngresos])

    <h2>Resumen</h2>
    <table class="resumen">
        <tr>
            <td><div class="muted">Total de los eventos</div><div class="n">${{ number_format($resumen['total'], 2) }}</div></td>
            <td><div class="muted">Cobrado</div><div class="n">${{ number_format($resumen['pagado'], 2) }}</div></td>
            <td><div class="muted">Por cobrar</div><div class="n">${{ number_format($resumen['saldo'], 2) }}</div></td>
            <td><div class="muted">Saldo vencido ({{ $resumen['vencidas'] }})</div><div class="n">${{ number_format($resumen['monto_vencido'], 2) }}</div></td>
        </tr>
    </table>

    <h2>Detalle por evento</h2>
    <table class="tabla">
        <thead>
            <tr>
                <th>Fecha</th><th>Cliente</th><th>Locación</th>
                <th class="right">Total</th><th class="right">Pagado</th><th class="right">Saldo</th><th>Estado de pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filas as $f)
                <tr>
                    <td>{{ $f->fecha->format('d/m/Y') }}</td>
                    <td>{{ $f->cliente }}</td>
                    <td>{{ $f->locacion }}</td>
                    <td class="right">${{ number_format($f->total, 2) }}</td>
                    <td class="right">${{ number_format($f->pagado, 2) }}</td>
                    <td class="right"><strong>${{ number_format($f->saldo, 2) }}</strong></td>
                    <td>{{ $f->estado }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted" style="text-align:center; padding:14px;">No hay eventos en el rango seleccionado.</td></tr>
            @endforelse
            <tr class="total">
                <td colspan="3">TOTALES</td>
                <td class="right">${{ number_format($resumen['total'], 2) }}</td>
                <td class="right">${{ number_format($resumen['pagado'], 2) }}</td>
                <td class="right">${{ number_format($resumen['saldo'], 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="pie">"Vencido": el evento ya se realizó y aún hay saldo por pagar. No incluye reservaciones canceladas. · {{ $negocio }}</div>
</body>
</html>