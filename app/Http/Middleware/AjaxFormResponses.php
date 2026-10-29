<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hace que los formularios enviados por AJAX (script layouts/ajax-forms.blade.php) no recarguen la página:
 *
 *  - Redirección con errores de validación (back()->withErrors(...)) => 422 JSON con los errores por campo.
 *  - Redirección normal (guardado correcto)                           => JSON {"redirect": "<url>"}; el script
 *    navega a esa URL y el mensaje flash ("success") se conserva para esa página.
 *
 * Las peticiones normales (sin AJAX) no se tocan, así que todo sigue funcionando sin JavaScript.
 * Los FormRequest que fallan ya devuelven 422 JSON por sí solos cuando la petición pide JSON.
 */
class AjaxFormResponses
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! ($request->ajax() && $request->expectsJson()) || ! $response instanceof RedirectResponse) {
            return $response;
        }

        $errores = $request->session()->get('errors');

        if ($errores && $errores->any()) {
            // Los errores viajan en el JSON; se borran de la sesión para que no aparezcan en la siguiente página
            $request->session()->forget(['errors', '_old_input']);

            return response()->json([
                'message' => 'Hay campos que necesitan tu atención.',
                'errors'  => $errores->getBag('default')->messages(),
            ], 422);
        }

        return response()->json(['redirect' => $response->getTargetUrl()]);
    }
}