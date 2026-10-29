<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MobiliarioRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'stock_total' => ['required', 'integer', 'min:1'],
            'precio_renta_unitario' => ['required', 'numeric', 'min:0'],
            'costo_reposicion' => ['nullable', 'numeric', 'min:0'],
            'activo' => ['boolean'],
            'id_categoriamob' => ['required', 'exists:categorias_mobiliario,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'                => 'El nombre es obligatorio.',
            'stock_total.required'           => 'El stock total es obligatorio.',
            'stock_total.min'                => 'El stock total debe ser mayor a 0.',
            'precio_renta_unitario.required' => 'El precio de renta es obligatorio.',
            'id_categoriamob.required'       => 'Debes seleccionar una categoría.',
            'id_categoriamob.exists'         => 'La categoría seleccionada no existe.',
        ];
    }
}
