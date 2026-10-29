<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnticipoRequest;
use App\Models\Anticipo;
use App\Models\Reservacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnticipoController extends Controller
{
    //
    public function store(AnticipoRequest $request, Reservacion $reservacion): RedirectResponse
    {
        $data = $request->validated();

        // Validar que el monto no supere el saldo pendiente
        if ($data['monto'] > $reservacion->saldo_pendiente) {
            return back()->withInput()->withErrors([
                'monto' => "El monto ($" . number_format($data['monto'], 2) . ") supera el saldo pendiente ($" . number_format($reservacion->saldo_pendiente, 2) . ").",
            ]);
        }

        $data['reservacion_id'] = $reservacion->id;
        Anticipo::create($data);

        return redirect()->route('reservaciones.show', $reservacion)
            ->with('success', 'Anticipo registrado correctamente.');
    }

    public function destroy(Anticipo $anticipo): RedirectResponse
    {
        $reservacion = $anticipo->reservacion;
        $anticipo->delete();

        return redirect()->route('reservaciones.show', $reservacion)
            ->with('success', 'Anticipo eliminado correctamente.');
    }
}
