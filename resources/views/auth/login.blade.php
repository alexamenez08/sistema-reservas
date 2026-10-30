<x-guest-layout>
    <div class="mt-6 text-center">
        <h1 class="text-2xl font-extrabold text-[#0b1f3a]">Iniciar sesión en SOTyTECH</h1>
        <p class="mt-2 text-sm text-slate-500">Ingresa tus credenciales para acceder a tu cuenta</p>
    </div>

    <x-auth-session-status class="mt-4 text-center" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
        @csrf

        {{-- Correo --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-[#0b1f3a]">Correo electrónico</label>
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       placeholder="nombre@empresa.com"
                       class="block w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0b1f3a] focus:bg-white focus:ring-2 focus:ring-[#0b1f3a]/20">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Contraseña --}}
        <div x-data="{ ver: false }">
            <label for="password" class="block text-sm font-semibold text-[#0b1f3a]">Contraseña</label>
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4"/></svg>
                </span>
                <input id="password" :type="ver ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       placeholder="Ingresa tu contraseña"
                       class="block w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-11 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0b1f3a] focus:bg-white focus:ring-2 focus:ring-[#0b1f3a]/20">
                <button type="button" @click="ver = !ver" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-[#0b1f3a]"
                        :aria-label="ver ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                    <svg x-show="!ver" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="ver" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Recordarme y recuperación --}}
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-[#0b1f3a] shadow-sm focus:ring-[#0b1f3a]/30">
                <span class="ms-2 text-sm text-slate-500">Recordarme</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-[#0b1f3a] hover:underline">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-[#0b1f3a] py-3 text-sm font-semibold text-white shadow-lg shadow-[#0b1f3a]/30 transition hover:bg-[#15335f] focus:outline-none focus:ring-4 focus:ring-[#0b1f3a]/30">
            Ingresar
        </button>
    </form>
</x-guest-layout>
