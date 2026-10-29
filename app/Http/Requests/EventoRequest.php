<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EventoRequest extends FormRequest
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
            //
            'estado_montaje' => ['required', 'in:pendiente,en proceso,terminado'],
            'reservacion_id' => ['sometimes', 'exists:reservaciones,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'estado_montaje.required' => 'El estado del montaje es obligatorio.',
            'reservacion_id.required' => 'La reservación es obligatoria.',
            'reservacion_id.exists'   => 'La reservación seleccionada no existe.',
        ];
    }
    
}
