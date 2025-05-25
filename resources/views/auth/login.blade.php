@extends('layout')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-zinc-900 to-black flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-zinc-800 p-8 rounded-2xl shadow-lg">
        <h2 class="text-4xl font-extrabold text-center text-violet-500 mb-6">Iniciar sesión</h2>

        @if (session('status'))
            <div class="mb-4 text-green-400 font-semibold text-sm text-center">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="block mb-1 text-zinc-300 font-semibold">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full px-4 py-2 rounded-lg bg-zinc-900 text-white border border-zinc-600 focus:outline-none focus:ring-2 focus:ring-violet-500 transition">
                @error('email')
                    <span class="text-red-400 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password" class="block mb-1 text-zinc-300 font-semibold">Contraseña</label>
                <input id="password" type="password" name="password" required
                       class="w-full px-4 py-2 rounded-lg bg-zinc-900 text-white border border-zinc-600 focus:outline-none focus:ring-2 focus:ring-violet-500 transition">
                @error('password')
                    <span class="text-red-400 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm text-zinc-400">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="mr-2 rounded text-violet-500 focus:ring-violet-500">
                    Recuérdame
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="hover:underline text-violet-400">¿Olvidaste tu contraseña?</a>
                @endif
            </div>

            <button type="submit"
                    class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-500 rounded-lg text-white font-bold text-lg hover:scale-105 transition-transform">
                Iniciar sesión
            </button>

            <p class="text-center text-zinc-400 text-sm mt-4">
                ¿No tienes cuenta?
                <a href="{{ route('register') }}" class="text-violet-400 hover:underline">Regístrate aquí</a>
            </p>
        </form>
    </div>
</div>
@endsection
