<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/** Notificación interna: se restauró la base de datos desde un respaldo. */
class BaseDatosRestaurada extends Notification
{
    private string $momento;

    public function __construct(private string $respaldoPrevio, private ?string $autor = null)
    {
        $this->momento = now()->format('d/m/Y H:i');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'titulo'  => 'Base de datos restaurada',
            'mensaje' => 'Se restauró un respaldo' . ($this->autor ? " por {$this->autor}" : '') . " el {$this->momento}. "
                . "Antes se guardó la copia {$this->respaldoPrevio}.",
            'icono'   => 'restore',
            'url'     => route('respaldos.index', [], false),
        ];
    }
}