{{-- Encabezado común de los PDF: logo o nombre de la empresa, título y filtros aplicados --}}
<table>
    <tr>
        <td style="width:55%; vertical-align: top;">
            @if($logo)
                <img src="{{ $logo }}" style="max-height:52px; max-width:180px;" alt="Logo">
                <div style="margin-top:3px; font-weight:bold;">{{ $negocio }}</div>
            @else
                <h1>{{ $negocio }}</h1>
            @endif
        </td>
        <td class="right" style="vertical-align: top;">
            <h1>{{ $titulo }}</h1>
            <div class="muted">{{ $subtitulo }}</div>
            <div class="muted">Generado: {{ $generado }}</div>
        </td>
    </tr>
</table>