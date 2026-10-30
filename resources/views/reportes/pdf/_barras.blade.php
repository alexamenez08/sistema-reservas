{{-- Gráfica de barras horizontales para PDF (dompdf no ejecuta JavaScript, por eso se dibuja con tablas).
     Recibe $items: [['etiqueta' => ..., 'valor' => numero, 'texto' => ...], ...] --}}
@php
    $maximo = max(1, collect($items)->max('valor'));
@endphp
<table style="width:100%; border-collapse:collapse;">
    @foreach($items as $item)
        <tr>
            <td style="width:27%; padding:3px 6px; font-size:9.5px;">{{ $item['etiqueta'] }}</td>
            <td style="width:58%; padding:3px 6px;">
                <div style="background:#6366f1; height:11px; width:{{ round(($item['valor'] / $maximo) * 100) }}%;"></div>
            </td>
            <td style="width:15%; padding:3px 6px; font-size:9.5px; text-align:right;">{{ $item['texto'] }}</td>
        </tr>
    @endforeach
</table>