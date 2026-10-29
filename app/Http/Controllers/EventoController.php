<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventoRequest;
use App\Models\Evento;
use App\Models\Reservacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

class EventoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        //
        $eventos = Evento::has('reservacion')
            ->with(['reservacion.cliente', 'reservacion.locacion'])
            ->latest()->paginate(10);
        return view('eventos.index', compact('eventos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
{
    $reservaciones = Reservacion::where('status', 'confirmada')
        ->whereDoesntHave('evento')
        ->with(['cliente', 'locacion'])
        ->get();

   /*  dd($reservaciones);  // <- temporal */

    return view('eventos.create', compact('reservaciones'));
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventoRequest $request): RedirectResponse
    {
        //
        Evento::create($request->validated());
        return redirect()->route('eventos.index')
            ->with('success', 'Evento creado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Evento $evento): View
    {
        //
        $evento->load([
            'reservacion.cliente',
            'reservacion.locacion',
            'reservacion.paquete',
            'actividades.responsableUsuario',
            'staff.empleado',
        ]);
        return view('eventos.show', compact('evento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Evento $evento): View
    {
        //
        return view('eventos.edit', compact('evento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventoRequest $request, Evento $evento): RedirectResponse
    {
        //
        $evento->update($request->safe()->only('estado_montaje'));

        return redirect()->route('eventos.show', $evento)
            ->with('success', 'Estado de montaje actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Evento $evento): RedirectResponse
    {
        //
        $evento->delete();

        return redirect()->route('eventos.index')
            ->with('success', 'Evento eliminado correctamente');
    }
}
