<?php

namespace App\Notifications;

use App\Models\ConfiguracionNegocio;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * FN20. Correo de recuperación de contraseña, con advertencia de cambio de contraseña.
 * Reemplaza el correo en inglés que trae Laravel (se activa en el modelo User).
 */
class RestablecerContrasena extends Notification
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

        return (new MailMessage)
            ->subject('Solicitud de cambio de contraseña')
            ->greeting('Hola, ' . $notifiable->name)
            ->line('Recibimos una solicitud para cambiar la contraseña de tu cuenta en ' . $negocio . '.')
            ->action('Cambiar mi contraseña', $url)
            ->line('Este enlace vence en ' . $this->vigencia() . '.')
            ->line('**Advertencia:** si tú no solicitaste este cambio, ignora este correo; tu contraseña actual no se modificará. '
                . 'Si sospechas que alguien intenta entrar a tu cuenta, avisa al administrador.')
            ->salutation('Saludos, ' . $negocio);
    }

    private function vigencia(): string
    {
        $minutos = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

        return ($minutos >= 60 && $minutos % 60 === 0) ? ($minutos / 60) . ' hora(s)' : $minutos . ' minutos';
    }
}