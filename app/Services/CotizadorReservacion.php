<?php

namespace App\Services;

use App\Models\Locacion;
use App\Models\Paquete;

/**
 * Única fórmula de precio del sistema. La usan:
 *  - ReservacionController (al guardar: costo_total = total)
 *  - CotizacionController  (PDF)
 *  - El resumen en vivo del formulario de reservaciones
 *
 * Reglas:
 *  - CON paquete:  total = precio_venta del paquete
 *                        + (invitados que excedan la capacidad del paquete) x precio_persona_extra
 *                        - descuento
 *    El precio del paquete ya incluye la locación, el mobiliario, el menú y los servicios.
 *    Si el paquete no tiene precio por invitado extra, los excedentes no generan cargo (solo un aviso).
 *    Si hay MENOS invitados que la capacidad del paquete no se rebaja nada: para eso está el descuento manual.
 *  - SIN paquete:  total = precio_base de la locación - descuento
 */
class CotizadorReservacion
{
    /** Orden en que se listan las categorías del menú en el PDF. */
    private const ORDEN_MENU = ['entrada', 'plato fuerte', 'postre', 'bebida', 'menu infantil'];

    /**
     * @return array{
     *   lineas: array<int, array{concepto:string, detalle:string, importe:float}>,
     *   mobiliario: \Illuminate\Support\Collection,
     *   menu: \Illuminate\Support\Collection,
     *   servicios: \Illuminate\Support\Collection,
     *   subtotal: float,
     *   descuento: float,
     *   total: float,
     *   problema: ?string,
     *   aviso: ?string
     * }
     */
    public function calcular(?Locacion $locacion, ?Paquete $paquete, int $invitados, float $descuento = 0): array
    {
        $lineas     = [];
        $mobiliario = collect();
        $menu       = collect();
        $servicios  = collect();
        $problema   = null;
        $aviso      = null;

        if ($paquete) {
            $paquete->loadMissing(['mobiliario', 'platillos', 'servicios']);

            // Precio fijo del paquete
            $precioPaquete = (float) $paquete->precio_venta;
            if ($precioPaquete <= 0) {
                $problema = "El paquete \"{$paquete->nombre}\" no tiene precio de venta. Captúralo en el paquete.";
            }

            $lineas[] = [
                'concepto' => 'Paquete ' . $paquete->nombre,
                'detalle'  => 'Incluye hasta ' . (int) $paquete->capacidad . ' invitados',
                'importe'  => $precioPaquete,
            ];

            // Invitados extra (por encima de la capacidad que cubre el paquete).
            // Con precio por invitado extra => se cobran. Sin precio (vacío o 0) => el paquete no cobra extras.
            $extras      = max(0, $invitados - (int) $paquete->capacidad);
            $precioExtra = (float) $paquete->precio_persona_extra;
            if ($extras > 0) {
                if ($precioExtra > 0) {
                    $lineas[] = [
                        'concepto' => 'Invitados adicionales',
                        'detalle'  => $extras . ' persona(s) x $' . number_format($precioExtra, 2),
                        'importe'  => $extras * $precioExtra,
                    ];
                } else {
                    $aviso = "{$extras} invitado(s) exceden la capacidad del paquete ({$paquete->capacidad}). "
                        . 'Este paquete no tiene cargo por invitado extra.';
                }
            }

            // Lo que incluye el paquete (para listarlo en la cotización)
            $mobiliario = $paquete->mobiliario->map(fn ($m) => [
                'nombre'   => $m->nombre,
                'cantidad' => (int) $m->pivot->cantidad,
            ]);

            $menu = $paquete->platillos
                ->sortBy(fn ($p) => array_search($p->categoria, self::ORDEN_MENU))
                ->groupBy('categoria');

            $servicios = $paquete->servicios->pluck('concepto')->values();
        } elseif ($locacion) {
            // Sin paquete: solo la renta de la locación
            $lineas[] = [
                'concepto' => 'Renta de locación',
                'detalle'  => $locacion->nombre,
                'importe'  => (float) $locacion->precio_base,
            ];
        }

        $subtotal  = round((float) collect($lineas)->sum('importe'), 2);
        $descuento = round(max(0, $descuento), 2);

        return [
            'lineas'     => $lineas,
            'mobiliario' => $mobiliario,
            'menu'       => $menu,
            'servicios'  => $servicios,
            'subtotal'   => $subtotal,
            'descuento'  => $descuento,
            'total'      => round($subtotal - $descuento, 2),
            'problema'   => $problema,
            'aviso'      => $aviso,
        ];
    }
}