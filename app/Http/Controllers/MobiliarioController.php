<?php

namespace App\Http\Controllers;

use App\Http\Requests\MobiliarioRequest;
use App\Models\CategoriaMobiliario;
use App\Models\Mobiliario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class MobiliarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index():View
    {
        //
        $mobiliario = Mobiliario::with('categoria')->latest()->paginate(10);
        return view('mobiliario.index', compact('mobiliario'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        //
        $categorias = CategoriaMobiliario::all();
        return view('mobiliario.create', compact('categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MobiliarioRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['stock_disponible'] = $data['stock_total'];

        Mobiliario::create($data);

        return redirect()->route('mobiliario.index')
            ->with('success', 'Artículo registrado correctamente.');
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
    public function edit(Mobiliario $mobiliario): View
    {
        $categorias = CategoriaMobiliario::all();
        return view('mobiliario.edit', compact('mobiliario', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MobiliarioRequest $request, Mobiliario $mobiliario): RedirectResponse
    {
        $data = $request->validated();

        $diferencia = $data['stock_total'] - $mobiliario->stock_total;
        $data['stock_disponible'] = $mobiliario->stock_disponible + $diferencia;

        $mobiliario->update($data);

        return redirect()->route('mobiliario.index')
            ->with('success', 'Artículo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mobiliario $mobiliario): RedirectResponse
    {
        $enUso = DB::table('inventario_paquete')
            ->where('id_articulo', $mobiliario->id)
            ->exists();

        if ($enUso) {
            return redirect()->route('mobiliario.index')
                ->with('error', 'No puedes eliminar este artículo porque está asignado a uno o más paquetes.');
        }

        $mobiliario->update(['activo' => false]);
        $mobiliario->delete();

        return redirect()->route('mobiliario.index')
            ->with('success', 'Artículo eliminado correctamente.');
    }
}
