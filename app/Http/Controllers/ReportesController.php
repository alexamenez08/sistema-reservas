<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionNegocio;
use App\Models\Locacion;
use App\Models\Paquete;
use App\Models\Reservacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reportes del administrador (FN15 a FN18).
 * Cada reporte se ve en pantalla y se descarga en PDF con los mismos filtros (?pdf=1).
 */
class ReportesController extends Controller
{
    /* ===================================================================
     *  FN15. Ingresos y saldos pendientes
     * =================================================================== */
    public function ingresos(Request $request): View|Response
    {
        [$desde, $hasta] = $this->rango($request, Carbon::today(), Carbon::today()->addDays(60)->endOfDay());
        $hoy = Carbon::today();

        $filas = Reservacion::with(['cliente', 'locacion'])
            ->withSum('anticipos as total_anticipos', 'monto')
            ->whereIn('status', ['pendiente', 'confirmada', 'en curso', 'finalizada'])
            ->whereBetween('dia_evento', [$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('dia_evento')
            ->orderBy('hora_inicio')
            ->get()
            ->map(function (Reservacion $r) use ($hoy) {
                $total  = (float) $r->costo_total;
                $pagado = (float) ($r->total_anticipos ?? 0);
                $saldo  = round($total - $pagado, 2);

                // Vencido = el evento ya pasó y todavía se debe dinero
                $estado = match (true) {
                    $saldo <= 0               => 'Liquidado',
                    $r->dia_evento->lt($hoy)  => 'Vencido',
                    $pagado > 0               => 'Con anticipo',
                    default                   => 'Sin anticipo',
                };

                return (object) [
                    'id'       => $r->id,
                    'fecha'    => $r->dia_evento,
                    'cliente'  => $r->cliente?->nombre_completo ?? 'Cliente eliminado',
                    'locacion' => $r->locacion?->nombre ?? 'Locación eliminada',
                    'status'   => $r->status,
                    'total'    => $total,
                    'pagado'   => $pagado,
                    'saldo'    => max(0, $saldo),
                    'estado'   => $estado,
                ];
            });

        $resumen = [
            'total'          => (float) $filas->sum('total'),
            'pagado'         => (float) $filas->sum('pagado'),
            'saldo'          => (float) $filas->sum('saldo'),
            'vencidas'       => $filas->where('estado', 'Vencido')->count(),
            'monto_vencido'  => (float) $filas->where('estado', 'Vencido')->sum('saldo'),
        ];

        $datos = compact('filas', 'resumen', 'desde', 'hasta');

        return $request->boolean('pdf')
            ? $this->pdf('reportes.pdf.ingresos', $datos, 'Reporte-ingresos-y-saldos.pdf', 'landscape')
            : view('reportes.ingresos', $datos);
    }

    /* ===================================================================
     *  FN16. Ocupación de locaciones
     * =================================================================== */
    public function ocupacion(Request $request): View|Response
    {
        $request->validate(['locacion_id' => ['nullable', 'integer', 'exists:locaciones,id']]);

        [$desde, $hasta] = $this->rango(
            $request,
            Carbon::today()->subMonths(11)->startOfMonth(),
            Carbon::today()->endOfMonth()
        );
        $locacionId = $request->input('locacion_id');

        $reservaciones = Reservacion::query()
            ->whereNotIn('status', ['cancelada'])
            ->whereBetween('dia_evento', [$desde->toDateString(), $hasta->toDateString()])
            ->when($locacionId, fn ($q) => $q->where('locacion_id', $locacionId))
            ->get(['id', 'locacion_id', 'dia_evento', 'contador_invitados', 'costo_total']);

        // Días del rango, para calcular el % de ocupación (días con evento / días del rango)
        $diasRango = (int) abs($desde->copy()->startOfDay()->diffInDays($hasta->copy()->startOfDay())) + 1;

        // Locaciones a mostrar: las activas (aunque no tengan reservas) + las que tengan reservas en el rango
        $locaciones = Locacion::withTrashed()
            ->where(fn ($q) => $q->where(fn ($w) => $w->where('activo', true)->whereNull('deleted_at'))
                ->orWhereIn('id', $reservaciones->pluck('locacion_id')->unique()))
            ->when($locacionId, fn ($q) => $q->where('id', $locacionId))
            ->get(['id', 'nombre'])
            ->keyBy('id');

        $porLocacion = $reservaciones->groupBy('locacion_id');

        $filas = $locaciones->map(function ($loc) use ($porLocacion, $diasRango) {
            $reservas = $porLocacion->get($loc->id, collect());
            $diasOcupados = $reservas->pluck('dia_evento')->map(fn ($d) => $d->toDateString())->unique()->count();

            return (object) [
                'nombre'       => $loc->nombre,
                'reservas'     => $reservas->count(),
                'dias'         => $diasOcupados,
                'ocupacion'    => round($diasOcupados / max(1, $diasRango) * 100, 1),
                'invitados'    => (int) $reservas->sum('contador_invitados'),
                'monto'        => (float) $reservas->sum('costo_total'),
            ];
        })->sortByDesc('reservas')->values();

        // Temporadas: reservaciones por mes
        $porMes = $reservaciones->groupBy(fn ($r) => $r->dia_evento->format('Y-m'))->map->count();
        $meses = [];
        foreach (CarbonPeriod::create($desde->copy()->startOfMonth(), '1 month', $hasta->copy()->startOfMonth()) as $mes) {
            $meses[] = [
                'etiqueta' => ucfirst($mes->copy()->locale('es')->translatedFormat('M Y')),
                'total'    => (int) ($porMes[$mes->format('Y-m')] ?? 0),
            ];
        }

        $datos = [
            'filas'            => $filas,
            'meses'            => $meses,
            'totalReservas'    => $reservaciones->count(),
            'locacionesLista'  => Locacion::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'locacionNombre'   => $locacionId ? ($locaciones[$locacionId]->nombre ?? null) : null,
            'desde'            => $desde,
            'hasta'            => $hasta,
            'locacionId'       => $locacionId,
        ];

        return $request->boolean('pdf')
            ? $this->pdf('reportes.pdf.ocupacion', $datos, 'Reporte-ocupacion-de-locaciones.pdf')
            : view('reportes.ocupacion', $datos);
    }

    /* ===================================================================
     *  FN17. Paquetes más pedidos
     * =================================================================== */
    public function paquetes(Request $request): View|Response
    {
        [$desde, $hasta] = $this->rango(
            $request,
            Carbon::today()->subMonths(11)->startOfMonth(),
            Carbon::today()->endOfMonth()
        );

        $conteo = Reservacion::query()
            ->select('paquete_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(costo_total) as monto'))
            ->whereNotNull('paquete_id')
            ->whereNotIn('status', ['cancelada'])
            ->whereBetween('dia_evento', [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('paquete_id')
            ->orderByDesc('total')
            ->get();

        $paquetes = Paquete::with('locacion')->whereIn('id', $conteo->pluck('paquete_id'))->get()->keyBy('id');

        $totalConPaquete = (int) $conteo->sum('total');

        $filas = $conteo->map(function ($c) use ($paquetes, $totalConPaquete) {
            $p = $paquetes->get($c->paquete_id);

            return (object) [
                'nombre'     => $p?->nombre ?? 'Paquete eliminado',
                'locacion'   => $p?->locacion?->nombre ?? '—',
                'reservas'   => (int) $c->total,
                'porcentaje' => $totalConPaquete > 0 ? round($c->total / $totalConPaquete * 100, 1) : 0,
                'monto'      => (float) $c->monto,
            ];
        })->values();

        $sinPaquete = Reservacion::query()
            ->whereNull('paquete_id')
            ->whereNotIn('status', ['cancelada'])
            ->whereBetween('dia_evento', [$desde->toDateString(), $hasta->toDateString()])
            ->count();

        $datos = compact('filas', 'sinPaquete', 'totalConPaquete', 'desde', 'hasta');

        return $request->boolean('pdf')
            ? $this->pdf('reportes.pdf.paquetes', $datos, 'Reporte-paquetes-mas-pedidos.pdf')
            : view('reportes.paquetes', $datos);
    }

    /* ===================================================================
     *  FN18. Concentración de eventos por periodo
     *  Solo cuentan reservaciones "confirmada" o "finalizada".
     * =================================================================== */
    public function concentracion(Request $request): View|Response
    {
        $request->validate([
            'anio'  => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'desde' => ['nullable', 'date', 'required_with:hasta'],
            'hasta' => ['nullable', 'date', 'required_with:desde', 'after_or_equal:desde'],
        ], [
            'desde.required_with'  => 'Indica también la fecha inicial.',
            'hasta.required_with'  => 'Indica también la fecha final.',
            'hasta.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ]);

        $anio     = (int) $request->input('anio', Carbon::today()->year);
        $usaRango = $request->filled('desde') && $request->filled('hasta');

        if ($usaRango) {
            $desde = Carbon::parse($request->input('desde'))->startOfDay();
            $hasta = Carbon::parse($request->input('hasta'))->endOfDay();
        } else {
            $desde = Carbon::create($anio, 1, 1)->startOfDay();
            $hasta = Carbon::create($anio, 12, 31)->endOfDay();
        }

        $porMes = Reservacion::query()
            ->whereIn('status', ['confirmada', 'finalizada'])
            ->whereBetween('dia_evento', [$desde->toDateString(), $hasta->toDateString()])
            ->get(['dia_evento'])
            ->groupBy(fn ($r) => $r->dia_evento->format('Y-m'))
            ->map->count();

        $meses = [];
        foreach (CarbonPeriod::create($desde->copy()->startOfMonth(), '1 month', $hasta->copy()->startOfMonth()) as $mes) {
            $meses[] = [
                'etiqueta' => ucfirst($mes->copy()->locale('es')->translatedFormat('F Y')),
                'total'    => (int) ($porMes[$mes->format('Y-m')] ?? 0),
            ];
        }

        $coleccion = collect($meses);
        $total     = (int) $coleccion->sum('total');
        $mayor     = $coleccion->sortByDesc('total')->first();   // mes de mayor actividad
        $menor     = $coleccion->sortBy('total')->first();       // mes de menor actividad

        $datos = [
            'meses'    => $meses,
            'total'    => $total,
            'mayor'    => $mayor,
            'menor'    => $menor,
            'anio'     => $anio,
            'usaRango' => $usaRango,
            'desde'    => $desde,
            'hasta'    => $hasta,
            'anios'    => range(Carbon::today()->year - 5, Carbon::today()->year + 1),
        ];

        return $request->boolean('pdf')
            ? $this->pdf('reportes.pdf.concentracion', $datos, 'Reporte-concentracion-de-eventos.pdf')
            : view('reportes.concentracion', $datos);
    }

    /* ===================================================================
     *  Utilidades
     * =================================================================== */

    /** Valida y devuelve [desde, hasta] (con valores por defecto si no se enviaron). */
    private function rango(Request $request, Carbon $desdePorDefecto, Carbon $hastaPorDefecto): array
    {
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ], [
            'desde.date'           => 'La fecha inicial no es válida.',
            'hasta.date'           => 'La fecha final no es válida.',
            'hasta.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ]);

        $desde = $request->filled('desde') ? Carbon::parse($request->input('desde'))->startOfDay() : $desdePorDefecto;
        $hasta = $request->filled('hasta') ? Carbon::parse($request->input('hasta'))->endOfDay() : $hastaPorDefecto;

        return [$desde, $hasta];
    }

    /** Genera el PDF con el encabezado de la empresa (logo y nombre del perfil de empresa). */
    private function pdf(string $vista, array $datos, string $archivo, string $orientacion = 'portrait'): Response
    {
        $empresa = ConfiguracionNegocio::actual();

        return Pdf::loadView($vista, $datos + [
            'empresa'  => $empresa,
            'negocio'  => $empresa->nombre_mostrar,
            'logo'     => $empresa->logoBase64(),
            'generado' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', $orientacion)->download($archivo);
    }
}