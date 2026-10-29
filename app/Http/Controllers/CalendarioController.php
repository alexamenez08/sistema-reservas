<?php

namespace App\Http\Controllers;

use App\Models\Locacion;
use Illuminate\Http\Request;
use App\Models\Reservacion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CalendarioController extends Controller
{
/**
     * Color y etiqueta por estado de la reservación.
     * Cambia aquí los colores si quieres ajustarlos (FN11 del documento).
     *
     *  - pendiente              => amarillo (apartado)
     *  - confirmada / en curso  => rojo (reservado y confirmado)
     *  - finalizada             => gris (histórico)
     *  - cancelada              => no se muestra (la fecha queda disponible)
     */
    private const ESTADOS = [
        'pendiente'  => ['color' => '#f59e0b', 'tinte' => 'rgba(245, 158, 11, 0.22)', 'etiqueta' => 'Apartada (pendiente)', 'prioridad' => 2],
        'confirmada' => ['color' => '#ef4444', 'tinte' => 'rgba(239, 68, 68, 0.20)',  'etiqueta' => 'Confirmada',           'prioridad' => 3],
        'en curso'   => ['color' => '#ef4444', 'tinte' => 'rgba(239, 68, 68, 0.20)',  'etiqueta' => 'En curso',             'prioridad' => 3],
        'finalizada' => ['color' => '#6b7280', 'tinte' => 'rgba(107, 114, 128, 0.15)', 'etiqueta' => 'Finalizada',           'prioridad' => 1],
    ];
 
    private const TINTE_DISPONIBLE = 'rgba(34, 197, 94, 0.18)';
 
    /**
     * Vista principal del calendario.
     */
    public function index(Request $request): View
    {
        $locaciones = Locacion::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
 
        // Staff: solo consulta (sin finanzas ni acceso a reservaciones)
        /** @var \App\Models\User $usuario */
        $usuario = $request->user();
        $puedeGestionar = $usuario->hasAnyRole(['administrador', 'coordinador']);
 
        return view('calendario.index', compact('locaciones', 'puedeGestionar'));
    }
 
    /**
     * Feed JSON que consume FullCalendar (se llama cada vez que cambia el rango visible).
     * Devuelve:
     *  - un evento por reservación (chip de color con el resumen en extendedProps)
     *  - eventos de fondo que pintan el día: verde (disponible) / amarillo / rojo
     */
    public function eventos(Request $request): JsonResponse
    {
        $request->validate([
            'start'       => ['required', 'date'],
            'end'         => ['required', 'date'],
            'locacion_id' => ['nullable', 'integer', 'exists:locaciones,id'],
        ]);
 
        // FullCalendar manda ISO8601 con zona (ej. 2026-10-01T00:00:00-06:00); nos quedamos con la fecha.
        $desde      = Carbon::parse($request->input('start'))->toDateString();
        $hasta      = Carbon::parse($request->input('end'))->toDateString(); // exclusivo
        $locacionId = $request->input('locacion_id');
 
        // El staff no ve el estado financiero ni el enlace a la reservación
        /** @var \App\Models\User $usuario */
        $usuario = $request->user();
        $puedeGestionar = $usuario->hasAnyRole(['administrador', 'coordinador']);
 
        // Las canceladas se hacen soft delete en ReservacionController@destroy,
        // así que ya quedan fuera; el whereNotIn cubre las que solo cambian de status.
        $reservaciones = Reservacion::query()
            ->with(['cliente', 'locacion', 'paquete', 'evento'])
            ->withSum('anticipos as total_anticipos', 'monto')
            ->whereNotIn('status', ['cancelada'])
            ->where('dia_evento', '>=', $desde)
            ->where('dia_evento', '<', $hasta)
            ->when($locacionId, fn ($q) => $q->where('locacion_id', $locacionId))
            ->orderBy('hora_inicio')
            ->get();
 
        $eventos = [];
 
        /* ---------- 1. Una entrada por reservación ---------- */
        foreach ($reservaciones as $r) {
            $estado = self::ESTADOS[$r->status] ?? self::ESTADOS['pendiente'];
 
            $anticipado = (float) ($r->total_anticipos ?? 0);
            $financiero = match (true) {
                $anticipado <= 0                         => 'Sin anticipo',
                (float) $r->saldo_pendiente <= 0         => 'Liquidada',
                default                                  => 'Anticipo parcial',
            };
 
            $cliente  = $r->cliente?->nombre_completo ?? 'Cliente eliminado';
            $locacion = $r->locacion?->nombre ?? 'Locación eliminada';
 
            $eventos[] = [
                'id'              => 'res-' . $r->id,
                'title'           => $cliente . ' · ' . $locacion,
                'start'           => $r->hora_inicio->format('Y-m-d\TH:i:s'),
                'end'             => $r->hora_fin->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $estado['color'],
                'borderColor'     => $estado['color'],
                'extendedProps'   => [
                    'dia'             => $r->dia_evento->toDateString(),
                    'cliente'         => $cliente,
                    'locacion'        => $locacion,
                    'estado'          => $r->status,
                    'estado_etiqueta' => $estado['etiqueta'],
                    'color'           => $estado['color'],
                    'financiero'      => $puedeGestionar ? $financiero : null,
                    'horario'         => $r->hora_inicio->format('H:i') . ' – ' . $r->hora_fin->format('H:i'),
                    'invitados'       => $r->contador_invitados,
                    'paquete'         => $r->paquete?->nombre,
                    'url_reservacion' => $puedeGestionar ? route('reservaciones.show', $r) : null,
                    'url_evento'      => $r->evento ? route('eventos.show', $r->evento) : null,
                ],
            ];
        }
 
        /* ---------- 2. Color de fondo de cada día ---------- */
        $totalLocaciones = $locacionId
            ? 1
            : Locacion::where('activo', true)->count();
 
        $porDia = $reservaciones->groupBy(fn ($r) => $r->dia_evento->toDateString());
        $hoy    = Carbon::today()->toDateString();
 
        $periodo = CarbonPeriod::create($desde, Carbon::parse($hasta)->subDay()->toDateString());
 
        foreach ($periodo as $dia) {
            $fecha    = $dia->toDateString();
            $delDia   = $porDia->get($fecha);
            $ocupadas = $delDia ? $delDia->pluck('locacion_id')->unique()->count() : 0;
            $llena    = $totalLocaciones > 0 && $ocupadas >= $totalLocaciones;
 
            if ($llena) {
                // Todo ocupado: se tiñe con el estado más "fuerte" del día (rojo > amarillo > gris).
                $tinte = $delDia
                    ->map(fn ($r) => self::ESTADOS[$r->status] ?? self::ESTADOS['pendiente'])
                    ->sortByDesc('prioridad')
                    ->first()['tinte'];
            } elseif ($fecha >= $hoy && $totalLocaciones > 0) {
                // Hay al menos una locación libre (con filtro: la locación elegida está libre).
                $tinte = self::TINTE_DISPONIBLE;
            } else {
                continue; // días pasados sin ocupación: sin color
            }
 
            $eventos[] = [
                'id'      => 'bg-' . $fecha,
                'start'   => $fecha,
                'end'     => $dia->copy()->addDay()->toDateString(),
                'allDay'  => true,
                'display' => 'background',
                'color'   => $tinte,
            ];
        }
 
        return response()->json($eventos);
    }
}
