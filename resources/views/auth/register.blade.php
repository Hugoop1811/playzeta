@extends('layout')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-zinc-900 to-black flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-zinc-800 p-8 rounded-2xl shadow-lg">
        <h2 class="text-4xl font-extrabold text-center text-violet-500 mb-6">Crear cuenta</h2>

        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block mb-1 text-zinc-300 font-semibold">Nombre</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full px-4 py-2 rounded-lg bg-zinc-900 text-white border border-zinc-600 focus:outline-none focus:ring-2 focus:ring-violet-500 transition">
                @error('name')
                    <span class="text-red-400 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="email" class="block mb-1 text-zinc-300 font-semibold">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
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

            <div>
                <label for="password_confirmation" class="block mb-1 text-zinc-300 font-semibold">Confirmar contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       class="w-full px-4 py-2 rounded-lg bg-zinc-900 text-white border border-zinc-600 focus:outline-none focus:ring-2 focus:ring-violet-500 transition">
            </div>

            <button type="submit"
                    class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-500 rounded-lg text-white font-bold text-lg hover:scale-105 transition-transform">
                Registrarse
            </button>

            <p class="text-center text-zinc-400 text-sm mt-4">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}" class="text-violet-400 hover:underline">Inicia sesión</a>
            </p>
        </form>
    </div>
</div>
@endsection
