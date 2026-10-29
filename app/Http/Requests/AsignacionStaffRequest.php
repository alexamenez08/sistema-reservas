<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AsignacionStaffRequest extends FormRequest
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
            'rol_asignado' => ['required', 'string', 'max:100'],
            'pago_evento'  => ['required', 'numeric', 'min:0'],
            'empleado_id'  => ['required', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'rol_asignado.required' => 'El rol asignado es obligatorio.',
            'pago_evento.required'  => 'El pago es obligatorio.',
            'pago_evento.min'       => 'El pago no puede ser negativo.',
            'empleado_id.required'  => 'Debes seleccionar un empleado.',
            'empleado_id.exists'    => 'El empleado seleccionado no existe.',
        ];
    }
}
