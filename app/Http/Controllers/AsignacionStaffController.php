<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsignacionStaffRequest;
use App\Models\AsignacionStaff;
use App\Models\Evento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AsignacionStaffController extends Controller
{
    //
    public function store(AsignacionStaffRequest $request, Evento $evento): RedirectResponse
    {
        // Verificar que el empleado no esté ya asignado a este evento
        $yaAsignado = AsignacionStaff::where('evento_id', $evento->id)
            ->where('empleado_id', $request->empleado_id)
            ->exists();

        if ($yaAsignado) {
            return back()->withErrors([
                'empleado_id' => 'Este empleado ya está asignado a este evento.',
            ]);
        }

        AsignacionStaff::create([
            'rol_asignado' => $request->rol_asignado,
            'pago_evento'  => $request->pago_evento,
            'evento_id'    => $evento->id,
            'empleado_id'  => $request->empleado_id,
        ]);

        return redirect()->route('eventos.show', $evento)
            ->with('success', 'Staff asignado correctamente.');
    }

    public function destroy(AsignacionStaff $asignacionStaff): RedirectResponse
    {
        $evento = $asignacionStaff->evento;
        $asignacionStaff->delete();

        return redirect()->route('eventos.show', $evento)
            ->with('success', 'Asignación eliminada correctamente.');
    }
}
