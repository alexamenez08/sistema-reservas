<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LocacionRequest extends FormRequest
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
            'nombre'           => ['required', 'string', 'max:255'],
            'descripcion'      => ['nullable', 'string'],
            'capacidad'        => ['required', 'integer', 'min:1'],
            'precio_base'      => ['required', 'numeric', 'min:0'],
            'calle'            => ['required', 'string', 'max:150'],
            'numero_exterior'  => ['required', 'string', 'max:20'],
            'numero_interior'  => ['nullable', 'string', 'max:20'],
            'colonia'          => ['required', 'string', 'max:120'],
            'entidad_feder'    => ['required', 'string', 'max:100'],
            'codigo_postal'    => ['required', 'string', 'max:10'],
            'latitud'          => ['required', 'numeric'],
            'longitud'         => ['required', 'numeric'],
            'activo'           => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'          => 'El nombre es obligatorio.',
            'capacidad.required'       => 'La capacidad es obligatoria.',
            'capacidad.min'            => 'La capacidad debe ser mayor a 0.',
            'precio_base.required'     => 'El precio base es obligatorio.',
            'calle.required'           => 'La calle es obligatoria.',
            'numero_exterior.required' => 'El número exterior es obligatorio.',
            'colonia.required'         => 'La colonia es obligatoria.',
            'entidad_feder.required'   => 'La entidad federativa es obligatoria.',
            'codigo_postal.required'   => 'El código postal es obligatorio.',
            'latitud.required'         => 'La latitud es obligatoria.',
            'longitud.required'        => 'La longitud es obligatoria.',
        ];
    }
}
