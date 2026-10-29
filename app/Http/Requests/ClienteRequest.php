<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClienteRequest extends FormRequest
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
        $clienteId = $this->route('cliente')?->id;

        return [
            //
            'nombre' => ['required', 'string', 'max:100'],
            'apepat' => ['required', 'string', 'max:100'],
            'apemat' => ['nullable', 'string', 'max:100'],
            'correo' => [
            'required', 
            'email', 
            'max:200', 
            Rule::unique('clientes', 'correo')->ignore($clienteId)],
            'tel'    => ['required', 'string', 'max:20'],

        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'apepat.required' => 'El apellido paterno es obligatorio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.unique'   => 'Este correo ya está registrado.',
            'tel.required'    => 'El teléfono es obligatorio.',
        ];
    }
}
