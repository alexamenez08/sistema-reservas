<?php

namespace App\Http\Controllers;

use App\Models\Locacion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapaController extends Controller
{
    //
    public function index(): View
    {
        $locaciones = Locacion::where('activo', true)
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Locacion $l) => [
                'id'        => $l->id,
                'nombre'    => $l->nombre,
                'direccion' => $l->direccion,
                'capacidad' => $l->capacidad,
                'lat'       => (float) $l->latitud,
                'lng'       => (float) $l->longitud,
                'url'       => route('locaciones.show', $l),
            ])
            ->values();
 
        return view('mapa.index', compact('locaciones'));
    }
}
