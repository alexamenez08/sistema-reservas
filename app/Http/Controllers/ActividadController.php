<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActividadRequest;
use App\Models\Actividad;
use App\Models\Evento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
    //
    public function store(ActividadRequest $request, Evento $evento): RedirectResponse
    {
        $data = $request->validated();
        $data['evento_id'] = $evento->id;

        Actividad::create($data);

        return redirect()->route('eventos.show', $evento)
            ->with('success', 'Actividad agregada correctamente.');
    }

    public function update(ActividadRequest $request, Actividad $actividad): RedirectResponse
    {
        $actividad->update($request->validated());

        return redirect()->route('eventos.show', $actividad->evento)
            ->with('success', 'Actividad actualizada correctamente.');
    }

    public function destroy(Actividad $actividad): RedirectResponse
    {
        $evento = $actividad->evento;
        $actividad->delete();

        return redirect()->route('eventos.show', $evento)
            ->with('success', 'Actividad eliminada correctamente.');
    }
}
