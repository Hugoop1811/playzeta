@extends('layout')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-zinc-900 to-black flex flex-col justify-center items-center text-white px-4">
    <h1 class="text-6xl font-extrabold tracking-widest text-violet-500 drop-shadow-md animate-pulse mb-6">PlayZeta</h1>
    <p class="text-xl mb-10 text-zinc-300">Elige cómo quieres jugar hoy</p>

    <div class="flex flex-col gap-4 w-full max-w-sm">
        <a href="{{ route('login') }}"
           class="bg-gradient-to-r from-purple-600 to-indigo-500 text-white py-3 px-6 rounded-xl font-semibold text-lg shadow-lg hover:scale-105 transition-transform duration-300 text-center">
            Iniciar sesión
        </a>
        <a href="{{ route('register') }}"
           class="bg-gradient-to-r from-pink-500 to-red-500 text-white py-3 px-6 rounded-xl font-semibold text-lg shadow-lg hover:scale-105 transition-transform duration-300 text-center">
            Registrarse
        </a>
        <a href="{{ route('games.index') }}"
           class="bg-zinc-800 border border-zinc-600 py-3 px-6 rounded-xl font-semibold text-lg text-white hover:bg-zinc-700 transition text-center">
            Jugar 
        </a>
    </div>

    <div class="mt-16 opacity-10 text-9xl font-black text-zinc-700 select-none pointer-events-none animate-bounce">
        Z
    </div>
</div>
@endsection
