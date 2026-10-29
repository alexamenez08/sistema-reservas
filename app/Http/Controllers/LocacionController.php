<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LocacionRequest;
use App\Models\Locacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LocacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $locaciones = Locacion::latest()->paginate(10);
        return view('locaciones.index', compact('locaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        //
        return view('locaciones.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LocacionRequest $request): RedirectResponse
    {
        //
        Locacion::create($request->validated());

        return redirect()->route('locaciones.index')
        ->with('success', 'Locación registrada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Locacion $locacion): View
    {
        //
        $proximas = $locacion->reservaciones()
            ->with('cliente')
            ->whereNotIn('status', ['cancelada'])
            ->where('dia_evento', '>=', now()->toDateString())
            ->orderBy('dia_evento')
            ->orderBy('hora_inicio')
            ->limit(5)
            ->get();
 
        return view('locaciones.show', compact('locacion', 'proximas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Locacion $locacion): View
    {
        return view('locaciones.edit', compact('locacion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LocacionRequest $request, Locacion $locacion)
    {
        //
        $locacion->update($request->validated());

        return redirect()->route('locaciones.index')
            ->with('success', 'Locación actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Locacion $locacion)
    {
        //
        $locacion->update(['activo' => false]);
        $locacion->delete();

        return redirect()->route('locaciones.index')
        ->with('success', 'Locación eliminada correctamente');
    }
}
