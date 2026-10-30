<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de paquetes más pedidos</title>
    @include('reportes.pdf._estilos')
</head>
<body>
    @php
        $subtituloPaquetes = $desde->format('d/m/Y') . ' al ' . $hasta->format('d/m/Y');
    @endphp
    @include('reportes.pdf._cabecera', ['titulo' => 'Paquetes más pedidos', 'subtitulo' => $subtituloPaquetes])

    <h2>Frecuencia de cada paquete</h2>
    @if(count($filas))
        @php
            $itemsPaquetes = $filas->map(fn ($f) => ['etiqueta' => $f->nombre, 'valor' => $f->reservas, 'texto' => $f->reservas . ' (' . $f->porcentaje . '%)'])->all();
        @endphp
        @include('reportes.pdf._barras', ['items' => $itemsPaquetes])
    @else
        <p class="muted">No hay reservaciones con paquete en este periodo.</p>
    @endif

    <h2>Detalle</h2>
    <table class="tabla">
        <thead>
            <tr>
                <th>#</th><th>Paquete</th><th>Locación</th>
                <th class="right">Reservaciones</th><th class="right">% del total</th><th class="right">Valor reservado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filas as $i => $f)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $f->nombre }}</td>
                    <td>{{ $f->locacion }}</td>
                    <td class="right">{{ $f->reservas }}</td>
                    <td class="right">{{ $f->porcentaje }}%</td>
                    <td class="right">${{ number_format($f->monto, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted" style="text-align:center; padding:14px;">Sin datos en el rango seleccionado.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pie">No incluye reservaciones canceladas ni las {{ $sinPaquete }} reservación(es) sin paquete. · {{ $negocio }}</div>
</body>
</html>