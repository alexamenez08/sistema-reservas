{{--
  Campanita de notificaciones para la barra superior (FN20).
  Muestra el número de no leídas y, al hacer clic, las 5 más recientes con un enlace a "Ver todas".
  Uso:  @include('layouts.campanita')   (dentro de layouts/navigation.blade.php)
  Requiere Alpine (ya viene en Breeze) y Material Symbols (ya se usa en el sidebar).
--}}
@php
    /** @var \App\Models\User $usuarioCampana */
    $usuarioCampana   = auth()->user();
    $noLeidasCampana  = $usuarioCampana->unreadNotifications()->count();
    $recientesCampana = $usuarioCampana->notifications()->limit(5)->get();
@endphp

<div x-data="{ abierto: false }"
     @click.outside="abierto = false"
     @keydown.escape.window="abierto = false"
     class="relative me-3">

    <button type="button" @click="abierto = ! abierto" aria-label="Notificaciones"
            class="relative p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none transition duration-150">
        <span class="material-symbols-outlined text-[24px] leading-none">notifications</span>

        @if($noLeidasCampana > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-semibold bg-red-500 text-white">
                {{ $noLeidasCampana > 99 ? '99+' : $noLeidasCampana }}
            </span>
        @endif
    </button>

    {{-- display:none en línea para que no parpadee antes de que cargue Alpine --}}
    <div x-show="abierto" style="display: none;"
         x-transition.opacity.duration.150ms
         class="absolute right-0 mt-2 w-96 max-w-[90vw] bg-white rounded-lg shadow-lg ring-1 ring-black/5 z-50 overflow-hidden">

        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <p class="text-sm font-semibold text-gray-800">Notificaciones</p>
            @if($noLeidasCampana > 0)
                <span class="text-xs text-gray-500">{{ $noLeidasCampana }} sin leer</span>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
            @forelse($recientesCampana as $n)
                <a href="{{ route('notificaciones.abrir', $n->id) }}"
                   class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 {{ $n->read_at ? '' : 'bg-indigo-50/60' }}">
                    <span class="material-symbols-outlined text-[22px] mt-0.5 {{ $n->read_at ? 'text-gray-400' : 'text-indigo-600' }}">
                        {{ $n->data['icono'] ?? 'notifications' }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm {{ $n->read_at ? 'font-medium text-gray-700' : 'font-semibold text-gray-900' }}">
                            {{ $n->data['titulo'] ?? 'Notificación' }}
                        </p>
                        <p class="text-xs text-gray-600 mt-0.5">{{ \Illuminate\Support\Str::limit($n->data['mensaje'] ?? '', 100) }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $n->created_at->format('d/m/Y H:i') }} · {{ $n->created_at->locale('es')->diffForHumans() }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="px-4 py-8 text-center text-gray-400">
                    <span class="material-symbols-outlined text-[32px]">notifications_off</span>
                    <p class="mt-1 text-sm italic">No tienes notificaciones.</p>
                </div>
            @endforelse
        </div>

        <a href="{{ route('notificaciones.index') }}"
           class="block px-4 py-3 text-center text-sm font-medium text-indigo-600 hover:bg-gray-50 border-t border-gray-100">
            Ver todas las notificaciones
        </a>
    </div>
</div>