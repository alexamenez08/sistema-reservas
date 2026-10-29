<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionNegocio;
use App\Models\Reservacion;
use App\Services\CotizadorReservacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class CotizacionController extends Controller
{
    //
    private const INCLUIR_STAFF = false;
 
    public function __construct(private CotizadorReservacion $cotizador)
    {
    }
 
    /**
     * Genera y descarga la cotización (FN12) de una reservación.
     * Usa la misma fórmula que el formulario de reservaciones (CotizadorReservacion),
     * así que el PDF siempre cuadra con el total y no necesita renglones de "ajuste".
     */
    public function descargar(Reservacion $reservacion): Response
    {
        $reservacion->load(['cliente', 'locacion', 'paquete', 'anticipos']);
 
        $invitados = (int) $reservacion->contador_invitados;
 
        $cot = $this->cotizador->calcular(
            $reservacion->locacion,
            $reservacion->paquete,
            $invitados,
            (float) $reservacion->descuento,
        );
 
        $anticipado = (float) $reservacion->anticipos->sum('monto');
        $empresa    = ConfiguracionNegocio::actual();
        $folio      = 'COT-' . str_pad((string) $reservacion->id, 5, '0', STR_PAD_LEFT);
 
        $pdf = Pdf::loadView('cotizaciones.pdf', [
            'reservacion'     => $reservacion,
            'folio'           => $folio,
            'empresa'         => $empresa,
            'negocio'         => $empresa->nombre_mostrar,
            'logo'            => $empresa->logoBase64(),
            'lineas'          => $cot['lineas'],
            'mobiliario'      => $cot['mobiliario'],
            'menu'            => $cot['menu'],
            'servicios'       => $cot['servicios'],
            'subtotal'        => $cot['subtotal'],
            'descuento'       => $cot['descuento'],
            'motivoDescuento' => $reservacion->motivo_descuento,
            'costoTotal'      => $cot['total'],
            'anticipado'      => $anticipado,
            'saldo'           => $cot['total'] - $anticipado,
            'invitados'       => $invitados,
        ])->setPaper('letter');
 
        return $pdf->download("Cotizacion-{$folio}.pdf");
    }
}
