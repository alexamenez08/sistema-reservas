<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * FN20. Panel de notificaciones: cada usuario ve solo las suyas, con fecha y hora.
 */
class NotificacionController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\User $usuario */
        $usuario = $request->user();

        $notificaciones = $usuario->notifications()->paginate(15);
        $noLeidas       = $usuario->unreadNotifications()->count();

        return view('notificaciones.index', compact('notificaciones', 'noLeidas'));
    }

    /** Marca una notificación como leída y lleva a la sección relacionada. */
    public function abrir(Request $request, string $id): RedirectResponse
    {
        /** @var \App\Models\User $usuario */
        $usuario = $request->user();

        $notificacion = $usuario->notifications()->where('id', $id)->firstOrFail();
        $notificacion->markAsRead();

        $ruta = $notificacion->data['url'] ?? null;

        // Solo se redirige a rutas internas del sistema
        return ($ruta && str_starts_with($ruta, '/'))
            ? redirect($ruta)
            : redirect()->route('notificaciones.index');
    }

    public function leerTodas(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $usuario */
        $usuario = $request->user();

        $usuario->unreadNotifications->markAsRead();

        return redirect()->route('notificaciones.index')
            ->with('success', 'Todas las notificaciones se marcaron como leídas.');
    }
}