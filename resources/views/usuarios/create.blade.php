<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('usuarios.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nuevo Usuario
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <form action="{{ route('usuarios.store') }}" method="POST" class="space-y-5">
                    @csrf
                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Nombre --}}
                    <div>
                        <x-input-label for="name" value="Nombre(s)" />
                        <x-text-input id="name" name="name" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('name') }}"
                            placeholder="Ej. María" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    {{-- Apellido --}}
                    <div>
                        <x-input-label for="last_name" value="Apellido(s)" />
                        <x-text-input id="last_name" name="last_name" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('last_name') }}"
                            placeholder="Ej. García López" />
                        <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
                    </div>

                    {{-- Correo --}}
                    <div>
                        <x-input-label for="email" value="Correo electrónico" />
                        <x-text-input id="email" name="email" type="email"
                            class="mt-1 block w-full"
                            value="{{ old('email') }}"
                            placeholder="correo@ejemplo.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    {{-- Teléfono --}}
                    <div>
                        <x-input-label for="phone" value="Teléfono (opcional)" />
                        <x-text-input id="phone" name="phone" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('phone') }}"
                            placeholder="Ej. 777 123 4567" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>

                    {{-- Rol --}}
                    <div>
                        <x-input-label for="rol" value="Rol" />
                        <select id="rol" name="rol"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">-- Selecciona un rol --</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->name }}" {{ old('rol') == $rol->name ? 'selected' : '' }}>
                                    {{ ucfirst($rol->name) }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('rol')" class="mt-1" />
                    </div>

                    {{-- Nota contraseña --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex items-start gap-2 text-sm text-blue-700">
                        <span class="material-symbols-outlined text-[18px] mt-0.5">info</span>
                        Se generará una contraseña temporal automáticamente. El usuario podrá cambiarla al iniciar sesión.
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('usuarios.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-800 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            Crear Usuario
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>