<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Override;

class UserRequest extends FormRequest
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
        $usuario = $this->route('usuario');

        return [
            //
            'name'  =>['required', 'string', 'max:100'],
            'last_name'  =>['required', 'string', 'max:100'],
            'email'  =>['required', 'email', Rule::unique('users' , 'email')->ignore($usuario?->id)],
            'phone'  =>['nullable', 'string', 'max:20'],
            'rol'  =>['required', 'in:administrador,coordinador,staff'],
            'is_active'  =>['boolean'],
            'password'  => $usuario ? ['nullable', Password::min(8)] 
                                    : ['nullable', Password::min(8)],
        ];
    }

    #[Override]
    public function messages()
    {
        return[
            'name.required' => 'El nombre es obligatorio',
            'last_name.required' => 'El apellido es obligatorio',
            'email.required' => 'El correo es obligatorio',
            'rol.required' => 'Debes asignar un rol',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La constraseña debe tener al menos 8 caracteres',
        ];
    }
}
