<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/** FN20. Notificación interna: el administrador generó un respaldo (con fecha y hora). */
class RespaldoGenerado extends Notification
{
    private string $momento;

    public function __construct(private string $archivo, private ?string $autor = null)
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
            'titulo'  => 'Respaldo de la base de datos generado',
            'mensaje' => "Archivo {$this->archivo}, generado" . ($this->autor ? " por {$this->autor}" : '') . " el {$this->momento}.",
            'icono'   => 'backup',
            'url'     => route('respaldos.index', [], false),
        ];
    }
}