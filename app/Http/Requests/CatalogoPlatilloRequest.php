<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CatalogoPlatilloRequest extends FormRequest
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
            'nombre'            => ['required', 'string', 'max:100'],
            'categoria'         => ['required', 'in:entrada,plato fuerte,postre,bebida,menu infantil'],
            'descripcion'       => ['nullable', 'string'],
            'costo_preparacion' => ['nullable', 'numeric', 'min:0'],
            'precio_venta'      => ['nullable', 'numeric', 'min:0'],
            'imagen'            => ['nullable', 'image', 'max:2048'],
        ];
    }
 
    public function messages(): array{
        return [
            'nombre.required'    => 'El nombre del platillo es obligatorio.',
            'categoria.required' => 'La categoría es obligatoria.',
            'categoria.in'       => 'La categoría seleccionada no es válida.',
            'precio_venta.numeric' => 'El precio de venta debe ser un número.',
            'imagen.image'       => 'El archivo debe ser una imagen.',
            'imagen.max'         => 'La imagen no debe pesar más de 2MB.',
        ];
    }
}
