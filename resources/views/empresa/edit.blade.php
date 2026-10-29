<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Perfil de la empresa</h2>
  </x-slot>

  <div class="py-8">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

      @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
          {{ session('success') }}
        </div>
      @endif

      <div class="bg-white shadow-sm rounded-lg p-6">
        <p class="text-sm text-gray-500 mb-6">
          Estos datos aparecen en el menú lateral y en las cotizaciones en PDF.
        </p>

        <form action="{{ route('empresa.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
          @csrf
          @method('PUT')

          {{-- Logo --}}
          <div>
            <x-input-label for="logo" value="Logo" />
            <div class="mt-2 flex items-center gap-4">
              <div class="w-24 h-24 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                <img id="logo-preview" src="{{ $empresa->logo_url }}" alt="Logo"
                     class="max-w-full max-h-full {{ $empresa->logo_url ? '' : 'hidden' }}">
                <span id="logo-vacio" class="material-symbols-outlined text-gray-300 text-[40px] {{ $empresa->logo_url ? 'hidden' : '' }}">image</span>
              </div>
              <div>
                <input id="logo" name="logo" type="file" accept="image/png,image/jpeg"
                       class="block text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-400 mt-1">PNG o JPG, máximo 2 MB. Se recomienda fondo transparente (PNG).</p>
                @if($empresa->logo)
                  <label class="inline-flex items-center gap-2 mt-2 text-sm text-gray-600">
                    <input type="checkbox" name="quitar_logo" value="1" class="rounded border-gray-300 text-indigo-600">
                    Quitar el logo actual
                  </label>
                @endif
              </div>
            </div>
            <x-input-error :messages="$errors->get('logo')" class="mt-1" />
          </div>

          {{-- Nombre --}}
          <div>
            <x-input-label for="nombre" value="Nombre de la empresa *" />
            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                          value="{{ old('nombre', $empresa->nombre) }}" required />
            <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <x-input-label for="telefono" value="Teléfono" />
              <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full"
                            value="{{ old('telefono', $empresa->telefono) }}" />
              <x-input-error :messages="$errors->get('telefono')" class="mt-1" />
            </div>
            <div>
              <x-input-label for="correo" value="Correo de contacto" />
              <x-text-input id="correo" name="correo" type="email" class="mt-1 block w-full"
                            value="{{ old('correo', $empresa->correo) }}" />
              <x-input-error :messages="$errors->get('correo')" class="mt-1" />
            </div>
          </div>

          <div>
            <x-input-label for="direccion" value="Dirección o leyenda (aparece en el pie de la cotización)" />
            <x-text-input id="direccion" name="direccion" type="text" class="mt-1 block w-full"
                          value="{{ old('direccion', $empresa->direccion) }}" />
            <x-input-error :messages="$errors->get('direccion')" class="mt-1" />
          </div>

          <div class="flex justify-end pt-2">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
              Guardar cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Vista previa del logo al elegir un archivo
    document.getElementById('logo').addEventListener('change', function () {
      const file = this.files[0];
      if (!file) return;
      const img = document.getElementById('logo-preview');
      img.src = URL.createObjectURL(file);
      img.classList.remove('hidden');
      document.getElementById('logo-vacio').classList.add('hidden');
    });
  </script>
</x-app-layout>