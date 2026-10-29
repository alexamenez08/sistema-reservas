<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaqueteRequest;
use App\Models\CatalogoPlatillo;
use App\Models\Locacion;
use App\Models\Mobiliario;
use App\Models\Paquete;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaqueteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $paquetes = Paquete::with('locacion')->latest()->paginate(10);
        return view('paquetes.index', compact('paquetes'));
    }
 
    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        //
        $locaciones = Locacion::where('activo', true)->get();
        $mobiliario = Mobiliario::where('activo', true)->with('categoria')->get();
        $platillos = CatalogoPlatillo::orderBy('categoria')->get();
        return view('paquetes.create', compact('locaciones', 'mobiliario', 'platillos'));
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(PaqueteRequest $request): RedirectResponse
    {
        //
        $paquete = Paquete::create($request->safe()->except(['mobiliario', 'platillos', 'servicios']));
 
        if($request->filled('mobiliario')){
            $mobiliarioSync = [];
            foreach($request->mobiliario as $item){
                $mobiliarioSync[$item['id']] = ['cantidad' => $item['cantidad']];
            }
            $paquete->mobiliario()->sync($mobiliarioSync);
        }
 
        if($request->filled('platillos')){
            $paquete->platillos()->sync($request->platillos);
        }
 
        $this->guardarServicios($paquete, $request->input('servicios', []));
 
        return redirect()->route('paquetes.index')
        ->with('success', 'Paquete registrado correctamente');
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
    public function edit(Paquete $paquete): View
    {
        //
        $locaciones = Locacion::where('activo', true)->get();
        $mobiliario = Mobiliario::where('activo', true)->with('categoria')->get();
        $platillos = CatalogoPlatillo::orderBy('categoria')->get();
        $paquete->load('mobiliario', 'platillos', 'servicios');
        return view('paquetes.edit', compact('paquete', 'locaciones', 'mobiliario', 'platillos'));
    }
 
    /**
     * Update the specified resource in storage.
     */
    public function update(PaqueteRequest $request, Paquete $paquete): RedirectResponse
    {
        //
        $paquete->update($request->safe()->except(['mobiliario','platillos','servicios']));
 
        $mobiliarioSync = [];
        if($request->filled('mobiliario')){
            foreach ($request->mobiliario as $item){
                $mobiliarioSync[$item['id']] = ['cantidad' => $item['cantidad']];
            }
        }
        $paquete->mobiliario()->sync($mobiliarioSync);
        $paquete->platillos()->sync($request->platillos ?? []);
 
        $this->guardarServicios($paquete, $request->input('servicios', []));
 
        return redirect()->route('paquetes.index')
        ->with('success','Paquete actualizado correctamente');
    }
 
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paquete $paquete): RedirectResponse
    {
        $tieneReservaciones = $paquete->reservaciones()
            ->whereIn('status', ['pendiente', 'confirmada', 'en curso'])
            ->exists();
 
        if ($tieneReservaciones) {
            return redirect()->route('paquetes.index')
                ->with('error', 'No puedes eliminar este paquete porque tiene reservaciones activas.');
        }
 
        $paquete->update(['activo' => false]);
        $paquete->delete();
 
        return redirect()->route('paquetes.index')
            ->with('success', 'Paquete eliminado correctamente.');
    }
 
    /**
     * Reemplaza los servicios adicionales del paquete por los que llegan del formulario.
     */
    private function guardarServicios(Paquete $paquete, array $servicios): void
    {
        $paquete->servicios()->delete();
 
        foreach ($servicios as $s) {
            $paquete->servicios()->create([
                'concepto' => $s['concepto'],
                'precio'   => $s['precio'] ?? 0,
            ]);
        }
    }
}
