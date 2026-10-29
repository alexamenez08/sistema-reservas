<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('usuarios.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Usuario
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <form action="{{ route('usuarios.update', $usuario) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Nombre --}}
                    <div>
                        <x-input-label for="name" value="Nombre(s)" />
                        <x-text-input id="name" name="name" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('name', $usuario->name) }}" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    {{-- Apellido --}}
                    <div>
                        <x-input-label for="last_name" value="Apellido(s)" />
                        <x-text-input id="last_name" name="last_name" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('last_name', $usuario->last_name) }}" />
                        <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
                    </div>

                    {{-- Correo --}}
                    <div>
                        <x-input-label for="email" value="Correo electrónico" />
                        <x-text-input id="email" name="email" type="email"
                            class="mt-1 block w-full"
                            value="{{ old('email', $usuario->email) }}" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    {{-- Teléfono --}}
                    <div>
                        <x-input-label for="phone" value="Teléfono (opcional)" />
                        <x-text-input id="phone" name="phone" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('phone', $usuario->phone) }}" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>

                    {{-- Rol --}}
                    <div>
                        <x-input-label for="rol" value="Rol" />
                        <select id="rol" name="rol"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @foreach($roles as $rol)
                                <option value="{{ $rol->name }}"
                                    {{ $usuario->hasRole($rol->name) ? 'selected' : '' }}>
                                    {{ ucfirst($rol->name) }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('rol')" class="mt-1" />
                    </div>

                    {{-- Estado --}}
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            {{ $usuario->is_active ? 'checked' : '' }}>
                        <x-input-label for="is_active" value="Usuario activo" />
                    </div>

                    {{-- Nueva contraseña (opcional) --}}
                    <div>
                        <x-input-label for="password" value="Nueva contraseña (opcional)" />
                        <x-text-input id="password" name="password" type="password"
                            class="mt-1 block w-full"
                            placeholder="Dejar vacío para no cambiarla" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('usuarios.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-800 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            Guardar Cambios
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>