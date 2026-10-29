<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfiguracionNegocioRequest;
use App\Models\ConfiguracionNegocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ConfiguracionNegocioController extends Controller
{
    //
    public function edit(Request $request): View
    {
        abort_unless($request->user()->hasRole('administrador'), 403);
 
        $empresa = ConfiguracionNegocio::actual();
 
        return view('empresa.edit', compact('empresa'));
    }
 
    public function update(ConfiguracionNegocioRequest $request): RedirectResponse
    {
        $data = $request->validated();
 
        // Siempre trabajamos con la única fila (se crea la primera vez)
        $empresa = ConfiguracionNegocio::query()->first() ?? new ConfiguracionNegocio();
 
        if ($request->hasFile('logo')) {
            if ($empresa->logo) {
                Storage::disk('public')->delete($empresa->logo);
            }
            $data['logo'] = $request->file('logo')->store('empresa', 'public');
        } elseif ($request->boolean('quitar_logo') && $empresa->logo) {
            Storage::disk('public')->delete($empresa->logo);
            $data['logo'] = null;
        } else {
            unset($data['logo']); // no se tocó el logo
        }
 
        unset($data['quitar_logo']);
 
        $empresa->fill($data)->save();
 
        return redirect()->route('empresa.edit')
            ->with('success', 'Perfil de la empresa actualizado correctamente');
    }
}
