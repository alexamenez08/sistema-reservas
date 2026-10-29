<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AnticipoRequest extends FormRequest
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
            'monto'      => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['required', 'date'],
            'notas'      => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'monto.required'      => 'El monto es obligatorio.',
            'monto.min'           => 'El monto debe ser mayor a 0.',
            'fecha_pago.required' => 'La fecha de pago es obligatoria.',
        ];
    }
}
