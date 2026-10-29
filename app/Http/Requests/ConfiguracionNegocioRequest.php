<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConfiguracionNegocioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('administrador') ?? false;
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
            'nombre'     => ['required', 'string', 'max:120'],
            'telefono'   => ['nullable', 'string', 'max:30'],
            'correo'     => ['nullable', 'email', 'max:150'],
            'direccion'  => ['nullable', 'string', 'max:255'],
            // dompdf solo maneja bien PNG y JPG (no WebP), por eso se limita aquí
            'logo'       => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'quitar_logo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la empresa es obligatorio.',
            'correo.email'    => 'Ingresa un correo válido.',
            'logo.image'      => 'El archivo debe ser una imagen.',
            'logo.mimes'      => 'El logo debe ser PNG o JPG.',
            'logo.max'        => 'El logo no debe pesar más de 2 MB.',
        ];
    }
}
