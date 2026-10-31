<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notificaciones</h2>

            @if($noLeidas > 0)
                <form action="{{ route('notificaciones.leer-todas') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm bg-white ring-1 ring-slate-200 hover:bg-slate-50 text-gray-700 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">done_all</span>
                        Marcar todas como leídas
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">{{ session('success') }}</div>
            @endif

            <p class="text-sm text-gray-500">
                {{ $noLeidas > 0 ? "Tienes {$noLeidas} notificación(es) sin leer." : 'No tienes notificaciones sin leer.' }}
            </p>

            <div class="bg-white shadow-sm rounded-lg divide-y divide-gray-100">
                @forelse($notificaciones as $n)
                    <a href="{{ route('notificaciones.abrir', $n->id) }}"
                       class="flex items-start gap-4 px-5 py-4 hover:bg-gray-50 {{ $n->read_at ? '' : 'bg-indigo-50/60' }}">
                        <span class="material-symbols-outlined text-[24px] mt-0.5 {{ $n->read_at ? 'text-gray-400' : 'text-indigo-600' }}">
                            {{ $n->data['icono'] ?? 'notifications' }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm {{ $n->read_at ? 'font-medium text-gray-700' : 'font-semibold text-gray-900' }}">
                                    {{ $n->data['titulo'] ?? 'Notificación' }}
                                </p>
                                @unless($n->read_at)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-indigo-100 text-indigo-700">Nueva</span>
                                @endunless
                            </div>
                            <p class="text-sm text-gray-600 mt-0.5">{{ $n->data['mensaje'] ?? '' }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $n->created_at->format('d/m/Y H:i') }} · {{ $n->created_at->locale('es')->diffForHumans() }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-14 text-center text-gray-400">
                        <span class="material-symbols-outlined text-[40px]">notifications_off</span>
                        <p class="mt-2 text-sm italic">Todavía no tienes notificaciones.</p>
                    </div>
                @endforelse
            </div>

            <div>{{ $notificaciones->links() }}</div>
        </div>
    </div>
</x-app-layout>