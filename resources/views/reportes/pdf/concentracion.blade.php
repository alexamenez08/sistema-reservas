<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de concentración de eventos por periodo</title>
    @include('reportes.pdf._estilos')
</head>
<body>
    @php
        $subtituloConcentracion = $desde->format('d/m/Y') . ' al ' . $hasta->format('d/m/Y');
    @endphp
    @include('reportes.pdf._cabecera', ['titulo' => 'Concentración de eventos por periodo', 'subtitulo' => $subtituloConcentracion])

    <h2>Resumen</h2>
    <table class="resumen">
        <tr>
            <td><div class="muted">Total de eventos</div><div class="n">{{ $total }}</div></td>
            <td><div class="muted">Mes de mayor actividad</div><div class="n">{{ $total > 0 ? $mayor['etiqueta'] : '—' }}</div>{{ $total > 0 ? $mayor['total'] . ' evento(s)' : '' }}</td>
            <td><div class="muted">Mes de menor actividad</div><div class="n">{{ $total > 0 ? $menor['etiqueta'] : '—' }}</div>{{ $total > 0 ? $menor['total'] . ' evento(s)' : '' }}</td>
        </tr>
    </table>

    <h2>Eventos por mes</h2>
    @php
        $itemsMeses = collect($meses)->map(fn ($m) => ['etiqueta' => $m['etiqueta'], 'valor' => $m['total'], 'texto' => $m['total']])->all();
    @endphp
    @include('reportes.pdf._barras', ['items' => $itemsMeses])

    <h2>Listado</h2>
    <table class="tabla">
        <thead><tr><th>Mes</th><th class="right">Eventos</th></tr></thead>
        <tbody>
            @foreach($meses as $m)
                <tr><td>{{ $m['etiqueta'] }}</td><td class="right">{{ $m['total'] }}</td></tr>
            @endforeach
            <tr class="total"><td>TOTAL</td><td class="right">{{ $total }}</td></tr>
        </tbody>
    </table>

    <div class="pie">Solo cuentan reservaciones con estado confirmada o finalizada. · {{ $negocio }}</div>
</body>
</html>