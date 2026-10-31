<?php

namespace App\Notifications;

use App\Models\Reservacion;
use Illuminate\Notifications\Notification;

/** FN20. Notificación interna: se agendó una reservación (con la fecha del evento). */
class ReservacionCreada extends Notification
{
    public function __construct(private Reservacion $reservacion, private ?string $autor = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $r = $this->reservacion->loadMissing(['cliente', 'locacion']);

        $cliente  = $r->cliente?->nombre_completo ?? 'Cliente';
        $locacion = $r->locacion?->nombre ?? 'Locación';

        return [
            'titulo'  => 'Nueva reservación creada',
            'mensaje' => "{$cliente} · {$locacion}. Fecha del evento: " . $r->dia_evento->format('d/m/Y')
                . ' de ' . $r->hora_inicio->format('H:i') . ' a ' . $r->hora_fin->format('H:i') . '.'
                . ($this->autor ? " Registrada por {$this->autor}." : ''),
            'icono'   => 'event_available',
            'url'     => route('reservaciones.show', $r, false),
        ];
    }
}