<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nuevo Cliente
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <form action="{{ route('clientes.store') }}" method="POST" class="space-y-5">
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
                        <x-input-label for="nombre" value="Nombre(s)" />
                        <x-text-input id="nombre" name="nombre" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('nombre') }}"
                            placeholder="Ej. María" />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
                    </div>

                    {{-- Apellidos --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="apepat" value="Apellido paterno" />
                            <x-text-input id="apepat" name="apepat" type="text"
                                class="mt-1 block w-full"
                                value="{{ old('apepat') }}"
                                placeholder="Ej. García" />
                            <x-input-error :messages="$errors->get('apepat')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="apemat" value="Apellido materno (opcional)" />
                            <x-text-input id="apemat" name="apemat" type="text"
                                class="mt-1 block w-full"
                                value="{{ old('apemat') }}"
                                placeholder="Ej. López" />
                            <x-input-error :messages="$errors->get('apemat')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Correo --}}
                    <div>
                        <x-input-label for="correo" value="Correo electrónico" />
                        <x-text-input id="correo" name="correo" type="email"
                            class="mt-1 block w-full"
                            value="{{ old('correo') }}"
                            placeholder="correo@ejemplo.com" />
                        <x-input-error :messages="$errors->get('correo')" class="mt-1" />
                    </div>

                    {{-- Teléfono --}}
                    <div>
                        <x-input-label for="tel" value="Teléfono" />
                        <x-text-input id="tel" name="tel" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('tel') }}"
                            placeholder="Ej. 777 123 4567" />
                        <x-input-error :messages="$errors->get('tel')" class="mt-1" />
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('clientes.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-800 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            Guardar Cliente
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>