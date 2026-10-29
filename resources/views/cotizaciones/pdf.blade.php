<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cotización {{ $folio }}</title>
  {{-- dompdf no soporta flexbox ni grid: todo el layout va con tablas. DejaVu Sans incluye acentos, ñ y "$". --}}
  <style>
    @page { margin: 36px 40px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; }
    h1 { font-size: 20px; margin: 0; color: #111827; }
    h2 { font-size: 11px; text-transform: uppercase; letter-spacing: .8px; color: #4f46e5; margin: 22px 0 8px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; }
    .muted { color: #6b7280; }
    .right { text-align: right; }
    .header td { vertical-align: top; }
    .datos td { padding: 3px 0; vertical-align: top; }
    .datos .k { color: #6b7280; width: 28%; }
    .tabla th { background: #f3f4f6; text-align: left; padding: 7px 8px; font-size: 10px; text-transform: uppercase; color: #374151; }
    .tabla td { padding: 7px 8px; border-bottom: 1px solid #f1f5f9; }
    .total td { font-size: 13px; font-weight: bold; border-top: 2px solid #111827; padding: 9px 8px; }
    .cat { font-weight: bold; text-transform: capitalize; margin-top: 6px; }
    .pie { margin-top: 30px; font-size: 9px; color: #9ca3af; text-align: center; }
  </style>
</head>
<body>

  {{-- Encabezado --}}
  <table class="header">
    <tr>
      <td style="width:55%">
        @if($logo)
          <img src="{{ $logo }}" style="max-height:60px; max-width:200px;" alt="Logo">
        @else
          <h1>{{ $negocio }}</h1>
        @endif
        @if($logo)
          <div style="margin-top:4px; font-weight:bold;">{{ $negocio }}</div>
        @endif
        @if($empresa->telefono)<div class="muted">Tel: {{ $empresa->telefono }}</div>@endif
        @if($empresa->correo)<div class="muted">{{ $empresa->correo }}</div>@endif
      </td>
      <td class="right">
        <h1>COTIZACIÓN</h1>
        <div class="muted">Folio: <strong>{{ $folio }}</strong></div>
        <div class="muted">Fecha de emisión: {{ now()->format('d/m/Y') }}</div>
      </td>
    </tr>
  </table>

  {{-- Cliente --}}
  <h2>Datos del cliente</h2>
  <table class="datos">
    <tr><td class="k">Nombre</td><td>{{ $reservacion->cliente->nombre_completo ?? '—' }}</td></tr>
    @if(!empty($reservacion->cliente->correo))
      <tr><td class="k">Correo</td><td>{{ $reservacion->cliente->correo }}</td></tr>
    @endif
    @if(!empty($reservacion->cliente->tel))
      <tr><td class="k">Teléfono</td><td>{{ $reservacion->cliente->tel }}</td></tr>
    @endif
  </table>

  {{-- Evento --}}
  <h2>Datos del evento</h2>
  <table class="datos">
    <tr><td class="k">Fecha</td><td>{{ $reservacion->dia_evento->format('d/m/Y') }}</td></tr>
    <tr><td class="k">Horario</td><td>{{ $reservacion->hora_inicio->format('H:i') }} – {{ $reservacion->hora_fin->format('H:i') }}</td></tr>
    <tr><td class="k">Locación</td><td>{{ $reservacion->locacion->nombre ?? '—' }}</td></tr>
    @if($reservacion->locacion)
      <tr><td class="k">Dirección</td><td>{{ $reservacion->locacion->direccion }}</td></tr>
    @endif
    <tr><td class="k">Invitados</td><td>{{ $invitados }}</td></tr>
    <tr><td class="k">Paquete</td><td>{{ $reservacion->paquete->nombre ?? 'Sin paquete' }}</td></tr>
  </table>

  {{-- Servicios contratados --}}
  @if($mobiliario->isNotEmpty() || $menu->isNotEmpty() || $servicios->isNotEmpty())
    <h2>Servicios contratados</h2>

    @if($mobiliario->isNotEmpty())
      <div class="cat">Mobiliario</div>
      <table class="tabla">
        <thead><tr><th>Artículo</th><th class="right">Cantidad</th></tr></thead>
        <tbody>
          @foreach($mobiliario as $m)
            <tr><td>{{ $m['nombre'] }}</td><td class="right">{{ $m['cantidad'] }}</td></tr>
          @endforeach
        </tbody>
      </table>
    @endif

    @if($menu->isNotEmpty())
      <div class="cat" style="margin-top:12px">Menú</div>
      @foreach($menu as $categoria => $platillos)
        <div style="margin-top:4px"><strong style="text-transform:capitalize">{{ $categoria }}:</strong>
          {{ $platillos->pluck('nombre')->implode(', ') }}
        </div>
      @endforeach
    @endif

    @if($servicios->isNotEmpty())
      <div class="cat" style="margin-top:12px">Servicios adicionales incluidos</div>
      <div>{{ $servicios->implode(', ') }}</div>
    @endif
  @endif

  {{-- Desglose de costos --}}
  <h2>Desglose de costos</h2>
  <table class="tabla">
    <thead>
      <tr><th>Concepto</th><th>Detalle</th><th class="right">Importe</th></tr>
    </thead>
    <tbody>
      @foreach($lineas as $l)
        <tr>
          <td>{{ $l['concepto'] }}</td>
          <td class="muted">{{ $l['detalle'] }}</td>
          <td class="right">{{ $l['importe'] < 0 ? '-' : '' }}${{ number_format(abs($l['importe']), 2) }}</td>
        </tr>
      @endforeach
      @if($descuento > 0)
        <tr>
          <td colspan="2" class="right"><strong>Subtotal</strong></td>
          <td class="right">${{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr>
          <td>Descuento</td>
          <td class="muted">{{ $motivoDescuento }}</td>
          <td class="right">-${{ number_format($descuento, 2) }}</td>
        </tr>
      @endif
      <tr class="total">
        <td colspan="2">TOTAL</td>
        <td class="right">${{ number_format($costoTotal, 2) }} MXN</td>
      </tr>
    </tbody>
  </table>

  @if($anticipado > 0)
    <table style="margin-top:10px">
      <tr><td class="right muted">Anticipos recibidos:</td><td class="right" style="width:120px">${{ number_format($anticipado, 2) }}</td></tr>
      <tr><td class="right"><strong>Saldo pendiente:</strong></td><td class="right"><strong>${{ number_format($saldo, 2) }}</strong></td></tr>
    </table>
  @endif

  <div class="pie">
    @if($empresa->direccion){{ $empresa->direccion }}<br>@endif
    Documento generado por {{ $negocio }} · {{ $folio }}
  </div>
</body>
</html>