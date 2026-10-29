<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Control de acceso por rol (RD.1).
 * Uso en rutas:  ->middleware('rol:administrador')   o   ->middleware('rol:administrador,coordinador')
 *
 * - Sin sesión              => redirige al login.
 * - Cuenta desactivada      => cierra la sesión y manda al login.
 * - Rol sin permiso         => 403 (aunque el usuario escriba la URL a mano).
 */
class RolAcceso
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->route('login');
        }

        if (! $usuario->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
            ]);
        }

        if (! $usuario->hasAnyRole($roles)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}