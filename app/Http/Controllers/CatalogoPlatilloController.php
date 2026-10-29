<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogoPlatilloRequest;
use App\Models\CatalogoPlatillo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class CatalogoPlatilloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        //
        $platillos = CatalogoPlatillo::latest()->paginate(10);
        return view('catalogo_platillos.index', compact('platillos'));
    }
 
    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        //
        return view('catalogo_platillos.create');
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(CatalogoPlatilloRequest $request): RedirectResponse
    {
        //
        $data = $request->validated();
 
        if ($request->hasFile('imagen')){
            $data['imagen'] = $request->file('imagen')->store('platillos','public');
        }
 
        $platillo = new CatalogoPlatillo();
        $platillo->nombre = $data['nombre'];
        $platillo->categoria = $data['categoria'];
        $platillo->descripcion = $data['descripcion'] ?? null;
        $platillo->costo_preparacion = $data['costo_preparacion'] ?? null;
        $platillo->precio_venta = $data['precio_venta'] ?? null;
        $platillo->imagen = $data['imagen'] ?? null;
        $platillo->save();
 
        // (Se quitó "CatalogoPlatillo::created($data);": no hacía nada útil, solo registraba un listener.)
 
        return redirect()->route('catalogo-platillos.index')
            ->with('success', 'Platillo registrado correctamente');
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
    public function edit(CatalogoPlatillo $catalogoPlatillo): View
    {
        //
        return view('catalogo_platillos.edit', compact('catalogoPlatillo'));
    }
 
    /**
     * Update the specified resource in storage.
     */
    public function update(CatalogoPlatilloRequest $request, CatalogoPlatillo $catalogoPlatillo)
    {
        //
        $data = $request->validated();
 
        if ($request->hasFile('imagen')) {
            if($catalogoPlatillo->imagen){
                Storage::disk('public')->delete($catalogoPlatillo->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('platillos','public');
        }
        
        $catalogoPlatillo->update($data);
 
        return redirect()->route('catalogo-platillos.index')
            ->with('success', 'Platillo actualizado correctamente');
    }
 
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CatalogoPlatillo $catalogoPlatillo): RedirectResponse
    {
            $enUso = DB::table('comida_paquete')
                ->where('id_comida', $catalogoPlatillo->id)
                ->exists();
 
            if ($enUso) {
                return redirect()->route('catalogo-platillos.index')
                    ->with('error', 'No puedes eliminar este platillo porque está asignado a uno o más paquetes.');
            }
 
            if ($catalogoPlatillo->imagen) {
                Storage::disk('public')->delete($catalogoPlatillo->imagen);
            }
 
            $catalogoPlatillo->delete();
 
            return redirect()->route('catalogo-platillos.index')
                ->with('success', 'Platillo eliminado correctamente.');
    }
}
