<x-guest-layout>
    <div class="mt-6 text-center">
        <h1 class="text-2xl font-extrabold text-[#0b1f3a]">Recuperar contraseña</h1>
        <p class="mt-2 text-sm text-slate-500">
            Escribe tu correo y te enviaremos un enlace para que elijas una contraseña nueva.
        </p>
    </div>

    <x-auth-session-status class="mt-4 text-center" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-[#0b1f3a]">Correo electrónico</label>
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="nombre@empresa.com"
                       class="block w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0b1f3a] focus:bg-white focus:ring-2 focus:ring-[#0b1f3a]/20">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-[#0b1f3a] py-3 text-sm font-semibold text-white shadow-lg shadow-[#0b1f3a]/30 transition hover:bg-[#15335f] focus:outline-none focus:ring-4 focus:ring-[#0b1f3a]/30">
            Enviar enlace de recuperación
        </button>

        <p class="text-center">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-[#0b1f3a] hover:underline">← Volver a iniciar sesión</a>
        </p>
    </form>
</x-guest-layout>
