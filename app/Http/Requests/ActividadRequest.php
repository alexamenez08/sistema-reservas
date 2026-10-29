<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ActividadRequest extends FormRequest
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
            'actividad'   => ['required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string'],
            'hora_inicio' => ['required', 'date'],
            'hora_fin'    => ['required', 'date', 'after:hora_inicio'],
            'responsable' => ['required', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'actividad.required'   => 'El nombre de la actividad es obligatorio.',
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'hora_fin.required'    => 'La hora de fin es obligatoria.',
            'hora_fin.after'       => 'La hora de fin debe ser posterior a la hora de inicio.',
            'responsable.required' => 'El responsable es obligatorio.',
            'responsable.exists'   => 'El usuario seleccionado no existe.',
        ];
    }
}
