<?php

namespace App\Http\Controllers;

use App\Models\Anticipo;
use App\Models\Locacion;
use App\Models\Reservacion;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Estados que cuentan como "confirmadas" (incluye las que ya están en curso o finalizaron). */
    private const CONFIRMADAS = ['confirmada', 'en curso', 'finalizada'];

    /** Estados "vivos": reservaciones que todavía ocupan fecha y pueden generar cobro. */
    private const ACTIVAS = ['pendiente', 'confirmada', 'en curso'];

    public function index(Request $request): View|RedirectResponse
    {
        /** @var \App\Models\User $usuario */
        $usuario = $request->user();

        // El dashboard es del administrador; coordinador y staff entran al calendario
        if (! $usuario->hasRole('administrador')) {
            return redirect()->route('calendario.index');
        }

        $hoy       = Carbon::today();
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes    = $hoy->copy()->endOfMonth();

        /* ---------- 1) Reservaciones del mes por estado (según la fecha del evento) ----------
           Las canceladas se eliminan con soft delete, por eso se usa withTrashed(). */
        $porEstado = Reservacion::withTrashed()
            ->whereBetween('dia_evento', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $confirmadas = (int) collect(self::CONFIRMADAS)->sum(fn ($e) => $porEstado[$e] ?? 0);
        $pendientes  = (int) ($porEstado['pendiente'] ?? 0);
        $canceladas  = (int) ($porEstado['cancelada'] ?? 0);
        $totalMes    = $confirmadas + $pendientes + $canceladas;

        /* ---------- 2) Ingresos del mes = anticipos recibidos en el mes ---------- */
        $ingresosMes = (float) Anticipo::whereBetween('fecha_pago', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->sum('monto');

        // Dinero que falta por cobrar de las reservaciones vigentes
        $saldoPorCobrar = (float) Reservacion::whereIn('status', self::ACTIVAS)
            ->where('dia_evento', '>=', $hoy->toDateString())
            ->sum('saldo_pendiente');

        /* ---------- 3) Ingresos de los últimos 6 meses (gráfica de líneas) ---------- */
        $inicioSerie = $hoy->copy()->subMonths(5)->startOfMonth();
        $pagos = Anticipo::whereBetween('fecha_pago', [$inicioSerie->toDateString(), $finMes->toDateString()])
            ->get(['monto', 'fecha_pago'])
            ->groupBy(fn ($a) => $a->fecha_pago->format('Y-m'))
            ->map(fn ($grupo) => (float) $grupo->sum('monto'));

        $serieMeses   = [];
        $serieIngreso = [];
        for ($mes = $inicioSerie->copy(); $mes <= $inicioMes; $mes->addMonth()) {
            $serieMeses[]   = ucfirst($mes->copy()->locale('es')->translatedFormat('M Y'));
            $serieIngreso[] = round($pagos[$mes->format('Y-m')] ?? 0, 2);
        }

        /* ---------- 4) Próximos eventos (siguientes 7 días) ---------- */
        $proximos = Reservacion::with(['cliente', 'locacion'])
            ->whereIn('status', self::ACTIVAS)
            ->whereBetween('dia_evento', [$hoy->toDateString(), $hoy->copy()->addDays(7)->toDateString()])
            ->orderBy('dia_evento')
            ->orderBy('hora_inicio')
            ->get();

        /* ---------- 5) Locaciones con mayor demanda (últimos 12 meses, sin canceladas) ---------- */
        $demanda = Reservacion::query()
            ->select('locacion_id', DB::raw('COUNT(*) as total'))
            ->whereNotIn('status', ['cancelada'])
            ->where('dia_evento', '>=', $hoy->copy()->subMonths(12)->toDateString())
            ->groupBy('locacion_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $nombres = Locacion::withTrashed()
            ->whereIn('id', $demanda->pluck('locacion_id'))
            ->pluck('nombre', 'id');

        $demandaEtiquetas = $demanda->map(fn ($d) => $nombres[$d->locacion_id] ?? 'Locación eliminada')->values();
        $demandaValores   = $demanda->pluck('total')->map(fn ($t) => (int) $t)->values();

        return view('dashboard', [
            'mesActual'        => ucfirst($hoy->copy()->locale('es')->translatedFormat('F Y')),
            'totalMes'         => $totalMes,
            'confirmadas'      => $confirmadas,
            'pendientes'       => $pendientes,
            'canceladas'       => $canceladas,
            'ingresosMes'      => $ingresosMes,
            'saldoPorCobrar'   => $saldoPorCobrar,
            'serieMeses'       => $serieMeses,
            'serieIngreso'     => $serieIngreso,
            'proximos'         => $proximos,
            'demandaEtiquetas' => $demandaEtiquetas,
            'demandaValores'   => $demandaValores,
            'actualizado'      => now()->format('H:i'),
        ]);
    }
}