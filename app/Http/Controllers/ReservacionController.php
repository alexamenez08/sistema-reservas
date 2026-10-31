<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservacionRequest;
use App\Models\Cliente;
use App\Models\Locacion;
use App\Models\Paquete;
use App\Models\Reservacion;
use App\Models\User;
use App\Notifications\ReservacionCreada;
use App\Services\CotizadorReservacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use Throwable;

class ReservacionController extends Controller
{
    public function __construct(private CotizadorReservacion $cotizador)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $reservaciones = Reservacion::with(['cliente', 'locacion', 'paquete'])
            ->latest()
            ->paginate(10);
        return view('reservaciones.index', compact('reservaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $clientes  = Cliente::orderBy('nombre')->get();
        $locaciones = Locacion::where('activo', true)->get();
        $paquetes  = Paquete::where('activo', true)->with('locacion')->get();
        return view('reservaciones.create', compact('clientes', 'locaciones', 'paquetes'));
    }

    /**
     * Resumen de costos en vivo para el formulario (solo calcula, no guarda nada).
     */
    public function calcular(Request $request): JsonResponse
    {
        $request->validate([
            'locacion_id'        => ['required', 'exists:locaciones,id'],
            'paquete_id'         => ['nullable', 'exists:paquetes,id'],
            'contador_invitados' => ['nullable', 'integer', 'min:0'],
            'descuento'          => ['nullable', 'numeric', 'min:0'],
        ]);

        $cot = $this->cotizador->calcular(
            Locacion::find($request->input('locacion_id')),
            $request->filled('paquete_id') ? Paquete::find($request->input('paquete_id')) : null,
            (int) $request->input('contador_invitados', 0),
            (float) $request->input('descuento', 0),
        );

        return response()->json([
            'lineas'    => $cot['lineas'],
            'subtotal'  => $cot['subtotal'],
            'descuento' => $cot['descuento'],
            'total'     => $cot['total'],
            'problema'  => $cot['problema'],
            'aviso'     => $cot['aviso'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReservacionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Validar que la locación no tenga otra reserva en la misma fecha
        $conflicto = Reservacion::where('locacion_id', $data['locacion_id'])
            ->where('dia_evento', $data['dia_evento'])
            ->whereNotIn('status', ['cancelada'])
            ->exists();

        if ($conflicto) {
            return back()->withInput()->withErrors([
                'locacion_id' => 'La locación seleccionada ya tiene una reservación en esa fecha.',
            ]);
        }

        // El total lo calcula el sistema con los precios de venta; nunca viene del formulario
        if ($error = $this->aplicarTotales($data)) {
            return back()->withInput()->withErrors($error);
        }
        $data['saldo_pendiente'] = $data['costo_total'];

        $reservacion = Reservacion::create($data);

        // FN20: notificación interna con la fecha del evento (administradores y coordinadores)
        $this->notificarReservacion($reservacion, $request->user()?->name);

        return redirect()->route('reservaciones.index')
            ->with('success', 'Reservación registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservacion $reservacion): View
    {
        $reservacion->load(['cliente', 'locacion', 'paquete', 'anticipos']);
        return view('reservaciones.show', compact('reservacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservacion $reservacion): View
    {
        $clientes   = Cliente::orderBy('nombre')->get();
        $locaciones = Locacion::where('activo', true)->get();
        $paquetes   = Paquete::where('activo', true)->with('locacion')->get();
        return view('reservaciones.edit', compact('reservacion', 'clientes', 'locaciones', 'paquetes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReservacionRequest $request, Reservacion $reservacion): RedirectResponse
    {
        $data = $request->validated();

        // Validar conflicto de fecha excluyendo la reservación actual
        $conflicto = Reservacion::where('locacion_id', $data['locacion_id'])
            ->where('dia_evento', $data['dia_evento'])
            ->whereNotIn('status', ['cancelada'])
            ->where('id', '!=', $reservacion->id)
            ->exists();

        if ($conflicto) {
            return back()->withInput()->withErrors([
                'locacion_id' => 'La locación seleccionada ya tiene una reservación en esa fecha.',
            ]);
        }

        if ($error = $this->aplicarTotales($data)) {
            return back()->withInput()->withErrors($error);
        }

        $reservacion->update($data);
        $reservacion->recalcularSaldo();

        return redirect()->route('reservaciones.index')
            ->with('success', 'Reservación actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservacion $reservacion): RedirectResponse
    {
        $reservacion->update(['status' => 'cancelada']);
        $reservacion->delete();

        return redirect()->route('reservaciones.index')
            ->with('success', 'Reservación cancelada correctamente.');
    }

    /** Crea la notificación interna de la reservación. Un fallo aquí nunca debe impedir guardar la reservación. */
    private function notificarReservacion(Reservacion $reservacion, ?string $autor): void
    {
        try {
            $destinatarios = User::role(['administrador', 'coordinador'])->where('is_active', true)->get();
            Notification::send($destinatarios, new ReservacionCreada($reservacion, $autor));
        } catch (Throwable $e) {
            Log::warning('No se pudo crear la notificación de la reservación: ' . $e->getMessage());
        }
    }

    /**
     * Calcula subtotal, descuento y total y los escribe en $data.
     * Devuelve un arreglo de errores si algo no cuadra, o null si todo está bien.
     */
    private function aplicarTotales(array &$data): ?array
    {
        $descuentoPedido = (float) ($data['descuento'] ?? 0);

        $cot = $this->cotizador->calcular(
            Locacion::find($data['locacion_id']),
            ! empty($data['paquete_id']) ? Paquete::find($data['paquete_id']) : null,
            (int) $data['contador_invitados'],
            $descuentoPedido,
        );

        // Paquete sin precio, o invitados extra sin precio por persona extra
        if ($cot['problema']) {
            return ['paquete_id' => $cot['problema']];
        }

        if ($cot['subtotal'] <= 0) {
            return ['locacion_id' => 'El subtotal calculado es $0. Captura el precio base de la locación o el precio del paquete.'];
        }

        if ($descuentoPedido > $cot['subtotal']) {
            return ['descuento' => 'El descuento no puede ser mayor al subtotal ($' . number_format($cot['subtotal'], 2) . ').'];
        }

        $data['costo_total']      = $cot['total'];
        $data['descuento']        = $cot['descuento'];
        $data['motivo_descuento'] = $cot['descuento'] > 0 ? trim((string) ($data['motivo_descuento'] ?? '')) : null;

        return null;
    }
}