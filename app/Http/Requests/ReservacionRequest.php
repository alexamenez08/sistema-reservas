<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReservacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dia_evento'         => ['required', 'date'],
            'hora_inicio'        => ['required'],
            'hora_fin'           => ['required', 'after:hora_inicio'],
            'contador_invitados' => ['required',
                                    'integer',
                                    'min:1',
                                    function ($attribute, $value, $fail) {
                                        $locacionId = $this->input('locacion_id');
                                        if ($locacionId) {
                                            $locacion = \App\Models\Locacion::find($locacionId);
                                            if ($locacion && $value > $locacion->capacidad) {
                                                $fail("El número de invitados ({$value}) supera la capacidad de la locación ({$locacion->capacidad} personas).");
                                            }
                                        }
                                    }
                                ],
            // costo_total YA NO se captura: lo calcula CotizadorReservacion (subtotal - descuento)
            'descuento'          => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ((float) $value > 0 && trim((string) $this->input('motivo_descuento')) === '') {
                        $fail('Indica el motivo del descuento.');
                    }
                },
            ],
            'motivo_descuento'   => ['nullable', 'string', 'max:150'],
            'status'             => ['required', 'in:pendiente,confirmada,en curso,finalizada,cancelada'],
            'cliente_id'         => ['required', 'exists:clientes,id'],
            'locacion_id'        => ['required', 'exists:locaciones,id'],
            'paquete_id'         => ['nullable', 'exists:paquetes,id'],
        ];
    }
 
    public function messages(): array
    {
        return [
            'dia_evento.required'         => 'La fecha del evento es obligatoria.',
            'dia_evento.after_or_equal'   => 'La fecha del evento no puede ser anterior a hoy.',
            'hora_inicio.required'        => 'La hora de inicio es obligatoria.',
            'hora_fin.required'           => 'La hora de fin es obligatoria.',
            'hora_fin.after'              => 'La hora de fin debe ser posterior a la hora de inicio.',
            'contador_invitados.required' => 'El número de invitados es obligatorio.',
            'descuento.numeric'           => 'El descuento debe ser un número.',
            'descuento.min'               => 'El descuento no puede ser negativo.',
            'cliente_id.required'         => 'Debes seleccionar un cliente.',
            'locacion_id.required'        => 'Debes seleccionar una locación.',
        ];
    }
}
