<?php

namespace App\Notifications;

use App\Models\ConfiguracionNegocio;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * FN20. Correo de bienvenida al crear una cuenta.
 * Incluye el botón para que el usuario establezca su propia contraseña.
 */
class BienvenidaUsuario extends Notification
{
    public function __construct(private string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $negocio = ConfiguracionNegocio::actual()->nombre_mostrar;

        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $rol = method_exists($notifiable, 'getRoleNames') ? $notifiable->getRoleNames()->first() : null;

        $mensaje = (new MailMessage)
            ->subject('Bienvenido(a) a ' . $negocio)
            ->greeting('¡Hola, ' . $notifiable->name . '!')
            ->line('Te damos la bienvenida al sistema de reservaciones de ' . $negocio . '. Tu cuenta fue creada correctamente.');

        if ($rol) {
            $mensaje->line('Tu rol en el sistema es: **' . ucfirst($rol) . '**.');
        }

        return $mensaje
            ->line('Para comenzar, establece tu contraseña con el siguiente botón. Por seguridad, el enlace vence en ' . $this->vigencia() . '.')
            ->action('Establecer mi contraseña', $url)
            ->line('Si no esperabas este correo, puedes ignorarlo.')
            ->salutation('Saludos, ' . $negocio);
    }

    private function vigencia(): string
    {
        $minutos = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

        return ($minutos >= 60 && $minutos % 60 === 0) ? ($minutos / 60) . ' hora(s)' : $minutos . ' minutos';
    }
}