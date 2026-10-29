<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaMobiliarioRequest;
use App\Models\CategoriaMobiliario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaMobiliarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categorias = CategoriaMobiliario::withCount('mobiliario')->latest()->paginate(10);
        return view('categorias_mobiliario.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        //
        return view('categorias_mobiliario.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoriaMobiliarioRequest $request): RedirectResponse
    {
        //
        CategoriaMobiliario::create($request->validated());

        return redirect()->route('categorias-mobiliario.index')
            ->with('success', 'Categoría registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CategoriaMobiliario $categoriasMobiliario): View
    {
        return view('categorias_mobiliario.edit', compact('categoriasMobiliario'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoriaMobiliarioRequest $request, CategoriaMobiliario $categoriasMobiliario): RedirectResponse
    {
        $categoriasMobiliario->update($request->validated());

        return redirect()->route('categorias-mobiliario.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CategoriaMobiliario $categoriasMobiliario): RedirectResponse
    {
        if ($categoriasMobiliario->mobiliario()->count() > 0) {
            return redirect()->route('categorias-mobiliario.index')
                ->with('error', 'No puedes eliminar una categoría que tiene artículos asignados.');
        }

        $categoriasMobiliario->delete();

        return redirect()->route('categorias-mobiliario.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}
