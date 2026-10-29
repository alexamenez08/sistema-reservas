<?php

namespace App\Http\Requests;

use App\Models\Locacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PaqueteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
 
    /**
     * Quita las filas de "servicios adicionales" que se agregaron pero se dejaron totalmente vacías.
     */
    protected function prepareForValidation(): void
    {
        if (is_array($this->input('servicios'))) {
            $this->merge([
                'servicios' => collect($this->input('servicios'))
                    ->filter(fn ($s) => trim((string) ($s['concepto'] ?? '')) !== '' || trim((string) ($s['precio'] ?? '')) !== '')
                    ->values()
                    ->all(),
            ]);
        }
    }
 
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre'       => ['required', 'string', 'max:100'],
            'descripcion'  => ['nullable', 'string'],
            'capacidad'    => [
            'required', 
            'integer', 
            'min:1',
            function ($attribute, $value, $fail) {
                $locacionId = $this->input('id_locacion');
                if ($locacionId) {
                    $locacion = Locacion::find($locacionId);
                    if ($locacion && $value > $locacion->capacidad) {
                        $fail("La capacidad del paquete ({$value}) no puede ser mayor a la capacidad de la locación ({$locacion->capacidad} personas).");
                    }
                }
            }
        ],
            'precio_venta' => ['required', 'numeric', 'min:0.01'],
            'precio_persona_extra' => ['nullable', 'numeric', 'min:0'],
            'activo'       => ['boolean'],
            'id_locacion'  => ['required', 'exists:locaciones,id'],
            'mobiliario'   => ['nullable', 'array'],
            'mobiliario.*.id'       => ['required', 'exists:mobiliario,id'],
            'mobiliario.*.cantidad' => ['required', 'integer', 'min:1'],
            'platillos'    => ['nullable', 'array'],
            'platillos.*'  => ['exists:catalogo_platillos,id'],
            'servicios'            => ['nullable', 'array'],
            'servicios.*.concepto' => ['required', 'string', 'max:120'],
            'servicios.*.precio'   => ['nullable', 'numeric', 'min:0'],
        ];
    }
 
    public function messages(): array
    {
        return [
            'nombre.required'      => 'El nombre del paquete es obligatorio.',
            'precio_venta.required' => 'El precio de venta del paquete es obligatorio.',
            'precio_venta.min'     => 'El precio de venta debe ser mayor a $0.',
            'capacidad.required'   => 'La capacidad es obligatoria.',
            'capacidad.min'        => 'La capacidad debe ser mayor a 0.',
            'id_locacion.required' => 'Debes seleccionar una locación.',
            'id_locacion.exists'   => 'La locación seleccionada no existe.',
            'servicios.*.concepto.required' => 'Cada servicio adicional necesita un concepto.',
            'servicios.*.precio.numeric'    => 'El precio de un servicio adicional debe ser un número.',
        ];
    }
}
