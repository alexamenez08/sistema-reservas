<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de ocupación de locaciones</title>
    @include('reportes.pdf._estilos')
</head>
<body>
    @php
        $subtituloOcupacion = ($locacionNombre ?? 'Todas las locaciones') . ' · ' . $desde->format('d/m/Y') . ' al ' . $hasta->format('d/m/Y');
    @endphp
    @include('reportes.pdf._cabecera', ['titulo' => 'Ocupación de locaciones', 'subtitulo' => $subtituloOcupacion])

    <h2>Reservaciones por locación ({{ $totalReservas }} en total, sin canceladas)</h2>
    @if($totalReservas > 0)
        @php
            $itemsLocaciones = $filas->map(fn ($f) => ['etiqueta' => $f->nombre, 'valor' => $f->reservas, 'texto' => $f->reservas])->all();
        @endphp
        @include('reportes.pdf._barras', ['items' => $itemsLocaciones])
    @else
        <p class="muted">Sin reservaciones en este periodo.</p>
    @endif

    <h2>Detalle</h2>
    <table class="tabla">
        <thead>
            <tr>
                <th>Locación</th><th class="right">Reservaciones</th><th class="right">Días ocupados</th>
                <th class="right">Ocupación</th><th class="right">Invitados</th><th class="right">Valor reservado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($filas as $f)
                <tr>
                    <td>{{ $f->nombre }}</td>
                    <td class="right">{{ $f->reservas }}</td>
                    <td class="right">{{ $f->dias }}</td>
                    <td class="right">{{ $f->ocupacion }}%</td>
                    <td class="right">{{ number_format($f->invitados) }}</td>
                    <td class="right">${{ number_format($f->monto, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Temporadas: reservaciones por mes</h2>
    @php
        $itemsMeses = collect($meses)->map(fn ($m) => ['etiqueta' => $m['etiqueta'], 'valor' => $m['total'], 'texto' => $m['total']])->all();
    @endphp
    @include('reportes.pdf._barras', ['items' => $itemsMeses])

    <div class="pie">"Ocupación" = días con evento entre los días del rango. · {{ $negocio }}</div>
</body>
</html>